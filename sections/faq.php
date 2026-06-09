<?php $faqItems = parseStructuredLines($section['content'] ?? '', 2); ?>
<section class="section-surface">
    <div class="container">
        <div class="section-heading text-center mx-auto">
            <span class="section-kicker"><?= htmlspecialchars(getSectionKicker($section, 'FAQs')) ?></span>
            <h2 class="section-title mt-3"><?= htmlspecialchars($section['title'] ?? '') ?></h2>
            <?php if (!empty($section['subtitle'])): ?>
                <p class="section-subtitle mx-auto mt-3"><?= htmlspecialchars($section['subtitle']) ?></p>
            <?php endif; ?>
        </div>

        <div class="faq-shell mx-auto">
            <div class="accordion" id="accordion-<?= (int) $section['id'] ?>">
                <?php foreach ($faqItems as $index => $item): ?>
                    <div class="accordion-item faq-card mb-3">
                        <h2 class="accordion-header" id="faq-heading-<?= (int) $section['id'] ?>-<?= $index ?>">
                            <button class="accordion-button <?= $index !== 0 ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#faq-collapse-<?= (int) $section['id'] ?>-<?= $index ?>" aria-expanded="<?= $index === 0 ? 'true' : 'false' ?>">
                                <?= htmlspecialchars($item[0]) ?>
                            </button>
                        </h2>
                        <div id="faq-collapse-<?= (int) $section['id'] ?>-<?= $index ?>" class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>" data-bs-parent="#accordion-<?= (int) $section['id'] ?>">
                            <div class="accordion-body">
                                <?= htmlspecialchars($item[1]) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
