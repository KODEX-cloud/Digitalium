<?php
/**
 * Universal Admin Account Modification Tool
 * Placed in public/ to be accessible under both Mode A and Mode B configurations.
 */

define('SECURE_ACCESS', true);

// 1. Load Configuration
require_once __DIR__ . '/../config/config.php';

// 2. PSR-4 Compliant Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = dirname(__DIR__) . '/app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

use App\Services\Database;

$message = '';
$isSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newUsername = trim($_POST['username'] ?? '');
    $newEmail = trim($_POST['email'] ?? '');
    $newPassword = $_POST['password'] ?? '';

    if (!empty($newUsername) && !empty($newEmail) && !empty($newPassword)) {
        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            
            // Check if admin user exists first
            $checkSql = "SELECT COUNT(*) as total FROM users WHERE id = 1";
            $row = Database::fetch($checkSql);
            
            if ((int)$row['total'] > 0) {
                // Update the single admin user (id = 1)
                $sql = "UPDATE users SET username = :username, email = :email, password = :password WHERE id = 1";
                Database::query($sql, [
                    'username' => $newUsername,
                    'email'    => $newEmail,
                    'password' => $hashedPassword
                ]);
            } else {
                // Insert a new admin user if not exists
                $sql = "INSERT INTO users (id, username, email, password) VALUES (1, :username, :email, :password)";
                Database::query($sql, [
                    'username' => $newUsername,
                    'email'    => $newEmail,
                    'password' => $hashedPassword
                ]);
            }
            
            $message = "Vos identifiants administrateur ont été configurés avec succès ! Veuillez supprimer immédiatement ce fichier 'change_admin_prod.php' de votre hébergement par mesure de sécurité.";
            $isSuccess = true;
        } catch (Exception $e) {
            $message = "Erreur lors de la mise à jour : " . $e->getMessage();
        }
    } else {
        $message = "Veuillez remplir tous les champs.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuration du Profil Administrateur - Digitalium Group</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #1d4ed8;
            --primary-hover: #6d28d9;
            --bg: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.7);
            --border: rgba(255, 255, 255, 0.1);
            --text: #f8fafc;
            --text-muted: #94a3b8;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            color: var(--text);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(8, 145, 178, 0.1) 0%, transparent 40%),
                radial-gradient(circle at 90% 80%, rgba(30, 58, 138, 0.1) 0%, transparent 40%);
        }
        .card {
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 32px;
            width: 100%;
            max-width: 440px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.2);
        }
        h2 {
            margin: 0 0 8px 0;
            font-size: 24px;
            font-weight: 700;
            color: var(--text);
            text-align: center;
        }
        .subtitle {
            text-align: center;
            color: var(--text-muted);
            font-size: 14px;
            margin-bottom: 24px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 500;
            color: var(--text-muted);
        }
        input {
            width: 100%;
            padding: 12px 16px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: rgba(15, 23, 42, 0.6);
            color: #fff;
            font-size: 15px;
            box-sizing: border-box;
            transition: all 0.2s;
        }
        input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(8, 145, 178, 0.2);
        }
        button {
            width: 100%;
            padding: 14px;
            border-radius: 8px;
            border: none;
            background: var(--primary);
            color: #fff;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 10px;
        }
        button:hover {
            background: var(--primary-hover);
        }
        .message {
            padding: 16px;
            border-radius: 8px;
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 24px;
            border: 1px solid;
        }
        .message-success {
            background: rgba(16, 185, 129, 0.15);
            border-color: rgba(16, 185, 129, 0.4);
            color: #34d399;
        }
        .message-error {
            background: rgba(239, 68, 68, 0.15);
            border-color: rgba(239, 68, 68, 0.4);
            color: #f87171;
        }
    </style>
</head>
<body>
    <div class="card">
        <h2>Identifiants de Sécurité</h2>
        <div class="subtitle">Configurez le compte administrateur de votre CMS</div>
        
        <?php if (!empty($message)): ?>
            <div class="message <?= $isSuccess ? 'message-success' : 'message-error' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <?php if (!$isSuccess): ?>
            <form method="POST">
                <div class="form-group">
                    <label for="username">Nom d'utilisateur administrateur</label>
                    <input type="text" id="username" name="username" value="admin" required autocomplete="username">
                </div>
                <div class="form-group">
                    <label for="email">Adresse e-mail de contact</label>
                    <input type="email" id="email" name="email" value="admin@digitaliumgroup.com" required autocomplete="email">
                </div>
                <div class="form-group">
                    <label for="password">Nouveau mot de passe de sécurité</label>
                    <input type="password" id="password" name="password" placeholder="Saisissez votre nouveau mot de passe" required autocomplete="new-password">
                </div>
                <button type="submit">Mettre à jour mon profil</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
