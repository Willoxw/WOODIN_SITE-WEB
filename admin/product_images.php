<?php
require_once __DIR__ . '/auth.php';
$message = '';
$uploadDirectory = __DIR__ . '/../assets/images/produits/';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    $mainIndex = isset($_POST['main_index']) ? (int)$_POST['main_index'] : 0;
    if (!$productId || empty($_FILES['images']['name'])) {
        $message = 'Sélectionnez un produit et au moins une image.';
    } else {
        if (!is_dir($uploadDirectory)) mkdir($uploadDirectory, 0755, true);
        $insert = $pdo->prepare('INSERT INTO product_images (product_id, image_url, is_main) VALUES (?, ?, ?)');
        $uploaded = 0;
        foreach ($_FILES['images']['tmp_name'] as $index => $temporaryPath) {
            if ($_FILES['images']['error'][$index] !== UPLOAD_ERR_OK || $_FILES['images']['size'][$index] > 2097152) continue;
            $extension = strtolower(pathinfo($_FILES['images']['name'][$index], PATHINFO_EXTENSION));
            $mime = function_exists('mime_content_type') ? mime_content_type($temporaryPath) : '';
            if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true) || ($mime && !in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) || !getimagesize($temporaryPath)) continue;
            $filename = uniqid('produit_', true) . '.' . $extension;
            if (move_uploaded_file($temporaryPath, $uploadDirectory . $filename)) {
                $insert->execute([$productId, 'assets/images/produits/' . $filename, $index === $mainIndex ? 1 : 0]);
                $uploaded++;
            }
        }
        $message = $uploaded ? $uploaded . ' image(s) ajoutée(s).' : 'Aucune image valide n’a été ajoutée.';
    }
}
$productsStmt = $pdo->prepare('SELECT id, name FROM products ORDER BY name');
$productsStmt->execute();
$products = $productsStmt->fetchAll();
?>
<!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Galerie produits | Woodin Admin</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head><body><nav class="navbar navbar-dark bg-dark"><div class="container"><a class="navbar-brand text-warning" href="index.php">WOODIN ADMIN</a><a class="btn btn-warning btn-sm" href="logout.php">Déconnexion</a></div></nav><main class="container py-4"><h1>Galerie produit</h1><?php if ($message): ?><div class="alert alert-info"><?= e($message) ?></div><?php endif; ?><form method="post" enctype="multipart/form-data" class="card p-3"><select name="product_id" class="form-select mb-3" required><option value="">Produit</option><?php foreach ($products as $product): ?><option value="<?= (int)$product['id'] ?>"><?= e($product['name']) ?></option><?php endforeach; ?></select><input type="file" name="images[]" class="form-control mb-3" accept=".jpg,.jpeg,.png,.webp" multiple required><label class="form-label">Index de la photo principale (commence à 0)</label><input type="number" name="main_index" class="form-control mb-3" min="0" value="0"><button class="btn btn-warning" type="submit">Ajouter les photos</button></form></main></body></html>
