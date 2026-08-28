<?php
$pendingOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'En attente' AND created_at < DATE_SUB(NOW(), INTERVAL 48 HOUR)")->fetchColumn();
$unreadMessages = (int)$pdo->query('SELECT COUNT(*) FROM messages WHERE is_read = 0')->fetchColumn();
?>
<nav class="navbar navbar-dark bg-dark">
  <div class="container-fluid align-items-start">
    <a class="navbar-brand text-warning" href="index.php">WOODIN ADMIN</a>
    <div class="d-flex flex-wrap gap-2 align-items-center">
      <span class="text-secondary small text-uppercase">Catalogue</span>
      <a class="btn btn-outline-light btn-sm" href="products.php">Produits</a>
      <a class="btn btn-outline-light btn-sm" href="categories.php">Catégories</a>
      <a class="btn btn-outline-light btn-sm" href="product_images.php">Images produits</a>
      <a class="btn btn-outline-light btn-sm" href="product_promotions.php">Promotions</a>
      <span class="text-secondary small text-uppercase ms-2">Ventes</span>
      <a class="btn btn-outline-light btn-sm" href="orders.php">Commandes <?php if ($pendingOrders): ?><span class="badge bg-danger"><?= $pendingOrders ?></span><?php endif; ?></a>
      <a class="btn btn-outline-light btn-sm" href="discounts.php">Codes promo</a>
      <a class="btn btn-outline-light btn-sm" href="customers.php">Clients</a>
      <span class="text-secondary small text-uppercase ms-2">Stock</span>
      <a class="btn btn-outline-light btn-sm" href="stock_history.php">Historique</a>
      <span class="text-secondary small text-uppercase ms-2">Communication</span>
      <a class="btn btn-outline-light btn-sm" href="messages.php">Messages <?php if ($unreadMessages): ?><span class="badge bg-danger"><?= $unreadMessages ?></span><?php endif; ?></a>
      <span class="text-secondary small text-uppercase ms-2">Système</span>
      <?php if ((isset($_SESSION['admin_role']) ? $_SESSION['admin_role'] : '') === 'super_admin'): ?><a class="btn btn-outline-light btn-sm" href="users.php">Comptes admin</a><a class="btn btn-outline-light btn-sm" href="backup_db.php">Sauvegarde</a><?php endif; ?>
      <a class="btn btn-warning btn-sm" href="logout.php">Déconnexion</a>
    </div>
  </div>
</nav>
