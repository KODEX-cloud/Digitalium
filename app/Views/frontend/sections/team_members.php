<?php
/**
 * Section : team_members — L'équipe, avec repli sur les pôles d'expertise
 *
 * Deux rendus dans une seule section, et c'est délibéré :
 *
 *   1. dès qu'un collaborateur PUBLIÉ existe dans `team_members`, la grille des
 *      personnes s'affiche (photo, nom, fonction, bio courte, LinkedIn) ;
 *   2. tant qu'il n'y en a aucun, la grille des PÔLES D'EXPERTISE s'affiche.
 *
 * Le cahier des charges interdit d'inventer un membre d'équipe. Le repli n'est
 * donc pas un mode dégradé : c'est l'état normal du site tant que l'équipe
 * n'est pas publiée. Le placer DANS la section évite d'obliger l'administrateur
 * à basculer un type de section le jour où il saisit son premier collaborateur
 * — ce jour-là, la page change d'elle-même.
 *
 * Blocs attendus — TOUT est administrable (Règle #2) :
 *   single : tag, title, subtitle
 *            poles_title      titre affiché au-dessus des pôles (repli)
 *   groups : pole_icon        nom d'icône Lucide
 *            pole_title       nom du pôle (vider ce champ retire le pôle)
 *            pole_desc        précision affichée sous le nom
 *
 * Les membres viennent de /admin/team, jamais des blocs : eux seuls peuvent
 * être publiés ou dépubliés individuellement.
 *
 * Design System v4.1 — variables CSS uniquement.
 */

$membres = \App\Models\TeamMember::getPublic();
$poles   = [];
foreach (($groups ?? []) as $g) {
    if (trim((string)($g['pole_title'] ?? '')) !== '') { $poles[] = $g; }
}

$tmAccents = ['var(--primary)', 'var(--secondary)', 'var(--accent)'];
?>

<section class="section-padding tm-section" id="equipe">
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
            <div class="section-divider"></div>
        </div>

        <?php if (!empty($membres)): ?>
            <!-- ── Collaborateurs réels, saisis dans /admin/team ────────────── -->
            <div class="tm-grid">
                <?php foreach ($membres as $i => $m):
                    $accent  = $tmAccents[$i % count($tmAccents)];
                    $nom     = trim((string)($m['name'] ?? ''));
                    if ($nom === '') { continue; }
                    $photo   = trim((string)($m['photo'] ?? ''));
                    $lien    = trim((string)($m['linkedin'] ?? ''));
                    $pole    = \App\Models\TeamMember::libelleDepartement($m['department'] ?? '');
                    // Initiales : un portrait manquant ne doit pas laisser un trou.
                    $mots    = preg_split('/\s+/', $nom) ?: [];
                    $init    = '';
                    foreach (array_slice($mots, 0, 2) as $mot) {
                        $init .= function_exists('mb_substr') ? mb_substr($mot, 0, 1) : substr($mot, 0, 1);
                    }
                    $init = function_exists('mb_strtoupper') ? mb_strtoupper($init) : strtoupper($init);
                ?>
                    <article class="tm-card reveal" style="transition-delay:<?= min($i * 80, 400) ?>ms;">
                        <div class="tm-avatar" style="--tm-accent:<?= $accent ?>;">
                            <?php if ($photo !== ''): ?>
                                <img src="<?= htmlspecialchars(url($photo)) ?>"
                                     alt="<?= htmlspecialchars($nom) ?>" loading="lazy">
                            <?php else: ?>
                                <span class="tm-initials"><?= htmlspecialchars($init) ?></span>
                            <?php endif; ?>
                        </div>

                        <h3 class="tm-name"><?= htmlspecialchars($nom) ?></h3>

                        <?php if (!empty($m['role'])): ?>
                            <p class="tm-role" style="color:<?= $accent ?>;">
                                <?= htmlspecialchars($m['role']) ?>
                            </p>
                        <?php endif; ?>

                        <?php if ($pole !== ''): ?>
                            <span class="tm-dept"><?= htmlspecialchars($pole) ?></span>
                        <?php endif; ?>

                        <?php if (!empty($m['bio'])): ?>
                            <p class="tm-bio"><?= htmlspecialchars($m['bio']) ?></p>
                        <?php endif; ?>

                        <?php if ($lien !== ''): ?>
                            <a class="tm-link" href="<?= htmlspecialchars($lien) ?>"
                               target="_blank" rel="noopener noreferrer"
                               aria-label="Profil LinkedIn de <?= htmlspecialchars($nom) ?>">
                                <i data-lucide="linkedin" style="width:16px;height:16px;"></i>
                                <span>LinkedIn</span>
                            </a>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>

        <?php elseif (!empty($poles)): ?>
            <!-- ── Repli : les pôles d'expertise ───────────────────────────── -->
            <?php if (!empty($single['poles_title'])): ?>
                <p class="tm-poles-title reveal"><?= htmlspecialchars($single['poles_title']) ?></p>
            <?php endif; ?>

            <div class="tm-poles-grid">
                <?php foreach ($poles as $i => $p):
                    $accent = $tmAccents[$i % count($tmAccents)];
                ?>
                    <div class="tm-pole reveal" style="transition-delay:<?= min($i * 70, 420) ?>ms;">
                        <div class="tm-pole-icon" style="--tm-accent:<?= $accent ?>;">
                            <?= \App\Helpers\IconHelper::render(
                                    (string)($p['pole_icon'] ?? 'users'), ['size' => '20px']
                                ) ?>
                        </div>
                        <h3 class="tm-pole-title"><?= htmlspecialchars($p['pole_title']) ?></h3>
                        <?php if (!empty($p['pole_desc'])): ?>
                            <p class="tm-pole-desc"><?= htmlspecialchars($p['pole_desc']) ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</section>

