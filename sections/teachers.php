<?php
$teachers = getTeachers();
?>
<section id="section-<?= htmlspecialchars($section['id']) ?>" class="section-surface">
    <div class="container">
        <div class="section-heading text-center mx-auto">
            <span class="section-kicker">Our Expert Faculty</span>
            <h2 class="section-title mt-3"><?= htmlspecialchars($section['title']) ?></h2>
            <?php if (!empty($section['subtitle'])): ?>
                <p class="section-subtitle mx-auto mt-3"><?= htmlspecialchars($section['subtitle']) ?></p>
            <?php endif; ?>
        </div>

        <div class="row g-4">
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

        <?php if (!empty($section['button_text'])): ?>
            <div class="text-center mt-5">
                <a href="<?= htmlspecialchars($section['button_link'] ?: 'teachers.php') ?>" class="btn btn-primary btn-lg px-4 py-3">👨‍🏫 <?= htmlspecialchars($section['button_text']) ?></a>
            </div>
        <?php endif; ?>
    </div>
</section>
