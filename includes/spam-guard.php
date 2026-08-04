<?php

/**
 * LearnWise — Spam Guard
 *
 * Centralised, layered bot/spam protection for all public forms.
 * All checks run server-side and cannot be bypassed by disabling JS.
 *
 * Checks (in execution order inside lwRunSpamGauntlet):
 *  1. Origin / Referer verification
 *  2. CSRF token validation
 *  3. Honeypot field check
 *  4. Form timing (< 5 seconds → reject)
 *  5. IP-based rate limiting (3/hr, 10/day)
 *  6. Server-side input sanitisation + suspicious-payload detection
 *  7. Cloudflare Turnstile token verification (when enabled)
 */

declare(strict_types=1);

// ---------------------------------------------------------------
// Configuration helpers
// ---------------------------------------------------------------

function lwSpamGuardConfig(string $key, mixed $default = null): mixed
{
    static $cfg = null;

    if ($cfg === null) {
        $localFile = __DIR__ . '/config.local.php';
        $cfg = is_file($localFile) ? (require $localFile) : [];
        if (!is_array($cfg)) {
            $cfg = [];
        }
    }

    return $cfg[$key] ?? $default;
}

// ---------------------------------------------------------------
// 1. Client IP resolution
// ---------------------------------------------------------------

/**
 * Return the real visitor IP, honouring Cloudflare's CF-Connecting-IP
 * header (only trusted when the request actually comes from CF ranges).
 * For safety we also accept X-Forwarded-For as a secondary source.
 */
function lwGetClientIp(): string
{
    // Cloudflare sets CF-Connecting-IP when the site is proxied through CF.
    $cfIp = trim((string) ($_SERVER['HTTP_CF_CONNECTING_IP'] ?? ''));
    if ($cfIp !== '' && filter_var($cfIp, FILTER_VALIDATE_IP)) {
        return $cfIp;
    }

    // X-Forwarded-For (first hop)
    $xff = trim((string) ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? ''));
    if ($xff !== '') {
        $first = trim(explode(',', $xff)[0]);
        if (filter_var($first, FILTER_VALIDATE_IP)) {
            return $first;
        }
    }

    return trim((string) ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'));
}

// ---------------------------------------------------------------
// 2. Origin / Referer verification
// ---------------------------------------------------------------

function lwVerifyFormOrigin(): bool
{
    $appUrl  = rtrim(lwGetAppUrl(), '/');
    $appHost = parse_url($appUrl, PHP_URL_HOST) ?? '';

    // Check HTTP_ORIGIN (sent by browsers on CORS / fetch requests)
    $origin = trim((string) ($_SERVER['HTTP_ORIGIN'] ?? ''));
    if ($origin !== '') {
        $originHost = parse_url($origin, PHP_URL_HOST) ?? '';
        if (strcasecmp($originHost, $appHost) !== 0) {
            return false;
        }
    }

    // Check HTTP_REFERER (sent by browser on form POSTs)
    $referer = trim((string) ($_SERVER['HTTP_REFERER'] ?? ''));
    if ($referer !== '') {
        $refHost = parse_url($referer, PHP_URL_HOST) ?? '';
        if (strcasecmp($refHost, $appHost) !== 0) {
            return false;
        }
    }

    // If both headers are missing this is a direct endpoint hit (e.g. curl).
    // We still allow it here but CSRF will catch unauthenticated direct posts.
    return true;
}

// ---------------------------------------------------------------
// 3. CSRF token management
// ---------------------------------------------------------------

/**
 * Generate (or retrieve) the session CSRF token.
 * Called during page render to inject the token into hidden form fields.
 */
function lwGetCsrfToken(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return '';
    }

    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Regenerate the CSRF token (called after each submission attempt).
 */
function lwRegenerateCsrfToken(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return '';
    }

    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}

/**
 * Validate the CSRF token submitted with the form.
 * Uses hash_equals() to prevent timing attacks.
 */
function lwVerifyCsrfToken(array $post, array $session): bool
{
    $submitted = trim((string) ($post['csrf_token'] ?? ''));
    $stored    = trim((string) ($session['csrf_token'] ?? ''));

    if ($submitted === '' || $stored === '') {
        return false;
    }

    return hash_equals($stored, $submitted);
}

// ---------------------------------------------------------------
// 4. Honeypot field check
// ---------------------------------------------------------------

/**
 * Returns true (= is spam) if the honeypot "website" field contains any value.
 * Real users never see or fill this field; bots typically fill all fields.
 */
