<?php

function lwGetPdo(): PDO
{
    global $pdo;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    if (isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO) {
        $pdo = $GLOBALS['pdo'];
        return $pdo;
    }

    require_once __DIR__ . '/db.php';

    if (!($pdo instanceof PDO)) {
        throw new RuntimeException('Database connection is not available.');
    }

    return $pdo;
}
