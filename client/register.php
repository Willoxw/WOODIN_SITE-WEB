<?php
require_once __DIR__ . '/../includes/bootstrap.php';
if (currentCustomer()) redirect('mon-compte.php');
$error = '';
$values = ['full_name' => '', 'email' => '', 'phone' => '', 'city' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values['full_name'] = trim(isset($_POST['full_name']) ? $_POST['full_name'] : '');
    $values['email'] = strtolower(trim(isset($_POST['email']) ? $_POST['email'] : ''));
    $values['phone'] = trim(isset($_POST['phone']) ? $_POST['phone'] : '');
    $values['city'] = trim(isset($_POST['city']) ? $_POST['city'] : '');
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    if (!preg_match('/^[\p{L}][\p{L} \-\']{1,99}$/u', $values['full_name']) || $values['city'] === '') {
        $error = 'Veuillez saisir un nom et une ville valides.';
    } elseif (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Veuillez saisir une adresse email valide.';
    } elseif (!preg_match('/^(?:6\d{8}|\+2376\d{8})$/', $values['phone'])) {
        $error = 'Veuillez saisir un numéro camerounais valide.';
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password)) {
        $error = 'Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule et un chiffre.';
    } else {
        try {
            $stmt = db()->prepare('INSERT INTO customers (full_name,email,phone,password,city) VALUES (?,?,?,?,?)');
            $stmt->execute([$values['full_name'], $values['email'], $values['phone'], password_hash($password, PASSWORD_DEFAULT), $values['city']]);
            session_regenerate_id(true);
            $_SESSION['customer_id'] = db()->lastInsertId();
            redirect('mon-compte.php');
        } catch (PDOException $exception) {
            $error = $exception->getCode() === '23000' ? 'Cet email ou ce numéro est déjà utilisé.' : 'Inscription impossible pour le moment.';
        }
    }
}
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Créer un compte | Woodin</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head><body class="bg-light"><main class="container py-5" style="max-width:600px"><h1>Créer un compte client</h1><?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?><form method="post" class="card p-4"><input class="form-control mb-3" name="full_name" placeholder="Nom complet" required value="<?= e($values['full_name']) ?>"><input class="form-control mb-3" type="email" name="email" placeholder="Email" required value="<?= e($values['email']) ?>"><input class="form-control mb-3" name="phone" placeholder="Téléphone camerounais" required value="<?= e($values['phone']) ?>"><input class="form-control mb-3" name="city" placeholder="Ville" required value="<?= e($values['city']) ?>"><input class="form-control mb-3" type="password" name="password" placeholder="Mot de passe fort" required><button class="btn btn-warning" type="submit">Créer mon compte</button></form><p class="mt-3"><a href="login.php">J'ai déjà un compte</a></p></main></body></html>
