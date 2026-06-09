<?php
$pageTitle = $pageTitle ?? getSetting('site_name', 'LearnWise');
$pageDescription = $pageDescription ?? getSetting('site_tagline', 'Premium online learning with expert teachers, flexible schedules, and parent-friendly progress tracking.');
$pageOgImage = $pageOgImage ?? getSiteLogo();
$bodyClass = trim(($bodyClass ?? '') . ' page-' . preg_replace('/[^a-z0-9_-]+/i', '-', (string) ($currentPage ?? 'site')));
$favicon = getSetting('favicon', getSiteLogo());
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$canonicalUrl = $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($_SERVER['SCRIPT_NAME'] ?? '/index.php');
$scriptDir = str_replace('\\', '/', dirname((string) ($_SERVER['SCRIPT_NAME'] ?? '')));
$appBase = ($scriptDir === '/' || $scriptDir === '.' || $scriptDir === '') ? '' : rtrim($scriptDir, '/');
$appUrl = $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($appBase !== '' ? $appBase : '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <meta name="description" content="<?= htmlspecialchars($pageDescription) ?>">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?= htmlspecialchars($canonicalUrl) ?>">
    <meta property="og:title" content="<?= htmlspecialchars($pageTitle) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($pageDescription) ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= htmlspecialchars($canonicalUrl) ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($pageTitle) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($pageDescription) ?>">
    <?php if ($pageOgImage !== ''): ?>
        <meta property="og:image" content="<?= htmlspecialchars($pageOgImage) ?>">
        <meta name="twitter:image" content="<?= htmlspecialchars($pageOgImage) ?>">
    <?php endif; ?>
    <?php if ($favicon !== ''): ?>
        <link rel="icon" href="<?= htmlspecialchars($favicon) ?>" type="image/png">
    <?php endif; ?>
    <meta name="app-url" content="<?= htmlspecialchars($appUrl) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/premium-ui.css">
</head>
<body class="<?= htmlspecialchars($bodyClass) ?>">
