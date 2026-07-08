<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/site.php';

requireAdminLogin();

$homePage = getPage('home', false);
$pageId = (int) ($homePage['id'] ?? 0);

header('Location: sections.php' . ($pageId > 0 ? '?page_id=' . $pageId : ''));
exit;
