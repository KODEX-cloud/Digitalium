<?php
/**
 * Section : lead_form — Demande de projet en quatre étapes
 *
 * Le formulaire est un POST classique en `multipart/form-data` vers
 * /contact/demande. Le découpage en étapes est purement visuel : sans
 * JavaScript, les quatre blocs s'affichent à la suite et l'envoi fonctionne
 * exactement pareil. Un formulaire commercial qui exige JavaScript perd les
 * prospects dont le navigateur ou le réseau ne suit pas — c'est le pire endroit
 * du site où prendre ce risque.
 *
 * Blocs attendus — TOUT est administrable (Règle #2) :
 *   single : tag, title, subtitle,
 *            step1_title, step2_title, step3_title, step4_title,
 *            submit_text, back_text, next_text,
 *            success_title, success_text, privacy_note,
 *            file_note, error_title
 *   groups : une ligne porte UN seul de ces jeux de clés, ce qui détermine
 *            la liste qu'elle alimente :
 *              besoin_label  (+ besoin_icon) → choix de l'étape 1
 *              secteur_label                 → secteurs de l'étape 2
 *              urgence_label                 → niveaux d'urgence de l'étape 3
 *              budget_label                  → fourchettes de l'étape 3
 *
 * Les erreurs de validation et les valeurs déjà saisies sont relues depuis la
 * session : un envoi refusé ne fait jamais reperdre au visiteur ce qu'il a tapé.
 */

use App\Services\CSRF;

$L = static function (string $k, string $defaut = '') use ($single): string {
    $v = trim((string)($single[$k] ?? ''));
    return $v !== '' ? $v : $defaut;
};

/* Chaque ligne de groupe alimente la liste correspondant à la clé qu'elle porte. */
$listes = ['besoin' => [], 'secteur' => [], 'urgence' => [], 'budget' => []];
foreach (($groups ?? []) as $g) {
    foreach ($listes as $nom => $_) {
        $val = trim((string)($g[$nom . '_label'] ?? ''));
        if ($val !== '') {
            $listes[$nom][] = ['label' => $val, 'icon' => trim((string)($g[$nom . '_icon'] ?? ''))];
        }
    }
}

/* Les erreurs et les valeurs saisies transitent par la session. Elle est
   ouverte ici explicitement : sur une simple visite (GET), rien d'autre ne
   l'aurait encore démarrée à ce point du rendu. */
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$envoye  = ($_GET['demande'] ?? '') === 'ok';
$erreurs = $_SESSION['lead_errors'] ?? [];
$old     = $_SESSION['lead_old'] ?? [];
unset($_SESSION['lead_errors'], $_SESSION['lead_old']);

$v = static function (string $k) use ($old): string {
    return htmlspecialchars((string)($old[$k] ?? ''), ENT_QUOTES);
};
$err = static function (string $k) use ($erreurs): string {
    return isset($erreurs[$k]) ? '<span class="lf-err">' . htmlspecialchars((string)$erreurs[$k]) . '</span>' : '';
};
?>

