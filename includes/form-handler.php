<?php

require_once __DIR__ . '/pdo.php';
require_once __DIR__ . '/site.php';
require_once __DIR__ . '/logger.php';
require_once __DIR__ . '/mailer.php';

function lwIsAjaxRequest(): bool
{
    return lwShouldReturnJson();
}

function lwRespondLead(array $payload, bool $isAjax, string $redirectAnchor = ''): void
{
    if ($isAjax) {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }

    $_SESSION['flash'] = [
        'type' => !empty($payload['success']) ? 'success' : 'danger',
        'text' => (string) ($payload['message'] ?? ''),
    ];

    $redirect = strtok((string) ($_SERVER['REQUEST_URI'] ?? '/'), '?');
    if ($redirectAnchor !== '') {
        $redirect .= '#' . ltrim($redirectAnchor, '#');
    }
    header('Location: ' . $redirect);
    exit;
}

function lwValidateLeadInput(array $input): array
{
    $errors = [];
    $name = trim((string) ($input['name'] ?? ''));
    $parentName = trim((string) ($input['parent_name'] ?? ''));
    $studentName = trim((string) ($input['student_name'] ?? ''));
    $email = trim((string) ($input['email'] ?? ''));
    $phone = trim((string) ($input['phone'] ?? ''));
    $course = trim((string) ($input['course'] ?? ''));
    $message = trim((string) ($input['message'] ?? ''));

    if ($name === '') {
        $errors[] = 'Please enter your name.';
    }

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if ($phone === '' || !preg_match('/^[0-9+\-\s()]{7,20}$/', $phone)) {
        $errors[] = 'Please enter a valid phone number.';
    }

    if ($course === '' && $message === '') {
        $errors[] = 'Please tell us which course you are interested in or add a short message.';
    }

    return [
        'valid' => $errors === [],
        'errors' => $errors,
        'data' => [
            'name' => $name,
            'parent_name' => $parentName,
            'student_name' => $studentName,
            'email' => $email,
            'phone' => $phone,
            'course' => $course,
            'message' => $message,
            'source' => trim((string) ($input['lead_source'] ?? 'website')),
        ],
    ];
}

function lwProcessLeadSubmission(array $input, string $redirectAnchor = '', bool $forceJson = false): void
{
    $isAjax = $forceJson || lwIsAjaxRequest();
    $validation = lwValidateLeadInput($input);

    lwLogEnrollment([
        'event' => 'submission_received',
        'payload' => $input,
        'validation' => $validation['errors'],
    ]);

    if (!$validation['valid']) {
        lwLogEnrollment([
            'event' => 'validation_failed',
            'errors' => $validation['errors'],
        ]);

        lwRespondLead([
            'success' => false,
            'message' => implode(' ', $validation['errors']),
            'errors' => $validation['errors'],
        ], $isAjax, $redirectAnchor);
    }

    $lead = $validation['data'];

    try {
        $pdo = lwGetPdo();
        $insert = $pdo->prepare('
            INSERT INTO leads (name, parent_name, student_name, email, phone, course, message, source, status)
            VALUES (:name, :parent_name, :student_name, :email, :phone, :course, :message, :source, :status)
        ');
        $insert->execute([
            ':name' => $lead['name'],
            ':parent_name' => $lead['parent_name'] !== '' ? $lead['parent_name'] : null,
            ':student_name' => $lead['student_name'] !== '' ? $lead['student_name'] : null,
            ':email' => $lead['email'],
            ':phone' => $lead['phone'],
            ':course' => $lead['course'] !== '' ? $lead['course'] : null,
            ':message' => $lead['message'] !== '' ? $lead['message'] : null,
            ':source' => $lead['source'],
            ':status' => 'new',
        ]);

        $leadId = (int) $pdo->lastInsertId();
        $lead['submitted_at'] = date('Y-m-d H:i:s');
        $lead['lead_id'] = $leadId;

        lwLogEnrollment([
            'event' => 'database_saved',
            'lead_id' => $leadId,
            'name' => $lead['name'],
            'email' => $lead['email'],
            'phone' => $lead['phone'],
            'course' => $lead['course'],
            'source' => $lead['source'],
        ]);

        $emailResult = lwSendLeadNotification($lead);
        lwLogEmail([
            'event' => 'lead_notification',
            'lead_id' => $leadId,
            'success' => $emailResult['success'],
            'message' => $emailResult['message'],
        ]);

        lwLogEnrollment([
            'event' => 'email_result',
            'lead_id' => $leadId,
            'success' => $emailResult['success'],
            'message' => $emailResult['message'],
        ]);

        $redirectAfter = trim((string) ($input['redirect_after'] ?? ''));
        if ($redirectAfter === '' && stripos((string) ($lead['source'] ?? ''), 'enroll') !== false) {
            $redirectAfter = 'enroll.php';
        }
        if ($redirectAfter === '' && stripos((string) ($lead['source'] ?? ''), 'contact') !== false) {
            $redirectAfter = 'contact.php';
        }

        lwRespondLead([
            'success' => true,
            'message' => 'Your enquiry has been submitted successfully. Our team will contact you shortly.',
            'lead_id' => $leadId,
            'email_sent' => $emailResult['success'],
            'redirect' => $redirectAfter,
        ], $isAjax, $redirectAnchor);
    } catch (Throwable $exception) {
        lwReportException($exception, ['area' => 'lead_submission']);
        lwLogEnrollment([
            'event' => 'database_error',
            'error' => $exception->getMessage(),
            'payload' => $input,
        ]);

        lwRespondLead([
            'success' => false,
            'message' => 'We could not save your enquiry right now. Please try again or contact us directly.',
        ], $isAjax, $redirectAnchor);
    }
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST'
    && !empty($_POST['lead_source'])
    && basename((string) ($_SERVER['SCRIPT_NAME'] ?? '')) !== 'submit-lead.php') {
    $formAnchor = preg_replace('/[^a-z0-9_-]+/i', '-', (string) ($_POST['form_anchor'] ?? 'lead-form'));
    lwProcessLeadSubmission($_POST, $formAnchor, false);
}
