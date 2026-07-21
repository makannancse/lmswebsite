<?php
require_once __DIR__ . '/pdo.php';
require_once __DIR__ . '/app.php';

function getSettings(): array
{
    global $lwSettingsCache;

    if (is_array($lwSettingsCache ?? null)) {
        return $lwSettingsCache;
    }

    $pdo = lwGetPdo();
    $stmt = $pdo->query('SELECT setting_key, setting_value FROM website_settings');
    $lwSettingsCache = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $lwSettingsCache[$row['setting_key']] = $row['setting_value'];
    }

    return $lwSettingsCache;
}

function getSetting(string $key, string $default = ''): string
{
    $settings = getSettings();
    if ($key === 'site_logo' && !empty($settings['logo']) && empty($settings['site_logo'])) {
        return (string) $settings['logo'];
    }
    if ($key === 'logo' && !empty($settings['site_logo']) && empty($settings['logo'])) {
        return (string) $settings['site_logo'];
    }
    return isset($settings[$key]) && $settings[$key] !== '' ? (string) $settings[$key] : $default;
}

function getSiteLogo(): string
{
    return getSetting('site_logo', getSetting('logo'));
}

function getDisplayLogo(): string
{
    $displayLogo = 'assets/images/learnwise-logo-display.png';
    if (is_file(dirname(__DIR__) . '/' . $displayLogo)) {
        return $displayLogo;
    }

    return getSiteLogo();
}

function getTeacherPlaceholderImage(): string
{
    return 'assets/images/teacher-placeholder.svg';
}

function getTeacherPhotoUrl(array $teacher): string
{
    $image = trim((string) ($teacher['image'] ?? ''));
    return $image !== '' ? $image : getTeacherPlaceholderImage();
}

function refreshSettingsCache(): void
{
    global $lwSettingsCache;
    $lwSettingsCache = null;
}

function getSocialLinks(): array
{
    $json = getSetting('social_links', '[]');
    $data = json_decode($json, true);
    return is_array($data) ? $data : [];
}

