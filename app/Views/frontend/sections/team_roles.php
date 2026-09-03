<?php
/**
 * Section: team_roles — Rôles de l'équipe (grille de rôles/expertises)
 * Données CMS : $single (tag, title), $groups (role_avatar, role_image, role_title, role_sub, role_link)
 * Design System v4.1 — variables CSS uniquement
 */
?>

<section class="section-padding team-roles-section" id="team-roles">
    <div class="container team-inner">

        <!-- Section header -->
        <div class="team-header reveal" style="text-align:center;margin-bottom:4rem;">
            <span class="section-badge">
                <?= htmlspecialchars($single['tag'] ?? 'Notre Équipe') ?>
            </span>
            <h2 class="section-title">
                <?= htmlspecialchars($single['title'] ?? 'Des experts à votre service') ?>
            </h2>
            <div class="section-divider"></div>
        </div>

        <!-- Roles grid -->
        <div class="team-roles-grid">
            <?php if (!empty($groups)):
                foreach ($groups as $idx => $role):
                    $hasLink = !empty($role['role_link']);
                    $cardTag = $hasLink ? 'a' : 'div';
                    $cardAttr = $hasLink ? 'href="' . htmlspecialchars($role['role_link']) . '" rel="noopener"' : '';
            ?>
                <<?= $cardTag ?> <?= $cardAttr ?> class="role-card reveal" style="transition-delay:<?= min($idx * 80, 400) ?>ms;">
                    <!-- Avatar / Icon -->
                    <div class="role-avatar-wrap">
                        <?= \App\Helpers\IconHelper::render($role['role_avatar'] ?? 'user', ['image' => $role['role_image'] ?? '', 'size' => '24px']) ?>
                    </div>

                    <!-- Text -->
                    <h3 class="role-card-title">
                        <?= htmlspecialchars($role['role_title'] ?? '') ?>
                    </h3>
                    <div class="role-card-sub">
                        <?= htmlspecialchars($role['role_sub'] ?? '') ?>
                    </div>

                    <!-- Arrow hint if link -->
                    <?php if ($hasLink): ?>
                        <div class="role-card-arrow">
                            <i data-lucide="arrow-right" style="width:14px;height:14px;"></i>
                        </div>
                    <?php endif; ?>

                </<?= $cardTag ?>>
            <?php endforeach; endif; ?>
        </div>

    </div>
</section>

<style>
/* ── Team Roles section ────────────────────────────────────────── */
.team-roles-section {
    background: var(--bg-alt);
    border-top: 1px solid var(--border);
}

.team-roles-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
}

.role-card {
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 2.2rem 1.2rem 2rem;
    text-decoration: none;
    position: relative;
}

/* Gradient top bar on hover */
.role-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--primary), var(--secondary));
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    border-radius: var(--radius-lg) var(--radius-lg) 0 0;
}

.role-card:hover::before { transform: scaleX(1); }

/* Avatar */
.role-avatar-wrap {
    width: 60px; height: 60px;
    border-radius: 50%;
    border: 2px solid rgba(13,148,136,0.2);
    background: linear-gradient(135deg, rgba(13,148,136,0.08), color-mix(in srgb, var(--secondary) 4%, transparent));
    color: var(--primary);
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 1.2rem auto;
    overflow: hidden;
    padding: 12px;
    transition: var(--transition);
}

.role-card:hover .role-avatar-wrap {
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    border-color: var(--primary);
    color: #fff;
    box-shadow: 0 8px 24px rgba(13,148,136,0.28);
    transform: scale(1.08);
}

.role-card-title {
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--text-main);
    margin-bottom: 0.3rem;
    font-family: var(--font-heading);
    line-height: 1.3;
}

.role-card-sub {
    font-size: 0.75rem;
    color: var(--text-muted);
    letter-spacing: 0.05em;
    font-weight: 500;
    text-transform: uppercase;
}

/* Arrow hint */
.role-card-arrow {
    margin-top: 14px;
    color: var(--primary);
    opacity: 0;
    transform: translateX(-6px);
    transition: var(--transition-fast);
}

.role-card:hover .role-card-arrow {
    opacity: 1;
    transform: translateX(0);
}

.role-card:hover {
    border-color: rgba(13,148,136,0.25) !important;
    transform: translateY(-5px) !important;
}

/* Responsive */
@media (max-width: 768px) {
    .team-roles-grid { grid-template-columns: 1fr 1fr; gap: 14px; }
    .role-card { padding: 1.8rem 1rem; }
}

@media (max-width: 480px) {
    .team-roles-grid { grid-template-columns: 1fr; }
}
</style>
