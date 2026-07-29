<?php
/**
 * Section: team — Notre Équipe (grille de membres)
 * Données CMS : $single (title, subtitle), $groups (member_avatar, member_name, member_role, member_linkedin, member_twitter, member_github)
 * Design System v4.1 — variables CSS uniquement
 */
?>

<section class="section-padding team-section" id="team">
    <div class="container">

        <!-- Section header -->
        <div class="section-header reveal">
            <span class="section-badge">Équipe</span>
            <h2 class="section-title"><?= htmlspecialchars($single['title'] ?? 'Nos Experts') ?></h2>
            <p class="section-subtitle"><?= htmlspecialchars($single['subtitle'] ?? 'Une équipe d\'ingénieurs d\'élite et de concepteurs chevronnés.') ?></p>
            <div class="section-divider"></div>
        </div>

        <!-- Team grid -->
        <div class="team-grid">
            <?php if (!empty($groups)):
                foreach ($groups as $idx => $member):
                    $linkedin = $member['member_linkedin'] ?? '';
                    $twitter  = $member['member_twitter']  ?? '';
                    $github   = $member['member_github']   ?? '';
            ?>
                <div class="team-card reveal" style="transition-delay:<?= min($idx * 80, 400) ?>ms;">
                    <!-- Avatar -->
                    <div class="team-avatar-ring">
                        <?php if (!empty($member['member_avatar'])): ?>
                            <img src="<?= htmlspecialchars(url($member['member_avatar'])) ?>"
                                 alt="<?= htmlspecialchars($member['member_name'] ?? 'Membre') ?>"
                                 loading="lazy" class="team-avatar-img">
                        <?php else: ?>
                            <div class="team-avatar-placeholder">
                                <i data-lucide="user" style="width:40px;height:40px;color:var(--primary);opacity:0.5;"></i>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Info -->
                    <div class="team-info">
                        <h3 class="team-name"><?= htmlspecialchars($member['member_name'] ?? 'Nom du Membre') ?></h3>
                        <p class="team-role"><?= htmlspecialchars($member['member_role'] ?? 'Consultant') ?></p>

                        <!-- Socials -->
                        <?php if (!empty($linkedin) || !empty($twitter) || !empty($github)): ?>
                            <div class="team-socials">
                                <?php if (!empty($linkedin)): ?>
                                    <a href="<?= htmlspecialchars(url($linkedin)) ?>" class="team-social-link" target="_blank" rel="noopener" aria-label="LinkedIn">
                                        <i data-lucide="linkedin" style="width:16px;height:16px;"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if (!empty($twitter)): ?>
                                    <a href="<?= htmlspecialchars(url($twitter)) ?>" class="team-social-link" target="_blank" rel="noopener" aria-label="Twitter">
                                        <i data-lucide="twitter" style="width:16px;height:16px;"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if (!empty($github)): ?>
                                    <a href="<?= htmlspecialchars(url($github)) ?>" class="team-social-link" target="_blank" rel="noopener" aria-label="GitHub">
                                        <i data-lucide="github" style="width:16px;height:16px;"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; else: ?>

                <!-- Fallback team membres par défaut (depuis settings) -->
                <?php
                $defaultMembers = [
                    ['icon' => 'shield-check', 'name' => 'Alexandre Dumas', 'role' => 'Lead Architect & Fondateur'],
                    ['icon' => 'terminal',     'name' => 'Thomas Morel',    'role' => 'Expert Core PHP & SecOps'],
                ];
                foreach ($defaultMembers as $dm):
                ?>
                <div class="team-card reveal">
                    <div class="team-avatar-ring">
                        <div class="team-avatar-placeholder">
                            <i data-lucide="<?= $dm['icon'] ?>" style="width:40px;height:40px;color:var(--primary);opacity:0.6;"></i>
                        </div>
                    </div>
                    <div class="team-info">
                        <h3 class="team-name"><?= htmlspecialchars($dm['name']) ?></h3>
                        <p class="team-role"><?= htmlspecialchars($dm['role']) ?></p>
                        <div class="team-socials">
                            <a href="<?= htmlspecialchars($settings['social_linkedin'] ?? url('/contact')) ?>"
                               class="team-social-link" target="_blank" rel="noopener" aria-label="LinkedIn">
                                <i data-lucide="linkedin" style="width:16px;height:16px;"></i>
                            </a>
                            <a href="<?= htmlspecialchars($settings['social_github'] ?? url('/contact')) ?>"
                               class="team-social-link" target="_blank" rel="noopener" aria-label="GitHub">
                                <i data-lucide="github" style="width:16px;height:16px;"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; endif; ?>
        </div>

    </div>
</section>

<style>
/* ── Team section ──────────────────────────────────────────────── */
.team-section { background: var(--bg-alt); }

.team-card {
    text-align: center;
    padding: 36px 28px 28px;
}

/* Avatar with gradient ring */
.team-avatar-ring {
    width: 96px; height: 96px;
    border-radius: 50%;
    margin: 0 auto 20px;
    padding: 3px;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    transition: var(--transition);
    position: relative;
}

.team-card:hover .team-avatar-ring {
    transform: scale(1.05);
    box-shadow: 0 8px 28px rgba(13,148,136,0.28);
}

.team-avatar-img {
    width: 100%; height: 100%;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid var(--bg-card);
    display: block;
}

.team-avatar-placeholder {
    width: 100%; height: 100%;
    border-radius: 50%;
    background: linear-gradient(135deg, #1e1b4b 0%, #0f172a 100%);
    border: 3px solid var(--bg-card);
    display: flex; align-items: center; justify-content: center;
}

/* Info */
.team-name {
    font-size: 1rem;
    font-weight: 700;
    color: var(--text-main);
    margin-bottom: 4px;
    font-family: var(--font-heading);
}

.team-role {
    font-size: 0.8rem;
    color: var(--text-muted);
    font-weight: 500;
    margin-bottom: 16px;
    letter-spacing: 0.04em;
}

/* Social links */
.team-socials {
    display: flex;
    gap: 8px;
    justify-content: center;
    flex-wrap: wrap;
    margin-top: 4px;
}

.team-social-link {
    width: 34px; height: 34px;
    border-radius: 50%;
    background: rgba(13,148,136,0.06);
    border: 1.5px solid rgba(13,148,136,0.15);
    color: var(--text-muted);
    display: flex; align-items: center; justify-content: center;
    transition: var(--transition-fast);
}

.team-social-link:hover {
    background: var(--primary);
    border-color: var(--primary);
    color: #fff;
    transform: translateY(-3px);
    box-shadow: 0 6px 16px rgba(13,148,136,0.28);
}
</style>
