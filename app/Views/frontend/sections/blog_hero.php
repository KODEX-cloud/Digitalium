<section class="hero section-padding" id="blog-hero" style="min-height: 70vh; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; position: relative; overflow: hidden; padding-bottom: 2rem;">
    <div class="container" style="display: flex; flex-direction: column; align-items: center; position: relative; z-index: 2;">
        
        <?php if (!empty($single['badge'])): ?>
            <div class="hero-badge reveal" style="display: inline-flex; align-items: center; gap: 8px; border: 1px solid var(--border); padding: 0.4rem 1.2rem; border-radius: 50px; font-size: 0.72rem; letter-spacing: 0.25em; text-transform: uppercase; color: var(--accent); margin-bottom: 2rem;">
                <div class="badge-dot" style="width: 6px; height: 6px; border-radius: 50%; background: var(--accent); animation: pulse 2s ease-in-out infinite;"></div>
                <?= htmlspecialchars($single['badge']) ?>
            </div>
        <?php endif; ?>

        <h1 class="hero-title reveal" style="font-size: clamp(2.4rem, 5.5vw, 4.2rem); font-weight: 800; line-height: 1.1; color: var(--text-main); margin-bottom: 1rem; font-family: var(--font-heading); max-width: 900px;">
            <?= $single['title'] ?? 'Explorez l\'avenir du digital et de l\'IA' ?>
        </h1>

        <div class="accent-bar reveal" style="display: flex; gap: 4px; justify-content: center; margin: 1.8rem auto;">
            <span style="width: 40px; height: 3px; border-radius: 2px; background: #e03a3a;"></span>
            <span style="width: 40px; height: 3px; border-radius: 2px; background: #2eaa5c;"></span>
            <span style="width: 40px; height: 3px; border-radius: 2px; background: #f5b800;"></span>
            <span style="width: 40px; height: 3px; border-radius: 2px; background: #f07820;"></span>
            <span style="width: 40px; height: 3px; border-radius: 2px; background: #1a6fba;"></span>
        </div>

        <p class="hero-sub reveal" style="font-size: 1.1rem; line-height: 1.8; color: var(--text-muted); max-width: 680px; margin: 0 auto 3rem auto;">
            <?= htmlspecialchars($single['subtitle'] ?? '') ?>
        </p>

        <!-- Search and Filter Bar -->
        <div class="filter-bar reveal" style="width: 100%; max-width: 700px; display: flex; flex-direction: column; gap: 20px; align-items: center; margin-top: 1rem;">
            <div class="search-box" style="display: flex; align-items: center; width: 100%; max-width: 520px; background: var(--bg-surface-alt); border: 1px solid var(--border); border-radius: 8px; overflow: hidden;">
                <input type="text" id="blogSearchInput" onkeyup="filterBlogSearch()" placeholder="Rechercher un article, un sujet..." style="flex: 1; background: transparent; border: none; outline: none; padding: 0.75rem 1.2rem; color: var(--text-main); font-size: 0.9rem;">
                <button style="background: var(--primary); border: none; padding: 0.75rem 1.5rem; cursor: pointer; color: white; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="search" style="width: 16px; height: 16px;"></i>
                </button>
            </div>
            
            <div class="tags" style="display: flex; gap: 10px; flex-wrap: wrap; justify-content: center;">
                <button class="blog-tag-btn active" onclick="filterBlogTag('Tous', this)">Tous</button>
                <button class="blog-tag-btn" onclick="filterBlogTag('Intelligence Artificielle', this)">IA</button>
                <button class="blog-tag-btn" onclick="filterBlogTag('Marketing Digital', this)">Marketing</button>
                <button class="blog-tag-btn" onclick="filterBlogTag('Développement Web', this)">Développement</button>
                <button class="blog-tag-btn" onclick="filterBlogTag('Stratégie', this)">Stratégie</button>
                <button class="blog-tag-btn" onclick="filterBlogTag('Transformation', this)">Transformation</button>
            </div>
        </div>

    </div>
</section>

<style>
.search-box {
    transition: var(--transition);
    border-radius: 12px !important;
}
.search-box:focus-within {
    border-color: var(--secondary) !important;
    box-shadow: 0 12px 30px -10px rgba(124, 58, 237, 0.25) !important;
}
.search-box button {
    border-radius: 0 12px 12px 0 !important;
    transition: var(--transition);
}
.search-box button:hover {
    background: var(--secondary) !important;
}
.blog-tag-btn {
    background: rgba(255, 255, 255, 0.6);
    backdrop-filter: blur(8px);
    border: 1px solid var(--border);
    color: var(--text-muted);
    padding: 8px 20px;
    border-radius: 12px;
    cursor: pointer;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    transition: var(--transition);
}
.blog-tag-btn.active, .blog-tag-btn:hover {
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    border-color: transparent;
    color: white;
    box-shadow: 0 10px 20px -8px rgba(79, 70, 229, 0.3);
    transform: translateY(-1px);
}
</style>

<script>
function filterBlogTag(tag, btn) {
    document.querySelectorAll('.blog-tag-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    const cards = document.querySelectorAll('.bcard');
    cards.forEach(card => {
        const cardTag = card.getAttribute('data-tag');
        if (tag === 'Tous' || cardTag === tag) {
            card.style.display = 'block';
            card.style.opacity = '0';
            setTimeout(() => {
                card.style.transition = 'opacity 0.4s ease';
                card.style.opacity = '1';
            }, 50);
        } else {
            card.style.display = 'none';
        }
    });
}

function filterBlogSearch() {
    const input = document.getElementById('blogSearchInput').value.toLowerCase();
    const cards = document.querySelectorAll('.bcard');
    
    cards.forEach(card => {
        const title = card.querySelector('h3').textContent.toLowerCase();
        const desc = card.querySelector('p').textContent.toLowerCase();
        
        if (title.includes(input) || desc.includes(input)) {
            card.style.display = 'block';
            card.style.opacity = '1';
        } else {
            card.style.display = 'none';
        }
    });
}
</script>
