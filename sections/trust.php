<?php
$trustItems = parseStructuredLines($section['content'] ?? '', 3);
if ($trustItems === []) {
    $trustItems = [
        ['4.9/5', 'Average parent rating', 'bi-star-fill'],
        ['2,500+', 'Students learning live', 'bi-people-fill'],
        ['150+', 'Qualified teachers', 'bi-mortarboard-fill'],
        ['100%', 'Safe online classrooms', 'bi-shield-check'],
    ];
}
$sectionKicker = getSectionKicker($section, 'Trust');
?>
<section class="section-surface">
    <div class="container">
        <?php if (!empty($section['title']) || !empty($section['subtitle'])): ?>
            <div class="section-heading text-center mx-auto">
                <span class="section-kicker"><?= htmlspecialchars($sectionKicker) ?></span>
                <?php if (!empty($section['title'])): ?>
                    <h2 class="section-title mt-3"><?= htmlspecialchars($section['title']) ?></h2>
                <?php endif; ?>
                <?php if (!empty($section['subtitle'])): ?>
                    <p class="section-subtitle mx-auto mt-3"><?= htmlspecialchars($section['subtitle']) ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <?php foreach ($trustItems as $item): ?>
                <div class="col-6 col-lg-3">
                    <div class="trust-indicator-card text-center h-100">
                        <div class="trust-indicator-icon">
                            <i class="bi <?= htmlspecialchars($item[2] ?: 'bi-award') ?>"></i>
                        </div>
                        <h3 class="trust-indicator-value"><?= htmlspecialchars($item[0]) ?></h3>
                        <p class="trust-indicator-label mb-0"><?= htmlspecialchars($item[1]) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