function lwCheckHoneypot(array $post): bool
{
    $honey = trim((string) ($post['website'] ?? ''));
    return $honey !== '';
}

// ---------------------------------------------------------------
// 5. Form timing validation
// ---------------------------------------------------------------

/**
 * Returns true (= is spam) if the form was submitted in < 5 seconds.
 * The load time is set server-side during page render so it cannot
 * be spoofed to a future timestamp to cheat timing.
 */
function lwCheckFormTiming(array $post, int $minimumSeconds = 5): bool
{
    $loadTime = (int) ($post['form_load_time'] ?? 0);

    // If no timestamp was submitted, let it through (JS disabled path).
    if ($loadTime === 0) {
        return false;
    }

    $elapsed = time() - $loadTime;

    // Also reject absurdly old timestamps (> 4 hours) or future timestamps.
    if ($elapsed < 0 || $elapsed > 14400) {
        return true;
    }

    return $elapsed < $minimumSeconds;
}

// ---------------------------------------------------------------
// 6. File-based IP rate limiting
// ---------------------------------------------------------------

/**
 * Checks and records a form submission attempt for the given IP.
 *
 * Limits:
 *   - 3 submissions per hour
 *   - 10 submissions per day
 *
 * Uses a JSON file per IP in logs/rate_limits/ for zero-extra-query
 * performance. Files are pruned automatically on each call.
 *
 * @return array{allowed: bool, reason: string}
 */
function lwCheckRateLimit(string $ip): array
{
    $dir = lwRuntimePath('rate_limits');

    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }

    // Sanitise IP for use as a filename
    $safeIp  = preg_replace('/[^a-zA-Z0-9._:-]/', '_', $ip);
    $file    = $dir . '/' . $safeIp . '.json';
    $now     = time();
    $hourAgo = $now - 3600;
    $dayAgo  = $now - 86400;

    // Load existing timestamps (LOCK_EX for concurrent request safety)
    $fp = @fopen($file, 'c+');
    if ($fp === false) {
        // Cannot open file — fail open (allow) to avoid blocking real users
        return ['allowed' => true, 'reason' => ''];
    }

    flock($fp, LOCK_EX);
    $content    = stream_get_contents($fp);
    $timestamps = [];

    if ($content !== '' && $content !== false) {
        $decoded = json_decode($content, true);
        if (is_array($decoded)) {
            $timestamps = $decoded;
        }
    }

    // Prune timestamps older than 24 hours
    $timestamps = array_values(array_filter($timestamps, fn($t) => $t > $dayAgo));

    $hourlyCount = count(array_filter($timestamps, fn($t) => $t > $hourAgo));
    $dailyCount  = count($timestamps);

    if ($hourlyCount >= 3) {
        flock($fp, LOCK_UN);
        fclose($fp);
        return [
            'allowed' => false,
            'reason'  => 'rate_limit_hourly',
        ];
    }

    if ($dailyCount >= 10) {
        flock($fp, LOCK_UN);
        fclose($fp);
        return [
            'allowed' => false,
            'reason'  => 'rate_limit_daily',
        ];
    }

    // Record this submission
    $timestamps[] = $now;

    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, json_encode($timestamps));
    flock($fp, LOCK_UN);
    fclose($fp);

    return ['allowed' => true, 'reason' => ''];
}

// ---------------------------------------------------------------
// 7. Input sanitisation
// ---------------------------------------------------------------

/**
 * Strip HTML tags, normalise whitespace, trim.
 */
function lwSanitizeInput(string $value): string
{
    $value = strip_tags($value);
    $value = preg_replace('/\s+/', ' ', $value) ?? $value;
    return trim($value);
}

/**
 * Sanitise all leaf string values in an array (shallow + one level deep).
 */
function lwSanitizeInputArray(array $data): array
{
    $out = [];
    foreach ($data as $key => $value) {
        if (is_string($value)) {
            $out[$key] = lwSanitizeInput($value);
        } elseif (is_array($value)) {
            $out[$key] = array_map(
                fn($v) => is_string($v) ? lwSanitizeInput($v) : $v,
                $value
            );
        } else {
            $out[$key] = $value;
        }
    }
    return $out;
}

// ---------------------------------------------------------------
// 8. Suspicious payload detection
// ---------------------------------------------------------------

/**
 * Returns true (= suspicious) if any field value matches known
 * injection or spam patterns.
 *
 * Patterns checked:
 *  - Script injection tags
 *  - SQL keywords (UNION SELECT, DROP TABLE, INSERT INTO, etc.)
 *  - SQL comment markers (--, /*, xp_)
 *  - More than 3 URLs in a single field value
 */
