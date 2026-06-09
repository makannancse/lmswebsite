<?php
$currentPage = $currentPage ?? '';
$menuItems = getMenus();
$logo = getSiteLogo();
$siteName = getSetting('site_name', 'LearnWise');
$navCtaText = getSetting('nav_cta_text', 'Book Free Trial');
$navCtaLink = getSetting('nav_cta_link', '#lead-form');
?>
<nav class="navbar site-navbar navbar-expand-lg navbar-light sticky-top py-3">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <?php if ($logo !== ''): ?>
                <img src="<?= htmlspecialchars($logo) ?>" alt="<?= htmlspecialchars($siteName) ?> logo" class="nav-logo me-2" loading="eager" decoding="async" width="42" height="42">
            <?php else: ?>
                <span class="brand-mark">LW</span>
            <?php endif; ?>
            <span class="ms-2 brand-title"><?= htmlspecialchars($siteName) ?></span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                <?php foreach ($menuItems as $menu):
                    $menuPage = basename((string) $menu['menu_link'], '.php');
                    $menuPage = $menuPage === 'index' ? 'home' : $menuPage;
                ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage === $menuPage ? 'active' : '' ?>" href="<?= htmlspecialchars($menu['menu_link']) ?>">
                            <?= htmlspecialchars($menu['menu_name']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="d-flex gap-2">
                <a href="enroll.php" class="btn btn-outline-primary d-none d-lg-inline-flex px-3">Enroll</a>
                <a href="<?= htmlspecialchars($navCtaLink) ?>" class="btn btn-primary btn-enroll px-4"><?= htmlspecialchars($navCtaText) ?></a>
            </div>
        </div>
    </div>
</nav>
