<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/logger.php';

lwBootstrapApplication();

require_once __DIR__ . '/../includes/db.php';

function isAdminLoggedIn(): bool
{
    return !empty($_SESSION['admin_logged_in']) && !empty($_SESSION['admin_email']);
}

function requireAdminLogin(): void
{
    if (isAdminLoggedIn()) {
        return;
    }

    lwLogAdminLogin([
        'event' => 'auth_required_redirect',
        'session_id' => session_id(),
        'session_keys' => array_keys($_SESSION ?? []),
        'script' => (string) ($_SERVER['SCRIPT_NAME'] ?? ''),
    ]);

    lwRedirect(lwAdminUrl('login.php?session_expired=1'));
}

function adminLogin(string $email): bool
{
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_email'] = $email;
    $_SESSION['admin_login_at'] = time();

    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }

    lwLogAdminLogin([
        'event' => 'login_session_created',
        'email' => $email,
        'session_id' => session_id(),
        'session_save_path' => ini_get('session.save_path'),
        'cookie_path' => lwGetAppBasePath() === '' ? '/' : lwGetAppBasePath() . '/',
        'cookie_secure' => lwIsHttps(),
        'session_data' => [
            'admin_logged_in' => $_SESSION['admin_logged_in'] ?? null,
            'admin_email' => $_SESSION['admin_email'] ?? null,
        ],
    ]);

    return isAdminLoggedIn();
}

function adminLogout(): void
{
    lwLogAdminLogin([
        'event' => 'logout',
        'email' => $_SESSION['admin_email'] ?? '',
        'session_id' => session_id(),
    ]);

    $_SESSION = [];

    if (session_status() === PHP_SESSION_ACTIVE) {
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool) $params['secure'], (bool) $params['httponly']);
        }
        session_destroy();
    }

    lwRedirect(lwAdminUrl('login.php?logged_out=1'));
}

function getAdminByEmail(PDO $pdo, string $email): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM admins WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    return $admin ?: null;
}

function lwAttemptAdminLogin(PDO $pdo, string $email, string $password): array
{
    $email = strtolower(trim($email));
    $password = (string) $password;

    lwLogAdminLogin([
        'event' => 'login_attempt',
        'method' => (string) ($_SERVER['REQUEST_METHOD'] ?? ''),
        'email' => $email,
        'post_keys' => array_keys($_POST ?? []),
        'session_id_before' => session_id(),
        'app_url' => lwGetAppUrl(),
        'app_base_path' => lwGetAppBasePath(),
    ]);

    if ($email === '' || $password === '') {
        return [
            'success' => false,
            'message' => 'Please enter both email and password.',
            'reason' => 'missing_fields',
        ];
    }

    try {
        $admin = getAdminByEmail($pdo, $email);
    } catch (Throwable $exception) {
        lwLogAdminLogin([
            'event' => 'database_lookup_failed',
            'email' => $email,
            'error' => $exception->getMessage(),
        ]);

        return [
            'success' => false,
            'message' => 'Authentication failed. Database connection error.',
            'reason' => 'database_error',
        ];
    }

    lwLogAdminLogin([
        'event' => 'admin_lookup',
        'email' => $email,
        'user_found' => $admin !== null,
        'user_id' => $admin['id'] ?? null,
    ]);

    if (!$admin) {
        return [
            'success' => false,
            'message' => 'Invalid username or password.',
            'reason' => 'user_not_found',
        ];
    }

    $passwordValid = password_verify($password, (string) ($admin['password'] ?? ''));

    lwLogAdminLogin([
        'event' => 'password_verify',
        'email' => $email,
        'valid' => $passwordValid,
    ]);

    if (!$passwordValid) {
        return [
            'success' => false,
            'message' => 'Invalid username or password.',
            'reason' => 'invalid_password',
        ];
    }

    if (!adminLogin((string) $admin['email'])) {
        lwLogAdminLogin([
            'event' => 'session_create_failed',
            'email' => $email,
        ]);

        return [
            'success' => false,
            'message' => 'Session could not be created. Please contact support.',
            'reason' => 'session_failed',
        ];
    }

    return [
        'success' => true,
        'message' => 'Login successful.',
        'redirect' => lwAdminUrl('dashboard.php'),
    ];
}
