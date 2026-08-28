<?php
require_once __DIR__ . '/includes/bootstrap.php';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$id = $id ? $id : 1;
$stmt = db()->prepare('SELECT * FROM products WHERE id = ?');
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) { require __DIR__ . '/404.php'; exit; }
$galleryStmt = db()->prepare('SELECT image_url, is_main FROM product_images WHERE product_id = ? ORDER BY is_main DESC, id ASC');
$galleryStmt->execute([$id]);
$gallery = $galleryStmt->fetchAll();
$mainImage = $gallery ? $gallery[0]['image_url'] : $product['image_url'];
$promotion = productPromotion($product['id']);
$salePrice = productSalePrice($product);
$pageTitle = $product['name'] . ' | Woodin Cameroun';
$pageDescription = $product['description'];
$pageImage = $mainImage;
$ogTitle = $product['name'] . ' - WOODIN Cameroun';
$ogDescription = substr($product['description'], 0, 160);
$ogImage = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/' . ltrim($mainImage, '/');
$ogUrl = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$flash = isset($_SESSION['flash']) ? $_SESSION['flash'] : '';
unset($_SESSION['flash']);
include __DIR__ . '/includes/header.php';
?>
<?php if ($promotion): ?><script>document.addEventListener('DOMContentLoaded', function () { var price = document.querySelector('.detail-price'); price.innerHTML = '<del><?= number_format($product['price'], 0, ',', ' ') ?> FCFA</del> <strong class="text-danger"><?= number_format($salePrice, 0, ',', ' ') ?> FCFA</strong> <small>FCFA</small>'; var badge = document.createElement('span'); badge.className = 'badge bg-danger'; badge.textContent = '-<?= e($promotion['discount_percentage']) ?>%'; price.parentNode.insertBefore(badge, price); });</script><?php endif; ?>
<main><section class="product-detail section section-light"><div class="container"><div class="breadcrumb detail-breadcrumb"><a href="index.php">Accueil</a><i class="fa-solid fa-chevron-right"></i><a href="catalogue.php">Catalogue</a><i class="fa-solid fa-chevron-right"></i><span><?= e($product['name']) ?></span></div><div class="row g-5 align-items-start"><div class="col-lg-6"><div class="product-gallery"><img id="main-product-image" class="main-product-image" src="<?= e($mainImage) ?>" alt="<?= e($product['name']) ?>"><?php if (count($gallery) > 1): ?><div class="product-thumbnails"><?php foreach ($gallery as $image): ?><button type="button" onclick="document.getElementById('main-product-image').src=this.dataset.image" data-image="<?= e($image['image_url']) ?>"><img src="<?= e($image['image_url']) ?>" alt="<?= e($product['name']) ?>"></button><?php endforeach; ?></div><?php endif; ?></div></div><div class="col-lg-6"><div class="detail-copy"><p class="eyebrow">Woodin / Collection signature</p><h1><?= e($product['name']) ?></h1><div class="detail-price"><?= number_format($product['price'], 0, ',', ' ') ?> <small>FCFA</small></div><hr><p class="lead-copy"><?= e($product['description']) ?></p><p class="stock-line">Disponibilité : <span class="fw-bold"><?= (int)$product['stock'] ?> disponible(s)</span></p><?php if ($flash): ?><div class="alert alert-warning"><?= e($flash) ?></div><?php endif; ?><form class="detail-actions" method="post" action="actions/add_to_cart.php"><input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>"><label class="quantity-row">Quantité <input class="form-control" type="number" name="quantity" value="1" min="1" max="<?= (int)$product['stock'] ?>"></label><button class="btn btn-gold" type="submit" <?= $product['stock'] < 1 ? 'disabled' : '' ?>><i class="fa-solid fa-bag-shopping"></i> Ajouter au panier</button><a class="btn btn-whatsapp" href="https://wa.me/237693183918" target="_blank" rel="noopener"><i class="fa-brands fa-whatsapp"></i> WhatsApp</a></form><ul class="spec-list"><li><i class="fa-solid fa-check"></i><span>Matière<strong>100% coton</strong></span></li><li><i class="fa-solid fa-check"></i><span>Origine<strong>Côte d'Ivoire</strong></span></li><li><i class="fa-solid fa-check"></i><span>Livraison<strong>Douala J+1 / Yaoundé J+2</strong></span></li></ul></div></div></div></div></section></main><?php include __DIR__ . '/includes/footer.php'; ?>
