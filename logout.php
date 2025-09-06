<?php
require_once 'config.php';

// Configurer la page
$page_title = 'Déconnexion - Mini Services';
$page_description = 'Déconnexion de votre compte Mini Services';
$body_class = 'auth-page logout-page';
$additional_css = [];
$additional_js = [];

// Vérifier si l'utilisateur est connecté
if (isLoggedIn()) {
    $userId = $_SESSION['user_id'];
    
    // Log de sécurité pour la déconnexion
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            INSERT INTO security_logs (event_type, user_id, ip_address, user_agent, details) 
            VALUES ('logout', ?, ?, ?, ?)
        ");
        $stmt->execute([
            $userId,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            json_encode(['logout_time' => date('c')])
        ]);
    } catch (Exception $e) {
        error_log("Erreur log déconnexion: " . $e->getMessage());
    }
}

// Détruire la session
session_destroy();

// Inclure le header Bootstrap 
require_once 'includes/header-bootstrap.php';
?>

<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-md-6 text-center">
            <div class="card shadow-sm border-0">
                <div class="card-body p-5">
                    <div class="mb-4">
                        <i class="bi bi-box-arrow-right display-1 text-primary"></i>
                    </div>
                    <h1 class="h3 mb-3">Déconnexion réussie</h1>
                    <p class="text-muted mb-4">Vous avez été déconnecté avec succès. À bientôt !</p>
                    <a href="index.php" class="btn btn-primary">
                        <i class="bi bi-house-door me-2"></i>
                        Retour à l'accueil
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Inclure le footer Bootstrap
require_once 'includes/footer-bootstrap.php';
?>
