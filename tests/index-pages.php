<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>WOODIN - Index de toutes les pages</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5c518 0%, #8b0000 100%);
            min-height: 100vh;
            padding: 40px 0;
        }
        .container {
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h1 {
            color: #8b0000;
            font-weight: 900;
            margin-bottom: 40px;
            text-align: center;
            font-family: 'Playfair Display', serif;
        }
        .pages-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .page-card {
            background: #fafafa;
            border: 2px solid #f5c518;
            border-radius: 8px;
            padding: 20px;
            transition: all 0.3s ease;
        }
        .page-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(245, 197, 24, 0.3);
            border-color: #8b0000;
        }
        .page-card h3 {
            color: #8b0000;
            font-size: 1.1rem;
            margin-bottom: 10px;
        }
        .page-card p {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }
        .page-card a {
            display: inline-block;
            background: #f5c518;
            color: #1a1a1a;
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        .page-card a:hover {
            background: #8b0000;
            color: white;
        }
        .status {
            display: inline-block;
            font-size: 0.75rem;
            padding: 4px 8px;
            border-radius: 20px;
            margin-top: 10px;
            font-weight: 600;
        }
        .status.ready {
            background: #25d366;
            color: white;
        }
        .status.test {
            background: #2196F3;
            color: white;
        }
        .status.db-required {
            background: #FF9800;
            color: white;
        }
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196F3;
            padding: 20px;
            border-radius: 4px;
            margin-bottom: 30px;
        }
        .info-box h4 {
            color: #1565c0;
            margin-bottom: 10px;
        }
        .tech-stack {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
        }
        .badge-tech {
            display: inline-block;
            background: #8b0000;
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            margin: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎨 WOODIN Cameroun - Index Complet</h1>
        
        <div class="info-box">
            <h4><i class="fa-solid fa-circle-info"></i> Information</h4>
            <p>
                <strong>Serveur actif sur :</strong> <code>http://localhost:8000</code><br>
                <strong>Toutes les pages publiques</strong> sont accessibles ci-dessous avec les améliorations visuelles premium implémentées.
            </p>
        </div>

        <h2 style="color: #8b0000; margin-bottom: 30px; font-size: 1.5rem;">📑 Pages Publiques</h2>
        
        <div class="pages-grid">
            <!-- Page Accueil -->
            <div class="page-card">
                <h3><i class="fa-solid fa-home"></i> Accueil</h3>
                <p>Page d'accueil principale avec hero premium, stats animées et collections phares.</p>
                <a href="http://localhost:8000/index.php" target="_blank">Ouvrir <i class="fa-solid fa-arrow-right"></i></a>
                <div class="status db-required">Requiert DB MySQL</div>
            </div>

            <!-- Page Catalogue -->
            <div class="page-card">
                <h3><i class="fa-solid fa-store"></i> Catalogue</h3>
                <p>Catalogue complet avec recherche, filtres, tri et pagination.</p>
                <a href="http://localhost:8000/catalogue.php" target="_blank">Ouvrir <i class="fa-solid fa-arrow-right"></i></a>
                <div class="status db-required">Requiert DB MySQL</div>
            </div>

            <!-- Page Produit -->
            <div class="page-card">
                <h3><i class="fa-solid fa-shirt"></i> Détail Produit</h3>
                <p>Page détail d'un produit (remplacer ID par un ID existant).</p>
                <a href="http://localhost:8000/produit.php?id=1" target="_blank">Ouvrir <i class="fa-solid fa-arrow-right"></i></a>
                <div class="status db-required">Requiert DB MySQL</div>
            </div>

            <!-- Page Boutiques -->
            <div class="page-card">
                <h3><i class="fa-solid fa-location-dot"></i> Nos Boutiques</h3>
                <p>Localisation des 4 boutiques WOODIN au Cameroun avec Google Maps.</p>
                <a href="http://localhost:8000/boutiques.php" target="_blank">Ouvrir <i class="fa-solid fa-arrow-right"></i></a>
                <div class="status ready">✅ Prêt</div>
            </div>

            <!-- Page Contact -->
            <div class="page-card">
                <h3><i class="fa-solid fa-envelope"></i> Contact</h3>
                <p>Formulaire de contact et informations de communication.</p>
                <a href="http://localhost:8000/contact.php" target="_blank">Ouvrir <i class="fa-solid fa-arrow-right"></i></a>
                <div class="status db-required">Requiert DB MySQL</div>
            </div>

            <!-- Page Panier -->
            <div class="page-card">
                <h3><i class="fa-solid fa-bag-shopping"></i> Panier</h3>
                <p>Panier d'achat avec résumé et actions.</p>
                <a href="http://localhost:8000/panier.php" target="_blank">Ouvrir <i class="fa-solid fa-arrow-right"></i></a>
                <div class="status db-required">Requiert DB MySQL</div>
            </div>

            <!-- Page Commande Réussie -->
            <div class="page-card">
                <h3><i class="fa-solid fa-check-circle"></i> Commande Réussie</h3>
                <p>Page de confirmation après une commande.</p>
                <a href="http://localhost:8000/order_success.php" target="_blank">Ouvrir <i class="fa-solid fa-arrow-right"></i></a>
                <div class="status db-required">Requiert DB MySQL</div>
            </div>

            <!-- Erreur 404 -->
            <div class="page-card">
                <h3><i class="fa-solid fa-triangle-exclamation"></i> Erreur 404</h3>
                <p>Page d'erreur 404 (page non trouvée).</p>
                <a href="http://localhost:8000/404.php" target="_blank">Ouvrir <i class="fa-solid fa-arrow-right"></i></a>
                <div class="status ready">✅ Prêt</div>
            </div>

            <!-- Erreur 403 -->
            <div class="page-card">
                <h3><i class="fa-solid fa-lock"></i> Erreur 403</h3>
                <p>Page d'erreur 403 (accès refusé).</p>
                <a href="http://localhost:8000/403.php" target="_blank">Ouvrir <i class="fa-solid fa-arrow-right"></i></a>
                <div class="status ready">✅ Prêt</div>
            </div>

            <!-- Page Test Visuels -->
            <div class="page-card">
                <h3><i class="fa-solid fa-sparkles"></i> Test Visuels</h3>
                <p>Page de démonstration de tous les effets visuels premium implémentés.</p>
                <a href="http://localhost:8000/tests/test-visual-effects.php" target="_blank">Ouvrir <i class="fa-solid fa-arrow-right"></i></a>
                <div class="status test">🧪 Page Test</div>
            </div>
        </div>

        <hr style="margin: 40px 0;">

        <h2 style="color: #8b0000; margin-bottom: 30px; font-size: 1.5rem;">👤 Pages Client (Authentifiées)</h2>
        
        <div class="pages-grid">
            <!-- Login Client -->
            <div class="page-card">
                <h3><i class="fa-solid fa-sign-in-alt"></i> Connexion Client</h3>
                <p>Page de connexion pour les clients existants.</p>
                <a href="http://localhost:8000/client/login.php" target="_blank">Ouvrir <i class="fa-solid fa-arrow-right"></i></a>
                <div class="status db-required">Requiert DB MySQL</div>
            </div>

            <!-- Register Client -->
            <div class="page-card">
                <h3><i class="fa-solid fa-user-plus"></i> Inscription Client</h3>
                <p>Création d'un nouveau compte client.</p>
                <a href="http://localhost:8000/client/register.php" target="_blank">Ouvrir <i class="fa-solid fa-arrow-right"></i></a>
                <div class="status db-required">Requiert DB MySQL</div>
            </div>

            <!-- Mes Commandes -->
            <div class="page-card">
                <h3><i class="fa-solid fa-list"></i> Mes Commandes</h3>
                <p>Historique des commandes du client (authentification requise).</p>
                <a href="http://localhost:8000/client/mes-commandes.php" target="_blank">Ouvrir <i class="fa-solid fa-arrow-right"></i></a>
                <div class="status db-required">Auth + DB MySQL</div>
            </div>

            <!-- Mes Factures -->
            <div class="page-card">
                <h3><i class="fa-solid fa-file-invoice"></i> Mes Factures</h3>
                <p>Téléchargement des factures PDF (authentification requise).</p>
                <a href="http://localhost:8000/client/mes-factures.php" target="_blank">Ouvrir <i class="fa-solid fa-arrow-right"></i></a>
                <div class="status db-required">Auth + DB MySQL</div>
            </div>

            <!-- Mon Compte -->
            <div class="page-card">
                <h3><i class="fa-solid fa-user-circle"></i> Mon Compte</h3>
                <p>Gestion du profil et des paramètres (authentification requise).</p>
                <a href="http://localhost:8000/client/mon-compte.php" target="_blank">Ouvrir <i class="fa-solid fa-arrow-right"></i></a>
                <div class="status db-required">Auth + DB MySQL</div>
            </div>
        </div>

        <hr style="margin: 40px 0;">

        <h2 style="color: #8b0000; margin-bottom: 30px; font-size: 1.5rem;">⚙️ Pages Admin (Sécurisées)</h2>
        
        <div class="info-box" style="background: #fff3e0; border-left-color: #FF9800;">
            <h4 style="color: #E65100;"><i class="fa-solid fa-shield"></i> Accès Sécurisé</h4>
            <p>Les pages admin sont protégées par authentification. Accès réservé aux administrateurs.</p>
        </div>

        <div class="pages-grid">
            <div class="page-card">
                <h3><i class="fa-solid fa-lock"></i> Admin - Connexion</h3>
                <p>Page de connexion administrateur sécurisée.</p>
                <a href="http://localhost:8000/admin/login.php" target="_blank">Ouvrir <i class="fa-solid fa-arrow-right"></i></a>
                <div class="status db-required">Auth + DB MySQL</div>
            </div>

            <div class="page-card">
                <h3><i class="fa-solid fa-dashboard"></i> Admin - Tableau de Bord</h3>
                <p>Accueil administrateur avec statistiques et actions rapides.</p>
                <a href="http://localhost:8000/admin/index.php" target="_blank">Ouvrir <i class="fa-solid fa-arrow-right"></i></a>
                <div class="status db-required">Auth + DB MySQL</div>
            </div>

            <div class="page-card">
                <h3><i class="fa-solid fa-boxes"></i> Admin - Produits</h3>
                <p>Gestion des produits (CRUD).</p>
                <a href="http://localhost:8000/admin/products.php" target="_blank">Ouvrir <i class="fa-solid fa-arrow-right"></i></a>
                <div class="status db-required">Auth + DB MySQL</div>
            </div>

            <div class="page-card">
                <h3><i class="fa-solid fa-tags"></i> Admin - Catégories</h3>
                <p>Gestion des catégories de produits.</p>
                <a href="http://localhost:8000/admin/categories.php" target="_blank">Ouvrir <i class="fa-solid fa-arrow-right"></i></a>
                <div class="status db-required">Auth + DB MySQL</div>
            </div>

            <div class="page-card">
                <h3><i class="fa-solid fa-cart-shopping"></i> Admin - Commandes</h3>
                <p>Suivi et gestion des commandes clients.</p>
                <a href="http://localhost:8000/admin/orders.php" target="_blank">Ouvrir <i class="fa-solid fa-arrow-right"></i></a>
                <div class="status db-required">Auth + DB MySQL</div>
            </div>

            <div class="page-card">
                <h3><i class="fa-solid fa-users"></i> Admin - Clients</h3>
                <p>Gestion de la base de clients.</p>
                <a href="http://localhost:8000/admin/customers.php" target="_blank">Ouvrir <i class="fa-solid fa-arrow-right"></i></a>
                <div class="status db-required">Auth + DB MySQL</div>
            </div>

            <div class="page-card">
                <h3><i class="fa-solid fa-percent"></i> Admin - Promotions</h3>
                <p>Gestion des réductions et promotions.</p>
                <a href="http://localhost:8000/admin/product_promotions.php" target="_blank">Ouvrir <i class="fa-solid fa-arrow-right"></i></a>
                <div class="status db-required">Auth + DB MySQL</div>
            </div>

            <div class="page-card">
                <h3><i class="fa-solid fa-coupon"></i> Admin - Codes Promo</h3>
                <p>Gestion des codes de réduction.</p>
                <a href="http://localhost:8000/admin/discounts.php" target="_blank">Ouvrir <i class="fa-solid fa-arrow-right"></i></a>
                <div class="status db-required">Auth + DB MySQL</div>
            </div>
        </div>

        <hr style="margin: 40px 0;">

        <div class="tech-stack">
            <h3 style="color: #8b0000; margin-bottom: 20px;">🔧 Stack Technique Utilisé</h3>
            <div>
                <span class="badge-tech"><i class="fa-solid fa-leaf"></i> PHP 8+</span>
                <span class="badge-tech"><i class="fa-brands fa-mysql"></i> MySQL/MariaDB</span>
                <span class="badge-tech"><i class="fa-brands fa-bootstrap"></i> Bootstrap 5</span>
                <span class="badge-tech"><i class="fa-brands fa-js"></i> Vanilla JavaScript</span>
                <span class="badge-tech"><i class="fa-solid fa-palette"></i> CSS3 + Animations</span>
                <span class="badge-tech"><i class="fa-solid fa-layer-group"></i> AOS.js</span>
                <span class="badge-tech"><i class="fa-brands fa-font-awesome"></i> Font Awesome 6</span>
                <span class="badge-tech"><i class="fa-solid fa-file-pdf"></i> DOMPDF</span>
                <span class="badge-tech"><i class="fa-brands fa-git"></i> Git</span>
            </div>
        </div>

        <div style="background: #f0f0f0; padding: 20px; border-radius: 8px; margin-top: 30px; text-align: center; color: #666;">
            <p style="margin: 0;">
                <strong>Note :</strong> Pages marquées "Requiert DB MySQL" nécessitent une base de données configurée.<br>
                Pour configurer la DB, suivez les instructions dans <code>docs/README.md</code>
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
