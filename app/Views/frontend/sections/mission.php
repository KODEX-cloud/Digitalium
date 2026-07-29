<?php
/**
 * Section: mission — Notre Mission (split layout)
 * Données CMS : $single (tag, title, description), $groups (card_icon, card_title, card_description)
 * Design System v4.1 — variables CSS uniquement
 */
?>

<section class="section-padding mission-section" id="mission">

    <!-- Background decoration -->
    <div aria-hidden="true" class="mission-glow-left"></div>

    <div class="container">
        <div class="mission-wrap">

            <!-- ── LEFT — Tag + Titre + Description ──────────── -->
            <div class="reveal">
                <span class="section-badge">
                    <?= htmlspecialchars($single['tag'] ?? 'Notre Mission') ?>
                </span>

                <h2 class="section-title" style="margin-top:0;margin-bottom:16px;">
                    <?= htmlspecialchars($single['title'] ?? 'Votre succès, notre engagement') ?>
                </h2>

                <div class="section-divider" style="margin:0 0 28px 0;"></div>

                <div class="section-sub" style="text-align:left;margin:0;max-width:100%;">
                    <?= $single['description'] ?? '' ?>
                </div>
            </div>

            <!-- ── RIGHT — Mission Cards ──────────────────────── -->
            <div class="mission-cards reveal" style="transition-delay:0.12s;">
                <?php if (!empty($groups)):
                    $mColors = ['var(--primary)', 'var(--secondary)', 'var(--accent)', 'var(--primary)'];
                    foreach ($groups as $idx => $card):
                        $borderColor = $mColors[$idx % count($mColors)];
                ?>
                    <div class="mcard mission-card" style="border-left:3px solid <?= $borderColor ?>;">
                        <div class="mission-card-icon" style="background:linear-gradient(135deg,<?= $borderColor ?>,var(--secondary));">
                            <i data-lucide="<?= htmlspecialchars($card['card_icon'] ?? 'check') ?>" style="width:18px;height:18px;color:#fff;"></i>
                        </div>
                        <div class="mission-card-body">
                            <h4 class="mission-card-title">
                                <?= htmlspecialchars($card['card_title'] ?? '') ?>
                            </h4>
                            <p class="mission-card-desc">
                                <?= htmlspecialchars($card['card_description'] ?? '') ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; endif; ?>
            </div>

        </div>
    </div>
</section>

<style>
/* ── Mission section ───────────────────────────────────────────── */
.mission-section {
    background: var(--bg-alt);
    position: relative;
    overflow: hidden;
}

.mission-glow-left {
    position: absolute;
    bottom: -100px; left: -100px;
    width: 500px; height: 500px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(8,145,178,0.06) 0%, transparent 70%);
    pointer-events: none; z-index: 0;
}

/* Layout */
.mission-wrap {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 64px;
    align-items: center;
    position: relative; z-index: 1;
}

/* Cards list */
.mission-cards { display: flex; flex-direction: column; gap: 14px; }

.mission-card {
    display: flex;
    gap: 18px;
    align-items: flex-start;
    padding: 20px 24px;
    border-radius: var(--radius-sm) var(--radius-md) var(--radius-md) var(--radius-sm);
}

.mission-card:hover { border-left-width: 4px !important; }

.mission-card-icon {
    width: 42px; height: 42px;
    border-radius: var(--radius-md);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 6px 16px rgba(13,148,136,0.22);
    transition: var(--transition);
}

.mission-card:hover .mission-card-icon {
    transform: scale(1.08) translateY(-2px);
    box-shadow: 0 10px 24px rgba(13,148,136,0.32);
}

.mission-card-body { flex: 1; }

.mission-card-title {
    font-size: 0.9rem;
    font-weight: 700;
    color: var(--text-main);
    margin-bottom: 6px;
    font-family: var(--font-heading);
    line-height: 1.3;
    letter-spacing: 0.02em;
}

.mission-card-desc {
    font-size: 0.84rem;
    line-height: 1.65;
    color: var(--text-muted);
    margin: 0;
}

/* Responsive */
@media (max-width: 768px) {
    .mission-wrap { gap: 44px; }
    .mission-card { padding: 18px 20px; }
}
</style>
