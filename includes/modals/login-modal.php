<?php
// Validation CSRF
$csrf = CSRFProtection::getInstance();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Appliquer le rate limiting SEULEMENT sur les tentatives de connexion
    applyRateLimiting('auth_attempts');
    
    if (!$csrf->validateToken($_POST['csrf_token'] ?? null)) {
        $error = 'Erreur de sécurité. Veuillez recharger la page.';
    } else {
        $email = sanitize($_POST['email']);
        $password = $_POST['password'];
        
        if (empty($email) || empty($password)) {
            $error = 'Veuillez remplir tous les champs.';
        } else {
            try {
                $db = Database::getInstance()->getConnection();
                $stmt = $db->prepare("SELECT id, username, password_hash, plan_type, status FROM users WHERE email = ? AND status = 'active'");
                $stmt->execute([$email]);
                $user = $stmt->fetch();
                
                if ($user && password_verify($password, $user['password_hash'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    
                    // Mise à jour dernière connexion
                    $stmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                    $stmt->execute([$user['id']]);
                    
                    // Log de sécurité
                    $stmt = $db->prepare("
                        INSERT INTO security_logs (event_type, user_id, ip_address, user_agent, details) 
                        VALUES ('login_success', ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $user['id'],
                        $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                        $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                        json_encode(['email' => $email])
                    ]);
                    
                    $success = true;
                } else {
                    $error = 'Email ou mot de passe incorrect.';
                    
                    // Log tentative échouée
                    $stmt = $db->prepare("
                        INSERT INTO security_logs (event_type, ip_address, user_agent, details) 
                        VALUES ('login_failure', ?, ?, ?)
                    ");
                    $stmt->execute([
                        $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                        $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                        json_encode(['email' => $email])
                    ]);
                }
            } catch (Exception $e) {
                error_log("Erreur de connexion: " . $e->getMessage());
                $error = 'Erreur technique. Veuillez réessayer.';
            }
        }
    }
}

// Générer un nouveau token CSRF
$csrfToken = $csrf->generateToken();
?>

<div class="auth-content">
            <p class="text-muted mt-3">Connectez-vous pour accéder à tous vos outils</p>
        <div id="loginError" class="alert alert-danger d-flex align-items-center alert-hidden">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <div></div>
        </div>

        <form id="loginForm" class="auth-form" method="POST" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">

            <div class="form-group">
                <label for="email">
                    <i class="fas fa-envelope"></i>
                    Email
                </label>
                <input type="email" id="email" name="email" class="form-control" required placeholder="votre@email.com">
            </div>

            <div class="form-group">
                <label for="password">
                    <i class="fas fa-lock"></i>
                    Mot de passe
                </label>
                <input type="password" id="password" name="password" class="form-control" required placeholder="Votre mot de passe">
            </div>

            <button type="submit" class="btn btn-primary btn-auth">
                <i class="fas fa-sign-in-alt"></i>
                Se connecter
            </button>
        </form>

        <div class="auth-links">
            <p class="mb-2">Pas encore de compte ? 
                <a href="#" class="text-decoration-none fw-medium" onclick="closeLoginModal(); openRegisterModal();">
                    S'inscrire gratuitement
                </a>
            </p>
            <p class="mb-0">
                <a href="#" class="text-muted text-decoration-none small">
                    <i class="bi bi-question-circle me-1"></i>
                    Mot de passe oublié ?
                </a>
            </p>
        </div>
    </div>
<script>
document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = e.target;
    const errorDiv = document.getElementById('loginError');
    
    fetch(window.location.href, {
        method: 'POST',
        body: new FormData(form)
    })
    .then(response => response.text())
    .then(html => {
        if (html.includes('login_success')) {
            location.reload();
        } else {
            errorDiv.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2"></i>Email ou mot de passe incorrect.';
            errorDiv.classList.remove('alert-hidden');
        }
    })
    .catch(err => {
        errorDiv.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2"></i>Erreur technique. Veuillez réessayer.';
        errorDiv.classList.remove('alert-hidden');
    });
});

async function handleLogin(e) {
    e.preventDefault();
    const form = e.target;
    const errorDiv = document.getElementById('loginError');
    
    try {
        const response = await fetch(BASE_URL + '/api/auth/login.php', {
            method: 'POST',
            body: new FormData(form)
        });
        
        const result = await response.json();
        
        if (result.success) {
            location.reload();
        } else {
            errorDiv.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2"></i>' + result.message;
            errorDiv.classList.remove('alert-hidden');
        }
    } catch (err) {
        errorDiv.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2"></i>Erreur technique. Veuillez réessayer.';
        errorDiv.classList.remove('alert-hidden');
    }
}
</script>

<style>
.auth-card {
    width: 100%;
    max-width: 400px;
    margin: 0 auto;
    padding: 2.5rem;
    background: white;
    border-radius: 1rem;
    position: relative;
}

.auth-content {
    padding: 0px;
    margin:0px;
}

.auth-icon-container {
    margin-bottom: 2rem;
}

.auth-icon {
    width: 80px;
    height: 80px;
    background: var(--bs-primary-bg-subtle);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    color: var(--bs-primary);
}

.auth-form {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
    margin-bottom: 2rem;
}

.form-group {
    margin-bottom: 0;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
    color: var(--bs-gray-700);
}

.form-control {
    width: 100%;
    padding: 0.75rem 1rem;
    font-size: 1rem;
    border: 1px solid var(--bs-gray-300);
    border-radius: 0.5rem;
    transition: all 0.2s;
}

.form-control:focus {
    border-color: var(--bs-primary);
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.btn-auth {
    width: 100%;
    padding: 0.875rem;
    font-size: 1rem;
    border-radius: 0.5rem;
}

.auth-links {
    text-align: center;
    padding-top: 1.5rem;
    margin-top: 1.5rem;
    border-top: 1px solid var(--bs-border-color);
}

/* Close button styles */
.close {
    position: absolute;
    right: 1.5rem;
    top: 1.5rem;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: transparent;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: var(--bs-gray-600);
    transition: all 0.2s ease;
}

.close:hover {
    background-color: rgba(220, 38, 38, 0.1);
    color: rgb(220, 38, 38);
}

/* Responsive adjustments */
@media (max-width: 576px) {
    .auth-card {
        width: 100%;
        padding: 1.5rem;
    }

    .auth-form {
        gap: 1rem;
    }

    .form-control {
        padding: 0.625rem 0.875rem;
        font-size: 0.95rem;
    }
}

/* Modal adjustments */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    align-items: center;
    justify-content: center;
    z-index: 1050;
}

.modal.active {
    display: flex;
}

.modal-content {
    position: relative;
    max-height: 90vh;
    overflow-y: auto;
    margin: 1rem;
    width: 100%;
    max-width: 400px;
    animation: modalSlideIn 0.3s ease;
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

/* Add this new style for alert */
.alert-hidden {
    display: none !important;
}
</style>
}
</style>
