# WOODIN CAMEROUN - Suivi d'implementation

Derniere mise a jour : 28/08/2026

Ce document conserve le suivi des ameliorations fonctionnelles des points 12 a 25.

## Etat general

Les points 12 a 20 et 25 ont ete implementes dans le code local.

Validation deja effectuee :

- Tous les fichiers PHP passent `php -l`.
- `git diff --check` ne signale aucune erreur.
- Les dependances Composer n'ont pas ete testees ici, car Composer n'est pas disponible dans l'environnement courant.
- Les tests avec une base MySQL reelle restent a executer.

## Fonctionnalites implementees

### Point 12 - Pagination

Fichiers :

- `catalogue.php` : 12 produits par page.
- `admin/products.php` : 15 produits par page.
- `admin/orders.php` : 20 commandes par page.
- `admin/customers.php` : 20 clients par page.

Les totaux sont calcules avec `COUNT(*)`, les requetes utilisent `LIMIT/OFFSET`, et les filtres `filter` et `q` sont conserves dans les liens de pagination.

### Point 13 - Navigation admin

Fichiers :

- `includes/admin_header.php`
- `admin/auth.php`

La navigation est centralisee et injectee dans les pages admin protegees. Elle contient les sections Catalogue, Ventes, Stock, Communication et Systeme.

Badges dynamiques :

- Commandes en attente depuis plus de 48 heures.
- Messages non lus.

### Point 14 - Codes promo

Fichier : `admin/discounts.php`

- Edition via `?action=edit&id=X`.
- Formulaire pre-rempli.
- UPDATE si un identifiant est fourni, INSERT sinon.
- Suppression reservee au `super_admin`.
- Suppression bloquee si `usage_count > 0`.
- Confirmation via modale Bootstrap.

### Point 15 - Categories

Fichier : `admin/categories.php`

- Edition et formulaire pre-rempli.
- Suppression reservee au `super_admin`.
- Suppression bloquee si des produits utilisent la categorie.
- Confirmation via modale Bootstrap.

### Point 16 - Roles administrateur

Fichiers :

- `includes/functions.php`
- `admin/login.php`
- `admin/auth.php`
- `admin/users.php`

Roles disponibles : `super_admin` et `gestionnaire`.

- Le role est stocke en session apres connexion.
- Les comptes inactifs ne peuvent pas se connecter.
- Une desactivation prend effet lors de la prochaine verification de session.
- `admin/users.php` permet de creer un compte et de le desactiver sans suppression.
- La sauvegarde et les suppressions sensibles sont reservees au `super_admin`.

### Point 17 - mysqldump configurable

Fichiers :

- `admin/backup_db.php`
- `.env.development`
- `.env.production`
- `docs/README.md`

Variable :

```env
MYSQLDUMP_PATH=mysqldump
```

Sous Windows, un chemin complet vers `mysqldump.exe` peut etre defini. Une detection automatique de chemins WampServer est prevue.

### Point 18 - Historique des statuts

Fichiers :

- `admin/update_order_status.php`
- `admin/orders.php`
- `database/database.sql`
- `database/database_migration_priority4.sql`

La table `order_status_history` enregistre l'ancien statut, le nouveau statut, l'administrateur, la date et la note. La modale de commande affiche la timeline.

### Point 19 - Confirmations de suppression

Fichiers :

- `admin/products.php`
- `admin/categories.php`
- `admin/discounts.php`

Les suppressions utilisent des modales Bootstrap et des formulaires POST proteges par CSRF.

### Point 20 - Quantites catalogue

Fichier : `catalogue.php`

- Les quantites sont envoyees a `actions/add_to_cart.php`.
- Des boutons `+` et `-` sont ajoutes aux champs quantite.
- La limite est le stock disponible.
- Une rupture de stock est signalee et le bouton d'ajout est desactive.

### Point 25 - Images orphelines

Fichier : `admin/products.php`

- L'image principale est recuperee avant suppression.
- Les images de galerie sont recuperees avant suppression.
- Les fichiers sont supprimes uniquement si leur chemin commence par `assets/images/`.
- L'ancienne image est supprimee lors du remplacement par une nouvelle image.

## SQL a appliquer

Sur une nouvelle installation, importer :

1. `database/database.sql`

Pour une base existante, executer la migration :

1. `database/database_migration_priority4.sql`

Cette migration ajoute notamment les roles admin et la table `order_status_history`.

Verifier la migration sur une copie de la base avant production, car certaines anciennes instructions `ALTER TABLE` peuvent dependre de la version exacte deja installee.

## A faire avant production

- Creer `.env` a partir de `.env.development` et remplacer les valeurs `CHANGE_ME`.
- Configurer les vrais identifiants MySQL.
- Configurer les vrais identifiants SMTP.
- Installer les dependances avec Composer : `composer install`.
- Verifier que Dompdf et PHPMailer sont disponibles.
- Importer ou migrer la base MySQL.
- Tester la connexion admin avec les deux roles.
- Tester la desactivation d'un compte.
- Tester les suppressions et les blocages metier.
- Tester la creation de commande, le stock, les promotions et les factures PDF.
- Tester l'historique des statuts dans les modales de commandes.
- Tester la sauvegarde et la restauration MySQL.
- Executer `docs/QA_CHECKLIST.md`.
- Verifier HTTPS, les permissions d'upload et les logs.

## Fichiers sensibles a ne jamais exposer

- `.env`
- `.env.development`
- `.env.production`
- `database/`
- `includes/`
- `docs/`
- `backups/`
- `invoices/`

Le fichier `.htaccess` bloque les fichiers d'environnement et les dossiers internes sur Apache. Cette protection doit aussi etre verifiee dans la configuration du serveur de production.

## Points 21 a 24 - SEO, favicon, sauvegardes et monitoring

### Point 21 - Open Graph

Fichiers : `includes/header.php` et `produit.php`.

Le header central fournit `og:type`, `og:title`, `og:description`, `og:image`, `og:url`, `og:site_name` et `og:locale`. Les pages produit utilisent le nom, la description et l'image du produit.

### Point 22 - Favicon

Fichier : `includes/header.php`.

`assets/favicon.svg` existe et est utilise de maniere centralisee. Les liens ICO et Apple touch icon sont ajoutes uniquement si les fichiers correspondants existent physiquement.

### Point 23 - Sauvegardes automatisees

Fichiers : `scripts/backup.sh`, `docs/README.md`.

Le script genere un dump MySQL, le compresse en `.sql.gz`, journalise les succes et echecs dans `scripts/backup.log`, puis supprime les sauvegardes de plus de sept jours. Les instructions cron Linux et Planificateur de taches Windows sont documentees.

### Point 24 - Monitoring

Fichiers : `includes/bootstrap.php`, `includes/403.php`, `includes/500.php`, `.htaccess`, `.gitignore`.

En production (`APP_ENV=prod`), les erreurs sont masquees, journalisees dans `logs/error.log` et les exceptions/fautes fatales affichent une page 500 sans detail technique. Le dossier `logs/` est bloque par Apache et les logs sont ignores par Git. Le suivi cron et l'option Sentry sont documentes dans `docs/README.md`.
