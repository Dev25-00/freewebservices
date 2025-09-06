</main>
<!-- Main Content End -->

<footer class="footer site-footer mt-auto py-5 bg-dark text-light">
    <div class="container-fluid px-4">
        <div class="row g-4">
            <!-- Section principale -->
            <div class="col-lg-4 col-md-6">
                <h5 class="fw-bold mb-3">
                    <i class="fas fa-tools me-2 text-primary"></i>
                    Mini Services
                </h5>
                <p class="text-light-emphasis">
                    Votre plateforme tout-en-un pour tous vos besoins numériques. 
                    Rapide, sécurisé et gratuit.
                </p>
                <div class="d-flex gap-3">
                    <a href="#" class="text-light-emphasis hover-text-primary">
                        <i class="fab fa-facebook-f fs-5"></i>
                    </a>
                    <a href="#" class="text-light-emphasis hover-text-primary">
                        <i class="fab fa-twitter fs-5"></i>
                    </a>
                    <a href="#" class="text-light-emphasis hover-text-primary">
                        <i class="fab fa-linkedin-in fs-5"></i>
                    </a>
                    <a href="#" class="text-light-emphasis hover-text-primary">
                        <i class="fab fa-instagram fs-5"></i>
                    </a>
                </div>
            </div>
            
            <!-- Services -->
            <div class="col-lg-2 col-md-6">
                <h6 class="fw-bold mb-3">Services</h6>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <a href="<?php echo $base_path; ?>services/file-converter.php" class="text-light-emphasis text-decoration-none hover-text-primary">
                            <i class="bi bi-arrow-left-right me-1"></i>
                            Convertisseur
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="<?php echo $base_path; ?>services/hashtag-generator.php" class="text-light-emphasis text-decoration-none hover-text-primary">
                            <i class="fas fa-hashtag me-1"></i>
                            Hashtags
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="<?php echo $base_path; ?>services/qr-generator.php" class="text-light-emphasis text-decoration-none hover-text-primary">
                            <i class="fas fa-qrcode me-1"></i>
                            QR Code
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="<?php echo $base_path; ?>services/bg-remover.php" class="text-light-emphasis text-decoration-none hover-text-primary">
                            <i class="bi bi-magic me-1"></i>
                            Suppresseur Fond
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="<?php echo $base_path; ?>services/recipe-finder.php" class="text-light-emphasis text-decoration-none hover-text-primary">
                            <i class="bi bi-egg-fried me-1"></i>
                            Recettes
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Support -->
            <div class="col-lg-2 col-md-6">
                <h6 class="fw-bold mb-3">Support</h6>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <a href="<?php echo $base_path; ?>help.php" class="text-light-emphasis text-decoration-none hover-text-primary">
                            <i class="bi bi-question-circle me-1"></i>
                            Centre d'aide
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="<?php echo $base_path; ?>contact.php" class="text-light-emphasis text-decoration-none hover-text-primary">
                            <i class="bi bi-envelope me-1"></i>
                            Contact
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="<?php echo $base_path; ?>faq.php" class="text-light-emphasis text-decoration-none hover-text-primary">
                            <i class="bi bi-chat-dots me-1"></i>
                            FAQ
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="<?php echo $base_path; ?>guestbook.php" class="text-light-emphasis text-decoration-none hover-text-primary">
                            <i class="bi bi-journal-text me-1"></i>
                            Livre d'or
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="<?php echo $base_path; ?>status.php" class="text-light-emphasis text-decoration-none hover-text-primary">
                            <i class="bi bi-heart-pulse me-1"></i>
                            Statut
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Compte -->
            <div class="col-lg-2 col-md-6">
                <h6 class="fw-bold mb-3">Compte</h6>
                <ul class="list-unstyled">
                    <?php if ($user): ?>
                        <li class="mb-2">
                            <a href="<?php echo $base_path; ?>profile.php" class="text-light-emphasis text-decoration-none hover-text-primary">
                                <i class="bi bi-person me-1"></i>
                                Mon Profil
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?php echo $base_path; ?>upgrade.php" class="text-light-emphasis text-decoration-none hover-text-primary">
                                <i class="bi bi-star me-1"></i>
                                Premium
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?php echo $base_path; ?>logout.php" class="text-light-emphasis text-decoration-none hover-text-primary">
                                <i class="bi bi-box-arrow-right me-1"></i>
                                Déconnexion
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="mb-2">
                            <a href="<?php echo $base_path; ?>login.php" class="text-light-emphasis text-decoration-none hover-text-primary">
                                <i class="bi bi-box-arrow-in-right me-1"></i>
                                Connexion
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?php echo $base_path; ?>register.php" class="text-light-emphasis text-decoration-none hover-text-primary">
                                <i class="bi bi-person-plus me-1"></i>
                                Inscription
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="mb-2">
                        <a href="<?php echo $base_path; ?>privacy.php" class="text-light-emphasis text-decoration-none hover-text-primary">
                            <i class="bi bi-shield-check me-1"></i>
                            Confidentialité
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="<?php echo $base_path; ?>terms.php" class="text-light-emphasis text-decoration-none hover-text-primary">
                            <i class="bi bi-file-text me-1"></i>
                            Conditions
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Newsletter -->
            <div class="col-lg-2 col-md-12">
                <h6 class="fw-bold mb-3">Newsletter</h6>
                <p class="text-light-emphasis small">
                    Restez informé des nouveautés et mises à jour.
                </p>
                <div class="input-group input-group-sm">
                    <input type="email" class="form-control" placeholder="Votre email">
                    <button class="btn btn-primary" type="button">
                        <i class="bi bi-send"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <hr class="my-4 text-light-emphasis">
        
        <!-- Footer bottom -->
        <div class="row align-items-center">
            <div class="col-md-6">
                <p class="mb-0 text-light-emphasis">
                    &copy; <?php echo date('Y'); ?> Mini Services. Tous droits réservés.
                </p>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="d-flex justify-content-md-end justify-content-start gap-3 align-items-center">
                    <a href="<?php echo $base_path; ?>sitemap.xml" class="text-light-emphasis text-decoration-none small">
                        Plan du site
                    </a>
                    <span class="text-light-emphasis">|</span>
                    <a href="<?php echo $base_path; ?>robots.txt" class="text-light-emphasis text-decoration-none small">
                        Robots.txt
                    </a>
                    <span class="text-light-emphasis">|</span>
                    <span class="text-light-emphasis small">v2.0</span>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Ensure footer spans full viewport width -->
