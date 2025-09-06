<?php
require_once 'config.php';

// Rediriger si déjà connecté
if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

// Configuration de la page
$page_title = 'Connexion - Mini Services';
$page_description = 'Connectez-vous à votre compte Mini Services pour accéder à tous vos outils et fonctionnalités.';
$page_keywords = 'connexion, login, compte, authentification, mini services';
$body_class = 'auth-page login-page d-flex flex-column min-vh-100';
$additional_css = [];
$additional_js = [];

// Inclure le header Bootstrap
require_once 'includes/header-bootstrap.php';
?>

<div class="auth-container flex-grow-1">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="auth-card">
                    <div class="auth-header">
                        <div class="text-center mb-4">
                            <div class="bg-primary bg-gradient rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                <i class="bi bi-box-arrow-in-right text-white fs-2"></i>
                            </div>
                            <h1 class="h3 fw-bold">Connexion</h1>
                            <p class="text-muted">Connectez-vous pour accéder à tous vos outils</p>
                        </div>
                    </div>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger d-flex align-items-center" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <div><?php echo htmlspecialchars($error); ?></div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success d-flex align-items-center" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            <div><?php echo htmlspecialchars($success); ?></div>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="bi bi-envelope me-2"></i>
                                Adresse email
                            </label>
                            <input type="email" 
                                   class="form-control form-control-lg" 
                                   id="email" 
                                   name="email" 
                                   required 
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                   placeholder="votre@email.com">
                            <div class="invalid-feedback">
                                Veuillez entrer une adresse email valide.
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="password" class="form-label">
                                <i class="bi bi-lock me-2"></i>
                                Mot de passe
                            </label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control form-control-lg" 
                                       id="password" 
                                       name="password" 
                                       required
                                       placeholder="Votre mot de passe">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="bi bi-eye" id="togglePasswordIcon"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback">
                                Veuillez entrer votre mot de passe.
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-box-arrow-in-right me-2"></i>
                                Se connecter
                            </button>
                        </div>
                    </form>
                    
                    <div class="auth-links">
                        <div class="row text-center">
                            <div class="col">
                                <p class="mb-0">
                                    Pas encore de compte ? 
                                    <a href="register.php" class="text-decoration-none fw-medium">
                                        S'inscrire gratuitement
                                    </a>
                                </p>
                            </div>
                        </div>
                        <div class="row text-center mt-2">
                            <div class="col">
                                <a href="#" class="text-muted text-decoration-none small">
                                    <i class="bi bi-question-circle me-1"></i>
                                    Mot de passe oublié ?
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Avantages de la connexion -->
                    <div class="mt-4 p-3 bg-light rounded">
                        <h6 class="fw-bold mb-3">
                            <i class="bi bi-star me-2 text-warning"></i>
                            Pourquoi se connecter ?
                        </h6>
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-clock-history text-primary me-2"></i>
                                    <small>Historique</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-cloud text-info me-2"></i>
                                    <small>Sauvegarde</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-coin text-warning me-2"></i>
                                    <small>Crédits gratuits</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-rocket text-success me-2"></i>
                                    <small>Premium</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('togglePasswordIcon');
    
    togglePassword.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        if (type === 'password') {
            toggleIcon.className = 'bi bi-eye';
        } else {
            toggleIcon.className = 'bi bi-eye-slash';
        }
    });
    
    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
    
    // Auto-focus sur le premier champ
    document.getElementById('email').focus();
});
</script>

<?php
// Inclure le footer Bootstrap
require_once 'includes/footer-bootstrap.php';
?>