<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>WOODIN - Test Visual Effects</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { padding-top: 70px; }
        .test-section { padding: 60px 0; }
    </style>
</head>
<body class="inner-page">
<div id="splashScreen" class="splash-screen">
    <div class="splash-content">
        <h1>WOODIN</h1>
        <div class="progress-bar-wrapper">
            <div class="progress-bar"></div>
        </div>
    </div>
</div>

<header class="site-header">
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="mainNav">
        <div class="container">
            <a class="navbar-brand" href="#"><span>WOODIN</span><small>CAMEROUN</small></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#siteNav" aria-label="Ouvrir le menu">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
    </nav>
</header>

<main>
    <!-- HERO WITH ANIMATIONS -->
    <section class="hero hero-home">
        <div class="container hero-content">
            <p class="eyebrow">L'héritage textile africain</p>
            <h1>Le pagne<br><em>d'exception</em><br>au Cameroun</h1>
            <p class="hero-copy">Découvrez les plus belles collections Woodin, 100% coton, livrées chez vous partout au Cameroun.</p>
            <div class="hero-actions">
                <a class="btn btn-gold" href="#stats">Voir les stats <i class="fa-solid fa-arrow-right"></i></a>
                <a class="btn btn-outline-light" href="#cards">Voir les cartes</a>
            </div>
        </div>
    </section>

    <!-- STATS BAND WITH ANIMATED COUNTERS -->
    <section class="stats-band" id="stats">
        <div class="container">
            <div class="row g-0">
                <div class="col-6 col-lg-3 stat-item" data-aos="fade-up" data-aos-delay="0">
                    <strong>135</strong>
                    <span>ans d'histoire</span>
                    <small>Depuis 1890</small>
                </div>
                <div class="col-6 col-lg-3 stat-item" data-aos="fade-up" data-aos-delay="100">
                    <strong>6+</strong>
                    <span>pays en Afrique</span>
                    <small>Une présence régionale</small>
                </div>
                <div class="col-6 col-lg-3 stat-item" data-aos="fade-up" data-aos-delay="200">
                    <strong>4</strong>
                    <span>villes au Cameroun</span>
                    <small>Au plus près de vous</small>
                </div>
                <div class="col-6 col-lg-3 stat-item" data-aos="fade-up" data-aos-delay="300">
                    <strong>100<span class="percent">%</span></strong>
                    <span>tissus coton</span>
                    <small>Une qualité authentique</small>
                </div>
            </div>
        </div>
    </section>

    <!-- PRODUCT CARDS WITH HOVER EFFECTS -->
    <section class="section section-light" id="cards">
        <div class="container">
            <div class="section-heading">
                <p class="eyebrow">Test des cartes</p>
                <h2>Effets de <em>hover</em> premium</h2>
                <p style="margin-top: 20px; color: var(--muted); font-size: 0.95rem;">
                    ✨ Survolez les cartes pour voir : ligne dorée animée, zoom image, élévation
                </p>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="0">
                    <article class="product-card">
                        <div style="height: 250px; background: linear-gradient(135deg, #f5c518 0%, #8b0000 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem;">
                            <i class="fa-solid fa-bolt"></i>
                        </div>
                        <div class="product-info">
                            <p class="product-kicker">Woodin / collection</p>
                            <h3>Pagne Premium Gold</h3>
                            <div class="product-bottom">
                                <strong>15.000 <small>FCFA</small></strong>
                                <a href="#">Voir le produit <i class="fa-solid fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </article>
                </div>
                <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="100">
                    <article class="product-card">
                        <div style="height: 250px; background: linear-gradient(135deg, #8b0000 0%, #f5c518 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem;">
                            <i class="fa-solid fa-heart"></i>
                        </div>
                        <div class="product-info">
                            <p class="product-kicker">Woodin / collection</p>
                            <h3>Pagne Bordeaux Passion</h3>
                            <div class="product-bottom">
                                <strong>12.500 <small>FCFA</small></strong>
                                <a href="#">Voir le produit <i class="fa-solid fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </article>
                </div>
                <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="200">
                    <article class="product-card">
                        <div style="height: 250px; background: linear-gradient(135deg, #25d366 0%, #1a1a1a 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem;">
                            <i class="fa-solid fa-leaf"></i>
                        </div>
                        <div class="product-info">
                            <p class="product-kicker">Woodin / collection</p>
                            <h3>Pagne Nature Vert</h3>
                            <div class="product-bottom">
                                <strong>14.000 <small>FCFA</small></strong>
                                <a href="#">Voir le produit <i class="fa-solid fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </article>
                </div>
            </div>
        </div>
    </section>

    <!-- CART BUTTON FEEDBACK TEST -->
    <section class="section section-dark">
        <div class="container">
            <div class="text-center" style="color: white; margin-bottom: 40px;">
                <h2>Test du feedback "Ajouter au panier" 🛒</h2>
                <p style="margin-top: 15px; color: #ddd;">Cliquez sur le bouton ci-dessous pour voir le feedback immédiat</p>
            </div>
            <div style="text-align: center;">
                <form id="testCartForm" style="display: inline-block;">
                    <button class="btn btn-gold" type="submit" style="padding: 12px 30px; font-size: 1.1rem;">
                        <i class="fa-solid fa-bag-shopping"></i> Ajouter au panier
                    </button>
                </form>
            </div>
        </div>
    </section>

    <div style="text-align: center; padding: 40px; background: var(--mist); color: var(--muted); font-size: 0.9rem;">
        <p>✅ Splash Screen au chargement (800ms)</p>
        <p>✅ Animations Hero : h1, texte, boutons</p>
        <p>✅ Motif de tissu animé en arrière-plan</p>
        <p>✅ AOS fade-up en cascade sur les stats</p>
        <p>✅ Compteurs animés au scroll (IntersectionObserver)</p>
        <p>✅ Hover premium sur les cartes produits</p>
        <p>✅ Navbar premium au scroll avec backdrop-filter</p>
        <p>✅ Feedback immédiat bouton "Ajouter au panier"</p>
    </div>
</main>

<footer style="background: var(--black); color: white; padding: 20px; text-align: center;">
    <p>&copy; 2026 WOODIN Cameroun - Page de test des effets visuels</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="assets/js/script.js"></script>

<script>
    // Simple test form handler (simulating add to cart)
    document.getElementById('testCartForm').addEventListener('submit', (e) => {
        e.preventDefault();
        const button = e.target.querySelector('button[type="submit"]');
        const originalHTML = button.innerHTML;
        button.innerHTML = '<i class="fa-solid fa-check"></i> Ajout en cours...';
        button.classList.add('btn-submitting');
        button.disabled = true;
        
        setTimeout(() => {
            button.innerHTML = originalHTML;
            button.classList.remove('btn-submitting');
            button.disabled = false;
            alert('✅ Produit ajouté au panier avec succès!');
        }, 1000);
    });
</script>
</body>
</html>
