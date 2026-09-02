<?php
/**
 * Section: testimonials_carousel — Témoignages en carrousel (sans notation étoiles)
 * Distinct de "testimonials" (grille statique avec étoiles).
 * Données CMS : $single (tag, title, subtitle), $groups (client_quote, client_name, client_role, client_avatar)
 * Design System v4.1 — variables CSS uniquement, zéro hardcode
 */
$tcId = 'testi-carousel-' . $sectionId;
?>

<section class="section-padding testimonials-carousel-section">
    <div class="container">

        <div class="section-header reveal">
            <?php if (!empty($single['tag'])): ?>
                <span class="section-badge"><?= htmlspecialchars($single['tag']) ?></span>
            <?php endif; ?>
            <?php if (!empty($single['title'])): ?>
                <h2 class="section-title"><?= htmlspecialchars($single['title']) ?></h2>
            <?php endif; ?>
            <?php if (!empty($single['subtitle'])): ?>
                <p class="section-subtitle"><?= htmlspecialchars($single['subtitle']) ?></p>
            <?php endif; ?>
        </div>

        <?php if (!empty($groups)): ?>
        <div class="testi-carousel-wrap reveal">
            <button type="button" class="testi-carousel-nav testi-carousel-prev" data-target="<?= htmlspecialchars($tcId) ?>" aria-label="Témoignage précédent">
                <i data-lucide="chevron-left" style="width:18px;height:18px;"></i>
            </button>

            <div class="testi-carousel-track" id="<?= htmlspecialchars($tcId) ?>">
                <?php foreach ($groups as $testi): ?>
                    <div class="card testi-carousel-card">
                        <i data-lucide="quote" style="width:26px;height:26px;color:var(--primary);opacity:0.25;margin-bottom:16px;"></i>
                        <p class="testi-carousel-text">
                            "<?= htmlspecialchars($testi['client_quote'] ?? '') ?>"
                        </p>
                        <div class="testi-carousel-client">
                            <div class="testi-carousel-avatar">
                                <?php if (!empty($testi['client_avatar'])): ?>
                                    <img src="<?= htmlspecialchars(url($testi['client_avatar'])) ?>" alt="<?= htmlspecialchars($testi['client_name'] ?? '') ?>" loading="lazy">
                                <?php else: ?>
                                    <i data-lucide="user" style="width:18px;height:18px;color:var(--primary);"></i>
                                <?php endif; ?>
                            </div>
                            <div>
                                <div class="testi-carousel-name"><?= htmlspecialchars($testi['client_name'] ?? '') ?></div>
                                <div class="testi-carousel-role"><?= htmlspecialchars($testi['client_role'] ?? '') ?></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="button" class="testi-carousel-nav testi-carousel-next" data-target="<?= htmlspecialchars($tcId) ?>" aria-label="Témoignage suivant">
                <i data-lucide="chevron-right" style="width:18px;height:18px;"></i>
            </button>
        </div>
        <?php endif; ?>

    </div>
</section>

<style>
.testi-carousel-wrap { display: flex; align-items: center; gap: 16px; }

.testi-carousel-track {
    display: flex;
    gap: 24px;
    overflow-x: auto;
    scroll-snap-type: x mandatory;
    scroll-behavior: smooth;
    flex: 1;
    padding: 6px 2px 14px;
    scrollbar-width: none;
}
.testi-carousel-track::-webkit-scrollbar { display: none; }

.testi-carousel-card {
    scroll-snap-align: start;
    flex: 0 0 calc(33.333% - 16px);
    min-width: 280px;
    padding: 32px;
}

.testi-carousel-text {
    font-size: 0.92rem;
    line-height: 1.75;
    color: var(--text-sub);
    margin-bottom: 24px;
}

.testi-carousel-client { display: flex; align-items: center; gap: 12px; }

.testi-carousel-avatar {
    width: 44px; height: 44px; border-radius: 50%;
    background: rgba(37,99,235,0.1);
    border: 2px solid rgba(37,99,235,0.2);
    display: flex; align-items: center; justify-content: center;
    overflow: hidden; flex-shrink: 0;
}
.testi-carousel-avatar img { width: 100%; height: 100%; object-fit: cover; }

.testi-carousel-name { font-weight: 700; font-size: 0.88rem; color: var(--text-main); }
.testi-carousel-role { font-size: 0.78rem; color: var(--text-muted); margin-top: 2px; }

.testi-carousel-nav {
    flex-shrink: 0;
    width: 44px; height: 44px; border-radius: 50%;
    background: var(--bg-card);
    border: 1px solid var(--border);
    color: var(--text-main);
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    transition: var(--transition-fast);
}
.testi-carousel-nav:hover { background: var(--primary); border-color: var(--primary); color: #fff; }

@media (max-width: 900px) {
    .testi-carousel-card { flex: 0 0 calc(70% - 12px); }
}
@media (max-width: 560px) {
    .testi-carousel-card { flex: 0 0 100%; }
    .testi-carousel-wrap { gap: 8px; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.testi-carousel-nav').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var track = document.getElementById(btn.getAttribute('data-target'));
            if (!track) return;
            var card = track.querySelector('.testi-carousel-card');
            var step = card ? (card.getBoundingClientRect().width + 24) : 300;
            track.scrollBy({ left: btn.classList.contains('testi-carousel-next') ? step : -step, behavior: 'smooth' });
        });
    });
});
</script>
