<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/site.php';
require_once __DIR__ . '/../includes/logger.php';
requireAdminLogin();

$pageTitle = 'Global Settings';

function lwLogSettingChange(string $key, string $oldValue, string $newValue, bool $saved): void
{
    lwLogCmsSettings([
        'event' => 'setting_update',
        'setting' => $key,
        'old_value' => $oldValue,
        'new_value' => $newValue,
        'saved' => $saved,
    ]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settingsBefore = getSettings();
    $textSettings = [
        'site_phone' => trim($_POST['site_phone'] ?? ''),
        'site_email' => trim($_POST['site_email'] ?? ''),
        'whatsapp_number' => trim($_POST['whatsapp_number'] ?? ''),
        'address' => trim($_POST['address'] ?? ''),
        'footer_text' => trim($_POST['footer_text'] ?? ''),
        'site_name' => trim($_POST['site_name'] ?? ''),
        'site_tagline' => trim($_POST['site_tagline'] ?? ''),
        'nav_cta_text' => trim($_POST['nav_cta_text'] ?? ''),
        'nav_cta_link' => trim($_POST['nav_cta_link'] ?? ''),
        'footer_legal_privacy' => trim($_POST['footer_legal_privacy'] ?? 'privacy.php'),
        'footer_legal_terms' => trim($_POST['footer_legal_terms'] ?? 'terms.php'),
        'admin_notification_email' => trim($_POST['admin_notification_email'] ?? ''),
        'smtp_host' => trim($_POST['smtp_host'] ?? ''),
        'smtp_port' => trim($_POST['smtp_port'] ?? '587'),
        'smtp_username' => trim($_POST['smtp_username'] ?? ''),
        'smtp_password' => trim($_POST['smtp_password'] ?? ''),
        'smtp_encryption' => trim($_POST['smtp_encryption'] ?? 'tls'),
        'smtp_from_email' => trim($_POST['smtp_from_email'] ?? ''),
        'smtp_from_name' => trim($_POST['smtp_from_name'] ?? ''),
    ];

    foreach ($textSettings as $key => $value) {
        $oldValue = (string) ($settingsBefore[$key] ?? '');
        $type = in_array($key, ['address', 'footer_text'], true) ? 'textarea' : 'text';
        try {
            lwSaveSetting($pdo, $key, $value, $type);
            lwLogSettingChange($key, $oldValue, $value, true);
        } catch (Throwable $exception) {
            lwLogSettingChange($key, $oldValue, $value, false);
            lwReportException($exception, ['area' => 'cms_settings', 'setting' => $key]);
        }
    }

    if (trim($_POST['smtp_password'] ?? '') === '' && !empty($settingsBefore['smtp_password'])) {
        lwSaveSetting($pdo, 'smtp_password', (string) $settingsBefore['smtp_password'], 'text');
    }

    $socialLinks = [
        ['label' => trim($_POST['social_label_1'] ?? ''), 'url' => trim($_POST['social_url_1'] ?? '')],
        ['label' => trim($_POST['social_label_2'] ?? ''), 'url' => trim($_POST['social_url_2'] ?? '')],
        ['label' => trim($_POST['social_label_3'] ?? ''), 'url' => trim($_POST['social_url_3'] ?? '')],
    ];
    $socialLinks = array_values(array_filter($socialLinks, static fn ($item) => $item['label'] !== '' && $item['url'] !== ''));
    $encodedSocial = json_encode($socialLinks, JSON_UNESCAPED_SLASHES);
    lwSaveSetting($pdo, 'social_links', $encodedSocial, 'json');
    lwLogSettingChange('social_links', (string) ($settingsBefore['social_links'] ?? ''), $encodedSocial, true);

    $flash = [
        'type' => 'success',
        'text' => 'Settings updated successfully.',
    ];

    $logoError = null;
    $logo = cmsUploadFile($_FILES['logo'] ?? [], 'logos', ['png', 'jpg', 'jpeg', 'svg'], 'logo', 2097152, $logoError);
    if ($logo !== null) {
        lwSaveSetting($pdo, 'site_logo', $logo, 'image');
        lwSaveSetting($pdo, 'logo', $logo, 'image');
    } elseif (!empty($_FILES['logo']['name'])) {
        $flash = [
            'type' => 'danger',
            'text' => $logoError ?: 'Upload failed. Please try again.',
        ];
    }

    $faviconError = null;
    $favicon = cmsUploadFile($_FILES['favicon'] ?? [], 'logos', ['png', 'jpg', 'jpeg', 'ico', 'svg'], 'favicon', 1048576, $faviconError);
    if ($favicon !== null) {
        lwSaveSetting($pdo, 'favicon', $favicon, 'image');
    } elseif (!empty($_FILES['favicon']['name']) && ($flash['type'] ?? '') !== 'danger') {
        $flash = [
            'type' => 'danger',
            'text' => $faviconError ?: 'Favicon upload failed. Please try again.',
        ];
    }

    refreshSettingsCache();
    $_SESSION['admin_flash'] = $flash;
    header('Location: settings.php');
    exit;
}

$settings = getSettings();
$socialLinks = getSocialLinks();
$socialLinks = array_pad($socialLinks, 3, ['label' => '', 'url' => '']);
$siteLogo = $settings['site_logo'] ?? ($settings['logo'] ?? '');
$adminFlash = $_SESSION['admin_flash'] ?? null;
unset($_SESSION['admin_flash']);

include __DIR__ . '/admin-header.php';
?>
<?php if (is_array($adminFlash)): ?>
    <div class="alert alert-<?= htmlspecialchars($adminFlash['type']) ?>"><?= htmlspecialchars($adminFlash['text']) ?></div>
<?php endif; ?>
<div class="row g-4">
    <div class="col-xl-8">
        <div class="card admin-card p-4">
            <form method="post" enctype="multipart/form-data">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Site Name</label>
                        <input type="text" name="site_name" class="form-control" value="<?= htmlspecialchars($settings['site_name'] ?? 'LearnWise') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Site Tagline</label>
                        <input type="text" name="site_tagline" class="form-control" value="<?= htmlspecialchars($settings['site_tagline'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Navbar CTA Text</label>
                        <input type="text" name="nav_cta_text" class="form-control" value="<?= htmlspecialchars($settings['nav_cta_text'] ?? 'Book Free Trial') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Navbar CTA Link</label>
                        <input type="text" name="nav_cta_link" class="form-control" value="<?= htmlspecialchars($settings['nav_cta_link'] ?? '#lead-form') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Site Phone</label>
                        <input type="text" name="site_phone" class="form-control" value="<?= htmlspecialchars($settings['site_phone'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">WhatsApp Number</label>
                        <input type="text" name="whatsapp_number" class="form-control" value="<?= htmlspecialchars($settings['whatsapp_number'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Site Email</label>
                        <input type="email" name="site_email" class="form-control" value="<?= htmlspecialchars($settings['site_email'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Admin Notification Email</label>
                        <input type="email" name="admin_notification_email" class="form-control" value="<?= htmlspecialchars($settings['admin_notification_email'] ?? ($settings['site_email'] ?? '')) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Logo</label>
                        <input type="file" name="logo" class="form-control" accept=".png,.jpg,.jpeg,.svg,image/png,image/jpeg,image/svg+xml">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Favicon</label>
                        <input type="file" name="favicon" class="form-control" accept=".png,.jpg,.jpeg,.ico,.svg,image/png,image/jpeg,image/svg+xml,image/x-icon">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Privacy Policy Link</label>
                        <input type="text" name="footer_legal_privacy" class="form-control" value="<?= htmlspecialchars($settings['footer_legal_privacy'] ?? 'privacy.php') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Terms & Conditions Link</label>
                        <input type="text" name="footer_legal_terms" class="form-control" value="<?= htmlspecialchars($settings['footer_legal_terms'] ?? 'terms.php') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Address</label>
                        <textarea name="address" rows="3" class="form-control"><?= htmlspecialchars($settings['address'] ?? '') ?></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Footer Text</label>
                        <textarea name="footer_text" rows="3" class="form-control"><?= htmlspecialchars($settings['footer_text'] ?? '') ?></textarea>
                    </div>
                    <div class="col-12"><h6 class="mb-0">Email / SMTP Settings</h6></div>
                    <div class="col-md-6">
                        <label class="form-label">SMTP Host</label>
                        <input type="text" name="smtp_host" class="form-control" value="<?= htmlspecialchars($settings['smtp_host'] ?? '') ?>" placeholder="smtp.example.com">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">SMTP Port</label>
                        <input type="number" name="smtp_port" class="form-control" value="<?= htmlspecialchars($settings['smtp_port'] ?? '587') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">SMTP Username</label>
                        <input type="text" name="smtp_username" class="form-control" value="<?= htmlspecialchars($settings['smtp_username'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">SMTP Password</label>
                        <input type="password" name="smtp_password" class="form-control" value="" placeholder="Leave blank to keep existing password">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">SMTP Encryption</label>
                        <select name="smtp_encryption" class="form-select">
                            <?php foreach (['tls', 'ssl', ''] as $encryption): ?>
                                <option value="<?= htmlspecialchars($encryption) ?>" <?= ($settings['smtp_encryption'] ?? 'tls') === $encryption ? 'selected' : '' ?>><?= $encryption !== '' ? strtoupper($encryption) : 'None' ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">From Email</label>
                        <input type="email" name="smtp_from_email" class="form-control" value="<?= htmlspecialchars($settings['smtp_from_email'] ?? 'noreply@learnwise.com') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">From Name</label>
                        <input type="text" name="smtp_from_name" class="form-control" value="<?= htmlspecialchars($settings['smtp_from_name'] ?? ($settings['site_name'] ?? 'LearnWise')) ?>">
                    </div>
                    <div class="col-12">
                        <h6 class="mb-3">Social Links</h6>
                    </div>
                    <?php for ($i = 0; $i < 3; $i++): ?>
                        <div class="col-md-4">
                            <input type="text" name="social_label_<?= $i + 1 ?>" class="form-control" placeholder="Platform label" value="<?= htmlspecialchars($socialLinks[$i]['label'] ?? '') ?>">
                        </div>
                        <div class="col-md-8">
                            <input type="url" name="social_url_<?= $i + 1 ?>" class="form-control" placeholder="https://..." value="<?= htmlspecialchars($socialLinks[$i]['url'] ?? '') ?>">
                        </div>
                    <?php endfor; ?>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Save Website Settings</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card admin-card p-4 h-100">
            <h5 class="mb-3">Live Preview</h5>
            <?php if ($siteLogo !== ''): ?>
                <img src="../<?= htmlspecialchars($siteLogo) ?>" alt="Site logo" class="img-fluid rounded-4 border mb-3 p-3 bg-white">
            <?php endif; ?>
            <p class="mb-1"><strong>Phone:</strong> <?= htmlspecialchars($settings['site_phone'] ?? '') ?></p>
            <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($settings['site_email'] ?? '') ?></p>
            <p class="mb-1"><strong>Admin Notifications:</strong> <?= htmlspecialchars($settings['admin_notification_email'] ?? '') ?></p>
            <p class="mb-0 text-muted"><?= nl2br(htmlspecialchars($settings['address'] ?? '')) ?></p>
        </div>
    </div>
</div>
<?php include __DIR__ . '/admin-footer.php'; ?>
