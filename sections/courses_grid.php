<?php
$courses = getCourses();
$courseSettings = getSectionSettings($section);
$ctaLabel = trim((string) ($courseSettings['cta_label'] ?? 'Explore'));
$ctaLink = trim((string) ($courseSettings['cta_link'] ?? 'enroll.php'));
$sectionKicker = getSectionKicker($section, 'Programs');
$sectionClass = trim((string) ($courseSettings['surface'] ?? 'section-muted'));
?>
<section class="<?= htmlspecialchars($sectionClass) ?>">
    <div class="container">
        <div class="section-heading text-center mx-auto">
            <span class="section-kicker"><?= htmlspecialchars($sectionKicker) ?></span>
            <h2 class="section-title mt-3"><?= htmlspecialchars($section['title'] ?? '') ?></h2>
            <?php if (!empty($section['subtitle'])): ?>
                <p class="section-subtitle mx-auto mt-3"><?= htmlspecialchars($section['subtitle']) ?></p>
            <?php endif; ?>
        </div>

        <div class="row g-4">
            <?php foreach ($courses as $course): ?>
                <?php $meta = getCourseCategoryMeta((string) $course['category']); ?>
                <div class="col-md-6 col-xl-4">
                    <article class="card course-category-card h-100 border-0">
                        <div class="course-category-visual <?= htmlspecialchars($meta['gradient']) ?>">
                            <?php if (!empty($course['image'])): ?>
                                <img src="<?= htmlspecialchars($course['image']) ?>" alt="<?= htmlspecialchars($course['title']) ?>" class="course-category-image" loading="lazy" decoding="async">
                            <?php else: ?>
                                <div class="course-category-icon">
                                    <i class="bi <?= htmlspecialchars($meta['icon']) ?>"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-body p-4 d-flex flex-column">
                            <span class="course-category-label"><?= htmlspecialchars($meta['label']) ?></span>
                            <h3 class="h5 mb-2"><?= htmlspecialchars($course['title']) ?></h3>
                            <p class="text-muted mb-4 flex-grow-1"><?= htmlspecialchars($course['description']) ?></p>
                            <a href="<?= htmlspecialchars($ctaLink) ?>" class="btn btn-primary align-self-start"><?= htmlspecialchars($ctaLabel) ?></a>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
