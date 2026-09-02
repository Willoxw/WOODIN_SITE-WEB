<?php
require_once __DIR__ . '/includes/bootstrap.php';
$pageTitle = 'Catalogue | Woodin Cameroun';
$pageDescription = 'Explorez le catalogue Woodin Cameroun et commandez vos tissus en ligne.';
$page = max(1, (int)(isset($_GET['page']) ? $_GET['page'] : 1));
$perPage = 12;
$totalStmt = db()->prepare('SELECT COUNT(*) FROM products');
$totalStmt->execute();
$totalPages = max(1, (int)ceil($totalStmt->fetchColumn() / $perPage));
$page = min($page, $totalPages);
$productsStmt = db()->prepare('SELECT p.*, c.name AS category_name, c.slug AS category_slug FROM products p LEFT JOIN categories c ON c.id = p.category_id ORDER BY p.created_at DESC LIMIT ? OFFSET ?');
$productsStmt->bindValue(1, $perPage, PDO::PARAM_INT);
$productsStmt->bindValue(2, ($page - 1) * $perPage, PDO::PARAM_INT);
$productsStmt->execute();
$products = $productsStmt->fetchAll();
$promotionData = [];
foreach ($products as $product) {
	$promotion = productPromotion($product['id']);
	if ($promotion) $promotionData[strtolower($product['name'])] = ['price' => (float)$product['price'], 'sale' => productSalePrice($product), 'percentage' => (float)$promotion['discount_percentage']];
}
$flash = isset($_SESSION['flash']) ? $_SESSION['flash'] : '';
unset($_SESSION['flash']);
$paginationHtml = '';
if ($totalPages > 1) {
	$paginationHtml = '<nav class="mt-5" aria-label="Pagination du catalogue"><ul class="pagination justify-content-center">';
	$paginationHtml .= '<li class="page-item' . ($page <= 1 ? ' disabled' : '') . '"><a class="page-link" href="?' . e(http_build_query(['page' => max(1, $page - 1)])) . '">Précédent</a></li>';
	for ($i = 1; $i <= $totalPages; $i++) {
		$paginationHtml .= '<li class="page-item' . ($i === $page ? ' active' : '') . '"><a class="page-link" href="?' . e(http_build_query(['page' => $i])) . '">' . $i . '</a></li>';
	}
	$paginationHtml .= '<li class="page-item' . ($page >= $totalPages ? ' disabled' : '') . '"><a class="page-link" href="?' . e(http_build_query(['page' => min($totalPages, $page + 1)])) . '">Suivant</a></li></ul></nav>';
}
ob_start(function ($buffer) use ($paginationHtml) {
	return preg_replace('/<nav class="mt-5 text-center">.*?<\/nav>/', $paginationHtml, $buffer, 1);
});
include __DIR__ . '/includes/header.php';
?>
<script>document.addEventListener('DOMContentLoaded', function () { document.querySelectorAll('input[name="quantity"]').forEach(function (input) { var group = document.createElement('div'); group.className = 'input-group input-group-sm my-2'; var minus = document.createElement('button'); minus.type = 'button'; minus.className = 'btn btn-outline-secondary'; minus.textContent = '-'; var plus = document.createElement('button'); plus.type = 'button'; plus.className = 'btn btn-outline-secondary'; plus.textContent = '+'; input.parentNode.insertBefore(group, input); group.appendChild(minus); group.appendChild(input); group.appendChild(plus); if (parseInt(input.max || '0', 10) < 1) { var label = document.createElement('span'); label.className = 'text-danger small'; label.textContent = 'Rupture de stock'; input.parentNode.appendChild(label); } minus.addEventListener('click', function () { input.value = Math.max(1, parseInt(input.value || '1', 10) - 1); }); plus.addEventListener('click', function () { input.value = Math.min(parseInt(input.max || '1', 10), parseInt(input.value || '1', 10) + 1); }); }); });</script>
<?php if ($promotionData): ?><script>document.addEventListener('DOMContentLoaded', function () { var promotions = <?= json_encode($promotionData) ?>; document.querySelectorAll('.product-column').forEach(function (column) { var promotion = promotions[column.dataset.name]; if (!promotion) return; var price = column.querySelector('.catalogue-card strong'); price.innerHTML = '<del>' + Number(promotion.price).toLocaleString('fr-FR') + ' FCFA</del> <strong class="text-danger">' + Number(promotion.sale).toLocaleString('fr-FR') + ' FCFA</strong>'; var badge = document.createElement('span'); badge.className = 'badge bg-danger'; badge.textContent = '-' + promotion.percentage + '%'; column.querySelector('.product-info').prepend(badge); }); });</script><?php endif; ?>
<main><section class="page-hero"><div class="container"><p class="eyebrow">L'étoffe de vos moments</p><h1>Notre <em>catalogue</em></h1><div class="breadcrumb"><a href="index.php">Accueil</a><i class="fa-solid fa-chevron-right"></i><span>Catalogue</span></div></div></section><section class="section section-light catalogue-section"><div class="container"><?php if ($flash): ?><div class="alert alert-warning"><?= e($flash) ?></div><?php endif; ?><div class="catalogue-toolbar"><div class="search-box"><i class="fa-solid fa-magnifying-glass"></i><input id="productSearch" type="search" placeholder="Rechercher une collection..."></div><div class="filter-pills"><button class="filter-btn active" data-filter="all">Tous</button><button class="filter-btn" data-filter="4 yards">4 yards</button><button class="filter-btn" data-filter="6 yards">6 yards</button></div><label class="sort-select"><span>Trier par</span><select id="sortProducts"><option value="default">Pertinence</option><option value="asc">Prix croissant</option><option value="desc">Prix décroissant</option></select></label></div><div class="row g-4" id="productGrid"><?php $catalogueIndex = 0; foreach ($products as $product): $aoDelay = $catalogueIndex * 100; ?><div class="col-sm-6 col-lg-4 product-column" data-name="<?= e(strtolower($product['name'])) ?>" data-price="<?= e($product['price']) ?>" data-category="<?= e($product['category_name'] ? $product['category_name'] : '') ?>" data-aos="fade-up" data-aos-delay="<?php echo $aoDelay; ?>"><article class="catalogue-card"><img class="product-image" src="<?= e($product['image_url']) ?>" alt="<?= e($product['name']) ?>"><div class="product-info"><span class="yard-badge"><?= e($product['category_name'] ? $product['category_name'] : 'Sans catégorie') ?></span><h2><?= e($product['name']) ?></h2><strong><?= number_format($product['price'], 0, ',', ' ') ?> <small>FCFA</small></strong><div class="card-actions"><form method="post" action="actions/add_to_cart.php"><input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>"><input class="form-control" type="number" name="quantity" value="1" min="1" max="<?= (int)$product['stock'] ?>" aria-label="Quantité"><button class="btn btn-gold" type="submit" <?= $product['stock'] < 1 ? 'disabled' : '' ?>><i class="fa-solid fa-bag-shopping"></i> Ajouter</button></form><a href="produit.php?id=<?= (int)$product['id'] ?>" class="btn btn-dark-custom">Voir le détail</a></div></div></article></div><?php $catalogueIndex++; endforeach; ?></div><?php if ($totalPages > 1): ?><nav class="mt-5 text-center"><a class="btn btn-outline-dark <?= $page <= 1 ? 'disabled' : '' ?>" href="?page=<?= $page - 1 ?>">Page précédente</a> <span class="mx-2">Page <?= $page ?> / <?= $totalPages ?></span><a class="btn btn-outline-dark <?= $page >= $totalPages ? 'disabled' : '' ?>" href="?page=<?= $page + 1 ?>">Page suivante</a></nav><?php endif; ?></div></section></main><?php include __DIR__ . '/includes/footer.php'; ?>
