<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\Exception as MailerException;
use PHPMailer\PHPMailer\PHPMailer;

function lwGetEmailBranding(): array
{
    require_once __DIR__ . '/site.php';
    $siteName = getSetting('site_name', 'LearnWise');
    $siteEmail = getSetting('site_email', 'hello@learnwise.com');
    $sitePhone = getSetting('site_phone', '');
    $logoUrl = getSetting('site_logo', '');
    if ($logoUrl !== '' && !preg_match('/^https?:\/\//i', $logoUrl)) {
        $logoUrl = lwUrl($logoUrl);
    }
    return [
        'site_name'  => $siteName,
        'site_email' => $siteEmail,
        'site_phone' => $sitePhone,
        'logo_url'   => $logoUrl,
    ];
}

function lwBuildEmailTemplate(string $title, string $contentHtml, array $branding = []): string
{
    if ($branding === []) {
        $branding = lwGetEmailBranding();
    }
    $siteName = htmlspecialchars($branding['site_name']);
    $headerLogoHtml = !empty($branding['logo_url'])
        ? '<img src="' . htmlspecialchars($branding['logo_url']) . '" alt="' . $siteName . '" style="max-height:44px;">'
        : '<span style="font-size:24px;font-weight:700;color:#fff;font-family:Poppins,Arial,sans-serif;">' . $siteName . '</span>';

    $phoneHtml = !empty($branding['site_phone'])
        ? '<p style="margin:4px 0 0;font-size:13px;color:#8c99a9;">Phone: ' . htmlspecialchars($branding['site_phone']) . '</p>'
        : '';

    return '<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>' . htmlspecialchars($title) . '</title></head>
<body style="margin:0;padding:0;font-family:\'Open Sans\',Arial,Helvetica,sans-serif;background:#f3f6fb;color:#1f2630;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3f6fb;padding:32px 16px;">
<tr><td align="center">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 8px 32px rgba(19,44,77,0.08);">
<tr><td style="background:linear-gradient(135deg,#1e73be 0%,#155a96 100%);padding:28px 32px;text-align:center;">
' . $headerLogoHtml . '
</td></tr>
<tr><td style="padding:32px;">
<h2 style="margin:0 0 20px;font-size:22px;color:#1e73be;font-family:Poppins,Arial,sans-serif;">' . htmlspecialchars($title) . '</h2>
' . $contentHtml . '
</td></tr>
<tr><td style="background:#f8fafd;padding:20px 32px;text-align:center;border-top:1px solid #eef2f7;">
<p style="margin:0;font-size:13px;color:#8c99a9;">&copy; ' . date('Y') . ' ' . $siteName . '. All rights reserved.</p>
' . $phoneHtml . '
</td></tr>
</table>
</td></tr></table>
</body></html>';
}

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

function lwBuildAdminEmailHtml(array $lead): string
{
    $fields = [
        'Name'           => $lead['name'] ?? '',
        'Parent Name'    => $lead['parent_name'] ?? '',
        'Student Name'   => $lead['student_name'] ?? '',
        'Email'          => $lead['email'] ?? '',
        'Phone'          => $lead['phone'] ?? '',
        'Course Interest' => $lead['course'] ?? '',
        'Message'        => $lead['message'] ?? '',
        'Source'         => $lead['source'] ?? '',
        'Submitted'      => $lead['submitted_at'] ?? date('Y-m-d H:i:s'),
    ];

    $rows = '';
    foreach ($fields as $label => $value) {
        if (trim((string) $value) === '') {
            continue;
        }
        $rows .= '<tr>'
            . '<td style="padding:10px 14px;font-weight:600;color:#3d4f65;border-bottom:1px solid #f0f3f7;width:140px;vertical-align:top;">' . htmlspecialchars($label) . '</td>'
            . '<td style="padding:10px 14px;color:#1f2630;border-bottom:1px solid #f0f3f7;">' . htmlspecialchars($value) . '</td>'
            . '</tr>';
    }

    return '<p style="margin:0 0 16px;line-height:1.7;color:#4a5568;">A new enquiry has been received from the website. Details below:</p>'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #eef2f7;border-radius:10px;overflow:hidden;margin-bottom:20px;">'
        . $rows
        . '</table>'
        . '<p style="margin:0;font-size:14px;color:#8c99a9;">You can manage all leads from the Admin Dashboard.</p>';
}

