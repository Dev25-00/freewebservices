<?php
require_once 'config.php';

// Configuration de la page d'accueil
$page_title = 'Mini Services - Tous vos outils numériques';
$page_description = 'Mini Services - Tous vos outils numériques favoris réunis en un seul endroit. Convertisseur, générateur de hashtags, QR codes et plus encore.';
$page_keywords = 'convertisseur fichiers, hashtags, QR code, suppression fond, recettes, outils en ligne, gratuit';
$body_class = 'homepage';
$additional_css = [];
$additional_js = [];

// Inclure le header Bootstrap
require_once 'includes/header-bootstrap.php';
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="hero-content text-lg-start">
                    <h1 class="hero-title">
                        Tous vos outils numériques
                        <span class="text-gradient-primary">en un seul endroit</span>
                    </h1>
                    <p class="hero-subtitle">
                        Convertissez, générez, créez et découvrez avec nos outils gratuits et faciles à utiliser. 
                        Pas d'inscription requise, résultats instantanés.
                    </p>
                    <div class="d-flex gap-3 justify-content-lg-start justify-content-center flex-wrap">
                        <a href="#services" class="btn btn-primary btn-lg">
                            <i class="bi bi-rocket-takeoff me-2"></i>
                            Découvrir nos services
                        </a>
                        <?php if (!$user): ?>
                            <a href="register.php" class="btn btn-outline-primary btn-lg">
                                <i class="bi bi-person-plus me-2"></i>
                                S'inscrire gratuitement
                            </a>
                        <?php else: ?>
                            <a href="profile.php" class="btn btn-outline-primary btn-lg">
                                <i class="bi bi-speedometer2 me-2"></i>
                                Mon tableau de bord
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="row g-3 mt-4 mt-lg-0">
                    <div class="col-6">
                        <div class="card text-center p-3 animate-on-scroll">
                            <div class="card-body">
                                <div class="h2 text-primary mb-2">50K+</div>
                                <small class="text-muted">Fichiers traités</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card text-center p-3 animate-on-scroll">
                            <div class="card-body">
                                <div class="h2 text-success mb-2">5</div>
                                <small class="text-muted">Services disponibles</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card text-center p-3 animate-on-scroll">
                            <div class="card-body">
                                <div class="h2 text-warning mb-2">100%</div>
                                <small class="text-muted">Gratuit</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card text-center p-3 animate-on-scroll">
                            <div class="card-body">
                                <div class="h2 text-info mb-2">24/7</div>
                                <small class="text-muted">Disponible</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Services Section -->
