<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

<style>
.blog-editor-grid { display: grid; grid-template-columns: 1fr 320px; gap: 24px; align-items: start; }
@media(max-width:1100px){ .blog-editor-grid { grid-template-columns: 1fr; } }
.ql-container { min-height: 320px; font-size: 1rem; }
.image-field-wrapper { display: flex; gap: 12px; align-items: center; }
.image-field-preview { width: 72px; height: 72px; border-radius: 10px; border: 1px solid var(--border); background: rgba(255,255,255,0.4); display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0; }
.image-field-preview img { width: 100%; height: 100%; object-fit: cover; }
.media-modal { display:none; position:fixed; inset:0; background:rgba(15,23,42,0.3); z-index:9999; align-items:center; justify-content:center; padding:20px; backdrop-filter:blur(8px); }
.media-modal.active { display:flex; }
.modal-content { background:rgba(255,255,255,0.92); backdrop-filter:blur(20px); border:1px solid rgba(255,255,255,0.6); border-radius:24px; width:100%; max-width:760px; max-height:80vh; display:flex; flex-direction:column; overflow:hidden; box-shadow:0 30px 60px -15px rgba(99,102,241,0.15); }
.modal-header { padding:18px 20px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; }
.modal-body { padding:20px; overflow-y:auto; flex-grow:1; }
.modal-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(110px,1fr)); gap:14px; }
.modal-media-item { border:1px solid var(--border); border-radius:10px; aspect-ratio:1; overflow:hidden; cursor:pointer; transition:all 0.2s; }
.modal-media-item:hover { border-color:var(--primary); transform:translateY(-2px); box-shadow:0 6px 14px -3px rgba(79,70,229,0.2); }
.modal-media-item img { width:100%; height:100%; object-fit:cover; }
</style>

<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;">
    <div>
        <a href="<?= url('/admin/blog') ?>" style="font-size:0.82rem;color:var(--text-muted);text-decoration:none;display:inline-flex;align-items:center;gap:4px;margin-bottom:8px;">
            <i data-lucide="arrow-left" style="width:14px;height:14px;"></i> Blog
        </a>
        <h1 class="page-title">Nouvel article</h1>
    </div>
    <button form="blog-form" type="submit" class="btn-primary" style="display:inline-flex;align-items:center;gap:8px;padding:11px 22px;border-radius:10px;font-size:0.88rem;font-weight:700;">
        <i data-lucide="save" style="width:16px;height:16px;"></i> Créer l'article
    </button>
</div>