function lwBuildUserEmailHtml(array $lead): string
{
    $branding = lwGetEmailBranding();
    $name = htmlspecialchars($lead['name'] ?? 'there');
    $siteName = htmlspecialchars($branding['site_name']);

    $courseHtml = !empty($lead['course'])
        ? '<p style="margin:0 0 4px;color:#4a5568;"><strong>Course Interest:</strong> ' . htmlspecialchars($lead['course']) . '</p>'
        : '';
    $msgHtml = !empty($lead['message'])
        ? '<p style="margin:0;color:#4a5568;"><strong>Message:</strong> ' . htmlspecialchars($lead['message']) . '</p>'
        : '';
    $phoneHtml = !empty($branding['site_phone'])
        ? ' or call us at <strong>' . htmlspecialchars($branding['site_phone']) . '</strong>'
        : '';

    return '<p style="margin:0 0 16px;line-height:1.7;color:#4a5568;">Hi ' . $name . ',</p>'
        . '<p style="margin:0 0 16px;line-height:1.7;color:#4a5568;">Thank you for reaching out to <strong>' . $siteName . '</strong>! We have received your enquiry and our team will get back to you shortly.</p>'
        . '<div style="background:#f0f7ff;border-radius:12px;padding:20px;margin:0 0 20px;border-left:4px solid #1e73be;">'
        . '<p style="margin:0 0 8px;font-weight:600;color:#1e73be;">Your Enquiry Summary</p>'
        . $courseHtml . $msgHtml
        . '</div>'
        . '<p style="margin:0 0 8px;line-height:1.7;color:#4a5568;">If you have any immediate questions, feel free to reply to this email' . $phoneHtml . '.</p>'
        . '<p style="margin:16px 0 0;line-height:1.7;color:#4a5568;">Warm regards,<br><strong>The ' . $siteName . ' Team</strong></p>';
}

function lwCreateMailer(): PHPMailer
{
    require_once __DIR__ . '/site.php';

    $fromEmail     = trim(getSetting('smtp_from_email', 'noreply@learnwise.com'));
    $fromName      = trim(getSetting('smtp_from_name', getSetting('site_name', 'LearnWise')));
    $smtpHost      = trim(getSetting('smtp_host', ''));
    $smtpPort      = (int) getSetting('smtp_port', '587');
    $smtpUsername  = trim(getSetting('smtp_username', ''));
    $smtpPassword  = getSetting('smtp_password', '');
    $smtpEncryption = strtolower(trim(getSetting('smtp_encryption', 'tls')));

    $mail = new PHPMailer(true);
    $mail->CharSet = 'UTF-8';
    $mail->setFrom($fromEmail, $fromName);

    if ($smtpHost !== '') {
        $mail->isSMTP();
        $mail->Host     = $smtpHost;
        $mail->Port     = $smtpPort > 0 ? $smtpPort : 587;
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

    return $mail;
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

    $branding    = lwGetEmailBranding();
    $adminResult = ['success' => false, 'message' => ''];
    $userResult  = ['success' => false, 'message' => ''];

    // --- Admin notification email ---
    try {
        $mail = lwCreateMailer();
        $mail->addAddress($adminEmail);
        $mail->isHTML(true);
        $mail->Subject = 'New Demo Request Received — ' . ($lead['name'] ?? 'Website Enquiry');
        $mail->Body    = lwBuildEmailTemplate('New Enquiry Received', lwBuildAdminEmailHtml($lead), $branding);
        $mail->AltBody = lwBuildLeadEmailBody($lead);
        $mail->send();
        $adminResult = ['success' => true, 'message' => 'Admin notification sent.'];
    } catch (MailerException $exception) {
        $adminResult = ['success' => false, 'message' => 'Admin email: ' . $exception->getMessage()];
    }

    // --- User confirmation email ---
    $userEmail = trim((string) ($lead['email'] ?? ''));
    if ($userEmail !== '' && filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
        try {
            $mail = lwCreateMailer();
            $mail->addAddress($userEmail, $lead['name'] ?? '');
            $mail->isHTML(true);
            $mail->Subject = 'Thank you for contacting ' . $branding['site_name'] . '!';
            $mail->Body    = lwBuildEmailTemplate('We Received Your Enquiry', lwBuildUserEmailHtml($lead), $branding);
            $mail->AltBody = 'Thank you for contacting ' . $branding['site_name'] . '. We received your enquiry and will get back to you shortly.';
            $mail->send();
            $userResult = ['success' => true, 'message' => 'User confirmation sent.'];
        } catch (MailerException $exception) {
            $userResult = ['success' => false, 'message' => 'User email: ' . $exception->getMessage()];
        }
    }

    return [
        'success'    => $adminResult['success'],
        'message'    => $adminResult['message'] . ($userResult['message'] ? ' | ' . $userResult['message'] : ''),
        'admin_sent' => $adminResult['success'],
        'user_sent'  => $userResult['success'],
    ];
}
