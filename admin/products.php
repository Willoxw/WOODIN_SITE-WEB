<?php
require_once __DIR__ . '/auth.php';
$message = '';
$uploadDirectory = __DIR__ . '/../assets/images/produits/';
$categoryOptions = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    if ($action === 'save') {
        $name = trim(isset($_POST['name']) ? $_POST['name'] : '');
        $description = trim(isset($_POST['description']) ? $_POST['description'] : '');
        $price = filter_var(isset($_POST['price']) ? $_POST['price'] : '', FILTER_VALIDATE_FLOAT);
        $stock = filter_var(isset($_POST['stock']) ? $_POST['stock'] : '', FILTER_VALIDATE_INT);
        $categoryId = filter_var(isset($_POST['category_id']) ? $_POST['category_id'] : '', FILTER_VALIDATE_INT);
        if ($categoryId === false || !$categoryId) {
            $defaultCategory = $pdo->prepare('SELECT id FROM categories ORDER BY id LIMIT 1');
            $defaultCategory->execute();
            $categoryId = (int)$defaultCategory->fetchColumn();
        }
        $categoryCheck = $pdo->prepare('SELECT COUNT(*) FROM categories WHERE id = ?');
        $categoryCheck->execute([$categoryId]);
        $categoryExists = (int)$categoryCheck->fetchColumn() === 1;
        if ($name === '' || $description === '' || $price === false || $price < 0 || $stock === false || $stock < 0 || !$categoryExists) {
            $message = 'Veuillez renseigner des informations produit valides.';
        } else {
            $imageUrl = '';
            if ($id) {
                $existing = $pdo->prepare('SELECT image_url FROM products WHERE id = ?');
                $existing->execute([$id]);
                $existingProduct = $existing->fetch();
                $imageUrl = $existingProduct ? $existingProduct['image_url'] : '';
            }
            if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                if ($_FILES['image']['error'] !== UPLOAD_ERR_OK || $_FILES['image']['size'] > 2097152) {
                    $message = 'L’image est invalide ou dépasse 2 Mo.';
                } else {
                    $extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                    $mime = function_exists('mime_content_type') ? mime_content_type($_FILES['image']['tmp_name']) : '';
                    $allowedMime = ['image/jpeg', 'image/png', 'image/webp'];
                    if (!in_array($extension, $allowed, true) || ($mime && !in_array($mime, $allowedMime, true)) || !getimagesize($_FILES['image']['tmp_name'])) {
                        $message = 'Format d’image refusé. Utilisez JPG, PNG ou WEBP.';
                    } else {
                        if (!is_dir($uploadDirectory)) mkdir($uploadDirectory, 0755, true);
                        $filename = uniqid('produit_', true) . '.' . $extension;
                        if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadDirectory . $filename)) {
                            $message = 'Impossible d’enregistrer l’image.';
                        } else {
                            $imageUrl = 'assets/images/produits/' . $filename;
                        }
                    }
                }
            }
            if ($message === '') {
                $savedProductId = $id;
                if ($id) {
                    $oldStockStmt = $pdo->prepare('SELECT stock FROM products WHERE id = ?');
                    $oldStockStmt->execute([$id]);
                    $oldStock = (int)$oldStockStmt->fetchColumn();
                    $stmt = $pdo->prepare('UPDATE products SET name = ?, description = ?, price = ?, stock = ?, image_url = ?, category_id = ? WHERE id = ?');
                    $stmt->execute([$name, $description, $price, $stock, $imageUrl, $categoryId, $id]);
                    if ($existingProduct && $existingProduct['image_url'] !== $imageUrl && strpos($existingProduct['image_url'], 'assets/images/') === 0 && is_file(__DIR__ . '/../' . $existingProduct['image_url'])) unlink(__DIR__ . '/../' . $existingProduct['image_url']);
                    if ($stock !== $oldStock) {
                        $movement = $pdo->prepare("INSERT INTO stock_movements (product_id, quantity_change, reason) VALUES (?, ?, 'correction')");
                        $movement->execute([$id, $stock - $oldStock]);
                    }
                } else {
                    $stmt = $pdo->prepare('INSERT INTO products (name, description, price, stock, image_url, category_id) VALUES (?, ?, ?, ?, ?, ?)');
                    $stmt->execute([$name, $description, $price, $stock, $imageUrl, $categoryId]);
                    $savedProductId = $pdo->lastInsertId();
                    if ($stock > 0) {
                        $movement = $pdo->prepare("INSERT INTO stock_movements (product_id, quantity_change, reason) VALUES (?, ?, 'réapprovisionnement')");
                        $movement->execute([$pdo->lastInsertId(), $stock]);
                    }
                }
                if ($imageUrl !== '' && $savedProductId) {
                    $galleryInsert = $pdo->prepare('INSERT INTO product_images (product_id, image_url, is_main) VALUES (?, ?, 1)');
                    $galleryInsert->execute([$savedProductId, $imageUrl]);
                }
                $message = 'Produit enregistré.';
            }
        }
    } elseif ($action === 'delete' && $id) {
        requireAdminRole('super_admin');
        try {
            $productStmt = $pdo->prepare('SELECT image_url FROM products WHERE id = ?');
            $productStmt->execute([$id]);
            $product = $productStmt->fetch();
            $imagesStmt = $pdo->prepare('SELECT image_url FROM product_images WHERE product_id = ?');
            $imagesStmt->execute([$id]);
            $galleryImages = $imagesStmt->fetchAll();
            $pdo->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);
            $imageUrls = $product ? array_merge([$product['image_url']], array_column($galleryImages, 'image_url')) : array_column($galleryImages, 'image_url');
            foreach (array_unique($imageUrls) as $imageUrl) if (strpos($imageUrl, 'assets/images/') === 0 && is_file(__DIR__ . '/../' . $imageUrl)) unlink(__DIR__ . '/../' . $imageUrl);
            $message = 'Produit supprimé.';
        } catch (Exception $error) {
            $message = 'Impossible de supprimer ce produit lié à une commande.';
        }
    }
}

