<section class="hero section-padding" id="services-hero" style="min-height: 85vh; display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 4rem; align-items: center; position: relative; overflow: hidden; padding-bottom: 3rem;">
    
    <!-- Left Column -->
    <div class="hero-left reveal" style="position: relative; z-index: 2;">
        <?php if (!empty($single['badge'])): ?>
            <div class="hero-eyebrow" style="display: inline-flex; align-items: center; gap: 8px; border: 1px solid var(--border); padding: 0.38rem 1.1rem; border-radius: 50px; font-size: 0.7rem; letter-spacing: 0.22em; text-transform: uppercase; color: var(--accent); margin-bottom: 1.5rem;">
                <div class="bdot" style="width: 6px; height: 6px; border-radius: 50%; background: var(--accent); animation: pulse 2s ease-in-out infinite;"></div>
                <?= htmlspecialchars($single['badge']) ?>
            </div>
        <?php endif; ?>
        
        <h1 style="font-size: clamp(2rem, 4.5vw, 3.5rem); font-weight: 800; line-height: 1.15; color: var(--text-main); margin-bottom: 1rem; font-family: var(--font-heading);">
            <?= $single['title'] ?? 'Des solutions sur mesure pour votre business' ?>
        </h1>

        <div class="accent-bar" style="display: flex; gap: 4px; margin: 1.5rem 0;">
            <span style="width: 36px; height: 3px; border-radius: 2px; background: #e03a3a;"></span>
            <span style="width: 36px; height: 3px; border-radius: 2px; background: #2eaa5c;"></span>
            <span style="width: 36px; height: 3px; border-radius: 2px; background: #f5b800;"></span>
            <span style="width: 36px; height: 3px; border-radius: 2px; background: #f07820;"></span>
            <span style="width: 36px; height: 3px; border-radius: 2px; background: #1a6fba;"></span>
        </div>

        <p style="font-size: 1rem; line-height: 1.8; color: var(--text-muted); margin-bottom: 2.5rem; max-width: 540px;">
            <?= htmlspecialchars($single['subtitle'] ?? '') ?>
        </p>

        <div class="hero-actions" style="display: flex; gap: 16px; flex-wrap: wrap;">
            <a href="<?= url('/contact') ?>" class="btn-hero-primary" style="padding: 14px 30px; font-weight: 600; border-radius: 8px;">
                <span>Demander un devis</span>
                <i data-lucide="arrow-up-right" style="width: 16px; height: 16px;"></i>
            </a>
            <a href="<?= url('/services#services-grid') ?>" class="btn-hero-secondary" style="padding: 14px 30px; font-weight: 600; border-radius: 8px;">
                <span>Voir nos Expertises</span>
            </a>
        </div>
    </div>

    <!-- Right Column -->
    <div class="hero-right reveal" style="position: relative; z-index: 2;">
        <div class="hero-panel" style="background: var(--bg-surface); border: 1px solid var(--border); border-radius: 16px; padding: 2rem;">
            <div class="panel-title" style="font-size: 0.72rem; letter-spacing: 0.25em; text-transform: uppercase; color: var(--text-muted); margin-bottom: 1.5rem; font-weight: 600;">Nos 5 domaines d'expertise</div>
            
            <div class="panel-services" style="display: flex; flex-direction: column; gap: 12px;">
                <div class="ps-item" style="display: flex; align-items: center; gap: 14px; padding: 0.8rem 1.2rem; background: var(--bg-surface-alt); border: 1px solid var(--border); border-radius: 8px; transition: var(--transition);">
                    <div class="ps-dot" style="width: 8px; height: 8px; border-radius: 50%; background: #1a6fba; flex-shrink: 0;"></div>
                    <span class="ps-label" style="font-size: 0.88rem; color: var(--text-main); font-weight: 500;">Web Design & Développement</span>
                    <i data-lucide="arrow-right" class="ps-arrow" style="margin-left: auto; width: 14px; height: 14px; color: var(--text-muted);"></i>
                </div>
                <div class="ps-item" style="display: flex; align-items: center; gap: 14px; padding: 0.8rem 1.2rem; background: var(--bg-surface-alt); border: 1px solid var(--border); border-radius: 8px; transition: var(--transition);">
                    <div class="ps-dot" style="width: 8px; height: 8px; border-radius: 50%; background: #2eaa5c; flex-shrink: 0;"></div>
                    <span class="ps-label" style="font-size: 0.88rem; color: var(--text-main); font-weight: 500;">Maintenance Informatique</span>
                    <i data-lucide="arrow-right" class="ps-arrow" style="margin-left: auto; width: 14px; height: 14px; color: var(--text-muted);"></i>
                </div>
                <div class="ps-item" style="display: flex; align-items: center; gap: 14px; padding: 0.8rem 1.2rem; background: var(--bg-surface-alt); border: 1px solid var(--border); border-radius: 8px; transition: var(--transition);">
                    <div class="ps-dot" style="width: 8px; height: 8px; border-radius: 50%; background: #f07820; flex-shrink: 0;"></div>
                    <span class="ps-label" style="font-size: 0.88rem; color: var(--text-main); font-weight: 500;">Câblage Réseaux</span>
                    <i data-lucide="arrow-right" class="ps-arrow" style="margin-left: auto; width: 14px; height: 14px; color: var(--text-muted);"></i>
                </div>
                <div class="ps-item" style="display: flex; align-items: center; gap: 14px; padding: 0.8rem 1.2rem; background: var(--bg-surface-alt); border: 1px solid var(--border); border-radius: 8px; transition: var(--transition);">
                    <div class="ps-dot" style="width: 8px; height: 8px; border-radius: 50%; background: #e03a3a; flex-shrink: 0;"></div>
                    <span class="ps-label" style="font-size: 0.88rem; color: var(--text-main); font-weight: 500;">Montage Vidéo TikTok & YouTube</span>
                    <i data-lucide="arrow-right" class="ps-arrow" style="margin-left: auto; width: 14px; height: 14px; color: var(--text-muted);"></i>
                </div>
                <div class="ps-item" style="display: flex; align-items: center; gap: 14px; padding: 0.8rem 1.2rem; background: var(--bg-surface-alt); border: 1px solid var(--border); border-radius: 8px; transition: var(--transition);">
                    <div class="ps-dot" style="width: 8px; height: 8px; border-radius: 50%; background: #f5b800; flex-shrink: 0;"></div>
                    <span class="ps-label" style="font-size: 0.88rem; color: var(--text-main); font-weight: 500;">Créateur de Contenu Digital</span>
                    <i data-lucide="arrow-right" class="ps-arrow" style="margin-left: auto; width: 14px; height: 14px; color: var(--text-muted);"></i>
                </div>
            </div>

            <div class="panel-bottom" style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-top: 1.5rem;">
                <div class="pstat" style="text-align: center; padding: 1rem; background: var(--bg-surface-alt); border: 1px solid var(--border); border-radius: 8px;">
                    <div class="pstat-num" style="font-size: 1.8rem; font-weight: 700; color: var(--text-main); font-family: var(--font-heading);"><?= htmlspecialchars($single['pstat_clients'] ?? '100+') ?></div>
                    <div class="pstat-label" style="font-size: 0.68rem; letter-spacing: 0.12em; text-transform: uppercase; color: var(--text-muted); margin-top: 0.3rem;">Clients satisfaits</div>
                </div>
                <div class="pstat" style="text-align: center; padding: 1rem; background: var(--bg-surface-alt); border: 1px solid var(--border); border-radius: 8px;">
                    <div class="pstat-num" style="font-size: 1.8rem; font-weight: 700; color: var(--text-main); font-family: var(--font-heading);"><?= htmlspecialchars($single['pstat_years'] ?? '7+') ?></div>
                    <div class="pstat-label" style="font-size: 0.68rem; letter-spacing: 0.12em; text-transform: uppercase; color: var(--text-muted); margin-top: 0.3rem;">Années d'expérience</div>
                </div>
            </div>
        </div>
    </div>

</section>

<style>
.ps-item:hover {
    border-color: rgba(99, 102, 241, 0.45) !important;
}
.ps-item:hover .ps-arrow {
    color: var(--accent) !important;
    transform: translateX(4px);
}
</style>
