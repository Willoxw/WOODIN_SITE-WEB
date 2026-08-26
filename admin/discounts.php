<?php
require_once __DIR__ . '/auth.php';
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    if ($action === 'toggle' && $id) {
        $pdo->prepare('UPDATE discounts SET is_active = 1 - is_active WHERE id = ?')->execute([$id]);
    } elseif ($action === 'save') {
        $code = strtoupper(trim(isset($_POST['code']) ? $_POST['code'] : ''));
        $type = isset($_POST['type']) && $_POST['type'] === 'fixed' ? 'fixed' : 'percentage';
        $value = filter_var(isset($_POST['value']) ? $_POST['value'] : '', FILTER_VALIDATE_FLOAT);
        $minimum = trim(isset($_POST['min_purchase_amount']) ? $_POST['min_purchase_amount'] : '') === '' ? null : filter_var($_POST['min_purchase_amount'], FILTER_VALIDATE_FLOAT);
        $limit = trim(isset($_POST['usage_limit']) ? $_POST['usage_limit'] : '') === '' ? null : filter_var($_POST['usage_limit'], FILTER_VALIDATE_INT);
        $from = isset($_POST['valid_from']) ? $_POST['valid_from'] : '';
        $until = isset($_POST['valid_until']) ? $_POST['valid_until'] : '';
        if (!preg_match('/^[A-Z0-9_-]{3,50}$/', $code) || $value === false || $value <= 0 || ($type === 'percentage' && $value > 100) || $minimum === false || $limit === false || !$from || !$until || $from > $until) {
            $message = 'Données de promotion invalides.';
        } else {
            try {
                if ($id) $pdo->prepare('UPDATE discounts SET code=?, type=?, value=?, min_purchase_amount=?, usage_limit=?, valid_from=?, valid_until=? WHERE id=?')->execute([$code,$type,$value,$minimum,$limit,$from,$until,$id]);
                else $pdo->prepare('INSERT INTO discounts (code,type,value,min_purchase_amount,usage_limit,valid_from,valid_until) VALUES (?,?,?,?,?,?,?)')->execute([$code,$type,$value,$minimum,$limit,$from,$until]);
                $message = 'Code promo enregistré.';
            } catch (Exception $error) { $message = 'Ce code promo existe déjà.'; }
        }
    }
}
$discounts = $pdo->query('SELECT * FROM discounts ORDER BY id DESC')->fetchAll();
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Codes promo | Woodin Admin</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head><body><main class="container py-4"><a href="index.php">Tableau de bord</a><h1>Codes promo</h1><?php if ($message): ?><div class="alert alert-info"><?= e($message) ?></div><?php endif; ?><form method="post" class="row g-2 mb-4"><input type="hidden" name="action" value="save"><div class="col-md-2"><input class="form-control" name="code" placeholder="WOODIN10" required></div><div class="col-md-2"><select class="form-select" name="type"><option value="percentage">Pourcentage</option><option value="fixed">Montant fixe</option></select></div><div class="col-md-2"><input class="form-control" name="value" type="number" min="0.01" step=".01" placeholder="Valeur" required></div><div class="col-md-2"><input class="form-control" name="min_purchase_amount" type="number" min="0" step=".01" placeholder="Minimum"></div><div class="col-md-2"><input class="form-control" name="usage_limit" type="number" min="1" placeholder="Limite"></div><div class="col-md-2"><input class="form-control" name="valid_from" type="date" required></div><div class="col-md-2"><input class="form-control" name="valid_until" type="date" required></div><div class="col-md-2"><button class="btn btn-warning" type="submit">Créer</button></div></form><div class="table-responsive"><table class="table"><thead><tr><th>Code</th><th>Réduction</th><th>Période</th><th>Utilisations</th><th>État</th><th></th></tr></thead><tbody><?php foreach ($discounts as $discount): ?><tr><td><?= e($discount['code']) ?></td><td><?= e($discount['value']) ?> <?= $discount['type'] === 'percentage' ? '%' : 'FCFA' ?></td><td><?= e($discount['valid_from']) ?> au <?= e($discount['valid_until']) ?></td><td><?= (int)$discount['usage_count'] ?><?= $discount['usage_limit'] === null ? '' : ' / ' . (int)$discount['usage_limit'] ?></td><td><?= $discount['is_active'] ? 'Actif' : 'Inactif' ?></td><td><form method="post"><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= (int)$discount['id'] ?>"><button class="btn btn-sm btn-outline-dark">Activer/Désactiver</button></form></td></tr><?php endforeach; ?></tbody></table></div></main></body></html>
