<?php
$featureItems = parseStructuredLines($section['content'] ?? '', 3);
$featureSettings = getSectionSettings($section);
$columns = max(1, min(4, (int) ($featureSettings['columns'] ?? 3)));
$columnClass = [
    1 => 'col-12',
    2 => 'col-md-6',
    3 => 'col-md-6 col-xl-4',
    4 => 'col-md-6 col-xl-3',
][$columns];
$sectionClass = trim((string) ($featureSettings['surface'] ?? ''));
if ($sectionClass === '') {
    $sectionClass = in_array($section['key'] ?? '', ['why_parents_trust_us', 'parent_trust'], true) ? 'section-muted' : 'section-surface';
}
$sectionKicker = getSectionKicker($section, 'Highlights');
?>
<section class="<?= htmlspecialchars($sectionClass) ?>">
    <div class="container">
        <div class="section-heading text-center mx-auto">
            <span class="section-kicker"><?= htmlspecialchars($sectionKicker) ?></span>
            <h2 class="section-title mt-3"><?= htmlspecialchars($section['title'] ?? '') ?></h2>
            <?php if (!empty($section['subtitle'])): ?>
                <p class="section-subtitle mx-auto mt-3"><?= htmlspecialchars($section['subtitle']) ?></p>
            <?php endif; ?>
        </div>

        <div class="row g-4">
            <?php foreach ($featureItems as $item): ?>
                <div class="<?= $columnClass ?>">
                    <article class="card feature-card premium-icon-card h-100">
                        <div class="card-body p-4">
                            <div class="card-icon mb-4">
                                <i class="bi <?= htmlspecialchars($item[2] ?: 'bi-stars') ?>"></i>
                            </div>
                            <h3 class="h5 mb-3"><?= htmlspecialchars($item[0]) ?></h3>
                            <p class="text-muted mb-0"><?= htmlspecialchars($item[1]) ?></p>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
