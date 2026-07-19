<?php
$teachers = getTeachers();
$sectionKicker = getSectionKicker($section, 'Our Team');
$sectionClass = 'section-surface';

// --- Founder data from the companion founder section (db row on same page, type=founder) ---
// We load it directly so teachers_grid can render both blocks in one <section>.
$founderSection = null;
try {
    $pdo = lwGetPdo();
    $stmt = $pdo->prepare("SELECT * FROM page_sections WHERE page_id = :pid AND section_type = 'founder' AND status = 'active' ORDER BY sort_order ASC LIMIT 1");
    $stmt->execute([':pid' => $section['page_id'] ?? 1]);
    $founderSection = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
} catch (\Throwable $e) {
    $founderSection = null;
}

$showFounder = $founderSection !== null;
$founderLines = parseTextBlocks($founderSection['section_content'] ?? $founderSection['content'] ?? '');
$founderQuote   = $founderLines[0] ?? 'We believe every child deserves clarity, care, and the very best in teaching.';
$founderCompany = $founderLines[1] ?? getSetting('site_name', 'LearnWise');
$founderName    = $founderLines[2] ?? 'Founder';
$founderRole    = $founderLines[3] ?? 'Founder & CEO';
$founderImgRaw  = $founderSection['section_image'] ?? $founderSection['image'] ?? '';
$founderImg     = $founderImgRaw;
?>
<section class="<?= htmlspecialchars($sectionClass) ?>">
    <div class="container">

        <!-- Section heading -->
        <div class="section-heading text-center mx-auto">
            <span class="section-kicker"><?= htmlspecialchars($sectionKicker) ?></span>
            <h2 class="section-title mt-3"><?= htmlspecialchars($section['title'] ?? '') ?></h2>
            <?php if (!empty($section['subtitle'])): ?>
                <p class="section-subtitle mx-auto mt-3"><?= htmlspecialchars($section['subtitle']) ?></p>
            <?php endif; ?>
        </div>

        <?php if ($showFounder): ?>
        <!-- ── Founder card (larger, prominent) ── -->
        <div class="founder-row mb-5">
            <div class="founder-combined-card">
                <?php if ($founderImg !== ''): ?>
                <div class="founder-combined-img-wrap">
                    <img src="<?= htmlspecialchars($founderImg) ?>"
                         alt="<?= htmlspecialchars($founderName) ?>"
                         class="founder-combined-img"
                         loading="lazy" decoding="async">
                </div>
                <?php endif; ?>
                <div class="founder-combined-body">
                    <div class="founder-name-badge mb-3">
                        <i class="bi bi-award-fill"></i>
                        Founder
                    </div>
                    <h3 class="founder-combined-name"><?= htmlspecialchars($founderName) ?></h3>
                    <p class="founder-combined-role"><?= htmlspecialchars($founderRole) ?></p>
                    <blockquote class="founder-combined-quote">
                        "<?= htmlspecialchars(trim($founderQuote, '"')) ?>"
                    </blockquote>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- ── Our Experts grid ── -->
        <?php if (!empty($teachers)): ?>
        <div class="experts-subheading">
            <span class="section-kicker" style="font-size:.75rem;">Our Experts</span>
        </div>
        <div class="row g-4 mt-2">
            <?php foreach ($teachers as $teacher): ?>
                <?php $teacherPhoto = getTeacherPhotoUrl($teacher); ?>
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <article class="expert-card h-100">
                        <div class="expert-card-avatar-wrap">
                            <img src="<?= htmlspecialchars($teacherPhoto) ?>"
                                 alt="<?= htmlspecialchars($teacher['name']) ?>"
                                 class="expert-card-avatar"
                                 width="80" height="80"
                                 loading="lazy" decoding="async">
                        </div>
                        <div class="expert-card-body">
                            <h4 class="expert-card-name"><?= htmlspecialchars($teacher['name']) ?></h4>
                            <p class="expert-card-role">
                                <?= htmlspecialchars(!empty($teacher['designation']) ? $teacher['designation'] : $teacher['subject']) ?>
                            </p>
                            <?php if (!empty($teacher['subject'])): ?>
                            <span class="expert-card-subject">
                                <i class="bi bi-book-half"></i>
                                <?= htmlspecialchars($teacher['subject']) ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

    </div>
</section>
