<?php
$ctaAction = getSectionPrimaryAction($section, [
    'text' => 'Book Free Demo',
    'link' => '#lead-form',
]);
$whatsappLink = buildWhatsappLink();
?>
<section class="section-surface">
    <div class="container">
        <div class="cta-banner cta-banner-premium cta-banner-centered">
            <div class="row justify-content-center">
                <div class="col-xl-8 text-center">
                    <span class="section-kicker section-kicker-light">Let’s Talk</span>
                    <h2 class="mt-3 mb-3"><?= htmlspecialchars($section['title'] ?? '') ?></h2>
                    <?php if (!empty($section['subtitle'])): ?>
                        <p class="lead mb-4"><?= htmlspecialchars($section['subtitle']) ?></p>
                    <?php endif; ?>
                    <div class="d-flex flex-wrap justify-content-center gap-3">
                        <a href="<?= htmlspecialchars($ctaAction['link']) ?>" class="btn btn-light btn-lg px-4"><?= htmlspecialchars($ctaAction['text']) ?></a>
                        <?php if ($whatsappLink !== '#'): ?>
                            <a href="<?= htmlspecialchars($whatsappLink) ?>" class="btn btn-outline-light btn-lg px-4" target="_blank" rel="noopener noreferrer">Chat on WhatsApp</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
