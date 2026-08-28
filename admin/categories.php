<?php
require_once __DIR__ . '/auth.php';
$message = '';
$edit = null;
if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'edit') {
    $stmt = $pdo->prepare('SELECT * FROM categories WHERE id = ?'); $stmt->execute([(int)$_GET['id']]); $edit = $stmt->fetch();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $action = isset($_POST['action']) ? $_POST['action'] : 'save';
    if ($action === 'delete' && $id) {
        requireAdminRole('super_admin');
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM products WHERE category_id = ?'); $stmt->execute([$id]); $count = (int)$stmt->fetchColumn();
        if ($count > 0) $message = 'Impossible : ' . $count . ' produit(s) utilisent cette catégorie.';
        else { $pdo->prepare('DELETE FROM categories WHERE id = ?')->execute([$id]); $message = 'Catégorie supprimée.'; }
    } else {
        $name = trim(isset($_POST['name']) ? $_POST['name'] : ''); $slug = strtolower(trim(isset($_POST['slug']) ? $_POST['slug'] : '')); $slug = preg_replace('/[^a-z0-9-]+/', '-', $slug);
        if ($name === '' || $slug === '') $message = 'Nom et slug obligatoires.';
        else try { if ($id) $pdo->prepare('UPDATE categories SET name=?, slug=? WHERE id=?')->execute([$name,$slug,$id]); else $pdo->prepare('INSERT INTO categories (name,slug) VALUES (?,?)')->execute([$name,$slug]); $message = 'Catégorie enregistrée.'; } catch (Exception $error) { $message = 'Cette catégorie existe déjà.'; }
    }
}
$categories = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Catégories | Woodin Admin</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head><body><main class="container py-4"><h1>Catégories</h1><?php if ($message): ?><div class="alert alert-info"><?= e($message) ?></div><?php endif; ?><form method="post" class="row g-2 mb-4"><input type="hidden" name="id" value="<?= (int)($edit ? $edit['id'] : 0) ?>"><div class="col-md-5"><input class="form-control" name="name" placeholder="Nom" required value="<?= e($edit ? $edit['name'] : '') ?>"></div><div class="col-md-5"><input class="form-control" name="slug" placeholder="slug" required value="<?= e($edit ? $edit['slug'] : '') ?>"></div><div class="col-md-2"><button class="btn btn-warning w-100" type="submit"><?= $edit ? 'Modifier' : 'Ajouter' ?></button></div></form><ul class="list-group"><?php foreach ($categories as $category): ?><li class="list-group-item d-flex justify-content-between align-items-center"><span><?= e($category['name']) ?> <code><?= e($category['slug']) ?></code></span><span><a class="btn btn-sm btn-outline-dark" href="?action=edit&amp;id=<?= (int)$category['id'] ?>">Modifier</a><?php if ((isset($_SESSION['admin_role']) ? $_SESSION['admin_role'] : '') === 'super_admin'): ?><button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal" data-item-id="<?= (int)$category['id'] ?>" data-item-name="<?= e($category['name']) ?>">Supprimer</button><?php endif; ?></span></li><?php endforeach; ?></ul><div class="modal fade" id="deleteModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header bg-danger text-white"><h5>Confirmer la suppression</h5></div><div class="modal-body">Supprimer <strong id="deleteItemName"></strong> ? Cette action est irréversible.</div><div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button><form method="post"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" id="deleteItemId"><button class="btn btn-danger">Supprimer définitivement</button></form></div></div></div></div><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script><script>document.getElementById('deleteModal').addEventListener('show.bs.modal',function(event){document.getElementById('deleteItemName').textContent=event.relatedTarget.dataset.itemName;document.getElementById('deleteItemId').value=event.relatedTarget.dataset.itemId;});</script></main></body></html>
