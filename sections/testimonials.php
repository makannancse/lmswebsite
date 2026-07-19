<?php
$testimonialSettings = getSectionSettings($section);
$sectionKicker = getSectionKicker($section, 'Testimonials');
$cmsTestimonials = getTestimonials();
$carouselId = 'testimonialCarousel-' . (int) ($section['id'] ?? 0);
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

        <div class="testimonial-carousel" id="<?= htmlspecialchars($carouselId) ?>">
            <div class="testimonial-carousel-track">
                <?php foreach ($cmsTestimonials as $testimonial): ?>
                    <?php $imgUrl = getTestimonialImageUrl($testimonial); ?>
                    <div class="testimonial-carousel-slide">
                        <article class="card premium-testimonial h-100">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                                    <div class="testimonial-rating">
                                        <?php for ($s = 0; $s < (int) ($testimonial['rating'] ?? 5); $s++): ?>
                                            <i class="bi bi-star-fill"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <?php if (!empty($testimonial['student_name'])): ?>
                                        <span class="testimonial-role testimonial-role-parent">Parent</span>
                                    <?php endif; ?>
                                </div>
                                <p class="testimonial-copy mb-4">"<?= htmlspecialchars($testimonial['review_text']) ?>"</p>
                                <div class="d-flex align-items-center gap-3">
                                    <img src="<?= htmlspecialchars($imgUrl) ?>" alt="<?= htmlspecialchars($testimonial['parent_name']) ?>" class="testimonial-avatar" loading="lazy" decoding="async" width="56" height="56">
                                    <div>
                                        <strong class="d-block"><?= htmlspecialchars($testimonial['parent_name']) ?></strong>
                                        <?php if (!empty($testimonial['student_name'])): ?>
                                            <span class="text-muted small">Parent of <?= htmlspecialchars($testimonial['student_name']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="button" class="testimonial-carousel-btn testimonial-carousel-prev" aria-label="Previous testimonial">
                <i class="bi bi-chevron-left"></i>
            </button>
            <button type="button" class="testimonial-carousel-btn testimonial-carousel-next" aria-label="Next testimonial">
                <i class="bi bi-chevron-right"></i>
            </button>

            <div class="testimonial-carousel-dots"></div>
        </div>
    </div>
</section>

<script>
(function() {
    const carousel = document.getElementById('<?= htmlspecialchars($carouselId) ?>');
    if (!carousel) return;
    const track = carousel.querySelector('.testimonial-carousel-track');
    const slides = Array.from(track.querySelectorAll('.testimonial-carousel-slide'));
    const prevBtn = carousel.querySelector('.testimonial-carousel-prev');
    const nextBtn = carousel.querySelector('.testimonial-carousel-next');
    const dotsContainer = carousel.querySelector('.testimonial-carousel-dots');
    const total = slides.length;
    if (total === 0) return;

    let currentIndex = 0;
    let autoSlideTimer = null;
    const autoSlideInterval = 2000;

    function getSlidesPerView() {
        if (window.innerWidth >= 992) return 3;
        if (window.innerWidth >= 768) return 2;
        return 1;
    }

    function getMaxIndex() {
        return Math.max(0, total - getSlidesPerView());
    }

    function buildDots() {
        dotsContainer.innerHTML = '';
        const maxIdx = getMaxIndex();
        for (let i = 0; i <= maxIdx; i++) {
            const dot = document.createElement('button');
            dot.type = 'button';
            dot.className = 'testimonial-carousel-dot' + (i === currentIndex ? ' active' : '');
            dot.setAttribute('aria-label', 'Go to slide ' + (i + 1));
            dot.addEventListener('click', () => goTo(i));
            dotsContainer.appendChild(dot);
        }
    }

    function updateSlideWidths() {
        const perView = getSlidesPerView();
        const gap = 24;
        const trackWidth = track.parentElement.clientWidth;
        const slideWidth = (trackWidth - gap * (perView - 1)) / perView;
        slides.forEach(s => { s.style.minWidth = slideWidth + 'px'; s.style.maxWidth = slideWidth + 'px'; });
    }

    function goTo(index) {
        const maxIdx = getMaxIndex();
        currentIndex = Math.max(0, Math.min(index, maxIdx));
        const perView = getSlidesPerView();
        const gap = 24;
        const trackWidth = track.parentElement.clientWidth;
        const slideWidth = (trackWidth - gap * (perView - 1)) / perView;
        const offset = currentIndex * (slideWidth + gap);
        track.style.transform = 'translateX(-' + offset + 'px)';
        const dots = dotsContainer.querySelectorAll('.testimonial-carousel-dot');
        dots.forEach((d, i) => d.classList.toggle('active', i === currentIndex));
    }

    function next() { goTo(currentIndex >= getMaxIndex() ? 0 : currentIndex + 1); }
    function prev() { goTo(currentIndex <= 0 ? getMaxIndex() : currentIndex - 1); }

    function startAutoSlide() { stopAutoSlide(); autoSlideTimer = setInterval(next, autoSlideInterval); }
    function stopAutoSlide() { if (autoSlideTimer) { clearInterval(autoSlideTimer); autoSlideTimer = null; } }

    prevBtn.addEventListener('click', () => { prev(); startAutoSlide(); });
    nextBtn.addEventListener('click', () => { next(); startAutoSlide(); });
    carousel.addEventListener('mouseenter', stopAutoSlide);
    carousel.addEventListener('mouseleave', startAutoSlide);

    let resizeTimeout;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            updateSlideWidths();
            buildDots();
            goTo(Math.min(currentIndex, getMaxIndex()));
        }, 150);
    });

    updateSlideWidths();
    buildDots();
    goTo(0);
    startAutoSlide();
})();
</script>
