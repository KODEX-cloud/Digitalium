<?php
/**
 * Page détail d'un article — /insights/{slug}
 *
 * Rien n'est écrit ici : titre, auteur, date, catégorie, image et contenu
 * viennent du module Blog, et l'appel à l'action final vient de la
 * Configuration du site (clés insights_cta_*), donc modifiable sans toucher au
 * code (Règle #2).
 *
 * ── Sommaire automatique ────────────────────────────────────────────────────
 * Les titres <h2> et <h3> du contenu reçoivent un identifiant stable et
 * alimentent un sommaire. Il n'apparaît qu'à partir de trois titres : au-dessous,
 * un sommaire encombre plus qu'il n'aide.
 *
 * ── Partage ─────────────────────────────────────────────────────────────────
 * Quatre liens ordinaires, sans script tiers : rien qui piste le lecteur, rien
 * qui casse si un bloqueur est actif.
 */

use App\Models\Post;

$duree     = Post::dureeLecture($post);
$urlPublic = 'https://digitaliumgroup.com' . url('/insights/' . ($post['slug'] ?? ''));

/* ── Sommaire : on ancre les titres du contenu ────────────────────────────── */
$sommaire = [];
$contenu  = (string)($post['content'] ?? '');
$contenu  = preg_replace_callback(
    '#<h([23])(\s[^>]*)?>(.*?)</h\1>#is',
    static function (array $m) use (&$sommaire): string {
        $texte = trim(html_entity_decode(strip_tags($m[3]), ENT_QUOTES, 'UTF-8'));
        if ($texte === '') { return $m[0]; }

        $ancre = strtolower(trim(preg_replace('~[^a-z0-9]+~i', '-',
            (string)@iconv('utf-8', 'us-ascii//TRANSLIT', $texte)), '-'));
        if ($ancre === '') { $ancre = 'section'; }
        $ancre = 'a-' . $ancre . '-' . (count($sommaire) + 1);

        $sommaire[] = ['niveau' => (int)$m[1], 'texte' => $texte, 'ancre' => $ancre];

        $attrs = $m[2] ?? '';
        // Un id déjà posé par la rédaction fait foi : on ne le remplace pas.
        if (stripos($attrs, 'id=') !== false) {
            return $m[0];
        }
        return '<h' . $m[1] . $attrs . ' id="' . htmlspecialchars($ancre, ENT_QUOTES) . '">'
             . $m[3] . '</h' . $m[1] . '>';
    },
    $contenu
) ?? $contenu;

$afficheSommaire = count($sommaire) >= 3;

/* ── Appel à l'action final — Configuration du site ───────────────────────── */
$ctaTitre  = trim((string)($settings['insights_cta_title'] ?? ''));
$ctaTexte  = trim((string)($settings['insights_cta_text'] ?? ''));
$ctaBouton = trim((string)($settings['insights_cta_button'] ?? ''));
$ctaUrl    = trim((string)($settings['insights_cta_url'] ?? '')) ?: '/contact';

$partages = [
    ['linkedin', 'LinkedIn', 'https://www.linkedin.com/sharing/share-offsite/?url=' . rawurlencode($urlPublic)],
    ['twitter',  'X',        'https://twitter.com/intent/tweet?text=' . rawurlencode((string)($post['title'] ?? '')) . '&url=' . rawurlencode($urlPublic)],
    ['message-circle', 'WhatsApp', 'https://wa.me/?text=' . rawurlencode(($post['title'] ?? '') . ' — ' . $urlPublic)],
    ['mail', 'Email', 'mailto:?subject=' . rawurlencode((string)($post['title'] ?? '')) . '&body=' . rawurlencode($urlPublic)],
];
?>

