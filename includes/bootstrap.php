<?php

function lwRuntimePath(string $path = ''): string
{
    $basePath = dirname(__DIR__) . '/logs';
    return $path === '' ? $basePath : $basePath . '/' . ltrim($path, '/');
}

function lwEnsureRuntimeDirectories(): void
{
    foreach ([lwRuntimePath(), lwRuntimePath('sessions')] as $directory) {
        if (!is_dir($directory)) {
            @mkdir($directory, 0755, true);
        }
    }
}

function lwFriendlyErrorText(): string
{
    return 'Something went wrong. Please try again.';
}

function lwLogRuntimeMessage(string $message): void
{
    error_log('[LearnWise] ' . $message);
}

function lwReportException(Throwable $exception, array $context = []): void
{
    $contextString = $context === [] ? '' : ' | context=' . json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    lwLogRuntimeMessage(sprintf(
        '%s in %s:%d | %s%s',
        get_class($exception),
        $exception->getFile(),
        $exception->getLine(),
        $exception->getMessage(),
        $contextString
    ));
}

function lwShouldReturnJson(): bool
{
    $accept = strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? ''));
    $requestedWith = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? ''));

    return $requestedWith === 'xmlhttprequest' || str_contains($accept, 'application/json');
}

function lwRenderFriendlyErrorPage(string $message = '', int $statusCode = 500): void
{
    $safeMessage = $message !== '' ? $message : lwFriendlyErrorText();

    if (!headers_sent()) {
        http_response_code($statusCode);
    }

    if (lwShouldReturnJson()) {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }

        echo json_encode([
            'success' => false,
            'message' => $safeMessage,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return;
    }

    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>LearnWise</title><style>body{margin:0;font-family:Open Sans,Arial,sans-serif;background:#f5f7fb;color:#1f2630;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:24px}.error-shell{max-width:560px;width:100%;background:#fff;border-radius:18px;box-shadow:0 20px 60px rgba(18,43,70,.12);padding:40px;text-align:center}.error-shell h1{margin:0 0 12px;font-family:Poppins,Arial,sans-serif;font-size:2rem;color:#1e73be}.error-shell p{margin:0;color:#5c6b7a;line-height:1.7}.error-shell a{display:inline-flex;margin-top:24px;padding:12px 20px;border-radius:999px;background:#1e73be;color:#fff;text-decoration:none;font-weight:700}</style></head><body><div class="error-shell"><h1>LearnWise</h1><p>' . htmlspecialchars($safeMessage, ENT_QUOTES, 'UTF-8') . '</p><a href="javascript:history.back()">Go Back</a></div></body></html>';
}

function lwHandlePhpError(int $severity, string $message, string $file, int $line): bool
{
    if (!(error_reporting() & $severity)) {
        return false;
    }

    lwLogRuntimeMessage(sprintf('PHP error [%d] %s in %s:%d', $severity, $message, $file, $line));
    return true;
}

function lwHandleUncaughtException(Throwable $exception): void
{
    lwReportException($exception);

    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    lwRenderFriendlyErrorPage();
    exit;
}

function lwHandleShutdown(): void
{
    $error = error_get_last();
    if ($error === null) {
        return;
    }

    $fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];
    if (!in_array((int) $error['type'], $fatalTypes, true)) {
        return;
    }

    lwLogRuntimeMessage(sprintf(
        'Fatal error [%d] %s in %s:%d',
        (int) $error['type'],
        (string) ($error['message'] ?? ''),
        (string) ($error['file'] ?? ''),
        (int) ($error['line'] ?? 0)
    ));

    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    lwRenderFriendlyErrorPage();
}

function lwAbortRequest(string $message = '', int $statusCode = 500): void
{
    lwRenderFriendlyErrorPage($message, $statusCode);
    exit;
}

function lwBootstrapApplication(): void
{
    static $bootstrapped = false;
    if ($bootstrapped) {
        return;
    }

    $bootstrapped = true;
    lwEnsureRuntimeDirectories();

    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', lwRuntimePath('error.log'));
    if (extension_loaded('xdebug')) {
        @ini_set('xdebug.log', lwRuntimePath('xdebug.log'));
        @ini_set('xdebug.log_level', '0');
    }
    error_reporting(E_ALL);

    set_error_handler('lwHandlePhpError');
    set_exception_handler('lwHandleUncaughtException');
    register_shutdown_function('lwHandleShutdown');

    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.save_path', lwRuntimePath('sessions'));
        @session_start();
    }

    if (!isset($_SESSION) || !is_array($_SESSION)) {
        $_SESSION = [];
    }
}
