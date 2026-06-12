<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Connexion - Digitalium Admin') ?></title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-primary: #f1f3f9;
            --bg-gradient: linear-gradient(135deg, #eef2f8 0%, #f6f5fa 40%, #e0e6ff 100%);
            --bg-secondary: rgba(255, 255, 255, 0.58);
            --text-primary: #1e293b;
            --text-secondary: #475569;
            --primary: #4f46e5;
            --primary-hover: #7c3aed;
            --border: rgba(255, 255, 255, 0.65);
            --danger: #ef4444;
            --success: #10b981;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-gradient);
            background-attachment: fixed;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .login-card {
            background: var(--bg-secondary);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid var(--border);
            border-radius: 28px;
            width: 100%;
            max-width: 440px;
            padding: 40px;
            box-shadow: 0 30px 60px -15px rgba(99, 102, 241, 0.1), 0 10px 20px -5px rgba(0, 0, 0, 0.03);
            position: relative;
            z-index: 10;
        }

        .glow {
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.12) 0%, rgba(244, 245, 248, 0) 70%);
            border-radius: 50%;
            z-index: 1;
            pointer-events: none;
        }
        .glow-1 { top: -150px; right: -150px; }
        .glow-2 { bottom: -150px; left: -150px; }

        .logo-container {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo-text {
            font-family: 'Outfit', sans-serif;
            font-size: 2rem;
            font-weight: 900;
            background: linear-gradient(135deg, #1e293b 0%, #4f46e5 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.03em;
        }

        .subtitle {
            color: var(--text-secondary);
            font-size: 0.95rem;
            margin-top: 6px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-primary);
        }

        input {
            width: 100%;
            padding: 12px 16px;
            background-color: rgba(255, 255, 255, 0.58);
            border: 1px solid var(--border);
            border-radius: 12px;
            color: var(--text-primary);
            font-family: inherit;
            font-size: 0.95rem;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        input:focus {
            outline: none;
            background-color: white;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.15);
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--primary) 0%, #7c3aed 100%);
            color: white;
            border: none;
            border-radius: 50px;
            font-family: inherit;
            font-size: 0.98rem;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 10px 20px -5px rgba(79, 70, 229, 0.3);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            margin-top: 10px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 25px -5px rgba(79, 70, 229, 0.4);
            filter: brightness(1.05);
        }

        .alert {
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: 0.9rem;
            font-weight: 600;
            border: 1px solid transparent;
        }

        .alert-error {
            background-color: rgba(239, 68, 68, 0.08);
            border-color: rgba(239, 68, 68, 0.25);
            color: #dc2626;
        }

        .alert-success {
            background-color: rgba(16, 185, 129, 0.08);
            border-color: rgba(16, 185, 129, 0.25);
            color: #16a34a;
        }
    </style>
</head>
<body>
    <div class="glow glow-1"></div>
    <div class="glow glow-2"></div>

    <div class="login-card">
        <div class="logo-container">
            <div class="logo-text">DIGITALIUM</div>
            <div class="subtitle">Espace d'Administration Sécurisé</div>
        </div>

        <!-- Render Flash Messages pointing to App\Services\Session -->
        <?php if ($errorMsg = \App\Services\Session::getFlash('error')): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($errorMsg) ?>
            </div>
        <?php endif; ?>

        <?php if ($successMsg = \App\Services\Session::getFlash('success')): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($successMsg) ?>
            </div>
        <?php endif; ?>

        <form action="<?= url('/admin/login') ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

            <div class="form-group">
                <label for="username">Identifiant</label>
                <input type="text" id="username" name="username" placeholder="Entrez votre identifiant" required autofocus autocomplete="username">
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required autocomplete="current-password">
            </div>

            <button type="submit" class="btn-submit">Se connecter</button>
        </form>
    </div>
</body>
</html>