<article class="art">

    <!-- ── En-tête ─────────────────────────────────────────────────────── -->
    <header class="art-head">
        <div class="container art-col">
            <nav class="art-breadcrumb" aria-label="Fil d'Ariane">
                <a href="<?= htmlspecialchars(url('/insights')) ?>">
                    <i data-lucide="arrow-left" style="width:14px;height:14px;"></i>
                    <span>Insights</span>
                </a>
                <?php if (!empty($post['category'])): ?>
                    <span class="art-sep" aria-hidden="true">/</span>
                    <a class="art-cat" href="<?= htmlspecialchars(url('/insights') . '?cat=' . urlencode($post['category'])) ?>">
                        <?= htmlspecialchars($post['category']) ?>
                    </a>
                <?php endif; ?>
            </nav>

            <h1 class="art-title"><?= htmlspecialchars($post['title'] ?? '') ?></h1>

            <?php if (!empty($post['excerpt'])): ?>
                <p class="art-lead"><?= htmlspecialchars($post['excerpt']) ?></p>
            <?php endif; ?>

            <div class="art-meta">
                <span class="art-author">
                    <span class="art-avatar" aria-hidden="true"><?= htmlspecialchars(strtoupper(substr((string)($post['author'] ?? 'D'), 0, 1))) ?></span>
                    <?= htmlspecialchars((string)($post['author'] ?? '')) ?>
                </span>
                <?php if (!empty($post['published_at'])): ?>
                    <time datetime="<?= htmlspecialchars(date('Y-m-d', strtotime($post['published_at']))) ?>">
                        <?= htmlspecialchars(date('d/m/Y', strtotime($post['published_at']))) ?>
                    </time>
                <?php endif; ?>
                <?php if ($duree > 0): ?>
                    <span><?= (int)$duree ?> min de lecture</span>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- ── Visuel principal ────────────────────────────────────────────── -->
    <?php if (!empty($post['featured_image'])): ?>
        <div class="container art-col">
            <figure class="art-figure">
                <img src="<?= htmlspecialchars(url($post['featured_image'])) ?>"
                     alt="<?= htmlspecialchars($post['title'] ?? '') ?>" decoding="async">
            </figure>
        </div>
    <?php endif; ?>

    <!-- ── Corps ───────────────────────────────────────────────────────── -->
    <div class="container art-body<?= $afficheSommaire ? ' has-toc' : '' ?>">

        <?php if ($afficheSommaire): ?>
            <aside class="art-toc">
                <nav aria-label="Sommaire de l'article">
                    <p class="art-toc-title">Sommaire</p>
                    <ol>
                        <?php foreach ($sommaire as $s): ?>
                            <li class="lvl-<?= (int)$s['niveau'] ?>">
                                <a href="#<?= htmlspecialchars($s['ancre'], ENT_QUOTES) ?>"><?= htmlspecialchars($s['texte']) ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                </nav>
            </aside>
        <?php endif; ?>

        <div class="art-main">
            <div class="blog-content"><?= $contenu ?></div>

            <?php if (!empty($postTags)): ?>
                <div class="art-tags">
                    <?php foreach ($postTags as $t): ?>
                        <a href="<?= htmlspecialchars(url('/insights') . '?q=' . urlencode($t['name'])) ?>">
                            <?= htmlspecialchars($t['name']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="art-share">
                <span class="art-share-label">Partager</span>
                <?php foreach ($partages as [$icone, $nom, $href]): ?>
                    <a href="<?= htmlspecialchars($href, ENT_QUOTES) ?>"
                       target="_blank" rel="noopener noreferrer nofollow"
                       aria-label="Partager sur <?= htmlspecialchars($nom, ENT_QUOTES) ?>"
                       title="<?= htmlspecialchars($nom, ENT_QUOTES) ?>">
                        <i data-lucide="<?= htmlspecialchars($icone) ?>" style="width:17px;height:17px;"></i>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- ── Appel à l'action ────────────────────────────────────────────── -->
    <?php if ($ctaTitre !== '' || $ctaBouton !== ''): ?>
        <section class="art-cta">
            <div class="container art-col">
                <div class="art-cta-panel">
                    <?php if ($ctaTitre !== ''): ?>
                        <h2><?= htmlspecialchars($ctaTitre) ?></h2>
                    <?php endif; ?>
                    <?php if ($ctaTexte !== ''): ?>
                        <p><?= htmlspecialchars($ctaTexte) ?></p>
                    <?php endif; ?>
                    <?php if ($ctaBouton !== ''): ?>
                        <a href="<?= htmlspecialchars(url($ctaUrl)) ?>">
                            <span><?= htmlspecialchars($ctaBouton) ?></span>
                            <i data-lucide="arrow-right" style="width:16px;height:16px;"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- ── Commentaires ────────────────────────────────────────────────── -->
    <section class="art-comments">
        <div class="container art-col">
            <h2 class="art-h2">
                <?= count($comments ?? []) ?> commentaire<?= count($comments ?? []) > 1 ? 's' : '' ?>
            </h2>

            <?php if (!empty($comments)): ?>
                <div class="art-comment-list">
                    <?php foreach ($comments as $c): ?>
                        <div class="art-comment">
                            <div class="art-comment-head">
                                <span class="art-avatar" aria-hidden="true"><?= htmlspecialchars(strtoupper(substr((string)$c['author_name'], 0, 1))) ?></span>
                                <div>
                                    <strong><?= htmlspecialchars($c['author_name']) ?></strong>
                                    <time datetime="<?= htmlspecialchars(date('Y-m-d', strtotime($c['created_at']))) ?>">
                                        <?= htmlspecialchars(date('d/m/Y', strtotime($c['created_at']))) ?>
                                    </time>
                                </div>
                            </div>
                            <p><?= nl2br(htmlspecialchars($c['content'])) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="art-comment-form">
                <h3>Laisser un commentaire</h3>
                <form id="commentForm" data-post-id="<?= (int)$post['id'] ?>">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES) ?>">
                    <div class="art-field-row">
                        <div>
                            <label for="cf-name">Nom *</label>
                            <input type="text" id="cf-name" name="author_name" required placeholder="Votre nom">
                        </div>
                        <div>
                            <label for="cf-email">Email</label>
                            <input type="email" id="cf-email" name="author_email" placeholder="votre@email.com">
                        </div>
                    </div>
                    <div>
                        <label for="cf-content">Commentaire *</label>
                        <textarea id="cf-content" name="content" required rows="5" placeholder="Votre commentaire…"></textarea>
                    </div>
                    <button type="submit">
                        <i data-lucide="send" style="width:15px;height:15px;"></i>
                        <span>Envoyer le commentaire</span>
                    </button>
                    <div id="commentResult" hidden></div>
                </form>
            </div>
        </div>
    </section>

    <!-- ── Articles similaires ─────────────────────────────────────────── -->
    <?php if (!empty($related)): ?>
        <section class="art-related">
            <div class="container">
                <h2 class="art-h2">Articles similaires</h2>
                <div class="art-related-grid">
                    <?php foreach ($related as $r): ?>
                        <a class="art-related-card" href="<?= htmlspecialchars(url('/insights/' . $r['slug'])) ?>">
                            <?php if (!empty($r['featured_image'])): ?>
                                <span class="art-related-media">
                                    <img src="<?= htmlspecialchars(url($r['featured_image'])) ?>" alt="" loading="lazy" decoding="async">
                                </span>
                            <?php endif; ?>
                            <span class="art-related-body">
                                <?php if (!empty($r['category'])): ?>
                                    <span class="art-related-cat"><?= htmlspecialchars($r['category']) ?></span>
                                <?php endif; ?>
                                <strong><?= htmlspecialchars($r['title']) ?></strong>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

