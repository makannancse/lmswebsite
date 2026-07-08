<?php
require_once __DIR__ . '/../includes/site.php';

$pageTitle = $pageTitle ?? 'Admin Dashboard | LearnWise';
$adminSiteName = getSetting('site_name', 'LearnWise');
$adminLogo = getDisplayLogo();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { background: linear-gradient(180deg, #eef4ff 0%, #f8fbff 100%); }
        .admin-sidebar { min-height: 100vh; background: #fff; border-right: 1px solid #e7ebf4; }
        .admin-sidebar .nav-link { color: #556374; font-weight: 600; }
        .admin-sidebar .nav-link.active, .admin-sidebar .nav-link:hover { color: #1e73be; }
        .admin-topbar { background: #fff; border-bottom: 1px solid #e7ebf4; }
        .admin-card { border-radius: 24px; box-shadow: 0 18px 40px rgba(19, 44, 77, 0.08); border: none; }
        .admin-sidebar-logo { width: min(190px, 100%); max-height: 68px; object-fit: contain; }
        .drag-handle { cursor: grab; }
        .table td, .table th { vertical-align: middle; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-3 admin-sidebar p-4">
            <div class="mb-5">
                <a href="dashboard.php" class="d-inline-flex align-items-center text-decoration-none mb-4" aria-label="<?= htmlspecialchars($adminSiteName) ?> CMS dashboard">
                    <?php if ($adminLogo !== ''): ?>
                        <img src="../<?= htmlspecialchars($adminLogo) ?>" alt="<?= htmlspecialchars($adminSiteName) ?> logo" class="admin-sidebar-logo">
                    <?php else: ?>
                        <span class="bg-primary text-white rounded-3 d-inline-flex align-items-center justify-content-center fw-bold" style="width:54px;height:54px;">LW</span>
                    <?php endif; ?>
                </a>
            </div>
            <?php include __DIR__ . '/sidebar.php'; ?>
        </div>
        <div class="col-lg-9 py-4 px-4">
            <div class="d-flex justify-content-between align-items-center admin-topbar mb-4 px-3 py-3 rounded-4 shadow-sm">
                <div>
                    <h4 class="mb-0"><?= htmlspecialchars($pageTitle) ?></h4>
                    <small class="text-muted">Manage your LearnWise website content.</small>
                </div>
                <a href="login.php?logout=1" class="btn btn-outline-secondary">Logout</a>
            </div>
