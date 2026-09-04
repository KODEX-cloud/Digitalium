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

<form action="<?= url('/admin/settings') ?>" method="POST">
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

                <!-- Light Logo -->
                <div class="admin-form-group">
                    <label>Logo Clair (pour fonds foncés / contrastes sombres)</label>
                    <?= \App\Helpers\MediaHelper::renderField('site_logo_light', $settings['site_logo_light'] ?? '', 'logo_light') ?>
                </div>

                <!-- Dark Logo -->
                <div class="admin-form-group">
                    <label>Logo Sombre (pour fonds clairs / contrastes clairs)</label>
                    <?= \App\Helpers\MediaHelper::renderField('site_logo_dark', $settings['site_logo_dark'] ?? '', 'logo_dark') ?>
                </div>

                <div class="admin-form-group">
                    <label for="footer_pitch">Accroche corporative (Footer Pitch)</label>
                    <textarea id="footer_pitch" name="footer_pitch" class="admin-textarea" rows="3"><?= htmlspecialchars($settings['footer_pitch'] ?? '') ?></textarea>
                </div>

                <div class="admin-form-group">
                    <label for="footer_slogan">Slogan de bas de page (Footer Slogan)</label>
                    <input type="text" id="footer_slogan" name="footer_slogan" class="admin-input" value="<?= htmlspecialchars($settings['footer_slogan'] ?? '') ?>" placeholder="Ex: L'innovation digitale par l'excellence technique.">
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
                        <label for="footer_cta_text">Texte du Bouton CTA Footer</label>
                        <input type="text" id="footer_cta_text" name="footer_cta_text" class="admin-input" value="<?= htmlspecialchars($settings['footer_cta_text'] ?? '') ?>" placeholder="Ex: Commencer mon projet">
                    </div>
                    <div class="admin-form-group">
                        <label for="footer_cta_link">Lien du Bouton CTA Footer</label>
                        <input type="text" id="footer_cta_link" name="footer_cta_link" class="admin-input" value="<?= htmlspecialchars($settings['footer_cta_link'] ?? '') ?>" placeholder="Ex: /contact">
                    </div>
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

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="admin-form-group">
                        <label for="footer_sitemap_text">Texte du lien Plan du site</label>
                        <input type="text" id="footer_sitemap_text" name="footer_sitemap_text" class="admin-input" value="<?= htmlspecialchars($settings['footer_sitemap_text'] ?? '') ?>" placeholder="Ex: Plan du site">
                    </div>
                    <div class="admin-form-group">
                        <label for="footer_sitemap_url">URL du Plan du site</label>
                        <input type="text" id="footer_sitemap_url" name="footer_sitemap_url" class="admin-input" value="<?= htmlspecialchars($settings['footer_sitemap_url'] ?? '/sitemap.xml') ?>">
                    </div>
                </div>

                <div class="admin-form-group">
                    <label for="footer_backtotop_text">Texte du lien « Remonter »</label>
                    <input type="text" id="footer_backtotop_text" name="footer_backtotop_text" class="admin-input" value="<?= htmlspecialchars($settings['footer_backtotop_text'] ?? '') ?>" placeholder="Ex: Remonter">
                </div>

                <h3 style="margin: 28px 0 16px; font-size: 1rem; font-weight: 700;">Titres des colonnes du footer</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                    <div class="admin-form-group">
                        <label for="footer_nav_title">Colonne Navigation</label>
                        <input type="text" id="footer_nav_title" name="footer_nav_title" class="admin-input" value="<?= htmlspecialchars($settings['footer_nav_title'] ?? '') ?>" placeholder="Ex: Liens utiles">
                    </div>
                    <div class="admin-form-group">
                        <label for="footer_services_title">Colonne Services</label>
                        <input type="text" id="footer_services_title" name="footer_services_title" class="admin-input" value="<?= htmlspecialchars($settings['footer_services_title'] ?? '') ?>" placeholder="Ex: Services">
                    </div>
                    <div class="admin-form-group">
                        <label for="footer_contact_title">Colonne Contact</label>
                        <input type="text" id="footer_contact_title" name="footer_contact_title" class="admin-input" value="<?= htmlspecialchars($settings['footer_contact_title'] ?? '') ?>" placeholder="Ex: Contact">
                    </div>
                </div>

                <h3 style="margin: 28px 0 16px; font-size: 1rem; font-weight: 700;">Panneau Newsletter du footer</h3>
                <div class="admin-form-group">
                    <label for="footer_newsletter_title">Titre du panneau</label>
                    <input type="text" id="footer_newsletter_title" name="footer_newsletter_title" class="admin-input" value="<?= htmlspecialchars($settings['footer_newsletter_title'] ?? '') ?>">
                </div>
                <div class="admin-form-group">
                    <label for="footer_newsletter_text">Texte d'accroche</label>
                    <input type="text" id="footer_newsletter_text" name="footer_newsletter_text" class="admin-input" value="<?= htmlspecialchars($settings['footer_newsletter_text'] ?? '') ?>">
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="admin-form-group">
                        <label for="footer_newsletter_placeholder">Texte indicatif du champ email</label>
                        <input type="text" id="footer_newsletter_placeholder" name="footer_newsletter_placeholder" class="admin-input" value="<?= htmlspecialchars($settings['footer_newsletter_placeholder'] ?? '') ?>" placeholder="Ex: Votre email">
                    </div>
                    <div class="admin-form-group">
                        <label for="footer_newsletter_button">Libellé du bouton</label>
                        <input type="text" id="footer_newsletter_button" name="footer_newsletter_button" class="admin-input" value="<?= htmlspecialchars($settings['footer_newsletter_button'] ?? '') ?>" placeholder="Ex: S'inscrire">
                    </div>
                </div>
                <div class="admin-form-group">
                    <label for="footer_newsletter_note">Mention sous le formulaire</label>
                    <input type="text" id="footer_newsletter_note" name="footer_newsletter_note" class="admin-input" value="<?= htmlspecialchars($settings['footer_newsletter_note'] ?? '') ?>" placeholder="Ex: Vous pouvez vous désabonner à tout moment. Consultez notre">
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="admin-form-group">
                        <label for="footer_newsletter_privacy_text">Texte du lien confidentialité</label>
                        <input type="text" id="footer_newsletter_privacy_text" name="footer_newsletter_privacy_text" class="admin-input" value="<?= htmlspecialchars($settings['footer_newsletter_privacy_text'] ?? '') ?>" placeholder="Ex: politique de confidentialité">
                    </div>
                    <div class="admin-form-group">
                        <label for="footer_newsletter_privacy_url">URL du lien confidentialité</label>
                        <input type="text" id="footer_newsletter_privacy_url" name="footer_newsletter_privacy_url" class="admin-input" value="<?= htmlspecialchars($settings['footer_newsletter_privacy_url'] ?? '') ?>" placeholder="Ex: /mentions-legales">
                    </div>
                </div>
                <div class="admin-form-group">
                    <label for="footer_newsletter_image">Illustration du panneau (chemin Médiathèque)</label>
                    <input type="text" id="footer_newsletter_image" name="footer_newsletter_image" class="admin-input" value="<?= htmlspecialchars($settings['footer_newsletter_image'] ?? '') ?>" placeholder="Ex: /assets/uploads/newsletter.png">
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
                    <label for="social_facebook">Facebook URL</label>
                    <input type="url" id="social_facebook" name="social_facebook" class="admin-input" value="<?= htmlspecialchars($settings['social_facebook'] ?? '') ?>" placeholder="https://facebook.com/...">
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
                    <label for="social_instagram">Instagram URL</label>
                    <input type="url" id="social_instagram" name="social_instagram" class="admin-input" value="<?= htmlspecialchars($settings['social_instagram'] ?? '') ?>" placeholder="https://instagram.com/...">
                </div>

                <div class="admin-form-group">
                    <label for="social_youtube">Youtube URL</label>
                    <input type="url" id="social_youtube" name="social_youtube" class="admin-input" value="<?= htmlspecialchars($settings['social_youtube'] ?? '') ?>" placeholder="https://youtube.com/...">
                </div>

                <div class="admin-form-group">
                    <label for="social_github">GitHub URL</label>
                    <input type="url" id="social_github" name="social_github" class="admin-input" value="<?= htmlspecialchars($settings['social_github'] ?? '') ?>" placeholder="https://github.com/...">
                </div>
            </div>

            <!-- Scripts & CSS -->
            <div class="card">
                <div class="card-header" style="margin-bottom: 24px;">
                    <h2 class="card-title">
                        <i data-lucide="code-2"></i>
                        <span>Scripts &amp; CSS personnalisés</span>
                    </h2>
                    <p style="font-size:0.8rem;color:var(--text-muted);margin-top:6px;">Injectez vos scripts de tracking (GTM, Analytics, Pixel) et styles CSS additionnels sans toucher au code.</p>
                </div>

                <div class="admin-form-group">
                    <label for="header_scripts">Scripts &lt;head&gt; (GTM, Analytics, Pixel Facebook...)</label>
                    <textarea id="header_scripts" name="header_scripts" class="admin-textarea" rows="5" placeholder="<!-- Google Tag Manager ou autres scripts head -->"><?= htmlspecialchars($settings['header_scripts'] ?? '') ?></textarea>
                </div>

                <div class="admin-form-group">
                    <label for="footer_scripts">Scripts avant &lt;/body&gt; (widgets, chat, scripts externes...)</label>
                    <textarea id="footer_scripts" name="footer_scripts" class="admin-textarea" rows="5" placeholder="<!-- Scripts footer: Intercom, HubSpot, etc. -->"><?= htmlspecialchars($settings['footer_scripts'] ?? '') ?></textarea>
                </div>

                <div class="admin-form-group">
                    <label for="custom_css">CSS personnalisé (typographies, overrides, thème personnalisé)</label>
                    <textarea id="custom_css" name="custom_css" class="admin-textarea" rows="6" placeholder="/* Ex: --font-heading: 'Montserrat', sans-serif; */"><?= htmlspecialchars($settings['custom_css'] ?? '') ?></textarea>
                    <p style="font-size:0.75rem;color:var(--text-muted);margin-top:6px;">Astuce : Pour changer la typographie, ajoutez d'abord le lien Google Fonts dans les scripts &lt;head&gt;, puis écrivez ici : <code>--font-heading: 'VotrePolice', sans-serif;</code></p>
                </div>
            </div>

            <!-- Colours & Theme -->
            <div class="card">
                <div class="card-header" style="margin-bottom: 24px;">
                    <h2 class="card-title">
                        <i data-lucide="palette"></i>
                        <span>Couleurs &amp; Thème</span>
                    </h2>
                    <p style="font-size:0.8rem;color:var(--text-muted);margin-top:6px;">Personnalisez les couleurs principales du site. Laissez vide pour utiliser les valeurs par défaut.</p>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="admin-form-group">
                        <label for="color_primary">Couleur Principale (--primary)</label>
                        <div style="display:flex;gap:10px;align-items:center;">
                            <input type="color" id="color_primary_picker" value="<?= htmlspecialchars($settings['color_primary'] ?? '#2563eb') ?>" style="width:44px;height:38px;padding:2px;border:1px solid var(--border);border-radius:8px;cursor:pointer;background:none;" oninput="document.getElementById('color_primary').value=this.value">
                            <input type="text" id="color_primary" name="color_primary" class="admin-input" value="<?= htmlspecialchars($settings['color_primary'] ?? '#2563eb') ?>" placeholder="#2563eb" oninput="document.getElementById('color_primary_picker').value=this.value">
                        </div>
                    </div>
                    <div class="admin-form-group">
                        <label for="color_accent">Couleur Accent (--accent)</label>
                        <div style="display:flex;gap:10px;align-items:center;">
                            <input type="color" id="color_accent_picker" value="<?= htmlspecialchars($settings['color_accent'] ?? '#f59e0b') ?>" style="width:44px;height:38px;padding:2px;border:1px solid var(--border);border-radius:8px;cursor:pointer;background:none;" oninput="document.getElementById('color_accent').value=this.value">
                            <input type="text" id="color_accent" name="color_accent" class="admin-input" value="<?= htmlspecialchars($settings['color_accent'] ?? '#f59e0b') ?>" placeholder="#f59e0b" oninput="document.getElementById('color_accent_picker').value=this.value">
                        </div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="admin-form-group">
                        <label for="color_text_main">Couleur Texte Principal (--text-main)</label>
                        <div style="display:flex;gap:10px;align-items:center;">
                            <input type="color" id="color_text_main_picker" value="<?= htmlspecialchars($settings['color_text_main'] ?? '#0f172a') ?>" style="width:44px;height:38px;padding:2px;border:1px solid var(--border);border-radius:8px;cursor:pointer;background:none;" oninput="document.getElementById('color_text_main').value=this.value">
                            <input type="text" id="color_text_main" name="color_text_main" class="admin-input" value="<?= htmlspecialchars($settings['color_text_main'] ?? '#0f172a') ?>" placeholder="#0f172a" oninput="document.getElementById('color_text_main_picker').value=this.value">
                        </div>
                    </div>
                    <div class="admin-form-group">
                        <label for="color_text_muted">Couleur Texte Secondaire (--text-muted)</label>
                        <div style="display:flex;gap:10px;align-items:center;">
                            <input type="color" id="color_text_muted_picker" value="<?= htmlspecialchars($settings['color_text_muted'] ?? '#64748b') ?>" style="width:44px;height:38px;padding:2px;border:1px solid var(--border);border-radius:8px;cursor:pointer;background:none;" oninput="document.getElementById('color_text_muted').value=this.value">
                            <input type="text" id="color_text_muted" name="color_text_muted" class="admin-input" value="<?= htmlspecialchars($settings['color_text_muted'] ?? '#64748b') ?>" placeholder="#64748b" oninput="document.getElementById('color_text_muted_picker').value=this.value">
                        </div>
                    </div>
                </div>

                <div class="admin-form-group">
                    <label for="color_bg_base">Couleur Fond de Page (--bg-base)</label>
                    <div style="display:flex;gap:10px;align-items:center;">
                        <input type="color" id="color_bg_base_picker" value="<?= htmlspecialchars($settings['color_bg_base'] ?? '#f0f4ff') ?>" style="width:44px;height:38px;padding:2px;border:1px solid var(--border);border-radius:8px;cursor:pointer;background:none;" oninput="document.getElementById('color_bg_base').value=this.value">
                        <input type="text" id="color_bg_base" name="color_bg_base" class="admin-input" value="<?= htmlspecialchars($settings['color_bg_base'] ?? '#f0f4ff') ?>" placeholder="#f0f4ff" oninput="document.getElementById('color_bg_base_picker').value=this.value">
                    </div>
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


