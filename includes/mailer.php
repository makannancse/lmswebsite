<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\Exception as MailerException;
use PHPMailer\PHPMailer\PHPMailer;

function lwBuildLeadEmailBody(array $lead): string
{
    $lines = [
        'New enquiry submitted from website.',
        '',
        'Name: ' . ($lead['name'] ?? ''),
        'Parent Name: ' . ($lead['parent_name'] ?? ''),
        'Student Name: ' . ($lead['student_name'] ?? ''),
        'Email: ' . ($lead['email'] ?? ''),
        'Phone: ' . ($lead['phone'] ?? ''),
        'Course: ' . ($lead['course'] ?? ''),
        'Message: ' . ($lead['message'] ?? ''),
        'Source: ' . ($lead['source'] ?? ''),
        'Submitted On: ' . ($lead['submitted_at'] ?? date('Y-m-d H:i:s')),
    ];

    return implode("\n", $lines);
}

function lwSendLeadNotification(array $lead): array
{
    require_once __DIR__ . '/site.php';

    $adminEmail = trim(getSetting('admin_notification_email', getSetting('site_email', '')));
    if ($adminEmail === '' || !filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
        return [
            'success' => false,
            'message' => 'Admin notification email is not configured.',
        ];
    }

    $fromEmail = trim(getSetting('smtp_from_email', 'noreply@learnwise.com'));
    $fromName = trim(getSetting('smtp_from_name', getSetting('site_name', 'LearnWise')));
    $smtpHost = trim(getSetting('smtp_host', ''));
    $smtpPort = (int) getSetting('smtp_port', '587');
    $smtpUsername = trim(getSetting('smtp_username', ''));
    $smtpPassword = getSetting('smtp_password', '');
    $smtpEncryption = strtolower(trim(getSetting('smtp_encryption', 'tls')));

    try {
        $mail = new PHPMailer(true);
        $mail->CharSet = 'UTF-8';
        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($adminEmail);
        $mail->Subject = 'New Demo Request Received';
        $mail->Body = lwBuildLeadEmailBody($lead);

        if ($smtpHost !== '') {
            $mail->isSMTP();
            $mail->Host = $smtpHost;
            $mail->Port = $smtpPort > 0 ? $smtpPort : 587;
            $mail->SMTPAuth = $smtpUsername !== '';
            if ($smtpUsername !== '') {
                $mail->Username = $smtpUsername;
                $mail->Password = $smtpPassword;
            }
            if (in_array($smtpEncryption, ['tls', 'ssl'], true)) {
                $mail->SMTPSecure = $smtpEncryption;
            }
        } else {
            $mail->isMail();
        }

        $mail->send();

        return [
            'success' => true,
            'message' => 'Notification email sent.',
        ];
    } catch (MailerException $exception) {
        return [
            'success' => false,
            'message' => $exception->getMessage(),
        ];
    }
}