<style>
/* Make footer full-viewport width even inside centered layouts */
.site-footer{
    width: 100vw;
    position: relative;
    left: 50%;
    right: 50%;
    margin-left: -50vw;
    margin-right: -50vw;
    box-sizing: border-box;
    z-index: 50;
}

/* Ensure page layout keeps footer at bottom */
html, body {
    height: 100%;
    margin: 0;
}
body {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}
main {
    flex: 1 0 auto;
}
</style>

<!-- Bouton retour en haut -->
<button id="backToTop" class="btn btn-primary position-fixed bottom-0 end-0 m-4 rounded-circle shadow" 
        style="display: none; width: 50px; height: 50px; z-index: 1000;">
    <i class="bi bi-arrow-up"></i>
</button>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Scripts personnalisés -->
<script src="<?php echo BASE_URL; ?>/assets/js/main-bootstrap.js"></script>

<!-- Scripts spécifiques à la page -->
<?php foreach ($additional_js as $js): ?>
    <?php if (strpos($js, 'http://') === 0 || strpos($js, 'https://') === 0): ?>
        <script src="<?php echo htmlspecialchars($js); ?>"></script>
    <?php else: ?>
        <script src="<?php echo BASE_URL; ?>/assets/<?php echo htmlspecialchars($js); ?>"></script>
    <?php endif; ?>
<?php endforeach; ?>

<!-- Google Analytics et tracking -->
<?php if (function_exists('get_analytics_code')): ?>
    <?php echo get_analytics_code(); ?>
<?php endif; ?>

<!-- Tracking de page -->
<?php if (function_exists('track_service_usage') && isset($page_tracking)): ?>
    <?php echo track_service_usage($page_tracking['service'], $page_tracking['action'], $page_tracking['data'] ?? []); ?>
<?php endif; ?>

<script>
    // Initialisation après chargement du DOM
    document.addEventListener('DOMContentLoaded', function() {
        // Gestion du bouton retour en haut
        const backToTop = document.getElementById('backToTop');
        
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTop.style.display = 'block';
            } else {
                backToTop.style.display = 'none';
            }
        });
        
        backToTop.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
        
        // Animation d'apparition pour les éléments
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate__animated', 'animate__fadeInUp');
                }
            });
        }, observerOptions);
        
        // Observer les éléments avec la classe 'animate-on-scroll'
        document.querySelectorAll('.animate-on-scroll').forEach(el => {
            observer.observe(el);
        });
    });
    
    // Fonction pour tracking des interactions
    function trackUserInteraction(action, element, data = {}) {
        if (typeof gtag !== 'undefined') {
            gtag('event', action, {
                'element': element,
                'custom_parameters': data
            });
        }
        
        // Log en console pour debug
        console.log('Interaction tracked:', action, element, data);
    }
</script>
</body>
</html>