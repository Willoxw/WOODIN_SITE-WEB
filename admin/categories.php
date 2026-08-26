<?php
require_once __DIR__ . '/auth.php';
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $name = trim(isset($_POST['name']) ? $_POST['name'] : '');
    $slug = strtolower(trim(isset($_POST['slug']) ? $_POST['slug'] : ''));
    $slug = preg_replace('/[^a-z0-9-]+/', '-', $slug);
    if ($name === '' || $slug === '') {
        $message = 'Nom et slug obligatoires.';
    } else {
        try {
            $stmt = $pdo->prepare('INSERT INTO categories (name, slug) VALUES (?, ?)');
            $stmt->execute([$name, $slug]);
            $message = 'Catégorie créée.';
        } catch (Exception $error) {
            $message = 'Cette catégorie existe déjà.';
        }
    }
}
$categoriesStmt = $pdo->prepare('SELECT * FROM categories ORDER BY name');
$categoriesStmt->execute();
$categories = $categoriesStmt->fetchAll();
?>
<!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Catégories | Woodin Admin</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head><body><nav class="navbar navbar-dark bg-dark"><div class="container"><a class="navbar-brand text-warning" href="index.php">WOODIN ADMIN</a><a class="btn btn-warning btn-sm" href="logout.php">Déconnexion</a></div></nav><main class="container py-4"><h1>Catégories</h1><?php if ($message): ?><div class="alert alert-info"><?= e($message) ?></div><?php endif; ?><form method="post" class="row g-2 mb-4"><div class="col-md-5"><input class="form-control" name="name" placeholder="Nom" required></div><div class="col-md-5"><input class="form-control" name="slug" placeholder="slug" required></div><div class="col-md-2"><button class="btn btn-warning w-100" type="submit">Ajouter</button></div></form><ul class="list-group"><?php foreach ($categories as $category): ?><li class="list-group-item d-flex justify-content-between"><span><?= e($category['name']) ?></span><code><?= e($category['slug']) ?></code></li><?php endforeach; ?></ul></main></body></html>
