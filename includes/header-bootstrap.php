<?php
// includes/header-bootstrap.php - Header avec Bootstrap

// Vérification des dépendances
try {
    require_once __DIR__ . '/../config.php';
    
    if (!class_exists('Database')) {
        throw new Exception('La classe Database est manquante');
    }
    
    if (!class_exists('CSRFProtection')) {
        throw new Exception('La classe CSRFProtection est manquante');
    }
    
    // Démarrer la session si elle n'est pas déjà active
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
} catch (Exception $e) {
    error_log('Erreur header-bootstrap.php: ' . $e->getMessage());
    die('Une erreur est survenue. Veuillez consulter les logs.');
}

// Obtenir l'utilisateur actuel
$user = getCurrentUser();

// Configuration des meta tags SEO
if (!isset($page_title)) $page_title = 'Mini Services - Tous vos outils numériques';
if (!isset($page_description)) $page_description = 'Mini Services - Tous vos outils numériques favoris réunis en un seul endroit. Convertisseur, générateur de hashtags, QR codes et plus encore.';
if (!isset($page_keywords)) $page_keywords = 'convertisseur fichiers, hashtags, QR code, suppression fond, recettes, outils en ligne, gratuit';
if (!isset($body_class)) $body_class = '';

// CSS additionnels spécifiques à la page
if (!isset($additional_css)) $additional_css = [];

// JavaScript additionnels spécifiques à la page
if (!isset($additional_js)) $additional_js = [];

// Page actuelle pour navigation active
$current_page = basename($_SERVER['PHP_SELF']);

// Déterminer le préfixe de chemin selon le répertoire
$is_in_services = (dirname($_SERVER['PHP_SELF']) === '/services');
$base_path = $is_in_services ? '../' : '';
?>
<!DOCTYPE html>
<html lang="fr" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo htmlspecialchars($page_description); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($page_keywords); ?>">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    
    <!-- Favicon - TODO: Ajouter favicon.ico à la racine -->
    <!-- <link rel="icon" type="image/x-icon" href="<?php echo BASE_URL; ?>/favicon.ico"> -->
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css">
    
    <!-- Font Awesome (pour les icônes existantes) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    
    <!-- CSS personnalisé -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/custom-bootstrap.css">
    
    <!-- Styles principaux -->
    <link href="<?php echo BASE_URL; ?>/assets/css/style.css" rel="stylesheet">

    <!-- CSS spécifiques à la page -->
    <?php foreach ($additional_css as $css): ?>
        <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/<?php echo $css; ?>">
    <?php endforeach; ?>
    
    <!-- Open Graph pour les réseaux sociaux -->
    <meta property="og:title" content="<?php echo htmlspecialchars($page_title); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($page_description); ?>">
    <meta property="og:image" content="<?php echo SITE_URL; ?>/assets/images/og-image.jpg">
    <meta property="og:url" content="<?php echo 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    <meta property="og:type" content="website">
    
    <!-- Schema.org JSON-LD -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebApplication",
        "name": "Mini Services",
        "description": "Plateforme d'outils numériques tout-en-un",
        "url": "<?php echo SITE_URL; ?>",
        "applicationCategory": "UtilitiesApplication",
        "operatingSystem": "Any",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "EUR"
        }
    }
    </script>