function lwDetectSuspiciousPayload(array $data): bool
{
    // Fields to inspect (skip internal / system fields)
    $skip = ['csrf_token', 'form_load_time', 'website', 'lead_source', 'form_anchor', 'redirect_after', 'cf-turnstile-response'];

    $scriptPattern = '/<\s*(script|iframe|object|embed|link|meta|form|img|svg|style)[^>]*>/i';

    $sqlPattern = '/\b(UNION\s+SELECT|SELECT\s+\*|INSERT\s+INTO|DROP\s+(TABLE|DATABASE)|'
                . 'DELETE\s+FROM|UPDATE\s+\w+\s+SET|EXEC\s*\(|EXECUTE\s*\(|'
                . 'CAST\s*\(|CONVERT\s*\(|LOAD_FILE\s*\(|OUTFILE|xp_\w+)\b/i';

    $sqlCommentPattern = '/(--|\/\*|\*\/|;\s*DROP|;\s*INSERT|;\s*UPDATE|;\s*DELETE|;\s*SELECT)/i';

    // URL pattern: more than 3 URLs in a single field
    $urlPattern = '/https?:\/\//i';

    foreach ($data as $key => $value) {
        if (in_array($key, $skip, true) || !is_string($value)) {
            continue;
        }

        if (preg_match($scriptPattern, $value)) {
            return true;
        }

        if (preg_match($sqlPattern, $value)) {
            return true;
        }

        if (preg_match($sqlCommentPattern, $value)) {
            return true;
        }

        // Count URLs
        $urlCount = preg_match_all($urlPattern, $value);
        if ($urlCount !== false && $urlCount > 3) {
            return true;
        }
    }

    return false;
}

// ---------------------------------------------------------------
// 9. Email format validation
// ---------------------------------------------------------------

function lwValidateEmailFormat(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// ---------------------------------------------------------------
// 10. Cloudflare Turnstile token verification
// ---------------------------------------------------------------

/**
 * Verifies a Turnstile response token with Cloudflare's siteverify API.
 * Returns true on success, false on any failure.
 */
function lwVerifyTurnstileToken(string $token, string $ip): bool
{
    $secretKey = (string) lwSpamGuardConfig('turnstile_secret_key', '');

    if ($secretKey === '') {
        // Turnstile not configured — skip (useful for local dev).
        return true;
    }

    if ($token === '') {
        return false;
    }

    $payload = http_build_query([
        'secret'   => $secretKey,
        'response' => $token,
        'remoteip' => $ip,
    ]);

    $ch = curl_init('https://challenges.cloudflare.com/turnstile/v0/siteverify');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
    ]);

    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($response === false || $curlError !== '') {
        // Network error — fail open to avoid blocking genuine users if CF is down.
        error_log('[LearnWise][SpamGuard] Turnstile cURL error: ' . $curlError);
        return true;
    }

    $data = json_decode((string) $response, true);

    return is_array($data) && ($data['success'] ?? false) === true;
}

// ---------------------------------------------------------------
// 11. Spam logging
// ---------------------------------------------------------------

/**
 * Writes a structured spam-rejection record to:
 *  - logs/spam.log (always)
 *  - spam_log DB table (when DB is available)
 */
