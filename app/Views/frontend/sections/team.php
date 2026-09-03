<?php
/**
 * Section: team — Notre Équipe (grille de membres)
 * Données CMS : $single (tag, title, subtitle, more_text, more_url),
 *               $groups (member_avatar, member_name, member_role, member_dept, member_linkedin, member_twitter, member_github)
 * Règle #2 (zéro hardcode) : aucun texte affiché n'est écrit en dur — tout provient
 * des blocs CMS, et chaque élément non renseigné est simplement masqué.
 * Design System v4.1 — variables CSS uniquement
 */
$teamAccents = ['var(--primary)', 'var(--secondary)', 'var(--accent)', 'var(--primary)'];
?>

<section class="section-padding team-section" id="team">
    <div class="container">

        <!-- Section header -->
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
                                 alt="<?= htmlspecialchars($member['member_name'] ?? '') ?>"
                                 loading="lazy" class="team-avatar-img">
                        <?php else: ?>
                            <div class="team-avatar-placeholder">
                                <i data-lucide="user" style="width:40px;height:40px;color:var(--primary);opacity:0.5;"></i>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Info -->
                    <div class="team-info">
                        <?php if (!empty($member['member_name'])): ?>
                            <h3 class="team-name"><?= htmlspecialchars($member['member_name']) ?></h3>
                        <?php endif; ?>
                        <?php if (!empty($member['member_role'])): ?>
                            <p class="team-role"><?= htmlspecialchars($member['member_role']) ?></p>
                        <?php endif; ?>

                        <?php if (!empty($member['member_dept'])): ?>
                            <span class="team-dept-pill" style="background:<?= $teamAccents[$idx % count($teamAccents)] ?>;">
                                <?= htmlspecialchars($member['member_dept']) ?>
                            </span>
                        <?php endif; ?>

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
            <?php endforeach; endif; ?>
        </div>

        <?php if (!empty($single['more_text']) && !empty($single['more_url'])): ?>
            <div style="text-align:center;margin-top:44px;">
                <a href="<?= htmlspecialchars(url($single['more_url'])) ?>" style="display:inline-flex;align-items:center;gap:8px;font-size:0.85rem;font-weight:700;color:var(--primary);letter-spacing:0.04em;">
                    <span><?= htmlspecialchars($single['more_text']) ?></span>
                    <i data-lucide="arrow-right" style="width:15px;height:15px;"></i>
                </a>
            </div>
        <?php endif; ?>

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
    box-shadow: 0 8px 28px color-mix(in srgb, var(--primary) 28%, transparent);
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
    background: linear-gradient(135deg, var(--surface-dark) 0%, var(--surface-dark) 100%);
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
    margin-bottom: 12px;
    letter-spacing: 0.04em;
}

.team-dept-pill {
    display: inline-block;
    padding: 5px 16px;
    margin-bottom: 16px;
    border-radius: var(--radius-pill);
    color: #fff;
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
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
    background: color-mix(in srgb, var(--primary) 6%, transparent);
    border: 1.5px solid color-mix(in srgb, var(--primary) 15%, transparent);
    color: var(--text-muted);
    display: flex; align-items: center; justify-content: center;
    transition: var(--transition-fast);
}

.team-social-link:hover {
    background: var(--primary);
    border-color: var(--primary);
    color: #fff;
    transform: translateY(-3px);
    box-shadow: 0 6px 16px color-mix(in srgb, var(--primary) 28%, transparent);
}
</style>
