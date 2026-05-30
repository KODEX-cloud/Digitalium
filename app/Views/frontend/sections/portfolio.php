<?php
use App\Models\Project;
$projectsList = Project::all('sort_order ASC, id DESC');

$categories = ['Tous'];
foreach ($projectsList as $proj) {
    if (!empty($proj['category']) && !in_array($proj['category'], $categories)) {
        $categories[] = $proj['category'];
    }
}
?>
<section class="section-padding" id="portfolio">
    <div class="container">
        
        <div class="section-header">
            <span class="section-badge">Réalisations</span>
            <h2 class="section-title"><?= htmlspecialchars($single['title'] ?? 'Nos Réalisations Digitales') ?></h2>
            <p class="section-subtitle"><?= htmlspecialchars($single['subtitle'] ?? 'Découvrez mes projets de transformation digitale pour des clients variés.') ?></p>
        </div>

        <?php if (count($categories) > 1): ?>
            <div class="portfolio-tabs" style="display: flex; justify-content: center; gap: 12px; margin-bottom: 48px; flex-wrap: wrap;">
                <?php foreach ($categories as $index => $cat): ?>
                    <button class="portfolio-tab-btn <?= $index === 0 ? 'active' : '' ?>" onclick="filterPortfolio('<?= htmlspecialchars($cat) ?>', this)" style="padding: 8px 22px; border-radius: 50px; font-size: 0.85rem; font-weight: 700; border: 1.5px solid var(--border); background: rgba(255,255,255,0.6); color: var(--text-muted); cursor: pointer; transition: var(--transition); font-family: var(--font-heading);">
                        <?= htmlspecialchars($cat) ?>
                    </button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="portfolio-grid" id="portfolioGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 32px;">
            <?php if (!empty($projectsList)): ?>
                <?php foreach ($projectsList as $project): ?>
                    <?php require APP_PATH . '/Views/frontend/partials/project_card.php'; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align: center; color: var(--text-muted); grid-column: 1/-1; padding: 50px 0;">
                    Aucun projet disponible pour le moment.
                </div>
            <?php endif; ?>
        </div>

    </div>
</section>

<style>
.portfolio-tab-btn:hover {
    color: var(--primary) !important;
    border-color: rgba(79, 70, 229, 0.25) !important;
    background: rgba(255,255,255,0.85) !important;
}
.portfolio-tab-btn.active {
    background: linear-gradient(135deg, var(--primary) 0%, #1e40af 100%) !important;
    color: white !important;
    border-color: transparent !important;
    box-shadow: 0 8px 20px -4px rgba(30, 58, 138, 0.25) !important;
}
</style>

<script>
    function filterPortfolio(category, btn) {
        document.querySelectorAll('.portfolio-tab-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        const items = document.querySelectorAll('.project-card');
        items.forEach(item => {
            const itemCat = item.getAttribute('data-category');
            if (category === 'Tous' || itemCat === category) {
                item.style.display = 'flex';
                item.style.opacity = '0';
                setTimeout(() => {
                    item.style.transition = 'opacity 0.4s ease';
                    item.style.opacity = '1';
                }, 50);
            } else {
                item.style.display = 'none';
            }
        });
    }
</script>
