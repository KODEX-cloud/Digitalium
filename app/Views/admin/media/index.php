<style>
    .media-workspace {
        display: grid;
        grid-template-columns: 3fr 1fr;
        gap: 24px;
    }
    @media (max-width: 1024px) {
        .media-workspace {
            grid-template-columns: 1fr;
        }
    }

    .upload-zone {
        border: 2px dashed rgba(79, 70, 229, 0.25);
        border-radius: 20px;
        background-color: rgba(255, 255, 255, 0.4);
        padding: 40px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 16px;
        margin-bottom: 24px;
    }
    .upload-zone:hover, .upload-zone.dragover {
        border-color: var(--primary);
        background-color: rgba(79, 70, 229, 0.08);
    }
    .upload-zone i {
        color: var(--text-muted);
        transition: color 0.3s;
    }
    .upload-zone:hover i, .upload-zone.dragover i {
        color: var(--primary);
    }
    .upload-zone input {
        display: none;
    }

    .search-box {
        display: flex;
        gap: 10px;
        margin-bottom: 24px;
    }

    .media-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 20px;
    }

    .media-card {
        background: var(--bg-surface);
        backdrop-filter: blur(24px);
        -webkit-backdrop-filter: blur(24px);
        border: 1px solid var(--border-highlight);
        border-radius: 16px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        position: relative;
        box-shadow: 0 10px 20px -5px rgba(99, 102, 241, 0.04), 0 4px 6px -2px rgba(0, 0, 0, 0.01);
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .media-card:hover {
        transform: translateY(-4px);
        border-color: rgba(99, 102, 241, 0.25);
        box-shadow: 0 20px 30px -10px rgba(99, 102, 241, 0.08);
    }

    .media-preview-container {
        aspect-ratio: 4/3;
        background-color: var(--bg-base);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border-bottom: 1px solid var(--border);
        position: relative;
    }
    .media-preview-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .media-preview-container i {
        color: var(--text-muted);
    }

    .media-card-details {
        padding: 12px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .media-title {
        font-size: 0.85rem;
        font-weight: 700;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        color: var(--text-main);
        margin-bottom: 4px;
    }

    .media-meta {
        font-size: 0.75rem;
        color: var(--text-muted);
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-weight: 500;
    }

    .media-actions {
        display: flex;
        justify-content: space-between;
        margin-top: 10px;
        border-top: 1px solid var(--border);
        padding-top: 8px;
    }

    .media-btn {
        background: none;
        border: none;
        color: var(--text-muted);
        cursor: pointer;
        padding: 6px;
        border-radius: 6px;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .media-btn:hover {
        color: var(--text-main);
        background-color: rgba(0, 0, 0, 0.04);
    }
    .media-btn.btn-delete-media:hover {
        color: var(--danger);
        background-color: rgba(239, 68, 68, 0.08);
    }

    .no-media {
        text-align: center;
        padding: 40px;
        grid-column: 1 / -1;
        color: var(--text-muted);
    }
</style>

<div class="media-workspace">
    <div>
        <div class="card">
            <div class="card-header" style="margin-bottom: 20px;">
                <h2 class="card-title">
                    <i data-lucide="image"></i>
                    <span>Vos Fichiers Média</span>
                </h2>
                <form action="<?= url('/admin/media') ?>" method="GET" class="search-box" style="margin-bottom: 0;">
                    <input type="text" name="search" class="admin-input" style="width: 220px;" placeholder="Rechercher un média..." value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" class="btn-secondary" style="padding: 10px 14px;">
                        <i data-lucide="search" style="width: 16px; height: 16px;"></i>
                    </button>
                    <?php if (!empty($search)): ?>
                        <a href="<?= url('/admin/media') ?>" class="btn-secondary" style="padding: 10px 14px;">
                            <i data-lucide="x" style="width: 16px; height: 16px;"></i>
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="media-grid" id="mediaGrid">
                <?php if (empty($mediaList)): ?>
                    <div class="no-media" id="noMediaPlaceholder">
                        <i data-lucide="image-off" style="width: 48px; height: 48px; margin-bottom: 12px;"></i>
                        <p>Aucun fichier média trouvé.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($mediaList as $media): ?>
                        <div class="media-card" id="media-card-<?= $media['id'] ?>">
                            <div class="media-preview-container">
                                <?php 
                                $isImg = str_starts_with($media['mime_type'], 'image/');
                                if ($isImg): 
                                ?>
                                    <img src="<?= htmlspecialchars(url($media['filepath'])) ?>" alt="<?= htmlspecialchars($media['original_name']) ?>" loading="lazy">
                                <?php else: ?>
                                    <i data-lucide="file" style="width: 32px; height: 32px;"></i>
                                <?php endif; ?>
                            </div>
                            <div class="media-card-details">
                                <div>
                                    <div class="media-title" title="<?= htmlspecialchars($media['original_name']) ?>"><?= htmlspecialchars($media['original_name']) ?></div>
                                    <div class="media-meta">
                                        <span><?= round($media['file_size'] / 1024, 1) ?> Ko</span>
                                        <span style="font-family: monospace; font-size: 0.65rem; text-transform: uppercase;"><?= substr($media['mime_type'], strpos($media['mime_type'], '/') + 1) ?></span>
                                    </div>
                                </div>
                                <div class="media-actions">
                                    <button class="media-btn" onclick="copyToClipboard('<?= htmlspecialchars(url($media['filepath'])) ?>')" title="Copier le lien direct">
                                        <i data-lucide="copy" style="width: 16px; height: 16px;"></i>
                                    </button>
                                    <button class="media-btn" onclick="window.open('<?= htmlspecialchars(url($media['filepath'])) ?>', '_blank')" title="Ouvrir dans un nouvel onglet">
                                        <i data-lucide="external-link" style="width: 16px; height: 16px;"></i>
                                    </button>
                                    <button class="media-btn btn-delete-media" onclick="deleteMedia(<?= $media['id'] ?>)" title="Supprimer définitivement">
                                        <i data-lucide="trash-2" style="width: 16px; height: 16px;"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div>
        <div class="card" style="position: sticky; top: 20px;">
            <div class="card-header" style="margin-bottom: 15px; padding-bottom: 8px;">
                <h3 class="card-title" style="font-size: 1rem;">Ajouter des Médias</h3>
            </div>
            
            <div class="upload-zone" id="uploadZone" onclick="document.getElementById('fileInput').click()">
                <i data-lucide="cloud-upload" style="width: 48px; height: 48px; stroke-width: 1.5;"></i>
                <div>
                    <p style="font-weight: 500; font-size: 0.9rem; margin-bottom: 4px;">Glissez-déposez ici</p>
                    <p style="color: var(--text-muted); font-size: 0.75rem;">ou cliquez pour choisir</p>
                </div>
                <input type="file" id="fileInput" name="file" multiple accept="image/*,.svg">
            </div>

            <p style="color: var(--text-muted); font-size: 0.75rem; line-height: 1.5; text-align: center;">
                Formats acceptés : JPG, PNG, GIF, WEBP, SVG.<br>
                Taille max par fichier : 10 Mo.<br>
                <em>Les images sont automatiquement optimisées en WebP.</em>
            </p>
        </div>
    </div>
</div>

<script>
    const uploadZone = document.getElementById('uploadZone');
    const fileInput = document.getElementById('fileInput');

    ['dragenter', 'dragover'].forEach(eventName => {
        uploadZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            uploadZone.classList.add('dragover');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        uploadZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            uploadZone.classList.remove('dragover');
        }, false);
    });

    uploadZone.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;
        handleFiles(files);
    });

    fileInput.addEventListener('change', function() {
        handleFiles(this.files);
    });

    function handleFiles(files) {
        if (!files.length) return;
        for (let i = 0; i < files.length; i++) {
            uploadFile(files[i]);
        }
    }

    function uploadFile(file) {
        const formData = new FormData();
        formData.append('file', file);
        formData.append('csrf_token', '<?= htmlspecialchars($csrf_token) ?>');
        formData.append('ajax', '1');

        showNotification('Téléversement de ' + file.name + '...', 'info');

        fetch(BASE_URL + '/admin/media/upload?ajax=1', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                setTimeout(() => window.location.reload(), 800);
            } else {
                showNotification(data.error || 'Erreur lors du téléversement', 'error');
            }
        })
        .catch(err => {
            console.error(err);
            showNotification('Échec du téléversement réseau.', 'error');
        });
    }

    function deleteMedia(id) {
        if (!confirm('Voulez-vous vraiment supprimer définitivement ce fichier média ?')) {
            return;
        }

        const formData = new FormData();
        formData.append('id', id);
        formData.append('csrf_token', '<?= htmlspecialchars($csrf_token) ?>');

        fetch(BASE_URL + '/admin/media/delete', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                const card = document.getElementById('media-card-' + id);
                if (card) {
                    card.style.transition = 'all 0.3s ease-out';
                    card.style.transform = 'scale(0.8)';
                    card.style.opacity = '0';
                    setTimeout(() => {
                        card.remove();
                        if (document.querySelectorAll('.media-card').length === 0) {
                            window.location.reload();
                        }
                    }, 300);
                }
            } else {
                showNotification(data.error, 'error');
            }
        })
        .catch(err => {
            console.error(err);
            showNotification('Échec de la suppression.', 'error');
        });
    }

    function copyToClipboard(text) {
        const link = window.location.origin + text;
        navigator.clipboard.writeText(link).then(() => {
            showNotification('Lien copié dans le presse-papiers !', 'success');
        }).catch(err => {
            showNotification('Erreur de copie.', 'error');
        });
    }

    function showNotification(message, type) {
        const container = document.querySelector('.toast-container');
        if (!container) return;

        const toast = document.createElement('div');
        toast.className = 'toast toast-' + type;
        
        let iconName = 'info';
        if (type === 'success') iconName = 'check-circle';
        if (type === 'error') iconName = 'alert-octagon';

        toast.innerHTML = `<i data-lucide="${iconName}"></i><span>${message}</span>`;
        container.appendChild(toast);
        
        lucide.createIcons();

        setTimeout(() => {
            toast.style.transition = 'opacity 0.5s ease-out, transform 0.5s ease-out';
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(-20px)';
            setTimeout(() => toast.remove(), 500);
        }, 3000);
    }
</script>
