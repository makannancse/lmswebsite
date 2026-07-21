<?php
$heroSettings = getSectionSettings($section);
$heroActions = parseStructuredLines($section['content'] ?? '', 2);

if ($heroActions === []) {
    $heroActions = [
        ['Book Free Trial', '#lead-form'],
        ['Enroll Now', 'enroll.php'],
        ['Contact Us', 'contact.php'],
    ];
}

$heroBadge = trim((string) ($heroSettings['badge'] ?? ''));
$heroProof = $heroSettings['proof'] ?? null;
if (!is_array($heroProof) || $heroProof === []) {
    $heroProof = parseStructuredLines((string) ($heroSettings['proof_lines'] ?? ''), 2);
    $heroProof = array_map(static fn ($row) => ['icon' => $row[1] ?? 'bi-stars', 'text' => $row[0] ?? ''], $heroProof);
}
if ($heroProof === []) {
    $heroProof = [
        ['icon' => 'bi-stars', 'text' => 'Personalized learning plans for every child'],
        ['icon' => 'bi-graph-up-arrow', 'text' => 'Transparent progress reports for parents'],
        ['icon' => 'bi-calendar2-week', 'text' => 'Flexible schedules that fit your family'],
    ];
}

$heroStats = $heroSettings['stats'] ?? null;
if (!is_array($heroStats) || $heroStats === []) {
    $parsedStats = parseStructuredLines((string) ($heroSettings['stats_lines'] ?? ''), 2);
    $heroStats = array_map(static fn ($row) => ['value' => $row[0] ?? '', 'label' => $row[1] ?? ''], $parsedStats);
}

$hasHeroImage = !empty($section['image']);
?>
<section class="hero hero-premium">
    <div class="container">
        <div class="row align-items-center gy-5">
            <div class="<?= $hasHeroImage ? 'col-lg-6' : 'col-lg-9 col-xl-8' ?>">
                <div class="hero-copy-wrap">
                    <?php if ($heroBadge !== ''): ?>
                        <span class="section-kicker hero-kicker"><?= htmlspecialchars($heroBadge) ?></span>
                    <?php endif; ?>

                    <h1 class="hero-title mt-4 mb-3"><?= htmlspecialchars($section['title'] ?? '') ?></h1>

                    <?php if (!empty($section['subtitle'])): ?>
                        <p class="hero-copy mb-0"><?= htmlspecialchars($section['subtitle']) ?></p>
                    <?php endif; ?>

                    <div class="hero-actions d-flex flex-wrap gap-3">
                        <?php foreach ($heroActions as $index => $action): ?>
                            <?php
                            $label = trim((string) ($action[0] ?? 'Learn More'));
                            $link = trim((string) ($action[1] ?? '#'));
                            if ($link === 'dynamic' && strtolower($label) === 'whatsapp') {
                                $link = buildWhatsappLink();
                            }
                            $btnClass = $index === 0 ? 'btn-primary' : ($index === 1 ? 'btn-accent' : 'btn-outline-primary');
                            ?>
                            <a href="<?= htmlspecialchars($link !== '' ? $link : '#') ?>" class="btn <?= $btnClass ?> btn-lg px-4">
                                <?= htmlspecialchars($label) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>

                    <div class="row g-3 hero-proof-grid">
                        <?php foreach ($heroProof as $proof): ?>
                            <div class="col-sm-6 col-lg-12 col-xl-4">
                                <div class="hero-proof-item">
                                    <span class="hero-proof-icon"><i class="bi <?= htmlspecialchars($proof['icon'] ?? 'bi-stars') ?>"></i></span>
                                    <span><?= htmlspecialchars($proof['text'] ?? '') ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <?php if ($hasHeroImage): ?>
                <div class="col-lg-6">
                    <div class="hero-visual-shell">
                        <div class="hero-visual-card">
                            <img src="<?= htmlspecialchars($section['image']) ?>" alt="<?= htmlspecialchars($section['title'] ?? '') ?>" class="hero-visual-image img-fluid" fetchpriority="high" decoding="async" width="600" height="520">
                        </div>

                        <!-- <div class="hero-floating-panel">
                            <div class="row g-3">
                                <?php foreach ($heroStats as $stat): ?>
                                    <div class="col-4">
                                        <div class="hero-stat-card">
                                            <strong><?= htmlspecialchars($stat['value'] ?? '') ?></strong>
                                            <span><?= htmlspecialchars($stat['label'] ?? '') ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div> -->
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
