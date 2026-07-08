<?php
$year = date('Y');
$footerMenus = getMenus();
$socialLinks = getSocialLinks();
$footerLogo = getDisplayLogo();
$privacyLink = getSetting('footer_legal_privacy', 'privacy.php');
$termsLink = getSetting('footer_legal_terms', 'terms.php');
?>
<footer class="site-footer footer-dark py-5">
    <div class="container">
        <div class="row gy-4">
            <div class="col-lg-4 col-md-6">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <?php if ($footerLogo !== ''): ?>
                        <img src="<?= htmlspecialchars($footerLogo) ?>" alt="<?= htmlspecialchars(getSetting('site_name', 'LearnWise')) ?> logo" class="footer-logo" loading="lazy" width="190" height="65">
                    <?php else: ?>
                        <span class="brand-mark">LW</span>
                    <?php endif; ?>
                </div>
                <p><?= htmlspecialchars(getSetting('footer_text', 'Premium online learning with expert teachers, flexible schedules, and parent-friendly progress tracking.')) ?></p>
            </div>
            <div class="col-lg-2 col-md-6">
                <h6 class="footer-title">Explore</h6>
                <ul class="list-unstyled footer-links">
                    <?php foreach ($footerMenus as $menu): ?>
                        <li><a href="<?= htmlspecialchars($menu['menu_link']) ?>"><?= htmlspecialchars($menu['menu_name']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="col-lg-3 col-md-6">
                <h6 class="footer-title">Contact</h6>
                <p class="mb-1"><strong>Email:</strong> <a href="mailto:<?= htmlspecialchars(getSetting('site_email', 'hello@learnwise.com')) ?>"><?= htmlspecialchars(getSetting('site_email', 'hello@learnwise.com')) ?></a></p>
                <p class="mb-1"><strong>Phone:</strong> <a href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', getSetting('site_phone'))) ?>"><?= htmlspecialchars(getSetting('site_phone')) ?></a></p>
                <p class="mb-0"><?= nl2br(htmlspecialchars(getSetting('address'))) ?></p>
            </div>
            <div class="col-lg-3 col-md-6">
                <h6 class="footer-title">Follow Us</h6>
                <div class="social-links d-flex flex-column gap-2 mb-4">
                    <?php foreach ($socialLinks as $social): ?>
                        <a href="<?= htmlspecialchars($social['url'] ?? '#') ?>" target="_blank" rel="noopener noreferrer">
                            <?= htmlspecialchars($social['label'] ?? 'Social') ?>
                        </a>
                    <?php endforeach; ?>
                </div>
                <h6 class="footer-title">Legal</h6>
                <ul class="list-unstyled footer-links">
                    <li><a href="<?= htmlspecialchars($privacyLink) ?>">Privacy Policy</a></li>
                    <li><a href="<?= htmlspecialchars($termsLink) ?>">Terms & Conditions</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom text-center mt-4 pt-4">
            <p class="small text-secondary mb-0">&copy; <?= $year ?> <?= htmlspecialchars(getSetting('site_name', 'LearnWise')) ?>. All rights reserved.</p>
        </div>
    </div>
</footer>
<?php
$assetBase = lwGetAppBasePath();
include __DIR__ . '/whatsapp.php';
?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= htmlspecialchars($assetBase) ?>/assets/js/script.js?v=20260609"></script>
</body>
</html>
