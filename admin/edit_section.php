<?php
require_once __DIR__ . '/auth.php';

requireAdminLogin();

$sectionId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$target = 'sections.php';
if ($sectionId > 0) {
    $target .= '?edit=' . $sectionId;
}

header('Location: ' . $target);
exit;
