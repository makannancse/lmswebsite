<section id="section-<?= htmlspecialchars($section['id']) ?>" class="courses py-5 bg-light">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-5 flex-column flex-md-row gap-3">
            <div>
                <small class="text-primary fw-semibold">Our Programs</small>
                <h2 class="section-title mt-3 display-5 fw-bold text-primary"><?= htmlspecialchars($section['title']) ?></h2>
                <?php if (!empty($section['subtitle'])): ?>
                    <p class="section-caption mt-3 lead text-muted"><?= htmlspecialchars($section['subtitle']) ?></p>
                <?php endif; ?>
            </div>
            <?php if (!empty($section['button_text'])): ?>
                <a href="<?= htmlspecialchars($section['button_link'] ?: 'courses.php') ?>" class="btn btn-primary btn-lg px-4 py-3">📚 <?= htmlspecialchars($section['button_text']) ?></a>
            <?php endif; ?>
        </div>
        <div class="row g-4">
            <?php
            $activeCourses = getCourses($pdo);
            if (!empty($activeCourses)) {
                foreach ($activeCourses as $course): ?>
                    <div class="col-md-6 col-xl-4">
                        <div class="card course-card border-0 shadow-sm h-100 p-4 rounded-3">
                            <?php if (!empty($course['image'])): ?>
                                <img src="<?= htmlspecialchars($course['image']) ?>" alt="<?= htmlspecialchars($course['title']) ?>" class="img-fluid rounded-3 mb-3" loading="lazy">
                            <?php endif; ?>
                            <h5 class="fw-bold text-primary mb-3"><?= htmlspecialchars($course['title']) ?></h5>
                            <p class="text-muted mb-0 lead small"><?= htmlspecialchars($course['description']) ?></p>
                        </div>
                    </div>
                <?php endforeach;
            } else {
                $items = parseSectionItems($section['content'] ?? '');
                if (empty($items)) {
                    $items = [
                        ['title' => 'Interactive Coding', 'text' => 'Build real apps with live teacher-led sessions.'],
                        ['title' => 'Math Mastery', 'text' => 'Structured practice for every grade and exam.'],
                        ['title' => 'Science Lab', 'text' => 'Hands-on experiments and live concept review.'],
                    ];
                }
                foreach ($items as $item): ?>
                    <div class="col-md-6 col-xl-4">
                        <div class="card course-card border-0 shadow-sm h-100 p-4 rounded-3">
                            <h5 class="fw-bold text-primary mb-3"><?= htmlspecialchars($item['title']) ?></h5>
                            <p class="text-muted mb-0 lead small"><?= htmlspecialchars($item['text']) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php } ?>
        </div>
    </div>
</section>
