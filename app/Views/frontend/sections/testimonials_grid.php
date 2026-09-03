<section class="testimonial-section section-padding" id="testimonials-grid">
    <div class="container">
        
        <div style="text-align: center; max-width: 600px; margin: 0 auto 3rem auto;" class="reveal">
            <span class="section-tag" style="display: inline-flex; align-items: center; gap: 8px; font-size: 0.68rem; letter-spacing: 0.3em; text-transform: uppercase; color: var(--primary); margin-bottom: 1rem;"><?= htmlspecialchars($single['tag'] ?? 'Avis') ?></span>
            <h2 class="section-title" style="color: var(--text-main); font-family: var(--font-heading);"><?= htmlspecialchars($single['title'] ?? 'Ce que disent nos clients') ?></h2>
        </div>

        <div class="testi-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 30px; max-width: 1100px; margin: 3rem auto 0 auto;">
            <?php if (!empty($groups)): ?>
                <?php foreach ($groups as $testi): 
                    $rating = (int)($testi['client_rating'] ?? 5);
                ?>
                    <div class="tcard reveal" style="padding: 2rem; background: var(--bg-surface); border: 1px solid var(--border); border-radius: 12px; transition: var(--transition); display: flex; flex-direction: column; justify-content: space-between; min-height: 220px;">
                        <div>
                            <div class="tcard-stars" style="display: flex; gap: 4px; margin-bottom: 1.2rem;">
                                <?php for ($i = 0; $i < 5; $i++): ?>
                                    <i data-lucide="star" style="width: 14px; height: 14px; fill: <?= $i < $rating ? '#fbbf24' : 'none' ?>; color: #fbbf24;"></i>
                                <?php endfor; ?>
                            </div>
                            <p class="tcard-text" style="font-size: 0.88rem; line-height: 1.7; color: var(--text-muted); margin-bottom: 1.5rem; font-style: italic;">
                                "<?= htmlspecialchars($testi['client_quote'] ?? '') ?>"
                            </p>
                        </div>
                        <div class="tcard-author" style="display: flex; align-items: center; gap: 12px; border-top: 1px solid var(--border); padding-top: 1rem;">
                            <div class="tcard-av" style="width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; background: var(--primary-glow); color: var(--accent); border: 1px solid var(--border);">
                                <?= htmlspecialchars($testi['client_avatar'] ?? 'U') ?>
                            </div>
                            <div>
                                <div class="tcard-name" style="font-size: 0.88rem; font-weight: 700; color: var(--text-main); font-family: var(--font-heading);"><?= htmlspecialchars($testi['client_name'] ?? '') ?></div>
                                <div class="tcard-role" style="font-size: 0.72rem; color: var(--text-muted);"><?= htmlspecialchars($testi['client_company'] ?? '') ?></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>
</section>

<style>
.tcard:hover {
    border-color: color-mix(in srgb, var(--primary) 40%, transparent) !important;
    transform: translateY(-3px);
}
</style>