<style>
/* ── Section équipe ────────────────────────────────────────────────────────
   Aucune couleur littérale : uniquement les variables du Design System, pour
   que la page suive l'accent choisi en administration. */
.tm-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 24px;
    margin-top: 3rem;
}
.tm-card {
    background: var(--bg-surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg, 16px);
    padding: 28px 22px;
    text-align: center;
    transition: var(--transition);
}
.tm-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 18px 40px rgba(0,0,0,0.09);
    border-color: color-mix(in srgb, var(--primary) 35%, var(--border));
}
.tm-avatar {
    width: 96px; height: 96px;
    margin: 0 auto 18px;
    border-radius: 50%;
    overflow: hidden;
    display: flex; align-items: center; justify-content: center;
    background: color-mix(in srgb, var(--tm-accent, var(--primary)) 10%, transparent);
    border: 2px solid color-mix(in srgb, var(--tm-accent, var(--primary)) 40%, transparent);
}
.tm-avatar img { width: 100%; height: 100%; object-fit: cover; }
.tm-initials {
    font-family: var(--font-heading);
    font-size: 1.6rem; font-weight: 700;
    color: var(--tm-accent, var(--primary));
    letter-spacing: 0.04em;
}
.tm-name  { font-family: var(--font-heading); font-size: 1.05rem; font-weight: 700; color: var(--text-main); margin: 0 0 4px; }
.tm-role  { font-size: 0.85rem; font-weight: 600; margin: 0 0 10px; }
.tm-dept  {
    display: inline-block;
    font-size: 0.68rem; letter-spacing: 0.12em; text-transform: uppercase;
    color: var(--text-muted);
    border: 1px solid var(--border);
    border-radius: 999px;
    padding: 3px 10px;
    margin-bottom: 12px;
}
.tm-bio   { font-size: 0.88rem; line-height: 1.6; color: var(--text-muted); margin: 0 0 14px; }
.tm-link  {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 0.82rem; font-weight: 600; color: var(--primary);
    min-height: 40px;
}
.tm-link:hover { text-decoration: underline; }

/* ── Repli : pôles d'expertise ─────────────────────────────────────────── */
.tm-poles-title {
    text-align: center;
    color: var(--text-muted);
    font-size: 0.95rem;
    margin: 0 auto 2.5rem;
    max-width: 640px;
}
.tm-poles-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 18px;
    margin-top: 1rem;
}
.tm-pole {
    background: var(--bg-surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-md, 12px);
    padding: 24px 20px;
    text-align: center;
    transition: var(--transition);
}
.tm-pole:hover {
    transform: translateY(-3px);
    border-color: color-mix(in srgb, var(--primary) 35%, var(--border));
}
.tm-pole-icon {
    width: 46px; height: 46px;
    margin: 0 auto 14px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    background: color-mix(in srgb, var(--tm-accent, var(--primary)) 10%, transparent);
    color: var(--tm-accent, var(--primary));
    border: 1px solid color-mix(in srgb, var(--tm-accent, var(--primary)) 30%, transparent);
}
.tm-pole-title { font-family: var(--font-heading); font-size: 0.98rem; font-weight: 700; color: var(--text-main); margin: 0 0 6px; }
.tm-pole-desc  { font-size: 0.85rem; line-height: 1.55; color: var(--text-muted); margin: 0; }

@media (max-width: 560px) {
    .tm-grid, .tm-poles-grid { grid-template-columns: 1fr; }
}
</style>
