# Digitalium CMS Local Development Launcher
# Run this script in PowerShell to boot up MySQL and the PHP server.

Write-Host "=== DEMARRAGE DE L'ENVIRONNEMENT DE DEVELOPPEMENT ===" -ForegroundColor Cyan

# 1. Start MySQL Standalone if not already running
$mysqlProc = Get-Process -Name mysqld -ErrorAction SilentlyContinue
if ($mysqlProc) {
    Write-Host "MySQL est déjà en cours d'exécution." -ForegroundColor Green
} else {
    Write-Host "Démarrage de MySQL en arrière-plan..." -ForegroundColor Yellow
    Start-Process -FilePath "c:\wamp64\bin\mysql\mysql8.4.7\bin\mysqld.exe" -ArgumentList '--defaults-file="c:\wamp64\bin\mysql\mysql8.4.7\my.ini"', "--standalone" -NoNewWindow
    Start-Sleep -Seconds 2
}

# 2. Start PHP Built-in Server if not already running
$phpPort = 8000
$portActive = Get-NetTCPConnection -LocalPort $phpPort -ErrorAction SilentlyContinue
if ($portActive) {
    Write-Host "Le serveur de développement PHP est déjà actif sur le port $phpPort." -ForegroundColor Green
} else {
    Write-Host "Démarrage du serveur PHP sur http://127.0.0.1:$phpPort ..." -ForegroundColor Yellow
    
    # Start PHP in a separate window to keep logs visible
    Start-Process -FilePath "c:\wamp64\bin\php\php8.2.29\php.exe" -ArgumentList "-S 127.0.0.1:$phpPort", "-t public" -NoNewWindow
    Start-Sleep -Seconds 1
}

# 3. Launch default browser tabs
Write-Host "Lancement du navigateur..." -ForegroundColor Cyan
Start-Process "http://127.0.0.1:$phpPort/"
Start-Process "http://127.0.0.1:$phpPort/admin"

Write-Host "=== ENVIRONNEMENT PRÊT ! ===" -ForegroundColor Green
Write-Host "Site public : http://127.0.0.1:$phpPort/" -ForegroundColor White
Write-Host "Administration : http://127.0.0.1:$phpPort/admin (admin / password123)" -ForegroundColor White
