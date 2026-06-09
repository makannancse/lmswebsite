<?php
$teachers = getTeachers();
$teacherSettings = getSectionSettings($section);
$sectionKicker = getSectionKicker($section, 'Our Faculty');
$sectionClass = trim((string) ($teacherSettings['surface'] ?? 'section-surface'));
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
            <?php foreach ($teachers as $teacher): ?>
                <div class="col-lg-4 col-md-6">
                    <article class="card teacher-showcase-card h-100 border-0">
                        <div class="teacher-showcase-header">
                            <div class="teacher-showcase-avatar">
                                <img src="<?= htmlspecialchars($teacher['image'] ?: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=300&q=80') ?>" alt="<?= htmlspecialchars($teacher['name']) ?>" width="120" height="120" loading="lazy" decoding="async">
                            </div>
                            <div class="teacher-showcase-badge">
                                <i class="bi bi-patch-check-fill"></i>
                                Verified Educator
                            </div>
                        </div>
                        <div class="card-body text-center p-4 pt-0">
                            <h3 class="h5 mb-1"><?= htmlspecialchars($teacher['name']) ?></h3>
                            <p class="teacher-subject mb-2"><?= htmlspecialchars($teacher['subject']) ?></p>
                            <?php if (!empty($teacher['qualifications'])): ?>
                                <p class="teacher-qualifications small text-muted mb-3"><?= htmlspecialchars($teacher['qualifications']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($teacher['bio'])): ?>
                                <p class="text-muted mb-4"><?= htmlspecialchars($teacher['bio']) ?></p>
                            <?php endif; ?>
                            <div class="teacher-metrics row g-3">
                                <div class="col-6">
                                    <div class="teacher-metric">
                                        <strong><?= htmlspecialchars($teacher['experience'] ?: ((int) ($teacher['experience_years'] ?? 0) . '+ years')) ?></strong>
                                        <span>Experience</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="teacher-metric">
                                        <strong><?= (int) ($teacher['students_count'] ?? 0) ?>+</strong>
                                        <span>Students</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
