<?php
$stats = parseStructuredLines($section['content'] ?? '', 2);
if ($stats === []) {
    $stats = [
        ['5,000+', 'Happy parents supported'],
        ['5★', 'Average family satisfaction'],
        ['100%', 'Demo-first admissions approach'],
    ];
}
?>
<section class="section-surface">
    <div class="container">
        <div class="stats-shell">
            <?php if (!empty($section['title']) || !empty($section['subtitle'])): ?>
                <div class="text-center text-lg-start mb-4 mb-lg-0">
                    <span class="section-kicker section-kicker-light">Proof Points</span>
                    <?php if (!empty($section['title'])): ?>
                        <h2 class="stats-title mt-3 mb-0"><?= htmlspecialchars($section['title']) ?></h2>
                    <?php endif; ?>
                    <?php if (!empty($section['subtitle'])): ?>
                        <p class="mt-3 mb-0 text-white-50"><?= htmlspecialchars($section['subtitle']) ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="row g-4 w-100">
                <?php foreach ($stats as $stat): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="stats-item text-center text-lg-start">
                            <strong><?= htmlspecialchars($stat[0]) ?></strong>
                            <span><?= htmlspecialchars($stat[1]) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
