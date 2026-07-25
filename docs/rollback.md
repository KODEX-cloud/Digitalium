# Procédures de Rollback — Digitalium

## Option 1 — Recovery Center (recommandé, sans SSH)

URL : https://digitaliumgroup.com/admin/system/recovery

1. Se connecter à l'admin
2. Aller sur **Recovery Center**
3. Cliquer **"Lancer la restauration complète"**

Pipeline automatique en 11 phases avec backup SQL préalable.

---

## Option 2 — GitHub Actions (rollback SQL)

1. https://github.com/KODEX-cloud/Digitalium/actions
2. **"Deploy — Digitalium Group CMS"** → **"Run workflow"**
3. Sélectionner mode : **`rollback`**
4. Cliquer **"Run workflow"**

Lance `bin/deploy.php --mode=rollback` sur le serveur (restaure le dernier backup SQL créé avant le déploiement précédent).

---

## Option 3 — Deploy Center admin

URL : https://digitaliumgroup.com/admin/system/deploy-center

Bouton **"Rollback"** dans l'interface.

---

## Option 4 — Git revert (rollback code)

Si le problème vient du code, pas de la base de données :

```bash
# En local
git log --oneline -10          # Trouver le commit stable
git revert HEAD                # Annuler le dernier commit
git push origin main           # Déclenche redéploiement automatique
```

---

## Option 5 — SSH direct (urgence totale)

```bash
ssh -p 65002 UTILISATEUR@IP_HOSTINGER
cd /home/UTILISATEUR/domains/digitaliumgroup.com/public_html

# Rollback Git vers commit précédent
git log --oneline -5
git reset --hard COMMIT_HASH

# Rollback SQL depuis le dernier backup
php8.3 bin/deploy.php --mode=rollback

# Vider le cache
find storage/cache -type f -delete
```

---

## Localisation des backups SQL

Sur le serveur : `storage/backups/` (créés automatiquement à chaque déploiement par `RollbackManager`).

---

## Prévention

Le pipeline crée automatiquement un backup SQL **avant** chaque déploiement.
Ne jamais supprimer `storage/backups/` manuellement.
