<section class="section-padding" id="contact-details-sec" style="padding-top: 3rem;">
    <div class="container">

        <div class="main-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 40px; max-width: 1100px; margin: 0 auto;">

            <!-- LEFT COLUMN: Contact Form Card -->
            <div class="form-card reveal" style="background: var(--bg-surface); border: 1px solid var(--border); border-radius: 16px; padding: 2.5rem; transition: var(--transition);">
                <h2 class="card-title" style="font-size: 1.3rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.4rem; font-family: var(--font-heading);"><?= htmlspecialchars($single['title'] ?? 'Envoyez-nous votre demande') ?></h2>
                <p class="card-sub" style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 2rem; line-height: 1.6;"><?= htmlspecialchars($single['subtitle'] ?? '') ?></p>

                <form id="mainContactForm" onsubmit="handleMainContactSubmit(event)">
                    <?= \App\Services\CSRF::field() ?>

                    <div id="mainContactStatus" style="display: none; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; font-size: 0.95rem;"></div>

                    <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 1rem;">
                        <div class="form-group" style="display: flex; flex-direction: column; gap: 6px;">
                            <label style="font-size: 0.72rem; letter-spacing: 0.1em; text-transform: uppercase; color: var(--text-muted); font-weight: 600;">Prénom *</label>
                            <input type="text" name="firstname" class="contact-input" placeholder="Jean" required style="width: 100%; padding: 0.75rem 1rem; background: var(--bg-surface-alt); border: 1px solid var(--border); border-radius: 7px; color: var(--text-main); font-size: 0.88rem; outline: none;">
                        </div>
                        <div class="form-group" style="display: flex; flex-direction: column; gap: 6px;">
                            <label style="font-size: 0.72rem; letter-spacing: 0.1em; text-transform: uppercase; color: var(--text-muted); font-weight: 600;">Nom *</label>
                            <input type="text" name="lastname" class="contact-input" placeholder="Kouassi" required style="width: 100%; padding: 0.75rem 1rem; background: var(--bg-surface-alt); border: 1px solid var(--border); border-radius: 7px; color: var(--text-main); font-size: 0.88rem; outline: none;">
                        </div>
                    </div>

                    <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 1rem;">
                        <div class="form-group" style="display: flex; flex-direction: column; gap: 6px;">
                            <label style="font-size: 0.72rem; letter-spacing: 0.1em; text-transform: uppercase; color: var(--text-muted); font-weight: 600;">Email *</label>
                            <input type="email" name="email" class="contact-input" placeholder="jean@entreprise.com" required style="width: 100%; padding: 0.75rem 1rem; background: var(--bg-surface-alt); border: 1px solid var(--border); border-radius: 7px; color: var(--text-main); font-size: 0.88rem; outline: none;">
                            <div class="error-msg" id="err-main-email" style="color: #f87171; font-size: 0.8rem; margin-top: 4px; display: none;"></div>
                        </div>
                        <div class="form-group" style="display: flex; flex-direction: column; gap: 6px;">
                            <label style="font-size: 0.72rem; letter-spacing: 0.1em; text-transform: uppercase; color: var(--text-muted); font-weight: 600;">Téléphone / WhatsApp</label>
                            <input type="tel" name="phone" class="contact-input" placeholder="+225 07 00 00 00" style="width: 100%; padding: 0.75rem 1rem; background: var(--bg-surface-alt); border: 1px solid var(--border); border-radius: 7px; color: var(--text-main); font-size: 0.88rem; outline: none;">
                        </div>
                    </div>

                    <div class="form-group" style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 1rem;">
                        <label style="font-size: 0.72rem; letter-spacing: 0.1em; text-transform: uppercase; color: var(--text-muted); font-weight: 600;">Société / Organisation</label>
                        <input type="text" name="company" class="contact-input" placeholder="Nom de votre entreprise" style="width: 100%; padding: 0.75rem 1rem; background: var(--bg-surface-alt); border: 1px solid var(--border); border-radius: 7px; color: var(--text-main); font-size: 0.88rem; outline: none;">
                    </div>

                    <div class="form-group" style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 1.5rem;">
                        <label style="font-size: 0.72rem; letter-spacing: 0.1em; text-transform: uppercase; color: var(--text-muted); font-weight: 600;">Service principal souhaité *</label>
                        <select name="service_primary" style="width: 100%; padding: 0.75rem 1rem; background: var(--bg-surface-alt); border: 1px solid var(--border); border-radius: 7px; color: var(--text-main); font-size: 0.88rem; outline: none; cursor: pointer;">
                            <?php
                            $defaultPrimary = 'Web Design & Développement|Maintenance Informatique|Câblage Réseaux|Montage Vidéo TikTok / YouTube|Création de Contenu Digital|Intelligence Artificielle & Stratégie|Autre / Plusieurs services';
                            $primaryServices = array_filter(array_map('trim', explode('|', $single['services_primary_list'] ?? $defaultPrimary)));
                            foreach ($primaryServices as $i => $svc):
                            ?>
                                <option value="<?= htmlspecialchars($svc) ?>" <?= $i === 0 ? 'selected' : '' ?>><?= htmlspecialchars($svc) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group" style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 1.5rem;">
                        <label style="font-size: 0.72rem; letter-spacing: 0.1em; text-transform: uppercase; color: var(--text-muted); font-weight: 600;">Services additionnels d'intérêt</label>
                        <div class="services-check" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 0.5rem;">
                            <?php
                            $defaultExtra = 'Web Design|Maintenance TI|Câblage Réseau|Vidéo TikTok/YT|Contenu Digital|IA & Stratégie';
                            $extraServices = array_filter(array_map('trim', explode('|', $single['services_extra_list'] ?? $defaultExtra)));
                            foreach ($extraServices as $svc):
                            ?>
                                <label class="check-item" style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.82rem; color: var(--text-muted);">
                                    <input type="checkbox" name="services_interest[]" value="<?= htmlspecialchars($svc) ?>" style="accent-color: var(--primary);">
                                    <span><?= htmlspecialchars($svc) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="form-group" style="display: flex; flex-direction: column; gap: 6px; margin-bottom: 1.5rem;">
                        <label style="font-size: 0.72rem; letter-spacing: 0.1em; text-transform: uppercase; color: var(--text-muted); font-weight: 600;">Message *</label>
                        <textarea name="message" rows="5" class="contact-textarea" placeholder="Décrivez votre projet, vos objectifs ou vos questions..." required style="width: 100%; padding: 0.75rem 1rem; background: var(--bg-surface-alt); border: 1px solid var(--border); border-radius: 7px; color: var(--text-main); font-size: 0.88rem; outline: none; resize: vertical; min-height: 110px;"></textarea>
                        <div class="error-msg" id="err-main-message" style="color: #f87171; font-size: 0.8rem; margin-top: 4px; display: none;"></div>
                    </div>

                    <p class="form-legal" style="font-size: 0.72rem; color: var(--text-muted); line-height: 1.6; margin: 1rem 0;">En soumettant ce formulaire, vous acceptez que vos données soient traitées par <?= htmlspecialchars($settings['site_name'] ?? 'Digitalium Group') ?> pour répondre à votre demande. Consulter notre <a href="<?= htmlspecialchars(url($settings['footer_legal_url'] ?? '/mentions-legales')) ?>" style="color: var(--accent); text-decoration: none;">politique de confidentialité / Mentions Légales</a>.</p>

                    <!-- B-01 FIX: removed onclick="window.location.href=..." — the form onsubmit handler manages AJAX submission -->
                    <button type="submit" class="btn-contact-submit" id="mainSubmitBtn" style="padding: 14px; font-weight: 600; font-size: 0.95rem;">
                        <i data-lucide="send" style="width: 16px; height: 16px; display: inline-block; vertical-align: middle; margin-right: 8px;"></i>
                        <span><?= htmlspecialchars($single['cta_label'] ?? 'Planifier un entretien technique') ?></span>
                    </button>

                    <?php if (!empty($settings['site_whatsapp'])): ?>
                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $settings['site_whatsapp']) ?>" target="_blank" class="whatsapp-btn" style="width: 100%; padding: 0.85rem; margin-top: 0.8rem; background: transparent; color: #25d366; font-size: 0.82rem; font-weight: 600; letter-spacing: 0.15em; text-transform: uppercase; border: 1.5px solid #25d366; border-radius: 7px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none; transition: var(--transition);">
                        <i data-lucide="phone-call" style="width: 16px; height: 16px;"></i>
                        <span><?= htmlspecialchars($single['whatsapp_btn_label'] ?? 'Contacter via WhatsApp') ?></span>
                    </a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- RIGHT COLUMN: Coordonnées, Horaires & Map Card -->
            <div class="info-col" style="display: flex; flex-direction: column; gap: 20px;">

                <!-- Coordonnées Card -->
                <div class="info-card reveal" style="background: var(--bg-surface); border: 1px solid var(--border); border-radius: 16px; padding: 2rem;">
                    <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--text-main); margin-bottom: 1.5rem; font-family: var(--font-heading);"><?= htmlspecialchars($single['coordonnees_title'] ?? 'Nos coordonnées') ?></h3>
                    <div class="info-items" style="display: flex; flex-direction: column; gap: 20px;">
                        <div class="info-item" style="display: flex; align-items: flex-start; gap: 12px;">
                            <div class="info-item-icon" style="width: 40px; height: 40px; border-radius: 8px; display: flex; align-items: center; justify-content: center; background: var(--primary-glow); color: var(--primary); flex-shrink: 0;">
                                <i data-lucide="map-pin" style="width: 18px; height: 18px;"></i>
                            </div>
                            <div>
                                <div class="info-item-label" style="font-size: 0.68rem; letter-spacing: 0.1em; text-transform: uppercase; color: var(--text-muted); font-weight: 600;">Adresse</div>
                                <div class="info-item-value" style="font-size: 0.88rem; font-weight: 700; color: var(--text-main); font-family: var(--font-heading); white-space: pre-line;"><?= htmlspecialchars($single['contact_address'] ?? $settings['contact_address'] ?? '') ?></div>
                            </div>
                        </div>
                        <?php if (!empty($single['contact_phone'] ?? $settings['contact_phone'] ?? '')): ?>
                        <div class="info-item" style="display: flex; align-items: flex-start; gap: 12px;">
                            <div class="info-item-icon" style="width: 40px; height: 40px; border-radius: 8px; display: flex; align-items: center; justify-content: center; background: var(--primary-glow); color: var(--primary); flex-shrink: 0;">
                                <i data-lucide="phone" style="width: 18px; height: 18px;"></i>
                            </div>
                            <div>
                                <div class="info-item-label" style="font-size: 0.68rem; letter-spacing: 0.1em; text-transform: uppercase; color: var(--text-muted); font-weight: 600;">Téléphone</div>
                                <div class="info-item-value" style="font-size: 0.88rem; font-weight: 700; color: var(--text-main); font-family: var(--font-heading);">
                                    <a href="tel:<?= htmlspecialchars($single['contact_phone'] ?? $settings['contact_phone'] ?? '') ?>"><?= htmlspecialchars($single['contact_phone'] ?? $settings['contact_phone'] ?? '') ?></a>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($single['contact_email'] ?? $settings['contact_email'] ?? '')): ?>
                        <div class="info-item" style="display: flex; align-items: flex-start; gap: 12px;">
                            <div class="info-item-icon" style="width: 40px; height: 40px; border-radius: 8px; display: flex; align-items: center; justify-content: center; background: var(--primary-glow); color: var(--primary); flex-shrink: 0;">
                                <i data-lucide="mail" style="width: 18px; height: 18px;"></i>
                            </div>
                            <div>
                                <div class="info-item-label" style="font-size: 0.68rem; letter-spacing: 0.1em; text-transform: uppercase; color: var(--text-muted); font-weight: 600;">Email</div>
                                <div class="info-item-value" style="font-size: 0.88rem; font-weight: 700; color: var(--text-main); font-family: var(--font-heading);">
                                    <a href="mailto:<?= htmlspecialchars($single['contact_email'] ?? $settings['contact_email'] ?? '') ?>"><?= htmlspecialchars($single['contact_email'] ?? $settings['contact_email'] ?? '') ?></a>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Horaires Card -->
                <?php if (!empty($single['hours_desc'])): ?>
                <div class="info-card reveal" style="background: var(--bg-surface); border: 1px solid var(--border); border-radius: 16px; padding: 2rem;">
                    <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--text-main); margin-bottom: 1.2rem; font-family: var(--font-heading);"><?= htmlspecialchars($single['hours_title'] ?? "Horaires d'ouverture") ?></h3>
                    <div class="hours-grid" style="display: flex; flex-direction: column; gap: 10px;">
                        <?php
                        $hours = explode('|', $single['hours_desc'] ?? '');
                        foreach ($hours as $hour): if (trim($hour) !== ''):
                        ?>
                            <div class="hour-item" style="padding: 0.65rem 0.85rem; background: var(--bg-surface-alt); border-radius: 7px; border: 1px solid var(--border); font-size: 0.82rem; color: var(--text-main); display: flex; justify-content: space-between;">
                                <span><?= htmlspecialchars(trim($hour)) ?></span>
                            </div>
                        <?php endif; endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Réseaux sociaux Card -->
                <?php
                $hasSocial = !empty($settings['social_facebook']) || !empty($settings['social_linkedin']) || !empty($settings['social_twitter']) || !empty($settings['social_instagram']) || !empty($settings['social_github']);
                if ($hasSocial):
                ?>
                <div class="info-card reveal" style="background: var(--bg-surface); border: 1px solid var(--border); border-radius: 16px; padding: 2rem;">
                    <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.4rem; font-family: var(--font-heading);"><?= htmlspecialchars($single['social_section_title'] ?? 'Nous suivre') ?></h3>
                    <p style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 1.2rem;"><?= htmlspecialchars($single['social_section_subtitle'] ?? 'Restez informé de nos derniers projets, conseils digitaux et offres exclusives.') ?></p>
                    <div class="social-row" style="display: flex; gap: 10px; flex-wrap: wrap;">
                        <?php if (!empty($settings['social_facebook'])): ?>
                            <a href="<?= htmlspecialchars($settings['social_facebook']) ?>" target="_blank" class="social-btn" style="flex: 1; min-width: 90px; padding: 0.6rem 0.5rem; border: 1px solid var(--border); border-radius: 8px; display: flex; align-items: center; justify-content: center; gap: 6px; font-size: 0.72rem; font-weight: 600; color: var(--text-muted); text-decoration: none; transition: var(--transition);">
                                <i data-lucide="facebook" style="width: 14px; height: 14px;"></i>
                                <span>Facebook</span>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($settings['social_linkedin'])): ?>
                            <a href="<?= htmlspecialchars($settings['social_linkedin']) ?>" target="_blank" class="social-btn" style="flex: 1; min-width: 90px; padding: 0.6rem 0.5rem; border: 1px solid var(--border); border-radius: 8px; display: flex; align-items: center; justify-content: center; gap: 6px; font-size: 0.72rem; font-weight: 600; color: var(--text-muted); text-decoration: none; transition: var(--transition);">
                                <i data-lucide="linkedin" style="width: 14px; height: 14px;"></i>
                                <span>LinkedIn</span>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($settings['social_twitter'])): ?>
                            <a href="<?= htmlspecialchars($settings['social_twitter']) ?>" target="_blank" class="social-btn" style="flex: 1; min-width: 90px; padding: 0.6rem 0.5rem; border: 1px solid var(--border); border-radius: 8px; display: flex; align-items: center; justify-content: center; gap: 6px; font-size: 0.72rem; font-weight: 600; color: var(--text-muted); text-decoration: none; transition: var(--transition);">
                                <i data-lucide="twitter" style="width: 14px; height: 14px;"></i>
                                <span>Twitter / X</span>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($settings['social_instagram'])): ?>
                            <a href="<?= htmlspecialchars($settings['social_instagram']) ?>" target="_blank" class="social-btn" style="flex: 1; min-width: 90px; padding: 0.6rem 0.5rem; border: 1px solid var(--border); border-radius: 8px; display: flex; align-items: center; justify-content: center; gap: 6px; font-size: 0.72rem; font-weight: 600; color: var(--text-muted); text-decoration: none; transition: var(--transition);">
                                <i data-lucide="instagram" style="width: 14px; height: 14px;"></i>
                                <span>Instagram</span>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($settings['social_github'])): ?>
                            <a href="<?= htmlspecialchars($settings['social_github']) ?>" target="_blank" class="social-btn" style="flex: 1; min-width: 90px; padding: 0.6rem 0.5rem; border: 1px solid var(--border); border-radius: 8px; display: flex; align-items: center; justify-content: center; gap: 6px; font-size: 0.72rem; font-weight: 600; color: var(--text-muted); text-decoration: none; transition: var(--transition);">
                                <i data-lucide="github" style="width: 14px; height: 14px;"></i>
                                <span>GitHub</span>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Map card -->
                <?php if (!empty($single['contact_address'] ?? $settings['contact_address'] ?? '')): ?>
                <div class="map-card reveal" style="background: var(--bg-surface); border: 1px solid var(--border); border-radius: 16px; overflow: hidden; display: flex; flex-direction: column;">
                    <div class="map-placeholder" style="height: 160px; background: linear-gradient(135deg, var(--bg-surface-alt), var(--bg-surface)); display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px; border-bottom: 1px solid var(--border);">
                        <div class="map-pin" style="width: 36px; height: 36px; border-radius: 50% 50% 50% 0; transform: rotate(-45deg); background: var(--primary); display: flex; align-items: center; justify-content: center;">
                            <i data-lucide="map-pin" style="width: 16px; height: 16px; color: white; transform: rotate(45deg);"></i>
                        </div>
                        <span class="map-label" style="font-size: 0.78rem; color: var(--text-muted);"><?= htmlspecialchars($single['contact_address'] ?? $settings['contact_address'] ?? '') ?></span>
                    </div>
                    <div class="map-addr" style="padding: 1rem 1.2rem; font-size: 0.85rem; color: var(--text-main); font-weight: 700; display: flex; align-items: center; gap: 8px; font-family: var(--font-heading);">
                        <i data-lucide="building-2" style="width: 16px; height: 16px; color: var(--primary);"></i>
                        <span><?= htmlspecialchars($single['map_office_label'] ?? ($settings['site_name'] ?? 'Digitalium Group') . ' — Bureau principal') ?></span>
                    </div>
                </div>
                <?php endif; ?>

            </div>

        </div>

    </div>