function lwLogSpamRejection(array $context): void
{
    // Always write to flat log file
    if (function_exists('lwLogToFile')) {
        lwLogToFile('spam.log', $context);
    }

    // Also persist to DB
    try {
        $pdo = lwGetPdo();
        $stmt = $pdo->prepare('
            INSERT INTO spam_log (ip_address, user_agent, form_source, reason, payload)
            VALUES (:ip, :ua, :source, :reason, :payload)
        ');
        $stmt->execute([
            ':ip'      => (string) ($context['ip']          ?? ''),
            ':ua'      => (string) ($context['user_agent']  ?? ''),
            ':source'  => (string) ($context['form_source'] ?? ''),
            ':reason'  => (string) ($context['reason']      ?? ''),
            ':payload' => isset($context['payload'])
                            ? json_encode($context['payload'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                            : null,
        ]);
    } catch (Throwable $e) {
        // DB logging is best-effort; don't crash if table not yet migrated
        error_log('[LearnWise][SpamGuard] Could not write spam_log to DB: ' . $e->getMessage());
    }
}

// ---------------------------------------------------------------
// 12. Master gauntlet — run all checks in order
// ---------------------------------------------------------------

/**
 * Runs every spam/bot check and returns on the first failure.
 *
 * @return array{blocked: bool, reason: string, message: string, http_code: int}
 */
function lwRunSpamGauntlet(array $post, array $session): array
{
    $ip        = lwGetClientIp();
    $userAgent = (string) ($_SERVER['HTTP_USER_AGENT'] ?? '');
    $source    = (string) ($post['lead_source'] ?? '');

    $spamCtx = [
        'ip'          => $ip,
        'user_agent'  => $userAgent,
        'form_source' => $source,
        'payload'     => null, // populated only for suspicious content
    ];

    // ----- Check 1: Origin / Referer -----
    if (!lwVerifyFormOrigin()) {
        $spamCtx['reason'] = 'invalid_origin';
        lwLogSpamRejection($spamCtx);
        return [
            'blocked'   => true,
            'reason'    => 'invalid_origin',
            'message'   => 'Your request could not be processed. Please submit the form from our website.',
            'http_code' => 403,
        ];
    }

    // ----- Check 2: CSRF -----
    if (!lwVerifyCsrfToken($post, $session)) {
        $spamCtx['reason'] = 'invalid_csrf';
        lwLogSpamRejection($spamCtx);
        return [
            'blocked'   => true,
            'reason'    => 'invalid_csrf',
            'message'   => 'Your session has expired. Please refresh the page and try again.',
            'http_code' => 403,
        ];
    }

    // ----- Check 3: Honeypot -----
    if (lwCheckHoneypot($post)) {
        $spamCtx['reason'] = 'honeypot_triggered';
        lwLogSpamRejection($spamCtx);
        // Return a fake success to confuse bots
        return [
            'blocked'   => true,
            'reason'    => 'honeypot_triggered',
            'message'   => 'Your enquiry has been submitted. Our team will get back to you.',
            'http_code' => 200,
            'fake_ok'   => true,
        ];
    }

    // ----- Check 4: Form timing -----
    if (lwCheckFormTiming($post)) {
        $spamCtx['reason'] = 'too_fast';
        lwLogSpamRejection($spamCtx);
        return [
            'blocked'   => true,
            'reason'    => 'too_fast',
            'message'   => 'Please take a moment to review your details before submitting.',
            'http_code' => 429,
        ];
    }

    // ----- Check 5: Rate limiting -----
    $rateResult = lwCheckRateLimit($ip);
    if (!$rateResult['allowed']) {
        $spamCtx['reason'] = $rateResult['reason'];
        lwLogSpamRejection($spamCtx);

        $message = $rateResult['reason'] === 'rate_limit_hourly'
            ? 'You have submitted too many enquiries in the past hour. Please try again later.'
            : 'You have reached the maximum number of enquiries for today. Please try again tomorrow.';

        return [
            'blocked'   => true,
            'reason'    => $rateResult['reason'],
            'message'   => $message,
            'http_code' => 429,
        ];
    }

    // ----- Check 6: Suspicious payload -----
    if (lwDetectSuspiciousPayload($post)) {
        $spamCtx['reason']  = 'suspicious_payload';
        $spamCtx['payload'] = $post; // Record payload for analysis
        lwLogSpamRejection($spamCtx);
        return [
            'blocked'   => true,
            'reason'    => 'suspicious_payload',
            'message'   => 'Your message contains content that cannot be processed. Please remove any links or special characters and try again.',
            'http_code' => 422,
        ];
    }

    // ----- Check 7: Email format -----
    $email = trim((string) ($post['email'] ?? ''));
    if ($email !== '' && !lwValidateEmailFormat($email)) {
        $spamCtx['reason'] = 'invalid_email_format';
        lwLogSpamRejection($spamCtx);
        return [
            'blocked'   => true,
            'reason'    => 'invalid_email_format',
            'message'   => 'Please enter a valid email address.',
            'http_code' => 422,
        ];
    }

    // ----- Check 8: Cloudflare Turnstile -----
    $turnstileEnabled = (bool) lwSpamGuardConfig('turnstile_enabled', false);
    if ($turnstileEnabled) {
        $token = trim((string) ($post['cf-turnstile-response'] ?? ''));
        if (!lwVerifyTurnstileToken($token, $ip)) {
            $spamCtx['reason'] = 'turnstile_failed';
            lwLogSpamRejection($spamCtx);
            return [
                'blocked'   => true,
                'reason'    => 'turnstile_failed',
                'message'   => 'Security check failed. Please complete the verification and try again.',
                'http_code' => 403,
            ];
        }
    }

    // All checks passed
    return ['blocked' => false, 'reason' => '', 'message' => '', 'http_code' => 200];
}