</article>

<?php
/* Données structurées Article — permet l'affichage enrichi dans les résultats
   de recherche. Encodé par json_encode, jamais concaténé à la main.

   ATTENTION aux drapeaux : à l'intérieur d'un <script>, le navigateur cherche
   la chaîne « </script> » AVANT d'interpréter le JSON. Un titre d'article
   contenant cette chaîne refermerait donc la balise, et tout ce qui suit
   s'exécuterait comme du HTML — une injection permanente déclenchée pour chaque
   visiteur. JSON_HEX_TAG encode < et > en < / >, et l'absence de
   JSON_UNESCAPED_SLASHES échappe la barre oblique : « </script> » ne peut plus
   apparaître en clair. La relecture du JSON, elle, est inchangée. */
$jsonLd = [
    '@context'      => 'https://schema.org',
    '@type'         => 'Article',
    'headline'      => (string)($post['title'] ?? ''),
    'description'   => (string)($post['meta_description'] ?: ($post['excerpt'] ?? '')),
    'author'        => ['@type' => 'Person', 'name' => (string)($post['author'] ?? '')],
    'publisher'     => [
        '@type' => 'Organization',
        'name'  => (string)($settings['site_name'] ?? 'Digitalium Group'),
    ],
    'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $urlPublic],
    'url'           => $urlPublic,
];
if (!empty($post['published_at'])) {
    $jsonLd['datePublished'] = date('c', strtotime($post['published_at']));
}
if (!empty($post['updated_at'])) {
    $jsonLd['dateModified'] = date('c', strtotime($post['updated_at']));
}
if (!empty($post['featured_image'])) {
    $jsonLd['image'] = 'https://digitaliumgroup.com' . url($post['featured_image']);
}
if (!empty($post['category'])) {
    $jsonLd['articleSection'] = (string)$post['category'];
}
?>
<script type="application/ld+json"><?= json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>

