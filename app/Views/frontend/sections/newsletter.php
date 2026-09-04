<?php
/**
 * Section : newsletter — Inscription à la lettre d'analyses
 *
 * ── Ce qui change par rapport à la version précédente ───────────────────────
 * Le formulaire n'avait AUCUN backend : son `onsubmit` affichait une boîte de
 * dialogue « Merci de votre abonnement » puis jetait l'adresse. Chaque visiteur
 * qui s'y inscrivait depuis la mise en ligne croyait être abonné sans l'être.
 * Il envoie désormais réellement vers /newsletter, où l'adresse est enregistrée
 * dans `newsletter_subscribers` et consultable dans /admin/newsletter.
 *
 * Envoi en POST classique, sans dépendance au JavaScript : une inscription qui
 * échoue en silence parce qu'un script n'a pas chargé est exactement le défaut
 * qu'on vient de corriger.
 *
 *   single : tag, title, subtitle,
 *            placeholder   texte indicatif du champ
 *            button_text   libellé du bouton
 *            note          mention sous le formulaire (fréquence, désabonnement)
 *            success_text  message affiché après une inscription réussie
 */

use App\Services\CSRF;

if (session_status() === PHP_SESSION_NONE) { session_start(); }

/* Retour d'une soumission : lu puis effacé, pour qu'un rafraîchissement de la
   page ne réaffiche pas indéfiniment le même message. */
$retour = $_SESSION['newsletter_retour'] ?? null;
unset($_SESSION['newsletter_retour']);

$succes  = ($_GET['newsletter'] ?? '') === 'ok';
$message = trim((string)($retour['message'] ?? ''));
$erreur  = ($retour['type'] ?? '') === 'erreur';

if ($succes && $message === '') {
    $message = trim((string)($single['success_text'] ?? ''))
             ?: 'Merci — votre inscription est enregistrée.';
}
?>

<section class="section-padding ins-news" id="newsletter" style="background:var(--bg-alt);">
    <div class="container">
        <div class="insn-panel reveal">

            <?php if (!empty($single['tag'])): ?>
                <span class="section-badge"><?= htmlspecialchars($single['tag']) ?></span>
            <?php endif; ?>

            <?php if (!empty($single['title'])): ?>
                <h2 class="insn-title"><?= htmlspecialchars($single['title']) ?></h2>
            <?php endif; ?>

            <?php if (!empty($single['subtitle'])): ?>
                <p class="insn-sub"><?= htmlspecialchars($single['subtitle']) ?></p>
            <?php endif; ?>

            <?php if ($message !== ''): ?>
                <p class="insn-flash<?= $erreur ? ' is-error' : '' ?>" role="status">
                    <?= htmlspecialchars($message) ?>
                </p>
            <?php endif; ?>

            <form class="insn-form" method="post" action="<?= htmlspecialchars(url('/newsletter')) ?>">
                <?= CSRF::field() ?>
                <input type="hidden" name="source" value="<?= htmlspecialchars((string)($currentSlug ?? ''), ENT_QUOTES) ?>">

                <?php /* Piège à robots : invisible et hors du parcours au clavier.
                        Rempli, l'inscription est ignorée sans le dire. */ ?>
                <div class="insn-hp" aria-hidden="true">
                    <label for="insn-website">Ne pas remplir</label>
                    <input type="text" id="insn-website" name="website" tabindex="-1" autocomplete="off">
                </div>

                <label class="insn-visually-hidden" for="insn-email">
                    <?= htmlspecialchars(trim((string)($single['placeholder'] ?? '')) ?: 'Votre adresse email') ?>
                </label>
                <input type="email" id="insn-email" name="email" required
                       autocomplete="email"
                       placeholder="<?= htmlspecialchars(trim((string)($single['placeholder'] ?? '')) ?: 'Votre adresse email', ENT_QUOTES) ?>">

                <button type="submit">
                    <span><?= htmlspecialchars(trim((string)($single['button_text'] ?? '')) ?: 'S’abonner') ?></span>
                    <i data-lucide="arrow-right" style="width:15px;height:15px;"></i>
                </button>
            </form>

            <?php if (!empty($single['note'])): ?>
                <p class="insn-note"><?= htmlspecialchars($single['note']) ?></p>
            <?php endif; ?>

        </div>
    </div>
</section>

<style>
.insn-panel {
    max-width: 760px; margin: 0 auto;
    padding: 52px 44px;
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    text-align: center;
    box-shadow: var(--shadow-card);
}
.insn-title {
    margin: 14px 0 12px;
    font-family: var(--font-heading);
    font-size: clamp(1.5rem, 2.8vw, 2.1rem);
    font-weight: 800; line-height: 1.24;
    color: var(--text-main);
}
.insn-sub { margin: 0 auto 26px; max-width: 520px; font-size: 0.98rem; line-height: 1.72; color: var(--text-muted); }

.insn-flash {
    margin: 0 auto 20px; max-width: 520px;
    padding: 13px 20px; border-radius: var(--radius-md);
    font-size: 0.89rem; font-weight: 600;
    background: color-mix(in srgb, var(--primary) 10%, transparent);
    border: 1px solid color-mix(in srgb, var(--primary) 26%, transparent);
    color: var(--primary);
}
.insn-flash.is-error {
    background: rgba(185, 28, 28, 0.08);
    border-color: rgba(185, 28, 28, 0.28);
    color: #b91c1c;
}

.insn-form {
    display: flex; align-items: center; gap: 8px;
    max-width: 520px; margin: 0 auto;
    padding: 6px 6px 6px 20px;
    background: var(--bg-base);
    border: 1.5px solid var(--border);
    border-radius: 999px;
}
.insn-form:focus-within { border-color: color-mix(in srgb, var(--primary) 45%, transparent); }
.insn-form input[type="email"] {
    flex: 1; min-width: 0;
    border: 0; outline: 0; background: transparent;
    font-family: inherit; font-size: 0.93rem; color: var(--text-main);
    padding: 12px 0;
}
.insn-form button {
    flex-shrink: 0;
    display: inline-flex; align-items: center; gap: 8px;
    padding: 13px 26px;
    border: 0; border-radius: 999px;
    background: var(--primary); color: #ffffff;
    font-family: var(--font-heading); font-size: 0.87rem; font-weight: 650;
    cursor: pointer; transition: var(--transition);
}
.insn-form button:hover { gap: 12px; box-shadow: var(--shadow-btn); }

.insn-note { margin: 18px auto 0; max-width: 480px; font-size: 0.78rem; line-height: 1.6; color: var(--text-muted); }

/* Le piège à robots doit rester invisible sans être `display:none`, que
   certains automates détectent et contournent. */
.insn-hp { position: absolute; left: -9999px; width: 1px; height: 1px; overflow: hidden; }
.insn-visually-hidden {
    position: absolute; width: 1px; height: 1px;
    padding: 0; margin: -1px; overflow: hidden;
    clip: rect(0 0 0 0); white-space: nowrap; border: 0;
}

@media (max-width: 620px) {
    .insn-panel { padding: 36px 22px; }
    .insn-form { flex-wrap: wrap; border-radius: var(--radius-md); padding: 12px; }
    .insn-form input[type="email"] { width: 100%; }
    .insn-form button { width: 100%; justify-content: center; }
}
</style>
