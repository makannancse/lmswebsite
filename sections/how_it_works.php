<?php
$steps = parseStructuredLines($section['content'] ?? '', 2);
if ($steps === []) {
    $steps = [
        ['Tell Us About Your Child', 'Share your child\'s grade, subject needs, and learning goals.'],
        ['We Match the Right Tutor', 'We pair your family with a tutor who fits both the syllabus and the learning style.'],
        ['Attend a Free Demo', 'See the teaching quality first, with no risk and no payment needed.'],
        ['Start a Structured Plan', 'Move forward with a schedule, reporting rhythm, and clear next steps.'],
    ];
}
?>
<section class="section-surface">
    <div class="container">
        <div class="section-heading text-center mx-auto">
            <span class="section-kicker">Simple Process</span>
            <h2 class="section-title mt-3"><?= htmlspecialchars($section['title'] ?? '') ?></h2>
            <?php if (!empty($section['subtitle'])): ?>
                <p class="section-subtitle mx-auto mt-3"><?= htmlspecialchars($section['subtitle']) ?></p>
            <?php endif; ?>
        </div>

        <div class="row g-4">
            <?php foreach ($steps as $index => $step): ?>
                <div class="col-lg-3 col-md-6">
                    <article class="card step-card h-100 text-center">
                        <div class="card-body p-4">
                            <span class="step-badge"><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span>
                            <h3 class="h5 mt-4 mb-3"><?= htmlspecialchars($step[0]) ?></h3>
                            <p class="text-muted mb-0"><?= htmlspecialchars($step[1]) ?></p>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($section['image'])): ?>
            <div class="how-it-works-visual mt-5 text-center">
                <img src="<?= htmlspecialchars($section['image']) ?>" alt="<?= htmlspecialchars($section['title'] ?? 'How LearnWise works') ?>" class="img-fluid rounded-4 shadow-sm" loading="lazy" decoding="async">
            </div>
        <?php endif; ?>
    </div>
</section>