<script>
document.getElementById('commentForm')?.addEventListener('submit', async function (e) {
    e.preventDefault();
    const btn = this.querySelector('button[type=submit]');
    const result = document.getElementById('commentResult');
    btn.disabled = true;
    const data = new URLSearchParams(new FormData(this));
    data.append('post_id', this.dataset.postId);
    try {
        const r = await fetch('<?= url('/blog/comment') ?>', { method: 'POST', body: data });
        const json = await r.json();
        result.hidden = false;
        result.style.color = json.success ? 'var(--primary)' : '#b91c1c';
        result.textContent = json.message;
        if (json.success) this.reset();
    } catch {
        result.hidden = false;
        result.textContent = 'Erreur. Réessayez plus tard.';
    }
    btn.disabled = false;
});
</script>

<style>
.art { background: var(--bg-base); }
.art-col { max-width: 820px; }
.art-head { padding: 130px 0 42px; }
.art-breadcrumb { display: flex; align-items: center; gap: 10px; margin-bottom: 20px; font-size: 0.79rem; }
.art-breadcrumb a { display: inline-flex; align-items: center; gap: 6px; color: var(--text-muted); text-decoration: none; }
.art-breadcrumb a:hover { color: var(--primary); }
.art-breadcrumb .art-cat { color: var(--primary); font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; }
.art-sep { color: var(--border); }

.art-title {
    margin: 0 0 18px;
    font-family: var(--font-heading);
    font-size: clamp(1.9rem, 4vw, 2.9rem);
    font-weight: 850; line-height: 1.16; letter-spacing: -0.02em;
    color: var(--text-main);
}
.art-lead { margin: 0 0 26px; font-size: 1.13rem; line-height: 1.7; color: var(--text-muted); }
.art-meta {
    display: flex; flex-wrap: wrap; align-items: center; gap: 10px 22px;
    padding-top: 20px; border-top: 1px solid var(--border);
    font-size: 0.84rem; color: var(--text-muted);
}
.art-author { display: inline-flex; align-items: center; gap: 9px; font-weight: 650; color: var(--text-main); }
.art-avatar {
    display: inline-flex; align-items: center; justify-content: center;
    width: 32px; height: 32px; border-radius: 50%;
    background: var(--primary); color: #ffffff;
    font-size: 0.74rem; font-weight: 700;
    flex-shrink: 0;
}

.art-figure { margin: 0 0 8px; border-radius: var(--radius-lg); overflow: hidden; aspect-ratio: 16 / 7; background: var(--bg-alt); }
.art-figure img { width: 100%; height: 100%; object-fit: cover; display: block; }

.art-body { display: block; padding: 48px 20px 70px; }
.art-body.has-toc {
    display: grid;
    grid-template-columns: 240px minmax(0, 1fr);
    gap: 48px;
    align-items: start;
    max-width: 1120px;
}
.art-body:not(.has-toc) .art-main { max-width: 820px; margin: 0 auto; }

