<?php
require_once __DIR__ . '/bootstrap.php';

lwBootstrapApplication();

$localConfig = [];
$configFile = __DIR__ . '/config.local.php';
if (is_file($configFile)) {
    $loaded = require $configFile;
    if (is_array($loaded)) {
        $localConfig = $loaded;
    }
}

if (!empty($localConfig['app_url'])) {
    define('LW_APP_URL', rtrim((string) $localConfig['app_url'], '/'));
}
if (!empty($localConfig['app_base_path'])) {
    define('LW_APP_BASE_PATH', (string) $localConfig['app_base_path']);
}

$dbHost = (string) ($localConfig['db_host'] ?? '127.0.0.1');
$dbName = (string) ($localConfig['db_name'] ?? 'learnwise');
$dbUser = (string) ($localConfig['db_user'] ?? 'root');
$dbPass = (string) ($localConfig['db_pass'] ?? '');

function lwTableExists(PDO $pdo, string $table): bool
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table_name');
    $stmt->execute([':table_name' => $table]);
    return (bool) $stmt->fetchColumn();
}

function lwColumnExists(PDO $pdo, string $table, string $column): bool
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table_name AND COLUMN_NAME = :column_name');
    $stmt->execute([
        ':table_name' => $table,
        ':column_name' => $column,
    ]);
    return (bool) $stmt->fetchColumn();
}

function lwEnsureColumn(PDO $pdo, string $table, string $column, string $definition): void
{
    if (!lwColumnExists($pdo, $table, $column)) {
        $pdo->exec("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}");
    }
}

