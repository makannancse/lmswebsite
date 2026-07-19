<?php
$menuItems = [
    ['label' => 'Dashboard', 'page' => 'dashboard.php', 'icon' => 'bi-grid-1x2'],
    ['label' => 'Pages', 'page' => 'pages.php', 'icon' => 'bi-files'],
    ['label' => 'Sections', 'page' => 'sections.php', 'icon' => 'bi-layout-text-window'],
    ['label' => 'Menus', 'page' => 'menus.php', 'icon' => 'bi-list'],
    ['label' => 'Courses', 'page' => 'courses.php', 'icon' => 'bi-book'],
    ['label' => 'Teachers', 'page' => 'teachers.php', 'icon' => 'bi-person-badge'],
    ['label' => 'Videos', 'page' => 'sample_videos.php', 'icon' => 'bi-camera-video'],
    ['label' => 'Standards', 'page' => 'standards.php', 'icon' => 'bi-shield-check'],
    ['label' => 'Testimonials', 'page' => 'testimonials.php', 'icon' => 'bi-chat-quote'],
    ['label' => 'Settings', 'page' => 'settings.php', 'icon' => 'bi-gear'],
    ['label' => 'Website Leads', 'page' => 'leads.php', 'icon' => 'bi-people'],
];
?>
<nav class="nav flex-column gap-2">
    <?php foreach ($menuItems as $item): ?>
        <a class="nav-link px-3 py-3 rounded-4 d-flex align-items-center gap-3 <?= basename($_SERVER['PHP_SELF']) === $item['page'] ? 'active bg-primary-subtle' : '' ?>" href="<?= htmlspecialchars($item['page']) ?>">
            <i class="bi <?= htmlspecialchars($item['icon']) ?>"></i>
            <span><?= htmlspecialchars($item['label']) ?></span>
        </a>
    <?php endforeach; ?>
</nav>
