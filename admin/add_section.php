<?php
require_once __DIR__ . '/auth.php';

requireAdminLogin();

header('Location: sections.php');
exit;
