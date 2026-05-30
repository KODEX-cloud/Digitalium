<section class="newsletter section-padding" style="background: var(--bg-surface); text-align: center; border-bottom: 1px solid var(--border);">
    <div class="container reveal">
        
        <span class="section-tag" style="display: inline-flex; align-items: center; gap: 8px; font-size: 0.68rem; letter-spacing: 0.3em; text-transform: uppercase; color: var(--primary); margin-bottom: 1rem;"><?= htmlspecialchars($single['tag'] ?? 'Newsletter') ?></span>
        <h2 style="font-size: clamp(1.6rem, 3vw, 2.2rem); font-weight: 700; color: var(--text-main); margin-bottom: 0.8rem; font-family: var(--font-heading);"><?= htmlspecialchars($single['title'] ?? 'Restez à la pointe du digital') ?></h2>
        <p style="font-size: 0.95rem; color: var(--text-muted); margin-bottom: 2.5rem; line-height: 1.8; max-width: 600px; margin-left: auto; margin-right: auto;">
            <?= htmlspecialchars($single['subtitle'] ?? 'Recevez chaque semaine nos meilleurs articles sur l\'IA et le marketing digital.') ?>
        </p>

        <form class="newsletter-form" onsubmit="event.preventDefault(); alert('Merci de votre abonnement à notre newsletter !'); this.reset();" style="display: flex; gap: 0; max-width: 480px; margin: 0 auto; border-radius: 8px; overflow: hidden; border: 1px solid var(--border);">
            <input type="email" placeholder="Votre adresse e-mail" required style="flex: 1; background: var(--bg-surface-alt); border: none; outline: none; padding: 0.85rem 1.2rem; color: var(--text-main); font-size: 0.9rem;">
            <button type="submit" style="background: var(--primary); border: none; padding: 0.85rem 1.8rem; color: white; font-size: 0.8rem; letter-spacing: 0.1em; text-transform: uppercase; font-weight: 600; cursor: pointer; transition: var(--transition); white-space: nowrap;">S'abonner</button>
        </form>

    </div>
</section>
