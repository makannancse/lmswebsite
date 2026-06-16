<?php

function lwIsHttps(): bool
{
    if (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') {
        return true;
    }

    if ((int) ($_SERVER['SERVER_PORT'] ?? 0) === 443) {
        return true;
    }

    $forwardedProto = strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));
    return $forwardedProto === 'https';
}

function lwGetAppBasePath(): string
{
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }

    if (defined('LW_APP_BASE_PATH')) {
        $cached = (string) LW_APP_BASE_PATH;
        return $cached;
    }

    $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    $dir = dirname($script);

    foreach (['/admin', '/api', '/includes', '/sections'] as $suffix) {
        if (str_ends_with($dir, $suffix)) {
            $dir = substr($dir, 0, -strlen($suffix));
            break;
        }
    }

    $dir = rtrim($dir, '/');
    if ($dir === '' || $dir === '/' || $dir === '.') {
        $cached = '';
        return $cached;
    }

    $cached = $dir;
    return $cached;
}

function lwGetAppUrl(): string
{
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }

    if (defined('LW_APP_URL')) {
        $cached = rtrim((string) LW_APP_URL, '/');
        return $cached;
    }

    $scheme = lwIsHttps() ? 'https' : 'http';
    $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
    $base = lwGetAppBasePath();
    $cached = $scheme . '://' . $host . ($base !== '' ? $base : '');

    return $cached;
}

function lwUrl(string $path = ''): string
{
    $path = ltrim($path, '/');
    $base = lwGetAppUrl();

    return $path === '' ? $base : $base . '/' . $path;
}

function lwAdminUrl(string $path = ''): string
{
    $path = ltrim($path, '/');
    return lwUrl('admin/' . $path);
}

function lwRedirect(string $url, int $statusCode = 302): void
{
    if (!headers_sent()) {
        header('Location: ' . $url, true, $statusCode);
    }
    exit;
}

function lwConfigureSession(): bool
{
    $sessionPath = lwRuntimePath('sessions');
    if (!is_dir($sessionPath)) {
        @mkdir($sessionPath, 0755, true);
    }

    if (!is_dir($sessionPath) || !is_writable($sessionPath)) {
        if (function_exists('lwLogAdminLogin')) {
            lwLogAdminLogin([
                'event' => 'session_path_unwritable',
                'path' => $sessionPath,
                'writable' => is_writable($sessionPath),
            ]);
        }
        return false;
    }

    ini_set('session.save_handler', 'files');
    ini_set('session.save_path', $sessionPath);
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_secure', lwIsHttps() ? '1' : '0');
    ini_set('session.gc_maxlifetime', '7200');

    $cookiePath = lwGetAppBasePath();
    $cookiePath = $cookiePath === '' ? '/' : $cookiePath . '/';

    session_name('LWSESSID');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => $cookiePath,
        'domain' => '',
        'secure' => lwIsHttps(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    return true;
}

function lwStartSession(): bool
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return true;
    }

    lwConfigureSession();
    $started = session_start();

    if (!$started && function_exists('lwLogAdminLogin')) {
        lwLogAdminLogin([
            'event' => 'session_start_failed',
            'session_id' => session_id(),
            'save_path' => ini_get('session.save_path'),
        ]);
    }

    if (!isset($_SESSION) || !is_array($_SESSION)) {
        $_SESSION = [];
    }

    return $started;
}
