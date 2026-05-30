<?php
/**
 * Global Reusable ProjectCard Component (Réalisations)
 * Aligned with the premium poster aesthetic.
 * Usage: Renders a single project card inside portfolio, home, services, case studies grids.
 */
if (isset($project)):
    $title = htmlspecialchars($project['title']);
    $category = htmlspecialchars($project['category']);
    $logo = htmlspecialchars($project['logo'] ?? '/assets/images/hero_3d.png');
    $mainImage = htmlspecialchars($project['main_image']);
    $context = htmlspecialchars($project['context'] ?? '');
    $impact = htmlspecialchars($project['impact'] ?? '');
    $techs = htmlspecialchars($project['technologies'] ?? '');
    $link = htmlspecialchars($project['external_link'] ?? '#');
    
    // Choose dynamic category styling to match the poster's custom colors
    $catColorClass = '';
    switch (strtolower($category)) {
        case 'politique':
            $catColor = 'background: rgba(124, 58, 237, 0.09); border: 1px solid rgba(124, 58, 237, 0.35); color: #7c3aed;';
            break;
        case 'institutionnel':
            $catColor = 'background: rgba(132, 204, 22, 0.09); border: 1px solid rgba(132, 204, 22, 0.35); color: #65a30d;';
            break;
        case 'médical':
            $catColor = 'background: rgba(30, 58, 138, 0.09); border: 1px solid rgba(30, 58, 138, 0.35); color: #1e3a8a;';
            break;
        case 'humanitaire':
            $catColor = 'background: rgba(16, 185, 129, 0.09); border: 1px solid rgba(16, 185, 129, 0.35); color: #059669;';
            break;
        case 'média digital':
        case 'media digital':
            $catColor = 'background: rgba(6, 182, 212, 0.09); border: 1px solid rgba(6, 182, 212, 0.35); color: #0891b2;';
            break;
        case 'e-commerce':
            $catColor = 'background: rgba(59, 130, 246, 0.09); border: 1px solid rgba(59, 130, 246, 0.35); color: #2563eb;';
            break;
        default:
            $catColor = 'background: rgba(79, 70, 229, 0.09); border: 1px solid rgba(79, 70, 229, 0.35); color: #4f46e5;';
            break;
    }