.art-toc { position: sticky; top: 110px; }
.art-toc-title {
    margin: 0 0 12px;
    font-size: 0.72rem; font-weight: 700;
    letter-spacing: 0.14em; text-transform: uppercase;
    color: var(--text-muted);
}
.art-toc ol { list-style: none; margin: 0; padding: 0 0 0 14px; border-left: 2px solid var(--border); }
.art-toc li { margin-bottom: 10px; }
.art-toc li.lvl-3 { padding-left: 14px; }
.art-toc a { color: var(--text-muted); text-decoration: none; font-size: 0.85rem; line-height: 1.5; transition: var(--transition); }
.art-toc a:hover { color: var(--primary); }

.art-tags { display: flex; flex-wrap: wrap; gap: 9px; margin-top: 40px; }
.art-tags a {
    padding: 5px 14px; border-radius: 999px;
    background: color-mix(in srgb, var(--primary) 7%, transparent);
    border: 1px solid color-mix(in srgb, var(--primary) 18%, transparent);
    color: var(--primary); font-size: 0.76rem; font-weight: 650; text-decoration: none;
}
.art-tags a:hover { background: color-mix(in srgb, var(--primary) 14%, transparent); }

.art-share { display: flex; align-items: center; gap: 10px; margin-top: 34px; padding-top: 26px; border-top: 1px solid var(--border); }
.art-share-label { font-size: 0.75rem; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: var(--text-muted); margin-right: 4px; }
.art-share a {
    display: inline-flex; align-items: center; justify-content: center;
    width: 38px; height: 38px; border-radius: 50%;
    border: 1.5px solid var(--border); color: var(--text-muted);
    transition: var(--transition);
}
.art-share a:hover { background: var(--primary); border-color: var(--primary); color: #ffffff; }

.art-cta { padding: 0 0 70px; }
.art-cta-panel {
    padding: 44px 40px; text-align: center;
    background: color-mix(in srgb, var(--primary) 7%, transparent);
    border: 1px solid color-mix(in srgb, var(--primary) 20%, transparent);
    border-radius: var(--radius-lg);
}
.art-cta-panel h2 { margin: 0 0 12px; font-family: var(--font-heading); font-size: clamp(1.3rem, 2.4vw, 1.8rem); font-weight: 800; color: var(--text-main); }
.art-cta-panel p { margin: 0 auto 24px; max-width: 520px; font-size: 0.98rem; line-height: 1.7; color: var(--text-muted); }
.art-cta-panel a {
    display: inline-flex; align-items: center; gap: 9px;
    padding: 14px 30px; border-radius: 999px;
    background: var(--primary); color: #ffffff;
    font-size: 0.92rem; font-weight: 650; text-decoration: none;
    transition: var(--transition);
}
.art-cta-panel a:hover { gap: 13px; box-shadow: var(--shadow-btn); }

.art-h2 { margin: 0 0 30px; font-family: var(--font-heading); font-size: 1.45rem; font-weight: 800; color: var(--text-main); }
.art-comments { padding: 56px 0 70px; border-top: 1px solid var(--border); }
.art-comment-list { display: flex; flex-direction: column; gap: 18px; margin-bottom: 38px; }
.art-comment { padding: 22px; background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-md); }
.art-comment-head { display: flex; align-items: center; gap: 12px; margin-bottom: 10px; }
.art-comment-head strong { display: block; font-size: 0.9rem; color: var(--text-main); }
.art-comment-head time { font-size: 0.75rem; color: var(--text-muted); }
.art-comment p { margin: 0; font-size: 0.93rem; line-height: 1.75; color: var(--text-muted); }

