<?php
$testimonialSettings = getSectionSettings($section);
$testimonials = parseStructuredLines($section['content'] ?? '', 4);
if ($testimonials === []) {
    $testimonials = parseStructuredLines($section['content'] ?? '', 3);
}
if ($testimonials === []) {
    $testimonials = [
        ['Mrs. Sharma', 'Our daughter\'s confidence in math improved within weeks. The teachers are patient, prepared, and genuinely invested.', 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?auto=format&fit=crop&w=300&q=80', 'Parent'],
        ['Arjun, Grade 9', 'Classes feel interactive and easy to follow. I can revisit recordings and stay on top of homework without stress.', 'https://images.unsplash.com/photo-1544717297-fa95b6ee9643?auto=format&fit=crop&w=300&q=80', 'Student'],
        ['The Mehta Family', 'Progress updates are clear, communication is fast, and scheduling works around our routine. Highly recommended.', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=300&q=80', 'Parent'],
    ];
}
$sectionKicker = getSectionKicker($section, 'Testimonials');
?>
<section class="section-muted">
    <div class="container">
        <div class="section-heading text-center mx-auto">
            <span class="section-kicker"><?= htmlspecialchars($sectionKicker) ?></span>
            <h2 class="section-title mt-3"><?= htmlspecialchars($section['title'] ?? '') ?></h2>
            <?php if (!empty($section['subtitle'])): ?>
                <p class="section-subtitle mx-auto mt-3"><?= htmlspecialchars($section['subtitle']) ?></p>
            <?php endif; ?>
        </div>

        <div class="row g-4">
            <?php foreach ($testimonials as $testimonial): ?>
                <?php
                $role = trim((string) ($testimonial[3] ?? 'Family'));
                $roleClass = strtolower($role) === 'student' ? 'testimonial-role-student' : 'testimonial-role-parent';
                ?>
                <div class="col-lg-4 col-md-6">
                    <article class="card testimonial-card premium-testimonial h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                                <div class="testimonial-rating">
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                </div>
                                <span class="testimonial-role <?= $roleClass ?>"><?= htmlspecialchars($role) ?></span>
                            </div>
                            <p class="testimonial-copy mb-4">"<?= htmlspecialchars($testimonial[1]) ?>"</p>
                            <div class="d-flex align-items-center gap-3">
                                <img src="<?= htmlspecialchars($testimonial[2] ?: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=300&q=80') ?>" alt="<?= htmlspecialchars($testimonial[0]) ?>" class="testimonial-avatar" loading="lazy" decoding="async" width="56" height="56">
                                <div>
                                    <strong class="d-block"><?= htmlspecialchars($testimonial[0]) ?></strong>
                                    <span class="text-muted small">LearnWise <?= htmlspecialchars(strtolower($role)) ?></span>
                                </div>
                            </div>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
