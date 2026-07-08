<?php
$ctaItems = parseStructuredLines($section['content'] ?? '', 2);
$ctaSettings = getSectionSettings($section);
$kicker = getSectionKicker($section, 'Get Started');
?>
<section class="section-surface">
    <div class="container">
        <div class="cta-banner cta-banner-premium">
            <div class="row align-items-center gy-4">
                <div class="col-lg-7">
                    <span class="section-kicker section-kicker-light"><?= htmlspecialchars($kicker) ?></span>
                    <h2 class="mt-3 mb-3"><?= htmlspecialchars($section['title'] ?? '') ?></h2>
                    <p class="lead mb-0"><?= htmlspecialchars($section['subtitle'] ?? '') ?></p>
                </div>
                <div class="col-lg-5">
                    <div class="cta-actions d-flex flex-wrap gap-3 justify-content-lg-end">
                        <?php foreach ($ctaItems as $index => $cta): ?>
                            <?php if (empty($cta[0])) {
                                continue;
                            } ?>
                            <a href="<?= htmlspecialchars($cta[1] ?: '#') ?>" class="btn <?= $index === 0 ? 'btn-light btn-lg' : 'btn-outline-light btn-lg' ?> px-4">
                                <?= htmlspecialchars($cta[0]) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