function getMenus(string $status = 'active'): array
{
    $pdo = lwGetPdo();

    $query = 'SELECT * FROM menus';
    $params = [];
    if ($status !== 'all') {
        $query .= ' WHERE status = :status';
        $params[':status'] = $status;
    }
    $query .= ' ORDER BY sort_order ASC, id ASC';

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getPage(string $pageName, bool $activeOnly = true): ?array
{
    $pdo = lwGetPdo();

    $query = 'SELECT * FROM pages WHERE page_name = :page_name';
    $params = [':page_name' => $pageName];
    if ($activeOnly) {
        $query .= " AND status = 'active'";
    }
    $query .= ' LIMIT 1';

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $page = $stmt->fetch(PDO::FETCH_ASSOC);
    return $page ?: null;
}

function getPageSectionsById(int $pageId, string $status = 'active'): array
{
    $pdo = lwGetPdo();

    $query = 'SELECT * FROM page_sections WHERE page_id = :page_id';
    $params = [':page_id' => $pageId];
    if ($status !== 'all') {
        $query .= ' AND status = :status';
        $params[':status'] = $status;
    }
    $query .= ' ORDER BY sort_order ASC, id ASC';

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getPageSections(string $pageName, string $status = 'active'): array
{
    $page = getPage($pageName, $status !== 'all');
    if (!$page) {
        return [];
    }
    return getPageSectionsById((int) $page['id'], $status);
}

function getPageSectionMap(string $pageName, string $status = 'active'): array
{
    $sections = getPageSections($pageName, $status);
    $mapped = [];
    foreach ($sections as $section) {
        $mapped[$section['section_key']] = $section;
    }
    return $mapped;
}

function lwNormalizeStatusFilter($status): string
{
    return $status instanceof PDO ? 'active' : (string) $status;
}

function getSampleVideos($status = 'active'): array
{
    $pdo = lwGetPdo();

    $status = lwNormalizeStatusFilter($status);
    $query = 'SELECT * FROM sample_videos';
    $params = [];
    if ($status !== 'all') {
        $query .= ' WHERE status = :status';
        $params[':status'] = $status;
    }
    $query .= ' ORDER BY sort_order ASC, id ASC';

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getCourses($status = 'active'): array
{
    $pdo = lwGetPdo();

    $status = lwNormalizeStatusFilter($status);
    $query = 'SELECT * FROM courses';
    $params = [];
    if ($status !== 'all') {
        $query .= ' WHERE status = :status';
        $params[':status'] = $status;
    }
    $query .= ' ORDER BY sort_order ASC, created_at DESC, id DESC';
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTeachers($status = 'active'): array
{
    $pdo = lwGetPdo();

    $status = lwNormalizeStatusFilter($status);
    $query = 'SELECT * FROM teachers';
    $params = [];
    if ($status !== 'all') {
        $query .= ' WHERE status = :status';
        $params[':status'] = $status;
    }
    $query .= ' ORDER BY sort_order ASC, created_at DESC, id DESC';
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getStandardsSections($status = 'active'): array
{
    $pdo = lwGetPdo();

    $status = lwNormalizeStatusFilter($status);
    $query = 'SELECT * FROM standards_sections';
    $params = [];
    if ($status !== 'all') {
        $query .= ' WHERE status = :status';
        $params[':status'] = $status;
    }
    $query .= ' ORDER BY sort_order ASC, id ASC';

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getComplianceRules($status = 'active'): array
{
    $pdo = lwGetPdo();

    $status = lwNormalizeStatusFilter($status);
    $query = 'SELECT * FROM compliance_rules';
    $params = [];
    if ($status !== 'all') {
        $query .= ' WHERE status = :status';
        $params[':status'] = $status;
    }
    $query .= ' ORDER BY sort_order ASC, id ASC';

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function parseStructuredLines(?string $content, int $parts = 2): array
{
    if ($content === null || trim($content) === '') {
        return [];
    }

    $rows = preg_split('/\r\n|\r|\n/', trim($content)) ?: [];
    $items = [];
    foreach ($rows as $row) {
        $segments = array_map('trim', explode('|', $row));
        $segments = array_pad($segments, $parts, '');
        $items[] = $segments;
    }
    return $items;
}

function parseTextBlocks(?string $content): array
{
    if ($content === null || trim($content) === '') {
        return [];
    }

    return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $content) ?: [])));
}

function parseSectionItems(?string $content, int $parts = 4): array
{
    $rows = parseStructuredLines($content, $parts);
    $items = [];

    foreach ($rows as $row) {
        $title = trim((string) ($row[0] ?? ''));
        $text = trim((string) ($row[1] ?? ''));
        $image = trim((string) ($row[2] ?? ''));
        $link = trim((string) ($row[3] ?? ''));

        if ($title === '' && $text === '' && $image === '' && $link === '') {
            continue;
        }

        $items[] = [
            'title' => $title,
            'text' => $text,
            'image' => $image,
            'link' => $link,
            'icon' => $image,
        ];
    }

    return $items;
}

function getSectionSettings(array $section): array
{
    if (empty($section['section_settings'])) {
        return [];
    }

    $decoded = json_decode((string) $section['section_settings'], true);
    return is_array($decoded) ? $decoded : [];
}

function getSectionPrimaryAction(array $section, array $fallback = []): array
{
    $settings = getSectionSettings($section);
    $actions = parseStructuredLines((string) ($section['section_content'] ?? $section['content'] ?? ''), 2);

    $text = trim((string) ($section['button_text'] ?? $settings['button_text'] ?? ($actions[0][0] ?? $fallback['text'] ?? '')));
    $link = trim((string) ($section['button_link'] ?? $settings['button_link'] ?? ($actions[0][1] ?? $fallback['link'] ?? '#')));

    if ($link === 'dynamic' && strtolower($text) === 'whatsapp') {
        $link = buildWhatsappLink();
    }

    return [
        'text' => $text,
        'link' => $link !== '' ? $link : '#',
    ];
}

function normalizePageSection(array $section): array
{
    $normalized = $section;
    $fieldMap = [
        'title' => 'section_title',
        'subtitle' => 'section_subtitle',
        'content' => 'section_content',
        'image' => 'section_image',
        'type' => 'section_type',
        'key' => 'section_key',
    ];

    foreach ($fieldMap as $legacyKey => $currentKey) {
        $legacyValue = trim((string) ($normalized[$legacyKey] ?? ''));
        $currentValue = trim((string) ($normalized[$currentKey] ?? ''));
        $bestValue = $currentValue !== '' ? $currentValue : $legacyValue;

        $normalized[$currentKey] = $bestValue;
        $normalized[$legacyKey] = $bestValue;
    }

    $action = getSectionPrimaryAction($normalized);
    if (trim((string) ($normalized['button_text'] ?? '')) === '') {
        $normalized['button_text'] = $action['text'];
    }
    if (trim((string) ($normalized['button_link'] ?? '')) === '') {
        $normalized['button_link'] = $action['link'];
    }

    if (trim((string) ($normalized['badge'] ?? '')) === '') {
        $settings = getSectionSettings($normalized);
        $normalized['badge'] = (string) ($settings['badge'] ?? '');
    }

    return $normalized;
}

function renderPageSections(array $sections): void
{
    foreach ($sections as $rawSection) {
        $section = normalizePageSection($rawSection);
        $sectionType = $section['section_type'] ?: 'rich_text';
        $sectionFile = __DIR__ . '/../sections/' . $sectionType . '.php';
        if (!file_exists($sectionFile)) {
            $sectionFile = __DIR__ . '/../sections/rich_text.php';
        }

        try {
            include $sectionFile;
        } catch (Throwable $exception) {
            lwReportException($exception, [
                'section_id' => (int) ($section['id'] ?? 0),
                'section_key' => (string) ($section['section_key'] ?? ''),
                'section_type' => (string) ($sectionType ?? ''),
            ]);
            echo '<section><div class="container"><div class="alert alert-light border rounded-4 px-4 py-3 mb-0">Something went wrong. Please try again.</div></div></section>';
        }
    }
}

function pageFileFromName(string $pageName): string
{
    return $pageName === 'home' ? 'index.php' : $pageName . '.php';
}

function normalizeWhatsappNumber(string $number): string
{
    return preg_replace('/\D+/', '', $number) ?: '';
}

function buildWhatsappLink(): string
{
    $number = normalizeWhatsappNumber(getSetting('whatsapp_number', getSetting('site_phone')));
    return $number !== '' ? 'https://wa.me/' . $number : '#';
}

function lwIniSizeToBytes(string $size): int
{
    $size = trim($size);
    if ($size === '') {
        return 0;
    }

    $unit = strtolower($size[strlen($size) - 1]);
    $value = (float) $size;

    switch ($unit) {
        case 'g':
            $value *= 1024;
            // no break
        case 'm':
            $value *= 1024;
            // no break
        case 'k':
            $value *= 1024;
            break;
    }

    return (int) round($value);
}

function lwFormatUploadBytes(int $bytes): string
{
    if ($bytes >= 1073741824) {
        return rtrim(rtrim(number_format($bytes / 1073741824, 2), '0'), '.') . 'GB';
    }
    if ($bytes >= 1048576) {
        return rtrim(rtrim(number_format($bytes / 1048576, 2), '0'), '.') . 'MB';
    }
    if ($bytes >= 1024) {
        return rtrim(rtrim(number_format($bytes / 1024, 2), '0'), '.') . 'KB';
    }

    return $bytes . 'B';
}

function lwUploadLimits(): array
{
    return [
        'file_uploads' => ini_get('file_uploads'),
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'post_max_size' => ini_get('post_max_size'),
        'memory_limit' => ini_get('memory_limit'),
        'max_file_uploads' => ini_get('max_file_uploads'),
        'upload_tmp_dir' => ini_get('upload_tmp_dir') ?: sys_get_temp_dir(),
    ];
}

function lwLogUploadEvent(string $event, array $entry = []): void
{
    $entry = array_merge([
        'event' => $event,
        'script' => (string) ($_SERVER['SCRIPT_NAME'] ?? ''),
        'method' => (string) ($_SERVER['REQUEST_METHOD'] ?? ''),
        'content_length' => (int) ($_SERVER['CONTENT_LENGTH'] ?? 0),
        'limits' => lwUploadLimits(),
    ], $entry);

    if (function_exists('lwLogToFile')) {
        lwLogToFile('uploads.log', $entry);
        return;
    }

    if (function_exists('lwLogRuntimeMessage')) {
        lwLogRuntimeMessage('Upload event: ' . json_encode($entry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
}

function lwUploadErrorMessage(int $uploadError): string
{
    switch ($uploadError) {
        case UPLOAD_ERR_INI_SIZE:
            return 'Upload failed because the file is larger than the server limit (' . ini_get('upload_max_filesize') . ').';
        case UPLOAD_ERR_FORM_SIZE:
            return 'Upload failed because the file is larger than the form limit.';
        case UPLOAD_ERR_PARTIAL:
            return 'Upload failed because the file was only partially uploaded.';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'Upload failed because the server temporary upload folder is missing.';
        case UPLOAD_ERR_CANT_WRITE:
            return 'Upload failed because the server could not write the temporary file.';
        case UPLOAD_ERR_EXTENSION:
            return 'Upload failed because a PHP extension blocked the file.';
        default:
            return 'Upload failed. Please try again.';
    }
}

function lwRequestExceedsPostMaxSize(): bool
{
    if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? '')) !== 'POST') {
        return false;
    }

    $contentLength = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);
    $postMaxBytes = lwIniSizeToBytes((string) ini_get('post_max_size'));

    return $postMaxBytes > 0 && $contentLength > $postMaxBytes;
}

function lwGetPostMaxSizeUploadError(string $area = ''): ?string
{
    if (!lwRequestExceedsPostMaxSize()) {
        return null;
    }

    $postMaxBytes = lwIniSizeToBytes((string) ini_get('post_max_size'));
    $message = 'Upload failed because the submitted form is larger than the server limit (' . ini_get('post_max_size') . ').';

    lwLogUploadEvent('upload_failed', [
        'area' => $area,
        'reason' => 'post_max_size_exceeded',
        'post_max_size_bytes' => $postMaxBytes,
        'post_max_size_human' => lwFormatUploadBytes($postMaxBytes),
        'message' => $message,
    ]);

    return $message;
}

function cmsUploadFile(
    array $file,
    string $targetFolder,
    array $allowedExtensions,
    string $prefix,
    int $maxSizeBytes = 5242880,
    ?string &$errorMessage = null,
    string $fieldName = ''
): ?string
{
    $errorMessage = null;
    $targetFolder = trim(str_replace('\\', '/', $targetFolder), '/');
    $allowedExtensions = array_values(array_unique(array_map('strtolower', $allowedExtensions)));
    $originalName = (string) ($file['name'] ?? '');
    $tmpName = (string) ($file['tmp_name'] ?? '');
    $fileSize = (int) ($file['size'] ?? 0);
    $uploadContext = [
        'field' => $fieldName,
        'target_folder' => $targetFolder,
        'original_name' => $originalName,
        'reported_size' => $fileSize,
        'reported_size_human' => lwFormatUploadBytes($fileSize),
        'tmp_name' => $tmpName,
    ];

    $uploadError = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

    if ($uploadError === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($uploadError !== UPLOAD_ERR_OK) {
        $errorMessage = lwUploadErrorMessage($uploadError);
        lwLogUploadEvent('upload_failed', $uploadContext + [
            'reason' => 'php_upload_error',
            'upload_error_code' => $uploadError,
            'message' => $errorMessage,
        ]);
        return null;
    }

    if ($maxSizeBytes > 0 && $fileSize > $maxSizeBytes) {
        $errorMessage = 'File too large. Maximum size is ' . number_format($maxSizeBytes / 1048576, 0) . 'MB.';
        lwLogUploadEvent('upload_failed', $uploadContext + [
            'reason' => 'app_max_size_exceeded',
            'max_size_bytes' => $maxSizeBytes,
            'max_size_human' => lwFormatUploadBytes($maxSizeBytes),
            'message' => $errorMessage,
        ]);
        return null;
    }

    $extension = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedExtensions, true)) {
        $errorMessage = 'Invalid file type. Please upload ' . implode(', ', $allowedExtensions) . ' only.';
        lwLogUploadEvent('upload_failed', $uploadContext + [
            'reason' => 'invalid_extension',
            'extension' => $extension,
            'allowed_extensions' => $allowedExtensions,
            'message' => $errorMessage,
        ]);
        return null;
    }

    if ($tmpName === '' || !is_uploaded_file($tmpName)) {
        $errorMessage = 'Upload failed. Please try again.';
        lwLogUploadEvent('upload_failed', $uploadContext + [
            'reason' => 'missing_or_invalid_uploaded_file',
            'is_uploaded_file' => $tmpName !== '' ? is_uploaded_file($tmpName) : false,
            'tmp_exists' => $tmpName !== '' ? file_exists($tmpName) : false,
            'message' => $errorMessage,
        ]);
        return null;
    }

    if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true) && @getimagesize($tmpName) === false) {
        $errorMessage = 'Upload failed. Please use a valid image file.';
        lwLogUploadEvent('upload_failed', $uploadContext + [
            'reason' => 'invalid_image_file',
            'extension' => $extension,
            'message' => $errorMessage,
        ]);
        return null;
    }

    if ($extension === 'svg') {
        $svgContents = @file_get_contents($tmpName) ?: '';
        if ($svgContents === '' || stripos($svgContents, '<svg') === false) {
            $errorMessage = 'Upload failed. Please use a valid SVG file.';
            lwLogUploadEvent('upload_failed', $uploadContext + [
                'reason' => 'invalid_svg_file',
                'message' => $errorMessage,
            ]);
            return null;
        }
    }

    if ($targetFolder === '' || str_contains($targetFolder, '..')) {
        $errorMessage = 'Upload failed. Please try again.';
        lwLogUploadEvent('upload_failed', $uploadContext + [
            'reason' => 'invalid_target_folder',
            'message' => $errorMessage,
        ]);
        return null;
    }

    $baseFolder = dirname(__DIR__) . '/uploads/' . $targetFolder;
    if (!is_dir($baseFolder)) {
        @mkdir($baseFolder, 0755, true);
    }

    if (!is_dir($baseFolder)) {
        $errorMessage = 'Upload failed. Please try again.';
        lwLogUploadEvent('upload_failed', $uploadContext + [
            'reason' => 'target_directory_missing',
            'target_directory' => $baseFolder,
            'message' => $errorMessage,
        ]);
        return null;
    }

    if (!is_writable($baseFolder)) {
        $errorMessage = 'Upload failed because the upload folder is not writable.';
        lwLogUploadEvent('upload_failed', $uploadContext + [
            'reason' => 'target_directory_not_writable',
            'target_directory' => $baseFolder,
            'message' => $errorMessage,
        ]);
        return null;
    }

    try {
        $token = bin2hex(random_bytes(4));
    } catch (Throwable $exception) {
        lwReportException($exception, ['area' => 'cms_upload_token']);
        $token = substr(str_replace('.', '', uniqid('', true)), -8);
    }

    $filename = $prefix . '-' . time() . '-' . $token . '.' . $extension;
    $absolutePath = $baseFolder . '/' . $filename;
    if (function_exists('error_clear_last')) {
        error_clear_last();
    }

    if (!@move_uploaded_file($tmpName, $absolutePath)) {
        $errorMessage = 'Upload failed. Please try again.';
        lwLogUploadEvent('upload_failed', $uploadContext + [
            'reason' => 'move_uploaded_file_failed',
            'target_path' => $absolutePath,
            'target_directory' => $baseFolder,
            'target_directory_writable' => is_writable($baseFolder),
            'last_php_error' => error_get_last(),
            'message' => $errorMessage,
        ]);
        return null;
    }

    return 'uploads/' . $targetFolder . '/' . $filename;
}

