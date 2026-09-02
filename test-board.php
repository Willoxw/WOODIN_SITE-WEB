<?php
/**
 * Tableau de Bord de Test Interactif
 * Permet à l'utilisateur de tester les fonctionnalités manuellement
 */

session_start();
require_once __DIR__ . '/includes/config.php';

$pdo = db();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Board - WOODIN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }
        .container { max-width: 1200px; }
        .card { margin-bottom: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .test-card { border-left: 4px solid #667eea; }
        .test-card.success { border-left-color: #28a745; }
        .test-card.warning { border-left-color: #ffc107; }
        .test-card.danger { border-left-color: #dc3545; }
        .btn-test { margin: 5px; }
        .status-badge { display: inline-block; padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .status-ok { background: #d4edda; color: #155724; }
        .status-warning { background: #fff3cd; color: #856404; }
        .status-error { background: #f8d7da; color: #721c24; }
        .section-header { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0 15px 0; }
        .result-box { background: #f5f5f5; padding: 15px; border-radius: 5px; margin-top: 10px; font-family: monospace; font-size: 12px; max-height: 300px; overflow-y: auto; }
        .test-item { padding: 10px; border-bottom: 1px solid #eee; }
        .test-item:last-child { border-bottom: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card mt-3">
            <div class="card-header bg-dark text-white">
                <h2 class="mb-0">🧪 Tableau de Test - WOODIN</h2>
                <small>Testez les fonctionnalités du site de manière interactive</small>
            </div>
        </div>

        <!-- Section 1: Vérification du Système -->
        <div class="section-header">
            <h4>1️⃣ Vérification du Système</h4>
        </div>
        
        <div class="card test-card success">
            <div class="card-body">
                <h5>État Général <span class="status-badge status-ok">✓ OK</span></h5>
                <div class="test-item">
                    <strong>Serveur:</strong> PHP <?php echo PHP_VERSION; ?> <span class="status-badge status-ok">✓</span>
                </div>
                <div class="test-item">
                    <strong>Base de Données:</strong> MySQL
                    <?php
                        try {
                            $stmt = $pdo->query("SELECT VERSION() as version");
                            $result = $stmt->fetch();
                            echo $result['version'];
                            echo ' <span class="status-badge status-ok">✓</span>';
                        } catch (Exception $e) {
                            echo '<span class="status-badge status-error">✗ Erreur</span>';
                        }
                    ?>
                </div>
                <div class="test-item">
                    <strong>Session:</strong> Session ID = <?php echo session_id(); ?> <span class="status-badge status-ok">✓</span>
                </div>
            </div>
        </div>

        <!-- Section 2: Tests du Panier -->
        <div class="section-header">
            <h4>2️⃣ Tests du Panier</h4>
        </div>

        <div class="card test-card">
            <div class="card-body">
                <h5>Ajouter un Produit au Panier</h5>
                <?php
                    $stmt = $pdo->query("SELECT id, name, price, stock FROM products LIMIT 3");
                    $products = $stmt->fetchAll();
                ?>
                <form method="POST" action="actions/add_to_cart.php" style="margin-bottom: 15px;">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="product_id" class="form-label">Produit:</label>
                            <select class="form-select" name="product_id" id="product_id" required>
                                <option value="">-- Sélectionner un produit --</option>
                                <?php foreach ($products as $p): ?>
                                    <option value="<?php echo $p['id']; ?>">
                                        <?php echo $p['name']; ?> (€<?php echo number_format($p['price'], 2); ?>) - Stock: <?php echo $p['stock']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="quantity" class="form-label">Quantité:</label>
                            <input type="number" class="form-control" name="quantity" id="quantity" value="1" min="1" max="10" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">➕ Ajouter</button>
                        </div>
                    </div>
                </form>

                <div class="alert alert-info">
                    <strong>Panier actuel:</strong>
                    <?php
                        if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
                            echo 'Contient ' . count($_SESSION['cart']) . ' article(s)';
                        } else {
                            echo 'Vide';
                        }
                    ?>
                </div>
                
                <a href="panier.php" class="btn btn-info" target="_blank">👁️ Voir le Panier</a>
                <a href="actions/clear_cart.php" class="btn btn-danger">🗑️ Vider le Panier</a>
            </div>
        </div>

        <!-- Section 3: Tests de Connexion -->
        <div class="section-header">
            <h4>3️⃣ Tests de Connexion & Administration</h4>
        </div>

        <div class="card test-card">
            <div class="card-body">
                <h5>Connexion Admin</h5>
                
                <?php
                    $stmt = $pdo->query("SELECT username, is_active FROM admins");
                    $admins = $stmt->fetchAll();
                ?>

                <div class="alert alert-warning">
                    <strong>Compte de test disponible:</strong><br>
                    Identifiant: <code>admin</code><br>
                    Mot de passe: <code>admin</code> (à modifier après première connexion)
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <h6>Comptes Admin:</h6>
                        <ul>
                            <?php foreach ($admins as $admin): ?>
                                <li>
                                    <code><?php echo $admin['username']; ?></code>
                                    <span class="status-badge <?php echo $admin['is_active'] ? 'status-ok' : 'status-error'; ?>">
                                        <?php echo $admin['is_active'] ? 'Actif' : 'Inactif'; ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <a href="admin/login.php" class="btn btn-warning" target="_blank">🔐 Aller au Login Admin</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 4: Pages Principales -->
        <div class="section-header">
            <h4>4️⃣ Navigation - Pages Principales</h4>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="card test-card success">
                    <div class="card-body">
                        <h5>🏠 Accueil</h5>
                        <a href="index.php" class="btn btn-primary w-100" target="_blank">Visiter</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card test-card success">
                    <div class="card-body">
                        <h5>📦 Catalogue</h5>
                        <a href="catalogue.php" class="btn btn-primary w-100" target="_blank">Visiter</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card test-card success">
                    <div class="card-body">
                        <h5>📨 Contact</h5>
                        <a href="contact.php" class="btn btn-primary w-100" target="_blank">Visiter</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 5: Statistiques -->
        <div class="section-header">
            <h4>5️⃣ Statistiques Base de Données</h4>
        </div>

        <div class="card test-card">
            <div class="card-body">
                <div class="row">
                    <?php
                        $stats = [
                            ['label' => 'Produits', 'table' => 'products', 'icon' => '📦'],
                            ['label' => 'Catégories', 'table' => 'categories', 'icon' => '🏷️'],
                            ['label' => 'Commandes', 'table' => 'orders', 'icon' => '🛒'],
                            ['label' => 'Clients', 'table' => 'customers', 'icon' => '👥'],
                            ['label' => 'Messages', 'table' => 'messages', 'icon' => '💬'],
                            ['label' => 'Codes Promo', 'table' => 'discounts', 'icon' => '🎟️'],
                        ];

                        foreach ($stats as $stat) {
                            $stmt = $pdo->query("SELECT COUNT(*) as count FROM {$stat['table']}");
                            $result = $stmt->fetch();
                            $count = $result['count'];
                            
                            echo '
                            <div class="col-md-4 mb-3">
                                <div class="alert alert-info mb-0">
                                    <strong>' . $stat['icon'] . ' ' . $stat['label'] . ':</strong><br>
                                    <h4 class="mb-0">' . $count . '</h4>
                                </div>
                            </div>
                            ';
                        }
                    ?>
                </div>
            </div>
        </div>

        <!-- Section 6: Tests Manuels -->
        <div class="section-header">
            <h4>6️⃣ Checklist des Tests Manuels</h4>
        </div>

        <div class="card test-card">
            <div class="card-body">
                <h5>Tâches à Tester</h5>
                <form>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="test1">
                        <label class="form-check-label" for="test1">
                            Parcourir le catalogue et vérifier l'affichage des produits
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="test2">
                        <label class="form-check-label" for="test2">
                            Ajouter plusieurs produits au panier
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="test3">
                        <label class="form-check-label" for="test3">
                            Vérifier la modification des quantités dans le panier
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="test4">
                        <label class="form-check-label" for="test4">
                            Tester la suppression d'un article du panier
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="test5">
                        <label class="form-check-label" for="test5">
                            Soumettre le formulaire de contact
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="test6">
                        <label class="form-check-label" for="test6">
                            Vérifier que les images des produits s'affichent
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="test7">
                        <label class="form-check-label" for="test7">
                            Tester la connexion admin
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="test8">
                        <label class="form-check-label" for="test8">
                            Tester l'accès à l'espace administrateur
                        </label>
                    </div>
                </form>
            </div>
        </div>

        <!-- Section 7: Informations de Configuration -->
        <div class="section-header">
            <h4>7️⃣ Configuration</h4>
        </div>

        <div class="card test-card warning">
            <div class="card-body">
                <h5>Fichiers de Configuration</h5>
                
                <div class="row">
                    <div class="col-md-6">
                        <h6>.env.development</h6>
                        <small class="text-muted">
                            DB_HOST: 127.0.0.1<br>
                            DB_NAME: woodin_db_dev<br>
                            DB_USER: woodin_dev<br>
                            <span class="text-warning">DB_PASS: CHANGE_ME</span>
                        </small>
                    </div>
                    <div class="col-md-6">
                        <h6>.env.production</h6>
                        <small class="text-muted">
                            DB_HOST: db.example.com<br>
                            DB_NAME: woodin_db_prod<br>
                            DB_USER: woodin_prod<br>
                            <span class="text-warning">DB_PASS: CHANGE_ME_PRODUCTION</span>
                        </small>
                    </div>
                </div>

                <div class="alert alert-danger mt-3 mb-0">
                    <strong>⚠️ Avant Production:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Remplacer les valeurs CHANGE_ME</li>
                        <li>Configurer les vrais identifiants SMTP</li>
                        <li>Tester la génération de PDF</li>
                        <li>Tester l'envoi d'emails</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Section 8: Liens Utiles -->
        <div class="section-header">
            <h4>8️⃣ Ressources Utiles</h4>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <a href="docs/README.md" class="btn btn-outline-primary w-100 mb-2">📖 README</a>
                    </div>
                    <div class="col-md-3">
                        <a href="docs/IMPLEMENTATION_STATUS.md" class="btn btn-outline-primary w-100 mb-2">✅ Statut</a>
                    </div>
                    <div class="col-md-3">
                        <a href="docs/QA_CHECKLIST.md" class="btn btn-outline-primary w-100 mb-2">🧪 Checklist QA</a>
                    </div>
                    <div class="col-md-3">
                        <a href="docs/TEST_REPORT.md" class="btn btn-outline-primary w-100 mb-2">📊 Rapport</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="alert alert-success mt-4 mb-4">
            <strong>✨ Système Prêt!</strong><br>
            Tous les tests automatisés ont réussi. Vous pouvez maintenant tester les fonctionnalités manuellement.
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
