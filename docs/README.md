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

La sauvegarde depuis l’administration utilise `MYSQLDUMP_PATH`. Sous Windows, définir le chemin complet vers `mysqldump.exe` si la détection automatique ne trouve pas l’installation WampServer. En production Linux, laisser `MYSQLDUMP_PATH=mysqldump` si le binaire est dans le `PATH`.

Planifier cette commande avec le Planificateur de tâches Windows et tester régulièrement la restauration.

## SAUVEGARDES AUTOMATIQUES

### Linux (production)

Ajouter une tâche quotidienne avec `crontab -e` :

```cron
0 2 * * * /bin/bash /var/www/woodin/scripts/backup.sh
```

### Windows (WampServer de développement)

Utiliser le Planificateur de tâches Windows avec l'action suivante :

```text
C:\Git\bin\bash.exe scripts/backup.sh
```

Configurer un déclencheur quotidien à 02h00. Consulter `scripts/backup.log` pour vérifier l'exécution. Les sauvegardes compressées sont conservées dans `backups/` au format `.sql.gz`, avec rotation des fichiers âgés de plus de sept jours.

## MONITORING

### Développement

Consulter manuellement `logs/error.log` après un parcours de test. Le mode de développement affiche les erreurs PHP pour faciliter le diagnostic. Utiliser `APP_ENV=dev` dans l'environnement local.

### Production

Définir `APP_ENV=prod` côté serveur pour charger `.env.production`. Les erreurs sont masquées aux visiteurs et écrites dans `logs/error.log`. Une vérification quotidienne peut être ajoutée à cron :

```cron
0 8 * * * [ -s /var/www/woodin/logs/error.log ] && mail -s "WOODIN - Erreurs détectées" admin@example.com < /var/www/woodin/logs/error.log
```

Pour un suivi avancé, ajouter éventuellement `SENTRY_DSN=https://xxxxx@sentry.io/xxxxx` dans la configuration et intégrer le SDK Sentry adapté à l'hébergement.

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
