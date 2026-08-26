<?php
require_once __DIR__ . '/includes/bootstrap.php';
http_response_code(404);
$pageTitle = 'Page introuvable | Woodin Cameroun';
include __DIR__ . '/includes/header.php';
?>
<main><section class="section section-light text-center"><div class="container"><p class="eyebrow">Erreur 404</p><h1>Page <em>introuvable</em></h1><p>Cette page n’existe pas ou n’est plus disponible.</p><a class="btn btn-gold" href="catalogue.php">Retour au catalogue</a></div></section></main><?php include __DIR__ . '/includes/footer.php'; ?>
