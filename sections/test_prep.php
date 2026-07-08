<section id="section-<?= htmlspecialchars($section['id']) ?>" class="test-prep py-5">
    <div class="container">
        <div class="row align-items-center gy-4">
            <div class="col-lg-7">
                <small class="text-primary fw-semibold">Test Preparation</small>
                <h2 class="section-title mt-3 display-5 fw-bold text-primary"><?= htmlspecialchars($section['title']) ?></h2>
                <?php if (!empty($section['subtitle'])): ?>
                    <p class="section-caption mt-3 lead text-muted"><?= htmlspecialchars($section['subtitle']) ?></p>
                <?php endif; ?>
                <div class="mt-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <div class="badge bg-warning text-dark me-3">📚</div>
                                <span>Structured Curriculum</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <div class="badge bg-warning text-dark me-3">⏱️</div>
                                <span>Timed Practice Sessions</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <div class="badge bg-warning text-dark me-3">👨‍🏫</div>
                                <span>Personalized Feedback</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <div class="badge bg-warning text-dark me-3">📊</div>
                                <span>Performance Analytics</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 text-lg-end">
                <?php if (!empty($section['button_text'])): ?>
                    <a href="<?= htmlspecialchars($section['button_link'] ?: 'contact.php') ?>" class="btn btn-primary btn-lg px-4 py-3">🎯 <?= htmlspecialchars($section['button_text']) ?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>