<?php
require_once __DIR__ . '/../includes/bootstrap.php';

lwBootstrapApplication();

require_once __DIR__ . '/../includes/db.php';

function isAdminLoggedIn(): bool
{
    return !empty($_SESSION['admin_logged_in']) && !empty($_SESSION['admin_email']);
}

function requireAdminLogin(): void
{
    if (!isAdminLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function adminLogin(string $email): void
{
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_email'] = $email;
}

function adminLogout(): void
{
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}

function getAdminByEmail(PDO $pdo, string $email): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM admins WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    return $admin ?: null;
}