?>
<div class="project-card pc reveal" data-category="<?= $category ?>" style="background: rgba(255, 255, 255, 0.65); backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px); border: 1px solid rgba(255,255,255,0.7); border-radius: 24px; padding: 24px; display: flex; flex-direction: column; gap: 20px; box-shadow: 0 15px 35px -10px rgba(30, 58, 138, 0.05); transition: var(--transition); position: relative; overflow: visible;">
    
    <!-- Top-Left Floating Category Badge -->
    <span class="project-category-badge" style="position: absolute; top: 12px; left: 12px; z-index: 10; padding: 5px 14px; border-radius: 30px; font-size: 0.72rem; font-weight: 700; letter-spacing: 0.03em; font-family: var(--font-heading); <?= $catColor ?>">
        <?= $category ?>
    </span>

    <!-- Visual Mockup Area -->
    <div class="project-visual-container" style="position: relative; width: 100%; aspect-ratio: 1.5; border-radius: 16px; overflow: visible; background: #0f172a; border: 1px solid rgba(255, 255, 255, 0.4); box-shadow: 0 8px 24px -8px rgba(30, 58, 138, 0.1);">
        
        <!-- Laptop screen mockup image -->
        <img src="<?= $mainImage ?>" alt="<?= $title ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: 14px; display: block;" loading="lazy">
        
        <!-- Intersecting Client Brand Logo (overlaps bottom-left) -->
        <div class="project-client-logo-wrapper" style="position: absolute; bottom: -18px; left: 16px; z-index: 15; width: 56px; height: 56px; border-radius: 14px; background: white; border: 1.5px solid rgba(255,255,255,0.85); box-shadow: 0 10px 20px -5px rgba(30, 58, 138, 0.15); display: flex; align-items: center; justify-content: center; overflow: hidden; padding: 4px; transition: var(--transition);">
            <img src="<?= $logo ?>" alt="Logo <?= $title ?>" style="max-width: 100%; max-height: 100%; object-fit: contain; border-radius: 8px;">
        </div>

    </div>

    <!-- Text Details (Offset slightly to accommodate brand logo overlap) -->
    <div class="project-card-details" style="padding-top: 10px; display: flex; flex-direction: column; flex-grow: 1; gap: 14px;">
        
        <!-- Project Title -->
        <h3 class="project-card-title" style="font-size: 1.25rem; font-weight: 800; font-family: var(--font-heading); color: var(--text-main); margin: 0; line-height: 1.25;">
            <?= $title ?>
        </h3>

        <!-- Context & Impact Pills (Exact replicated layout) -->
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <span style="font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; padding: 4px 10px; border-radius: 6px; background: rgba(226, 109, 54, 0.09); border: 1.5px solid rgba(226, 109, 54, 0.25); color: var(--accent); font-family: var(--font-heading);">Contexte</span>
            <span style="font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; padding: 4px 10px; border-radius: 6px; background: rgba(124, 58, 237, 0.09); border: 1.5px solid rgba(124, 58, 237, 0.25); color: var(--secondary); font-family: var(--font-heading);">Impact</span>
        </div>

        <!-- Description Paragraphs -->
        <div style="display: flex; flex-direction: column; gap: 8px; flex-grow: 1;">
            <?php if (!empty($context)): ?>
                <p style="font-size: 0.88rem; line-height: 1.5; color: var(--text-muted); margin: 0;">
                    <?= $context ?>
                </p>
            <?php endif; ?>

            <?php if (!empty($impact)): ?>
                <p style="font-size: 0.85rem; line-height: 1.45; color: var(--text-main); font-weight: 600; margin: 0;">
                    <strong style="color: var(--secondary); font-weight: 700;">Impact:</strong> <?= $impact ?>
                </p>
            <?php endif; ?>
        </div>

        <!-- Technologies Used -->
        <?php if (!empty($techs)): ?>
            <div style="display: flex; flex-wrap: wrap; gap: 6px; align-items: center; border-top: 1px dashed var(--border); padding-top: 12px;">
                <i data-lucide="cpu" style="width: 14px; height: 14px; color: var(--primary);"></i>
                <span style="font-size: 0.72rem; font-family: monospace; font-weight: 600; color: var(--text-muted);"><?= $techs ?></span>
            </div>
        <?php endif; ?>

        <!-- External CTA Link Button -->
        <div style="margin-top: auto; padding-top: 4px;">
            <a href="<?= $link ?>" target="_blank" class="btn-card-cta" style="display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; padding: 10px 16px; border-radius: 10px; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; background: rgba(255,255,255,0.8); border: 1.5px solid var(--border); color: var(--primary); transition: var(--transition); box-shadow: 0 4px 10px rgba(30, 58, 138, 0.02);">
                <span>Voir le projet</span>
                <i data-lucide="arrow-up-right" style="width: 14px; height: 14px;"></i>
            </a>
        </div>

    </div>

</div>

<style>
.project-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 25px 50px -15px rgba(30, 58, 138, 0.15) !important;
    border-color: rgba(79, 70, 229, 0.25) !important;
}
.project-card:hover .project-client-logo-wrapper {
    transform: scale(1.1) rotate(-3deg);
    box-shadow: 0 15px 30px -8px rgba(30, 58, 138, 0.3) !important;
}
.btn-card-cta:hover {
    background: linear-gradient(135deg, var(--accent) 0%, #f97316 100%) !important;
    color: white !important;
    border-color: transparent !important;
    box-shadow: 0 8px 20px -4px rgba(226, 109, 54, 0.3) !important;
    transform: translateY(-1px);
}
</style>
<?php endif; ?>
