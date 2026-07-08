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
            $activeCourses = getCourses();
            if (!empty($activeCourses)) {
                foreach ($activeCourses as $course):
                    $meta = getCourseCategoryMeta((string) ($course['category'] ?? $course['title']));
                    $courseImage = getCourseImageUrl($course);
                ?>
                    <div class="col-md-6 col-xl-4">
                        <div class="card course-card course-card-premium border-0 shadow-sm h-100 rounded-3 overflow-hidden">
                            <div class="course-card-image-wrap <?= htmlspecialchars($meta['gradient']) ?>">
                                <img src="<?= htmlspecialchars($courseImage) ?>" alt="<?= htmlspecialchars($course['title']) ?>" class="course-card-image" loading="lazy" decoding="async" width="640" height="426">
                            </div>
                            <div class="card-body p-4 d-flex flex-column">
                                <span class="course-category-label"><?= htmlspecialchars($meta['label']) ?></span>
                                <h5 class="fw-bold text-primary mb-3"><?= htmlspecialchars($course['title']) ?></h5>
                                <p class="text-muted mb-0 lead small flex-grow-1"><?= htmlspecialchars($course['description']) ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach;
            } else {
                $items = parseSectionItems($section['content'] ?? '');
                if (empty($items)) {
                    $items = [
                        ['title' => 'Mathematics Mastery', 'text' => 'Structured practice for every grade and exam.', 'category' => 'mathematics'],
                        ['title' => 'Science Explorer', 'text' => 'Hands-on experiments and live concept review.', 'category' => 'science'],
                        ['title' => 'Coding & Technology', 'text' => 'Build real apps with live teacher-led sessions.', 'category' => 'coding'],
                        ['title' => 'Languages & Communication', 'text' => 'Build speaking, writing, and comprehension confidence.', 'category' => 'languages'],
                        ['title' => 'Arts & Creativity', 'text' => 'Explore drawing, design, and creative expression.', 'category' => 'arts'],
                        ['title' => 'Test Preparation', 'text' => 'Focused coaching for exams and competitive preparation.', 'category' => 'test prep'],
                    ];
                }
                foreach ($items as $item):
                    $fallbackCourse = [
                        'title' => $item['title'] ?? '',
                        'category' => $item['category'] ?? $item['title'] ?? '',
                        'image' => $item['image'] ?? '',
                    ];
                    $meta = getCourseCategoryMeta((string) $fallbackCourse['category']);
                    $courseImage = getCourseImageUrl($fallbackCourse);
                ?>
                    <div class="col-md-6 col-xl-4">
                        <div class="card course-card course-card-premium border-0 shadow-sm h-100 rounded-3 overflow-hidden">
                            <div class="course-card-image-wrap <?= htmlspecialchars($meta['gradient']) ?>">
                                <img src="<?= htmlspecialchars($courseImage) ?>" alt="<?= htmlspecialchars($item['title']) ?>" class="course-card-image" loading="lazy" decoding="async" width="640" height="426">
                            </div>
                            <div class="card-body p-4 d-flex flex-column">
                                <span class="course-category-label"><?= htmlspecialchars($meta['label']) ?></span>
                                <h5 class="fw-bold text-primary mb-3"><?= htmlspecialchars($item['title']) ?></h5>
                                <p class="text-muted mb-0 lead small flex-grow-1"><?= htmlspecialchars($item['text']) ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php } ?>
        </div>
    </div>
</section>
