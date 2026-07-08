<?php $whatsappLink = buildWhatsappLink(); ?>
<?php if ($whatsappLink !== '#'): ?>
<a href="<?= htmlspecialchars($whatsappLink) ?>" class="whatsapp-btn" target="_blank" rel="noopener noreferrer" title="Chat on WhatsApp">
    <i class="bi bi-whatsapp" aria-hidden="true"></i>
    <span class="visually-hidden">Chat on WhatsApp</span>
</a>
<?php endif; ?>
