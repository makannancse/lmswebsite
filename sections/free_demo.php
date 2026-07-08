<?php
$demoFeatures = parseTextBlocks($section['content'] ?? '');
if ($demoFeatures === []) {
    $demoFeatures = [
        'Live 1-on-1 session with an expert tutor',
        'Personalized to your child\'s level and learning pace',
        'No obligation to continue after the demo',
        'Clear parent feedback after the session',
    ];
}

$demoAction = getSectionPrimaryAction($section, [
    'text' => 'Book Free Demo',
    'link' => '#lead-form',
]);
?>
<section class="section-muted">
    <div class="container">
        <div class="card demo-shell border-0 overflow-hidden">
            <div class="row g-0 align-items-center">
                <div class="col-lg-6">
                    <div class="demo-copy h-100">
                        <span class="section-kicker">Risk-Free Start</span>
                        <h2 class="section-title mt-3 mb-3"><?= htmlspecialchars($section['title'] ?? '') ?></h2>
                        <?php if (!empty($section['subtitle'])): ?>
                            <p class="section-subtitle mb-4"><?= htmlspecialchars($section['subtitle']) ?></p>
                        <?php endif; ?>

                        <div class="demo-feature-list">
                            <?php foreach ($demoFeatures as $feature): ?>
                                <div class="demo-feature-item">
                                    <i class="bi bi-check-circle-fill"></i>
                                    <span><?= htmlspecialchars($feature) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <?php if ($demoAction['text'] !== ''): ?>
                            <a href="<?= htmlspecialchars($demoAction['link']) ?>" class="btn btn-primary btn-lg mt-4 px-4">
                                <?= htmlspecialchars($demoAction['text']) ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="demo-visual h-100">
                        <img src="<?= htmlspecialchars($section['image'] ?: 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=1200&q=80') ?>" alt="<?= htmlspecialchars($section['title'] ?? 'Free demo') ?>" class="img-fluid w-100 h-100 object-fit-cover" loading="lazy" decoding="async">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
