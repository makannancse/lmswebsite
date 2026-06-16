<?php

function lwLogToFile(string $fileName, array $entry): void
{
    $logDir = lwRuntimePath();
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }

    $entry['logged_at'] = date('c');
    $line = json_encode($entry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
    @file_put_contents($logDir . '/' . ltrim($fileName, '/'), $line, FILE_APPEND | LOCK_EX);
}

function lwLogEnrollment(array $entry): void
{
    lwLogToFile('enrollment.log', $entry);
}

function lwLogCmsSettings(array $entry): void
{
    lwLogToFile('cms_settings.log', $entry);
}

function lwLogEmail(array $entry): void
{
    lwLogToFile('email.log', $entry);
}

function lwLogAdminLogin(array $entry): void
{
    lwLogToFile('admin_login_debug.log', $entry);
}
