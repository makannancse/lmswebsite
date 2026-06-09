<?php
require 'includes/db.php';
require 'includes/site.php';

$meta = getPageMeta('standards');
$pageSections = getPageSectionMap('standards');
$standards = getStandardsSections();
$complianceRules = getComplianceRules();
$hero = normalizePageSection($pageSections['standards_hero'] ?? [
    'section_title' => 'Online Teaching Standards & Best Practices',
    'section_subtitle' => 'Delivering high-quality learning experiences with professionalism, care, and impact.',
    'section_content' => 'A premium framework that helps LearnWise educators create trusted, engaging, and high-performing online classrooms.',
    'section_image' => 'https://images.unsplash.com/photo-1513258496099-48168024aec0?auto=format&fit=crop&w=900&q=80',
]);

$pageTitle = $meta['title'];
$pageDescription = $meta['description'];
$pageOgImage = $meta['og_image'];
$currentPage = 'standards';
include 'includes/header.php';
include 'includes/navbar.php';
?>
<main class="standards-page teacher-dashboard-page">
    <section class="standards-hero teacher-dashboard-hero">
        <div class="container">
            <div class="row align-items-center gy-5">
                <div class="col-lg-6">
                    <span class="section-kicker">Teacher Dashboard</span>
                    <h1 class="hero-title mt-4 mb-3"><?= htmlspecialchars($hero['title'] ?? '') ?></h1>
                    <p class="hero-copy mb-4"><?= htmlspecialchars($hero['subtitle'] ?? '') ?></p>
                    <?php if (!empty($hero['content'])): ?>
                        <p class="standards-mission-copy mb-4"><?= htmlspecialchars($hero['content']) ?></p>
                    <?php endif; ?>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="teacher-registration.php" class="btn btn-primary btn-lg px-4">Join LearnWise</a>
                        <a href="contact.php" class="btn btn-outline-primary btn-lg px-4">Contact Admin</a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="teacher-dashboard-hero-card">
                        <img src="<?= htmlspecialchars($hero['image'] ?: 'https://images.unsplash.com/photo-1513258496099-48168024aec0?auto=format&fit=crop&w=900&q=80') ?>" alt="<?= htmlspecialchars($hero['title'] ?? 'Teaching Standards') ?>" class="img-fluid rounded-4" loading="lazy" decoding="async" width="600" height="420">
                        <div class="teacher-dashboard-hero-stat">
                            <strong><?= count($standards) ?></strong>
                            <span>Teaching standards for consistent excellence</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section-surface">
        <div class="container">
            <div class="section-heading text-center mx-auto">
                <span class="section-kicker">Best Practices</span>
                <h2 class="section-title mt-3">Online Teaching Standards</h2>
                <p class="section-subtitle mx-auto mt-3">Professional expectations for every LearnWise educator — from lesson delivery to parent communication.</p>
            </div>

            <div class="row g-4">
                <?php foreach ($standards as $standard): ?>
                    <div class="col-md-6 col-xl-4">
                        <article class="card teacher-standard-card h-100 border-0">
                            <div class="card-body p-4">
                                <div class="teacher-standard-icon mb-4">
                                    <i class="bi <?= htmlspecialchars($standard['icon'] ?: 'bi-award') ?>"></i>
                                </div>
                                <h3 class="h5 mb-3"><?= htmlspecialchars($standard['title']) ?></h3>
                                <p class="text-muted mb-0"><?= htmlspecialchars($standard['content']) ?></p>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="teacher-mission-banner mt-5">
                <div class="row align-items-center gy-3">
                    <div class="col-lg-8">
                        <h3 class="h4 mb-2">Our mission</h3>
                        <p class="mb-0">Deliver consistent, high-quality learning experiences with professionalism, care, and impact.</p>
                    </div>
                    <div class="col-lg-4 text-lg-end">
                        <a href="teacher-registration.php" class="btn btn-light">Apply to Teach</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section-muted">
        <div class="container">
            <div class="section-heading text-center mx-auto">
                <span class="section-kicker">Protocols</span>
                <h2 class="section-title mt-3">Class Compliance & Protocols</h2>
                <p class="section-subtitle mx-auto mt-3">Clear escalation steps, accountability measures, and professional guidelines for every live session.</p>
            </div>

            <div class="compliance-timeline compliance-timeline-premium">
                <?php foreach ($complianceRules as $index => $rule): ?>
                    <article class="compliance-item">
                        <div class="compliance-step">
                            <i class="bi <?= htmlspecialchars($rule['icon'] ?: 'bi-check2-circle') ?>"></i>
                        </div>
                        <div class="card compliance-card border-0">
                            <div class="card-body p-4 p-lg-5">
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                                    <h3 class="h5 mb-0"><?= htmlspecialchars($rule['title']) ?></h3>
                                    <?php if (!empty($rule['penalty'])): ?>
                                        <span class="penalty-pill"><?= htmlspecialchars($rule['penalty']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php $ruleLines = parseTextBlocks($rule['content'] ?? ''); ?>
                                <?php if (count($ruleLines) > 1): ?>
                                    <ul class="compliance-list mb-0">
                                        <?php foreach ($ruleLines as $lineIndex => $line): ?>
                                            <li>
                                                <span class="compliance-timeline-marker"><?= $lineIndex + 1 ?></span>
                                                <?= htmlspecialchars($line) ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <p class="text-muted mb-0"><?= htmlspecialchars($ruleLines[0] ?? '') ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>
<?php include 'includes/footer.php'; ?>
