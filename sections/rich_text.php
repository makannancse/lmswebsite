<?php
$blocks = parseTextBlocks($section['content'] ?? '');
?>
<section class="section-surface legal-content-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="cms-rich-text-card">
                    <?php if (!empty($section['title'])): ?>
                        <h2 class="section-title h3 mb-3"><?= htmlspecialchars($section['title']) ?></h2>
                    <?php endif; ?>
                    <?php if (!empty($section['subtitle'])): ?>
                        <p class="section-subtitle mb-4"><?= htmlspecialchars($section['subtitle']) ?></p>
                    <?php endif; ?>
                    <div class="cms-rich-text">
                        <?php foreach ($blocks as $block): ?>
                            <p class="text-muted"><?= htmlspecialchars($block) ?></p>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
