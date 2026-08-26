# WOODIN CAMEROUN

Application PHP/MySQL pour le catalogue, le panier, les commandes et l’administration Woodin Cameroun.

## Installation locale WampServer

1. Placer le projet dans le dossier `www` de WampServer.
2. Créer la base `woodin_db` et importer `database/database.sql`.
3. Pour une base existante, importer aussi `database/database_migration_priority4.sql`.
4. Copier `.env.development` vers `.env` et remplacer les valeurs par les identifiants locaux.
5. Vérifier que PHP, PDO MySQL et les uploads sont activés.
6. Ouvrir `http://localhost/SITE%20WEB%20WOODIN/`.

## Environnements

- `.env.development` contient la structure de configuration locale.
- `.env.production` contient la structure de configuration serveur.
- Définir `APP_ENV=prod` dans l’environnement du serveur pour charger `.env.production`. Sans cette variable, l’application reste en mode `dev`.
- Remplacer toutes les valeurs `CHANGE_ME` avant utilisation.
- Ne jamais versionner `.env` ni de vrais mots de passe.

## Sauvegarde MySQL

Sous Linux ou macOS, rendre le script exécutable puis lancer :

```sh
chmod +x scripts/backup.sh
DB_HOST=127.0.0.1 DB_NAME=woodin_db DB_USER=root ./scripts/backup.sh
```

Sous Windows/WampServer, utiliser une commande équivalente depuis le dossier contenant `mysqldump.exe` :

```powershell
& 'C:\wamp\bin\mysql\mysql8.0.x\bin\mysqldump.exe' --host=127.0.0.1 --user=root --password woodin_db > "backups\woodin_db_$(Get-Date -Format yyyyMMdd_HHmmss).sql"
```

Planifier cette commande avec le Planificateur de tâches Windows et tester régulièrement la restauration.

## Avant mise en production

- Acheter et configurer le nom de domaine.
- Configurer les DNS vers l’hébergeur.
- Installer un certificat SSL valide et forcer HTTPS.
- Créer une base MySQL de production et importer `database/database.sql` puis les migrations nécessaires.
- Configurer `.env.production` avec des identifiants distincts de l’environnement local.
- Définir `APP_ENV=prod` côté serveur.
- Installer PHPMailer via Composer si l’envoi SMTP est activé, puis renseigner les variables SMTP.
- Vérifier les permissions du dossier `assets/images/produits/`.
- Configurer les sauvegardes automatiques et tester une restauration.
- Vérifier `robots.txt`, `sitemap.xml`, les pages 403/404 et les logs.
- Exécuter [QA_CHECKLIST.md](QA_CHECKLIST.md) avant l’ouverture aux clients.

## Administration

L’administration est accessible via `admin/login.php`. Modifiez le compte initial après l’installation et n’utilisez jamais le mot de passe de démonstration en production.
