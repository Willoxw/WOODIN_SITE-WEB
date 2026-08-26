<?php
require_once __DIR__ . '/includes/bootstrap.php';
http_response_code(403);
$pageTitle = 'Accès refusé | Woodin Cameroun';
include __DIR__ . '/includes/header.php';
?>
<main><section class="section section-light text-center"><div class="container"><p class="eyebrow">Erreur 403</p><h1>Accès <em>refusé</em></h1><p>Votre demande ne peut pas être traitée.</p><a class="btn btn-gold" href="index.php">Retour à l’accueil</a></div></section></main><?php include __DIR__ . '/includes/footer.php'; ?>
