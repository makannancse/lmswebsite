<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
lwBootstrapApplication();
require_once __DIR__ . '/../includes/form-handler.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(405);
    }
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed.',
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

if (empty($_POST['lead_source'])) {
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(422);
    }
    echo json_encode([
        'success' => false,
        'message' => 'Invalid form submission.',
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

$formAnchor = preg_replace('/[^a-z0-9_-]+/i', '-', (string) ($_POST['form_anchor'] ?? 'lead-form'));
lwProcessLeadSubmission($_POST, $formAnchor, true);
