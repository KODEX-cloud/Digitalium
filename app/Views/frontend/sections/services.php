<section class="section-padding" id="services">
    <div class="container">
        
        <div class="section-header">
            <span class="section-badge">Expertises</span>
            <h2 class="section-title"><?= htmlspecialchars($single['title'] ?? 'Nos Services Premium') ?></h2>
            <p class="section-subtitle"><?= htmlspecialchars($single['subtitle'] ?? 'Des solutions sur mesure conçues par des experts.') ?></p>
        </div>

        <div class="services-grid">
            <?php if (!empty($groups)): ?>
                <?php foreach ($groups as $service): 
                    $hasLink = !empty($service['card_link']);
                    $cardTag = $hasLink ? 'a' : 'div';
                    $cardAttr = $hasLink ? 'href="' . htmlspecialchars($service['card_link']) . '" style="text-decoration:none;"' : '';
                ?>
                    <<?= $cardTag ?> <?= $cardAttr ?> class="service-card">
                        <div class="service-icon-box" style="overflow: hidden; display: flex; align-items: center; justify-content: center;">
                            <?php if (!empty($service['card_image'])): ?>
                                <img src="<?= htmlspecialchars($service['card_image']) ?>" alt="<?= htmlspecialchars($service['card_title'] ?? '') ?>" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <i data-lucide="<?= htmlspecialchars($service['card_icon'] ?? 'laptop') ?>" style="width: 28px; height: 28px;"></i>
                            <?php endif; ?>
                        </div>
                        <h3 class="service-title"><?= htmlspecialchars($service['card_title'] ?? 'Titre du service') ?></h3>
                        <p class="service-description"><?= htmlspecialchars($service['card_description'] ?? '') ?></p>
                    </<?= $cardTag ?>>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align: center; color: var(--text-muted); grid-column: 1/-1;">
                    Aucun service configuré pour le moment.
                </div>
            <?php endif; ?>
        </div>

    </div>
</section>