.art-comment-form { padding: 28px; background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-lg); }
.art-comment-form h3 { margin: 0 0 20px; font-size: 1rem; font-weight: 750; color: var(--text-main); }
.art-comment-form label { display: block; font-size: 0.79rem; font-weight: 650; color: var(--text-main); margin-bottom: 6px; }
.art-comment-form input, .art-comment-form textarea {
    width: 100%; box-sizing: border-box;
    padding: 11px 14px; margin-bottom: 14px;
    border: 1.5px solid var(--border); border-radius: var(--radius-sm);
    background: var(--bg-base); color: var(--text-main);
    font-family: inherit; font-size: 0.9rem;
}
.art-comment-form input:focus, .art-comment-form textarea:focus { outline: 0; border-color: color-mix(in srgb, var(--primary) 45%, transparent); }
.art-comment-form textarea { resize: vertical; }
.art-field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.art-comment-form button {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 12px 26px; border: 0; border-radius: 999px;
    background: var(--primary); color: #ffffff;
    font-family: var(--font-heading); font-size: 0.87rem; font-weight: 650;
    cursor: pointer; transition: var(--transition);
}
.art-comment-form button:hover:not(:disabled) { box-shadow: var(--shadow-btn); }
.art-comment-form button:disabled { opacity: 0.6; cursor: progress; }
#commentResult { margin-top: 12px; font-size: 0.88rem; font-weight: 600; }

.art-related { padding: 60px 0 80px; background: var(--bg-alt); border-top: 1px solid var(--border); }
.art-related-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 24px; }
.art-related-card {
    display: flex; flex-direction: column;
    background: var(--bg-card); border: 1px solid var(--border);
    border-radius: var(--radius-lg); overflow: hidden;
    text-decoration: none; transition: var(--transition);
}
.art-related-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-card); }
.art-related-media { display: block; aspect-ratio: 16 / 9; background: color-mix(in srgb, var(--primary) 7%, transparent); }
.art-related-media img { width: 100%; height: 100%; object-fit: cover; display: block; }
.art-related-body { display: block; padding: 20px; }
.art-related-cat { display: block; font-size: 0.69rem; font-weight: 700; letter-spacing: 0.13em; text-transform: uppercase; color: var(--primary); margin-bottom: 8px; }
.art-related-body strong { display: block; font-family: var(--font-heading); font-size: 0.96rem; font-weight: 750; line-height: 1.4; color: var(--text-main); }

@media (max-width: 1000px) {
    .art-body.has-toc { grid-template-columns: 1fr; gap: 30px; max-width: 820px; }
    .art-toc { position: static; }
    .art-related-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 660px) {
    .art-head { padding: 108px 0 32px; }
    .art-field-row { grid-template-columns: 1fr; }
    .art-related-grid { grid-template-columns: 1fr; }
    .art-cta-panel { padding: 32px 22px; }
}

/* ── Mise en forme du contenu rédigé dans l'éditeur ───────────────────────── */
.blog-content { font-size: 1.05rem; line-height: 1.85; color: var(--text-main); }
.blog-content h1, .blog-content h2, .blog-content h3 {
    font-family: var(--font-heading); font-weight: 800;
    color: var(--text-main); margin: 2.2rem 0 1rem; line-height: 1.25;
    scroll-margin-top: 110px;
}
.blog-content h2 { font-size: 1.5rem; }
.blog-content h3 { font-size: 1.2rem; }
.blog-content p { margin-bottom: 1.4rem; }
.blog-content a { color: var(--primary); text-decoration: underline; }
.blog-content ul, .blog-content ol { margin: 1rem 0 1.4rem 2rem; }
.blog-content li { margin-bottom: 0.4rem; }
.blog-content blockquote {
    border-left: 4px solid var(--primary); padding: 12px 24px; margin: 1.5rem 0;
    background: color-mix(in srgb, var(--primary) 5%, transparent);
    border-radius: 0 8px 8px 0; font-style: italic; color: var(--text-muted);
}
.blog-content img { max-width: 100%; height: auto; border-radius: 12px; margin: 1rem 0; }
.blog-content code { background: color-mix(in srgb, var(--primary) 8%, transparent); padding: 2px 8px; border-radius: 4px; font-size: 0.88em; font-family: monospace; }
.blog-content pre { background: var(--surface-dark); color: #e2e8f0; padding: 20px 24px; border-radius: 12px; overflow-x: auto; margin: 1.5rem 0; }
.blog-content pre code { background: none; padding: 0; font-size: 0.9rem; }
.blog-content table { width: 100%; border-collapse: collapse; margin: 1.5rem 0; display: block; overflow-x: auto; }
.blog-content th, .blog-content td { border: 1px solid var(--border); padding: 10px 14px; text-align: left; }
</style>
