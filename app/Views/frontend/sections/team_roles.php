<section class="team-section section-padding" id="team-roles" style="background: var(--bg-surface-alt); border-top: 1px solid var(--border);">
    <div class="container team-inner">
        
        <div class="team-header reveal" style="text-align: center; margin-bottom: 4rem;">
            <span class="section-tag" style="display: inline-flex; align-items: center; gap: 8px; font-size: 0.68rem; letter-spacing: 0.3em; text-transform: uppercase; color: var(--primary); margin-bottom: 1rem;"><?= htmlspecialchars($single['tag'] ?? 'Notre Équipe') ?></span>
            <h2 class="section-title" style="color: var(--text-main); font-family: var(--font-heading);"><?= htmlspecialchars($single['title'] ?? 'Des experts à votre service') ?></h2>
        </div>

        <div class="team-roles" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px;">
            <?php if (!empty($groups)): ?>
                <?php foreach ($groups as $role): 
                    $hasLink = !empty($role['role_link']);
                    $cardTag = $hasLink ? 'a' : 'div';
                    $cardAttr = $hasLink ? 'href="' . htmlspecialchars($role['role_link']) . '" style="text-decoration:none;"' : '';
                ?>
                    <<?= $cardTag ?> <?= $cardAttr ?> class="role-card reveal" style="text-align: center; display: block; padding: 2.2rem 1.2rem; background: var(--bg-surface); border: 1px solid var(--border); border-radius: 12px; transition: var(--transition);">
                        <div class="role-avatar" style="width: 58px; height: 58px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.2rem auto; border: 2px solid var(--border); background: var(--primary-glow); color: var(--primary); overflow: hidden; padding: 12px;">
                            <?= \App\Helpers\IconHelper::render($role['role_avatar'] ?? 'user', ['image' => $role['role_image'] ?? '', 'size' => '24px']) ?>
                        </div>
                        <h3 class="role-title" style="font-size: 0.95rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.3rem; font-family: var(--font-heading);"><?= htmlspecialchars($role['role_title'] ?? '') ?></h3>
                        <div class="role-sub" style="font-size: 0.75rem; color: var(--text-muted); letter-spacing: 0.05em; font-weight: 500; text-transform: uppercase;"><?= htmlspecialchars($role['role_sub'] ?? '') ?></div>
                    </<?= $cardTag ?>>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>
</section>

<style>
.role-card:hover {
    border-color: rgba(99, 102, 241, 0.4) !important;
    transform: translateY(-3px);
}
</style>