</head>
<body class="<?php echo $body_class; ?> d-flex flex-column h-100">
    <!-- Navigation Bootstrap -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
        <div class="container">
            <!-- Logo -->
            <a class="navbar-brand fw-bold text-primary" href="<?php echo BASE_URL; ?>/index.php">
                <i class="fas fa-tools me-2"></i>
                Mini Services
            </a>
            
            <!-- Mobile menu button -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- Navigation items -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>" 
                           href="<?php echo $base_path; ?>index.php">
                            <i class="bi bi-house me-1"></i>Accueil
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link" 
                           href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-tools me-1"></i>Services
                        </a>
                        <ul class="dropdown-menu shadow">
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/services/qr-generator.php">
                                <i class="fas fa-qrcode me-2"></i>Générateur QR Code
                            </a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/services/hashtag-generator.php">
                                <i class="fas fa-hashtag me-2"></i>Générateur Hashtags
                            </a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/services/file-converter.php">
                                <i class="bi bi-arrow-left-right me-2"></i>Convertisseur Fichiers
                            </a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/services/bg-remover.php">
                                <i class="bi bi-magic me-2"></i>Suppresseur de Fond
                            </a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/services/recipe-finder.php">
                                <i class="bi bi-egg-fried me-2"></i>Trouveur de Recettes
                            </a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'guestbook.php') ? 'active' : ''; ?>" 
                           href="<?php echo $base_path; ?>guestbook.php">
                            <i class="bi bi-journal-text me-1"></i>Livre d'or
                        </a>
                    </li>
                </ul>
                
                <!-- User menu -->
                <ul class="navbar-nav">
                    <?php if ($user): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                                <div class="bg-primary rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                    <i class="bi bi-person text-white"></i>
                                </div>
                                <?php echo htmlspecialchars($user['username']); ?>
                                <span class="badge bg-secondary ms-2"><?php echo ucfirst($user['plan_type'] ?? 'gratuit'); ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                <li><h6 class="dropdown-header">
                                    <i class="bi bi-person-circle me-1"></i>
                                    <?php echo htmlspecialchars($user['username']); ?>
                                </h6></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?php echo $base_path; ?>profile.php">
                                    <i class="bi bi-gear me-2"></i>Mon Profil
                                </a></li>
                                <li><a class="dropdown-item" href="<?php echo $base_path; ?>upgrade.php">
                                    <i class="bi bi-star me-2"></i>Passer au Premium
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="<?php echo $base_path; ?>logout.php">
                                    <i class="bi bi-box-arrow-right me-2"></i>Déconnexion
                                </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="#" onclick="openLoginModal(); return false;">
                                <i class="bi bi-box-arrow-in-right me-1"></i>Connexion
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-primary ms-2" href="#" onclick="openRegisterModal(); return false;">
                                <i class="bi bi-person-plus me-1"></i>S'inscrire
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    
    <!-- Notification upgrade pour utilisateurs gratuits -->
    <?php if ($user && isset($user['plan_type']) && $user['plan_type'] === 'free'): ?>
        <div class="alert alert-warning alert-dismissible fade show m-0 rounded-0" role="alert">
            <div class="container">
                <div class="d-flex align-items-center">
                    <i class="bi bi-star-fill me-2"></i>
                    <span class="me-auto">Passez au Premium pour débloquer toutes les fonctionnalités !</span>
                    <a href="<?php echo $base_path; ?>upgrade.php" class="btn btn-warning btn-sm me-2">
                        Découvrir Premium
                    </a>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Main Content Start -->
    <main class="flex-grow-1">
    
    <!-- Modal Login -->
    <div id="loginModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="bi bi-box-arrow-in-right"></i> Connexion</h2>
                <button type="button" class="close" onclick="closeLoginModal()">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="auth-container">
                <?php include __DIR__ . '/modals/login-modal.php'; ?>
            </div>
        </div>
    </div>

    <!-- Modal Register -->
    <div id="registerModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="bi bi-person-plus"></i> Inscription</h2>
                <button type="button" class="close" onclick="closeRegisterModal()">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="auth-container">
                <?php include __DIR__ . '/modals/register-modal.php'; ?>
            </div>
        </div>
    </div>

    <!-- Styles modales -->
    <style>
    /* Styles pour les modales */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 2000;
    }

    .modal.active {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-content {
        background: white;
        border-radius: 12px;
        width: 95%;
        max-width: 420px;
        margin: 20px;
        position: relative;
        animation: modalSlideIn 0.3s ease;
        display: flex;
        flex-direction: column;
    }

    .modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        border-radius: 12px 12px 0 0;
        background: #f8fafc;
    }

    .modal-header h2 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 600;
        color: #1f2937;
    }

    .close {
        position: absolute;
        top: 1rem;
        right: 1rem;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        border: none;
        background: transparent;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        padding: 0;
    }

    .close:hover {
        background: rgba(220, 38, 38, 0.1);
        color: #dc2626;
    }

    .auth-container {
        padding: 1.5rem;
        flex: 1;
    }

    @media (max-width: 480px) {
        .modal-content {
            width: calc(100% - 32px);
            margin: 16px;
        }
        
        .auth-container {
            padding: 1rem;
        }
    }

    @keyframes modalSlideIn {
        from {
            transform: translateY(-20px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
    </style>

    <!-- Scripts modales -->
    <script>
        function openLoginModal() {
            document.getElementById('loginModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeLoginModal() {
            document.getElementById('loginModal').classList.remove('active');
            document.body.style.overflow = 'auto';
        }

        function openRegisterModal() {
            document.getElementById('registerModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeRegisterModal() {
            document.getElementById('registerModal').classList.remove('active');
            document.body.style.overflow = 'auto';
        }

        // Fermer les modales en cliquant en dehors
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.classList.remove('active');
                document.body.style.overflow = 'auto';
            }
        };
    </script>

    <style>
    /* Navigation Styles */
    .navbar {
        background-color: white !important;
        border-bottom: 1px solid rgba(0,0,0,0.1);
    }

    .navbar-nav .nav-link {
        color: #1f2937 !important; /* Couleur foncée pour meilleure lisibilité */
        font-weight: 500;
        padding: 0.5rem 1rem !important;
        transition: color 0.3s ease;
    }

    .navbar-nav .nav-link:hover,
    .navbar-nav .nav-link:focus {
        color: var(--bs-primary) !important;
    }

    .navbar-nav .nav-link.active {
        color: var(--bs-primary) !important;
        background-color: rgba(var(--bs-primary-rgb), 0.1);
        border-radius: 6px;
    }

    .dropdown-menu {
        border: none;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
    }

    .dropdown-item {
        color: #1f2937 !important;
        padding: 0.5rem 1rem;
    }

    .dropdown-item:hover,
    .dropdown-item:focus {
        color: var(--bs-primary) !important;
        background-color: rgba(var(--bs-primary-rgb), 0.1);
    }

    /* Supprimer la flèche du dropdown */
    .dropdown-toggle::after {
        display: none !important;
    }

    /* Ajustement pour le bouton d'inscription */
    .navbar .btn-primary {
        color: white !important;
        padding: 0.5rem 1rem;
    }

    /* Ajustement badge plan */
    .navbar .badge {
        font-weight: 500;
        padding: 0.35em 0.65em;
    }
    </style>
