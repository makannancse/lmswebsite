<?php
$curriculums = parseStructuredLines($section['content'] ?? '', 2);
if ($curriculums === []) {
    $curriculums = [
        ['American Curriculum', 'Common Core, AP, and state-specific standards for K-12 learners across the US.'],
        ['Australian Curriculum', 'Aligned with ACARA standards across all key learning areas and year levels.'],
        ['Canadian Curriculum', 'Provincial curricula support from Ontario, BC, Alberta, and more.'],
        ['Global & Regional Boards', 'IB, CBSE, ICSE, UAE MOE, Singapore, and British GCSE/A-Level preparation.'],
        ['Co-Curriculars & Future Skills', 'Coding, AI, chess, robotics, creative writing, and financial literacy.'],
    ];
}

$curriculumIcons = ['bi-globe2', 'bi-book', 'bi-journal-richtext', 'bi-mortarboard', 'bi-rocket-takeoff'];
?>
<section class="section-muted">
    <div class="container">
        <div class="section-heading text-center mx-auto">
            <span class="section-kicker">Global Coverage</span>
            <h2 class="section-title mt-3"><?= htmlspecialchars($section['title'] ?? '') ?></h2>
            <?php if (!empty($section['subtitle'])): ?>
                <p class="section-subtitle mx-auto mt-3"><?= htmlspecialchars($section['subtitle']) ?></p>
            <?php endif; ?>
        </div>

        <div class="row g-4">
            <?php foreach ($curriculums as $index => $curriculum): ?>
                <div class="col-lg-4 col-md-6">
                    <article class="card curriculum-card h-100">
                        <div class="card-body p-4">
                            <div class="card-icon mb-4">
                                <i class="bi <?= htmlspecialchars($curriculumIcons[$index % count($curriculumIcons)]) ?>"></i>
                            </div>
                            <h3 class="h5 mb-3"><?= htmlspecialchars($curriculum[0]) ?></h3>
                            <p class="text-muted mb-0"><?= htmlspecialchars($curriculum[1]) ?></p>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
