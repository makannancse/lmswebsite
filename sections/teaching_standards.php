<?php
$previewCards = array_slice(getStandardsSections(), 0, 3);
$standardsAction = getSectionPrimaryAction($section, [
    'text' => 'Explore Standards',
    'link' => 'standards.php',
]);
?>
<section class="section-muted teaching-standards-preview">
    <div class="container">
        <div class="row align-items-end gy-4 mb-5">
            <div class="col-lg-7">
                <span class="section-kicker">Teaching Standards</span>
                <h2 class="section-title mt-3"><?= htmlspecialchars($section['title'] ?? '') ?></h2>
                <?php if (!empty($section['subtitle'])): ?>
                    <p class="section-subtitle mt-3 mb-0"><?= htmlspecialchars($section['subtitle']) ?></p>
                <?php endif; ?>
            </div>
            <div class="col-lg-5 text-lg-end">
                <?php if (!empty($section['content'])): ?>
                    <p class="text-muted mb-3"><?= htmlspecialchars($section['content']) ?></p>
                <?php endif; ?>
                <a href="<?= htmlspecialchars($standardsAction['link']) ?>" class="btn btn-primary btn-lg px-4"><?= htmlspecialchars($standardsAction['text']) ?></a>
            </div>
        </div>

        <div class="row g-4">
            <?php foreach ($previewCards as $card): ?>
                <div class="col-md-6 col-xl-4">
                    <article class="card standards-card h-100 border-0">
                        <div class="card-body p-4">
                            <div class="standards-icon mb-4"><?= htmlspecialchars($card['icon'] ?: 'LW') ?></div>
                            <h3 class="h5 mb-3"><?= htmlspecialchars($card['title']) ?></h3>
                            <p class="text-muted mb-0"><?= htmlspecialchars($card['content']) ?></p>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
