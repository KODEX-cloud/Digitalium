<section class="section-padding" id="team">
    <div class="container">
        
        <div class="section-header">
            <span class="section-badge">Équipe</span>
            <h2 class="section-title"><?= htmlspecialchars($single['title'] ?? 'Nos Experts') ?></h2>
            <p class="section-subtitle"><?= htmlspecialchars($single['subtitle'] ?? 'Une équipe d\'ingénieurs d\'élite et de concepteurs chevronnés.') ?></p>
        </div>

        <div class="team-grid">
            <?php if (!empty($groups)): ?>
                <?php foreach ($groups as $member): 
                    $linkedin = $member['member_linkedin'] ?? '';
                    $twitter = $member['member_twitter'] ?? '';
                    $github = $member['member_github'] ?? '';
                ?>
                    <div class="team-card">
                        <div class="team-avatar-box">
                            <?php if (!empty($member['member_avatar'])): ?>
                                <img src="<?= htmlspecialchars($member['member_avatar']) ?>" alt="<?= htmlspecialchars($member['member_name'] ?? 'Membre') ?>" loading="lazy">
                            <?php else: ?>
                                <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #111827 0%, #1f2937 100%); display:flex; align-items:center; justify-content:center;">
                                    <i data-lucide="user" style="width: 64px; height: 64px; color: var(--border);"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="team-info">
                            <h3 class="team-name"><?= htmlspecialchars($member['member_name'] ?? 'Nom du Membre') ?></h3>
                            <p class="team-role"><?= htmlspecialchars($member['member_role'] ?? 'Consultant') ?></p>
                            
                            <?php if (!empty($linkedin) || !empty($twitter) || !empty($github)): ?>
                                <div class="team-socials">
                                    <?php if (!empty($linkedin)): ?>
                                        <a href="<?= htmlspecialchars($linkedin) ?>" class="team-social-link" target="_blank" aria-label="LinkedIn">
                                            <i data-lucide="linkedin" style="width: 18px; height: 18px;"></i>
                                        </a>
                                    <?php endif; ?>
                                    <?php if (!empty($twitter)): ?>
                                        <a href="<?= htmlspecialchars($twitter) ?>" class="team-social-link" target="_blank" aria-label="Twitter">
                                            <i data-lucide="twitter" style="width: 18px; height: 18px;"></i>
                                        </a>
                                    <?php endif; ?>
                                    <?php if (!empty($github)): ?>
                                        <a href="<?= htmlspecialchars($github) ?>" class="team-social-link" target="_blank" aria-label="GitHub">
                                            <i data-lucide="github" style="width: 18px; height: 18px;"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Default/Fallback team members if none seeded yet -->
                <div class="team-card">
                    <div class="team-avatar-box">
                        <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #1e1b4b 0%, #311042 100%); display:flex; align-items:center; justify-content:center;">
                            <i data-lucide="shield-check" style="width: 64px; height: 64px; color: var(--primary);"></i>
                        </div>
                    </div>
                    <div class="team-info">
                        <h3 class="team-name">Alexandre Dumas</h3>
                        <p class="team-role">Lead Architect & Fondateur</p>
                        <div class="team-socials">
                            <a href="#" class="team-social-link"><i data-lucide="linkedin" style="width: 18px; height: 18px;"></i></a>
                            <a href="#" class="team-social-link"><i data-lucide="github" style="width: 18px; height: 18px;"></i></a>
                        </div>
                    </div>
                </div>
                <div class="team-card">
                    <div class="team-avatar-box">
                        <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #1e1b4b 0%, #311042 100%); display:flex; align-items:center; justify-content:center;">
                            <i data-lucide="terminal" style="width: 64px; height: 64px; color: var(--primary);"></i>
                        </div>
                    </div>
                    <div class="team-info">
                        <h3 class="team-name">Thomas Morel</h3>
                        <p class="team-role">Expert Core PHP & SecOps</p>
                        <div class="team-socials">
                            <a href="#" class="team-social-link"><i data-lucide="linkedin" style="width: 18px; height: 18px;"></i></a>
                            <a href="#" class="team-social-link"><i data-lucide="github" style="width: 18px; height: 18px;"></i></a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

    </div>
</section>
