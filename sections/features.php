<section id="section-<?= htmlspecialchars($section['id']) ?>" class="features bg-white py-5">
    <div class="container">
        <div class="row align-items-center gy-4 mb-5">
            <div class="col-lg-6">
                <small class="text-primary fw-semibold">Highlights</small>
                <h2 class="section-title mt-3 display-5 fw-bold text-primary"><?= htmlspecialchars($section['title']) ?></h2>
                <?php if (!empty($section['subtitle'])): ?>
                    <p class="section-caption mt-3 lead text-muted"><?= htmlspecialchars($section['subtitle']) ?></p>
                <?php endif; ?>
            </div>
            <?php if (!empty($section['button_text'])): ?>
                <div class="col-lg-6 text-lg-end">
                    <a href="<?= htmlspecialchars($section['button_link'] ?: '#lead-form') ?>" class="btn btn-primary btn-lg px-4 py-3">🚀 <?= htmlspecialchars($section['button_text']) ?></a>
                </div>
            <?php endif; ?>
        </div>
        <div class="row g-4 feature-grid">
            <?php
            $items = parseSectionItems($section['content'] ?? '');
            if (empty($items)) {
                $items = [
                    ['title' => 'Smart Class Scheduling', 'text' => 'Create classes quickly with calendar clarity.'],
                    ['title' => 'Google Meet Integration', 'text' => 'Auto-generated meeting links for every lesson.'],
                    ['title' => 'Homework & Assignments', 'text' => 'Track student performance and submissions easily.'],
                ];
            }
            foreach ($items as $item): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card feature-card border-0 shadow-sm h-100 p-4 rounded-3">
                        <div class="card-icon mb-3 text-primary fs-2">✨</div>
                        <h5 class="fw-bold text-primary mb-3"><?= htmlspecialchars($item['title']) ?></h5>
                        <p class="text-muted mb-0 lead small"><?= htmlspecialchars($item['text']) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