<form id="blog-form" method="POST" action="<?= url('/admin/blog/create') ?>">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
    <input type="hidden" name="content" id="content-input">
    <input type="hidden" name="featured_image" id="featured-image-input">

    <div class="blog-editor-grid">
        <!-- Main column -->
        <div style="display:flex;flex-direction:column;gap:20px;">
            <div class="card" style="background:var(--bg-surface);border:1px solid var(--border);border-radius:16px;padding:24px;">
                <div style="margin-bottom:18px;">
                    <label class="field-label">Titre de l'article *</label>
                    <input type="text" name="title" id="post-title" class="field-input" placeholder="Titre accrocheur..." required oninput="autoSlug(this.value)" style="font-size:1.15rem;font-weight:600;">
                </div>
                <div>
                    <label class="field-label">Slug URL</label>
                    <input type="text" name="slug" id="post-slug" class="field-input" placeholder="slug-de-larticle">
                </div>
            </div>

            <div class="card" style="background:var(--bg-surface);border:1px solid var(--border);border-radius:16px;padding:24px;">
                <label class="field-label" style="margin-bottom:10px;">Contenu de l'article</label>
                <div id="quill-editor" style="min-height:320px;border-radius:8px;"></div>
            </div>

            <div class="card" style="background:var(--bg-surface);border:1px solid var(--border);border-radius:16px;padding:24px;">
                <label class="field-label" style="margin-bottom:10px;">Extrait / Chapô</label>
                <textarea name="excerpt" class="field-input" rows="3" placeholder="Court résumé affiché dans les listings..."></textarea>
            </div>
        </div>

        <!-- Sidebar -->
        <div style="display:flex;flex-direction:column;gap:16px;">
            <div class="card" style="background:var(--bg-surface);border:1px solid var(--border);border-radius:16px;padding:20px;">
                <h3 style="font-size:0.88rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);margin-bottom:16px;">Publication</h3>
                <div style="margin-bottom:14px;">
                    <label class="field-label">Statut</label>
                    <select name="status" class="field-input">
                        <option value="draft">Brouillon</option>
                        <option value="published">Publié</option>
                    </select>
                </div>
                <div style="margin-bottom:14px;">
                    <label class="field-label">Catégorie</label>
                    <select name="category" class="field-input">
                        <option value="">— Aucune —</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat['name']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="margin-bottom:14px;">
                    <label class="field-label">Auteur</label>
                    <input type="text" name="author" class="field-input" value="Équipe Digitalium" placeholder="Auteur de l'article">
                </div>
                <div>
                    <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:0.88rem;font-weight:600;">
                        <input type="checkbox" name="is_featured" value="1" style="width:16px;height:16px;accent-color:var(--primary);">
                        Article mis en avant
                    </label>
                </div>
            </div>

            <div class="card" style="background:var(--bg-surface);border:1px solid var(--border);border-radius:16px;padding:20px;">
                <h3 style="font-size:0.88rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);margin-bottom:16px;">Image de couverture</h3>
                <div class="image-field-wrapper">
                    <div class="image-field-preview" id="featured-image-preview">
                        <i data-lucide="image" style="width:24px;height:24px;color:var(--text-muted);opacity:0.4;"></i>
                    </div>
                    <div style="flex:1;">
                        <button type="button" class="btn-secondary" onclick="openMediaModal('featured-image-input','featured-image-preview')" style="width:100%;padding:8px;font-size:0.82rem;border-radius:8px;">
                            Choisir une image
                        </button>
                        <button type="button" onclick="clearImage('featured-image-input','featured-image-preview')" style="margin-top:6px;width:100%;padding:6px;font-size:0.78rem;border-radius:8px;background:none;border:1px solid var(--border);color:var(--text-muted);cursor:pointer;">
                            Supprimer
                        </button>
                    </div>
                </div>
            </div>

            <div class="card" style="background:var(--bg-surface);border:1px solid var(--border);border-radius:16px;padding:20px;">
                <h3 style="font-size:0.88rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);margin-bottom:16px;">SEO</h3>
                <div style="margin-bottom:12px;">
                    <label class="field-label">Meta titre</label>
                    <input type="text" name="meta_title" class="field-input" placeholder="Titre pour les moteurs de recherche">
                </div>
                <div style="margin-bottom:12px;">
                    <label class="field-label">Meta description</label>
                    <textarea name="meta_description" class="field-input" rows="3" placeholder="Description pour les moteurs de recherche (160 cars max)..."></textarea>
                </div>
                <div>
                    <label class="field-label">Tags</label>
                    <input type="text" name="tags" class="field-input" placeholder="tag1, tag2, tag3">
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Media Modal -->
<div class="media-modal" id="mediaModal">
    <div class="modal-content">
        <div class="modal-header">
            <span style="font-weight:700;font-size:1rem;">Bibliothèque média</span>
            <button type="button" onclick="closeMediaModal()" style="background:none;border:none;cursor:pointer;padding:4px;">
                <i data-lucide="x" style="width:20px;height:20px;"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="modal-grid">
                <?php foreach ($mediaList as $m): ?>
                <div class="modal-media-item" onclick="selectMedia('<?= htmlspecialchars(url($m['filepath'])) ?>')">
                    <img src="<?= htmlspecialchars(url($m['filepath'])) ?>" alt="<?= htmlspecialchars($m['original_name']) ?>" loading="lazy">
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
let _currentInput = null, _currentPreview = null;

function autoSlug(val) {
    const slug = val.toLowerCase().replace(/[^a-z0-9\s-]/g,'').replace(/\s+/g,'-').replace(/-+/g,'-').replace(/^-|-$/g,'');
    document.getElementById('post-slug').value = slug;
}

function openMediaModal(inputId, previewId) {
    _currentInput = inputId;
    _currentPreview = previewId;
    document.getElementById('mediaModal').classList.add('active');
}
function closeMediaModal() {
    document.getElementById('mediaModal').classList.remove('active');
}
function selectMedia(url) {
    document.getElementById(_currentInput).value = url;
    const prev = document.getElementById(_currentPreview);
    prev.innerHTML = '<img src="' + url + '" style="width:100%;height:100%;object-fit:cover;">';
    closeMediaModal();
}
function clearImage(inputId, previewId) {
    document.getElementById(inputId).value = '';
    document.getElementById(previewId).innerHTML = '<i data-lucide="image" style="width:24px;height:24px;opacity:0.3;"></i>';
    lucide.createIcons();
}

const quill = new Quill('#quill-editor', {
    theme: 'snow',
    modules: {
        toolbar: [
            [{ 'header': [1, 2, 3, false] }],
            ['bold', 'italic', 'underline', 'strike'],
            [{ 'list': 'ordered' }, { 'list': 'bullet' }],
            ['link', 'image', 'blockquote', 'code-block'],
            [{ 'align': [] }],
            ['clean']
        ]
    }
});

document.getElementById('blog-form').addEventListener('submit', function() {
    document.getElementById('content-input').value = quill.root.innerHTML;
});

lucide.createIcons();
</script>
