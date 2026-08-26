<?php
require_once __DIR__ . '/includes/bootstrap.php';
$pageTitle = 'Contact | Woodin Cameroun';
$pageDescription = 'Contactez Woodin Cameroun et retrouvez nos boutiques et coordonnées.';
$sent = false;
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim(isset($_POST['nom']) ? $_POST['nom'] : '');
    $email = trim(isset($_POST['email']) ? $_POST['email'] : '');
    $phone = trim(isset($_POST['telephone']) ? $_POST['telephone'] : '');
    $message = trim(isset($_POST['message']) ? $_POST['message'] : '');
    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $message === '') {
        $error = 'Veuillez renseigner un nom, un email valide et un message.';
    } else {
        $stmt = db()->prepare('INSERT INTO messages (nom, email, telephone, message) VALUES (?, ?, ?, ?)');
        $stmt->execute([$name, $email, $phone, $message]);
        $sent = true;
    }
}
include __DIR__ . '/includes/header.php';
?>
<main><section class="page-hero"><div class="container"><p class="eyebrow">Une histoire cousue de passion</p><h1>Contact & <em>histoire</em></h1><div class="breadcrumb"><a href="index.php">Accueil</a><i class="fa-solid fa-chevron-right"></i><span>Contact</span></div></div></section><section class="section section-light"><div class="container"><div class="section-heading"><p class="eyebrow">Depuis 1890</p><h2>Une maison, <em>un héritage</em></h2></div><div class="timeline"><div class="timeline-item"><strong>1890</strong><span>Création en Côte d'Ivoire</span></div><div class="timeline-item"><strong>1968</strong><span>Leader en Afrique de l'Ouest</span></div><div class="timeline-item"><strong>1985</strong><span>Lancement de WOODIN</span></div><div class="timeline-item active"><strong>2025</strong><span>4 villes au Cameroun</span></div></div></div></section><section class="section section-white"><div class="container"><div class="row g-5"><div class="col-lg-5"><p class="eyebrow">Parlons de vos projets</p><h2>Le showroom est à <em>votre écoute</em></h2><div class="contact-card"><h3>Belife Groupe</h3><p>Distributeur officiel Woodin Cameroun</p><div><i class="fa-solid fa-location-dot"></i><span>Immeuble Belife, 1944 Boulevard de la Liberté, Douala</span></div><div><i class="fa-solid fa-phone"></i><a href="tel:+237696809264">+237 696 80 92 64</a></div><div><i class="fa-solid fa-envelope"></i><a href="mailto:contact.cm@belifegroupe.com">contact.cm@belifegroupe.com</a></div></div></div><div class="col-lg-7"><form class="contact-form" method="post"><div class="row g-3"><div class="col-md-6"><label>Nom</label><input name="nom" required></div><div class="col-md-6"><label>Téléphone</label><input name="telephone" type="tel"></div><div class="col-12"><label>Email</label><input name="email" type="email" required></div><div class="col-12"><label>Message</label><textarea name="message" rows="5" required></textarea></div><div class="col-12"><button class="btn btn-gold" type="submit">Envoyer le message <i class="fa-solid fa-arrow-right"></i></button><?php if ($sent): ?><p class="form-feedback">Merci, votre message a bien été pris en compte.</p><?php endif; ?><?php if ($error): ?><p class="text-danger"><?= e($error) ?></p><?php endif; ?></div></div></form></div></div></div></section></main><?php include __DIR__ . '/includes/footer.php'; ?>
