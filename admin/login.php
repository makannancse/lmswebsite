<?php
ob_start();

require_once __DIR__ . '/auth.php';

if (isset($_GET['logout'])) {
    adminLogout();
}

if (isAdminLoggedIn()) {
    lwRedirect(lwAdminUrl('dashboard.php'));
}

$error = '';
$notice = '';

if (!empty($_GET['session_expired'])) {
    $notice = 'Your session expired. Please sign in again.';
}

if (!empty($_GET['logged_out'])) {
    $notice = 'You have been logged out successfully.';
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $result = lwAttemptAdminLogin($pdo, (string) ($_POST['email'] ?? ''), (string) ($_POST['password'] ?? ''));

    lwLogAdminLogin([
        'event' => 'login_result',
        'success' => $result['success'],
        'reason' => $result['reason'] ?? '',
        'redirect' => $result['redirect'] ?? '',
        'session_id_after' => session_id(),
    ]);

    if (!empty($result['success'])) {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        lwRedirect((string) ($result['redirect'] ?? lwAdminUrl('dashboard.php')));
    }

    $error = (string) ($result['message'] ?? 'Authentication failed.');
}

$postedEmail = htmlspecialchars((string) ($_POST['email'] ?? ''), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | LearnWise</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Open Sans', sans-serif; background: #f5f7fb; }
        .login-card { border-radius: 20px; box-shadow: 0 18px 50px rgba(0,0,0,0.08); border: none; }
        .brand-mark { width: 48px; height: 48px; border-radius: 16px; background: #1E73BE; color: #fff; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; }
    </style>
</head>
<body>
<div class="d-flex align-items-center justify-content-center vh-100 px-3">
    <div class="card login-card shadow-sm w-100" style="max-width: 480px;">
        <div class="card-body p-5">
            <div class="text-center mb-5">
                <div class="brand-mark mb-3 mx-auto">LW</div>
                <h3 class="mb-1">LearnWise Admin</h3>
                <p class="text-muted">Sign in to manage the website content.</p>
            </div>

            <?php if ($notice !== ''): ?>
                <div class="alert alert-info"><?= htmlspecialchars($notice) ?></div>
            <?php endif; ?>

            <?php if ($error !== ''): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="post" action="<?= htmlspecialchars(lwAdminUrl('login.php')) ?>" autocomplete="off">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?= $postedEmail ?>" required autofocus>
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    <?php if ($error !== ''): ?>
    Swal.fire({
        icon: 'error',
        title: 'Login Failed',
        text: <?= json_encode($error, JSON_UNESCAPED_UNICODE) ?>,
        confirmButtonColor: '#1E73BE'
    });
    <?php elseif ($notice !== ''): ?>
    Swal.fire({
        icon: 'info',
        title: 'Notice',
        text: <?= json_encode($notice, JSON_UNESCAPED_UNICODE) ?>,
        confirmButtonColor: '#1E73BE'
    });
    <?php endif; ?>
});
</script>
</body>
</html>
