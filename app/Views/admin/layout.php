<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Administration - Digitalium') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root {
            --bg-base: #f1f3f9;
            --bg-surface: rgba(255, 255, 255, 0.58);
            --bg-sidebar: rgba(255, 255, 255, 0.7);
            --border: rgba(0, 0, 0, 0.08);
            --border-highlight: rgba(255, 255, 255, 0.65);
            --text-main: #1e293b;
            --text-muted: #64748b;
            --primary: #4f46e5;
            --primary-hover: #7c3aed;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --font-main: 'Inter', sans-serif;
            --font-headings: 'Outfit', sans-serif;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: var(--font-main);
            background: linear-gradient(135deg, #eef2f8 0%, #f6f5fa 40%, #e0e6ff 100%);
            background-attachment: fixed;
            color: var(--text-main);
            display: flex;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .sidebar {
            width: 260px;
            background-color: var(--bg-sidebar);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
        }

        .sidebar-brand {
            padding: 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .brand-text {
            font-family: var(--font-headings);
            font-size: 1.4rem;
            font-weight: 700;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.025em;
        }

        .sidebar-menu {
            list-style: none;
            padding: 20px 12px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .menu-item a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: var(--text-muted);
            text-decoration: none;
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 500;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .menu-item a:hover,
        .menu-item.active a {
            color: var(--primary);
            background-color: rgba(79, 70, 229, 0.08);
        }

        .menu-item.active a {
            border-left: 4px solid var(--primary);
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
            font-weight: 600;
        }

        .sidebar-footer {
            padding: 16px 24px;
            border-top: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .user-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
            font-size: 0.85rem;
        }

        .user-name {
            font-weight: 600;
            color: var(--text-main);
        }

        .user-role {
            color: var(--text-muted);
            font-size: 0.75rem;
        }

        .btn-logout {
            color: var(--text-muted);
            background: none;
            border: none;
            cursor: pointer;
            padding: 6px;
            border-radius: 6px;
            transition: all 0.2s;
        }

        .btn-logout:hover {
            color: var(--danger);
            background-color: rgba(239, 68, 68, 0.08);
        }

        .main-wrapper {
            margin-left: 260px;
            width: calc(100% - 260px);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .navbar {
            height: 70px;
            border-bottom: 1px solid var(--border);
            background-color: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 40px;
        }

        .page-title-header {
            font-family: var(--font-headings);
            font-size: 1.35rem;
            font-weight: 700;
            letter-spacing: -0.01em;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .btn-visit {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 18px;
            background-color: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(10px);
            color: var(--primary);
            border: 1px solid rgba(79, 70, 229, 0.25);
            border-radius: 50px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .btn-visit:hover {
            background-color: white;
            border-color: var(--primary);
            transform: translateY(-1px);
            box-shadow: 0 6px 12px -3px rgba(79, 70, 229, 0.15);
        }

        .workspace-content {
            padding: 40px;
            flex-grow: 1;
        }

        .toast-container {
            position: fixed;
            top: 24px;
            right: 24px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
            pointer-events: none;
        }

        .toast {
            min-width: 320px;
            padding: 16px 20px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
            font-size: 0.9rem;
            animation: slideIn 0.3s ease forwards;
            pointer-events: auto;
            border: 1px solid transparent;
        }

        .toast-success {
            background-color: rgba(16, 185, 129, 0.95);
            color: white;
            border-left: 4px solid #047857;
        }

        .toast-error {
            background-color: rgba(239, 68, 68, 0.95);
            color: white;
            border-left: 4px solid #b91c1c;
        }

        .toast-info {
            background-color: rgba(99, 102, 241, 0.95);
            color: white;
            border-left: 4px solid #4338ca;
        }

        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        .card {
            background: var(--bg-surface);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid var(--border-highlight);
            border-radius: 20px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 20px 40px -15px rgba(99, 102, 241, 0.05), 0 5px 15px -5px rgba(0, 0, 0, 0.02);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            border-bottom: 1px solid var(--border);
            padding-bottom: 12px;
        }

        .card-title {
            font-family: var(--font-headings);
            font-size: 1.15rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-main);
        }

        .admin-form-group {
            margin-bottom: 20px;
        }

        .admin-form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-main);
        }

        .admin-input, .admin-textarea, .admin-select {
            width: 100%;
            padding: 11px 15px;
            background-color: rgba(255, 255, 255, 0.6);
            border: 1px solid var(--border);
            border-radius: 10px;
            color: var(--text-main);
            font-family: inherit;
            font-size: 0.9rem;
            transition: all 0.2s ease-in-out;
        }

        .admin-input:focus, .admin-textarea:focus, .admin-select:focus {
            outline: none;
            background-color: white;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 22px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-hover) 100%);
            color: white;
            border: none;
            border-radius: 50px;
            font-family: inherit;
            font-size: 0.88rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            box-shadow: 0 6px 14px -4px rgba(79, 70, 229, 0.25);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 18px -4px rgba(79, 70, 229, 0.35);
            filter: brightness(1.05);
        }

        .btn-secondary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 22px;
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(10px);
            color: var(--text-main);
            border: 1px solid var(--border);
            border-radius: 50px;
            font-family: inherit;
            font-size: 0.88rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.95);
            border-color: rgba(79, 70, 229, 0.2);
            transform: translateY(-1px);
        }

        .btn-danger {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 22px;
            background-color: var(--danger);
            color: white;
            border: none;
            border-radius: 50px;
            font-family: inherit;
            font-size: 0.88rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            box-shadow: 0 6px 14px -4px rgba(239, 68, 68, 0.25);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .btn-danger:hover {
            background-color: #dc2626;
            transform: translateY(-1px);
            box-shadow: 0 10px 18px -4px rgba(239, 68, 68, 0.35);
        }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-brand">
            <?php 
            $adminSettings = \App\Models\Setting::getAll();
            if (!empty($adminSettings['site_logo'])): 
            ?>
                <img src="<?= htmlspecialchars($adminSettings['site_logo']) ?>" alt="Logo" style="height: 32px; width: auto; object-fit: contain;">
                <div class="brand-text" style="font-size: 1.1rem; margin-left: 8px;"><?= htmlspecialchars($adminSettings['site_name'] ?? 'DIGITALIUM') ?></div>
            <?php else: ?>
                <i data-lucide="cpu" style="color: var(--primary)"></i>
                <div class="brand-text">DIGITALIUM</div>
            <?php endif; ?>
        </div>

        <ul class="sidebar-menu">
            <?php 
            $currentUri = $_SERVER['REQUEST_URI'];
            $scriptName = dirname($_SERVER['SCRIPT_NAME']);
            if ($scriptName !== '/' && $scriptName !== '\\') {
                $currentUri = str_replace($scriptName, '', $currentUri);
            }
            $isActive = function($pattern) use ($currentUri) {
                return preg_match($pattern, $currentUri) ? 'active' : '';
            };
            ?>
            <li class="menu-item <?= $isActive('#^/admin(/dashboard)?$#') ?>">
                <a href="<?= url('/admin/dashboard') ?>">
                    <i data-lucide="layout-dashboard"></i>
                    <span>Tableau de bord</span>
                </a>
            </li>
            <li class="menu-item <?= $isActive('#^/admin/pages#') ?>">
                <a href="<?= url('/admin/pages') ?>">
                    <i data-lucide="file-text"></i>
                    <span>Pages CMS</span>
                </a>
            </li>
            <li class="menu-item <?= $isActive('#^/admin/projects#') ?>">
                <a href="<?= url('/admin/projects') ?>">
                    <i data-lucide="briefcase"></i>
                    <span>Réalisations</span>
                </a>
            </li>
            <li class="menu-item <?= $isActive('#^/admin/blog#') ?>">
                <a href="<?= url('/admin/blog') ?>" style="position: relative;">
                    <i data-lucide="newspaper"></i>
                    <span>Blog</span>
                    <?php
                    try {
                        $pendingComments = \App\Models\Comment::countPending();
                        if ($pendingComments > 0): ?>
                        <span style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:#f59e0b;color:#fff;font-size:0.65rem;font-weight:800;padding:1px 7px;border-radius:50px;min-width:20px;text-align:center;" title="<?= $pendingComments ?> commentaire(s) en attente">
                            <?= $pendingComments ?>
                        </span>
                    <?php endif;
                    } catch (\Throwable $e) {} ?>
                </a>
            </li>
            <li class="menu-item <?= $isActive('#^/admin/blog/comments#') ?>">
                <a href="<?= url('/admin/blog/comments') ?>">
                    <i data-lucide="message-circle"></i>
                    <span>Commentaires</span>
                </a>
            </li>
            <li class="menu-item <?= $isActive('#^/admin/messages#') ?>">
                <a href="<?= url('/admin/messages') ?>" style="position: relative;">
                    <i data-lucide="inbox"></i>
                    <span>Messages</span>
                    <?php
                    try {
                        $unreadCount = \App\Models\Message::countNew();
                        if ($unreadCount > 0): ?>
                        <span style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:#6366f1;color:#fff;font-size:0.65rem;font-weight:800;padding:1px 7px;border-radius:50px;min-width:20px;text-align:center;">
                            <?= $unreadCount ?>
                        </span>
                    <?php endif;
                    } catch (\Throwable $e) {} ?>
                </a>
            </li>
            <li class="menu-item <?= $isActive('#^/admin/menus#') ?>">
                <a href="<?= url('/admin/menus') ?>">
                    <i data-lucide="menu"></i>
                    <span>Navigation</span>
                </a>
            </li>
            <li class="menu-item <?= $isActive('#^/admin/media#') ?>">
                <a href="<?= url('/admin/media') ?>">
                    <i data-lucide="image"></i>
                    <span>Bibliothèque Média</span>
                </a>
            </li>
            <li class="menu-item <?= $isActive('#^/admin/system/deploy-center#') ?>">
                <a href="<?= url('/admin/system/deploy-center') ?>">
                    <i data-lucide="rocket"></i>
                    <span>Deploy Center</span>
                </a>
            </li>
            <li class="menu-item <?= $isActive('#^/admin/system/sync-production#') ?>">
                <a href="<?= url('/admin/system/sync-production') ?>">
                    <i data-lucide="database-zap"></i>
                    <span>Sync Production</span>
                </a>
            </li>
            <li class="menu-item <?= $isActive('#^/admin/system/recovery#') ?>">
                <a href="<?= url('/admin/system/recovery') ?>">
                    <i data-lucide="shield-check"></i>
                    <span>Recovery Center</span>
                </a>
            </li>
            <li class="menu-item <?= $isActive('#^/admin/settings#') ?>">
                <a href="<?= url('/admin/settings') ?>">
                    <i data-lucide="settings"></i>
                    <span>Configuration</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-footer">
            <div class="user-info">
                <span class="user-name"><?= htmlspecialchars($currentUser['username'] ?? 'Admin') ?></span>
                <span class="user-role">Super Administrateur</span>
            </div>
            <a href="<?= url('/admin/logout') ?>" class="btn-logout" title="Se déconnecter">
                <i data-lucide="log-out"></i>
            </a>
        </div>
    </aside>

    <div class="main-wrapper">
        <header class="navbar">
            <h1 class="page-title-header"><?= htmlspecialchars($title ?? 'Administration') ?></h1>
            <div class="nav-actions">
                <a href="<?= url('/') ?>" target="_blank" class="btn-visit">
                    <i data-lucide="external-link"></i>
                    <span>Voir le site</span>
                </a>
            </div>
        </header>

        <main class="workspace-content">
            <?= $content ?>
        </main>
    </div>

    <!-- Dynamic Toast Notification Container pointing to App\Services\Session -->
    <div class="toast-container">
        <?php foreach (['success', 'error', 'info'] as $type): ?>
            <?php if ($flashMsg = \App\Services\Session::getFlash($type)): ?>
                <div class="toast toast-<?= $type ?>" id="toast-<?= $type ?>">
                    <i data-lucide="<?= $type === 'success' ? 'check-circle' : ($type === 'error' ? 'alert-octagon' : 'info') ?>"></i>
                    <span><?= htmlspecialchars($flashMsg) ?></span>
                </div>
                <script>
                    setTimeout(() => {
                        const toast = document.getElementById('toast-<?= $type ?>');
                        if (toast) {
                            toast.style.transition = 'opacity 0.5s ease-out, transform 0.5s ease-out';
                            toast.style.opacity = '0';
                            toast.style.transform = 'translateY(-20px)';
                            setTimeout(() => toast.remove(), 500);
                        }
                    }, 4000);
                </script>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <!-- Centralized Media Library Picker Modal -->
    <?php
    $layoutMediaList = \App\Models\Media::all('id DESC');
    ?>
    <div class="media-modal" id="mediaPickerModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(15, 23, 42, 0.25); z-index: 9999; align-items: center; justify-content: center; padding: 20px; backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);">
        <div class="modal-content" style="background-color: rgba(255, 255, 255, 0.9); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.6); border-radius: 24px; width: 100%; max-width: 800px; max-height: 80vh; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 30px 60px -15px rgba(99, 102, 241, 0.15), 0 10px 20px -5px rgba(0, 0, 0, 0.03);">
            <div class="modal-header" style="padding: 20px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between;">
                <h3 class="card-title">
                    <i data-lucide="image" style="color: var(--primary)"></i>
                    <span>Sélectionner une image</span>
                </h3>
                <button type="button" class="action-btn-small" onclick="document.getElementById('mediaPickerModal').classList.remove('active')" style="background: none; border: none; cursor: pointer; color: var(--text-muted); padding: 4px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center;">
                    <i data-lucide="x" style="width: 20px; height: 20px;"></i>
                </button>
            </div>
            <div class="modal-body" style="padding: 20px; overflow-y: auto; flex-grow: 1;">
                <?php if (empty($layoutMediaList)): ?>
                    <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                        <i data-lucide="image-off" style="width: 48px; height: 48px; margin-bottom: 12px;"></i>
                        <p>Aucun fichier média trouvé.</p>
                    </div>
                <?php else: ?>
                    <div class="modal-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 16px;">
                        <?php foreach ($layoutMediaList as $media): ?>
                            <div class="modal-media-item" data-filepath="<?= htmlspecialchars($media['filepath']) ?>" data-filename="<?= htmlspecialchars($media['original_name']) ?>" style="background-color: rgba(255,255,255,0.4); border: 1px solid var(--border); border-radius: 12px; aspect-ratio: 1; overflow: hidden; cursor: pointer; position: relative; transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);">
                                <img src="<?= htmlspecialchars($media['filepath']) ?>" alt="<?= htmlspecialchars($media['original_name']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <style>
        .media-modal.active {
            display: flex !important;
        }
        .modal-media-item:hover {
            border-color: var(--primary) !important;
            transform: translateY(-2px);
            box-shadow: 0 8px 16px -4px rgba(79, 70, 229, 0.2);
        }
    </style>

    <!-- Centralized JavaScript for Notifications, Toasts and Dynamic Media Selection -->
    <script>
        const BASE_URL = '<?= rtrim(url('/'), '/') ?>';
        // Centralized Toast Notifications
        function showNotification(message, type) {
            const container = document.querySelector('.toast-container') || document.body;
            const toast = document.createElement('div');
            toast.className = 'toast toast-' + type;
            toast.style.zIndex = '99999';
            
            let iconName = 'info';
            if (type === 'success') iconName = 'check-circle';
            if (type === 'error') iconName = 'alert-octagon';

            toast.innerHTML = `<i data-lucide="${iconName}"></i><span>${message}</span>`;
            container.appendChild(toast);
            
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            setTimeout(() => {
                toast.style.transition = 'opacity 0.5s ease-out, transform 0.5s ease-out';
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-20px)';
                setTimeout(() => toast.remove(), 500);
            }, 3000);
        }

        // Global Event Delegation for Media Selectors
        document.body.addEventListener('click', function(e) {
            // 1. Select Media Button Click
            const selectBtn = e.target.closest('.select-media-btn');
            if (selectBtn) {
                e.preventDefault();
                window.currentMediaTargetInputId = selectBtn.getAttribute('data-target');
                window.currentMediaTargetPreviewId = selectBtn.getAttribute('data-preview');
                window.currentMediaTargetLabelId = selectBtn.getAttribute('data-label');
                
                const modal = document.getElementById('mediaPickerModal');
                if (modal) {
                    modal.classList.add('active');
                }
                return;
            }
            
            // 2. Remove Media Button Click
            const removeBtn = e.target.closest('.remove-media-btn');
            if (removeBtn) {
                e.preventDefault();
                const targetInputId = removeBtn.getAttribute('data-target');
                const targetPreviewId = removeBtn.getAttribute('data-preview');
                const targetLabelId = removeBtn.getAttribute('data-label');
                
                if (targetInputId) {
                    const input = document.getElementById(targetInputId);
                    if (input) {
                        input.value = '';
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                }
                
                if (targetPreviewId) {
                    const preview = document.getElementById(targetPreviewId);
                    if (preview) {
                        preview.innerHTML = '<i data-lucide="image" style="width: 22px; height: 22px; color: var(--text-muted);"></i>';
                    }
                }
                
                if (targetLabelId) {
                    const label = document.getElementById(targetLabelId);
                    if (label) label.textContent = '';
                }
                
                removeBtn.style.display = 'none';
                
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
                
                showNotification('Image retirée.', 'info');
                return;
            }
            
            // 3. Selection inside the modal grid
            const mediaItem = e.target.closest('#mediaPickerModal .modal-media-item');
            if (mediaItem) {
                e.preventDefault();
                const filepath = mediaItem.getAttribute('data-filepath');
                const filename = mediaItem.getAttribute('data-filename') || filepath.split('/').pop();
                
                if (window.currentMediaTargetInputId) {
                    const input = document.getElementById(window.currentMediaTargetInputId);
                    if (input) {
                        input.value = filepath;
                        // Fire change event to notify any form updates
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                }
                
                if (window.currentMediaTargetPreviewId) {
                    const preview = document.getElementById(window.currentMediaTargetPreviewId);
                    if (preview) {
                        preview.innerHTML = `<img src="${filepath}" alt="Aperçu" style="width: 100%; height: 100%; object-fit: cover;">`;
                    }
                }
                
                if (window.currentMediaTargetLabelId) {
                    const label = document.getElementById(window.currentMediaTargetLabelId);
                    if (label) label.textContent = filename;
                }
                
                // Show the corresponding remove button
                if (window.currentMediaTargetInputId) {
                    const targetEl = document.getElementById(window.currentMediaTargetInputId);
                    if (targetEl) {
                        const parentComponent = targetEl.closest('.media-picker-component');
                        if (parentComponent) {
                            const removeBtnEl = parentComponent.querySelector('.remove-media-btn');
                            if (removeBtnEl) {
                                removeBtnEl.style.display = 'inline-flex';
                            }
                        }
                    }
                }
                
                const modal = document.getElementById('mediaPickerModal');
                if (modal) {
                    modal.classList.remove('active');
                }
                
                window.currentMediaTargetInputId = null;
                window.currentMediaTargetPreviewId = null;
                window.currentMediaTargetLabelId = null;
                
                showNotification('Image sélectionnée !', 'success');
            }
        });

        // Close modal when clicking outside content
        const mediaModal = document.getElementById('mediaPickerModal');
        if (mediaModal) {
            mediaModal.addEventListener('click', function(e) {
                if (e.target === mediaModal) {
                    mediaModal.classList.remove('active');
                }
            });
        }
    </script>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
