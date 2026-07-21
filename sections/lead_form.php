<?php
$leadSettings = getSectionSettings($section);
$leadBenefits = parseTextBlocks($section['content'] ?? '');
$formAnchor = 'lead-form';
$sectionKey = (string) ($section['key'] ?? 'lead-form');
$formId = $sectionKey === 'enroll-form' ? 'enrollForm' : $formAnchor . '-form';
$redirectAfter = $sectionKey === 'enroll-form' ? 'enroll.php' : '';
$courses = getCourses();
?>
<section id="<?= htmlspecialchars($formAnchor) ?>" class="section-surface">
    <div class="container">
        <div class="card lead-form-shell border-0 overflow-hidden">
            <div class="row g-0">
                <div class="col-lg-5">
                    <div class="lead-form-intro h-100">
                        <span class="section-kicker section-kicker-light">Free Consultation</span>
                        <h2 class="mt-3 mb-3"><?= htmlspecialchars($section['title'] ?? '') ?></h2>
                        <p class="mb-0"><?= htmlspecialchars($section['subtitle'] ?? '') ?></p>

                        <?php if (!empty($leadBenefits)): ?>
                            <div class="lead-benefits mt-4">
                                <?php foreach ($leadBenefits as $benefit): ?>
                                    <div class="lead-benefit-item">
                                        <i class="bi bi-check-circle-fill"></i>
                                        <span><?= htmlspecialchars($benefit) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="lead-form-panel h-100">
                        <form
                            method="post"
                            action=""
                            id="<?= htmlspecialchars($formId) ?>"
                            class="lw-lead-form"
                            data-form-anchor="<?= htmlspecialchars($formAnchor) ?>"
                            data-redirect="<?= htmlspecialchars($redirectAfter) ?>"
                            onsubmit="return false;"
                        >
                            <input type="hidden" name="lead_source" value="<?= htmlspecialchars($leadSettings['source'] ?? 'Homepage Demo Form') ?>">
                            <input type="hidden" name="form_anchor" value="<?= htmlspecialchars($formAnchor) ?>">
                            <input type="hidden" name="redirect_after" value="<?= htmlspecialchars($redirectAfter) ?>">

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="<?= htmlspecialchars($formAnchor) ?>-name" class="form-label fw-semibold">Your Name *</label>
                                    <input type="text" id="<?= htmlspecialchars($formAnchor) ?>-name" name="name" class="form-control form-control-lg" placeholder="Enter your full name" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="<?= htmlspecialchars($formAnchor) ?>-parent-name" class="form-label fw-semibold">Parent Name</label>
                                    <input type="text" id="<?= htmlspecialchars($formAnchor) ?>-parent-name" name="parent_name" class="form-control form-control-lg" placeholder="Parent or guardian name">
                                </div>
                                <div class="col-md-6">
                                    <label for="<?= htmlspecialchars($formAnchor) ?>-student-name" class="form-label fw-semibold">Student Name</label>
                                    <input type="text" id="<?= htmlspecialchars($formAnchor) ?>-student-name" name="student_name" class="form-control form-control-lg" placeholder="Student name">
                                </div>
                                <div class="col-md-6">
                                    <label for="<?= htmlspecialchars($formAnchor) ?>-email" class="form-label fw-semibold">Email Address *</label>
                                    <input type="email" id="<?= htmlspecialchars($formAnchor) ?>-email" name="email" class="form-control form-control-lg" placeholder="you@example.com" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="<?= htmlspecialchars($formAnchor) ?>-phone" class="form-label fw-semibold">Phone Number *</label>
                                    <input type="tel" id="<?= htmlspecialchars($formAnchor) ?>-phone" name="phone" class="form-control form-control-lg" placeholder="Enter your phone number" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="<?= htmlspecialchars($formAnchor) ?>-course" class="form-label fw-semibold">Course Interested *</label>
                                    <select id="<?= htmlspecialchars($formAnchor) ?>-course" name="course" class="form-select form-select-lg" required>
                                        <option value="">Select a course</option>
                                        <?php foreach ($courses as $course): ?>
                                            <option value="<?= htmlspecialchars($course['title']) ?>"><?= htmlspecialchars($course['title']) ?></option>
                                        <?php endforeach; ?>
                                        <option value="Other">Other / Not sure yet</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label for="<?= htmlspecialchars($formAnchor) ?>-message" class="form-label fw-semibold">Message</label>
                                    <textarea id="<?= htmlspecialchars($formAnchor) ?>-message" name="message" rows="3" class="form-control form-control-lg" placeholder="Grade level, goals, preferred schedule, or any questions"></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary btn-lg px-5 lead-submit-btn">Book My Free Trial</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
