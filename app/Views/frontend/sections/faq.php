<section class="section-padding" id="faq">
    <div class="container">
        
        <div class="section-header">
            <span class="section-badge">FAQ</span>
            <h2 class="section-title"><?= htmlspecialchars($single['title'] ?? 'Questions Fréquentes') ?></h2>
            <p class="section-subtitle"><?= htmlspecialchars($single['subtitle'] ?? 'Des réponses claires et précises à vos interrogations techniques.') ?></p>
        </div>

        <div class="faq-deck">
            <?php if (!empty($groups)): ?>
                <?php foreach ($groups as $index => $item): ?>
                    <div class="faq-item">
                        <button class="faq-question-btn" onclick="toggleFaq(this)" onclick="window.location.href='<?= url('/contact') ?>'">
                            <span><?= htmlspecialchars($item['faq_question'] ?? 'Question sans titre') ?></span>
                            <i data-lucide="chevron-down" style="width: 20px; height: 20px; transition: transform 0.3s;"></i>
                        </button>
                        <div class="faq-answer">
                            <div class="faq-answer-inner">
                                <?= nl2br(htmlspecialchars($item['faq_answer'] ?? '')) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align: center; color: var(--text-muted);">
                    Aucune question configurée pour le moment.
                </div>
            <?php endif; ?>
        </div>

    </div>
</section>

<script>
function toggleFaq(btn) {
    const item = btn.closest('.faq-item');
    const answer = item.querySelector('.faq-answer');
    const icon = btn.querySelector('[data-lucide="chevron-down"]');
    
    // Toggle active class
    const isActive = item.classList.toggle('active');
    
    if (isActive) {
        answer.style.maxHeight = answer.scrollHeight + 'px';
        if (icon) icon.style.transform = 'rotate(180deg)';
    } else {
        answer.style.maxHeight = '0px';
        if (icon) icon.style.transform = 'rotate(0deg)';
    }
}
</script>
