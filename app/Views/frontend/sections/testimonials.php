<section class="section-padding" id="testimonials">
    <div class="container">
        
        <div class="section-header">
            <span class="section-badge">Témoignages</span>
            <h2 class="section-title"><?= htmlspecialchars($single['title'] ?? 'Avis de Nos Partenaires') ?></h2>
            <p class="section-subtitle"><?= htmlspecialchars($single['subtitle'] ?? 'Découvrez les retours d\'expérience des directeurs techniques et fondateurs qui nous font confiance.') ?></p>
        </div>

        <div class="testimonials-grid">
            <?php if (!empty($groups)): ?>
                <?php foreach ($groups as $testi): 
                    $rating = (int)($testi['client_rating'] ?? 5);
                    if ($rating < 1) $rating = 1;
                    if ($rating > 5) $rating = 5;
                ?>
                    <div class="testimonial-card">
                        <div>
                            <div class="rating-stars">
                                <?php for ($i = 0; $i < 5; $i++): ?>
                                    <i data-lucide="star" style="width: 16px; height: 16px; fill: <?= $i < $rating ? '#fbbf24' : 'none' ?>; color: #fbbf24;"></i>
                                <?php endfor; ?>
                            </div>
                            <p class="testimonial-text">
                                "<?= htmlspecialchars($testi['client_quote'] ?? '') ?>"
                            </p>
                        </div>
                        <div class="testimonial-client">
                            <div class="client-avatar">
                                <?php if (!empty($testi['client_avatar'])): ?>
                                    <img src="<?= htmlspecialchars(url($testi['client_avatar'])) ?>" alt="<?= htmlspecialchars($testi['client_name'] ?? 'Client') ?>" loading="lazy">
                                <?php else: ?>
                                    <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #1e1b4b 0%, #311042 100%); display:flex; align-items:center; justify-content:center;">
                                        <i data-lucide="user" style="width: 20px; height: 20px; color: var(--primary);"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="client-info">
                                <span class="client-name"><?= htmlspecialchars($testi['client_name'] ?? 'Nom Client') ?></span>
                                <span class="client-company"><?= htmlspecialchars($testi['client_company'] ?? 'Entreprise') ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align: center; color: var(--text-muted); grid-column: 1/-1;">
                    Aucun témoignage configuré pour le moment.
                </div>
            <?php endif; ?>
        </div>

    </div>
</section>
