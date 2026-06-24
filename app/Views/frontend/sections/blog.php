<section class="section-padding" id="blog">
    <div class="container">
        
        <div class="section-header">
            <span class="section-badge">Blog</span>
            <h2 class="section-title"><?= htmlspecialchars($single['title'] ?? 'Actualités & Insights') ?></h2>
            <p class="section-subtitle"><?= htmlspecialchars($single['subtitle'] ?? 'Nos analyses techniques sur les architectures logicielles et les tendances cloud.') ?></p>
        </div>

        <div class="blog-grid">
            <?php if (!empty($groups)): ?>
                <?php foreach ($groups as $post): ?>
                    <div class="blog-card">
                        <div class="blog-img-box">
                            <?php if (!empty($post['post_image'])): ?>
                                <img src="<?= htmlspecialchars(url($post['post_image'])) ?>" alt="<?= htmlspecialchars($post['post_title'] ?? 'Article') ?>" loading="lazy">
                            <?php else: ?>
                                <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #111827 0%, #1f2937 100%); display:flex; align-items:center; justify-content:center;">
                                    <i data-lucide="book-open" style="width: 48px; height: 48px; color: var(--border);"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="blog-content">
                            <div>
                                <div class="blog-meta">
                                    <span><i data-lucide="tag" style="width: 12px; height: 12px; display:inline-block; vertical-align:middle; margin-right:4px;"></i><?= htmlspecialchars($post['post_category'] ?? 'Technologie') ?></span>
                                    <span><i data-lucide="calendar" style="width: 12px; height: 12px; display:inline-block; vertical-align:middle; margin-right:4px;"></i><?= htmlspecialchars($post['post_date'] ?? date('d.m.Y')) ?></span>
                                </div>
                                <h3 class="blog-title"><?= htmlspecialchars($post['post_title'] ?? 'Titre de l\'article') ?></h3>
                                <p class="blog-summary">
                                    <?= htmlspecialchars($post['post_summary'] ?? 'Résumé court de l\'article technique et de ses conclusions...') ?>
                                </p>
                            </div>
                            <a href="<?= htmlspecialchars(url($post['post_url'] ?? $post['post_link'] ?? '/blog')) ?>" class="blog-readmore">
                                <span>Lire la suite</span>
                                <i data-lucide="arrow-right" style="width: 14px; height: 14px;"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Clean default blog posts for display if none are created yet -->
                <div class="blog-card">
                    <div class="blog-img-box">
                        <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #1e1b4b 0%, #111827 100%); display:flex; align-items:center; justify-content:center;">
                            <i data-lucide="terminal" style="width: 48px; height: 48px; color: var(--primary);"></i>
                        </div>
                    </div>
                    <div class="blog-content">
                        <div>
                            <div class="blog-meta">
                                <span>PHP 8.1+</span>
                                <span>28 Mai 2026</span>
                            </div>
                            <h3 class="blog-title">Pourquoi migrer vers une architecture PHP 8.1+ typée ?</h3>
                            <p class="blog-summary">Découvrez comment les types d'intersection, les propriétés en lecture seule (readonly) et les enums changent l'ingénierie PHP.</p>
                        </div>
                        <a href="<?= url('/blog') ?>" class="blog-readmore">
                            <span>Lire la suite</span>
                            <i data-lucide="arrow-right" style="width: 14px; height: 14px;"></i>
                        </a>
                    </div>
                </div>

                <div class="blog-card">
                    <div class="blog-img-box">
                        <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #311042 0%, #111827 100%); display:flex; align-items:center; justify-content:center;">
                            <i data-lucide="database" style="width: 48px; height: 48px; color: var(--secondary);"></i>
                        </div>
                    </div>
                    <div class="blog-content">
                        <div>
                            <div class="blog-meta">
                                <span>Base de données</span>
                                <span>15 Mai 2026</span>
                            </div>
                            <h3 class="blog-title">Optimisation de MySQL pour les fortes charges applicatives</h3>
                            <p class="blog-summary">Stratégies d'indexation avancées, gestion des pools de connexions PDO et mise en cache des requêtes redondantes.</p>
                        </div>
                        <a href="<?= url('/blog') ?>" class="blog-readmore">
                            <span>Lire la suite</span>
                            <i data-lucide="arrow-right" style="width: 14px; height: 14px;"></i>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

    </div>
</section>
