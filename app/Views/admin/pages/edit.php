<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

<style>
    .builder-container {
        display: grid;
        grid-template-columns: 280px 1fr;
        gap: 28px;
        align-items: start;
    }

    @media (max-width: 1024px) {
        .builder-container {
            grid-template-columns: 1fr;
        }
    }

    .settings-panel {
        display: grid;
        grid-template-columns: 1fr;
        gap: 20px;
        margin-bottom: 24px;
    }

    .sections-sidebar {
        background-color: var(--bg-surface);
        backdrop-filter: blur(24px);
        -webkit-backdrop-filter: blur(24px);
        border: 1px solid var(--border-highlight);
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 20px 40px -15px rgba(37, 99, 235, 0.05), 0 5px 15px -5px rgba(0, 0, 0, 0.02);
    }
    .sidebar-header {
        padding: 16px 20px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        background-color: rgba(255, 255, 255, 0.1);
    }
    .sidebar-title {
        font-family: var(--font-headings);
        font-size: 0.95rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-muted);
    }
    
    .section-list {
        padding: 12px;
        display: flex;
        flex-direction: column;
        gap: 8px;
        min-height: 200px;
    }

    .section-nav-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 14px;
        background-color: rgba(255, 255, 255, 0.4);
        border: 1px solid var(--border);
        border-radius: 12px;
        cursor: grab;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        user-select: none;
    }
    .section-nav-item:active {
        cursor: grabbing;
    }
    .section-nav-item.active {
        border-color: var(--primary);
        background-color: rgba(37, 99, 235, 0.08);
        box-shadow: 0 4px 12px -3px rgba(37, 99, 235, 0.15);
    }
    .section-nav-item:hover:not(.active) {
        border-color: rgba(37, 99, 235, 0.2);
        background-color: rgba(255, 255, 255, 0.7);
    }

    .section-meta-left {
        display: flex;
        align-items: center;
        gap: 10px;
        overflow: hidden;
    }
    .section-icon {
        color: var(--text-muted);
        flex-shrink: 0;
    }
    .section-nav-item.active .section-icon {
        color: var(--primary);
    }
    .section-name-text {
        font-weight: 600;
        font-size: 0.9rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .section-actions {
        display: flex;
        gap: 4px;
        opacity: 0;
        transition: opacity 0.2s;
    }
    .section-nav-item:hover .section-actions,
    .section-nav-item.active .section-actions {
        opacity: 1;
    }

    .action-btn-small {
        background: none;
        border: none;
        color: var(--text-muted);
        cursor: pointer;
        padding: 4px;
        border-radius: 6px;
        transition: all 0.15s;
    }
    .action-btn-small:hover {
        color: var(--text-main);
        background-color: rgba(0, 0, 0, 0.04);
    }
    .action-btn-small.delete-sec:hover {
        color: var(--danger);
        background-color: rgba(239, 68, 68, 0.08);
    }

    .editor-workspace {
        flex-grow: 1;
    }
    
    .section-form-panel {
        display: none;
    }
    .section-form-panel.active {
        display: block;
        animation: fadeIn 0.2s ease-in-out forwards;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(4px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .repeatable-deck {
        margin-top: 24px;
        border-top: 1px solid var(--border);
        padding-top: 20px;
    }
    .deck-title {
        font-family: var(--font-headings);
        font-size: 1.05rem;
        font-weight: 600;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .cards-grid {
        display: flex;
        flex-direction: column;
        gap: 16px;
        margin-bottom: 20px;
    }
    .item-card {
        background-color: rgba(255, 255, 255, 0.45);
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 20px;
        position: relative;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.01);
    }
    .item-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
        border-bottom: 1px dashed var(--border);
        padding-bottom: 10px;
    }
    .item-card-title {
        font-weight: 700;
        font-size: 0.9rem;
        color: var(--primary);
    }

    .media-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(15, 23, 42, 0.25);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        padding: 20px;
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }
    .media-modal.active {
        display: flex;
    }
    .modal-content {
        background-color: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.6);
        border-radius: 24px;
        width: 100%;
        max-width: 800px;
        max-height: 80vh;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        box-shadow: 0 30px 60px -15px rgba(37, 99, 235, 0.15), 0 10px 20px -5px rgba(0, 0, 0, 0.03);
    }
    .modal-header {
        padding: 20px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .modal-body {
        padding: 20px;
        overflow-y: auto;
        flex-grow: 1;
    }
    .modal-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 16px;
    }
    .modal-media-item {
        background-color: rgba(255,255,255,0.4);
        border: 1px solid var(--border);
        border-radius: 12px;
        aspect-ratio: 1;
        overflow: hidden;
        cursor: pointer;
        position: relative;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .modal-media-item:hover {
        border-color: var(--primary);
        transform: translateY(-2px);
        box-shadow: 0 8px 16px -4px rgba(37, 99, 235, 0.2);
    }
    .modal-media-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .image-field-wrapper {
        display: flex;
        gap: 16px;
        align-items: center;
    }
    .image-field-preview {
        width: 70px;
        height: 70px;
        border-radius: 12px;
        border: 1px solid var(--border);
        background-color: rgba(255, 255, 255, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        flex-shrink: 0;
    }
    .image-field-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
</style>

<?php
$respSettings = [];
if (!empty($page['responsive_settings'])) {
    $respSettings = json_decode($page['responsive_settings'], true) ?: [];
}
$visualSettings = $respSettings['visual'] ?? [];
$headerContrastMode = $page['header_contrast_mode'] ?? 'default';
$headerBgMode = $page['header_bg_mode'] ?? 'glass';

$logoLight = !empty($page['logo_light']) ? $page['logo_light'] : ($settings['site_logo_light'] ?? '');
$logoDark = !empty($page['logo_dark']) ? $page['logo_dark'] : ($settings['site_logo_dark'] ?? '');

$selectedLogo = $settings['site_logo'] ?? '';
if ($headerContrastMode === 'light_on_dark' || $headerContrastMode === 'light on dark') {
    $selectedLogo = !empty($logoLight) ? $logoLight : ($settings['site_logo_light'] ?? ($settings['site_logo'] ?? ''));
} elseif ($headerContrastMode === 'dark_on_light' || $headerContrastMode === 'dark on light') {
    $selectedLogo = !empty($logoDark) ? $logoDark : ($settings['site_logo_dark'] ?? ($settings['site_logo'] ?? ''));
} else {
    if ($headerBgMode === 'sombre') {
        $selectedLogo = !empty($logoLight) ? $logoLight : ($settings['site_logo_light'] ?? ($settings['site_logo'] ?? ''));
    } else {
        $selectedLogo = !empty($logoDark) ? $logoDark : ($settings['site_logo_dark'] ?? ($settings['site_logo'] ?? ''));
    }
}
if (empty($selectedLogo)) {
    $selectedLogo = $settings['site_logo'] ?? '';
}
?>
<div class="config-grid-wrapper" style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 24px; margin-bottom: 28px; align-items: start;">
    
    <!-- Config Form Column -->
    <div class="card" style="padding: 24px; margin-bottom: 0;">
        <form action="<?= url('/admin/pages/edit/' . $page['id']) ?>" method="POST" style="display: flex; flex-direction: column; gap: 24px;">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <input type="hidden" name="responsive_settings" id="responsive_settings_input" value="<?= htmlspecialchars($page['responsive_settings'] ?? '') ?>">

            <div style="border-bottom: 1px solid var(--border); padding-bottom: 16px;">
                <h3 style="font-family: var(--font-heading); font-size: 1.2rem; color: var(--primary); display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
                    <i data-lucide="settings" style="width: 20px; height: 20px;"></i>
                    <span>Configuration de la Page</span>
                </h3>
                <p style="font-size: 0.85rem; color: var(--text-muted);">Gérez les métadonnées de la page, le style de l'en-tête (Header) et la Hero section.</p>
            </div>

            <!-- Part 1: Page Metadata -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px;">
                <div class="admin-form-group">
                    <label for="page_title">Titre de la page</label>
                    <input type="text" id="page_title" name="title" class="admin-input" value="<?= htmlspecialchars($page['title']) ?>" required>
                </div>

                <div class="admin-form-group">
                    <label for="page_slug">URL Slug</label>
                    <input type="text" id="page_slug" name="slug" class="admin-input" value="<?= htmlspecialchars($page['slug']) ?>" <?= $page['slug'] === 'home' ? 'readonly style="background-color: rgba(255,255,255,0.02); opacity:0.75;"' : '' ?> required>
                </div>

                <div class="admin-form-group">
                    <label for="page_status">Statut</label>
                    <select id="page_status" name="status" class="admin-select">
                        <option value="draft" <?= $page['status'] === 'draft' ? 'selected' : '' ?>>Brouillon</option>
                        <option value="published" <?= $page['status'] === 'published' ? 'selected' : '' ?>>Publiée</option>
                    </select>
                </div>

                <div class="admin-form-group">
                    <label for="page_sort_order">Ordre Menu</label>
                    <input type="number" id="page_sort_order" name="sort_order" class="admin-input" value="<?= (int)($page['sort_order'] ?? 0) ?>" required>
                </div>

                <div class="admin-form-group">
                    <label for="page_in_navigation">Dans le Menu</label>
                    <select id="page_in_navigation" name="in_navigation" class="admin-select">
                        <option value="1" <?= (int)($page['in_navigation'] ?? 1) === 1 ? 'selected' : '' ?>>Oui</option>
                        <option value="0" <?= (int)($page['in_navigation'] ?? 1) === 0 ? 'selected' : '' ?>>Non</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
                <div class="admin-form-group">
                    <label for="page_meta_title">Meta Title SEO</label>
                    <input type="text" id="page_meta_title" name="meta_title" class="admin-input" value="<?= htmlspecialchars($page['meta_title'] ?? '') ?>">
                </div>

                <div class="admin-form-group">
                    <label for="page_meta_description">Meta Description SEO</label>
                    <textarea id="page_meta_description" name="meta_description" class="admin-input" style="height: 42px; min-height: 42px; resize: vertical;"><?= htmlspecialchars($page['meta_description'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Part 1.5: Paramètres Visuels (Header & Logo) -->
            <div style="border-top: 1px dashed var(--border); padding-top: 20px; margin-top: 10px;">
                <h4 id="header_config_title" style="font-family: var(--font-heading); font-size: 1.05rem; color: var(--primary); display: flex; align-items: center; gap: 8px; margin-bottom: 16px;">
                    <i data-lucide="palette" style="width: 18px; height: 18px; color: var(--secondary);"></i>
                    <span>Paramètres Visuels (Header & Logo)</span>
                </h4>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; background-color: rgba(255,255,255,0.15); border: 1px solid var(--border); padding: 18px; border-radius: 14px;">
                    
                    <!-- Background Mode -->
                    <div class="admin-form-group">
                        <label for="header_bg_mode">Mode de Fond du Header</label>
                        <select id="header_bg_mode" name="header_bg_mode" class="admin-select visual-simulator-trigger">
                            <option value="glass" <?= ($page['header_bg_mode'] ?? 'glass') === 'glass' ? 'selected' : '' ?>>Flottant Effet Verre (Glassmorphic)</option>
                            <option value="clair" <?= ($page['header_bg_mode'] ?? 'glass') === 'clair' ? 'selected' : '' ?>>Clair (Blanc transparent)</option>
                            <option value="semi-transparent" <?= ($page['header_bg_mode'] ?? 'glass') === 'semi-transparent' ? 'selected' : '' ?>>Semi-transparent</option>
                            <option value="sombre" <?= ($page['header_bg_mode'] ?? 'glass') === 'sombre' ? 'selected' : '' ?>>Sombre (Slate transparent)</option>
                            <option value="blur" <?= ($page['header_bg_mode'] ?? 'glass') === 'blur' ? 'selected' : '' ?>>Flou Intense (Blur)</option>
                            <option value="plein" <?= ($page['header_bg_mode'] ?? 'glass') === 'plein' ? 'selected' : '' ?>>Solide Plein (Sans transparence)</option>
                        </select>
                    </div>

                    <!-- Opacity -->
                    <div class="admin-form-group">
                        <label for="header_opacity">Opacité du Fond (<span id="header_opacity_val">0.65</span>)</label>
                        <input type="range" id="header_opacity" name="header_opacity" min="0.1" max="1.0" step="0.05" class="admin-input visual-simulator-trigger" value="<?= isset($page['header_opacity']) ? (float)$page['header_opacity'] : 0.65 ?>">
                    </div>

                    <!-- Blur intensity -->
                    <div class="admin-form-group">
                        <label for="header_blur">Intensité du Flou (Blur px)</label>
                        <input type="number" id="header_blur" name="header_blur" min="0" max="100" class="admin-input visual-simulator-trigger" value="<?= isset($page['header_blur']) ? (int)$page['header_blur'] : 20 ?>">
                    </div>

                    <!-- Shadow -->
                    <div class="admin-form-group">
                        <label for="header_shadow">Ombre du Header</label>
                        <select id="header_shadow" name="header_shadow" class="admin-select visual-simulator-trigger">
                            <option value="aucun" <?= ($page['header_shadow'] ?? 'moyen') === 'aucun' ? 'selected' : '' ?>>Aucune ombre</option>
                            <option value="leger" <?= ($page['header_shadow'] ?? 'moyen') === 'leger' ? 'selected' : '' ?>>Légère</option>
                            <option value="moyen" <?= ($page['header_shadow'] ?? 'moyen') === 'moyen' ? 'selected' : '' ?>>Moyenne (Standard)</option>
                            <option value="fort" <?= ($page['header_shadow'] ?? 'moyen') === 'fort' ? 'selected' : '' ?>>Forte / Profonde</option>
                        </select>
                    </div>

                    <!-- Contrast Mode -->
                    <div class="admin-form-group">
                        <label for="header_contrast_mode">Mode de Contraste du Menu</label>
                        <select id="header_contrast_mode" name="header_contrast_mode" class="admin-select visual-simulator-trigger">
                            <option value="default" <?= ($page['header_contrast_mode'] ?? 'default') === 'default' ? 'selected' : '' ?>>Défaut (S'adapte au site)</option>
                            <option value="light_on_dark" <?= ($page['header_contrast_mode'] ?? 'default') === 'light_on_dark' ? 'selected' : '' ?>>Blanc sur Fond Sombre</option>
                            <option value="dark_on_light" <?= ($page['header_contrast_mode'] ?? 'default') === 'dark_on_light' ? 'selected' : '' ?>>Slate sur Fond Clair</option>
                            <option value="glass" <?= ($page['header_contrast_mode'] ?? 'default') === 'glass' ? 'selected' : '' ?>>Glassmorphic fluide</option>
                            <option value="solid" <?= ($page['header_contrast_mode'] ?? 'default') === 'solid' ? 'selected' : '' ?>>Contraste maximal</option>
                        </select>
                    </div>

                    <!-- Logo Sizing -->
                    <div class="admin-form-group">
                        <label for="logo_size">Hauteur du Logo (px)</label>
                        <input type="number" id="logo_size" name="logo_size" min="15" max="100" class="admin-input visual-simulator-trigger" value="<?= isset($page['logo_size']) ? (int)$page['logo_size'] : 38 ?>">
                    </div>

                    <!-- Logo Light Version -->
                    <div class="admin-form-group">
                        <label>Logo Fond Sombre (Version Claire)</label>
                        <?= \App\Helpers\MediaHelper::renderField('logo_light', $page['logo_light'] ?? '', 'logo_light') ?>
                    </div>

                    <!-- Logo Dark Version -->
                    <div class="admin-form-group">
                        <label>Logo Fond Clair (Version Sombre)</label>
                        <?= \App\Helpers\MediaHelper::renderField('logo_dark', $page['logo_dark'] ?? '', 'logo_dark') ?>
                    </div>
                </div>
            </div>

            <!-- Part 2: Unified Hero Manager -->
            <div style="border-top: 1px dashed var(--border); padding-top: 20px; margin-top: 10px;">
                <h4 id="hero_config_title" style="font-family: var(--font-heading); font-size: 1.05rem; color: var(--primary); display: flex; align-items: center; gap: 8px; margin-bottom: 16px;">
                    <i data-lucide="layout-template" style="width: 18px; height: 18px; color: var(--secondary);"></i>
                    <span>Gestionnaire de Hero Section</span>
                </h4>

                <div style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 20px; align-items: start;">
                    <!-- Hero Left Fields -->
                    <div style="display: flex; flex-direction: column; gap: 14px;">
                        <div class="admin-form-group">
                            <label for="hero_title">Titre principal de la Hero (HTML supporté)</label>
                            <input type="text" id="hero_title" name="hero_title" class="admin-input" value="<?= htmlspecialchars($page['hero_title'] ?? '') ?>" placeholder="Ex: Des solutions &lt;span class='hi'&gt;innovantes&lt;/span&gt;">
                        </div>

                        <div class="admin-form-group">
                            <label for="hero_subtitle">Sous-titre / Description de la Hero</label>
                            <textarea id="hero_subtitle" name="hero_subtitle" class="admin-input" style="height: 60px; min-height: 50px; resize: vertical;"><?= htmlspecialchars($page['hero_subtitle'] ?? '') ?></textarea>
                        </div>

                        <!-- Advanced Layout & Positioning Controls -->
                        <fieldset style="border: 1px dashed var(--border); padding: 14px; border-radius: 12px; display: flex; flex-direction: column; gap: 12px; margin-top: 5px;">
                            <legend style="padding: 0 8px; font-size: 0.8rem; font-weight: 700; color: var(--secondary);">Mise en Page & Positionnement Avancé</legend>
                            
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                                <div class="admin-form-group" style="margin-bottom: 0;">
                                    <label style="font-size: 0.75rem;">Hauteur / Format du Hero</label>
                                    <select id="hero_layout_mode" name="hero_layout_mode" class="admin-select visual-simulator-trigger">
                                        <option value="grand" <?= ($page['hero_layout_mode'] ?? 'moyen') === 'grand' ? 'selected' : '' ?>>Grand format (Large padding)</option>
                                        <option value="moyen" <?= ($page['hero_layout_mode'] ?? 'moyen') === 'moyen' ? 'selected' : '' ?>>Moyen format (Standard)</option>
                                        <option value="compact" <?= ($page['hero_layout_mode'] ?? 'moyen') === 'compact' ? 'selected' : '' ?>>Format compact</option>
                                        <option value="plein" <?= ($page['hero_layout_mode'] ?? 'moyen') === 'plein' ? 'selected' : '' ?>>Plein écran (100% de l'écran)</option>
                                    </select>
                                </div>

                                <div class="admin-form-group" style="margin-bottom: 0;">
                                    <label style="font-size: 0.75rem;">Position Verticale du Texte</label>
                                    <select id="hero_text_position" name="hero_text_position" class="admin-select visual-simulator-trigger">
                                        <option value="haut" <?= ($page['hero_text_position'] ?? 'centre') === 'haut' ? 'selected' : '' ?>>Haut</option>
                                        <option value="centre" <?= ($page['hero_text_position'] ?? 'centre') === 'centre' ? 'selected' : '' ?>>Milieu / Centré</option>
                                        <option value="bas" <?= ($page['hero_text_position'] ?? 'centre') === 'bas' ? 'selected' : '' ?>>Bas</option>
                                    </select>
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                                <div class="admin-form-group" style="margin-bottom: 0;">
                                    <label style="font-size: 0.75rem;">Alignement Horizontal Texte</label>
                                    <select id="hero_text_alignment" name="hero_text_alignment" class="admin-select visual-simulator-trigger">
                                        <option value="left" <?= ($page['hero_text_alignment'] ?? 'center') === 'left' ? 'selected' : '' ?>>Gauche</option>
                                        <option value="center" <?= ($page['hero_text_alignment'] ?? 'center') === 'center' ? 'selected' : '' ?>>Centré</option>
                                        <option value="right" <?= ($page['hero_text_alignment'] ?? 'center') === 'right' ? 'selected' : '' ?>>Droite</option>
                                    </select>
                                </div>

                                <div class="admin-form-group" style="margin-bottom: 0;">
                                    <label style="font-size: 0.75rem;">Largeur Max du Texte</label>
                                    <input type="text" id="hero_text_width" name="hero_text_width" class="admin-input visual-simulator-trigger" value="<?= htmlspecialchars($page['hero_text_width'] ?? '100%') ?>" placeholder="Ex: 600px, 80%, 100%">
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                                <div class="admin-form-group" style="margin-bottom: 0;">
                                    <label style="font-size: 0.75rem;">Niveau d'Ombre Texte</label>
                                    <select id="hero_shadow_strength" name="hero_shadow_strength" class="admin-select visual-simulator-trigger">
                                        <option value="aucun" <?= ($page['hero_shadow_strength'] ?? 'moyen') === 'aucun' ? 'selected' : '' ?>>Aucune</option>
                                        <option value="leger" <?= ($page['hero_shadow_strength'] ?? 'moyen') === 'leger' ? 'selected' : '' ?>>Légère</option>
                                        <option value="moyen" <?= ($page['hero_shadow_strength'] ?? 'moyen') === 'moyen' ? 'selected' : '' ?>>Moyenne</option>
                                        <option value="fort" <?= ($page['hero_shadow_strength'] ?? 'moyen') === 'fort' ? 'selected' : '' ?>>Forte</option>
                                    </select>
                                </div>

                                <div class="admin-form-group" style="margin-bottom: 0;">
                                    <label style="font-size: 0.75rem;">Opacité Voile Overlay (<span id="hero_overlay_opacity_val">0.45</span>)</label>
                                    <input type="range" id="hero_overlay_opacity" name="hero_overlay_opacity" min="0.0" max="1.0" step="0.05" class="admin-input visual-simulator-trigger" value="<?= isset($page['hero_overlay_opacity']) ? (float)$page['hero_overlay_opacity'] : 0.45 ?>">
                                </div>

                                <div class="admin-form-group" style="margin-bottom: 0;">
                                    <label style="font-size: 0.75rem;">Taille du Titre Hero</label>
                                    <select id="hero_title_size" name="hero_title_size" class="admin-select">
                                        <option value="small"   <?= ($page['hero_title_size'] ?? 'large') === 'small'   ? 'selected' : '' ?>>Petit   — ~2.5rem</option>
                                        <option value="medium"  <?= ($page['hero_title_size'] ?? 'large') === 'medium'  ? 'selected' : '' ?>>Moyen   — ~3.2rem</option>
                                        <option value="large"   <?= ($page['hero_title_size'] ?? 'large') === 'large'   ? 'selected' : '' ?>>Grand   — ~4.5rem</option>
                                        <option value="xlarge"  <?= ($page['hero_title_size'] ?? 'large') === 'xlarge'  ? 'selected' : '' ?>>X-Large — ~6rem</option>
                                        <option value="xxlarge" <?= ($page['hero_title_size'] ?? 'large') === 'xxlarge' ? 'selected' : '' ?>>XX-Large — ~8rem</option>
                                    </select>
                                </div>
                            </div>

                            <div class="admin-form-group" style="margin-bottom: 0;">
                                <label style="font-size: 0.75rem;">Image Mobile Spécifique (Responsive)</label>
                                <?= \App\Helpers\MediaHelper::renderField('hero_image_mobile', $page['hero_image_mobile'] ?? '', 'hero_image_mobile') ?>
                            </div>
                        </fieldset>

                        <!-- CTAs Grid -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 4px;">
                            <fieldset style="border: 1px solid var(--border); padding: 12px; border-radius: 12px; display: flex; flex-direction: column; gap: 8px;">
                                <legend style="padding: 0 8px; font-size: 0.8rem; font-weight: 700; color: var(--accent);">Bouton CTA 1</legend>
                                <div class="admin-form-group" style="margin-bottom: 0;">
                                    <label style="font-size: 0.7rem;">Texte</label>
                                    <input type="text" id="hero_cta1_text" name="hero_cta1_text" class="admin-input" value="<?= htmlspecialchars($page['hero_cta1_text'] ?? '') ?>" placeholder="Ex: Nous contacter">
                                </div>
                                <div class="admin-form-group" style="margin-bottom: 0;">
                                    <label style="font-size: 0.7rem;">URL</label>
                                    <input type="text" name="hero_cta1_url" class="admin-input" value="<?= htmlspecialchars($page['hero_cta1_url'] ?? '') ?>" placeholder="Ex: #contact">
                                </div>
                            </fieldset>

                            <fieldset style="border: 1px solid var(--border); padding: 12px; border-radius: 12px; display: flex; flex-direction: column; gap: 8px;">
                                <legend style="padding: 0 8px; font-size: 0.8rem; font-weight: 700; color: var(--primary);">Bouton CTA 2</legend>
                                <div class="admin-form-group" style="margin-bottom: 0;">
                                    <label style="font-size: 0.7rem;">Texte</label>
                                    <input type="text" id="hero_cta2_text" name="hero_cta2_text" class="admin-input" value="<?= htmlspecialchars($page['hero_cta2_text'] ?? '') ?>" placeholder="Ex: En savoir plus">
                                </div>
                                <div class="admin-form-group" style="margin-bottom: 0;">
                                    <label style="font-size: 0.7rem;">URL</label>
                                    <input type="text" name="hero_cta2_url" class="admin-input" value="<?= htmlspecialchars($page['hero_cta2_url'] ?? '') ?>" placeholder="Ex: /a-propos">
                                </div>
                            </fieldset>
                        </div>
                    </div>

                    <!-- Hero Right Style Fields & Media -->
                    <div style="display: flex; flex-direction: column; gap: 14px; background-color: rgba(255,255,255,0.25); border: 1px solid var(--border); padding: 16px; border-radius: 14px;">
                        
                        <div class="admin-form-group">
                            <label for="hero_status">Statut de la Hero Section</label>
                            <select id="hero_status" name="hero_status" class="admin-select" style="font-weight: 600;">
                                <option value="1" <?= (int)($page['hero_status'] ?? 1) === 1 ? 'selected' : '' ?>>✓ Activée (Affichée)</option>
                                <option value="0" <?= (int)($page['hero_status'] ?? 1) === 0 ? 'selected' : '' ?>>✗ Désactivée (Masquée)</option>
                            </select>
                        </div>

                        <div class="admin-form-group">
                            <label for="hero_variant">Style principal (Hero Variant)</label>
                            <select id="hero_variant" name="hero_variant" class="admin-select visual-simulator-trigger" onchange="toggleSlideshowManager(this.value)">
                                <option value="hero_slider" <?= ($page['hero_variant'] ?? 'hero_split') === 'hero_slider' ? 'selected' : '' ?>>Slider Dynamique (Diaporama)</option>
                                <option value="hero_split" <?= ($page['hero_variant'] ?? 'hero_split') === 'hero_split' ? 'selected' : '' ?>>Séparé - Side-by-Side (Splitté)</option>
                                <option value="hero_full_width" <?= ($page['hero_variant'] ?? 'hero_split') === 'hero_full_width' ? 'selected' : '' ?>>Pleine Page - Cover Immersive</option>
                                <option value="hero_card" <?= ($page['hero_variant'] ?? 'hero_split') === 'hero_card' ? 'selected' : '' ?>>Flottant - Carte Verre Premium</option>
                                <option value="hero_video" <?= ($page['hero_variant'] ?? 'hero_split') === 'hero_video' ? 'selected' : '' ?>>Vidéo - Arrière-plan Interactif</option>
                                <option value="hero_corporate" <?= ($page['hero_variant'] ?? 'hero_split') === 'hero_corporate' ? 'selected' : '' ?>>Corporate - Épuré Professionnel</option>
                                <option value="hero_magazine" <?= ($page['hero_variant'] ?? 'hero_split') === 'hero_magazine' ? 'selected' : '' ?>>Magazine - Offset & Badge</option>
                                <option value="hero_split_large_image" <?= ($page['hero_variant'] ?? 'hero_split') === 'hero_split_large_image' ? 'selected' : '' ?>>Legacy: Grand Visuel (Mockup)</option>
                                <option value="hero_split_small_image" <?= ($page['hero_variant'] ?? 'hero_split') === 'hero_split_small_image' ? 'selected' : '' ?>>Legacy: Petit Visuel (Compact)</option>
                                <option value="hero_text_only" <?= ($page['hero_variant'] ?? 'hero_split') === 'hero_text_only' ? 'selected' : '' ?>>Legacy: Texte Uniquement</option>
                                <option value="hero_ambient_glow" <?= ($page['hero_variant'] ?? 'hero_split') === 'hero_ambient_glow' ? 'selected' : '' ?>>Legacy: Halo Lumineux Centré</option>
                            </select>
                        </div>

                        <div class="admin-form-group">
                            <label for="hero_image_layout">Mise en page & Alignement</label>
                            <select id="hero_image_layout" name="hero_image_layout" class="admin-select visual-simulator-trigger">
                                <option value="right" <?= ($page['hero_image_layout'] ?? 'right') === 'right' ? 'selected' : '' ?>>Visuel à droite / Texte à gauche</option>
                                <option value="left" <?= ($page['hero_image_layout'] ?? 'right') === 'left' ? 'selected' : '' ?>>Visuel à gauche / Texte à droite</option>
                                <option value="floating" <?= ($page['hero_image_layout'] ?? 'right') === 'floating' ? 'selected' : '' ?>>Carte flottante premium (Lévitation)</option>
                            </select>
                        </div>

                        <div class="admin-form-group">
                            <label for="hero_image_size">Dimension du visuel</label>
                            <select id="hero_image_size" name="hero_image_size" class="admin-select visual-simulator-trigger">
                                <option value="large" <?= ($page['hero_image_size'] ?? 'large') === 'large' ? 'selected' : '' ?>>Grand format (100%)</option>
                                <option value="medium" <?= ($page['hero_image_size'] ?? 'large') === 'medium' ? 'selected' : '' ?>>Moyen format (80%)</option>
                                <option value="small" <?= ($page['hero_image_size'] ?? 'large') === 'small' ? 'selected' : '' ?>>Petit format (60%)</option>
                            </select>
                        </div>

                        <div class="admin-form-group">
                            <label for="hero_badge">Badge / Sur-titre de la Hero</label>
                            <input type="text" id="hero_badge" name="hero_badge" class="admin-input" value="<?= htmlspecialchars($page['hero_badge'] ?? '') ?>" placeholder="Ex: Innovation & IA">
                        </div>

                        <div class="admin-form-group">
                            <label>Illustration principale</label>
                            <?= \App\Helpers\MediaHelper::renderField('hero_image', $page['hero_image'] ?? '', 'hero_image') ?>
                        </div>

                        <div class="admin-form-group">
                            <label for="hero_bg_color">Couleur / Dégradé de fond</label>
                            <input type="text" id="hero_bg_color" name="hero_bg_color" class="admin-input" value="<?= htmlspecialchars($page['hero_bg_color'] ?? 'linear-gradient(135deg, #f8fafc 0%, #f1f5f9 50%, #e2e8f0 100%)') ?>">
                        </div>

                        <div class="admin-form-group">
                            <label for="hero_effect">Effet visuel arrière-plan</label>
                            <select id="hero_effect" name="hero_effect" class="admin-select">
                                <option value="particles" <?= ($page['hero_effect'] ?? 'particles') === 'particles' ? 'selected' : '' ?>>Réseau de particules (Recommandé)</option>
                                <option value="none" <?= ($page['hero_effect'] ?? 'particles') === 'none' ? 'selected' : '' ?>>Aucun</option>
                            </select>
                        </div>

                        <!-- Visual adjustments (Filters) -->
                        <fieldset style="border: 1px dashed var(--border); padding: 12px; border-radius: 12px; display: flex; flex-direction: column; gap: 8px;">
                            <legend style="padding: 0 6px; font-size: 0.75rem; font-weight: 700; color: var(--primary);">Finition & Effets Image</legend>
                            
                            <?php
                            $brightnessVal = $visualSettings['brightness'] ?? 1.0;
                            $saturationVal = $visualSettings['saturation'] ?? 1.0;
                            $blurVal = $visualSettings['blur'] ?? 0;
                            ?>

                            <div class="admin-form-group" style="margin-bottom: 0;">
                                <label style="font-size: 0.7rem; margin-bottom: 2px;">Luminosité (<span id="visual_brightness_val"><?= $brightnessVal ?></span>)</label>
                                <input type="range" id="visual_brightness" min="0.2" max="2.0" step="0.05" class="admin-input visual-simulator-trigger" value="<?= $brightnessVal ?>">
                            </div>

                            <div class="admin-form-group" style="margin-bottom: 0;">
                                <label style="font-size: 0.7rem; margin-bottom: 2px;">Saturation (<span id="visual_saturation_val"><?= $saturationVal ?></span>)</label>
                                <input type="range" id="visual_saturation" min="0.0" max="2.0" step="0.05" class="admin-input visual-simulator-trigger" value="<?= $saturationVal ?>">
                            </div>

                            <div class="admin-form-group" style="margin-bottom: 0;">
                                <label style="font-size: 0.7rem; margin-bottom: 2px;">Flou Image (<span id="visual_blur_val"><?= $blurVal ?>px</span>)</label>
                                <input type="range" id="visual_blur" min="0" max="20" step="1" class="admin-input visual-simulator-trigger" value="<?= $blurVal ?>">
                            </div>
                        </fieldset>

                        <!-- Breakpoint overrides configuration -->
                        <fieldset style="border: 1px dashed var(--border); padding: 12px; border-radius: 12px; display: flex; flex-direction: column; gap: 8px;">
                            <legend style="padding: 0 6px; font-size: 0.75rem; font-weight: 700; color: var(--secondary);">Variantes Mobile (Responsive)</legend>
                            
                            <?php
                            $mobileHeaderVisible = $respSettings['mobile']['header_visible'] ?? true;
                            $mobileLogoSize = $respSettings['mobile']['logo_size'] ?? '';
                            $mobileHeroTextPos = $respSettings['mobile']['hero_text_position'] ?? '';
                            $mobileHeroTextAlign = $respSettings['mobile']['hero_text_alignment'] ?? '';
                            ?>

                            <div style="display: flex; align-items: center; gap: 6px;">
                                <input type="checkbox" id="mobile_header_visible" class="visual-simulator-trigger" <?= $mobileHeaderVisible ? 'checked' : '' ?>>
                                <label for="mobile_header_visible" style="font-size: 0.7rem; margin-bottom:0; font-weight: 500;">Afficher Header sur mobile</label>
                            </div>

                            <div class="admin-form-group" style="margin-bottom: 0;">
                                <label style="font-size: 0.7rem; margin-bottom: 2px;">Logo Mobile (px)</label>
                                <input type="number" id="mobile_logo_size" class="admin-input visual-simulator-trigger" value="<?= htmlspecialchars($mobileLogoSize) ?>" placeholder="Défaut: 30">
                            </div>

                            <div class="admin-form-group" style="margin-bottom: 0;">
                                <label style="font-size: 0.7rem; margin-bottom: 2px;">Position Verticale Mobile</label>
                                <select id="mobile_hero_text_pos" class="admin-select visual-simulator-trigger">
                                    <option value="" <?= $mobileHeroTextPos === '' ? 'selected' : '' ?>>Identique</option>
                                    <option value="haut" <?= $mobileHeroTextPos === 'haut' ? 'selected' : '' ?>>Haut</option>
                                    <option value="centre" <?= $mobileHeroTextPos === 'centre' ? 'selected' : '' ?>>Milieu</option>
                                    <option value="bas" <?= $mobileHeroTextPos === 'bas' ? 'selected' : '' ?>>Bas</option>
                                </select>
                            </div>

                            <div class="admin-form-group" style="margin-bottom: 0;">
                                <label style="font-size: 0.7rem; margin-bottom: 2px;">Alignement Texte Mobile</label>
                                <select id="mobile_hero_text_align" class="admin-select visual-simulator-trigger">
                                    <option value="" <?= $mobileHeroTextAlign === '' ? 'selected' : '' ?>>Identique</option>
                                    <option value="left" <?= $mobileHeroTextAlign === 'left' ? 'selected' : '' ?>>Gauche</option>
                                    <option value="center" <?= $mobileHeroTextAlign === 'center' ? 'selected' : '' ?>>Centré</option>
                                    <option value="right" <?= $mobileHeroTextAlign === 'right' ? 'selected' : '' ?>>Droite</option>
                                </select>
                            </div>
                        </fieldset>

            </div>

            <!-- Part 2.5: Slideshow Slides Manager (Dynamic Slider CRUD) -->
            <div id="slides_manager_container" style="border-top: 1px dashed var(--border); padding-top: 20px; margin-top: 10px; <?= ($page['hero_variant'] ?? '') === 'hero_slider' ? 'display: block;' : 'display: none;' ?>">
                <h4 style="font-family: var(--font-heading); font-size: 1.05rem; color: var(--primary); display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                    <span style="display: flex; align-items: center; gap: 8px;">
                        <i data-lucide="images" style="width: 18px; height: 18px; color: var(--secondary);"></i>
                        <span>Gestionnaire de Slides (Hero Slider)</span>
                    </span>
                    <button type="button" class="btn-secondary" style="padding: 6px 12px; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 4px;" onclick="addHeroSlide(<?= $page['id'] ?>)">
                        <i data-lucide="plus" style="width: 14px; height: 14px;"></i> Ajouter un Slide
                    </button>
                </h4>

                <div id="slides_list" style="display: flex; flex-direction: column; gap: 16px; margin-bottom: 20px;">
                    <?php if (empty($slides)): ?>
                        <p style="text-align: center; color: var(--text-muted); font-size: 0.85rem; padding: 20px;" id="no_slides_text">Aucun slide enregistré. Cliquez sur "Ajouter un Slide" pour commencer.</p>
                    <?php else: ?>
                        <?php foreach ($slides as $slide): ?>
                            <div class="item-card slide-card" id="slide-card-<?= $slide['id'] ?>" data-slide-id="<?= $slide['id'] ?>">
                                <div class="item-card-header">
                                    <span class="item-card-title">Slide #<?= $slide['id'] ?></span>
                                    <button type="button" class="action-btn-small delete-sec" onclick="deleteHeroSlide(<?= $slide['id'] ?>)">
                                        <i data-lucide="trash-2" style="width: 16px; height: 16px;"></i>
                                    </button>
                                </div>
                                <div style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 16px;">
                                    <div style="display: flex; flex-direction: column; gap: 10px;">
                                        <div class="admin-form-group" style="margin-bottom: 0;">
                                            <label style="font-size: 0.75rem; margin-bottom: 4px;">Badge</label>
                                            <input type="text" name="slides[<?= $slide['id'] ?>][badge]" class="admin-input slide-input-trigger" value="<?= htmlspecialchars($slide['badge'] ?? '') ?>" placeholder="Ex: Nouveau">
                                        </div>
                                        <div class="admin-form-group" style="margin-bottom: 0;">
                                            <label style="font-size: 0.75rem; margin-bottom: 4px;">Titre principal</label>
                                            <input type="text" name="slides[<?= $slide['id'] ?>][title]" class="admin-input slide-input-trigger" value="<?= htmlspecialchars($slide['title']) ?>" placeholder="Ex: Titre du Slide" required>
                                        </div>
                                        <div class="admin-form-group" style="margin-bottom: 0;">
                                            <label style="font-size: 0.75rem; margin-bottom: 4px;">Description / Texte</label>
                                            <textarea name="slides[<?= $slide['id'] ?>][subtitle]" class="admin-textarea slide-input-trigger" rows="2" placeholder="Description courte du slide."><?= htmlspecialchars($slide['subtitle'] ?? '') ?></textarea>
                                        </div>
                                    </div>
                                    <div style="display: flex; flex-direction: column; gap: 10px;">
                                        <div class="admin-form-group" style="margin-bottom: 0;">
                                            <label style="font-size: 0.75rem; margin-bottom: 4px;">Illustration (Image)</label>
                                            <?= \App\Helpers\MediaHelper::renderField("slides[{$slide['id']}][image]", $slide['image'] ?? '', "slide_img_{$slide['id']}") ?>
                                        </div>
                                        <div style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 8px;">
                                            <div class="admin-form-group" style="margin-bottom: 0;">
                                                <label style="font-size: 0.75rem; margin-bottom: 4px;">CTA Texte</label>
                                                <input type="text" name="slides[<?= $slide['id'] ?>][cta_text]" class="admin-input slide-input-trigger" value="<?= htmlspecialchars($slide['cta_text'] ?? '') ?>" placeholder="CTA Texte">
                                            </div>
                                            <div class="admin-form-group" style="margin-bottom: 0;">
                                                <label style="font-size: 0.75rem; margin-bottom: 4px;">CTA URL</label>
                                                <input type="text" name="slides[<?= $slide['id'] ?>][cta_url]" class="admin-input slide-input-trigger" value="<?= htmlspecialchars($slide['cta_url'] ?? '') ?>" placeholder="CTA URL">
                                            </div>
                                        </div>
                                        <div class="admin-form-group" style="margin-bottom: 0;">
                                            <label style="font-size: 0.75rem; margin-bottom: 4px;">Ordre d'affichage</label>
                                            <input type="number" name="slides[<?= $slide['id'] ?>][sort_order]" class="admin-input slide-input-trigger" value="<?= (int)($slide['sort_order'] ?? 0) ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div style="display: flex; justify-content: flex-end; margin-bottom: 20px;">
                    <button type="button" class="btn-secondary" style="padding: 10px 22px; font-weight: 600; font-size: 0.88rem; display: inline-flex; align-items: center; gap: 8px;" onclick="saveSlidesContent()">
                        <i data-lucide="save" style="width: 16px; height: 16px;"></i>
                        <span>Sauvegarder l'ordre et le contenu des slides</span>
                    </button>
                </div>
            </div>

            <!-- Hero Features (pour variants: asymmetric, grid_features) -->
            <div style="border-top: 1px dashed var(--border); padding-top: 20px; margin-top: 4px;">
                <h4 style="font-family: var(--font-heading); font-size: 1rem; color: var(--primary); display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                    <i data-lucide="layout-grid" style="width: 16px; height: 16px;"></i>
                    Cartes de fonctionnalités <span style="font-size:0.72rem;color:var(--text-muted);font-weight:500;">(hero_split_asymmetric, hero_grid_features)</span>
                </h4>
                <p style="font-size: 0.78rem; color: var(--text-muted); margin-bottom: 14px;">Max 4 cartes. Icônes Lucide : zap, shield-check, cpu, cloud, globe, phone-call, etc.</p>
                <div id="hero-features-list" style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 12px;"></div>
                <button type="button" onclick="addHeroFeature()" style="padding: 8px 16px; font-size: 0.8rem; border-radius: 8px; border: 1px dashed var(--border); background: transparent; cursor: pointer; color: var(--primary); font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                    <i data-lucide="plus" style="width: 14px; height: 14px;"></i> Ajouter une carte
                </button>
                <input type="hidden" name="hero_features" id="hero_features_input" value="<?= htmlspecialchars($page['hero_features'] ?? '') ?>">
            </div>

            <!-- Hero Articles (pour variant: magazine) -->
            <div style="border-top: 1px dashed var(--border); padding-top: 20px; margin-top: 4px;">
                <h4 style="font-family: var(--font-heading); font-size: 1rem; color: var(--primary); display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                    <i data-lucide="newspaper" style="width: 16px; height: 16px;"></i>
                    Articles Magazine <span style="font-size:0.72rem;color:var(--text-muted);font-weight:500;">(hero_magazine)</span>
                </h4>
                <p style="font-size: 0.78rem; color: var(--text-muted); margin-bottom: 14px;">Max 3 articles visibles dans la sidebar du Hero Magazine.</p>
                <div id="hero-articles-list" style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 12px;"></div>
                <button type="button" onclick="addHeroArticle()" style="padding: 8px 16px; font-size: 0.8rem; border-radius: 8px; border: 1px dashed var(--border); background: transparent; cursor: pointer; color: var(--primary); font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                    <i data-lucide="plus" style="width: 14px; height: 14px;"></i> Ajouter un article
                </button>
                <input type="hidden" name="hero_articles" id="hero_articles_input" value="<?= htmlspecialchars($page['hero_articles'] ?? '') ?>">
            </div>

            <div style="display: flex; justify-content: flex-end; border-top: 1px solid var(--border); padding-top: 16px;">
                <button type="submit" class="btn-primary" style="padding: 12px 28px; font-weight: 700; font-size: 0.95rem; display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="save" style="width: 20px; height: 20px;"></i>
                    <span>Sauvegarder les paramètres & la Hero</span>
                </button>
            </div>
        </form>

        <script>
        // ── Hero Features Builder ──────────────────────────────────────────
        let heroFeatures = [];
        try { heroFeatures = JSON.parse(document.getElementById('hero_features_input').value || '[]'); } catch(e) { heroFeatures = []; }

        function renderHeroFeatures() {
            const container = document.getElementById('hero-features-list');
            container.innerHTML = '';
            heroFeatures.forEach((f, i) => {
                container.innerHTML += `
                <div style="background:rgba(255,255,255,0.45);border:1px solid var(--border);border-radius:10px;padding:14px 16px;display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:10px;align-items:end;">
                    <div><label style="font-size:0.72rem;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px;">Titre</label><input type="text" value="${escHtml(f.title||'')}" oninput="heroFeatures[${i}].title=this.value;syncHeroFeatures()" class="admin-input" style="height:34px;font-size:0.85rem;"></div>
                    <div><label style="font-size:0.72rem;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px;">Description</label><input type="text" value="${escHtml(f.desc||'')}" oninput="heroFeatures[${i}].desc=this.value;syncHeroFeatures()" class="admin-input" style="height:34px;font-size:0.85rem;"></div>
                    <div><label style="font-size:0.72rem;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px;">Icône Lucide</label><input type="text" value="${escHtml(f.icon||'zap')}" oninput="heroFeatures[${i}].icon=this.value;syncHeroFeatures()" class="admin-input" style="height:34px;font-size:0.85rem;" placeholder="zap, cpu, cloud…"></div>
                    <button type="button" onclick="removeHeroFeature(${i})" style="padding:6px 10px;background:none;border:1px solid var(--danger);border-radius:8px;cursor:pointer;color:var(--danger);font-size:0.8rem;height:34px;">✕</button>
                </div>`;
            });
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }
        function addHeroFeature() {
            if (heroFeatures.length >= 4) { alert('Maximum 4 cartes.'); return; }
            heroFeatures.push({icon:'zap',title:'Nouveau service',desc:'Description courte.',color:'rgba(37,99,235,0.1)',iconColor:'var(--primary)'});
            renderHeroFeatures(); syncHeroFeatures();
        }
        function removeHeroFeature(i) { heroFeatures.splice(i,1); renderHeroFeatures(); syncHeroFeatures(); }
        function syncHeroFeatures() { document.getElementById('hero_features_input').value = JSON.stringify(heroFeatures); }
        function escHtml(s) { return String(s).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
        renderHeroFeatures();

        // ── Hero Articles Builder ─────────────────────────────────────────
        let heroArticles = [];
        try { heroArticles = JSON.parse(document.getElementById('hero_articles_input').value || '[]'); } catch(e) { heroArticles = []; }

        function renderHeroArticles() {
            const container = document.getElementById('hero-articles-list');
            container.innerHTML = '';
            heroArticles.forEach((a, i) => {
                container.innerHTML += `
                <div style="background:rgba(255,255,255,0.45);border:1px solid var(--border);border-radius:10px;padding:14px 16px;display:grid;grid-template-columns:1fr 2fr 1fr auto;gap:10px;align-items:end;">
                    <div><label style="font-size:0.72rem;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px;">Catégorie</label><input type="text" value="${escHtml(a.category||'')}" oninput="heroArticles[${i}].category=this.value;syncHeroArticles()" class="admin-input" style="height:34px;font-size:0.85rem;" placeholder="TECHNOLOGIE"></div>
                    <div><label style="font-size:0.72rem;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px;">Titre de l'article</label><input type="text" value="${escHtml(a.title||'')}" oninput="heroArticles[${i}].title=this.value;syncHeroArticles()" class="admin-input" style="height:34px;font-size:0.85rem;"></div>
                    <div><label style="font-size:0.72rem;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px;">URL</label><input type="text" value="${escHtml(a.url||'/blog')}" oninput="heroArticles[${i}].url=this.value;syncHeroArticles()" class="admin-input" style="height:34px;font-size:0.85rem;"></div>
                    <button type="button" onclick="removeHeroArticle(${i})" style="padding:6px 10px;background:none;border:1px solid var(--danger);border-radius:8px;cursor:pointer;color:var(--danger);font-size:0.8rem;height:34px;">✕</button>
                </div>`;
            });
        }
        function addHeroArticle() {
            if (heroArticles.length >= 3) { alert('Maximum 3 articles.'); return; }
            heroArticles.push({category:'ACTUALITÉ',title:'Titre de l\'article',url:'/blog'});
            renderHeroArticles(); syncHeroArticles();
        }
        function removeHeroArticle(i) { heroArticles.splice(i,1); renderHeroArticles(); syncHeroArticles(); }
        function syncHeroArticles() { document.getElementById('hero_articles_input').value = JSON.stringify(heroArticles); }
        renderHeroArticles();
        </script>
    </div>

    <!-- Interactive Simulator Panel -->
    <div class="card visual-simulator-card" style="padding: 20px; position: sticky; top: 20px; display: flex; flex-direction: column; gap: 14px; border: 1px solid rgba(37, 99, 235, 0.15); background: rgba(255, 255, 255, 0.45); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); box-shadow: 0 15px 35px rgba(30,58,138,0.04);">
        <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--border); padding-bottom: 10px;">
            <h4 style="font-family: var(--font-heading); font-size: 0.9rem; color: var(--primary); display: flex; align-items: center; gap: 6px; margin: 0;">
                <i data-lucide="eye" style="width: 16px; height: 16px; color: var(--secondary);"></i>
                <span>Prévisualisation en Direct</span>
            </h4>
            <div style="display: flex; gap: 4px; background: rgba(0,0,0,0.05); padding: 3px; border-radius: 8px;">
                <button type="button" id="sim_btn_desktop" class="sim-btn active" style="border:none; background:white; padding: 4px 10px; border-radius: 6px; font-size:0.7rem; font-weight:700; cursor:pointer;" onclick="setSimulatorBreakpoint('desktop')">🖥️ Desktop</button>
                <button type="button" id="sim_btn_mobile" class="sim-btn" style="border:none; background:transparent; padding: 4px 10px; border-radius: 6px; font-size:0.7rem; font-weight:700; cursor:pointer;" onclick="setSimulatorBreakpoint('mobile')">📱 Mobile</button>
            </div>
        </div>

        <!-- Simulated browser viewport -->
        <div id="simulator_viewport" class="sim-viewport-desktop" style="width: 100%; border: 1px solid var(--border); border-radius: 12px; overflow: hidden; background: #f8fafc; transition: all 0.45s cubic-bezier(0.16, 1, 0.3, 1); box-shadow: 0 10px 25px rgba(0,0,0,0.05); aspect-ratio: 1.35; max-width: 100%;">
            <div style="width:100%; height: 20px; background: #e2e8f0; display:flex; align-items:center; gap: 4px; padding: 0 8px;">
                <span style="width: 6px; height: 6px; border-radius: 50%; background: #ef4444;"></span>
                <span style="width: 6px; height: 6px; border-radius: 50%; background: #eab308;"></span>
                <span style="width: 6px; height: 6px; border-radius: 50%; background: #22c55e;"></span>
                <span style="font-size: 0.55rem; color: #64748b; font-family: monospace; margin-left: 10px;">http://localhost/<?= htmlspecialchars($page['slug']) ?></span>
            </div>

            <div id="sim_container" style="position: relative; width: 100%; height: calc(100% - 20px); overflow-y: auto; overflow-x: hidden; background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 50%, #e2e8f0 100%);">
                
                <!-- Simulated ambient glows -->
                <div style="position: absolute; width: 100px; height: 100px; border-radius: 50%; background: radial-gradient(circle, rgba(37, 99, 235, 0.12) 0%, transparent 70%); top: -20px; right: -20px; pointer-events: none;"></div>
                <div style="position: absolute; width: 100px; height: 100px; border-radius: 50%; background: radial-gradient(circle, rgba(236, 72, 153, 0.06) 0%, transparent 70%); bottom: -20px; left: -20px; pointer-events: none;"></div>

                <!-- Simulated Header -->
                <div id="sim_header" style="position: sticky; top: 6px; margin: 0 8px; height: 32px; border-radius: 6px; display: flex; align-items: center; justify-content: space-between; padding: 0 10px; z-index: 10; transition: all 0.3s; font-size: 0.55rem; border: 1px solid rgba(255,255,255,0.6); background: rgba(255,255,255,0.45); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); cursor: pointer;" onclick="focusPageConfig('header')">
                    <!-- Logo -->
                    <div style="display:flex; align-items:center; gap: 4px;">
                        <img id="sim_header_logo" src="<?= htmlspecialchars($selectedLogo) ?>" style="height: 14px; width: auto; object-fit: contain;">
                        <strong id="sim_header_logo_text" style="font-size:0.55rem; font-family:var(--font-heading); color: var(--primary);"><?= htmlspecialchars($settings['site_logo_text'] ?? 'Digitalium') ?></strong>
                    </div>
                    <!-- Nav -->
                    <div id="sim_header_nav" style="display:flex; gap: 8px; font-weight:700; color: var(--text-muted);">
                        <span style="border-bottom: 1px solid var(--primary); color: var(--primary);">Accueil</span>
                        <span>Services</span>
                        <span>Contact</span>
                    </div>
                    <!-- Button -->
                    <div id="sim_header_cta" style="padding: 2px 6px; border-radius: 50px; background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); color: white; font-weight:700; font-size: 0.4rem;">CTA</div>
                </div>

                <!-- Simulated Hero Banner -->
                <div id="sim_hero" style="width: 100%; min-height: 120px; padding: 24px 12px; display: flex; align-items: center; justify-content: center; position: relative; cursor: pointer;" onclick="focusPageConfig('hero')">
                    
                    <!-- Background image or color of Hero -->
                    <div id="sim_hero_bg" style="position: absolute; top:0; left:0; width:100%; height:100%; z-index:1; background-size: cover; background-position: center; transition: all 0.3s;"></div>
                    <div id="sim_hero_overlay" style="position: absolute; top:0; left:0; width:100%; height:100%; z-index:2; background: rgba(0,0,0,0.4); transition: all 0.3s;"></div>

                    <!-- Content Grid -->
                    <div id="sim_hero_grid" style="position: relative; z-index: 3; width: 100%; display: grid; grid-template-columns: 1.1fr 0.9fr; gap: 12px; align-items: center; height: 100%;">
                        
                        <!-- Text Block -->
                        <div id="sim_hero_text_block" style="display:flex; flex-direction: column; gap: 2px; transition: all 0.3s;">
                            <span id="sim_hero_badge" style="display:inline-flex; align-self: flex-start; padding: 1px 5px; border-radius: 20px; background: rgba(37,99,235,0.1); color: var(--secondary); font-size: 0.42rem; font-weight: 700; border: 1px solid rgba(37,99,235,0.2);">BADGE</span>
                            <h2 id="sim_hero_title" style="font-size: 0.85rem; line-height: 1.1; font-weight: 800; font-family: var(--font-heading); margin: 0; color: var(--text-main);">Titre principal</h2>
                            <p id="sim_hero_subtitle" style="font-size: 0.5rem; color: var(--text-muted); margin: 0;">Description de la Hero section.</p>
                            <div id="sim_hero_ctas" style="display:flex; gap: 4px; margin-top: 3px;">
                                <span id="sim_hero_cta1_btn" style="padding: 2px 6px; border-radius: 4px; background: #e26d36; color: white; font-size: 0.42rem; font-weight:700;">CTA 1</span>
                                <span id="sim_hero_cta2_btn" style="padding: 2px 6px; border-radius: 4px; background: rgba(255,255,255,0.7); color: #000; font-size: 0.42rem; font-weight:700; border: 1px solid var(--border);">CTA 2</span>
                            </div>
                        </div>

                        <!-- Visual Column -->
                        <div id="sim_hero_visual" style="display:flex; justify-content: center; align-items: center; z-index:3; transition: all 0.3s;">
                            <div id="sim_hero_mockup" style="width: 80%; aspect-ratio: 1.4; border-radius: 6px; background: #0f172a; padding: 3px; border: 1px solid #334155; box-shadow: 0 4px 8px rgba(0,0,0,0.15);">
                                <div id="sim_hero_mockup_screen" style="width:100%; height:100%; background: #1e293b; border-radius: 4px; background-size: cover; background-position: center;"></div>
                            </div>
                        </div>

                </div>

                <!-- Simulated Sections Previews -->
                <div id="sim_sections_container" style="display: flex; flex-direction: column; gap: 8px; padding: 12px; position: relative; z-index: 5;">
                    <?php if (!empty($sections)): ?>
                        <?php foreach ($sections as $sec): 
                            if ($sec['type'] === 'hero' || $sec['type'] === 'about_hero' || $sec['type'] === 'services_hero' || $sec['type'] === 'blog_hero' || $sec['type'] === 'contact_hero') continue;
                        ?>
                            <div class="sim-section-box" data-id="<?= $sec['id'] ?>" style="background: rgba(255, 255, 255, 0.45); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.6); border-radius: 10px; padding: 12px 14px; cursor: pointer; transition: all 0.3s ease-in-out; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.02);" onclick="switchSection(<?= $sec['id'] ?>)">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 26px; height: 26px; border-radius: 6px; background: rgba(37, 99, 235, 0.08); display: flex; align-items: center; justify-content: center; color: var(--primary);">
                                        <i data-lucide="layout" style="width: 14px; height: 14px;"></i>
                                    </div>
                                    <div style="display: flex; flex-direction: column; gap: 2px;">
                                        <span style="font-size: 0.6rem; font-weight: 700; color: var(--text-main);"><?= htmlspecialchars($sec['name']) ?></span>
                                        <span style="font-size: 0.45rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600;"><?= $sec['type'] ?></span>
                                    </div>
                                </div>
                                <div style="width: 16px; height: 16px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: rgba(0,0,0,0.03); color: var(--text-muted);">
                                    <i data-lucide="chevron-right" style="width: 8px; height: 8px;"></i>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
</div>

<div class="builder-container">
    
    <div class="sections-sidebar">
        <div class="sidebar-header">
            <span class="sidebar-title">Sections de la page</span>
            <button class="action-btn-small" onclick="openAddSectionModal()" title="Ajouter une section">
                <i data-lucide="plus-circle" style="width: 18px; height: 18px; color: var(--primary);"></i>
            </button>
        </div>

        <div class="section-list" id="sectionList" ondragover="dragOver(event)">
            <?php if (empty($sections)): ?>
                <p style="text-align: center; color: var(--text-muted); font-size: 0.85rem; padding: 24px 10px;" id="noSectionsText">Aucune section.</p>
            <?php else: ?>
                <?php foreach ($sections as $index => $sec): ?>
                    <div class="section-nav-item <?= $index === 0 ? 'active' : '' ?>" 
                         data-id="<?= $sec['id'] ?>" 
                         draggable="true" 
                         ondragstart="dragStart(event)" 
                         ondragend="dragEnd(event)" 
                         onclick="switchSection(<?= $sec['id'] ?>)">
                        
                        <div class="section-meta-left">
                            <i data-lucide="grip-vertical" class="section-icon" style="cursor: grab; width: 16px; height: 16px;"></i>
                            <span class="section-name-text" id="nav-text-<?= $sec['id'] ?>"><?= htmlspecialchars($sec['name']) ?></span>
                        </div>
                        
                        <div class="section-actions">
                            <button class="action-btn-small" onclick="renameSection(<?= $sec['id'] ?>, event)">
                                <i data-lucide="edit-2" style="width: 14px; height: 14px;"></i>
                            </button>
                            <button class="action-btn-small delete-sec" onclick="deleteSection(<?= $sec['id'] ?>, event)">
                                <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="editor-workspace">
        <?php if (empty($sections)): ?>
            <div class="card" style="text-align: center; padding: 60px 20px; color: var(--text-muted);">
                <i data-lucide="layout-template" style="width: 48px; height: 48px; stroke-width: 1.5; margin-bottom: 16px;"></i>
                <h3>Votre page est vide</h3>
                <button class="btn-primary" onclick="openAddSectionModal()">Ajouter ma première section</button>
            </div>
        <?php else: ?>
            <?php foreach ($sections as $index => $sec): 
                $blocks = $sectionBlocks[$sec['id']] ?? ['single' => [], 'groups' => []];
            ?>
                <div class="section-form-panel <?= $index === 0 ? 'active' : '' ?>" id="panel-<?= $sec['id'] ?>">
                    <div class="card">
                        <div class="card-header" style="margin-bottom: 24px;">
                            <h2 class="card-title">
                                <i data-lucide="layout"></i>
                                <span id="panel-title-<?= $sec['id'] ?>"><?= htmlspecialchars($sec['name']) ?></span>
                                <span style="font-size: 0.75rem; background-color: rgba(37,99,235,0.15); color: #93c5fd; padding: 2px 8px; border-radius: 4px; text-transform: uppercase; font-weight: 600;"><?= $sec['type'] ?></span>
                            </h2>
                            <button class="btn-primary" onclick="saveSectionContent(<?= $sec['id'] ?>)">
                                <i data-lucide="save"></i>
                                <span>Sauvegarder les blocs</span>
                            </button>
                        </div>

                        <form id="form-<?= $sec['id'] ?>" onsubmit="event.preventDefault()">
                            
                            <?php foreach ($blocks['single'] as $blockKey => $blockValue): 
                                $label = ucfirst(str_replace('_', ' ', $blockKey));
                                $fieldType = 'text';
                                if (in_array($blockKey, ['subtitle', 'content', 'description', 'contact_address'])) {
                                    $fieldType = 'textarea';
                                }
                                if ($blockKey === 'title' && $sec['type'] === 'hero') {
                                    $fieldType = 'wysiwyg';
                                }
                                if (str_contains($blockKey, 'image') || str_contains($blockKey, 'logo') || str_contains($blockKey, 'avatar')) {
                                    $fieldType = 'image';
                                }
                                if (str_contains($blockKey, 'url') || str_contains($blockKey, 'link')) {
                                    $fieldType = 'link';
                                }
                            ?>
                                <div class="admin-form-group">
                                    <label><?= $label ?></label>
                                    
                                    <?php if ($fieldType === 'wysiwyg'): ?>
                                        <div id="quill-editor-<?= $sec['id'] ?>-<?= $blockKey ?>" style="height: 180px; margin-bottom: 12px; background-color: var(--bg-base); border-color: var(--border);"></div>
                                        <input type="hidden" name="blocks[<?= $blockKey ?>][value]" id="quill-input-<?= $sec['id'] ?>-<?= $blockKey ?>" value="<?= htmlspecialchars($blockValue) ?>">
                                        <input type="hidden" name="blocks[<?= $blockKey ?>][type]" value="wysiwyg">
                                        <script>
                                            document.addEventListener("DOMContentLoaded", function() {
                                                const quill = new Quill('#quill-editor-<?= $sec['id'] ?>-<?= $blockKey ?>', {
                                                    theme: 'snow',
                                                    modules: {
                                                        toolbar: [
                                                            ['bold', 'italic', 'underline'],
                                                            [{ 'header': [1, 2, 3, false] }],
                                                            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                                                            ['clean']
                                                        ]
                                                    }
                                                });
                                                quill.root.innerHTML = `<?= $blockValue ?>`;
                                                quill.on('text-change', function() {
                                                    document.getElementById('quill-input-<?= $sec['id'] ?>-<?= $blockKey ?>').value = quill.root.innerHTML;
                                                });
                                            });
                                        </script>

                                    <?php elseif ($fieldType === 'textarea'): ?>
                                        <textarea name="blocks[<?= $blockKey ?>][value]" class="admin-textarea" rows="4"><?= htmlspecialchars($blockValue) ?></textarea>
                                        <input type="hidden" name="blocks[<?= $blockKey ?>][type]" value="textarea">

                                    <?php elseif ($fieldType === 'image'): ?>
                                        <?= \App\Helpers\MediaHelper::renderField("blocks[{$blockKey}][value]", $blockValue, "{$sec['id']}_{$blockKey}") ?>
                                        <input type="hidden" name="blocks[<?= $blockKey ?>][type]" value="image">

                                    <?php else: ?>
                                        <input type="text" name="blocks[<?= $blockKey ?>][value]" class="admin-input" value="<?= htmlspecialchars($blockValue) ?>">
                                        <input type="hidden" name="blocks[<?= $blockKey ?>][type]" value="<?= $fieldType ?>">
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>

                            <?php if (!empty($blocks['groups'])): ?>
                                <div class="repeatable-deck">
                                    <div class="deck-title">
                                        <span>Éléments de liste (Répétables)</span>
                                        <button type="button" class="btn-secondary" style="padding: 6px 12px; font-size: 0.8rem;" onclick="addRepeatableGroup(<?= $sec['id'] ?>, '<?= $sec['type'] ?>')">Ajouter un élément</button>
                                    </div>

                                    <div class="cards-grid" id="deck-<?= $sec['id'] ?>">
                                        <?php foreach ($blocks['groups'] as $groupId => $groupFields): 
                                            $sortOrder = $groupFields['_sort_order'] ?? 0;
                                            $uniqueGroupId = $groupFields['_group_id'];
                                        ?>
                                            <div class="item-card" id="card-<?= $sec['id'] ?>-<?= $uniqueGroupId ?>">
                                                <div class="item-card-header">
                                                    <span class="item-card-title">Élément #<?= $uniqueGroupId ?></span>
                                                    <button type="button" class="action-btn-small delete-sec" onclick="deleteRepeatableGroup(<?= $sec['id'] ?>, <?= $uniqueGroupId ?>)">
                                                        <i data-lucide="trash-2" style="width: 16px; height: 16px;"></i>
                                                    </button>
                                                </div>

                                                <input type="hidden" name="blocks[card_metadata_<?= $uniqueGroupId ?>][group_id]" value="<?= $uniqueGroupId ?>">
                                                
                                                <div style="display: grid; grid-template-columns: 1fr; gap: 12px;">
                                                    <?php foreach ($groupFields as $key => $val): 
                                                        if ($key === '_group_id' || $key === '_sort_order') continue;
                                                        $fieldLabel = ucfirst(str_replace(['card_', 'item_', 'member_', 'client_', 'faq_', 'post_'], '', $key));
                                                        $type = 'text';
                                                        if (in_array($key, ['card_description', 'client_quote', 'faq_answer', 'post_summary'])) {
                                                            $type = 'textarea';
                                                        }
                                                        if (str_contains($key, 'image') || str_contains($key, 'avatar')) {
                                                            $type = 'image';
                                                        }
                                                        if (str_contains($key, 'url') || str_contains($key, 'link')) {
                                                            $type = 'link';
                                                        }
                                                        if (str_contains($key, 'rating')) {
                                                            $type = 'number';
                                                        }
                                                    ?>
                                                        <div class="admin-form-group" style="margin-bottom: 0;">
                                                            <label style="font-size: 0.8rem; margin-bottom: 4px;"><?= $fieldLabel ?></label>
                                                            <?php if (str_contains($key, 'icon') || str_contains($key, 'avatar')): ?>
                                                                <small style="font-size: 0.72rem; color: var(--text-muted); display: block; margin-bottom: 4px;">Saisir un nom Lucide (ex: <code>cpu</code>), FontAwesome (ex: <code>fa-solid fa-code</code>), code SVG, ou un chemin d'image.</small>
                                                            <?php endif; ?>
                                                            
                                                            <?php if ($type === 'textarea'): ?>
                                                                <textarea name="blocks[<?= $key ?>_<?= $uniqueGroupId ?>][value]" class="admin-textarea" rows="3"><?= htmlspecialchars($val) ?></textarea>
                                                                <input type="hidden" name="blocks[<?= $key ?>_<?= $uniqueGroupId ?>][type]" value="textarea">

                                                            <?php elseif ($type === 'image'): ?>
                                                                <?= \App\Helpers\MediaHelper::renderField("blocks[{$key}_{$uniqueGroupId}][value]", $val, "{$sec['id']}_{$key}_{$uniqueGroupId}") ?>
                                                                <input type="hidden" name="blocks[<?= $key ?>_<?= $uniqueGroupId ?>][type]" value="image">

                                                            <?php else: ?>
                                                                <input type="<?= $type ?>" name="blocks[<?= $key ?>_<?= $uniqueGroupId ?>][value]" class="admin-input" value="<?= htmlspecialchars($val) ?>">
                                                                <input type="hidden" name="blocks[<?= $key ?>_<?= $uniqueGroupId ?>][type]" value="<?= $type ?>">
                                                            <?php endif; ?>

                                                            <input type="hidden" name="blocks[<?= $key ?>_<?= $uniqueGroupId ?>][group_id]" value="<?= $uniqueGroupId ?>">
                                                            <input type="hidden" name="blocks[<?= $key ?>_<?= $uniqueGroupId ?>][sort_order]" value="<?= $sortOrder ?>">
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<div class="media-modal" id="addSectionModal">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3 class="card-title">
                <i data-lucide="plus-circle" style="color: var(--primary)"></i>
                <span>Ajouter une section</span>
            </h3>
            <button class="action-btn-small" onclick="closeAddSectionModal()">
                <i data-lucide="x"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="addSectionForm" onsubmit="submitAddSection(event)">
                <div class="admin-form-group">
                    <label for="new_sec_name">Nom de la section</label>
                    <input type="text" id="new_sec_name" class="admin-input" required placeholder="Ex: Notre équipe">
                </div>
                
                <div class="admin-form-group">
                    <label for="new_sec_type">Modèle visuel / Type</label>
                    <select id="new_sec_type" class="admin-select" required>
                        <option value="hero">Hero Banner</option>
                        <option value="services">Services</option>
                        <option value="portfolio">Portfolio</option>
                        <option value="team">Team</option>
                        <option value="testimonials">Témoignages</option>
                        <option value="faq">FAQ</option>
                        <option value="blog">Blog</option>
                        <option value="contact">Contact</option>
                    </select>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 20px;">
                    <button type="button" class="btn-secondary" onclick="closeAddSectionModal()">Annuler</button>
                    <button type="submit" class="btn-primary">Ajouter la section</button>
                </div>
            </form>
        </div>
    </div>
</div>



<script>
    function switchSection(sectionId) {
        document.querySelectorAll('.section-nav-item').forEach(item => {
            item.classList.remove('active');
        });
        const activeNav = document.querySelector(`.section-nav-item[data-id="${sectionId}"]`);
        if (activeNav) {
            activeNav.classList.add('active');
        }

        document.querySelectorAll('.section-form-panel').forEach(panel => {
            panel.classList.remove('active');
        });
        const activePanel = document.getElementById('panel-' + sectionId);
        if (activePanel) {
            activePanel.classList.add('active');
            activePanel.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            // Add a visual flash effect to show it is selected
            activePanel.style.transition = 'outline 0.3s ease';
            activePanel.style.outline = '2px solid var(--primary)';
            activePanel.style.borderRadius = '20px';
            setTimeout(() => {
                activePanel.style.outline = 'none';
            }, 1000);

            // Focus the first form input or textarea inside the card
            const firstInput = activePanel.querySelector('input:not([type="hidden"]), textarea, select');
            if (firstInput) {
                setTimeout(() => firstInput.focus(), 600);
            }
        }
    }

    function focusPageConfig(area) {
        document.querySelectorAll('.section-nav-item').forEach(item => {
            item.classList.remove('active');
        });
        document.querySelectorAll('.section-form-panel').forEach(panel => {
            panel.classList.remove('active');
        });
        
        if (area === 'header') {
            const headerTitleEl = document.getElementById('header_config_title') || document.getElementById('header_bg_mode');
            if (headerTitleEl) {
                headerTitleEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                const headerContainer = document.getElementById('header_bg_mode').closest('div').parentElement;
                if (headerContainer) {
                    headerContainer.style.transition = 'outline 0.3s ease';
                    headerContainer.style.outline = '2px solid var(--secondary)';
                    headerContainer.style.borderRadius = '14px';
                    setTimeout(() => {
                        headerContainer.style.outline = 'none';
                    }, 1000);
                }
                const firstInput = document.getElementById('header_bg_mode');
                if (firstInput) {
                    setTimeout(() => firstInput.focus(), 600);
                }
            }
        } else if (area === 'hero') {
            const heroTitleEl = document.getElementById('hero_config_title') || document.getElementById('hero_title');
            if (heroTitleEl) {
                heroTitleEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                const heroContainer = document.getElementById('hero_title').closest('div').parentElement;
                if (heroContainer) {
                    heroContainer.style.transition = 'outline 0.3s ease';
                    heroContainer.style.outline = '2px solid var(--secondary)';
                    heroContainer.style.borderRadius = '14px';
                    setTimeout(() => {
                        heroContainer.style.outline = 'none';
                    }, 1000);
                }
                const firstInput = document.getElementById('hero_title');
                if (firstInput) {
                    setTimeout(() => firstInput.focus(), 600);
                }
            }
        }
    }

    function openAddSectionModal() {
        document.getElementById('addSectionModal').classList.add('active');
        document.getElementById('new_sec_name').focus();
    }
    function closeAddSectionModal() {
        document.getElementById('addSectionModal').classList.remove('active');
    }

    function submitAddSection(e) {
        e.preventDefault();
        const pageId = <?= $page['id'] ?>;
        const name = document.getElementById('new_sec_name').value;
        const type = document.getElementById('new_sec_type').value;

        const formData = new FormData();
        formData.append('page_id', pageId);
        formData.append('name', name);
        formData.append('type', type);
        formData.append('csrf_token', '<?= htmlspecialchars($csrf_token) ?>');

        fetch(BASE_URL + '/admin/pages/sections/add', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeAddSectionModal();
                showNotification('Section ajoutée !', 'success');
                setTimeout(() => window.location.reload(), 800);
            } else {
                showNotification(data.error, 'error');
            }
        })
        .catch(err => {
            console.error(err);
            showNotification('Erreur réseau.', 'error');
        });
    }

    function renameSection(sectionId, e) {
        e.stopPropagation();
        const nameTextSpan = document.getElementById('nav-text-' + sectionId);
        const oldName = nameTextSpan.innerText;
        const newName = prompt('Entrez le nouveau nom de la section :', oldName);
        if (newName && newName.trim() !== '') {
            nameTextSpan.innerText = newName;
            document.getElementById('panel-title-' + sectionId).innerText = newName;
        }
    }

    function deleteSection(sectionId, e) {
        e.stopPropagation();
        if (!confirm('Supprimer définitivement cette section ?')) {
            return;
        }

        const formData = new FormData();
        formData.append('section_id', sectionId);
        formData.append('csrf_token', '<?= htmlspecialchars($csrf_token) ?>');

        fetch(BASE_URL + '/admin/pages/sections/delete', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Section supprimée.', 'success');
                setTimeout(() => window.location.reload(), 600);
            } else {
                showNotification(data.error, 'error');
            }
        });
    }

    function saveSectionContent(sectionId) {
        const form = document.getElementById('form-' + sectionId);
        if (!form) return;

        const formData = new FormData(form);
        formData.append('section_id', sectionId);
        formData.append('csrf_token', '<?= htmlspecialchars($csrf_token) ?>');

        showNotification('Enregistrement...', 'info');

        fetch(BASE_URL + '/admin/pages/blocks/update', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
            } else {
                showNotification(data.error, 'error');
            }
        });
    }

    function addRepeatableGroup(sectionId, type) {
        const formData = new FormData();
        formData.append('section_id', sectionId);
        formData.append('type', type);
        formData.append('csrf_token', '<?= htmlspecialchars($csrf_token) ?>');

        fetch(BASE_URL + '/admin/pages/blocks/group-add', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                setTimeout(() => window.location.reload(), 600);
            } else {
                showNotification(data.error, 'error');
            }
        });
    }

    function deleteRepeatableGroup(sectionId, groupId) {
        if (!confirm('Supprimer définitivement cet élément ?')) {
            return;
        }

        const formData = new FormData();
        formData.append('section_id', sectionId);
        formData.append('group_id', groupId);
        formData.append('csrf_token', '<?= htmlspecialchars($csrf_token) ?>');

        fetch(BASE_URL + '/admin/pages/blocks/group-delete', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                const card = document.getElementById(`card-${sectionId}-${groupId}`);
                if (card) {
                    card.style.transition = 'opacity 0.2s';
                    card.style.opacity = '0';
                    setTimeout(() => card.remove(), 250);
                }
            } else {
                showNotification(data.error, 'error');
            }
        });
    }

    let dragSrcEl = null;

    function dragStart(e) {
        dragSrcEl = e.currentTarget;
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/html', dragSrcEl.innerHTML);
        dragSrcEl.style.opacity = '0.4';
    }

    function dragOver(e) {
        e.preventDefault();
        return false;
    }

    function dragEnd(e) {
        e.currentTarget.style.opacity = '1';
        
        const items = document.querySelectorAll('.section-nav-item');
        const ids = [];
        items.forEach(item => {
            ids.push(item.getAttribute('data-id'));
        });

        const formData = new FormData();
        ids.forEach(id => formData.append('ids[]', id));
        formData.append('csrf_token', '<?= htmlspecialchars($csrf_token) ?>');

        fetch(BASE_URL + '/admin/pages/sections/sort', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Mise en page réordonnée !', 'success');
            } else {
                showNotification(data.error, 'error');
            }
        });
    }

    const sectionList = document.getElementById('sectionList');
    if (sectionList) {
        sectionList.addEventListener('dragover', e => {
            const afterElement = getDragAfterElement(sectionList, e.clientY);
            const draggable = document.querySelector('.section-nav-item[style*="opacity: 0.4"]');
            if (draggable) {
                if (afterElement == null) {
                    sectionList.appendChild(draggable);
                } else {
                    sectionList.insertBefore(draggable, afterElement);
                }
            }
        });
    }

    function getDragAfterElement(container, y) {
        const draggableElements = [...container.querySelectorAll('.section-nav-item:not([style*="opacity: 0.4"])')];
        return draggableElements.reduce((closest, child) => {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;
            if (offset < 0 && offset > closest.offset) {
                return { offset: offset, element: child };
            } else {
                return closest;
            }
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }

    function toggleSlideshowManager(variant) {
        const container = document.getElementById('slides_manager_container');
        if (container) {
            if (variant === 'hero_slider') {
                container.style.display = 'block';
            } else {
                container.style.display = 'none';
            }
        }
    }

    function addHeroSlide(pageId) {
        const formData = new FormData();
        formData.append('page_id', pageId);
        formData.append('csrf_token', '<?= htmlspecialchars($csrf_token) ?>');

        showNotification('Ajout du slide...', 'info');

        fetch(BASE_URL + '/admin/pages/slides/add', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                setTimeout(() => window.location.reload(), 600);
            } else {
                showNotification(data.error || 'Erreur lors de l\'ajout.', 'error');
            }
        })
        .catch(err => {
            console.error(err);
            showNotification('Erreur réseau.', 'error');
        });
    }

    function deleteHeroSlide(slideId) {
        if (!confirm('Supprimer définitivement ce slide ?')) {
            return;
        }

        const formData = new FormData();
        formData.append('slide_id', slideId);
        formData.append('csrf_token', '<?= htmlspecialchars($csrf_token) ?>');

        showNotification('Suppression du slide...', 'info');

        fetch(BASE_URL + '/admin/pages/slides/delete', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                const card = document.getElementById('slide-card-' + slideId);
                if (card) {
                    card.style.transition = 'opacity 0.2s';
                    card.style.opacity = '0';
                    setTimeout(() => {
                        card.remove();
                        const slidesList = document.getElementById('slides_list');
                        if (slidesList && slidesList.querySelectorAll('.slide-card').length === 0) {
                            slidesList.innerHTML = '<p style="text-align: center; color: var(--text-muted); font-size: 0.85rem; padding: 20px;" id="no_slides_text">Aucun slide enregistré. Cliquez sur "Ajouter un Slide" pour commencer.</p>';
                        }
                        updateSimulator();
                    }, 250);
                }
            } else {
                showNotification(data.error || 'Erreur lors de la suppression.', 'error');
            }
        })
        .catch(err => {
            console.error(err);
            showNotification('Erreur réseau.', 'error');
        });
    }

    function saveSlidesContent() {
        const formData = new FormData();
        formData.append('csrf_token', '<?= htmlspecialchars($csrf_token) ?>');

        const slideCards = document.querySelectorAll('.slide-card');
        if (slideCards.length === 0) {
            showNotification('Aucun slide à sauvegarder.', 'info');
            return;
        }

        slideCards.forEach(card => {
            const id = card.getAttribute('data-slide-id');
            const titleInput = card.querySelector(`input[name="slides[${id}][title]"]`);
            const subtitleText = card.querySelector(`textarea[name="slides[${id}][subtitle]"]`);
            const badgeInput = card.querySelector(`input[name="slides[${id}][badge]"]`);
            const ctaTextInput = card.querySelector(`input[name="slides[${id}][cta_text]"]`);
            const ctaUrlInput = card.querySelector(`input[name="slides[${id}][cta_url]"]`);
            const imageInput = card.querySelector(`input[name="slides[${id}][image]"]`);
            const sortOrderInput = card.querySelector(`input[name="slides[${id}][sort_order]"]`);

            if (titleInput) formData.append(`slides[${id}][title]`, titleInput.value);
            if (subtitleText) formData.append(`slides[${id}][subtitle]`, subtitleText.value);
            if (badgeInput) formData.append(`slides[${id}][badge]`, badgeInput.value);
            if (ctaTextInput) formData.append(`slides[${id}][cta_text]`, ctaTextInput.value);
            if (ctaUrlInput) formData.append(`slides[${id}][cta_url]`, ctaUrlInput.value);
            if (imageInput) formData.append(`slides[${id}][image]`, imageInput.value);
            if (sortOrderInput) formData.append(`slides[${id}][sort_order]`, sortOrderInput.value);
        });

        showNotification('Sauvegarde des slides...', 'info');

        fetch(BASE_URL + '/admin/pages/slides/update', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                updateSimulator();
            } else {
                showNotification(data.error || 'Erreur lors de la sauvegarde.', 'error');
            }
        })
        .catch(err => {
            console.error(err);
            showNotification('Erreur réseau.', 'error');
        });
    }

    window.simActiveSlideIndex = window.simActiveSlideIndex || 0;
    function setSimActiveSlide(idx) {
        window.simActiveSlideIndex = idx;
        updateSimulator();
    }

    // --- VISUAL SIMULATOR REAL-TIME UPDATE SCRIPTS ---
    function updateSimulator() {
        // 1. Read values from form
        const headerBgMode = document.getElementById('header_bg_mode').value;
        const headerOpacity = parseFloat(document.getElementById('header_opacity').value);
        const headerBlur = parseInt(document.getElementById('header_blur').value) || 0;
        const headerShadow = document.getElementById('header_shadow').value;
        const headerContrastMode = document.getElementById('header_contrast_mode').value;
        const logoSize = parseInt(document.getElementById('logo_size').value) || 38;

        const heroLayoutMode = document.getElementById('hero_layout_mode').value;
        const heroTextPosition = document.getElementById('hero_text_position').value;
        const heroTextAlignment = document.getElementById('hero_text_alignment').value;
        const heroTextWidth = document.getElementById('hero_text_width').value || '100%';
        const heroShadowStrength = document.getElementById('hero_shadow_strength').value;
        const heroOverlayOpacity = parseFloat(document.getElementById('hero_overlay_opacity').value);

        const heroTitle = document.getElementById('hero_title').value || 'Titre de la Section Hero';
        const heroSubtitle = document.getElementById('hero_subtitle').value || 'Description sous-titre de la section hero pour attirer vos visiteurs.';
        const heroBadge = document.getElementById('hero_badge').value || '';
        const heroVariant = document.getElementById('hero_variant').value;
        const heroImageLayout = document.getElementById('hero_image_layout').value;
        const heroImageSize = document.getElementById('hero_image_size').value;

        const visualBrightness = parseFloat(document.getElementById('visual_brightness').value) || 1.0;
        const visualSaturation = parseFloat(document.getElementById('visual_saturation').value) || 1.0;
        const visualBlur = parseInt(document.getElementById('visual_blur').value) || 0;

        // Mobile specific overrides (if simulator is in mobile view)
        const isMobile = document.getElementById('simulator_viewport').classList.contains('sim-viewport-mobile');

        // Update Slider text indicators
        document.getElementById('header_opacity_val').innerText = headerOpacity;
        document.getElementById('hero_overlay_opacity_val').innerText = heroOverlayOpacity;
        document.getElementById('visual_brightness_val').innerText = visualBrightness;
        document.getElementById('visual_saturation_val').innerText = visualSaturation;
        document.getElementById('visual_blur_val').innerText = visualBlur + 'px';

        // 2. Simulated Header adjustments
        const simHeader = document.getElementById('sim_header');
        const simLogo = document.getElementById('sim_header_logo');
        const simLogoText = document.getElementById('sim_header_logo_text');
        const simNav = document.getElementById('sim_header_nav');
        const simCta = document.getElementById('sim_header_cta');

        // Header Background style
        let headerBg = 'rgba(255, 255, 255, 0.45)';
        let headerFilter = 'blur(10px)';
        let headerBorder = '1px solid rgba(255, 255, 255, 0.6)';

        if (headerBgMode === 'clair') {
            headerBg = `rgba(255, 255, 255, ${headerOpacity})`;
            headerBorder = '1px solid rgba(255, 255, 255, 0.8)';
        } else if (headerBgMode === 'sombre') {
            headerBg = `rgba(15, 23, 42, ${headerOpacity})`;
            headerBorder = '1px solid rgba(255, 255, 255, 0.1)';
        } else if (headerBgMode === 'semi-transparent') {
            headerBg = `rgba(255, 255, 255, ${headerOpacity})`;
        } else if (headerBgMode === 'blur') {
            headerBg = `rgba(255, 255, 255, ${headerOpacity * 0.3})`;
            headerFilter = `blur(${headerBlur / 2}px)`;
        } else if (headerBgMode === 'plein') {
            headerBg = (headerContrastMode === 'light_on_dark') ? '#0f172a' : '#ffffff';
            headerFilter = 'none';
            headerBorder = (headerContrastMode === 'light_on_dark') ? '1px solid rgba(255,255,255,0.1)' : '1px solid rgba(0,0,0,0.1)';
        } else if (headerBgMode === 'glass') {
            headerBg = `rgba(255, 255, 255, ${headerOpacity})`;
            headerFilter = `blur(${headerBlur / 2}px)`;
        }

        simHeader.style.background = headerBg;
        simHeader.style.backdropFilter = headerFilter;
        simHeader.style.webkitBackdropFilter = headerFilter;
        simHeader.style.border = headerBorder;

        // Header Shadow
        let headerShadowCss = 'none';
        if (headerShadow === 'leger') headerShadowCss = '0 2px 4px rgba(0,0,0,0.05)';
        else if (headerShadow === 'moyen') headerShadowCss = '0 6px 10px rgba(0,0,0,0.08)';
        else if (headerShadow === 'fort') headerShadowCss = '0 12px 20px rgba(0,0,0,0.15)';
        simHeader.style.boxShadow = headerShadowCss;

        // Contrast text and elements
        const darkTheme = (headerContrastMode === 'light_on_dark' || headerBgMode === 'sombre');
        if (darkTheme) {
            simNav.style.color = 'rgba(255, 255, 255, 0.8)';
            simLogoText.style.color = '#ffffff';
        } else {
            simNav.style.color = '#334155';
            simLogoText.style.color = 'var(--primary)';
        }

        // Logo size in simulator
        simLogo.style.height = `${Math.min(22, logoSize / 2)}px`;

        // 3. Hero layout adjustments
        const simHero = document.getElementById('sim_hero');
        const simHeroBg = document.getElementById('sim_hero_bg');
        const simHeroOverlay = document.getElementById('sim_hero_overlay');
        const simHeroText = document.getElementById('sim_hero_text_block');
        const simHeroTitle = document.getElementById('sim_hero_title');
        const simHeroSubtitle = document.getElementById('sim_hero_subtitle');
        const simHeroBadge = document.getElementById('sim_hero_badge');
        const simHeroGrid = document.getElementById('sim_hero_grid');
        const simHeroVisual = document.getElementById('sim_hero_visual');

        // Hero background image
        const mainHeroImageInput = document.getElementById('hero_image');
        let heroImgUrl = mainHeroImageInput ? mainHeroImageInput.value : '';
        if (isMobile) {
            const mobHeroImageInput = document.getElementById('hero_image_mobile');
            if (mobHeroImageInput && mobHeroImageInput.value) {
                heroImgUrl = mobHeroImageInput.value;
            }
        }
        
        // Set backgrounds
        const bgInput = document.getElementById('hero_bg_color').value;
        
        let textShadowCss = 'none';
        if (heroShadowStrength === 'leger') textShadowCss = '0 1px 2px rgba(0,0,0,0.2)';
        else if (heroShadowStrength === 'moyen') textShadowCss = '0 2px 4px rgba(0,0,0,0.35)';
        else if (heroShadowStrength === 'fort') textShadowCss = '0 4px 8px rgba(0,0,0,0.6)';

        if (heroVariant === 'hero_slider') {
            simHeroBg.style.backgroundImage = 'none';
            simHeroBg.style.background = 'linear-gradient(135deg, #0b0f19 0%, #1e1b4b 100%)';
            simHeroOverlay.style.background = 'rgba(0,0,0,0.3)';
            simHeroOverlay.style.opacity = '1';
            simHeroTitle.style.color = '#ffffff';
            simHeroSubtitle.style.color = '#e2e8f0';
            simHeroVisual.style.display = 'flex';
            simHeroGrid.style.gridTemplateColumns = '1.1fr 0.9fr';
            
            // Extract slide list
            const slides = [];
            document.querySelectorAll('.slide-card').forEach((card) => {
                const id = card.getAttribute('data-slide-id');
                const titleInput = card.querySelector(`input[name="slides[${id}][title]"]`);
                const subtitleText = card.querySelector(`textarea[name="slides[${id}][subtitle]"]`);
                const badgeInput = card.querySelector(`input[name="slides[${id}][badge]"]`);
                const ctaTextInput = card.querySelector(`input[name="slides[${id}][cta_text]"]`);
                const hiddenImgInput = card.querySelector('.media-input-value');
                const img = (hiddenImgInput && hiddenImgInput.value) ? hiddenImgInput.value : '';

                slides.push({
                    title: titleInput ? titleInput.value : '',
                    subtitle: subtitleText ? subtitleText.value : '',
                    badge: badgeInput ? badgeInput.value : '',
                    cta_text: ctaTextInput ? ctaTextInput.value : '',
                    img: img
                });
            });

            if (slides.length === 0) {
                slides.push({
                    title: heroTitle,
                    subtitle: heroSubtitle,
                    badge: heroBadge,
                    cta_text: heroCta1Text || 'Découvrir',
                    img: heroImgUrl
                });
            }

            if (window.simActiveSlideIndex >= slides.length) {
                window.simActiveSlideIndex = 0;
            }

            let slidesHtml = '';
            let dotsHtml = '';
            slides.forEach((slide, idx) => {
                const isActive = idx === window.simActiveSlideIndex ? 'opacity: 1; z-index: 4;' : 'opacity: 0; z-index: 3;';
                slidesHtml += `
                    <div class="sim-slide-item" data-index="${idx}" style="position: absolute; inset: 0; display: flex; align-items: center; transition: opacity 0.5s ease-in-out; ${isActive}">
                        <div style="width: 100%; display: grid; grid-template-columns: 1.1fr 0.9fr; gap: 12px; align-items: center; padding: 0 12px;">
                            <div style="display: flex; flex-direction: column; gap: 4px;">
                                ${slide.badge ? `<span style="display: inline-flex; align-self: flex-start; padding: 1px 5px; border-radius: 20px; background: rgba(37,99,235,0.25); color: #93c5fd; font-size: 0.42rem; font-weight: 700; border: 1px solid rgba(37,99,235,0.3); text-transform: uppercase;">${slide.badge}</span>` : ''}
                                <h2 style="font-size: 0.85rem; line-height: 1.1; font-weight: 800; font-family: var(--font-heading); margin: 0; color: #ffffff; text-shadow: ${textShadowCss};">${slide.title}</h2>
                                <p style="font-size: 0.5rem; color: #cbd5e1; margin: 0; text-shadow: ${textShadowCss};">${slide.subtitle}</p>
                                <div style="display: flex; gap: 4px; margin-top: 3px;">
                                    ${slide.cta_text ? `<span style="padding: 2px 6px; border-radius: 4px; background: #e26d36; color: white; font-size: 0.42rem; font-weight: 700;">${slide.cta_text}</span>` : ''}
                                </div>
                            </div>
                            <div style="display: flex; justify-content: center; align-items: center;">
                                <div style="width: 80%; aspect-ratio: 1.4; border-radius: 6px; background: #0f172a; padding: 3px; border: 1px solid #334155; box-shadow: 0 4px 8px rgba(0,0,0,0.15); overflow: hidden;">
                                    <div style="width: 100%; height: 100%; background: #1e293b; border-radius: 4px; background-size: cover; background-position: center; background-image: url('${slide.img}');"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                dotsHtml += `
                    <span class="sim-slide-dot" data-index="${idx}" style="width: 6px; height: 6px; border-radius: 50%; background: ${idx === window.simActiveSlideIndex ? '#ffffff' : 'rgba(255,255,255,0.4)'}; cursor: pointer; transition: all 0.3s;" onclick="event.stopPropagation(); setSimActiveSlide(${idx})"></span>
                `;
            });

            simHero.innerHTML = `
                <div id="sim_slides_container" style="position: absolute; inset: 0; z-index: 3; overflow: hidden; height: 100%; width: 100%;">
                    ${slidesHtml}
                </div>
                <div id="sim_slides_dots" style="position: absolute; bottom: 8px; left: 50%; transform: translateX(-50%); display: flex; gap: 4px; z-index: 10;">
                    ${dotsHtml}
                </div>
            `;
        } else if (heroVariant === 'hero_video') {
            simHeroBg.style.backgroundImage = 'none';
            simHeroBg.style.background = 'linear-gradient(135deg, #090d16 0%, #05050f 100%)';
            simHeroOverlay.style.background = 'radial-gradient(circle, rgba(0,0,0,0.2) 0%, rgba(0,0,0,0.7) 100%)';
            simHeroOverlay.style.opacity = '1';
            simHeroTitle.style.color = '#ffffff';
            simHeroSubtitle.style.color = '#94a3b8';
            simHeroVisual.style.display = 'flex';
            simHeroGrid.style.gridTemplateColumns = '1.1fr 0.9fr';
            simHeroVisual.innerHTML = `
              <div id="sim_hero_mockup" style="width: 80%; aspect-ratio: 1.4; border-radius: 12px; background: rgba(30, 41, 59, 0.45); padding: 8px; border: 1px solid rgba(255,255,255,0.12); box-shadow: 0 10px 25px rgba(0,0,0,0.2); position: relative; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                 <div id="sim_hero_mockup_screen" style="width:100%; height:100%; background: #000; border-radius: 6px; background-size: cover; background-position: center; background-image: url('${heroImgUrl}'); filter: brightness(0.65);"></div>
                 <div style="position: absolute; width: 24px; height: 24px; border-radius: 50%; background: rgba(255,255,255,0.85); display: flex; align-items: center; justify-content: center; color: #000; z-index: 10; box-shadow: 0 0 10px rgba(0,0,0,0.3);"><i data-lucide="play" style="width: 10px; height: 10px; fill: #000; margin-left: 2px;"></i></div>
              </div>
            `;
            if (typeof lucide !== 'undefined') lucide.createIcons();
        } else if (heroVariant === 'hero_corporate') {
            simHeroBg.style.backgroundImage = 'none';
            simHeroBg.style.background = bgInput;
            simHeroOverlay.style.opacity = '0';
            simHeroTitle.style.color = 'var(--text-main)';
            simHeroSubtitle.style.color = 'var(--text-muted)';
            simHeroVisual.style.display = 'flex';
            simHeroGrid.style.gridTemplateColumns = '1.1fr 0.9fr';
            simHeroVisual.innerHTML = `
              <div id="sim_hero_mockup" style="width: 80%; aspect-ratio: 1.4; border-radius: 6px; background: #ffffff; padding: 4px; border: 1px solid #cbd5e1; box-shadow: 0 8px 16px rgba(0,0,0,0.06);">
                  <div id="sim_hero_mockup_screen" style="width:100%; height:100%; background: #f8fafc; border-radius: 4px; background-size: cover; background-position: center; background-image: url('${heroImgUrl}');"></div>
              </div>
            `;
        } else if (heroVariant === 'hero_magazine') {
            simHeroBg.style.backgroundImage = 'none';
            simHeroBg.style.background = 'linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%)';
            simHeroOverlay.style.opacity = '0';
            simHeroTitle.style.color = 'var(--text-main)';
            simHeroSubtitle.style.color = 'var(--text-muted)';
            simHeroVisual.style.display = 'flex';
            simHeroGrid.style.gridTemplateColumns = '1fr 1fr';
            simHeroVisual.innerHTML = `
              <div style="display: flex; flex-direction: column; gap: 8px; width: 100%; max-width: 220px; font-size: 0.35rem; color: var(--text-main); border-left: 1px solid var(--border); padding-left: 16px;">
                <div style="font-weight: 800; font-family: var(--font-heading); color: var(--primary); text-transform: uppercase; font-size: 0.4rem; letter-spacing: 0.05em; margin-bottom: 2px;">Derniers articles</div>
                <div style="border-bottom: 1px solid var(--border); padding-bottom: 6px;">
                   <strong>Stratégie IA 2026</strong>
                   <p style="font-size:0.28rem;color:var(--text-muted);">Comment piloter l'automatisation.</p>
                </div>
                <div style="border-bottom: 1px solid var(--border); padding-bottom: 6px;">
                   <strong>Design Systems</strong>
                   <p style="font-size:0.28rem;color:var(--text-muted);">Optimiser la vélocité UX.</p>
                </div>
                <div>
                   <strong>Green IT & Performance</strong>
                   <p style="font-size:0.28rem;color:var(--text-muted);">Développer durablement.</p>
                </div>
              </div>
            `;
        } else if (heroVariant === 'hero_full_image' || heroVariant === 'hero_full_width') {
            simHeroBg.style.backgroundImage = heroImgUrl ? `url('${heroImgUrl}')` : 'none';
            simHeroBg.style.background = 'transparent';
            simHeroOverlay.style.background = `linear-gradient(rgba(11, 15, 25, ${heroOverlayOpacity}), rgba(11, 15, 25, ${Math.min(1.0, heroOverlayOpacity + 0.15)}))`;
            simHeroOverlay.style.opacity = '1';
            simHeroTitle.style.color = '#ffffff';
            simHeroSubtitle.style.color = '#e2e8f0';
            simHeroVisual.style.display = 'none';
            simHeroGrid.style.gridTemplateColumns = '1fr';
        } else if (heroVariant === 'hero_text_only') {
            simHeroBg.style.backgroundImage = 'none';
            simHeroBg.style.background = bgInput;
            simHeroOverlay.style.opacity = '0';
            simHeroTitle.style.color = 'var(--text-main)';
            simHeroSubtitle.style.color = 'var(--text-muted)';
            simHeroVisual.style.display = 'none';
            simHeroGrid.style.gridTemplateColumns = '1fr';
        } else if (heroVariant === 'hero_ambient_glow') {
            simHeroBg.style.backgroundImage = 'none';
            simHeroBg.style.background = `radial-gradient(circle, rgba(8, 145, 178, 0.22) 0%, rgba(30, 58, 138, 0.08) 50%, rgba(0,0,0,0) 80%), ${bgInput}`;
            simHeroOverlay.style.opacity = '0';
            simHeroTitle.style.color = 'var(--text-main)';
            simHeroSubtitle.style.color = 'var(--text-muted)';
            simHeroVisual.style.display = 'none';
            simHeroGrid.style.gridTemplateColumns = '1fr';
        } else if (heroVariant === 'hero_split_asymmetric') {
            simHeroBg.style.backgroundImage = 'none';
            simHeroBg.style.background = bgInput;
            simHeroOverlay.style.opacity = '0';
            simHeroTitle.style.color = 'var(--text-main)';
            simHeroSubtitle.style.color = 'var(--text-muted)';
            simHeroVisual.style.display = 'flex';
            simHeroGrid.style.gridTemplateColumns = '1.1fr 0.9fr';
            simHeroVisual.innerHTML = `
              <div style="display: flex; flex-direction: column; gap: 8px; width: 100%; max-width: 220px; font-size: 0.35rem;">
                <div style="background: rgba(255,255,255,0.7); border: 1px solid rgba(255,255,255,0.8); border-radius: 8px; padding: 6px 10px; display: flex; align-items: center; gap: 8px; transform: translateX(-10px);">
                  <div style="width: 14px; height: 14px; border-radius: 4px; background: rgba(37, 99, 235, 0.1); display: flex; align-items: center; justify-content: center; color: var(--primary);"><i data-lucide="zap" style="width: 8px; height: 8px;"></i></div>
                  <div><strong>Haute Performance</strong><div style="font-size:0.28rem;color:var(--text-muted);">Web rapide.</div></div>
                </div>
                <div style="background: rgba(255,255,255,0.85); border: 1px solid rgba(255,255,255,0.9); border-radius: 8px; padding: 6px 10px; display: flex; align-items: center; gap: 8px; border-left: 2px solid var(--secondary);">
                  <div style="width: 14px; height: 14px; border-radius: 4px; background: rgba(8, 145, 178, 0.1); display: flex; align-items: center; justify-content: center; color: var(--secondary);"><i data-lucide="shield-check" style="width: 8px; height: 8px;"></i></div>
                  <div><strong>Sécurité</strong><div style="font-size:0.28rem;color:var(--text-muted);">Données protégées.</div></div>
                </div>
                <div style="background: rgba(255,255,255,0.7); border: 1px solid rgba(255,255,255,0.8); border-radius: 8px; padding: 6px 10px; display: flex; align-items: center; gap: 8px; transform: translateX(10px);">
                  <div style="width: 14px; height: 14px; border-radius: 4px; background: rgba(226, 109, 54, 0.1); display: flex; align-items: center; justify-content: center; color: #e26d36;"><i data-lucide="layout-grid" style="width: 8px; height: 8px;"></i></div>
                  <div><strong>CMS Modulaire</strong><div style="font-size:0.28rem;color:var(--text-muted);">Modifications live.</div></div>
                </div>
              </div>
            `;
            if (typeof lucide !== 'undefined') lucide.createIcons();
        } else if (heroVariant === 'hero_grid_features') {
            simHeroBg.style.backgroundImage = 'none';
            simHeroBg.style.background = bgInput;
            simHeroOverlay.style.opacity = '0';
            simHeroTitle.style.color = 'var(--text-main)';
            simHeroSubtitle.style.color = 'var(--text-muted)';
            simHeroVisual.style.display = 'flex';
            simHeroGrid.style.gridTemplateColumns = '1.1fr 0.9fr';
            simHeroVisual.innerHTML = `
              <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; width: 100%; max-width: 220px; font-size: 0.35rem;">
                <div style="background: rgba(255,255,255,0.7); border: 1px solid rgba(255,255,255,0.8); border-radius: 8px; padding: 8px 6px; display: flex; flex-direction: column; gap: 4px;">
                  <div style="width: 14px; height: 14px; border-radius: 4px; background: rgba(37, 99, 235, 0.1); display: flex; align-items: center; justify-content: center; color: var(--primary);"><i data-lucide="cpu" style="width: 8px; height: 8px;"></i></div>
                  <div><strong>IA & Automation</strong></div>
                </div>
                <div style="background: rgba(255,255,255,0.7); border: 1px solid rgba(255,255,255,0.8); border-radius: 8px; padding: 8px 6px; display: flex; flex-direction: column; gap: 4px;">
                  <div style="width: 14px; height: 14px; border-radius: 4px; background: rgba(8, 145, 178, 0.1); display: flex; align-items: center; justify-content: center; color: var(--secondary);"><i data-lucide="cloud" style="width: 8px; height: 8px;"></i></div>
                  <div><strong>Infrastructure</strong></div>
                </div>
                <div style="background: rgba(255,255,255,0.7); border: 1px solid rgba(255,255,255,0.8); border-radius: 8px; padding: 8px 6px; display: flex; flex-direction: column; gap: 4px;">
                  <div style="width: 14px; height: 14px; border-radius: 4px; background: rgba(6, 182, 212, 0.1); display: flex; align-items: center; justify-content: center; color: #0891b2;"><i data-lucide="globe" style="width: 8px; height: 8px;"></i></div>
                  <div><strong>Applications</strong></div>
                </div>
                <div style="background: rgba(255,255,255,0.7); border: 1px solid rgba(255,255,255,0.8); border-radius: 8px; padding: 8px 6px; display: flex; flex-direction: column; gap: 4px;">
                  <div style="width: 14px; height: 14px; border-radius: 4px; background: rgba(226, 109, 54, 0.1); display: flex; align-items: center; justify-content: center; color: #e26d36;"><i data-lucide="phone-call" style="width: 8px; height: 8px;"></i></div>
                  <div><strong>Support 24/7</strong></div>
                </div>
              </div>
            `;
            if (typeof lucide !== 'undefined') lucide.createIcons();
        } else {
            // Split layout variants (hero_split, hero_split_large_image, hero_split_small_image, hero_floating_card, hero_card)
            simHeroBg.style.backgroundImage = 'none';
            simHeroBg.style.background = bgInput;
            simHeroOverlay.style.opacity = '0';
            simHeroTitle.style.color = 'var(--text-main)';
            simHeroSubtitle.style.color = 'var(--text-muted)';
            simHeroVisual.style.display = 'flex';
            simHeroGrid.style.gridTemplateColumns = '1.1fr 0.9fr';
            // Reset mockup container structure if it got overwritten by stack/grid
            if (!document.getElementById('sim_hero_mockup')) {
                simHeroVisual.innerHTML = `
                  <div id="sim_hero_mockup" style="width: 80%; aspect-ratio: 1.4; border-radius: 6px; background: #0f172a; padding: 3px; border: 1px solid #334155; box-shadow: 0 4px 8px rgba(0,0,0,0.15); transition: all 0.3s;">
                      <div id="sim_hero_mockup_screen" style="width:100%; height:100%; background: #1e293b; border-radius: 4px; background-size: cover; background-position: center; transition: all 0.3s;"></div>
                  </div>
                `;
            }
        }

        // Adjust mockup visual sizes and style frames based on settings
        const mockupFrame = document.getElementById('sim_hero_mockup');
        if (mockupFrame) {
            let visualWidth = '90%';
            if (heroImageSize === 'medium') visualWidth = '75%';
            else if (heroImageSize === 'small') visualWidth = '55%';
            mockupFrame.style.width = visualWidth;

            if (heroImageLayout === 'floating') {
                mockupFrame.style.background = 'rgba(30, 41, 59, 0.45)';
                mockupFrame.style.borderRadius = '12px';
                mockupFrame.style.border = '1px solid rgba(255, 255, 255, 0.15)';
                mockupFrame.style.padding = '8px';
            } else {
                mockupFrame.style.background = '#0f172a';
                mockupFrame.style.borderRadius = '6px';
                mockupFrame.style.border = '1px solid #334155';
                mockupFrame.style.padding = '3px';
            }
        }

        // Update CTA Button Texts and Visibility
        const heroCta1Text = document.getElementById('hero_cta1_text').value || '';
        const heroCta2Text = document.getElementById('hero_cta2_text').value || '';
        const simCta1Btn = document.getElementById('sim_hero_cta1_btn');
        const simCta2Btn = document.getElementById('sim_hero_cta2_btn');
        const simHeroCtas = document.getElementById('sim_hero_ctas');

        if (simCta1Btn) {
            if (heroCta1Text) {
                simCta1Btn.innerText = heroCta1Text;
                simCta1Btn.style.display = 'inline-flex';
            } else {
                simCta1Btn.style.display = 'none';
            }
        }

        if (simCta2Btn) {
            if (heroCta2Text) {
                simCta2Btn.innerText = heroCta2Text;
                simCta2Btn.style.display = 'inline-flex';
            } else {
                simCta2Btn.style.display = 'none';
            }
        }

        if (simHeroCtas) {
            if (!heroCta1Text && !heroCta2Text) {
                simHeroCtas.style.display = 'none';
            } else {
                simHeroCtas.style.display = 'flex';
            }
        }

        // Set filters on background element
        simHeroBg.style.filter = `brightness(${visualBrightness}) saturate(${visualSaturation}) blur(${visualBlur}px)`;

        // Map size heights
        let minHeight = '120px';
        let padding = '24px 12px';
        if (heroLayoutMode === 'grand') {
            minHeight = '180px';
            padding = '36px 12px';
        } else if (heroLayoutMode === 'compact') {
            minHeight = '90px';
            padding = '14px 12px';
        } else if (heroLayoutMode === 'plein') {
            minHeight = '240px';
            padding = '48px 12px';
        }
        simHero.style.minHeight = minHeight;
        simHero.style.padding = padding;

        if (heroVariant !== 'hero_slider') {
            // Text content
            if (simHeroTitle) simHeroTitle.innerHTML = heroTitle;
            if (simHeroSubtitle) simHeroSubtitle.innerHTML = heroSubtitle;
            if (simHeroBadge) {
                if (heroBadge) {
                    simHeroBadge.innerText = heroBadge;
                    simHeroBadge.style.display = 'inline-flex';
                } else {
                    simHeroBadge.style.display = 'none';
                }
            }

            // Horizontal text alignment
            if (simHeroText) {
                simHeroText.style.textAlign = heroTextAlignment;
                let alignFlex = 'flex-start';
                let marginCss = '0 auto 0 0';
                if (heroTextAlignment === 'center') {
                    alignFlex = 'center';
                    marginCss = '0 auto';
                } else if (heroTextAlignment === 'right') {
                    alignFlex = 'flex-end';
                    marginCss = '0 0 0 auto';
                }
                simHeroText.style.alignItems = alignFlex;
                simHeroText.style.margin = marginCss;
                simHeroText.style.maxWidth = heroTextWidth;
            }

            // Vertical positioning
            let verticalAlign = 'center';
            if (heroTextPosition === 'haut') {
                verticalAlign = 'flex-start';
            } else if (heroTextPosition === 'bas') {
                verticalAlign = 'flex-end';
            }
            if (simHero) simHero.style.alignItems = verticalAlign;

            // Grid column sorting
            if (simHeroGrid) {
                if (heroImageLayout === 'left') {
                    simHeroGrid.style.gridTemplateColumns = '0.9fr 1.1fr';
                    const textBlock = document.getElementById('sim_hero_text_block');
                    const visualBlock = document.getElementById('sim_hero_visual');
                    if (textBlock) textBlock.style.order = '2';
                    if (visualBlock) visualBlock.style.order = '1';
                } else {
                    const textBlock = document.getElementById('sim_hero_text_block');
                    const visualBlock = document.getElementById('sim_hero_visual');
                    if (textBlock) textBlock.style.order = '1';
                    if (visualBlock) visualBlock.style.order = '2';
                }
            }
        }

        // Shadow strength on simulated texts
        if (simHeroTitle) simHeroTitle.style.textShadow = textShadowCss;
        if (simHeroSubtitle) simHeroSubtitle.style.textShadow = textShadowCss;

        // Visual image in mockups
        const mockupScreen = document.getElementById('sim_hero_mockup_screen');
        if (mockupScreen && heroVariant !== 'hero_slider') {
            mockupScreen.style.backgroundImage = heroImgUrl ? `url('${heroImgUrl}')` : 'none';
            mockupScreen.style.filter = `brightness(${visualBrightness}) saturate(${visualSaturation}) blur(${visualBlur}px)`;
        }

        // Mobile layout overrides in simulator view
        if (isMobile) {
            simHeroGrid.style.gridTemplateColumns = '1fr';
            simHeroVisual.style.display = 'none';
            simHeader.style.display = document.getElementById('mobile_header_visible').checked ? 'flex' : 'none';
            
            const mobLogoSize = parseInt(document.getElementById('mobile_logo_size').value);
            if (mobLogoSize) {
                simLogo.style.height = `${Math.min(18, mobLogoSize / 2)}px`;
            }

            const mobAlign = document.getElementById('mobile_hero_text_align').value;
            if (mobAlign) {
                simHeroText.style.textAlign = mobAlign;
                let mAlignFlex = 'center';
                let mMargin = '0 auto';
                if (mobAlign === 'left') { mAlignFlex = 'flex-start'; mMargin = '0 auto 0 0'; }
                else if (mobAlign === 'right') { mAlignFlex = 'flex-end'; mMargin = '0 0 0 auto'; }
                simHeroText.style.alignItems = mAlignFlex;
                simHeroText.style.margin = mMargin;
            }

            const mobPos = document.getElementById('mobile_hero_text_pos').value;
            if (mobPos) {
                simHero.style.alignItems = (mobPos === 'haut') ? 'flex-start' : ((mobPos === 'bas') ? 'flex-end' : 'center');
            }
        } else {
            simHeader.style.display = 'flex';
        }

        // 4. Update the hidden responsive_settings input
        serializeResponsiveSettings();
    }

    function serializeResponsiveSettings() {
        const settings = {
            mobile: {
                header_visible: document.getElementById('mobile_header_visible').checked,
                logo_size: document.getElementById('mobile_logo_size').value ? parseInt(document.getElementById('mobile_logo_size').value) : null,
                hero_text_position: document.getElementById('mobile_hero_text_pos').value || null,
                hero_text_alignment: document.getElementById('mobile_hero_text_align').value || null,
                hero_layout_mode: document.getElementById('mobile_hero_text_align').value ? 'compact' : null
            },
            tablet: {
                logo_size: null,
                hero_text_position: null,
                hero_text_alignment: null
            },
            visual: {
                brightness: parseFloat(document.getElementById('visual_brightness').value),
                saturation: parseFloat(document.getElementById('visual_saturation').value),
                blur: parseInt(document.getElementById('visual_blur').value)
            }
        };

        document.getElementById('responsive_settings_input').value = JSON.stringify(settings);
    }

    function setSimulatorBreakpoint(mode) {
        const viewport = document.getElementById('simulator_viewport');
        const desktopBtn = document.getElementById('sim_btn_desktop');
        const mobileBtn = document.getElementById('sim_btn_mobile');

        if (mode === 'desktop') {
            viewport.classList.remove('sim-viewport-mobile');
            viewport.classList.add('sim-viewport-desktop');
            desktopBtn.classList.add('active');
            desktopBtn.style.background = 'white';
            mobileBtn.classList.remove('active');
            mobileBtn.style.background = 'transparent';
        } else {
            viewport.classList.remove('sim-viewport-desktop');
            viewport.classList.add('sim-viewport-mobile');
            mobileBtn.classList.add('active');
            mobileBtn.style.background = 'white';
            desktopBtn.classList.remove('active');
            desktopBtn.style.background = 'transparent';
        }
        
        updateSimulator();
    }

    document.addEventListener("DOMContentLoaded", function() {
        // Initialize simulator
        updateSimulator();

        // Listen to changes on any form input/select/textarea dynamically to update preview
        const mainForm = document.querySelector('form');
        if (mainForm) {
            mainForm.addEventListener('input', updateSimulator);
            mainForm.addEventListener('change', updateSimulator);
        }

        // Attach listeners specifically to standard inputs/visual triggers
        document.querySelectorAll('.visual-simulator-trigger').forEach(input => {
            input.addEventListener('input', updateSimulator);
            input.addEventListener('change', updateSimulator);
        });
        
        const standardInputs = ['hero_title', 'hero_subtitle', 'hero_badge', 'hero_bg_color', 'hero_cta1_text', 'hero_cta2_text', 'hero_cta1_url', 'hero_cta2_url'];
        standardInputs.forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('input', updateSimulator);
                el.addEventListener('change', updateSimulator);
            }
        });

        // Track image fields by polling or observing mutations on value
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === "attributes" && mutation.attributeName === "value") {
                    updateSimulator();
                }
            });
        });
        
        const mediaInputs = ['hero_image', 'hero_image_mobile', 'logo_light', 'logo_dark'];
        mediaInputs.forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                observer.observe(el, { attributes: true });
                el.addEventListener('change', updateSimulator);
            }
        });
    });
</script>
