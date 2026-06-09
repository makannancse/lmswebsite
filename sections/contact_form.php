<?php
$contactSettings = getSectionSettings($section);
$formAnchor = 'contact-form';
$courses = getCourses();
$sectionTitle = $section['title'] ?? ($section['section_title'] ?? '');
$sectionSubtitle = $section['subtitle'] ?? ($section['section_subtitle'] ?? '');
?>
<section id="<?= htmlspecialchars($formAnchor) ?>" class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card p-5 rounded-4 border-0 shadow-lg">
                    <div class="mb-4">
                        <h2 class="section-title text-primary mb-2"><?= htmlspecialchars($sectionTitle) ?></h2>
                        <p class="text-muted mb-0"><?= htmlspecialchars($sectionSubtitle) ?></p>
                    </div>
                    <form method="post" action="" id="contactForm" class="lw-lead-form" data-form-anchor="<?= htmlspecialchars($formAnchor) ?>" data-redirect="contact.php" onsubmit="return false;">
                        <input type="hidden" name="lead_source" value="<?= htmlspecialchars($contactSettings['source'] ?? 'Contact Page') ?>">
                        <input type="hidden" name="form_anchor" value="<?= htmlspecialchars($formAnchor) ?>">
                        <input type="hidden" name="redirect_after" value="contact.php">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="contact-name">Name *</label>
                                <input type="text" id="contact-name" name="name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="contact-parent-name">Parent Name</label>
                                <input type="text" id="contact-parent-name" name="parent_name" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="contact-student-name">Student Name</label>
                                <input type="text" id="contact-student-name" name="student_name" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="contact-email">Email *</label>
                                <input type="email" id="contact-email" name="email" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="contact-phone">Phone *</label>
                                <input type="tel" id="contact-phone" name="phone" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="contact-course">Course Interested</label>
                                <select id="contact-course" name="course" class="form-select">
                                    <option value="">Select a course</option>
                                    <?php foreach ($courses as $course): ?>
                                        <option value="<?= htmlspecialchars($course['title']) ?>"><?= htmlspecialchars($course['title']) ?></option>
                                    <?php endforeach; ?>
                                    <option value="General Enquiry">General Enquiry</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="contact-message">Message</label>
                                <textarea id="contact-message" name="message" rows="4" class="form-control" placeholder="How can we help you? (required if no course selected)"></textarea>
                            </div>
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-primary btn-lg">Send Message</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
