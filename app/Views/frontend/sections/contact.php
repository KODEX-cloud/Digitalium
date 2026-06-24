<section class="section-padding" id="contact">
    <div class="container">
        
        <div class="section-header">
            <span class="section-badge">Contact</span>
            <h2 class="section-title"><?= htmlspecialchars($single['title'] ?? 'Parlez-nous de Votre Projet') ?></h2>
            <p class="section-subtitle"><?= htmlspecialchars($single['subtitle'] ?? 'Prêt à concevoir un système performant ? Remplissez ce formulaire et notre architecte principal vous contactera.') ?></p>
        </div>

        <div class="contact-layout">
            
            <!-- Contact Sidebar details -->
            <div class="contact-details">
                <div class="contact-item">
                    <div class="contact-icon-box">
                        <i data-lucide="mail" style="width: 22px; height: 22px;"></i>
                    </div>
                    <div>
                        <h3 class="contact-item-title">E-mail</h3>
                        <p class="contact-item-value">
                            <a href="mailto:<?= htmlspecialchars($single['contact_email'] ?? $settings['contact_email'] ?? 'contact@digitaliumgroup.com') ?>">
                                <?= htmlspecialchars($single['contact_email'] ?? $settings['contact_email'] ?? 'contact@digitaliumgroup.com') ?>
                            </a>
                        </p>
                    </div>
                </div>

                <div class="contact-item">
                    <div class="contact-icon-box">
                        <i data-lucide="phone" style="width: 22px; height: 22px;"></i>
                    </div>
                    <div>
                        <h3 class="contact-item-title">Téléphone</h3>
                        <p class="contact-item-value">
                            <a href="tel:<?= htmlspecialchars($single['contact_phone'] ?? $settings['contact_phone'] ?? '0101782919') ?>">
                                <?= htmlspecialchars($single['contact_phone'] ?? $settings['contact_phone'] ?? '0101782919') ?>
                            </a>
                        </p>
                    </div>
                </div>

                <div class="contact-item">
                    <div class="contact-icon-box">
                        <i data-lucide="map-pin" style="width: 22px; height: 22px;"></i>
                    </div>
                    <div>
                        <h3 class="contact-item-title">Adresse</h3>
                        <p class="contact-item-value" style="white-space: pre-line;"><?= htmlspecialchars($single['contact_address'] ?? $settings['contact_address'] ?? "Paris, France") ?></p>
                    </div>
                </div>
            </div>

            <!-- Contact Form Card -->
            <div class="contact-form-card">
                <form id="contactForm" onsubmit="handleContactSubmit(event)">
                    <?= \App\Services\CSRF::field() ?>
                    
                    <div id="contactFormStatus" style="display: none; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; font-size: 0.95rem;"></div>

                    <div class="contact-form-row">
                        <div class="contact-form-group">
                            <label for="contactName">Nom complet <span style="color: var(--primary);">*</span></label>
                            <input type="text" id="contactName" name="name" class="contact-input" placeholder="Alexandre Dumas" required>
                            <div class="error-msg" id="err-name" style="color: #f87171; font-size: 0.8rem; margin-top: 4px; display: none;"></div>
                        </div>
                        <div class="contact-form-group">
                            <label for="contactEmail">Adresse e-mail <span style="color: var(--primary);">*</span></label>
                            <input type="email" id="contactEmail" name="email" class="contact-input" placeholder="alex@digitaliumgroup.com" required>
                            <div class="error-msg" id="err-email" style="color: #f87171; font-size: 0.8rem; margin-top: 4px; display: none;"></div>
                        </div>
                    </div>

                    <div class="contact-form-group">
                        <label for="contactSubject">Sujet</label>
                        <input type="text" id="contactSubject" name="subject" class="contact-input" placeholder="Conception d'une application sur mesure">
                    </div>

                    <div class="contact-form-group">
                        <label for="contactMessage">Message <span style="color: var(--primary);">*</span></label>
                        <textarea id="contactMessage" name="message" rows="5" class="contact-textarea" placeholder="Décrivez brièvement les objectifs de votre projet..." required></textarea>
                        <div class="error-msg" id="err-message" style="color: #f87171; font-size: 0.8rem; margin-top: 4px; display: none;"></div>
                    </div>

                    <button type="submit" class="btn-contact-submit" id="contactSubmitBtn" onclick="window.location.href='<?= url('/contact') ?>'">
                        <span><?= htmlspecialchars($single['cta_label'] ?? 'Planifier un entretien technique') ?></span>
                    </button>
                </form>
            </div>

        </div>
    </div>
</section>

<script>
async function handleContactSubmit(event) {
    event.preventDefault();

    const form = document.getElementById('contactForm');
    const statusDiv = document.getElementById('contactFormStatus');
    const submitBtn = document.getElementById('contactSubmitBtn');
    
    // Hide previous error messages
    document.querySelectorAll('.error-msg').forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });
    
    statusDiv.style.display = 'none';
    statusDiv.className = '';
    statusDiv.textContent = '';

    const formData = new FormData(form);
    
    submitBtn.disabled = true;
    submitBtn.style.opacity = '0.7';
    submitBtn.textContent = 'Transmission en cours...';

    try {
        const response = await fetch('/contact', {
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
                    const errEl = document.getElementById('err-' + field);
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
        submitBtn.textContent = '<?= htmlspecialchars($single['cta_label'] ?? 'Planifier un entretien technique') ?>';
    }
}
</script>
