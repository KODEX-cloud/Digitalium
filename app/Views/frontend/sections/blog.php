<section class="section-padding" id="blog">
    <div class="container">

        <div class="section-header reveal">
            <span class="section-badge">Blog</span>
            <h2 class="section-title"><?= htmlspecialchars($single['title'] ?? 'Actualités & Insights') ?></h2>
            <?php if (!empty($single['subtitle'])): ?>
                <p class="section-subtitle"><?= htmlspecialchars($single['subtitle']) ?></p>
            <?php endif; ?>
        </div>

        <div class="blog-grid">
            <?php if (!empty($groups)): ?>
                <?php foreach ($groups as $i => $post): ?>
                    <div class="blog-card reveal" style="transition-delay:<?= $i * 0.1 ?>s;">
                        <div class="blog-img-box" style="border-radius:var(--radius-xl) var(--radius-xl) 0 0;">
                            <?php if (!empty($post['post_image'])): ?>
                                <img src="<?= htmlspecialchars(url($post['post_image'])) ?>" alt="<?= htmlspecialchars($post['post_title'] ?? 'Article') ?>" loading="lazy">
                            <?php else: ?>
                                <div style="width:100%;height:100%;background:linear-gradient(135deg,rgba(99,102,241,0.15) 0%,rgba(139,92,246,0.08) 100%);display:flex;align-items:center;justify-content:center;">
                                    <i data-lucide="book-open" style="width:44px;height:44px;color:rgba(99,102,241,0.4);"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="blog-content">
                            <div class="blog-meta">
                                <span><i data-lucide="tag" style="width:11px;height:11px;display:inline-block;vertical-align:middle;margin-right:4px;"></i><?= htmlspecialchars($post['post_category'] ?? 'Technologie') ?></span>
                                <span><i data-lucide="calendar" style="width:11px;height:11px;display:inline-block;vertical-align:middle;margin-right:4px;"></i><?= htmlspecialchars($post['post_date'] ?? date('d.m.Y')) ?></span>
                            </div>
                            <h3 class="blog-title"><?= htmlspecialchars($post['post_title'] ?? 'Titre de l\'article') ?></h3>
                            <p class="blog-summary"><?= htmlspecialchars($post['post_summary'] ?? '') ?></p>
                            <a href="<?= htmlspecialchars(url($post['post_url'] ?? $post['post_link'] ?? '/blog')) ?>" class="blog-readmore">
                                <span>Lire la suite</span>
                                <i data-lucide="arrow-right" style="width:14px;height:14px;"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <?php
                $defaults = [
                    ['icon'=>'terminal','cat'=>'IA & Automatisation','date'=>'28 Juin 2026','title'=>'Solutions IA : automatiser vos processus','summary'=>'Comment déployer des solutions d\'IA sur mesure pour automatiser vos processus et stimuler la croissance.','url'=>'/blog'],
                    ['icon'=>'search','cat'=>'Marketing Digital','date'=>'15 Juin 2026','title'=>'SEO & réseaux sociaux : l\'IA au service de votre visibilité','summary'=>'Automatisez votre stratégie de contenu et renforcez votre présence multicanale grâce à l\'intelligence artificielle.','url'=>'/blog'],
                    ['icon'=>'layers','cat'=>'Transformation Digitale','date'=>'1 Juin 2026','title'=>'Transformation numérique : repenser l\'expérience client','summary'=>'Culture agile, modèles d\'affaires innovants et omniprésence en ligne — les clés d\'une transformation réussie.','url'=>'/blog'],
                ];
                foreach ($defaults as $i => $d):
                ?>
                    <div class="blog-card reveal" style="transition-delay:<?= $i * 0.1 ?>s;">
                        <div class="blog-img-box" style="border-radius:var(--radius-xl) var(--radius-xl) 0 0;">
                            <div style="width:100%;height:100%;background:linear-gradient(135deg,rgba(99,102,241,0.15) 0%,rgba(<?= $i === 1 ? '139,92,246' : ($i === 2 ? '34,211,238' : '99,102,241') ?>,0.08) 100%);display:flex;align-items:center;justify-content:center;">
                                <i data-lucide="<?= $d['icon'] ?>" style="width:44px;height:44px;color:rgba(99,102,241,0.4);"></i>
                            </div>
                        </div>
                        <div class="blog-content">
                            <div class="blog-meta">
                                <span><?= $d['cat'] ?></span>
                                <span><?= $d['date'] ?></span>
                            </div>
                            <h3 class="blog-title"><?= $d['title'] ?></h3>
                            <p class="blog-summary"><?= $d['summary'] ?></p>
                            <a href="<?= url($d['url']) ?>" class="blog-readmore">
                                <span>Lire la suite</span>
                                <i data-lucide="arrow-right" style="width:14px;height:14px;"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>
</section>
