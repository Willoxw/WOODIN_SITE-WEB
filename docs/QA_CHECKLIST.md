# Checklist QA avant mise en ligne

## Parcours client

- [ ] Ouvrir l’accueil, le catalogue, une fiche produit et la page contact.
- [ ] Vérifier qu’une image produit réelle s’affiche avec un texte alternatif.
- [ ] Ajouter un produit avec quantité 1, puis plusieurs unités depuis le catalogue.
- [ ] Augmenter la quantité dans le panier jusqu’à la limite du stock.
- [ ] Vérifier qu’une quantité supérieure au stock est refusée.
- [ ] Retirer un article, vider le panier et vérifier le compteur.
- [ ] Finaliser une commande valide et contrôler le numéro, le total et les lignes du récapitulatif.
- [ ] Tester une commande avec stock à 0.
- [ ] Tester une commande avec un téléphone `6XXXXXXXX` puis `+2376XXXXXXXX`.
- [ ] Tester un téléphone invalide, un nom invalide et un email invalide.
- [ ] Soumettre chaque formulaire POST sans token CSRF et vérifier la page 403.
- [ ] Soumettre le formulaire contact valide et vérifier son enregistrement en administration.

## Administration

- [ ] Se connecter avec les identifiants valides.
- [ ] Tester cinq mauvais mots de passe et vérifier le verrouillage temporaire.
- [ ] Vérifier l’expiration après 20 minutes d’inactivité.
- [ ] Créer une catégorie et vérifier son affichage dans le catalogue.
- [ ] Créer, modifier et supprimer un produit.
- [ ] Tester un upload JPG, JPEG, PNG et WEBP valide.
- [ ] Tester un fichier de plus de 2 Mo et une extension interdite.
- [ ] Ajouter plusieurs images dans la galerie et désigner l’image principale.
- [ ] Vérifier les miniatures et le changement d’image sur la fiche produit.
- [ ] Modifier le stock et vérifier le mouvement `correction` ou `réapprovisionnement`.
- [ ] Vérifier le mouvement `vente` après une commande.
- [ ] Filtrer l’historique du stock par produit.
- [ ] Modifier chacun des quatre statuts de commande.
- [ ] Vérifier que les actions admin sans CSRF sont refusées.

## Déploiement

- [ ] Importer `database/database.sql` sur une nouvelle base.
- [ ] Exécuter `database/database_migration_priority4.sql` sur une base existante.
- [ ] Configurer `.env` hors de la racine publique si possible.
- [ ] Vérifier que `.env`, `database/database.sql` et `includes/` ne sont pas accessibles directement.
- [ ] Vérifier HTTPS en production et le fonctionnement local sur WampServer.
- [ ] Vérifier les logs sans afficher de détails SQL aux visiteurs.
- [ ] Vérifier la sauvegarde et la restauration MySQL avant mise en ligne.