function lwOptimizeUploadedImage(string $relativePath, int $maxWidth = 900, int $maxHeight = 900, int $quality = 82): void
{
    if (!function_exists('imagecreatetruecolor')) {
        return;
    }

    $relativePath = ltrim(str_replace('\\', '/', trim($relativePath)), '/');
    if ($relativePath === '') {
        return;
    }

    $projectRoot = realpath(dirname(__DIR__));
    $absolutePath = realpath(dirname(__DIR__) . '/' . $relativePath);
    if ($projectRoot === false || $absolutePath === false) {
        return;
    }

    $projectRoot = rtrim($projectRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    if (strpos($absolutePath, $projectRoot) !== 0 || !is_file($absolutePath)) {
        return;
    }

    $info = @getimagesize($absolutePath);
    if ($info === false || empty($info[0]) || empty($info[1])) {
        return;
    }

    [$width, $height] = $info;
    $mime = (string) ($info['mime'] ?? '');

    switch ($mime) {
        case 'image/jpeg':
            $source = @imagecreatefromjpeg($absolutePath);
            break;
        case 'image/png':
            $source = @imagecreatefrompng($absolutePath);
            break;
        case 'image/webp':
            if (!function_exists('imagecreatefromwebp')) {
                return;
            }
            $source = @imagecreatefromwebp($absolutePath);
            break;
        default:
            return;
    }

    if (!$source) {
        return;
    }

    $scale = min(1, $maxWidth / $width, $maxHeight / $height);
    $targetWidth = max(1, (int) round($width * $scale));
    $targetHeight = max(1, (int) round($height * $scale));

    $canvas = imagecreatetruecolor($targetWidth, $targetHeight);
    if (!$canvas) {
        imagedestroy($source);
        return;
    }

    if ($mime === 'image/png' || $mime === 'image/webp') {
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefilledrectangle($canvas, 0, 0, $targetWidth, $targetHeight, $transparent);
    }

    imagecopyresampled($canvas, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

    if ($mime === 'image/jpeg') {
        @imagejpeg($canvas, $absolutePath, $quality);
    } elseif ($mime === 'image/png') {
        @imagepng($canvas, $absolutePath, 7);
    } elseif ($mime === 'image/webp' && function_exists('imagewebp')) {
        @imagewebp($canvas, $absolutePath, $quality);
    }

    imagedestroy($source);
    imagedestroy($canvas);
}

function getPageMeta(string $pageName): array
{
    $page = getPage($pageName);
    return [
        'page' => $page,
        'title' => $page['meta_title'] ?? getSetting('site_name', 'LearnWise'),
        'description' => $page['meta_description'] ?? getSetting('site_tagline', ''),
        'og_image' => $page['og_image'] ?? getSiteLogo(),
    ];
}

function getSectionTypes(): array
{
    return [
        'hero' => 'Hero Banner',
        'feature_grid' => 'Feature / Icon Grid',
        'stats' => 'Statistics / Trust Bar',
        'courses_grid' => 'Course Categories',
        'videos' => 'Video Gallery',
        'testimonials' => 'Testimonials',
        'teachers_grid' => 'Teacher Showcase',
        'faq' => 'FAQ Accordion',
        'cta_banner' => 'CTA Banner',
        'lead_form' => 'Enrollment / Lead Form',
        'contact_form' => 'Contact Form',
        'rich_text' => 'Rich Text Block',
        'trust' => 'Trust Indicators',
        'founder' => 'Founder Note',
    ];
}

function getSectionKicker(array $section, string $default = 'LearnWise'): string
{
    $settings = getSectionSettings($section);
    $kicker = trim((string) ($settings['kicker'] ?? ''));
    return $kicker !== '' ? $kicker : $default;
}

function getCourseCategoryMeta(string $category): array
{
    $key = strtolower(trim($category));
    $programmeImages = [
        'math' => 'assets/images/programmes/mathematics.webp',
        'mathematics' => 'assets/images/programmes/mathematics.webp',
        'science' => 'assets/images/programmes/science.webp',
        'coding' => 'assets/images/programmes/coding.webp',
        'languages' => 'assets/images/programmes/languages.webp',
        'language' => 'assets/images/programmes/languages.webp',
        'arts' => 'assets/images/programmes/arts-creativity.webp',
        'art' => 'assets/images/programmes/arts-creativity.webp',
        'arts & creativity' => 'assets/images/programmes/arts-creativity.webp',
        'creative arts' => 'assets/images/programmes/arts-creativity.webp',
        'test prep' => 'assets/images/programmes/test-preparation.webp',
        'test preparation' => 'assets/images/programmes/test-preparation.webp',
        'test_preparation' => 'assets/images/programmes/test-preparation.webp',
        'test-preparation' => 'assets/images/programmes/test-preparation.webp',
        'testprep' => 'assets/images/programmes/test-preparation.webp',
    ];
    $map = [
        'math' => ['label' => 'Mathematics', 'icon' => 'bi-calculator', 'gradient' => 'course-math'],
        'mathematics' => ['label' => 'Mathematics', 'icon' => 'bi-calculator', 'gradient' => 'course-math'],
        'science' => ['label' => 'Science', 'icon' => 'bi-flask', 'gradient' => 'course-science'],
        'coding' => ['label' => 'Coding', 'icon' => 'bi-code-slash', 'gradient' => 'course-coding'],
        'languages' => ['label' => 'Languages', 'icon' => 'bi-translate', 'gradient' => 'course-languages'],
        'language' => ['label' => 'Languages', 'icon' => 'bi-translate', 'gradient' => 'course-languages'],
        'arts' => ['label' => 'Arts & Creativity', 'icon' => 'bi-palette', 'gradient' => 'course-arts'],
        'art' => ['label' => 'Arts & Creativity', 'icon' => 'bi-palette', 'gradient' => 'course-arts'],
        'arts & creativity' => ['label' => 'Arts & Creativity', 'icon' => 'bi-palette', 'gradient' => 'course-arts'],
        'creative arts' => ['label' => 'Arts & Creativity', 'icon' => 'bi-palette', 'gradient' => 'course-arts'],
        'test prep' => ['label' => 'Test Preparation', 'icon' => 'bi-trophy', 'gradient' => 'course-testprep'],
        'test preparation' => ['label' => 'Test Preparation', 'icon' => 'bi-trophy', 'gradient' => 'course-testprep'],
        'test_preparation' => ['label' => 'Test Preparation', 'icon' => 'bi-trophy', 'gradient' => 'course-testprep'],
        'test-preparation' => ['label' => 'Test Preparation', 'icon' => 'bi-trophy', 'gradient' => 'course-testprep'],
        'testprep' => ['label' => 'Test Preparation', 'icon' => 'bi-trophy', 'gradient' => 'course-testprep'],
    ];

    $meta = $map[$key] ?? [
        'label' => ucwords(str_replace(['_', '-'], ' ', $category)),
        'icon' => 'bi-book',
        'gradient' => 'course-default',
    ];

    $meta['image'] = $programmeImages[$key] ?? 'assets/images/programmes/mathematics.webp';
    return $meta;
}

function getCourseImageUrl(array $course): string
{
    $image = trim((string) ($course['image'] ?? ''));
    if ($image !== '') {
        return $image;
    }

    $category = trim((string) ($course['category'] ?? ''));
    if ($category === '') {
        $category = trim((string) ($course['title'] ?? ''));
    }

    $meta = getCourseCategoryMeta($category);
    return $meta['image'];
}

function getTestimonials($status = 'active'): array
{
    $pdo = lwGetPdo();
    $status = lwNormalizeStatusFilter($status);
    $query = 'SELECT * FROM testimonials';
    $params = [];
    if ($status !== 'all') {
        $query .= ' WHERE status = :status';
        $params[':status'] = $status;
    }
    $query .= ' ORDER BY sort_order ASC, id DESC';
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTestimonialImageUrl(?array $testimonial): string
{
    if ($testimonial !== null && !empty($testimonial['image'])) {
        $cleanPath = ltrim(str_replace('\\', '/', $testimonial['image']), '/');
        if (str_starts_with($cleanPath, 'http://') || str_starts_with($cleanPath, 'https://')) {
            return $cleanPath;
        }
        return $cleanPath;
    }
    return 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=300&q=80';
}

function getVideoThumbnail(array $video): string
{
    $thumbnail = trim((string) ($video['thumbnail'] ?? ''));
    if ($thumbnail === '' || str_contains($thumbnail, 'unsplash.com')) {
        $videoUrl = trim((string) ($video['video_url'] ?? ''));
        $videoFile = trim((string) ($video['video_file'] ?? ''));
        
        if ($videoUrl !== '') {
            if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/\s]{11})%i', $videoUrl, $match)) {
                return "https://img.youtube.com/vi/{$match[1]}/hqdefault.jpg";
            }
            
            if (preg_match('%vimeo\.com/(?:channels/(?:\w+/)?|groups/([^/]*)/videos/|album/(\d+)/video/|video/|)(\d+)(?:$|[?])%i', $videoUrl, $match)) {
                return getVimeoThumbnailUrl($match[3]);
            }
        }
        
        if ($videoFile !== '') {
            return 'video-preview';
        }
        
        return 'assets/images/video-placeholder.svg';
    }
    return $thumbnail;
}

function getVimeoThumbnailUrl(string $vimeoId): string
{
    $ctx = stream_context_create([
        'http' => [
            'timeout' => 1.5,
        ]
    ]);
    
    $response = @file_get_contents("https://vimeo.com/api/v2/video/{$vimeoId}.json", false, $ctx);
    if ($response !== false) {
        $data = json_decode($response, true);
        if (!empty($data[0]['thumbnail_large'])) {
            return $data[0]['thumbnail_large'];
        }
    }
    
    return 'assets/images/video-placeholder.svg';
}
