<?php
$s = $settings ?? [];
function thv(array $s, string $key, string $default): string {
    return htmlspecialchars($s[$key] ?? $default, ENT_QUOTES);
}
?>

<style>
.tb-wrap        { display: grid; grid-template-columns: 240px 1fr; gap: 0; min-height: calc(100vh - 70px - 80px); }
.tb-nav         { background: rgba(255,255,255,0.55); border-right: 1px solid rgba(0,0,0,0.07); padding: 24px 0; position: sticky; top: 70px; height: fit-content; border-radius: 16px 0 0 16px; }
.tb-nav-item    { display: flex; align-items: center; gap: 10px; padding: 11px 20px; font-size: 0.88rem; font-weight: 500; color: #64748b; cursor: pointer; transition: all 0.2s; border-left: 3px solid transparent; margin: 1px 0; }
.tb-nav-item:hover { color: #2563eb; background: rgba(37,99,235,0.05); }
.tb-nav-item.active { color: #2563eb; background: rgba(37,99,235,0.08); border-left-color: #2563eb; font-weight: 600; }
.tb-nav-item i  { width: 16px; height: 16px; flex-shrink: 0; }
.tb-content     { padding: 32px 40px; }
.tb-panel       { display: none; }
.tb-panel.active{ display: block; }

.tb-section     { background: #fff; border: 1px solid rgba(0,0,0,0.07); border-radius: 14px; padding: 28px 32px; margin-bottom: 20px; box-shadow: 0 1px 4px rgba(0,0,0,0.04); }
.tb-section-title { font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.15em; color: #94a3b8; margin-bottom: 22px; display: flex; align-items: center; gap: 8px; }
.tb-section-title::after { content: ''; flex: 1; height: 1px; background: rgba(0,0,0,0.06); }

.tb-row         { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 18px; }
.tb-row-2       { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
.tb-row-3       { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; }

.tb-field       { display: flex; flex-direction: column; gap: 6px; }
.tb-label       { font-size: 0.8rem; font-weight: 600; color: #475569; letter-spacing: 0.02em; }
.tb-hint        { font-size: 0.72rem; color: #94a3b8; margin-top: 2px; }

/* Color picker group */
.color-group    { display: flex; align-items: center; gap: 0; border: 1.5px solid rgba(0,0,0,0.1); border-radius: 10px; overflow: hidden; background: #f8fafc; transition: border-color 0.2s; }
.color-group:focus-within { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
.color-swatch   { width: 44px; height: 44px; border: none; outline: none; cursor: pointer; padding: 4px; background: transparent; flex-shrink: 0; }
.color-swatch::-webkit-color-swatch-wrapper { padding: 0; border-radius: 6px; }
.color-swatch::-webkit-color-swatch { border: none; border-radius: 6px; }
.color-hex      { flex: 1; border: none; background: transparent; padding: 0 12px; font-size: 0.85rem; font-family: 'Inter', monospace; color: #1e293b; font-weight: 500; outline: none; }

/* Range slider */
.tb-range-wrap  { display: flex; align-items: center; gap: 12px; }
.tb-range       { flex: 1; -webkit-appearance: none; height: 5px; border-radius: 10px; background: linear-gradient(90deg, #2563eb, #1d4ed8); outline: none; cursor: pointer; }
.tb-range::-webkit-slider-thumb { -webkit-appearance: none; width: 18px; height: 18px; border-radius: 50%; background: #fff; border: 2px solid #2563eb; box-shadow: 0 2px 6px rgba(37,99,235,0.3); cursor: pointer; }
.tb-range-val   { min-width: 48px; text-align: right; font-size: 0.82rem; font-weight: 700; color: #2563eb; background: rgba(37,99,235,0.08); padding: 3px 8px; border-radius: 6px; }

/* Input text */
.tb-input       { width: 100%; padding: 10px 14px; border: 1.5px solid rgba(0,0,0,0.1); border-radius: 10px; font-size: 0.88rem; color: #1e293b; font-family: 'Inter', sans-serif; background: #f8fafc; outline: none; transition: border-color 0.2s, box-shadow 0.2s; }
.tb-input:focus { border-color: #2563eb; background: #fff; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }

/* Select */
.tb-select      { width: 100%; padding: 10px 14px; border: 1.5px solid rgba(0,0,0,0.1); border-radius: 10px; font-size: 0.88rem; color: #1e293b; font-family: 'Inter', sans-serif; background: #f8fafc; outline: none; appearance: none; cursor: pointer; }
.tb-select:focus{ border-color: #2563eb; background: #fff; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }

/* Preview bar */
.tb-preview-bar { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; padding: 16px 20px; background: #f8fafc; border-radius: 10px; margin-bottom: 20px; border: 1px solid rgba(0,0,0,0.06); }
.tb-preview-color { width: 28px; height: 28px; border-radius: 50%; border: 2px solid #fff; box-shadow: 0 2px 6px rgba(0,0,0,0.15); transition: transform 0.2s; cursor: pointer; }
.tb-preview-color:hover { transform: scale(1.15); }

/* Save bar */
.tb-save-bar    { position: sticky; bottom: 0; background: rgba(255,255,255,0.95); backdrop-filter: blur(12px); border-top: 1px solid rgba(0,0,0,0.07); padding: 16px 40px; display: flex; align-items: center; justify-content: space-between; z-index: 50; margin: 0 -40px -32px; border-radius: 0 0 0 0; }
.tb-btn-save    { display: inline-flex; align-items: center; gap: 8px; padding: 12px 28px; background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); color: white; border: none; border-radius: 50px; font-size: 0.9rem; font-weight: 600; cursor: pointer; box-shadow: 0 6px 20px rgba(37,99,235,0.35); transition: all 0.2s; font-family: 'Inter', sans-serif; }
.tb-btn-save:hover { transform: translateY(-2px); box-shadow: 0 10px 28px rgba(37,99,235,0.45); }
.tb-btn-reset   { display: inline-flex; align-items: center; gap: 8px; padding: 12px 22px; background: transparent; color: #64748b; border: 1.5px solid rgba(0,0,0,0.1); border-radius: 50px; font-size: 0.88rem; font-weight: 500; cursor: pointer; font-family: 'Inter', sans-serif; transition: all 0.2s; }
.tb-btn-reset:hover { color: #ef4444; border-color: rgba(239,68,68,0.3); background: rgba(239,68,68,0.04); }
</style>

<!-- Page header -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;">
    <div>
        <h1 style="font-size:1.5rem;font-weight:700;color:#1e293b;letter-spacing:-0.02em;margin-bottom:4px;">
            <i data-lucide="palette" style="width:22px;height:22px;display:inline;vertical-align:-4px;margin-right:8px;color:#2563eb;"></i>
            Theme Builder
        </h1>
        <p style="font-size:0.88rem;color:#64748b;">Contrôlez l'identité graphique complète du site depuis le Backend — sans toucher au code.</p>
    </div>
    <a href="/" target="_blank" style="display:inline-flex;align-items:center;gap:6px;padding:9px 18px;background:rgba(13,148,136,0.08);color:#0d9488;border:1px solid rgba(13,148,136,0.2);border-radius:50px;font-size:0.82rem;font-weight:600;transition:all 0.2s;">
        <i data-lucide="external-link" style="width:14px;height:14px;"></i> Aperçu site
    </a>
</div>

<form method="POST" action="<?= url('/admin/settings/theme') ?>">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

    <div class="tb-wrap">

        <!-- ── Sidebar navigation ── -->
        <nav class="tb-nav">
            <div style="padding:0 20px 16px;font-size:0.7rem;font-weight:700;letter-spacing:0.15em;text-transform:uppercase;color:#cbd5e1;">Design System</div>
            <div class="tb-nav-item active" onclick="showPanel('palette', this)">
                <i data-lucide="droplets"></i> Palette de couleurs
            </div>
            <div class="tb-nav-item" onclick="showPanel('typo', this)">
                <i data-lucide="type"></i> Typographie
            </div>
            <div class="tb-nav-item" onclick="showPanel('shapes', this)">
                <i data-lucide="box"></i> Formes &amp; Espacements
            </div>
            <div class="tb-nav-item" onclick="showPanel('buttons', this)">
                <i data-lucide="mouse-pointer-click"></i> Boutons
            </div>
            <div class="tb-nav-item" onclick="showPanel('shadows', this)">
                <i data-lucide="layers"></i> Ombres &amp; Profondeur
            </div>
            <div class="tb-nav-item" onclick="showPanel('hero', this)">
                <i data-lucide="image"></i> Hero Section
            </div>
            <div style="padding:16px 20px 4px;font-size:0.7rem;font-weight:700;letter-spacing:0.15em;text-transform:uppercase;color:#cbd5e1;margin-top:8px;">Prévisualisation</div>
            <div style="padding:12px 20px;">
                <div id="palette-dots" style="display:flex;flex-wrap:wrap;gap:6px;"></div>
            </div>
        </nav>

        <!-- ── Main content ── -->
        <div class="tb-content">

            <!-- ╔══════════════════════════════╗
                 ║  PANEL : Palette             ║
                 ╚══════════════════════════════╝ -->
            <div id="panel-palette" class="tb-panel active">

                <div class="tb-section">
                    <div class="tb-section-title"><i data-lucide="zap" style="width:14px;height:14px;"></i> Couleurs de marque</div>
                    <div class="tb-row">
                        <?php
                        $brandColors = [
                            ['theme_primary',   '#0d9488', 'Couleur principale',  'Boutons, liens actifs, accents'],
                            ['theme_secondary',  '#0891b2', 'Couleur secondaire',  'Dégradés, variantes'],
                            ['theme_accent',     '#f59e0b', 'Couleur accent',      'Étoiles, highlights, alertes'],
                        ];
                        foreach ($brandColors as [$key, $def, $label, $hint]): ?>
                        <div class="tb-field">
                            <label class="tb-label"><?= $label ?></label>
                            <div class="color-group">
                                <input type="color" class="color-swatch" value="<?= thv($s, $key, $def) ?>"
                                       oninput="syncColor(this,'<?= $key ?>')" id="swatch-<?= $key ?>">
                                <input type="text" class="color-hex" name="<?= $key ?>" value="<?= thv($s, $key, $def) ?>"
                                       oninput="syncSwatch(this,'<?= $key ?>')" id="hex-<?= $key ?>" maxlength="30">
                            </div>
                            <div class="tb-hint"><?= $hint ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="tb-section">
                    <div class="tb-section-title"><i data-lucide="type" style="width:14px;height:14px;"></i> Couleurs de texte</div>
                    <div class="tb-row">
                        <?php
                        $textColors = [
                            ['theme_text_main',  '#0f172a', 'Texte principal',  'Titres, textes importants'],
                            ['theme_text_sub',   '#334155', 'Texte secondaire', 'Paragraphes courants'],
                            ['theme_text_muted', '#64748b', 'Texte atténué',    'Labels, métadonnées'],
                        ];
                        foreach ($textColors as [$key, $def, $label, $hint]): ?>
                        <div class="tb-field">
                            <label class="tb-label"><?= $label ?></label>
                            <div class="color-group">
                                <input type="color" class="color-swatch" value="<?= thv($s, $key, $def) ?>"
                                       oninput="syncColor(this,'<?= $key ?>')" id="swatch-<?= $key ?>">
                                <input type="text" class="color-hex" name="<?= $key ?>" value="<?= thv($s, $key, $def) ?>"
                                       oninput="syncSwatch(this,'<?= $key ?>')" id="hex-<?= $key ?>" maxlength="30">
                            </div>
                            <div class="tb-hint"><?= $hint ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="tb-section">
                    <div class="tb-section-title"><i data-lucide="layout" style="width:14px;height:14px;"></i> Couleurs de fond</div>
                    <div class="tb-row">
                        <?php
                        $bgColors = [
                            ['theme_bg_base', '#ffffff', 'Fond principal',   'Corps de la page'],
                            ['theme_bg_alt',  '#f8fafc', 'Fond alternatif',  'Sections alternées'],
                            ['theme_bg_card', '#ffffff', 'Fond des cartes',  'Cards, panneaux'],
                        ];
                        foreach ($bgColors as [$key, $def, $label, $hint]): ?>
                        <div class="tb-field">
                            <label class="tb-label"><?= $label ?></label>
                            <div class="color-group">
                                <input type="color" class="color-swatch" value="<?= thv($s, $key, $def) ?>"
                                       oninput="syncColor(this,'<?= $key ?>')" id="swatch-<?= $key ?>">
                                <input type="text" class="color-hex" name="<?= $key ?>" value="<?= thv($s, $key, $def) ?>"
                                       oninput="syncSwatch(this,'<?= $key ?>')" id="hex-<?= $key ?>" maxlength="30">
                            </div>
                            <div class="tb-hint"><?= $hint ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div><!-- /panel-palette -->


            <!-- ╔══════════════════════════════╗
                 ║  PANEL : Typographie         ║
                 ╚══════════════════════════════╝ -->
            <div id="panel-typo" class="tb-panel">

                <div class="tb-section">
                    <div class="tb-section-title"><i data-lucide="heading-1" style="width:14px;height:14px;"></i> Tailles des titres (rem max)</div>
                    <div class="tb-row-3">
                        <?php
                        $fontSizes = [
                            ['theme_font_h1', '4.2', 'H1 — Héro', '1', '8', '0.1'],
                            ['theme_font_h2', '2.8', 'H2 — Sections', '1', '6', '0.1'],
                            ['theme_font_h3', '1.08', 'H3 — Cartes', '0.8', '3', '0.02'],
                        ];
                        foreach ($fontSizes as [$key, $def, $label, $min, $max, $step]):
                            $val = thv($s, $key, $def);
                        ?>
                        <div class="tb-field">
                            <label class="tb-label"><?= $label ?></label>
                            <div class="tb-range-wrap">
                                <input type="range" class="tb-range" min="<?= $min ?>" max="<?= $max ?>" step="<?= $step ?>"
                                       value="<?= $val ?>" oninput="updateRange(this,'rng-<?= $key ?>')">
                                <span class="tb-range-val" id="rng-<?= $key ?>"><?= $val ?>rem</span>
                            </div>
                            <input type="hidden" name="<?= $key ?>" id="inp-<?= $key ?>" value="<?= $val ?>">
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="tb-section">
                    <div class="tb-section-title"><i data-lucide="baseline" style="width:14px;height:14px;"></i> Corps de texte</div>
                    <div class="tb-row-2">
                        <?php
                        $bodyFonts = [
                            ['theme_font_body', '1', 'Taille corps (rem)', '0.7', '1.4', '0.02'],
                            ['theme_line_height_body', '1.78', 'Hauteur de ligne', '1', '2.5', '0.02'],
                        ];
                        foreach ($bodyFonts as [$key, $def, $label, $min, $max, $step]):
                            $val = thv($s, $key, $def);
                        ?>
                        <div class="tb-field">
                            <label class="tb-label"><?= $label ?></label>
                            <div class="tb-range-wrap">
                                <input type="range" class="tb-range" min="<?= $min ?>" max="<?= $max ?>" step="<?= $step ?>"
                                       value="<?= $val ?>" oninput="updateRange(this,'rng-<?= $key ?>')">
                                <span class="tb-range-val" id="rng-<?= $key ?>"><?= $val ?></span>
                            </div>
                            <input type="hidden" name="<?= $key ?>" id="inp-<?= $key ?>" value="<?= $val ?>">
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="tb-section">
                    <div class="tb-section-title"><i data-lucide="bold" style="width:14px;height:14px;"></i> Graisses &amp; Espacement</div>
                    <div class="tb-row-3">
                        <div class="tb-field">
                            <label class="tb-label">Graisse titres</label>
                            <select class="tb-select" name="theme_font_weight_heading">
                                <?php foreach ([400=>'Normal (400)',500=>'Medium (500)',600=>'Semi-Bold (600)',700=>'Bold (700)',800=>'Extra-Bold (800)',900=>'Black (900)'] as $w => $l): ?>
                                <option value="<?= $w ?>" <?= (int)thv($s,'theme_font_weight_heading','800') === $w ? 'selected' : '' ?>><?= $l ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="tb-field">
                            <label class="tb-label">Graisse corps</label>
                            <select class="tb-select" name="theme_font_weight_body">
                                <?php foreach ([300=>'Light (300)',400=>'Normal (400)',500=>'Medium (500)'] as $w => $l): ?>
                                <option value="<?= $w ?>" <?= (int)thv($s,'theme_font_weight_body','400') === $w ? 'selected' : '' ?>><?= $l ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="tb-field">
                            <label class="tb-label">Letter-spacing titres (em)</label>
                            <?php $lsVal = thv($s, 'theme_letter_spacing_heading', '-0.032'); ?>
                            <div class="tb-range-wrap">
                                <input type="range" class="tb-range" min="-0.08" max="0.1" step="0.002"
                                       value="<?= $lsVal ?>" oninput="updateRange(this,'rng-ls')">
                                <span class="tb-range-val" id="rng-ls"><?= $lsVal ?>em</span>
                            </div>
                            <input type="hidden" name="theme_letter_spacing_heading" id="inp-theme_letter_spacing_heading" value="<?= $lsVal ?>">
                        </div>
                    </div>
                </div>

            </div><!-- /panel-typo -->


            <!-- ╔══════════════════════════════╗
                 ║  PANEL : Formes & Espacements║
                 ╚══════════════════════════════╝ -->
            <div id="panel-shapes" class="tb-panel">

                <div class="tb-section">
                    <div class="tb-section-title"><i data-lucide="maximize-2" style="width:14px;height:14px;"></i> Espacement des sections (px)</div>
                    <?php $ssVal = thv($s, 'theme_space_section', '130'); ?>
                    <div class="tb-field" style="max-width:500px;">
                        <label class="tb-label">Padding vertical des sections</label>
                        <div class="tb-range-wrap">
                            <input type="range" class="tb-range" min="40" max="240" step="4"
                                   value="<?= $ssVal ?>" oninput="updateRange(this,'rng-ss','',' px')">
                            <span class="tb-range-val" id="rng-ss"><?= $ssVal ?> px</span>
                        </div>
                        <input type="hidden" name="theme_space_section" id="inp-theme_space_section" value="<?= $ssVal ?>">
                        <div class="tb-hint">Desktop. Tablette = -50px, Mobile = -76px</div>
                    </div>
                </div>

                <div class="tb-section">
                    <div class="tb-section-title"><i data-lucide="circle" style="width:14px;height:14px;"></i> Rayons des bordures</div>
                    <div class="tb-row-3">
                        <?php
                        $radii = [
                            ['theme_radius_pill', '100', 'Rayon Pill (boutons)', 0, 100, 1],
                            ['theme_radius_card', '20',  'Rayon cartes',         4, 40,  1],
                            ['theme_radius_btn',  '100', 'Rayon boutons',        0, 100, 1],
                            ['theme_radius_md',   '12',  'Rayon MD (inputs)',    4, 24,  1],
                            ['theme_radius_sm',   '8',   'Rayon SM (badges)',    0, 16,  1],
                        ];
                        foreach ($radii as [$key, $def, $label, $min, $max, $step]):
                            $val = thv($s, $key, $def);
                        ?>
                        <div class="tb-field">
                            <label class="tb-label"><?= $label ?></label>
                            <div class="tb-range-wrap">
                                <input type="range" class="tb-range" min="<?= $min ?>" max="<?= $max ?>" step="<?= $step ?>"
                                       value="<?= $val ?>" oninput="updateRange(this,'rng-<?= $key ?>','',' px')">
                                <span class="tb-range-val" id="rng-<?= $key ?>"><?= $val ?> px</span>
                            </div>
                            <input type="hidden" name="<?= $key ?>" id="inp-<?= $key ?>" value="<?= $val ?>">
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div><!-- /panel-shapes -->


            <!-- ╔══════════════════════════════╗
                 ║  PANEL : Boutons             ║
                 ╚══════════════════════════════╝ -->
            <div id="panel-buttons" class="tb-panel">

                <div class="tb-section">
                    <div class="tb-section-title"><i data-lucide="mouse-pointer-click" style="width:14px;height:14px;"></i> Aperçu boutons</div>
                    <div id="btn-preview" style="display:flex;gap:14px;align-items:center;flex-wrap:wrap;padding:20px;background:#f8fafc;border-radius:10px;margin-bottom:4px;">
                        <button type="button" id="prev-btn-primary" style="display:inline-flex;align-items:center;gap:8px;padding:13px 32px;background:#0d9488;color:white;border:none;border-radius:100px;font-size:0.9rem;font-weight:600;cursor:default;box-shadow:0 4px 18px rgba(13,148,136,0.28);">
                            Bouton principal
                        </button>
                        <button type="button" id="prev-btn-secondary" style="display:inline-flex;align-items:center;gap:8px;padding:13px 32px;background:transparent;color:#0f172a;border:1.5px solid rgba(0,0,0,0.15);border-radius:100px;font-size:0.9rem;font-weight:600;cursor:default;">
                            Bouton secondaire
                        </button>
                        <button type="button" id="prev-btn-cta" style="display:inline-flex;align-items:center;gap:8px;padding:10px 24px;background:#0d9488;color:white;border:none;border-radius:100px;font-size:0.85rem;font-weight:600;cursor:default;box-shadow:0 4px 18px rgba(13,148,136,0.28);">
                            CTA Navigation
                        </button>
                    </div>
                </div>

                <div class="tb-section">
                    <div class="tb-section-title"><i data-lucide="sliders" style="width:14px;height:14px;"></i> Paramètres boutons</div>
                    <div class="tb-row-3">
                        <div class="tb-field">
                            <label class="tb-label">Rayon (px)</label>
                            <?php $btnR = thv($s, 'theme_radius_btn', '100'); ?>
                            <div class="tb-range-wrap">
                                <input type="range" class="tb-range" min="0" max="100" step="1" value="<?= $btnR ?>"
                                       oninput="updateRange(this,'rng-btnr','',' px');updateBtnPreview();">
                                <span class="tb-range-val" id="rng-btnr"><?= $btnR ?> px</span>
                            </div>
                            <input type="hidden" name="theme_radius_btn" id="inp-theme_radius_btn" value="<?= $btnR ?>">
                        </div>
                        <div class="tb-field">
                            <label class="tb-label">Couleur principale</label>
                            <div class="color-group">
                                <input type="color" class="color-swatch" value="<?= thv($s,'theme_primary','#0d9488') ?>"
                                       oninput="syncColor(this,'theme_primary');updateBtnPreview();" id="swatch-btn-primary">
                                <input type="text" class="color-hex" value="<?= thv($s,'theme_primary','#0d9488') ?>"
                                       oninput="syncSwatch(this,'theme_primary');updateBtnPreview();" id="hex-theme_primary2" maxlength="30">
                            </div>
                            <div class="tb-hint">Même que la couleur principale</div>
                        </div>
                        <div class="tb-field">
                            <label class="tb-label">Ombre bouton principal</label>
                            <input type="text" class="tb-input" name="theme_shadow_btn"
                                   value="<?= thv($s,'theme_shadow_btn','0 4px 18px rgba(13,148,136,0.28)') ?>" placeholder="0 4px 18px rgba(...)">
                        </div>
                    </div>
                </div>

            </div><!-- /panel-buttons -->


            <!-- ╔══════════════════════════════╗
                 ║  PANEL : Ombres              ║
                 ╚══════════════════════════════╝ -->
            <div id="panel-shadows" class="tb-panel">

                <div class="tb-section">
                    <div class="tb-section-title"><i data-lucide="layers" style="width:14px;height:14px;"></i> Aperçu ombres</div>
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;padding:20px;background:#f8fafc;border-radius:10px;margin-bottom:4px;">
                        <?php
                        $shadowPresets = ['Légère','Moyenne','Forte'];
                        $shadowVals    = [
                            '0 1px 3px rgba(0,0,0,0.04), 0 4px 12px rgba(0,0,0,0.05)',
                            '0 1px 3px rgba(0,0,0,0.04), 0 8px 32px rgba(0,0,0,0.06)',
                            '0 4px 8px rgba(0,0,0,0.06), 0 16px 48px rgba(0,0,0,0.10)',
                        ];
                        foreach ($shadowPresets as $i => $sp): ?>
                        <div style="background:#fff;border-radius:12px;padding:20px;box-shadow:<?= $shadowVals[$i] ?>;text-align:center;cursor:pointer;" onclick="applyCardShadow('<?= addslashes($shadowVals[$i]) ?>')">
                            <div style="font-size:0.8rem;font-weight:600;color:#475569;margin-bottom:4px;"><?= $sp ?></div>
                            <div style="font-size:0.7rem;color:#94a3b8;">Cliquer pour appliquer</div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="tb-section">
                    <div class="tb-section-title"><i data-lucide="edit-3" style="width:14px;height:14px;"></i> Valeurs CSS custom</div>
                    <div class="tb-row-2">
                        <div class="tb-field">
                            <label class="tb-label">Ombre carte (box-shadow)</label>
                            <input type="text" class="tb-input" name="theme_shadow_card" id="inp-shadow-card"
                                   value="<?= thv($s,'theme_shadow_card','0 1px 3px rgba(0,0,0,0.04), 0 8px 32px rgba(0,0,0,0.06)') ?>"
                                   placeholder="box-shadow value...">
                            <div class="tb-hint">Valeur box-shadow complète pour les cartes</div>
                        </div>
                        <div class="tb-field">
                            <label class="tb-label">Ombre bouton (box-shadow)</label>
                            <input type="text" class="tb-input" name="theme_shadow_btn"
                                   value="<?= thv($s,'theme_shadow_btn','0 4px 18px rgba(13,148,136,0.28)') ?>"
                                   placeholder="box-shadow value...">
                            <div class="tb-hint">Valeur box-shadow pour les boutons principaux</div>
                        </div>
                    </div>
                </div>

            </div><!-- /panel-shadows -->


            <!-- ╔══════════════════════════════╗
                 ║  PANEL : Hero Section        ║
                 ╚══════════════════════════════╝ -->
            <div id="panel-hero" class="tb-panel">

                <div class="tb-section">
                    <div class="tb-section-title"><i data-lucide="image" style="width:14px;height:14px;"></i> Paramètres Hero globaux</div>
                    <div class="tb-row-3">
                        <div class="tb-field">
                            <label class="tb-label">Hauteur minimale (%vh)</label>
                            <?php $hmVal = thv($s, 'theme_hero_min_height', '100'); ?>
                            <div class="tb-range-wrap">
                                <input type="range" class="tb-range" min="40" max="100" step="5"
                                       value="<?= $hmVal ?>" oninput="updateRange(this,'rng-hm','',' vh')">
                                <span class="tb-range-val" id="rng-hm"><?= $hmVal ?> vh</span>
                            </div>
                            <input type="hidden" name="theme_hero_min_height" id="inp-theme_hero_min_height" value="<?= $hmVal ?>">
                        </div>
                        <div class="tb-field">
                            <label class="tb-label">Opacité overlay</label>
                            <?php $hoVal = thv($s, 'theme_hero_overlay_opacity', '0.78'); ?>
                            <div class="tb-range-wrap">
                                <input type="range" class="tb-range" min="0" max="1" step="0.02"
                                       value="<?= $hoVal ?>" oninput="updateRange(this,'rng-ho')">
                                <span class="tb-range-val" id="rng-ho"><?= $hoVal ?></span>
                            </div>
                            <input type="hidden" name="theme_hero_overlay_opacity" id="inp-theme_hero_overlay_opacity" value="<?= $hoVal ?>">
                            <div class="tb-hint">0 = transparent · 1 = opaque</div>
                        </div>
                        <div class="tb-field">
                            <label class="tb-label">Couleur de l'overlay</label>
                            <div class="color-group">
                                <input type="color" class="color-swatch" value="<?= thv($s,'theme_hero_overlay_color','#0a0f1e') ?>"
                                       oninput="syncColor(this,'theme_hero_overlay_color')" id="swatch-theme_hero_overlay_color">
                                <input type="text" class="color-hex" name="theme_hero_overlay_color" value="<?= thv($s,'theme_hero_overlay_color','#0a0f1e') ?>"
                                       oninput="syncSwatch(this,'theme_hero_overlay_color')" id="hex-theme_hero_overlay_color" maxlength="30">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tb-section">
                    <div class="tb-section-title"><i data-lucide="info" style="width:14px;height:14px;"></i> Paramètres Hero par page</div>
                    <div style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:10px;padding:16px 20px;color:#0369a1;font-size:0.88rem;line-height:1.65;">
                        <strong>💡 Conseil :</strong> Les paramètres Hero spécifiques à chaque page (image, titre, sous-titre, boutons, variante) sont administrables directement dans
                        <a href="<?= url('/admin/pages') ?>" style="color:#0891b2;font-weight:600;">Pages CMS → Modifier la page → onglet Hero</a>.
                        Les réglages ci-dessus s'appliquent globalement à tous les heros du site.
                    </div>
                </div>

            </div><!-- /panel-hero -->


            <!-- ── Barre de sauvegarde ── -->
            <div class="tb-save-bar">
                <div style="font-size:0.85rem;color:#64748b;">
                    <i data-lucide="info" style="width:14px;height:14px;display:inline;vertical-align:-3px;margin-right:4px;"></i>
                    Les modifications sont appliquées instantanément sur le site après sauvegarde.
                </div>
                <div style="display:flex;gap:12px;align-items:center;">
                    <button type="button" class="tb-btn-reset" onclick="if(confirm('Réinitialiser les valeurs par défaut ?')) window.location.href='?reset=1'">
                        <i data-lucide="rotate-ccw" style="width:15px;height:15px;"></i> Réinitialiser
                    </button>
                    <button type="submit" class="tb-btn-save">
                        <i data-lucide="save" style="width:15px;height:15px;"></i> Enregistrer le thème
                    </button>
                </div>
            </div>

        </div><!-- /tb-content -->
    </div><!-- /tb-wrap -->
</form>

<script>
// ── Panel switching ──────────────────────────────────────────────────────
function showPanel(id, el) {
    document.querySelectorAll('.tb-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.tb-nav-item').forEach(n => n.classList.remove('active'));
    document.getElementById('panel-' + id).classList.add('active');
    el.classList.add('active');
}

// ── Color picker ↔ hex input sync ───────────────────────────────────────
function syncColor(picker, key) {
    const hex = document.getElementById('hex-' + key);
    if (hex) hex.value = picker.value;
    refreshPaletteDots();
    updateBtnPreview();
}
function syncSwatch(input, key) {
    const val = input.value;
    if (/^#[0-9a-fA-F]{6}$/.test(val)) {
        const sw = document.getElementById('swatch-' + key);
        if (sw) sw.value = val;
    }
    refreshPaletteDots();
    updateBtnPreview();
}

// ── Range slider ────────────────────────────────────────────────────────
function updateRange(el, displayId, prefix, suffix) {
    prefix = prefix || '';
    suffix = suffix || '';
    const display = document.getElementById(displayId);
    if (display) display.textContent = prefix + el.value + suffix;
    // Update hidden input by deriving name from range name attribute
    const hiddenId = 'inp-' + el.closest('.tb-field').querySelector('input[type=hidden]')?.id.replace('inp-','');
    const hidden = el.closest('.tb-field').querySelector('input[type=hidden]');
    if (hidden) hidden.value = el.value;
}

// ── Palette dots preview ─────────────────────────────────────────────────
function refreshPaletteDots() {
    const keys = ['theme_primary','theme_secondary','theme_accent','theme_text_main','theme_bg_base','theme_bg_alt'];
    const container = document.getElementById('palette-dots');
    if (!container) return;
    container.innerHTML = '';
    keys.forEach(k => {
        const hex = document.getElementById('hex-' + k);
        if (!hex) return;
        const dot = document.createElement('div');
        dot.className = 'tb-preview-color';
        dot.style.background = hex.value;
        dot.title = k.replace('theme_','').replace(/_/g,' ') + ': ' + hex.value;
        container.appendChild(dot);
    });
}

// ── Button preview ───────────────────────────────────────────────────────
function updateBtnPreview() {
    const color  = document.getElementById('hex-theme_primary')?.value || '#0d9488';
    const radius = document.getElementById('inp-theme_radius_btn')?.value || '100';
    const pBtn   = document.getElementById('prev-btn-primary');
    const cBtn   = document.getElementById('prev-btn-cta');
    if (pBtn) {
        pBtn.style.background     = color;
        pBtn.style.borderRadius   = radius + 'px';
    }
    if (cBtn) {
        cBtn.style.background   = color;
        cBtn.style.borderRadius = radius + 'px';
    }
    const sBtn = document.getElementById('prev-btn-secondary');
    if (sBtn) sBtn.style.borderRadius = radius + 'px';
}

// ── Shadow preset apply ──────────────────────────────────────────────────
function applyCardShadow(val) {
    const inp = document.getElementById('inp-shadow-card');
    if (inp) inp.value = val;
}

// ── Init ─────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    refreshPaletteDots();
    updateBtnPreview();
    lucide.createIcons();
});
</script>