<section id="services" class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold mb-3">Nos Services</h2>
            <p class="lead text-muted">
                Choisissez l'outil dont vous avez besoin parmi notre collection grandissante
            </p>
        </div>
        
        <div class="row g-4">
            <!-- QR Code Generator -->
            <div class="col-lg-4 col-md-6">
                <div class="card service-card h-100 animate-on-scroll">
                    <div class="card-body">
                        <div class="service-icon mx-auto mb-4">
                            <i class="fas fa-qrcode"></i>
                        </div>
                        <h5 class="service-title">Générateur QR Code</h5>
                        <p class="service-description">
                            Créez des codes QR personnalisés pour vos liens, textes et informations de contact.
                        </p>
                        <div class="service-features mb-4">
                            <span class="badge me-1">URL</span>
                            <span class="badge me-1">Texte</span>
                            <span class="badge me-1">Contact</span>
                        </div>
                        <div class="text-center">
                            <a href="services/qr-generator.php" class="btn btn-primary">
                                Utiliser maintenant
                                <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hashtag Generator -->
            <div class="col-lg-4 col-md-6">
                <div class="card service-card h-100 animate-on-scroll">
                    <div class="card-body">
                        <div class="service-icon mx-auto mb-4">
                            <i class="fas fa-hashtag"></i>
                        </div>
                        <h5 class="service-title">Générateur de Hashtags</h5>
                        <p class="service-description">
                            Générez des hashtags pertinents pour vos publications sur les réseaux sociaux.
                        </p>
                        <div class="service-features mb-4">
                            <span class="badge me-1">Instagram</span>
                            <span class="badge me-1">Twitter</span>
                            <span class="badge me-1">TikTok</span>
                        </div>
                        <div class="text-center">
                            <a href="services/hashtag-generator.php" class="btn btn-primary">
                                Utiliser maintenant
                                <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- File Converter -->
            <div class="col-lg-4 col-md-6">
                <div class="card service-card h-100 animate-on-scroll">
                    <div class="card-body">
                        <div class="service-icon mx-auto mb-4">
                            <i class="bi bi-arrow-left-right"></i>
                        </div>
                        <h5 class="service-title">Convertisseur de Fichiers</h5>
                        <p class="service-description">
                            Convertissez vos fichiers entre différents formats facilement et rapidement.
                        </p>
                        <div class="service-features mb-4">
                            <span class="badge me-1">PDF</span>
                            <span class="badge me-1">Images</span>
                            <span class="badge me-1">Documents</span>
                        </div>
                        <div class="text-center">
                            <a href="services/file-converter.php" class="btn btn-primary">
                                Utiliser maintenant
                                <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Background Remover -->
            <div class="col-lg-4 col-md-6">
                <div class="card service-card h-100 animate-on-scroll">
                    <div class="card-body">
                        <div class="service-icon mx-auto mb-4">
                            <i class="bi bi-magic"></i>
                        </div>
                        <h5 class="service-title">Suppresseur de Fond</h5>
                        <p class="service-description">
                            Supprimez automatiquement l'arrière-plan de vos images en quelques secondes.
                        </p>
                        <div class="service-features mb-4">
                            <span class="badge me-1">IA</span>
                            <span class="badge me-1">Instantané</span>
                            <span class="badge me-1">HD</span>
                        </div>
                        <div class="text-center">
                            <a href="services/bg-remover.php" class="btn btn-primary">
                                Utiliser maintenant
                                <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recipe Finder -->
            <div class="col-lg-4 col-md-6">
                <div class="card service-card h-100 animate-on-scroll">
                    <div class="card-body">
                        <div class="service-icon mx-auto mb-4">
                            <i class="bi bi-egg-fried"></i>
                        </div>
                        <h5 class="service-title">Trouveur de Recettes</h5>
                        <p class="service-description">
                            Découvrez des recettes délicieuses en fonction des ingrédients que vous avez.
                        </p>
                        <div class="service-features mb-4">
                            <span class="badge me-1">Ingrédients</span>
                            <span class="badge me-1">Cuisine</span>
                            <span class="badge me-1">Rapide</span>
                        </div>
                        <div class="text-center">
                            <a href="services/recipe-finder.php" class="btn btn-primary">
                                Utiliser maintenant
                                <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Coming Soon Service -->
            <div class="col-lg-4 col-md-6">
                <div class="card service-card h-100 animate-on-scroll">
                    <div class="card-body opacity-75">
                        <div class="service-icon mx-auto mb-4" style="background: linear-gradient(135deg, #6b7280, #9ca3af);">
                            <i class="bi bi-plus-lg"></i>
                        </div>
                        <h5 class="service-title">Bientôt disponible</h5>
                        <p class="service-description">
                            De nouveaux outils sont en cours de développement. Restez connectés !
                        </p>
                        <div class="service-features mb-4">
                            <span class="badge bg-secondary me-1">Prochainement</span>
                        </div>
                        <div class="text-center">
                            <button class="btn btn-outline-secondary" disabled>
                                En développement
                                <i class="bi bi-hourglass-split ms-1"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 bg-gradient-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h2 class="display-6 fw-bold mb-3">Prêt à commencer ?</h2>
                <p class="lead mb-0">
                    Rejoignez des milliers d'utilisateurs qui font confiance à Mini Services 
                    pour leurs besoins numériques quotidiens.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="d-flex gap-3 justify-content-lg-end justify-content-center flex-wrap mt-3 mt-lg-0">
                    <a href="#services" class="btn btn-light btn-lg">
                        <i class="bi bi-rocket-takeoff me-2"></i>
                        Essayer maintenant
                    </a>
                    <?php if (!$user): ?>
                        <a href="register.php" class="btn btn-outline-light btn-lg">
                            <i class="bi bi-person-plus me-2"></i>
                            Créer un compte
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// Inclure le footer Bootstrap
require_once 'includes/footer-bootstrap.php';
?>