# WOODIN CAMEROUN — Points non conformes / a corriger

Date de synthese : 2026-08-31

## 1) Environnement technique

### 1.1. PHP incompatible avec la stack actuelle
- Le projet utilise une version PHP trop ancienne pour le projet moderne : PHP 5.5.12.
- Composer a ete installe localement dans le projet, mais la version PHP du systeme est trop ancienne pour un usage fiable.
- Recommandation : utiliser PHP 8.x (dans WAMP ou environnement de dev) pour Composer, Dompdf et PHPMailer.

### 1.2. Composer absent dans l'environnement de travail
- Le fichier [composer.json](../composer.json) declare des dependances necessaires : dompdf et phpmailer.
- Dans l'environnement courant, Composer n'etait pas disponible au depart.
- Le projet a ete corrige localement via installation manuelle de Composer, mais il faut un environnement propre et stable.

### 1.3. Services WAMP non demarres via service Windows
- Les services `wampapache64` et `wampmysqld64` ne sont pas disponibles / demarres dans cet environnement.
- Le projet a du etre lance via PHP built-in server et MySQL manuel.
- Cela ne remplace pas une installation WAMP propre et fonctionnelle pour le developpement local.

### 1.4. Fichier .env manquant au depart
- Le projet attend un fichier [.env](../.env) pour les donnees de connexion et SMTP.
- Seules les templates [.env.development](../.env.development) et [.env.production](../.env.production) existaient.
- Le fichier local a ete cree pour faire tourner le site, mais il faut le remplacer par des vraies valeurs de prod/dev.

### 1.5. Valeurs d'environnement placeholders
- Les fichiers env contiennent des valeurs `CHANGE_ME` ou `example` dans la structure de configuration.
- Cela est inacceptable pour un environnement de production.
- Il faut remplacer par des vrais identifiants MySQL/SMTP et une configuration ciblee.

## 2) Base de donnees

### 2.1. Base locale non prete au depart
- La base `woodin_db` n'était pas importee dans l'environnement local au depart.
- Le projet fonctionnait seulement a partir de fichiers PHP, sans vraie base exploitable.

### 2.2. Mot de passe admin local incorrect / non valide
- Le compte admin seed est present dans la base, mais le mot de passe initial n'etait pas compatible avec le login fonctionnel.
- Cela a bloque le parcours d'authentification admin.
- Le compte doit etre reconfigure avec un mot de passe maitre reel pour l'environnement de test/production.

### 2.3. Validation SQL incomplete
- La base a ete importe localement, mais la validation complete des flux metiers et des migrations n'est pas encore terminee.
- Il reste a tester les commandes, le stock, les promotions, le statut des commandes et les factures.

## 3) Code applicatif / fonctionnalites a verifier

### 3.1. Fichiers assets manquants au depart
- Le site a affichait des 404 sur des ressources statiques.
- Les images de catalogue et l'image Open Graph etaient absentes, provoquant des erreurs visuelles et SEO degrade.
- Les assets ont ete recrees localement pour stabiliser le rendu.

### 3.2. Fichiers incontournables a proteger et a valider
- [.htaccess](../.htaccess) protege bien les environnements et certains dossiers sensibles.
- Cela doit etre verifie sur le vrai serveur de production.

### 3.3. SMTP / PDF non verifie reellement
- Les fichiers [composer.json](../composer.json), [includes/functions.php](../includes/functions.php) et [includes/generate_invoice.php](../includes/generate_invoice.php) supposent que PHPMailer et Dompdf sont disponibles et configures.
- L'envoi reel des emails et la generation des factures PDF doivent etre testes avec de vrais parametres SMTP.

## 4) Ce qui est fonctionnel pour l'instant

- Syntaxe PHP globalement OK sur les fichiers du projet.
- Les pages principales chargent correctement localement.
- Login admin fonctionne avec un compte local configure.
- Le site public se sert bien en local.
- Les fichiers critiques sont en place et les 404 initiaux ont ete corrigees.

## 5) Ce qu'il faut faire pour que tout soit propre

1. Mettre en place un environnement PHP 8.x + WAMP ou serveur fonctionnel.
2. Supprimer toute valeur placeholder dans les fichiers env.
3. Configurer DB_HOST, DB_NAME, DB_USER, DB_PASS, SMTP_* avec de vraies donnees.
4. Verifier le fichier [.env](../.env) et le garder hors du repo pour production.
5. Valider le parcours complet client : panier -> commande -> facture -> email.
6. Valider le parcours admin : commandes, stock, promotions, utilisateurs.
7. Executer la checklist QA dans [docs/QA_CHECKLIST.md](../docs/QA_CHECKLIST.md).
8. Verifier le HTTPs, les logs, les sauvegardes et la protection des dossiers sensibles.

## 6) Conclusion

Le projet n'est pas encore "tout bon pour la mise en ligne", mais il est maintenant mis en etat de test local de maniere correcte.
Le principal travail restant est l'environnement de production/validation reelle, pas la logique de base du code.