</section>

<script>
async function handleMainContactSubmit(event) {
    event.preventDefault();

    const form = document.getElementById('mainContactForm');
    const statusDiv = document.getElementById('mainContactStatus');
    const submitBtn = document.getElementById('mainSubmitBtn');

    document.querySelectorAll('.error-msg').forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });

    statusDiv.style.display = 'none';
    statusDiv.className = '';
    statusDiv.textContent = '';

    const formData = new FormData(form);

    const name = (formData.get('firstname') || '') + ' ' + (formData.get('lastname') || '');
    formData.append('name', name.trim());

    const checkedInterests = [];
    form.querySelectorAll('input[name="services_interest[]"]:checked').forEach(cb => {
        checkedInterests.push(cb.value);
    });

    let originalMessage = formData.get('message') || '';
    const company = formData.get('company') || '';
    const servicePrimary = formData.get('service_primary') || '';

    if (checkedInterests.length > 0) {
        originalMessage = 'Services additionnels souhaités : ' + checkedInterests.join(', ') + '\n\n' + originalMessage;
    }
    originalMessage = 'Société : ' + company + ' | Service Principal : ' + servicePrimary + '\n\n' + originalMessage;

    formData.set('message', originalMessage);
    formData.set('subject', 'Demande de devis - ' + servicePrimary);

    const originalLabel = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.style.opacity = '0.7';
    submitBtn.textContent = 'Transmission en cours...';

    try {
        // B-01 FIX: use url() helper — ensures correct base path in subdirectory or root
        const response = await fetch('<?= url('/contact') ?>', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (response.ok && result.success) {
            statusDiv.style.display = 'block';
            statusDiv.style.backgroundColor = 'rgba(16, 185, 129, 0.15)';
            statusDiv.style.border = '1px solid rgba(16, 185, 129, 0.3)';
            statusDiv.style.color = '#34d399';
            statusDiv.textContent = result.message;
            form.reset();
        } else {
            statusDiv.style.display = 'block';
            statusDiv.style.backgroundColor = 'rgba(239, 68, 68, 0.15)';
            statusDiv.style.border = '1px solid rgba(239, 68, 68, 0.3)';
            statusDiv.style.color = '#f87171';
            statusDiv.textContent = result.message || 'Erreur lors de la validation des données.';

            if (result.errors) {
                for (const [field, msg] of Object.entries(result.errors)) {
                    const errEl = document.getElementById('err-main-' + field);
                    if (errEl) {
                        errEl.textContent = msg;
                        errEl.style.display = 'block';
                    }
                }
            }
        }
    } catch (error) {
        statusDiv.style.display = 'block';
        statusDiv.style.backgroundColor = 'rgba(239, 68, 68, 0.15)';
        statusDiv.style.border = '1px solid rgba(239, 68, 68, 0.3)';
        statusDiv.style.color = '#f87171';
        statusDiv.textContent = 'Une erreur réseau est survenue. Veuillez réessayer ultérieurement.';
    } finally {
        submitBtn.disabled = false;
        submitBtn.style.opacity = '1';
        submitBtn.innerHTML = originalLabel;
        lucide.createIcons();
    }
}
</script>

<style>
.social-btn:hover {
    border-color: rgba(37, 99, 235, 0.45) !important;
    color: var(--accent) !important;
    background: var(--primary-glow) !important;
}
.whatsapp-btn:hover {
    background: #25d366 !important;
    color: white !important;
}
</style>