$edit = null;
$categoriesStmt = $pdo->prepare('SELECT * FROM categories ORDER BY name');
$categoriesStmt->execute();
$categories = $categoriesStmt->fetchAll();
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch();
}
$categoryOptions = '<option value="">Choisir une catégorie</option>';
foreach ($categories as $category) {
    $selected = $edit && (int)$edit['category_id'] === (int)$category['id'] ? ' selected' : '';
    $categoryOptions .= '<option value="' . (int)$category['id'] . '"' . $selected . '>' . e($category['name']) . '</option>';
}
$deleteModal = '<div class="modal fade" id="deleteModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header bg-danger text-white"><h5>Confirmer la suppression</h5></div><div class="modal-body">Supprimer <strong id="deleteItemName"></strong> ? Cette action est irréversible.</div><div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button><form method="post"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" id="deleteItemId"><button class="btn btn-danger">Supprimer définitivement</button></form></div></div></div></div><script>document.getElementById("deleteModal").addEventListener("show.bs.modal",function(event){document.getElementById("deleteItemName").textContent=event.relatedTarget.dataset.itemName;document.getElementById("deleteItemId").value=event.relatedTarget.dataset.itemId;});</script>';
ob_start(function ($buffer) use ($categoryOptions) {
    global $paginationHtml;
    global $deleteModal;
    $field = '<div class="col-md-4"><select class="form-select" name="category_id" required>' . $categoryOptions . '</select></div>';
    $buffer = preg_replace_callback('/<form class="d-inline" method="post" onsubmit="return confirm\(\'Supprimer ce produit \?\'\);"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="(\d+)"><button class="btn btn-sm btn-outline-danger" type="submit">Supprimer<\/button><\/form>/', function ($matches) {
        return '<button class="btn btn-sm btn-outline-danger" type="button" data-bs-toggle="modal" data-bs-target="#deleteModal" data-item-id="' . $matches[1] . '" data-item-name="Produit #' . $matches[1] . '">Supprimer</button>';
    }, $buffer);
    $buffer = preg_replace('/(<form\b[^>]*enctype=["\']multipart\/form-data["\'][^>]*>)/i', '$1' . $field, $buffer, 1);
    $buffer = preg_replace('/<nav>.*?<\/nav>/', $paginationHtml, $buffer, 1);
    return str_replace('</main>', $deleteModal . '</main>', $buffer);
});
$page = max(1, (int)(isset($_GET['page']) ? $_GET['page'] : 1));
$perPage = 15;
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';
$productWhere = $filter === 'rupture' ? ' WHERE stock = 0' : '';
$totalStmt = $pdo->prepare('SELECT COUNT(*) FROM products' . $productWhere);
$totalStmt->execute();
$totalPages = max(1, (int)ceil($totalStmt->fetchColumn() / $perPage));
$page = min($page, $totalPages);
$productsStmt = $pdo->prepare('SELECT * FROM products' . $productWhere . ' ORDER BY id DESC LIMIT ? OFFSET ?');
$productsStmt->bindValue(1, $perPage, PDO::PARAM_INT);
$productsStmt->bindValue(2, ($page - 1) * $perPage, PDO::PARAM_INT);
$productsStmt->execute();
$products = $productsStmt->fetchAll();
$paginationHtml = '';
if ($totalPages > 1) {
    $paginationHtml = '<nav aria-label="Pagination des produits"><ul class="pagination justify-content-center">';
    $paginationHtml .= '<li class="page-item' . ($page <= 1 ? ' disabled' : '') . '"><a class="page-link" href="?' . e(http_build_query(['page' => max(1, $page - 1), 'filter' => $filter])) . '">Précédent</a></li>';
    for ($i = 1; $i <= $totalPages; $i++) {
        $paginationHtml .= '<li class="page-item' . ($i === $page ? ' active' : '') . '"><a class="page-link" href="?' . e(http_build_query(['page' => $i, 'filter' => $filter])) . '">' . $i . '</a></li>';
    }
    $paginationHtml .= '<li class="page-item' . ($page >= $totalPages ? ' disabled' : '') . '"><a class="page-link" href="?' . e(http_build_query(['page' => min($totalPages, $page + 1), 'filter' => $filter])) . '">Suivant</a></li></ul></nav>';
}
?>
<!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Produits | Woodin Admin</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head><body><nav class="navbar navbar-dark bg-dark"><div class="container"><a class="navbar-brand text-warning" href="index.php">WOODIN ADMIN</a><a class="btn btn-warning btn-sm" href="logout.php">Déconnexion</a></div></nav><main class="container py-4"><div class="d-flex justify-content-between"><h1>Produits</h1><a class="btn btn-dark" href="products.php">Nouveau produit</a></div><?php if ($message): ?><div class="alert alert-info"><?= e($message) ?></div><?php endif; ?><form class="card p-3 my-4" method="post" enctype="multipart/form-data"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= (int)($edit ? $edit['id'] : 0) ?>"><div class="row g-2"><div class="col-md-6"><input class="form-control" name="name" required placeholder="Nom" value="<?= e($edit ? $edit['name'] : '') ?>"></div><div class="col-md-2"><input class="form-control" name="price" type="number" min="0" step=".01" required placeholder="Prix" value="<?= e($edit ? $edit['price'] : '') ?>"></div><div class="col-md-2"><input class="form-control" name="stock" type="number" min="0" required placeholder="Stock" value="<?= (int)($edit ? $edit['stock'] : 0) ?>"></div><div class="col-md-2"><input class="form-control" name="image" type="file" accept=".jpg,.jpeg,.png,.webp"></div><div class="col-12"><textarea class="form-control" name="description" required placeholder="Description"><?= e($edit ? $edit['description'] : '') ?></textarea></div><div class="col-12"><button class="btn btn-warning" type="submit">Enregistrer</button></div></div></form><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Image</th><th>Nom</th><th>Prix</th><th>Stock</th><th>Actions</th></tr></thead><tbody><?php foreach ($products as $product): ?><tr><td><?php if ($product['image_url']): ?><img src="../<?= e($product['image_url']) ?>" alt="<?= e($product['name']) ?>" width="56" height="56" style="object-fit:cover"><?php endif; ?></td><td><?= e($product['name']) ?></td><td><?= number_format($product['price'], 0, ',', ' ') ?> FCFA</td><td><?= (int)$product['stock'] ?></td><td><a class="btn btn-sm btn-outline-dark" href="?edit=<?= (int)$product['id'] ?>">Modifier</a> <form class="d-inline" method="post" onsubmit="return confirm('Supprimer ce produit ?');"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$product['id'] ?>"><button class="btn btn-sm btn-outline-danger" type="submit">Supprimer</button></form></td></tr><?php endforeach; ?></tbody></table></div><?php if ($totalPages > 1): ?><nav><a class="btn btn-outline-dark <?= $page <= 1 ? 'disabled' : '' ?>" href="?page=<?= $page - 1 ?>">Page précédente</a> <span class="mx-2">Page <?= $page ?> / <?= $totalPages ?></span><a class="btn btn-outline-dark <?= $page >= $totalPages ? 'disabled' : '' ?>" href="?page=<?= $page + 1 ?>">Page suivante</a></nav><?php endif; ?></main></body></html>
