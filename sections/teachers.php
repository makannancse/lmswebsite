<section id="section-<?= htmlspecialchars($section['id']) ?>" class="teachers py-5">
    <div class="container">
        <div class="text-center mb-5">
            <small class="text-primary fw-semibold">Our Expert Faculty</small>
            <h2 class="section-title mt-3 display-5 fw-bold text-primary"><?= htmlspecialchars($section['title']) ?></h2>
            <?php if (!empty($section['subtitle'])): ?>
                <p class="section-caption mt-3 lead text-muted"><?= htmlspecialchars($section['subtitle']) ?></p>
            <?php endif; ?>
        </div>

        <div class="row g-4">
            <?php
            $teachers = getTeachers();
            foreach ($teachers as $teacher):
            ?>
                <div class="col-lg-3 col-md-6">
                    <div class="teacher-card card border-0 shadow-sm h-100">
                        <div class="card-body text-center p-4">
                            <div class="teacher-avatar mb-3">
                                <img src="<?= htmlspecialchars($teacher['image'] ?: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=300&q=80') ?>" alt="<?= htmlspecialchars($teacher['name']) ?>" class="rounded-circle" width="100" height="100" loading="lazy">
                            </div>
                            <h5 class="card-title fw-bold text-primary mb-1"><?= htmlspecialchars($teacher['name']) ?></h5>
                            <p class="text-muted small mb-2"><?= htmlspecialchars($teacher['subject']) ?></p>
                            <p class="card-text small text-muted mb-3"><?= htmlspecialchars($teacher['bio']) ?></p>
                            <div class="teacher-stats">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <div class="stat">
                                            <small class="text-muted d-block">Experience</small>
                                            <span class="fw-semibold text-primary"><?= htmlspecialchars($teacher['experience_years']) ?> yrs</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="stat">
                                            <small class="text-muted d-block">Students</small>
                                            <span class="fw-semibold text-primary"><?= htmlspecialchars($teacher['students_count']) ?>+</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($section['button_text'])): ?>
            <div class="text-center mt-5">
                <a href="<?= htmlspecialchars($section['button_link'] ?: 'teachers.php') ?>" class="btn btn-primary btn-lg px-4 py-3">👨‍🏫 <?= htmlspecialchars($section['button_text']) ?></a>
            </div>
        <?php endif; ?>
    </div>
</section>