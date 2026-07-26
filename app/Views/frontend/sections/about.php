<section class="section-padding" id="about">
    <div class="container">
        <div class="about-split-grid">

            <!-- ── LEFT — Content ───────────────────────────────────── -->
            <div class="reveal">

                <span style="display:inline-flex;align-items:center;gap:8px;font-size:0.68rem;letter-spacing:0.22em;text-transform:uppercase;color:var(--primary);font-weight:700;font-family:var(--font-heading);margin-bottom:20px;">
                    <span style="width:24px;height:2px;background:var(--primary);border-radius:1px;display:inline-block;"></span>
                    <?= htmlspecialchars($single['tag'] ?? 'Qui Sommes-Nous') ?>
                </span>

                <h2 style="font-size:clamp(1.9rem,3.5vw,2.8rem);font-weight:800;letter-spacing:-0.03em;line-height:1.12;color:var(--text-main);margin-bottom:24px;font-family:var(--font-heading);">
                    <?= htmlspecialchars($single['title'] ?? '') ?>
                </h2>

                <p style="font-size:1rem;line-height:1.8;color:var(--text-muted);margin-bottom:36px;max-width:500px;">
                    <?= htmlspecialchars($single['description'] ?? '') ?>
                </p>

                <!-- Checkpoints -->
                <?php
                $hasChecks = false;
                for ($i = 1; $i <= 5; $i++) {
                    if (!empty($single['check_' . $i])) { $hasChecks = true; break; }
                }
                if ($hasChecks):
                ?>
                <div style="display:flex;flex-direction:column;gap:14px;margin-bottom:40px;">
                    <?php for ($i = 1; $i <= 5; $i++):
                        $ck = 'check_' . $i;
                        if (empty($single[$ck])) continue;
                    ?>
                    <div style="display:flex;align-items:center;gap:14px;">
                        <div style="width:26px;height:26px;border-radius:50%;background:rgba(13,148,136,0.1);border:1.5px solid rgba(13,148,136,0.25);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i data-lucide="check" style="width:13px;height:13px;color:#0d9488;"></i>
                        </div>
                        <span style="font-size:0.92rem;color:var(--text-sub);font-weight:500;"><?= htmlspecialchars($single[$ck]) ?></span>
                    </div>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>

            </div>

            <!-- ── RIGHT — Value Cards ──────────────────────────────── -->
            <div class="reveal" style="transition-delay:0.15s;">
                <?php if (!empty($groups)): ?>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                    <?php foreach ($groups as $j => $card): ?>
                    <div class="val-card" style="padding:28px 24px;<?= $j === 0 ? 'border-top:3px solid var(--primary);' : ($j === 1 ? 'border-top:3px solid var(--secondary);' : '') ?>">
                        <div style="width:44px;height:44px;border-radius:12px;background:rgba(13,148,136,0.09);border:1px solid rgba(13,148,136,0.18);display:flex;align-items:center;justify-content:center;color:var(--primary);margin-bottom:18px;">
                            <i data-lucide="<?= htmlspecialchars($card['val_icon'] ?? 'star') ?>" style="width:20px;height:20px;"></i>
                        </div>
                        <h4 style="font-size:0.95rem;font-weight:700;color:var(--text-main);margin-bottom:8px;font-family:var(--font-heading);line-height:1.3;">
                            <?= htmlspecialchars($card['val_title'] ?? '') ?>
                        </h4>
                        <p style="font-size:0.82rem;line-height:1.65;color:var(--text-muted);">
                            <?= htmlspecialchars($card['val_text'] ?? '') ?>
                        </p>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <!-- Fallback if no groups: decorative visual block -->
                <div style="background:linear-gradient(135deg,rgba(13,148,136,0.06) 0%,rgba(8,145,178,0.03) 100%);border:1px solid rgba(13,148,136,0.12);border-radius:24px;padding:60px 40px;text-align:center;">
                    <i data-lucide="layers" style="width:56px;height:56px;color:rgba(13,148,136,0.25);margin:0 auto 16px;display:block;"></i>
                    <p style="color:var(--text-muted);font-size:0.9rem;">Expertise &amp; Innovation</p>
                </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</section>
