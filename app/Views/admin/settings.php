<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
    .settings-grid-layout {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 28px;
        align-items: start;
    }
    @media (max-width: 1024px) {
        .settings-grid-layout {
            grid-template-columns: 1fr;
        }
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
        box-shadow: 0 30px 60px -15px rgba(99, 102, 241, 0.15), 0 10px 20px -5px rgba(0, 0, 0, 0.03);
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
        box-shadow: 0 8px 16px -4px rgba(79, 70, 229, 0.2);
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
        width: 80px;
        height: 80px;
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
        object-fit: contain;
        padding: 4px;
    }
</style>

<form action="/admin/settings" method="POST">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

    <div class="settings-grid-layout">
        <div>
            <!-- Branding Details -->
            <div class="card">
                <div class="card-header" style="margin-bottom: 24px;">
                    <h2 class="card-title">
                        <i data-lucide="sliders"></i>
                        <span>Branding &amp; Identité visuelle</span>
                    </h2>
                </div>

                <div class="admin-form-group">
                    <label for="site_name">Nom de l'agence / Site</label>
                    <input type="text" id="site_name" name="site_name" class="admin-input" value="<?= htmlspecialchars($settings['site_name'] ?? 'Digitalium Group') ?>" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="admin-form-group">
                        <label for="site_logo_text">Texte du Logo (Principal)</label>
                        <input type="text" id="site_logo_text" name="site_logo_text" class="admin-input" value="<?= htmlspecialchars($settings['site_logo_text'] ?? 'Digitalium') ?>">
                    </div>
                    <div class="admin-form-group">
                        <label for="site_logo_subtext">Sous-texte du Logo (Secondaire)</label>
                        <input type="text" id="site_logo_subtext" name="site_logo_subtext" class="admin-input" value="<?= htmlspecialchars($settings['site_logo_subtext'] ?? 'Group') ?>">
                    </div>
                </div>

                <!-- Favicon Selection -->
                <div class="admin-form-group">
                    <label>Favicon officiel (PNG / ICO / SVG)</label>
                    <?= \App\Helpers\MediaHelper::renderField('site_favicon', $settings['site_favicon'] ?? '', 'favicon') ?>
                </div>

                <!-- Desktop Logo -->
                <div class="admin-form-group">
                    <label>Logo Desktop principal</label>
                    <?= \App\Helpers\MediaHelper::renderField('site_logo', $settings['site_logo'] ?? '', 'logo') ?>
                </div>

                <!-- Mobile Logo -->
                <div class="admin-form-group">
                    <label>Logo Mobile principal</label>
                    <?= \App\Helpers\MediaHelper::renderField('site_logo_mobile', $settings['site_logo_mobile'] ?? '', 'logo_mobile') ?>
                </div>

                <div class="admin-form-group">
                    <label for="footer_pitch">Accroche corporative (Footer Pitch)</label>
                    <textarea id="footer_pitch" name="footer_pitch" class="admin-textarea" rows="3"><?= htmlspecialchars($settings['footer_pitch'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Header and Footer CTAs -->
            <div class="card">
                <div class="card-header" style="margin-bottom: 24px;">
                    <h2 class="card-title">
                        <i data-lucide="layout-template"></i>
                        <span>En-tête &amp; Bas de page (Header / Footer)</span>
                    </h2>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="admin-form-group">
                        <label for="header_cta_text">Texte du Bouton CTA Header</label>
                        <input type="text" id="header_cta_text" name="header_cta_text" class="admin-input" value="<?= htmlspecialchars($settings['header_cta_text'] ?? 'Discuter de mon projet') ?>">
                    </div>
                    <div class="admin-form-group">
                        <label for="header_cta_link">Lien du Bouton CTA Header</label>
                        <input type="text" id="header_cta_link" name="header_cta_link" class="admin-input" value="<?= htmlspecialchars($settings['header_cta_link'] ?? '/contact') ?>">
                    </div>
                </div>

                <div class="admin-form-group">
                    <label for="footer_copyright">Texte du Copyright du bas de page</label>
                    <input type="text" id="footer_copyright" name="footer_copyright" class="admin-input" value="<?= htmlspecialchars($settings['footer_copyright'] ?? '') ?>">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="admin-form-group">
                        <label for="footer_legal_text">Texte du Lien Légal (Ex: Mentions Légales)</label>
                        <input type="text" id="footer_legal_text" name="footer_legal_text" class="admin-input" value="<?= htmlspecialchars($settings['footer_legal_text'] ?? 'Mentions Légales') ?>">
                    </div>
                    <div class="admin-form-group">
                        <label for="footer_legal_url">URL du Lien Légal</label>
                        <input type="text" id="footer_legal_url" name="footer_legal_url" class="admin-input" value="<?= htmlspecialchars($settings['footer_legal_url'] ?? '/mentions-legales') ?>">
                    </div>
                </div>
            </div>

            <!-- Coordinates Details -->
            <div class="card">
                <div class="card-header" style="margin-bottom: 24px;">
                    <h2 class="card-title">
                        <i data-lucide="map-pin"></i>
                        <span>Coordonnées de l'agence</span>
                    </h2>
                </div>

                <div class="admin-form-group">
                    <label for="contact_address">Adresse physique</label>
                    <input type="text" id="contact_address" name="contact_address" class="admin-input" value="<?= htmlspecialchars($settings['contact_address'] ?? '') ?>">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="admin-form-group">
                        <label for="contact_phone">Téléphone</label>
                        <input type="text" id="contact_phone" name="contact_phone" class="admin-input" value="<?= htmlspecialchars($settings['contact_phone'] ?? '') ?>">
                    </div>
                    <div class="admin-form-group">
                        <label for="site_whatsapp">Numéro WhatsApp Officiel (Ex: 0101782919)</label>
                        <input type="text" id="site_whatsapp" name="site_whatsapp" class="admin-input" value="<?= htmlspecialchars($settings['site_whatsapp'] ?? '0101782919') ?>">
                    </div>
                </div>

                <div class="admin-form-group">
                    <label for="contact_email">E-mail de contact</label>
                    <input type="email" id="contact_email" name="contact_email" class="admin-input" value="<?= htmlspecialchars($settings['contact_email'] ?? '') ?>">
                </div>
            </div>
        </div>

        <div>
            <div class="card">
                <div class="card-header" style="margin-bottom: 24px;">
                    <h2 class="card-title">
                        <i data-lucide="share-2"></i>
                        <span>Réseaux sociaux</span>
                    </h2>
                </div>

                <div class="admin-form-group">
                    <label for="social_linkedin">LinkedIn URL</label>
                    <input type="url" id="social_linkedin" name="social_linkedin" class="admin-input" value="<?= htmlspecialchars($settings['social_linkedin'] ?? '') ?>" placeholder="https://linkedin.com/...">
                </div>

                <div class="admin-form-group">
                    <label for="social_twitter">Twitter / X URL</label>
                    <input type="url" id="social_twitter" name="social_twitter" class="admin-input" value="<?= htmlspecialchars($settings['social_twitter'] ?? '') ?>" placeholder="https://twitter.com/...">
                </div>

                <div class="admin-form-group">
                    <label for="social_github">GitHub URL</label>
                    <input type="url" id="social_github" name="social_github" class="admin-input" value="<?= htmlspecialchars($settings['social_github'] ?? '') ?>" placeholder="https://github.com/...">
                </div>
            </div>

            <div class="card" style="text-align: center;">
                <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; padding: 14px;">
                    <i data-lucide="save"></i>
                    <span>Enregistrer la configuration</span>
                </button>
            </div>
        </div>
    </div>
</form>