<section class="section-padding lf-section" id="demande" style="background:var(--bg-alt);">
    <div class="container">

        <div class="section-header reveal">
            <?php if ($L('tag')): ?><span class="section-badge"><?= htmlspecialchars($L('tag')) ?></span><?php endif; ?>
            <?php if ($L('title')): ?><h2 class="section-title"><?= htmlspecialchars($L('title')) ?></h2><?php endif; ?>
            <?php if ($L('subtitle')): ?><p class="section-subtitle"><?= htmlspecialchars($L('subtitle')) ?></p><?php endif; ?>
        </div>

        <?php if ($envoye): ?>
            <div class="lf-done reveal" role="status">
                <span class="lf-done-icon"><i data-lucide="check" style="width:26px;height:26px;"></i></span>
                <h3><?= htmlspecialchars($L('success_title', 'Votre demande est bien enregistrée.')) ?></h3>
                <p><?= htmlspecialchars($L('success_text', "Un membre de l'équipe revient vers vous rapidement.")) ?></p>
            </div>
        <?php else: ?>

            <?php if ($erreurs): ?>
                <div class="lf-alert reveal" role="alert">
                    <strong><?= htmlspecialchars($L('error_title', 'Votre demande n’a pas pu être envoyée.')) ?></strong>
                    <ul>
                        <?php foreach ($erreurs as $e): ?>
                            <li><?= htmlspecialchars((string)$e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form class="lf-form reveal" method="post" action="<?= htmlspecialchars(url('/contact/demande')) ?>"
                  enctype="multipart/form-data" novalidate>
                <?= CSRF::field() ?>
                <?php /* Piège à robots : un humain ne voit pas ce champ, un robot le remplit. */ ?>
                <div class="lf-hp" aria-hidden="true">
                    <label for="lf-website">Ne pas remplir</label>
                    <input type="text" id="lf-website" name="website" tabindex="-1" autocomplete="off">
                </div>

                <ol class="lf-steps" aria-hidden="true">
                    <?php foreach ([1, 2, 3, 4] as $n): ?>
                        <li class="lf-step<?= $n === 1 ? ' active' : '' ?>" data-step-dot="<?= $n ?>">
                            <span class="lf-step-num"><?= $n ?></span>
                            <span class="lf-step-label"><?= htmlspecialchars($L('step' . $n . '_title', 'Étape ' . $n)) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ol>

                <!-- ÉTAPE 1 — le besoin -->
                <fieldset class="lf-panel" data-step="1">
                    <legend><?= htmlspecialchars($L('step1_title', 'Votre besoin')) ?></legend>
                    <?= $err('besoin') ?>
                    <div class="lf-choices">
                        <?php foreach ($listes['besoin'] as $i => $b): ?>
                            <label class="lf-choice">
                                <input type="radio" name="besoin" value="<?= htmlspecialchars($b['label'], ENT_QUOTES) ?>"
                                    <?= ($old['besoin'] ?? '') === $b['label'] ? 'checked' : '' ?>>
                                <span class="lf-choice-box">
                                    <?php if ($b['icon']): ?>
                                        <span class="lf-choice-icon"><?= \App\Helpers\IconHelper::render($b['icon'], ['size' => '18px']) ?></span>
                                    <?php endif; ?>
                                    <span class="lf-choice-txt"><?= htmlspecialchars($b['label']) ?></span>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </fieldset>

                <!-- ÉTAPE 2 — l'organisation -->
                <fieldset class="lf-panel" data-step="2">
                    <legend><?= htmlspecialchars($L('step2_title', 'Votre organisation')) ?></legend>
                    <div class="lf-grid">
                        <label class="lf-field">
                            <span>Nom et prénom <em>*</em></span>
                            <input type="text" name="nom" value="<?= $v('nom') ?>" maxlength="150" required autocomplete="name">
                            <?= $err('nom') ?>
                        </label>
                        <label class="lf-field">
                            <span>Entreprise / Organisation</span>
                            <input type="text" name="entreprise" value="<?= $v('entreprise') ?>" maxlength="150" autocomplete="organization">
                        </label>
                        <label class="lf-field">
                            <span>Secteur d'activité</span>
                            <select name="secteur">
                                <option value="">—</option>
                                <?php foreach ($listes['secteur'] as $s): ?>
                                    <option value="<?= htmlspecialchars($s['label'], ENT_QUOTES) ?>"
                                        <?= ($old['secteur'] ?? '') === $s['label'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($s['label']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label class="lf-field">
                            <span>Pays</span>
                            <input type="text" name="pays" value="<?= $v('pays') ?>" maxlength="100" autocomplete="country-name">
                        </label>
                        <label class="lf-field">
                            <span>Email <em>*</em></span>
                            <input type="email" name="email" value="<?= $v('email') ?>" maxlength="255" required autocomplete="email">
                            <?= $err('email') ?>
                        </label>
                        <label class="lf-field">
                            <span>Téléphone / WhatsApp</span>
                            <input type="tel" name="telephone" value="<?= $v('telephone') ?>" maxlength="30" autocomplete="tel">
                        </label>
                    </div>
                </fieldset>

                <!-- ÉTAPE 3 — le projet -->
                <fieldset class="lf-panel" data-step="3">
                    <legend><?= htmlspecialchars($L('step3_title', 'Le projet')) ?></legend>
                    <label class="lf-field">
                        <span>Décrivez votre besoin <em>*</em></span>
                        <textarea name="message" rows="6" maxlength="4000" required><?= $v('message') ?></textarea>
                        <?= $err('message') ?>
                    </label>
                    <label class="lf-field">
                        <span>Objectif principal</span>
                        <input type="text" name="objectif" value="<?= $v('objectif') ?>" maxlength="255">
                    </label>
                    <div class="lf-grid">
                        <label class="lf-field">
                            <span>Niveau d'urgence</span>
                            <select name="urgence">
                                <option value="">—</option>
                                <?php foreach ($listes['urgence'] as $u): ?>
                                    <option value="<?= htmlspecialchars($u['label'], ENT_QUOTES) ?>"
                                        <?= ($old['urgence'] ?? '') === $u['label'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($u['label']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label class="lf-field">
                            <span>Budget indicatif <small>(facultatif)</small></span>
                            <select name="budget">
                                <option value="">—</option>
                                <?php foreach ($listes['budget'] as $b): ?>
                                    <option value="<?= htmlspecialchars($b['label'], ENT_QUOTES) ?>"
                                        <?= ($old['budget'] ?? '') === $b['label'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($b['label']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                    </div>
                    <label class="lf-field">
                        <span>Cahier des charges <small>(facultatif)</small></span>
                        <input type="file" name="document" accept=".pdf,.doc,.docx,.odt,.txt,.png,.jpg,.jpeg,.webp">
                        <?php if ($L('file_note')): ?>
                            <small class="lf-hint"><?= htmlspecialchars($L('file_note')) ?></small>
                        <?php endif; ?>
                        <?= $err('document') ?>
                    </label>
                </fieldset>

                <!-- ÉTAPE 4 — récapitulatif -->
                <fieldset class="lf-panel" data-step="4">
                    <legend><?= htmlspecialchars($L('step4_title', 'Validation')) ?></legend>
                    <?php /* Rempli par le script à partir des champs saisis ; sans JavaScript,
                            le visiteur relit simplement les blocs ci-dessus. */ ?>
                    <dl class="lf-recap" id="lfRecap"></dl>
                    <noscript>
                        <p class="lf-hint">Relisez les informations saisies ci-dessus avant d'envoyer.</p>
                    </noscript>
                    <?php if ($L('privacy_note')): ?>
                        <p class="lf-privacy"><?= htmlspecialchars($L('privacy_note')) ?></p>
                    <?php endif; ?>
                </fieldset>

                <div class="lf-actions">
                    <button type="button" class="lf-btn lf-btn-ghost" id="lfPrev" hidden>
                        <?= htmlspecialchars($L('back_text', 'Retour')) ?>
                    </button>
                    <button type="button" class="lf-btn lf-btn-primary" id="lfNext" hidden>
                        <?= htmlspecialchars($L('next_text', 'Continuer')) ?>
                    </button>
                    <button type="submit" class="lf-btn lf-btn-primary" id="lfSubmit">
                        <?= htmlspecialchars($L('submit_text', 'Envoyer ma demande')) ?>
                    </button>
                </div>
            </form>
        <?php endif; ?>

    </div>
</section>

<style>
.lf-form { max-width: 860px; margin: 0 auto; }

.lf-hp { position: absolute; left: -9999px; width: 1px; height: 1px; overflow: hidden; }

.lf-steps {
    display: flex; gap: 8px; list-style: none; padding: 0; margin: 0 0 32px;
    counter-reset: none; flex-wrap: wrap;
}
.lf-step {
    display: flex; align-items: center; gap: 9px;
    padding: 9px 15px; border-radius: 999px;
    background: var(--bg-card); border: 1px solid var(--border);
    font-size: 0.82rem; color: var(--text-muted);
    transition: var(--transition);
}
.lf-step.active { border-color: var(--primary); color: var(--text-main); }
.lf-step.done   { border-color: color-mix(in srgb, var(--primary) 40%, transparent); }
.lf-step-num {
    display: inline-flex; align-items: center; justify-content: center;
    width: 22px; height: 22px; border-radius: 50%;
    background: color-mix(in srgb, var(--primary) 12%, transparent);
    color: var(--primary); font-weight: 700; font-size: 0.74rem;
}
.lf-step.active .lf-step-num { background: var(--primary); color: #fff; }

.lf-panel {
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    background: var(--bg-card);
    padding: 28px 26px;
    margin: 0 0 18px;
}
.lf-panel legend {
    padding: 0 10px;
    font-family: var(--font-heading);
    font-weight: 700; font-size: 1.02rem; color: var(--text-main);
}

.lf-choices { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
.lf-choice input { position: absolute; opacity: 0; width: 0; height: 0; }
.lf-choice-box {
    display: flex; align-items: center; gap: 12px;
    padding: 15px 16px;
    border: 1px solid var(--border); border-radius: var(--radius-lg);
    cursor: pointer; transition: var(--transition);
    font-size: 0.92rem; color: var(--text-main); line-height: 1.35;
}
.lf-choice-box:hover { border-color: color-mix(in srgb, var(--primary) 40%, transparent); }
.lf-choice input:checked + .lf-choice-box {
    border-color: var(--primary);
    background: color-mix(in srgb, var(--primary) 7%, transparent);
}
/* Le focus clavier doit rester visible : le bouton radio est masqué. */
.lf-choice input:focus-visible + .lf-choice-box { outline: 2px solid var(--primary); outline-offset: 2px; }
.lf-choice-icon { display: inline-flex; color: var(--primary); flex-shrink: 0; }

.lf-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
.lf-field { display: block; margin-bottom: 16px; }
.lf-field > span {
    display: block; margin-bottom: 7px;
    font-size: 0.83rem; font-weight: 600; color: var(--text-main);
}
.lf-field > span em { color: var(--primary); font-style: normal; }
.lf-field > span small { font-weight: 400; color: var(--text-muted); }
.lf-field input[type="text"], .lf-field input[type="email"], .lf-field input[type="tel"],
.lf-field select, .lf-field textarea, .lf-field input[type="file"] {
    width: 100%; box-sizing: border-box;
    padding: 12px 14px;
    border: 1px solid var(--border); border-radius: 10px;
    background: var(--bg-base); color: var(--text-main);
    font-family: inherit; font-size: 0.92rem;
    transition: var(--transition);
}
.lf-field textarea { resize: vertical; min-height: 130px; }
.lf-field input:focus, .lf-field select:focus, .lf-field textarea:focus {
    outline: none; border-color: var(--primary);
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--primary) 14%, transparent);
}
.lf-hint { display: block; margin-top: 6px; font-size: 0.78rem; color: var(--text-muted); }
.lf-err  { display: block; margin-top: 6px; font-size: 0.8rem; color: #c0392b; font-weight: 600; }

.lf-recap { margin: 0; display: grid; grid-template-columns: minmax(140px, auto) 1fr; gap: 10px 20px; }
.lf-recap dt { font-size: 0.8rem; color: var(--text-muted); font-weight: 600; }
.lf-recap dd { margin: 0; font-size: 0.92rem; color: var(--text-main); overflow-wrap: anywhere; }
.lf-privacy { margin: 20px 0 0; font-size: 0.8rem; color: var(--text-muted); line-height: 1.55; }

.lf-actions { display: flex; gap: 12px; justify-content: flex-end; margin-top: 22px; }
.lf-btn {
    display: inline-flex; align-items: center; gap: 9px;
    padding: 14px 30px; border-radius: 999px; border: 1px solid transparent;
    font-size: 0.92rem; font-weight: 650; font-family: inherit;
    cursor: pointer; transition: var(--transition);
}
.lf-btn-primary { background: var(--primary); color: #fff; }
.lf-btn-primary:hover { transform: translateY(-2px); box-shadow: var(--shadow-btn); }
.lf-btn-ghost { background: transparent; color: var(--text-main); border-color: var(--border); }
.lf-btn-ghost:hover { border-color: var(--primary); color: var(--primary); }

.lf-alert {
    max-width: 860px; margin: 0 auto 22px;
    padding: 18px 20px; border-radius: var(--radius-lg);
    border: 1px solid #e5b3ad; background: #fdf2f1; color: #8c2f22;
}
.lf-alert ul { margin: 8px 0 0; padding-left: 20px; font-size: 0.88rem; }

.lf-done {
    max-width: 640px; margin: 0 auto; text-align: center;
    padding: 46px 34px; border-radius: var(--radius-lg);
    border: 1px solid var(--border); background: var(--bg-card);
}
.lf-done-icon {
    display: inline-flex; align-items: center; justify-content: center;
    width: 58px; height: 58px; border-radius: 50%; margin-bottom: 18px;
    background: color-mix(in srgb, var(--primary) 12%, transparent); color: var(--primary);
}
.lf-done h3 { font-family: var(--font-heading); font-size: 1.3rem; color: var(--text-main); margin: 0 0 10px; }
.lf-done p  { color: var(--text-muted); line-height: 1.6; margin: 0; }

@media (max-width: 760px) {
    .lf-choices, .lf-grid { grid-template-columns: 1fr; }
    .lf-recap { grid-template-columns: 1fr; gap: 3px 0; }
    .lf-recap dd { margin-bottom: 10px; }
    .lf-actions { flex-direction: column-reverse; }
    .lf-btn { justify-content: center; }
    .lf-step-label { display: none; }
}
</style>

<script>
(function () {
    var form = document.querySelector('.lf-form');
    if (!form) { return; }

    var panels = Array.prototype.slice.call(form.querySelectorAll('.lf-panel'));
    var dots   = Array.prototype.slice.call(form.querySelectorAll('[data-step-dot]'));
    var prev   = form.querySelector('#lfPrev');
    var next   = form.querySelector('#lfNext');
    var submit = form.querySelector('#lfSubmit');
    var recap  = form.querySelector('#lfRecap');
    if (panels.length < 2) { return; }

    // À partir d'ici le pas-à-pas est actif. Les commandes n'apparaissent que
    // maintenant : sans script, elles ne mèneraient nulle part.
    var courant = 0;
    prev.hidden = false;
    next.hidden = false;

    function afficher(i) {
        courant = Math.max(0, Math.min(panels.length - 1, i));
        panels.forEach(function (p, n) { p.hidden = (n !== courant); });
        dots.forEach(function (d, n) {
            d.classList.toggle('active', n === courant);
            d.classList.toggle('done', n < courant);
        });
        prev.hidden   = courant === 0;
        next.hidden   = courant === panels.length - 1;
        submit.hidden = courant !== panels.length - 1;
        if (courant === panels.length - 1) { remplirRecap(); }
        var y = form.getBoundingClientRect().top + window.pageYOffset - 90;
        window.scrollTo({ top: y, behavior: 'smooth' });
    }

    /* Validation d'étape : on ne bloque que sur les champs réellement requis,
       et le message vient du navigateur — pas de texte en dur ici. */
    function etapeValide() {
        var champs = panels[courant].querySelectorAll('input, select, textarea');
        for (var i = 0; i < champs.length; i++) {
            if (champs[i].required && !champs[i].checkValidity()) {
                champs[i].reportValidity();
                return false;
            }
        }
        return true;
    }

    function libelle(champ) {
        var label = champ.closest('.lf-field');
        if (label) {
            var s = label.querySelector('span');
            if (s) { return s.textContent.replace('*', '').trim(); }
        }
        return champ.name;
    }

    function remplirRecap() {
        recap.innerHTML = '';
        var vus = {};
        panels.slice(0, panels.length - 1).forEach(function (p) {
            p.querySelectorAll('input, select, textarea').forEach(function (c) {
                if (!c.name || c.name === 'website' || c.name === 'csrf_token') { return; }
                var val = '';
                if (c.type === 'radio') { if (!c.checked) { return; } val = c.value; }
                else if (c.type === 'file') { val = c.files && c.files[0] ? c.files[0].name : ''; }
                else { val = c.value; }
                if (!val.trim() || vus[c.name]) { return; }
                vus[c.name] = true;
                var dt = document.createElement('dt');
                dt.textContent = c.type === 'radio' ? p.querySelector('legend').textContent : libelle(c);
                var dd = document.createElement('dd');
                dd.textContent = val;   // textContent : jamais d'injection dans le récapitulatif
                recap.appendChild(dt); recap.appendChild(dd);
            });
        });
        if (!recap.children.length) {
            var dd = document.createElement('dd');
            dd.textContent = 'Aucune information saisie pour le moment.';
            recap.appendChild(dd);
        }
    }

    next.addEventListener('click', function () { if (etapeValide()) { afficher(courant + 1); } });
    prev.addEventListener('click', function () { afficher(courant - 1); });

    // Un choix à l'étape 1 fait avancer : c'est un clic unique, attendre un
    // second clic sur « Continuer » n'apporte rien.
    form.querySelectorAll('.lf-choice input').forEach(function (r) {
        r.addEventListener('change', function () { if (courant === 0) { afficher(1); } });
    });

    // Double envoi : le bouton se désactive dès la soumission acceptée.
    form.addEventListener('submit', function () {
        submit.disabled = true;
        submit.textContent = 'Envoi…';
    });

    afficher(0);
})();
</script>