function lwSaveSetting(PDO $pdo, string $key, string $value, string $type = 'text'): void
{
    $stmt = $pdo->prepare('
        INSERT INTO website_settings (setting_key, setting_value, setting_type)
        VALUES (:setting_key, :setting_value, :setting_type)
        ON DUPLICATE KEY UPDATE
            setting_value = VALUES(setting_value),
            setting_type = VALUES(setting_type),
            updated_at = CURRENT_TIMESTAMP
    ');
    $stmt->execute([
        ':setting_key' => $key,
        ':setting_value' => $value,
        ':setting_type' => $type,
    ]);
}

function lwEnsureSetting(PDO $pdo, string $key, string $value, string $type = 'text'): void
{
    $check = $pdo->prepare('SELECT COUNT(*) FROM website_settings WHERE setting_key = :setting_key');
    $check->execute([':setting_key' => $key]);

    if ((int) $check->fetchColumn() === 0) {
        lwSaveSetting($pdo, $key, $value, $type);
    }
}

function lwEnsurePage(PDO $pdo, array $page): int
{
    $select = $pdo->prepare('SELECT id FROM pages WHERE page_name = :page_name LIMIT 1');
    $select->execute([':page_name' => $page['page_name']]);
    $pageId = (int) $select->fetchColumn();

    if ($pageId > 0) {
        $update = $pdo->prepare('
            UPDATE pages
            SET page_title = COALESCE(NULLIF(page_title, ""), :page_title),
                meta_title = COALESCE(NULLIF(meta_title, ""), :meta_title),
                meta_description = COALESCE(NULLIF(meta_description, ""), :meta_description),
                og_image = COALESCE(NULLIF(og_image, ""), :og_image)
            WHERE id = :id
        ');
        $update->execute([
            ':page_title' => $page['page_title'],
            ':meta_title' => $page['meta_title'],
            ':meta_description' => $page['meta_description'],
            ':og_image' => $page['og_image'],
            ':id' => $pageId,
        ]);
        return $pageId;
    }

    $insert = $pdo->prepare('
        INSERT INTO pages (page_name, page_title, meta_title, meta_description, og_image, status)
        VALUES (:page_name, :page_title, :meta_title, :meta_description, :og_image, :status)
    ');
    $insert->execute($page);
    return (int) $pdo->lastInsertId();
}

function lwSeedSection(PDO $pdo, int $pageId, array $section): void
{
    $check = $pdo->prepare('SELECT id FROM page_sections WHERE page_id = :page_id AND section_key = :section_key LIMIT 1');
    $check->execute([
        ':page_id' => $pageId,
        ':section_key' => $section['section_key'],
    ]);
    if ($check->fetchColumn()) {
        return;
    }

    $insert = $pdo->prepare('
        INSERT INTO page_sections (
            page_id, section_key, section_title, section_subtitle, section_content,
            section_image, section_type, section_settings, sort_order, status
        ) VALUES (
            :page_id, :section_key, :section_title, :section_subtitle, :section_content,
            :section_image, :section_type, :section_settings, :sort_order, :status
        )
    ');
    $insert->execute([
        ':page_id' => $pageId,
        ':section_key' => $section['section_key'],
        ':section_title' => $section['section_title'],
        ':section_subtitle' => $section['section_subtitle'],
        ':section_content' => $section['section_content'],
        ':section_image' => $section['section_image'],
        ':section_type' => $section['section_type'],
        ':section_settings' => $section['section_settings'],
        ':sort_order' => $section['sort_order'],
        ':status' => $section['status'],
    ]);
}

function lwEnsureMenu(PDO $pdo, array $menu): void
{
    $check = $pdo->prepare('SELECT id FROM menus WHERE menu_name = :menu_name AND menu_link = :menu_link LIMIT 1');
    $check->execute([
        ':menu_name' => $menu['menu_name'],
        ':menu_link' => $menu['menu_link'],
    ]);
    if ($check->fetchColumn()) {
        return;
    }

    $insert = $pdo->prepare('INSERT INTO menus (menu_name, menu_link, sort_order, status) VALUES (:menu_name, :menu_link, :sort_order, :status)');
    $insert->execute($menu);
}

function lwEnsureSampleVideo(PDO $pdo, array $video): void
{
    $check = $pdo->prepare('SELECT id FROM sample_videos WHERE title = :title LIMIT 1');
    $check->execute([':title' => $video['title']]);
    if ($check->fetchColumn()) {
        return;
    }

    $insert = $pdo->prepare('
        INSERT INTO sample_videos (title, description, thumbnail, video_file, video_url, sort_order, status)
        VALUES (:title, :description, :thumbnail, :video_file, :video_url, :sort_order, :status)
    ');
    $insert->execute($video);
}

function lwInitializeDatabase(PDO $pdo): void
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(150) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS website_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(150) NOT NULL UNIQUE,
        setting_value LONGTEXT DEFAULT NULL,
        setting_type VARCHAR(50) NOT NULL DEFAULT 'text',
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS pages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        page_name VARCHAR(100) NOT NULL UNIQUE,
        page_title VARCHAR(255) NOT NULL,
        meta_title VARCHAR(255) DEFAULT NULL,
        meta_description TEXT DEFAULT NULL,
        og_image VARCHAR(255) DEFAULT NULL,
        status ENUM('active','inactive') NOT NULL DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS page_sections (
        id INT AUTO_INCREMENT PRIMARY KEY,
        page_id INT NOT NULL,
        section_key VARCHAR(100) NOT NULL,
        section_title VARCHAR(255) DEFAULT NULL,
        section_subtitle TEXT DEFAULT NULL,
        section_content LONGTEXT DEFAULT NULL,
        section_image VARCHAR(255) DEFAULT NULL,
        section_type VARCHAR(100) NOT NULL,
        section_settings LONGTEXT DEFAULT NULL,
        sort_order INT NOT NULL DEFAULT 0,
        status ENUM('active','inactive') NOT NULL DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        CONSTRAINT fk_page_sections_page FOREIGN KEY (page_id) REFERENCES pages(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS menus (
        id INT AUTO_INCREMENT PRIMARY KEY,
        menu_name VARCHAR(150) NOT NULL,
        menu_link VARCHAR(255) NOT NULL,
        sort_order INT NOT NULL DEFAULT 0,
        status ENUM('active','inactive') NOT NULL DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS sample_videos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT DEFAULT NULL,
        thumbnail VARCHAR(255) DEFAULT NULL,
        video_file VARCHAR(255) DEFAULT NULL,
        video_url VARCHAR(255) DEFAULT NULL,
        sort_order INT NOT NULL DEFAULT 0,
        status ENUM('active','inactive') NOT NULL DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS standards_sections (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        icon VARCHAR(50) DEFAULT NULL,
        sort_order INT DEFAULT 0,
        status ENUM('active','inactive') DEFAULT 'active'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS compliance_rules (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        icon VARCHAR(50) DEFAULT NULL,
        penalty VARCHAR(255) DEFAULT NULL,
        sort_order INT DEFAULT 0,
        status ENUM('active','inactive') DEFAULT 'active'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS courses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(200) NOT NULL,
        description TEXT NOT NULL,
        category VARCHAR(100) NOT NULL,
        image VARCHAR(255) DEFAULT NULL,
        sort_order INT NOT NULL DEFAULT 0,
        status ENUM('active','inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS teachers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(150) NOT NULL,
        subject VARCHAR(100) NOT NULL,
        experience VARCHAR(50) DEFAULT NULL,
        experience_years INT NOT NULL DEFAULT 0,
        students_count INT NOT NULL DEFAULT 0,
        bio TEXT DEFAULT NULL,
        image VARCHAR(255) DEFAULT NULL,
        status ENUM('active','inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS leads (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(120) NOT NULL,
        parent_name VARCHAR(120) DEFAULT NULL,
        student_name VARCHAR(120) DEFAULT NULL,
        email VARCHAR(150) NOT NULL,
        phone VARCHAR(40) NOT NULL,
        course VARCHAR(150) DEFAULT NULL,
        message TEXT DEFAULT NULL,
        source VARCHAR(60) DEFAULT 'website',
        status ENUM('new','contacted','converted','closed') NOT NULL DEFAULT 'new',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    lwEnsureColumn($pdo, 'leads', 'parent_name', 'VARCHAR(120) DEFAULT NULL');
    lwEnsureColumn($pdo, 'leads', 'student_name', 'VARCHAR(120) DEFAULT NULL');
    lwEnsureColumn($pdo, 'leads', 'course', 'VARCHAR(150) DEFAULT NULL');
    lwEnsureColumn($pdo, 'leads', 'status', "ENUM('new','contacted','converted','closed') NOT NULL DEFAULT 'new'");
    lwEnsureColumn($pdo, 'leads', 'updated_at', 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');

    lwEnsureColumn($pdo, 'courses', 'sort_order', 'INT NOT NULL DEFAULT 0');
    lwEnsureColumn($pdo, 'courses', 'updated_at', 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
    lwEnsureColumn($pdo, 'teachers', 'experience', 'VARCHAR(50) DEFAULT NULL');
    lwEnsureColumn($pdo, 'teachers', 'experience_years', 'INT NOT NULL DEFAULT 0');
    lwEnsureColumn($pdo, 'teachers', 'students_count', 'INT NOT NULL DEFAULT 0');
    lwEnsureColumn($pdo, 'teachers', 'bio', 'TEXT DEFAULT NULL');
    lwEnsureColumn($pdo, 'teachers', 'qualifications', 'VARCHAR(255) DEFAULT NULL');
    lwEnsureColumn($pdo, 'teachers', 'updated_at', 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
    lwEnsureColumn($pdo, 'page_sections', 'page_id', 'INT NULL');
    lwEnsureColumn($pdo, 'page_sections', 'section_key', 'VARCHAR(100) NULL');
    lwEnsureColumn($pdo, 'page_sections', 'section_title', 'VARCHAR(255) NULL');
    lwEnsureColumn($pdo, 'page_sections', 'section_subtitle', 'TEXT NULL');
    lwEnsureColumn($pdo, 'page_sections', 'section_content', 'LONGTEXT NULL');
    lwEnsureColumn($pdo, 'page_sections', 'section_image', 'VARCHAR(255) NULL');
    lwEnsureColumn($pdo, 'page_sections', 'section_settings', 'LONGTEXT NULL');
    lwEnsureColumn($pdo, 'page_sections', 'updated_at', 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');

    if (lwTableExists($pdo, 'settings')) {
        $migrationFlag = $pdo->query("SELECT setting_value FROM website_settings WHERE setting_key = 'legacy_settings_migrated' LIMIT 1")->fetchColumn();
        if ($migrationFlag !== '1') {
            $legacySettings = $pdo->query('SELECT key_name, value FROM settings')->fetchAll(PDO::FETCH_KEY_PAIR);
            $mappedSettings = [
                'phone' => 'site_phone',
                'email' => 'site_email',
                'address' => 'address',
                'whatsapp' => 'whatsapp_number',
                'site_logo' => 'site_logo',
            ];
            foreach ($mappedSettings as $oldKey => $newKey) {
                if (!empty($legacySettings[$oldKey])) {
                    $settingType = $newKey === 'site_logo' ? 'image' : 'text';
                    lwSaveSetting($pdo, $newKey, (string) $legacySettings[$oldKey], $settingType);
                    if ($newKey === 'site_logo') {
                        lwSaveSetting($pdo, 'logo', (string) $legacySettings[$oldKey], 'image');
                    }
                }
            }
            lwSaveSetting($pdo, 'legacy_settings_migrated', '1', 'text');
        }
    }

    $admin = $pdo->prepare('INSERT IGNORE INTO admins (email, password) VALUES (:email, :password)');
    $admin->execute([
        ':email' => 'admin@learnwise.com',
        ':password' => '$2y$10$hH7lbkWBUFtq6G/ynvyXvuWMfC5OGoktOVlmW1LlnN2X4qmETCuji',
    ]);

    $defaultSettings = [
        ['key' => 'site_phone', 'value' => '+91 98765 43210', 'type' => 'text'],
        ['key' => 'site_email', 'value' => 'hello@learnwise.com', 'type' => 'text'],
        ['key' => 'whatsapp_number', 'value' => '+919876543210', 'type' => 'text'],
        ['key' => 'site_logo', 'value' => 'uploads/logos/site-logo.png', 'type' => 'image'],
        ['key' => 'logo', 'value' => 'uploads/logos/site-logo.png', 'type' => 'image'],
        ['key' => 'footer_text', 'value' => 'LearnWise helps families access premium online classes, clear progress tracking, and trusted educators in one beautifully simple experience.', 'type' => 'textarea'],
        ['key' => 'address', 'value' => '86 EdTech Lane, Mumbai, India', 'type' => 'textarea'],
        ['key' => 'social_links', 'value' => json_encode([
            ['label' => 'Instagram', 'url' => 'https://instagram.com'],
            ['label' => 'LinkedIn', 'url' => 'https://linkedin.com'],
            ['label' => 'YouTube', 'url' => 'https://youtube.com'],
        ], JSON_UNESCAPED_SLASHES), 'type' => 'json'],
        ['key' => 'site_name', 'value' => 'LearnWise', 'type' => 'text'],
        ['key' => 'site_tagline', 'value' => 'Premium online learning with expert teachers, flexible schedules, and parent-friendly progress tracking.', 'type' => 'text'],
        ['key' => 'favicon', 'value' => '', 'type' => 'image'],
        ['key' => 'nav_cta_text', 'value' => 'Book Free Trial', 'type' => 'text'],
        ['key' => 'nav_cta_link', 'value' => '#lead-form', 'type' => 'text'],
        ['key' => 'footer_legal_privacy', 'value' => 'privacy.php', 'type' => 'text'],
        ['key' => 'footer_legal_terms', 'value' => 'terms.php', 'type' => 'text'],
        ['key' => 'admin_notification_email', 'value' => 'hello@learnwise.com', 'type' => 'text'],
        ['key' => 'smtp_host', 'value' => '', 'type' => 'text'],
        ['key' => 'smtp_port', 'value' => '587', 'type' => 'text'],
        ['key' => 'smtp_username', 'value' => '', 'type' => 'text'],
        ['key' => 'smtp_password', 'value' => '', 'type' => 'text'],
        ['key' => 'smtp_encryption', 'value' => 'tls', 'type' => 'text'],
        ['key' => 'smtp_from_email', 'value' => 'noreply@learnwise.com', 'type' => 'text'],
        ['key' => 'smtp_from_name', 'value' => 'LearnWise Website', 'type' => 'text'],
    ];
    foreach ($defaultSettings as $setting) {
        lwEnsureSetting($pdo, $setting['key'], $setting['value'], $setting['type']);
    }

    $siteLogo = $pdo->query("SELECT setting_value FROM website_settings WHERE setting_key = 'site_logo' LIMIT 1")->fetchColumn();
    $legacyLogo = $pdo->query("SELECT setting_value FROM website_settings WHERE setting_key = 'logo' LIMIT 1")->fetchColumn();
    if (!empty($siteLogo) && empty($legacyLogo)) {
        lwSaveSetting($pdo, 'logo', (string) $siteLogo, 'image');
    }
    if (!empty($legacyLogo) && empty($siteLogo)) {
        lwSaveSetting($pdo, 'site_logo', (string) $legacyLogo, 'image');
    }

    $pages = [
        [
            'page_name' => 'home',
            'page_title' => 'Home',
            'meta_title' => 'LearnWise | Premium Learning for Modern Families',
            'meta_description' => 'Explore the LearnWise learning platform, flexible programs, trusted educators, and immersive online class experiences.',
            'og_image' => 'https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=1200&q=80',
            'status' => 'active',
        ],
        [
            'page_name' => 'about',
            'page_title' => 'About',
            'meta_title' => 'About LearnWise',
            'meta_description' => 'Learn why LearnWise was created and how our online-first teaching model supports students and parents.',
            'og_image' => '',
            'status' => 'active',
        ],
        [
            'page_name' => 'courses',
            'page_title' => 'Courses',
            'meta_title' => 'Courses | LearnWise',
            'meta_description' => 'Browse LearnWise course categories, enrichment tracks, and academic support programs.',
            'og_image' => '',
            'status' => 'active',
        ],
        [
            'page_name' => 'teachers',
            'page_title' => 'Teachers',
            'meta_title' => 'Teachers | LearnWise',
            'meta_description' => 'Meet the LearnWise teachers behind our interactive, high-impact online classes.',
            'og_image' => '',
            'status' => 'active',
        ],
        [
            'page_name' => 'faq',
            'page_title' => 'FAQ',
            'meta_title' => 'FAQ | LearnWise',
            'meta_description' => 'Answers to common questions about LearnWise classes, scheduling, recordings, and support.',
            'og_image' => '',
            'status' => 'active',
        ],
        [
            'page_name' => 'contact',
            'page_title' => 'Contact',
            'meta_title' => 'Contact LearnWise',
            'meta_description' => 'Talk with LearnWise about demos, admissions, family support, and partnerships.',
            'og_image' => '',
            'status' => 'active',
        ],
        [
            'page_name' => 'standards',
            'page_title' => 'Teaching Standards',
            'meta_title' => 'Teaching Standards | LearnWise',
            'meta_description' => 'Explore LearnWise teaching standards, educator best practices, and classroom compliance protocols.',
            'og_image' => '',
            'status' => 'active',
        ],
        [
            'page_name' => 'enroll',
            'page_title' => 'Enroll Now',
            'meta_title' => 'Enroll Now | LearnWise',
            'meta_description' => 'Start your child\'s learning journey with LearnWise. Book a free trial or enroll in live online classes today.',
            'og_image' => '',
            'status' => 'active',
        ],
        [
            'page_name' => 'privacy',
            'page_title' => 'Privacy Policy',
            'meta_title' => 'Privacy Policy | LearnWise',
            'meta_description' => 'Learn how LearnWise protects student and family data across our online learning platform.',
            'og_image' => '',
            'status' => 'active',
        ],
        [
            'page_name' => 'terms',
            'page_title' => 'Terms & Conditions',
            'meta_title' => 'Terms & Conditions | LearnWise',
            'meta_description' => 'Read the terms and conditions for using LearnWise online classes, enrollment, and platform services.',
            'og_image' => '',
            'status' => 'active',
        ],
        [
            'page_name' => 'teacher-registration',
            'page_title' => 'Teacher Registration',
            'meta_title' => 'Teacher Registration | LearnWise',
            'meta_description' => 'Apply to teach with LearnWise. Join our community of qualified educators delivering premium online classes.',
            'og_image' => '',
            'status' => 'active',
        ],
        [
            'page_name' => 'student-registration',
            'page_title' => 'Student Registration',
            'meta_title' => 'Student Registration | LearnWise',
            'meta_description' => 'Register as a student with LearnWise and access personalized live classes, homework support, and progress tracking.',
            'og_image' => '',
            'status' => 'active',
        ],
    ];

    $pageIds = [];
    foreach ($pages as $page) {
        $pageIds[$page['page_name']] = lwEnsurePage($pdo, $page);
    }

    if (lwColumnExists($pdo, 'page_sections', 'page_name')) {
        $legacySections = $pdo->query('SELECT * FROM page_sections')->fetchAll(PDO::FETCH_ASSOC);
        $updateLegacySection = $pdo->prepare('
            UPDATE page_sections
            SET page_id = :page_id,
                section_key = :section_key,
                section_title = :section_title,
                section_subtitle = :section_subtitle,
                section_content = :section_content,
                section_image = :section_image
            WHERE id = :id
        ');

        foreach ($legacySections as $legacySection) {
            $legacyPageName = (string) ($legacySection['page_name'] ?? '');
            $pageId = $pageIds[$legacyPageName] ?? null;
            if ($pageId === null) {
                continue;
            }

            $sectionKey = trim((string) ($legacySection['section_key'] ?? ''));
            if ($sectionKey === '') {
                $sectionKey = trim((string) ($legacySection['section_type'] ?? 'section-' . $legacySection['id']));
            }

            $updateLegacySection->execute([
                ':page_id' => $pageId,
                ':section_key' => $sectionKey,
                ':section_title' => (string) ($legacySection['section_title'] ?? $legacySection['title'] ?? ''),
                ':section_subtitle' => (string) ($legacySection['section_subtitle'] ?? $legacySection['subtitle'] ?? ''),
                ':section_content' => (string) ($legacySection['section_content'] ?? $legacySection['content'] ?? ''),
                ':section_image' => (string) ($legacySection['section_image'] ?? $legacySection['image'] ?? ''),
                ':id' => (int) $legacySection['id'],
            ]);
        }
    }

    $menus = [
        ['menu_name' => 'Home', 'menu_link' => 'index.php', 'sort_order' => 1, 'status' => 'active'],
        ['menu_name' => 'Courses', 'menu_link' => 'courses.php', 'sort_order' => 2, 'status' => 'active'],
        ['menu_name' => 'Teachers', 'menu_link' => 'teachers.php', 'sort_order' => 3, 'status' => 'active'],
        ['menu_name' => 'Teaching Standards', 'menu_link' => 'standards.php', 'sort_order' => 4, 'status' => 'active'],
        ['menu_name' => 'About', 'menu_link' => 'about.php', 'sort_order' => 5, 'status' => 'active'],
        ['menu_name' => 'FAQ', 'menu_link' => 'faq.php', 'sort_order' => 6, 'status' => 'active'],
        ['menu_name' => 'Contact', 'menu_link' => 'contact.php', 'sort_order' => 7, 'status' => 'active'],
        ['menu_name' => 'Enroll Now', 'menu_link' => 'enroll.php', 'sort_order' => 8, 'status' => 'active'],
    ];
    foreach ($menus as $menu) {
        lwEnsureMenu($pdo, $menu);
    }

    $homeSections = [
        [
            'section_key' => 'hero',
            'section_title' => 'Where every child learns with confidence and care',
            'section_subtitle' => 'Live online classes with expert teachers, personalized learning plans, and transparent parent updates — designed for modern families.',
            'section_content' => "Book Free Trial|#lead-form\nEnroll Now|enroll.php\nContact Us|contact.php",
            'section_image' => 'https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=1200&q=80',
            'section_type' => 'hero',
            'section_settings' => json_encode([
                'badge' => 'Premium Online Learning',
                'proof_lines' => "Personalized learning plans|bi-stars\nParent progress reports|bi-graph-up-arrow\nFlexible family schedules|bi-calendar2-week",
                'stats_lines' => "500+|Expert teachers\n6+|Subject categories\nFree|Trial class",
            ], JSON_UNESCAPED_SLASHES),
            'sort_order' => 1,
            'status' => 'active',
        ],
        [
            'section_key' => 'why_learnwise',
            'section_title' => 'Why families choose LearnWise',
            'section_subtitle' => 'A complete learning experience built around your child\'s goals, pace, and potential.',
            'section_content' => "Personalized Learning|Custom lesson plans tailored to each student's strengths and growth areas.|bi-person-check\nQualified Teachers|Experienced educators vetted for subject expertise and classroom excellence.|bi-mortarboard\nGoogle Meet Classes|Secure, interactive live sessions with screen sharing and real-time engagement.|bi-camera-video\nProgress Tracking|Clear dashboards and milestone updates so families always know how learning is going.|bi-graph-up-arrow\nHomework Management|Structured assignments with timely feedback to reinforce every lesson.|bi-journal-text\nParent Reports|Regular performance summaries with actionable insights and next-step guidance.|bi-envelope-paper\nFlexible Scheduling|Classes that fit school routines, time zones, and family calendars.|bi-calendar2-week\nRecorded Sessions|Missed a class? Access recordings to review concepts anytime.|bi-play-circle",
            'section_image' => '',
            'section_type' => 'feature_grid',
            'section_settings' => json_encode(['columns' => 4, 'kicker' => 'Why LearnWise'], JSON_UNESCAPED_SLASHES),
            'sort_order' => 2,
            'status' => 'active',
        ],
        [
            'section_key' => 'course_categories',
            'section_title' => 'Explore our course categories',
            'section_subtitle' => 'From core academics to creative skills and exam preparation — find the right path for your child.',
            'section_content' => '',
            'section_image' => '',
            'section_type' => 'courses_grid',
            'section_settings' => json_encode(['kicker' => 'Programs', 'cta_label' => 'Enroll Now', 'cta_link' => 'enroll.php'], JSON_UNESCAPED_SLASHES),
            'sort_order' => 3,
            'status' => 'active',
        ],
        [
            'section_key' => 'parent_trust_indicators',
            'section_title' => 'Trusted by families who value quality education',
            'section_subtitle' => 'LearnWise combines experienced educators, transparent communication, and measurable outcomes.',
            'section_content' => "4.9/5|Average parent satisfaction|bi-star-fill\n2,500+|Students learning with us|bi-people-fill\n150+|Qualified teachers|bi-mortarboard-fill\n100%|Safe online environment|bi-shield-check",
            'section_image' => '',
            'section_type' => 'trust',
            'section_settings' => json_encode(['kicker' => 'Parent Trust'], JSON_UNESCAPED_SLASHES),
            'sort_order' => 4,
            'status' => 'active',
        ],
        [
            'section_key' => 'parent_trust',
            'section_title' => 'Why parents trust LearnWise',
            'section_subtitle' => 'We partner with families through every step of the learning journey with clarity, care, and consistency.',
            'section_content' => "Experienced Teachers|Every educator is selected for subject mastery, communication, and student rapport.|bi-award\nPersonalized Learning Plans|Programs adapt to each child's pace, goals, and learning style.|bi-sliders\nContinuous Progress Tracking|Regular assessments and milestone reviews keep learning on track.|bi-bar-chart-line\nTransparent Communication|Quick responses via WhatsApp, email, and scheduled parent check-ins.|bi-chat-dots\nPerformance Reports|Detailed reports highlight strengths, gaps, and recommended next steps.|bi-file-earmark-text\nSafe Online Environment|Secure Google Meet classrooms with professional teaching standards.|bi-shield-lock",
            'section_image' => '',
            'section_type' => 'feature_grid',
            'section_settings' => json_encode(['columns' => 3, 'kicker' => 'Parent Trust', 'surface' => 'section-muted'], JSON_UNESCAPED_SLASHES),
            'sort_order' => 5,
            'status' => 'active',
        ],
        [
            'section_key' => 'studying_features',
            'section_title' => 'Studying with LearnWise',
            'section_subtitle' => 'An engaging, structured learning experience designed to help students thrive online.',
            'section_content' => "Interactive Classes|Live sessions with polls, discussions, and hands-on problem solving.|bi-lightning-charge\nHomework Assignments|Practice tasks with teacher feedback to reinforce every concept.|bi-journal-check\nPerformance Tracking|Students and parents can monitor progress against clear learning goals.|bi-speedometer2\nGoogle Meet Learning|High-quality video classes with screen sharing and collaborative tools.|bi-camera-video\nClass Recordings|Review sessions anytime to strengthen understanding.|bi-play-btn\nOne-on-One Attention|Small groups and individual focus when students need extra support.|bi-person-hearts",
            'section_image' => '',
            'section_type' => 'feature_grid',
            'section_settings' => json_encode(['columns' => 3, 'kicker' => 'Student Experience'], JSON_UNESCAPED_SLASHES),
            'sort_order' => 6,
            'status' => 'active',
        ],
        [
            'section_key' => 'studying_videos',
            'section_title' => 'See learning in action',
            'section_subtitle' => 'Preview sample classes managed directly from your CMS video library.',
            'section_content' => '',
            'section_image' => '',
            'section_type' => 'videos',
            'section_settings' => json_encode(['source' => 'sample_videos', 'kicker' => 'Class Previews'], JSON_UNESCAPED_SLASHES),
            'sort_order' => 7,
            'status' => 'active',
        ],
        [
            'section_key' => 'testimonials',
            'section_title' => 'What families are saying',
            'section_subtitle' => 'Real stories from parents and students who learn with LearnWise every day.',
            'section_content' => "Mrs. Sharma|Our daughter's confidence in math improved within weeks. The teachers are patient, prepared, and genuinely invested.|https://images.unsplash.com/photo-1438761681033-6461ffad8d80?auto=format&fit=crop&w=300&q=80|Parent\nArjun, Grade 9|Classes feel interactive and easy to follow. I can revisit recordings and stay on top of homework without stress.|https://images.unsplash.com/photo-1544717297-fa95b6ee9643?auto=format&fit=crop&w=300&q=80|Student\nThe Mehta Family|Progress updates are clear, communication is fast, and scheduling works around our routine. Highly recommended.|https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=300&q=80|Parent",
            'section_image' => '',
            'section_type' => 'testimonials',
            'section_settings' => json_encode(['kicker' => 'Testimonials'], JSON_UNESCAPED_SLASHES),
            'sort_order' => 8,
            'status' => 'active',
        ],
        // [
        //     'section_key' => 'teachers_showcase',
        //     'section_title' => 'Meet our expert teachers',
        //     'section_subtitle' => 'Passionate educators committed to helping every student succeed.',
        //     'section_content' => '',
        //     'section_image' => '',
        //     'section_type' => 'teachers_grid',
        //     'section_settings' => json_encode(['kicker' => 'Our Faculty', 'surface' => 'section-muted'], JSON_UNESCAPED_SLASHES),
        //     'sort_order' => 9,
        //     'status' => 'active',
        // ],
        [
            'section_key' => 'faq',
            'section_title' => 'Frequently asked questions',
            'section_subtitle' => 'Quick answers to help you get started with confidence.',
            'section_content' => "How are classes conducted?|Students join secure live Google Meet sessions with their teacher, with homework and follow-up support after class.\nCan we book a free trial first?|Yes. Every family can request a free trial class before enrolling in a full program.\nWhat subjects do you offer?|LearnWise covers Mathematics, Science, Coding, Languages, Arts, and Test Preparation.\nHow do parents track progress?|You receive regular performance reports, teacher updates, and milestone summaries through our parent communication channels.\nAre class recordings available?|Yes. Recorded sessions are shared so students can review lessons at their own pace.\nHow flexible is scheduling?|We work around school hours, time zones, and family routines to find class times that fit.",
            'section_image' => '',
            'section_type' => 'faq',
            'section_settings' => json_encode(['kicker' => 'FAQ'], JSON_UNESCAPED_SLASHES),
            'sort_order' => 10,
            'status' => 'active',
        ],
        [
            'section_key' => 'cta_banner',
            'section_title' => 'Start your learning journey today',
            'section_subtitle' => 'Join thousands of families who trust LearnWise for premium online education.',
            'section_content' => "Enroll Now|enroll.php\nContact Us|contact.php",
            'section_image' => '',
            'section_type' => 'cta_banner',
            'section_settings' => json_encode(['kicker' => 'Get Started'], JSON_UNESCAPED_SLASHES),
            'sort_order' => 11,
            'status' => 'active',
        ],
        [
            'section_key' => 'lead_form',
            'section_title' => 'Book your free trial class',
            'section_subtitle' => 'Tell us about your child and we\'ll match you with the right teacher and program.',
            'section_content' => "100% Free Trial Class\nNo Credit Card Required\nPersonalized Program Recommendation",
            'section_image' => '',
            'section_type' => 'lead_form',
            'section_settings' => json_encode(['source' => 'Home Page'], JSON_UNESCAPED_SLASHES),
            'sort_order' => 12,
            'status' => 'active',
        ],
    ];
    foreach ($homeSections as $section) {
        lwSeedSection($pdo, $pageIds['home'], $section);
    }

    $legacyHomeSectionMap = [
        'features' => 'why_learnwise',
        'why_parents_trust_us' => 'parent_trust',
        'studying_with_learnwise' => 'studying_videos',
    ];
    $deactivateLegacyHome = $pdo->prepare('
        UPDATE page_sections
        SET status = \'inactive\'
        WHERE page_id = :page_id
          AND section_key = :legacy_key
          AND EXISTS (
              SELECT 1 FROM (
                  SELECT id FROM page_sections
                  WHERE page_id = :page_id_check AND section_key = :replacement_key AND status = \'active\'
              ) AS replacement_sections
          )
    ');
    foreach ($legacyHomeSectionMap as $legacyKey => $replacementKey) {
        $deactivateLegacyHome->execute([
            ':page_id' => $pageIds['home'],
            ':legacy_key' => $legacyKey,
            ':page_id_check' => $pageIds['home'],
            ':replacement_key' => $replacementKey,
        ]);
    }

    $productionHomeKeys = [
        'hero', 'why_learnwise', 'course_categories', 'parent_trust_indicators', 'parent_trust',
        'studying_features', 'studying_videos', 'testimonials', 'teachers_showcase',
        'faq', 'cta_banner', 'lead_form',
    ];
    $placeholders = implode(',', array_fill(0, count($productionHomeKeys), '?'));
    $cleanupHome = $pdo->prepare("
        UPDATE page_sections
        SET status = 'inactive'
        WHERE page_id = ?
          AND section_key NOT IN ({$placeholders})
    ");
    $cleanupHome->execute(array_merge([(int) $pageIds['home']], $productionHomeKeys));

    $duplicateHomeSections = $pdo->prepare('
        UPDATE page_sections AS outer_sections
        INNER JOIN (
            SELECT section_key, MIN(id) AS keep_id
            FROM page_sections
            WHERE page_id = :page_id
            GROUP BY section_key
            HAVING COUNT(*) > 1
        ) AS duplicates ON outer_sections.section_key = duplicates.section_key
        SET outer_sections.status = \'inactive\'
        WHERE outer_sections.page_id = :page_id_filter
          AND outer_sections.id <> duplicates.keep_id
    ');
    $duplicateHomeSections->execute([
        ':page_id' => $pageIds['home'],
        ':page_id_filter' => $pageIds['home'],
    ]);

    $aboutSections = [
        [
            'section_key' => 'about_hero',
            'section_title' => 'LearnWise was built to make online learning feel more human',
            'section_subtitle' => 'We combine premium presentation, smart operations, and warm parent communication in one scalable website experience.',
            'section_content' => "Talk to Our Team|contact.php\nSee Courses|courses.php",
            'section_image' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=1200&q=80',
            'section_type' => 'hero',
            'section_settings' => json_encode(['badge' => 'About LearnWise'], JSON_UNESCAPED_SLASHES),
            'sort_order' => 1,
            'status' => 'active',
        ],
        [
            'section_key' => 'about_story',
            'section_title' => 'Our story',
            'section_subtitle' => 'A lightweight CMS should never feel lightweight to the families using it.',
            'section_content' => "LearnWise was designed for education businesses that want complete control over their marketing website without the overhead of a bloated CMS.\n\nEvery page, section, menu item, and homepage block can be managed from the admin panel so your team can publish updates fast.",
            'section_image' => '',
            'section_type' => 'rich_text',
            'section_settings' => '',
            'sort_order' => 2,
            'status' => 'active',
        ],
        [
            'section_key' => 'about_values',
            'section_title' => 'What we value',
            'section_subtitle' => 'Consistency, clarity, and learning experiences that families can genuinely trust.',
            'section_content' => "Thoughtful Support|Every inquiry matters, and every family should feel guided.|bi-heart\nBeautiful Simplicity|Clean interfaces reduce friction for both admins and visitors.|bi-window\nScalable Content|New sections, new pages, and new campaigns should be easy to launch.|bi-layers",
            'section_image' => '',
            'section_type' => 'feature_grid',
            'section_settings' => json_encode(['columns' => 3], JSON_UNESCAPED_SLASHES),
            'sort_order' => 3,
            'status' => 'active',
        ],
    ];
    foreach ($aboutSections as $section) {
        lwSeedSection($pdo, $pageIds['about'], $section);
    }

    $coursesSections = [
        [
            'section_key' => 'courses_hero',
            'section_title' => 'Programs designed for modern learners',
            'section_subtitle' => 'Your admin can manage the course list separately while still controlling the page layout through sections.',
            'section_content' => "Enroll Now|contact.php",
            'section_image' => 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=1200&q=80',
            'section_type' => 'hero',
            'section_settings' => json_encode(['badge' => 'Courses'], JSON_UNESCAPED_SLASHES),
            'sort_order' => 1,
            'status' => 'active',
        ],
        [
            'section_key' => 'courses_grid',
            'section_title' => 'Explore course categories',
            'section_subtitle' => 'Active courses below are loaded directly from the database.',
            'section_content' => '',
            'section_image' => '',
            'section_type' => 'courses_grid',
            'section_settings' => '',
            'sort_order' => 2,
            'status' => 'active',
        ],
        [
            'section_key' => 'courses_cta',
            'section_title' => 'Need help choosing the right program?',
            'section_subtitle' => 'We can recommend a learning path based on grade, subject, and goals.',
            'section_content' => 'Contact Sales|contact.php',
            'section_image' => '',
            'section_type' => 'cta_banner',
            'section_settings' => '',
            'sort_order' => 3,
            'status' => 'active',
        ],
    ];
    foreach ($coursesSections as $section) {
        lwSeedSection($pdo, $pageIds['courses'], $section);
    }

    $teachersSections = [
        [
            'section_key' => 'teachers_hero',
            'section_title' => 'Meet the teachers behind the experience',
            'section_subtitle' => 'Showcase your educators with profiles, experience, and student impact metrics.',
            'section_content' => "Book a Demo|contact.php",
            'section_image' => 'https://images.unsplash.com/photo-1544717305-2782549b5136?auto=format&fit=crop&w=1200&q=80',
            'section_type' => 'hero',
            'section_settings' => json_encode(['badge' => 'Our Faculty'], JSON_UNESCAPED_SLASHES),
            'sort_order' => 1,
            'status' => 'active',
        ],
        [
            'section_key' => 'teachers_grid',
            'section_title' => 'Our expert teachers',
            'section_subtitle' => 'Only active teacher profiles are shown here.',
            'section_content' => '',
            'section_image' => '',
            'section_type' => 'teachers_grid',
            'section_settings' => '',
            'sort_order' => 2,
            'status' => 'active',
        ],
    ];
    foreach ($teachersSections as $section) {
        lwSeedSection($pdo, $pageIds['teachers'], $section);
    }

    $faqSections = [
        [
            'section_key' => 'faq_hero',
            'section_title' => 'Questions families usually ask first',
            'section_subtitle' => 'The FAQ page is fully manageable through the sections system too.',
            'section_content' => "Need More Help?|contact.php",
            'section_image' => '',
            'section_type' => 'hero',
            'section_settings' => json_encode(['badge' => 'FAQ'], JSON_UNESCAPED_SLASHES),
            'sort_order' => 1,
            'status' => 'active',
        ],
        [
            'section_key' => 'faq_items',
            'section_title' => 'Frequently asked questions',
            'section_subtitle' => '',
            'section_content' => "How do demo classes work?|Families can request a free demo and meet the instructor before committing.\nCan videos be uploaded in the CMS?|Yes. Admins can upload thumbnails and MP4 files or use external URLs.\nCan sections be hidden without deleting them?|Yes. Set the section status to inactive and it will disappear from the frontend.",
            'section_image' => '',
            'section_type' => 'faq',
            'section_settings' => '',
            'sort_order' => 2,
            'status' => 'active',
        ],
    ];
    foreach ($faqSections as $section) {
        lwSeedSection($pdo, $pageIds['faq'], $section);
    }

    $contactSections = [
        [
            'section_key' => 'contact_hero',
            'section_title' => 'Talk to LearnWise',
            'section_subtitle' => 'Use this page for demos, enrollment help, and partnership conversations.',
            'section_content' => "WhatsApp|dynamic\nEmail Us|mailto:hello@learnwise.com",
            'section_image' => '',
            'section_type' => 'hero',
            'section_settings' => json_encode(['badge' => 'Contact'], JSON_UNESCAPED_SLASHES),
            'sort_order' => 1,
            'status' => 'active',
        ],
        [
            'section_key' => 'contact_form',
            'section_title' => 'Send us your details',
            'section_subtitle' => 'We will get back to you with the right next step.',
            'section_content' => '',
            'section_image' => '',
            'section_type' => 'contact_form',
            'section_settings' => json_encode(['source' => 'Contact Page'], JSON_UNESCAPED_SLASHES),
            'sort_order' => 2,
            'status' => 'active',
        ],
    ];
    foreach ($contactSections as $section) {
        lwSeedSection($pdo, $pageIds['contact'], $section);
    }

    $enrollSections = [
        [
            'section_key' => 'enroll_hero',
            'section_title' => 'Enroll your child with confidence',
            'section_subtitle' => 'Choose a program, book a free trial, or speak with our team to find the perfect learning path.',
            'section_content' => "Book Free Trial|#enroll-form\nContact Us|contact.php",
            'section_image' => 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=1200&q=80',
            'section_type' => 'hero',
            'section_settings' => json_encode(['badge' => 'Enroll Now'], JSON_UNESCAPED_SLASHES),
            'sort_order' => 1,
            'status' => 'active',
        ],
        [
            'section_key' => 'enroll-form',
            'section_title' => 'Complete your enrollment request',
            'section_subtitle' => 'Share your details and our academic team will guide you through the next steps.',
            'section_content' => "Free trial class included\nFlexible scheduling options\nDedicated academic advisor",
            'section_image' => '',
            'section_type' => 'lead_form',
            'section_settings' => json_encode(['source' => 'Enroll Page'], JSON_UNESCAPED_SLASHES),
            'sort_order' => 2,
            'status' => 'active',
        ],
    ];
    foreach ($enrollSections as $section) {
        lwSeedSection($pdo, $pageIds['enroll'], $section);
    }

    $privacySections = [
        [
            'section_key' => 'privacy_hero',
            'section_title' => 'Privacy Policy',
            'section_subtitle' => 'How LearnWise collects, uses, and protects your family\'s information.',
            'section_content' => '',
            'section_image' => '',
            'section_type' => 'hero',
            'section_settings' => json_encode(['badge' => 'Legal'], JSON_UNESCAPED_SLASHES),
            'sort_order' => 1,
            'status' => 'active',
        ],
        [
            'section_key' => 'privacy_content',
            'section_title' => 'Your privacy matters',
            'section_subtitle' => '',
            'section_content' => "LearnWise is committed to protecting the privacy of students, parents, and educators who use our platform.\n\nWe collect only the information necessary to deliver classes, communicate with families, and improve our services. This may include names, contact details, academic preferences, and class participation data.\n\nWe do not sell personal information to third parties. Data is stored securely and accessed only by authorized team members who need it to support your learning experience.\n\nClass sessions may be recorded for educational purposes with prior consent. Recordings are shared only with enrolled students and their parents or guardians.\n\nYou may request access to, correction of, or deletion of your personal data by contacting us at the email address listed on our Contact page.\n\nThis policy may be updated periodically. Continued use of LearnWise services constitutes acceptance of the current policy.",
            'section_image' => '',
            'section_type' => 'rich_text',
            'section_settings' => '',
            'sort_order' => 2,
            'status' => 'active',
        ],
    ];
    foreach ($privacySections as $section) {
        lwSeedSection($pdo, $pageIds['privacy'], $section);
    }

    $termsSections = [
        [
            'section_key' => 'terms_hero',
            'section_title' => 'Terms & Conditions',
            'section_subtitle' => 'Please read these terms carefully before using LearnWise services.',
            'section_content' => '',
            'section_image' => '',
            'section_type' => 'hero',
            'section_settings' => json_encode(['badge' => 'Legal'], JSON_UNESCAPED_SLASHES),
            'sort_order' => 1,
            'status' => 'active',
        ],
        [
            'section_key' => 'terms_content',
            'section_title' => 'Terms of use',
            'section_subtitle' => '',
            'section_content' => "By accessing LearnWise classes, website, or enrollment services, you agree to these Terms & Conditions.\n\nEnrollment confirms acceptance of class schedules, fee structures, and communication policies shared during onboarding.\n\nStudents and parents agree to maintain respectful conduct during live sessions and follow teacher guidance for a productive learning environment.\n\nMissed classes, rescheduling, and refund policies are communicated at enrollment and may vary by program.\n\nLearnWise reserves the right to update programs, pricing, and platform features with reasonable notice to enrolled families.\n\nTeachers and staff are expected to follow LearnWise teaching standards and compliance protocols at all times.\n\nFor questions about these terms, please contact our support team through the Contact page.",
            'section_image' => '',
            'section_type' => 'rich_text',
            'section_settings' => '',
            'sort_order' => 2,
            'status' => 'active',
        ],
    ];
    foreach ($termsSections as $section) {
        lwSeedSection($pdo, $pageIds['terms'], $section);
    }

    $teacherRegSections = [
        [
            'section_key' => 'teacher_reg_hero',
            'section_title' => 'Teach with LearnWise',
            'section_subtitle' => 'Join a community of educators delivering premium online learning experiences.',
            'section_content' => "View Standards|standards.php",
            'section_image' => 'https://images.unsplash.com/photo-1544717305-2782549b5136?auto=format&fit=crop&w=1200&q=80',
            'section_type' => 'hero',
            'section_settings' => json_encode(['badge' => 'Teacher Registration'], JSON_UNESCAPED_SLASHES),
            'sort_order' => 1,
            'status' => 'active',
        ],
        [
            'section_key' => 'teacher_reg_form',
            'section_title' => 'Apply to become a LearnWise teacher',
            'section_subtitle' => 'Share your experience and subject expertise. Our team will review your application and reach out.',
            'section_content' => "Competitive compensation\nFlexible online teaching\nProfessional development support",
            'section_image' => '',
            'section_type' => 'lead_form',
            'section_settings' => json_encode(['source' => 'Teacher Registration'], JSON_UNESCAPED_SLASHES),
            'sort_order' => 2,
            'status' => 'active',
        ],
    ];
    foreach ($teacherRegSections as $section) {
        lwSeedSection($pdo, $pageIds['teacher-registration'], $section);
    }

    $studentRegSections = [
        [
            'section_key' => 'student_reg_hero',
            'section_title' => 'Student registration',
            'section_subtitle' => 'Register to access live classes, homework support, and progress tracking with LearnWise.',
            'section_content' => "View Courses|courses.php",
            'section_image' => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=1200&q=80',
            'section_type' => 'hero',
            'section_settings' => json_encode(['badge' => 'Student Registration'], JSON_UNESCAPED_SLASHES),
            'sort_order' => 1,
            'status' => 'active',
        ],
        [
            'section_key' => 'student_reg_form',
            'section_title' => 'Create your student profile',
            'section_subtitle' => 'A parent or guardian should complete this form to begin the registration process.',
            'section_content' => "Free trial available\nAll major subjects covered\nParent progress reports included",
            'section_image' => '',
            'section_type' => 'lead_form',
            'section_settings' => json_encode(['source' => 'Student Registration'], JSON_UNESCAPED_SLASHES),
            'sort_order' => 2,
            'status' => 'active',
        ],
    ];
    foreach ($studentRegSections as $section) {
        lwSeedSection($pdo, $pageIds['student-registration'], $section);
    }

    $standardsSections = [
        [
            'section_key' => 'standards_hero',
            'section_title' => 'Online Teaching Standards & Best Practices',
            'section_subtitle' => 'Delivering high-quality learning experiences with professionalism, care, and impact.',
            'section_content' => 'A premium framework that helps LearnWise educators create trusted, engaging, and high-performing online classrooms.',
            'section_image' => 'https://images.unsplash.com/photo-1513258496099-48168024aec0?auto=format&fit=crop&w=900&q=80',
            'section_type' => 'hero',
            'section_settings' => json_encode(['badge' => 'Premium Teaching Framework', 'button_text' => 'Join LearnWise', 'button_link' => 'contact.php'], JSON_UNESCAPED_SLASHES),
            'sort_order' => 1,
            'status' => 'active',
        ],
    ];
    foreach ($standardsSections as $section) {
        lwSeedSection($pdo, $pageIds['standards'], $section);
    }

    $defaultVideos = [
        [
            'title' => 'Live Class Walkthrough',
            'description' => 'A quick look at how a LearnWise sample class feels for students and parents.',
            'thumbnail' => 'https://img.youtube.com/vi/xcm0N9oQia4/hqdefault.jpg',
            'video_file' => '',
            'video_url' => 'https://www.youtube.com/watch?v=xcm0N9oQia4',
            'sort_order' => 1,
            'status' => 'active',
        ],
        [
            'title' => 'Competition Session',
            'description' => 'A high-energy class moment designed to keep learners engaged.',
            'thumbnail' => 'https://img.youtube.com/vi/g-x-aBLFR3k/hqdefault.jpg',
            'video_file' => '',
            'video_url' => 'https://www.youtube.com/watch?v=g-x-aBLFR3k',
            'sort_order' => 2,
            'status' => 'active',
        ],
        [
            'title' => 'Language Class Preview',
            'description' => 'A sample from one of our interactive language learning sessions.',
            'thumbnail' => 'https://img.youtube.com/vi/6TuiKjeQkBU/hqdefault.jpg',
            'video_file' => '',
            'video_url' => 'https://www.youtube.com/watch?v=6TuiKjeQkBU',
            'sort_order' => 3,
            'status' => 'active',
        ],
    ];
    foreach ($defaultVideos as $video) {
        lwEnsureSampleVideo($pdo, $video);
    }

    $defaultStandards = [
        ['title' => 'Student & Parent Relationships', 'content' => 'Build strong rapport through friendliness, respect, and confident, solution-oriented interactions.', 'icon' => 'bi-people', 'sort_order' => 1, 'status' => 'active'],
        ['title' => 'Lesson Delivery', 'content' => 'Be well prepared, use simple examples, and adapt teaching pace to suit every learner.', 'icon' => 'bi-journal-richtext', 'sort_order' => 2, 'status' => 'active'],
        ['title' => 'Student Engagement', 'content' => 'Encourage participation through active listening, age-appropriate interaction, and flexible teaching methods.', 'icon' => 'bi-lightning-charge', 'sort_order' => 3, 'status' => 'active'],
        ['title' => 'Professional Discipline', 'content' => 'Maintain punctuality, manage time effectively, and follow structured protocols for attendance and delays.', 'icon' => 'bi-shield-check', 'sort_order' => 4, 'status' => 'active'],
        ['title' => 'Communication Protocols', 'content' => 'Stay responsive and transparent with students, parents, and the LearnWise administration team.', 'icon' => 'bi-chat-dots', 'sort_order' => 5, 'status' => 'active'],
        ['title' => 'Accountability', 'content' => 'Track lessons, maintain accurate records, and follow up on student progress consistently.', 'icon' => 'bi-clipboard-data', 'sort_order' => 6, 'status' => 'active'],
        ['title' => 'Background Standards', 'content' => 'Use a clean, professional, and distraction-free teaching environment for every session.', 'icon' => 'bi-camera-video', 'sort_order' => 7, 'status' => 'active'],
        ['title' => 'Punctuality', 'content' => 'Join classes before the scheduled start time and be fully prepared when students arrive.', 'icon' => 'bi-clock-history', 'sort_order' => 8, 'status' => 'active'],
        ['title' => 'Avoid No-Shows', 'content' => 'Inform administration in advance if you cannot attend a scheduled class.', 'icon' => 'bi-exclamation-triangle', 'sort_order' => 9, 'status' => 'active'],
        ['title' => 'Notice Period', 'content' => 'Follow notice guidelines when changing availability or concluding teaching assignments.', 'icon' => 'bi-calendar-event', 'sort_order' => 10, 'status' => 'active'],
        ['title' => 'Dress Code', 'content' => 'Maintain a professional appearance appropriate for live online teaching with students and parents.', 'icon' => 'bi-person-badge', 'sort_order' => 11, 'status' => 'active'],
    ];
    $upsertStandard = $pdo->prepare('SELECT id FROM standards_sections WHERE title = :title LIMIT 1');
    $insertStandard = $pdo->prepare('INSERT INTO standards_sections (title, content, icon, sort_order, status) VALUES (:title, :content, :icon, :sort_order, :status)');
    $updateStandard = $pdo->prepare('UPDATE standards_sections SET content = :content, icon = :icon, sort_order = :sort_order, status = :status WHERE title = :title');
    foreach ($defaultStandards as $standard) {
        $upsertStandard->execute([':title' => $standard['title']]);
        if ($upsertStandard->fetchColumn()) {
            $updateStandard->execute($standard);
        } else {
            $insertStandard->execute($standard);
        }
    }

    $defaultRules = [
        ['title' => 'Student No Show', 'content' => "Wait 3 minutes for the student to join\nSend WhatsApp message at the 4th minute\nCall the parent at the 6th minute\nEnd session after 15 minutes if unresolved", 'icon' => 'bi-person-x', 'penalty' => '', 'sort_order' => 1, 'status' => 'active'],
        ['title' => 'Teacher No Show', 'content' => "Cancel at least 1 hour before the scheduled class\nInform administration and the family immediately\nDocument the reason in class records", 'icon' => 'bi-calendar-x', 'penalty' => '₹250 penalty if missed', 'sort_order' => 2, 'status' => 'active'],
        ['title' => 'Punctuality', 'content' => "Join within 3 minutes of scheduled start time\nCommunicate delays to administration promptly", 'icon' => 'bi-alarm', 'penalty' => '₹50 penalty after grace period', 'sort_order' => 3, 'status' => 'active'],
        ['title' => 'Late Join', 'content' => "Extend class duration to compensate for teacher late arrival\nEnsure students receive full allotted learning time", 'icon' => 'bi-hourglass-split', 'penalty' => '', 'sort_order' => 4, 'status' => 'active'],
        ['title' => 'Important Discussions', 'content' => "Keep the Director informed of significant student or parent concerns\nEscalate issues that affect learning outcomes or safety", 'icon' => 'bi-megaphone', 'penalty' => '', 'sort_order' => 5, 'status' => 'active'],
        ['title' => 'Notice Period', 'content' => "Provide minimum 1 month notice before leaving the platform\nComplete all pending classes and handover documentation", 'icon' => 'bi-file-earmark-text', 'penalty' => '', 'sort_order' => 6, 'status' => 'active'],
    ];
    $upsertRule = $pdo->prepare('SELECT id FROM compliance_rules WHERE title = :title LIMIT 1');
    $insertRule = $pdo->prepare('INSERT INTO compliance_rules (title, content, icon, penalty, sort_order, status) VALUES (:title, :content, :icon, :penalty, :sort_order, :status)');
    $updateRule = $pdo->prepare('UPDATE compliance_rules SET content = :content, icon = :icon, penalty = :penalty, sort_order = :sort_order, status = :status WHERE title = :title');
    foreach ($defaultRules as $rule) {
        $upsertRule->execute([':title' => $rule['title']]);
        if ($upsertRule->fetchColumn()) {
            $updateRule->execute($rule);
        } else {
            $insertRule->execute($rule);
        }
    }

    $pdo->exec("DELETE FROM compliance_rules WHERE title IN ('Student No-Show', 'Teacher No-Show')");

    if ((int) $pdo->query('SELECT COUNT(*) FROM teachers')->fetchColumn() === 0) {
        $defaultTeachers = [
            ['name' => 'Ananya Singh', 'subject' => 'Mathematics', 'experience' => '8 years', 'experience_years' => 8, 'students_count' => 400, 'qualifications' => 'M.Sc. Mathematics, B.Ed.', 'bio' => 'Known for clear explanations and calm, confidence-building classes.', 'image' => '', 'status' => 'active'],
            ['name' => 'Rahul Verma', 'subject' => 'Coding', 'experience' => '6 years', 'experience_years' => 6, 'students_count' => 320, 'qualifications' => 'B.Tech Computer Science', 'bio' => 'Helps learners move from curiosity to real project confidence.', 'image' => '', 'status' => 'active'],
            ['name' => 'Priya Joshi', 'subject' => 'Science', 'experience' => '7 years', 'experience_years' => 7, 'students_count' => 360, 'qualifications' => 'M.Sc. Physics, CTET Qualified', 'bio' => 'Makes difficult science concepts feel accessible and memorable.', 'image' => '', 'status' => 'active'],
        ];
        $insertTeacher = $pdo->prepare('
            INSERT INTO teachers (name, subject, experience, experience_years, students_count, qualifications, bio, image, status)
            VALUES (:name, :subject, :experience, :experience_years, :students_count, :qualifications, :bio, :image, :status)
        ');
        foreach ($defaultTeachers as $teacher) {
            $insertTeacher->execute($teacher);
        }
    }

    if ((int) $pdo->query('SELECT COUNT(*) FROM courses')->fetchColumn() === 0) {
        $defaultCourses = [
            ['title' => 'Mathematics Mastery', 'description' => 'From fundamentals to advanced problem solving with personalized support for every grade level.', 'category' => 'mathematics', 'image' => '', 'sort_order' => 1, 'status' => 'active'],
            ['title' => 'Science Explorer', 'description' => 'Interactive physics, chemistry, and biology lessons with experiments and concept mastery.', 'category' => 'science', 'image' => '', 'sort_order' => 2, 'status' => 'active'],
            ['title' => 'Coding & Technology', 'description' => 'Build real programming skills through live projects, logic building, and creative problem solving.', 'category' => 'coding', 'image' => '', 'sort_order' => 3, 'status' => 'active'],
            ['title' => 'Languages & Communication', 'description' => 'Develop fluency in English and world languages through speaking, writing, and comprehension practice.', 'category' => 'languages', 'image' => '', 'sort_order' => 4, 'status' => 'active'],
            ['title' => 'Creative Arts', 'description' => 'Explore drawing, design, and creative expression with guided projects and skill development.', 'category' => 'arts', 'image' => '', 'sort_order' => 5, 'status' => 'active'],
            ['title' => 'Test Preparation', 'description' => 'Focused coaching for school exams, board tests, and competitive entrance preparation.', 'category' => 'test prep', 'image' => '', 'sort_order' => 6, 'status' => 'active'],
        ];
        $insertCourse = $pdo->prepare('
            INSERT INTO courses (title, description, category, image, sort_order, status)
            VALUES (:title, :description, :category, :image, :sort_order, :status)
        ');
        foreach ($defaultCourses as $course) {
            $insertCourse->execute($course);
        }
    }
}

try {
    $pdo = new PDO("mysql:host={$dbHost};charset=utf8mb4", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `{$dbName}`");
    lwInitializeDatabase($pdo);
    $GLOBALS['pdo'] = $pdo;
} catch (PDOException $exception) {
    lwReportException($exception, ['area' => 'database_connection']);
    lwAbortRequest();
}
