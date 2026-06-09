<?php
$founderLines = parseTextBlocks($section['content'] ?? '');
$founderQuote = $founderLines[0] ?? 'We believe families should feel the value of every class through clarity, care, and high teaching standards.';
$founderCompany = $founderLines[1] ?? getSetting('site_name', 'LearnWise');
$founderName = $founderLines[2] ?? 'Founder';
$founderRole = $founderLines[3] ?? 'Founder & CEO';
?>
<section class="section-surface">
    <div class="container">
        <div class="row align-items-center gy-5">
            <div class="col-lg-6">
                <span class="section-kicker">Founder Note</span>
                <h2 class="section-title mt-3"><?= htmlspecialchars($section['title'] ?? '') ?></h2>
                <?php if (!empty($section['subtitle'])): ?>
                    <p class="section-subtitle mt-3"><?= htmlspecialchars($section['subtitle']) ?></p>
                <?php endif; ?>

                <div class="card founder-card mt-4">
                    <div class="card-body p-4">
                        <blockquote class="founder-quote mb-4">"<?= htmlspecialchars(trim($founderQuote, "\"")) ?>"</blockquote>
                        <div class="founder-signature">
                            <strong><?= htmlspecialchars($founderName) ?></strong>
                            <span><?= htmlspecialchars($founderRole) ?></span>
                            <small><?= htmlspecialchars($founderCompany) ?></small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="founder-visual text-center">
                    <img src="<?= htmlspecialchars($section['image'] ?: 'https://images.unsplash.com/photo-1494790108755-2616b612b786?auto=format&fit=crop&w=800&q=80') ?>" alt="<?= htmlspecialchars($founderName) ?>" class="img-fluid founder-portrait" loading="lazy" decoding="async">
                </div>
            </div>
        </div>
    </div>
</section>
