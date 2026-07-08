<section id="section-<?= htmlspecialchars($section['id']) ?>" class="about py-5 bg-light">
    <div class="container">
        <div class="row align-items-center gy-5">
            <div class="col-lg-6">
                <div class="about-content">
                    <small class="text-primary fw-semibold">About LearnWise</small>
                    <h2 class="section-title mt-3 display-5 fw-bold text-primary"><?= htmlspecialchars($section['title']) ?></h2>
                    <?php if (!empty($section['subtitle'])): ?>
                        <p class="section-caption mt-3 lead text-muted"><?= htmlspecialchars($section['subtitle']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($section['content'])): ?>
                        <div class="mt-4">
                            <?= $section['content'] ?>
                        </div>
                    <?php endif; ?>
                    <div class="mt-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <div class="badge bg-primary me-3">🎓</div>
                                    <span>Expert Educators</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <div class="badge bg-primary me-3">📱</div>
                                    <span>Interactive Learning</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <div class="badge bg-primary me-3">🏆</div>
                                    <span>Proven Results</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <div class="badge bg-primary me-3">💡</div>
                                    <span>Innovative Methods</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="about-image">
                    <img src="<?= htmlspecialchars($section['image'] ?: 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=1200&q=80') ?>" alt="<?= htmlspecialchars($section['title']) ?>" class="img-fluid rounded-3 shadow-lg" loading="lazy">
                </div>
            </div>
        </div>
    </div>
</section>