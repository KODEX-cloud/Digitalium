<style>
.error-section {
    min-height: 70vh;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 80px 20px;
    position: relative;
    z-index: 2;
}
.error-code {
    font-size: clamp(6rem, 18vw, 14rem);
    font-weight: 900;
    line-height: 1;
    letter-spacing: -0.05em;
    background: linear-gradient(135deg, #2563eb 0%, #a855f7 50%, #2563eb 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    opacity: 0.15;
    margin-bottom: -20px;
    display: block;
}
</style>

<section class="error-section">
    <div class="container">
        <span class="error-code">404</span>
        <div class="reveal" style="max-width: 520px; margin: 0 auto;">
            <div style="display: inline-flex; align-items: center; gap: 8px; background: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.2); padding: 0.35rem 1rem; border-radius: 50px; font-size: 0.68rem; letter-spacing: 0.2em; text-transform: uppercase; color: #ef4444; margin-bottom: 20px;">
                <i data-lucide="alert-circle" style="width: 12px; height: 12px;"></i>
                Erreur 404
            </div>
            <h1 style="font-size: clamp(1.6rem, 3vw, 2.4rem); font-weight: 800; line-height: 1.2; margin-bottom: 16px;">
                Page introuvable
            </h1>
            <p style="color: var(--text-muted); font-size: 1rem; line-height: 1.7; margin-bottom: 32px;">
                La page que vous recherchez n'existe pas ou a été déplacée.
                Retournez à l'accueil ou explorez nos services.
            </p>
            <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
                <a href="<?= url('/') ?>" class="btn-primary">
                    <i data-lucide="home" style="width: 16px; height: 16px;"></i>
                    <span>Retour à l'accueil</span>
                </a>
                <a href="<?= url('/realisations') ?>" class="btn-secondary">
                    <i data-lucide="briefcase" style="width: 16px; height: 16px;"></i>
                    <span>Nos réalisations</span>
                </a>
            </div>
        </div>
    </div>
</section>
