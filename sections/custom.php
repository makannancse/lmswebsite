<section id="section-<?= htmlspecialchars($section['id']) ?>" class="py-5">
    <div class="container">
        <div class="row align-items-center gx-5">
            <div class="col-lg-6">
                <h2 class="section-title"><?= htmlspecialchars($section['title']) ?></h2>
                <?php if (!empty($section['subtitle'])): ?>
                    <p class="section-caption mt-3"><?= htmlspecialchars($section['subtitle']) ?></p>
                <?php endif; ?>
                <?php if (!empty($section['content'])): ?>
                    <p class="mt-4 text-muted"><?= nl2br(htmlspecialchars($section['content'])) ?></p>
                <?php endif; ?>
                <?php if (!empty($section['button_text'])): ?>
                    <a href="<?= htmlspecialchars($section['button_link'] ?: '#lead-form') ?>" class="btn btn-primary mt-3"><?= htmlspecialchars($section['button_text']) ?></a>
                <?php endif; ?>
            </div>
            <?php if (!empty($section['image'])): ?>
                <div class="col-lg-6">
                    <img src="<?= htmlspecialchars($section['image']) ?>" alt="<?= htmlspecialchars($section['title']) ?>" class="img-fluid rounded-3 mt-4 mt-lg-0" loading="lazy">
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
