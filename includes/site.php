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
    $query .= ' ORDER BY created_at DESC, id DESC';
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

function cmsUploadFile(
    array $file,
    string $targetFolder,
    array $allowedExtensions,
    string $prefix,
    int $maxSizeBytes = 5242880,
    ?string &$errorMessage = null
): ?string
{
    $errorMessage = null;
    $uploadError = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

    if ($uploadError === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($uploadError !== UPLOAD_ERR_OK) {
        $errorMessage = 'Upload failed. Please try again.';
        return null;
    }

    $fileSize = (int) ($file['size'] ?? 0);
    if ($maxSizeBytes > 0 && $fileSize > $maxSizeBytes) {
        $errorMessage = 'File too large. Maximum size is ' . number_format($maxSizeBytes / 1048576, 0) . 'MB.';
        return null;
    }

    $extension = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedExtensions, true)) {
        $errorMessage = 'Invalid file type. Please upload ' . implode(', ', $allowedExtensions) . ' only.';
        return null;
    }

    $tmpName = (string) ($file['tmp_name'] ?? '');
    if ($tmpName === '' || !is_uploaded_file($tmpName)) {
        $errorMessage = 'Upload failed. Please try again.';
        return null;
    }

    if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true) && @getimagesize($tmpName) === false) {
        $errorMessage = 'Upload failed. Please use a valid image file.';
        return null;
    }

    if ($extension === 'svg') {
        $svgContents = @file_get_contents($tmpName) ?: '';
        if ($svgContents === '' || stripos($svgContents, '<svg') === false) {
            $errorMessage = 'Upload failed. Please use a valid SVG file.';
            return null;
        }
    }

    $baseFolder = dirname(__DIR__) . '/uploads/' . trim($targetFolder, '/');
    if (!is_dir($baseFolder)) {
        @mkdir($baseFolder, 0755, true);
    }

    if (!is_dir($baseFolder)) {
        $errorMessage = 'Upload failed. Please try again.';
        return null;
    }

    $filename = $prefix . '-' . time() . '-' . bin2hex(random_bytes(4)) . '.' . $extension;
    $absolutePath = $baseFolder . '/' . $filename;
    if (!move_uploaded_file($tmpName, $absolutePath)) {
        $errorMessage = 'Upload failed. Please try again.';
        return null;
    }

    return 'uploads/' . trim($targetFolder, '/') . '/' . $filename;
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
