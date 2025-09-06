<?php
require_once 'config.php';
require_once 'includes/page-helper.php';

$error = '';
$success = '';
$referralCode = isset($_GET['ref']) ? sanitize($_GET['ref']) : '';

// Vérifier le code de parrainage si fourni
$referrer = null;
if ($referralCode) {
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT id, username FROM users WHERE referral_code = ? AND status = 'active'");
        $stmt->execute([$referralCode]);
        $referrer = $stmt->fetch();
    } catch (Exception $e) {
        error_log("Erreur vérification parrainage: " . $e->getMessage());
    }
}

// Rediriger si déjà connecté
if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

// Validation CSRF
$csrf = CSRFProtection::getInstance();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Appliquer le rate limiting SEULEMENT sur les tentatives d'inscription
    applyRateLimiting('auth_attempts');
    
    // Vérifier le token CSRF
    if (!$csrf->validateToken($_POST['csrf_token'] ?? null)) {
        $error = 'Erreur de sécurité. Veuillez recharger la page.';
    } else {
        $username = sanitize($_POST['username']);
        $email = sanitize($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $referral_code_input = isset($_POST['referral_code']) ? sanitize($_POST['referral_code']) : $referralCode;
        
        // Validations
        if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
            $error = 'Veuillez remplir tous les champs obligatoires.';
        } elseif (strlen($username) < 3) {
            $error = 'Le nom d\'utilisateur doit contenir au moins 3 caractères.';
        } elseif (strlen($password) < 6) {
            $error = 'Le mot de passe doit contenir au moins 6 caractères.';
        } elseif ($password !== $confirm_password) {
            $error = 'Les mots de passe ne correspondent pas.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Veuillez entrer une adresse email valide.';
        } else {
            try {
                $db = Database::getInstance()->getConnection();
                
                // Vérifier si l'email existe déjà
                $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $error = 'Cette adresse email est déjà utilisée.';
                } else {
                    // Vérifier si le nom d'utilisateur existe déjà
                    $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
                    $stmt->execute([$username]);
                    if ($stmt->fetch()) {
                        $error = 'Ce nom d\'utilisateur est déjà pris.';
                    } else {
                        // Vérifier le code de parrainage si fourni
                        $referrerId = null;
                        if (!empty($referral_code_input)) {
                            $stmt = $db->prepare("SELECT id FROM users WHERE referral_code = ? AND status = 'active'");
                            $stmt->execute([$referral_code_input]);
                            $referrerData = $stmt->fetch();
                            if ($referrerData) {
                                $referrerId = $referrerData['id'];
                            } else {
                                $error = 'Code de parrainage invalide.';
                            }
                        }
                        
                        if (empty($error)) {
                            try {
                                $db->beginTransaction();
                                
                                // Créer le compte avec 100 crédits de base
                                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                                
                                // Générer un code de parrainage unique
                                $newReferralCode = generateUniqueReferralCode($db);
                                
                                $stmt = $db->prepare("
                                    INSERT INTO users (username, email, password_hash, credits, referred_by, referral_code, plan_type, status, created_at) 
                                    VALUES (?, ?, ?, 100, ?, ?, 'free', 'active', NOW())
                                ");
                                
                                if ($stmt->execute([$username, $email, $hashed_password, $referrerId, $newReferralCode])) {
                                    $newUserId = $db->lastInsertId();
                                    
                                    // Enregistrer la transaction de crédits de bienvenue
                                    $stmt = $db->prepare("
                                        INSERT INTO credit_transactions (user_id, transaction_type, amount, balance_after, description, created_at)
                                        VALUES (?, 'earned', 100, 100, 'Crédits de bienvenue', NOW())
                                    ");
                                    $stmt->execute([$newUserId]);
                                    
                                    // Log de sécurité
                                    $stmt = $db->prepare("
                                        INSERT INTO security_logs (event_type, user_id, ip_address, user_agent, details) 
                                        VALUES ('user_registration', ?, ?, ?, ?)
                                    ");
                                    $stmt->execute([
                                        $newUserId,
                                        $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                                        $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                                        json_encode(['email' => $email, 'username' => $username, 'referrer_id' => $referrerId])
                                    ]);
                                    
                                    $db->commit();
                                    
                                    $success = 'Compte créé avec succès ! Vous avez reçu 100 crédits de bienvenue.';
                                    
                                    // Connexion automatique
                                    $_SESSION['user_id'] = $newUserId;
                                    $_SESSION['username'] = $username;
                                    
                                    // Redirection après 2 secondes
                                    header("Refresh: 2; url=index.php");
                                } else {
                                    throw new Exception('Erreur lors de la création du compte');
                                }
                                
                            } catch (Exception $e) {
                                $db->rollback();
                                error_log("Erreur création compte: " . $e->getMessage());
                                $error = 'Erreur lors de la création du compte. Veuillez réessayer.';
                            }
                        }
                    }
                }
            } catch (Exception $e) {
                error_log("Erreur inscription: " . $e->getMessage());
                $error = 'Erreur technique. Veuillez réessayer.';
            }
        }
    }
}

function generateUniqueReferralCode($db) {
    $maxAttempts = 100;
    $attempt = 0;
    
    do {
        $code = 'REF' . strtoupper(substr(md5(uniqid() . rand()), 0, 8));
        $stmt = $db->prepare("SELECT id FROM users WHERE referral_code = ?");
        $stmt->execute([$code]);
        $exists = $stmt->fetch();
        $attempt++;
    } while ($exists && $attempt < $maxAttempts);
    
    return $exists ? 'REF' . uniqid() : $code;
}

// Générer un nouveau token CSRF
$csrfToken = $csrf->generateToken();

// Configuration de la page
$page_title = 'Inscription - Mini Services';
$page_description = 'Créez votre compte Mini Services et obtenez 100 crédits gratuits';
$body_class = 'd-flex flex-column min-vh-100';
$additional_css = [];
$additional_js = [];

// Inclure le header Bootstrap
require_once 'includes/header-bootstrap.php';
?>

<div class="flex-grow-1 d-flex align-items-center py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4 p-md-5">
                        <!-- En-tête avec icône -->
                        <div class="text-center mb-4">
                            <div class="bg-primary bg-gradient rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                <i class="bi bi-person-plus text-white fs-2"></i>
                            </div>
                            <h1 class="h3 fw-bold">Inscription Gratuite</h1>
                            <p class="text-muted">Rejoignez-nous et obtenez 100 crédits gratuits</p>
                        </div>

                        <?php if ($error): ?>
                            <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <div><?php echo htmlspecialchars($error); ?></div>
                            </div>
                        <?php endif; ?>

                        <?php if ($referrer): ?>
                            <div class="alert alert-info d-flex mb-4" role="alert">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-person-check fs-4 me-2"></i>
                                    <div>
                                        <div class="fw-bold">Invité par <?php echo htmlspecialchars($referrer['username']); ?></div>
                                        <div>Vous recevrez 20 crédits bonus !</div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <form method="POST" class="needs-validation" novalidate>
                            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                            
                            <div class="mb-3">
                                <label for="username" class="form-label">
                                    <i class="bi bi-person me-2"></i>
                                    Nom d'utilisateur
                                </label>
                                <input type="text" 
                                       class="form-control form-control-lg" 
                                       id="username" 
                                       name="username" 
                                       required 
                                       minlength="3"
                                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                                       placeholder="Votre nom d'utilisateur">
                                <div class="form-text">Au moins 3 caractères</div>
                            </div>

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
                            </div>

                            <div class="mb-3">
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
                                           minlength="6"
                                           placeholder="Au moins 6 caractères">
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="confirm_password" class="form-label">
                                    <i class="bi bi-lock-fill me-2"></i>
                                    Confirmer le mot de passe
                                </label>
                                <input type="password" 
                                       class="form-control form-control-lg" 
                                       id="confirm_password" 
                                       name="confirm_password" 
                                       required
                                       placeholder="Confirmez votre mot de passe">
                            </div>

                            <?php if (!$referrer): ?>
                            <div class="mb-4">
                                <label for="referral_code" class="form-label">
                                    <i class="bi bi-gift me-2"></i>
                                    Code de parrainage (optionnel)
                                </label>
                                <input type="text" 
                                       class="form-control form-control-lg" 
                                       id="referral_code" 
                                       name="referral_code"
                                       placeholder="Ex: REF123456">
                                <div class="form-text">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Obtenez 20 crédits bonus avec un code valide
                                </div>
                            </div>
                            <?php endif; ?>

                            <div class="d-grid mb-4">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-rocket-takeoff me-2"></i>
                                    S'inscrire et obtenir 100 crédits
                                </button>
                            </div>

                            <div class="text-center">
                                <p class="mb-0">
                                    Déjà un compte ? 
                                    <a href="login.php" class="text-decoration-none fw-medium">
                                        Se connecter
                                    </a>
                                </p>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Avantages de l'inscription -->
                <div class="mt-4 p-4 bg-light rounded-3">
                    <h5 class="fw-bold mb-3">
                        <i class="bi bi-stars me-2 text-warning"></i>
                        Avantages de l'inscription
                    </h5>
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-coin text-warning me-2"></i>
                                <small>100 crédits offerts</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-tools text-primary me-2"></i>
                                <small>Tous les outils</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-clock-history text-info me-2"></i>
                                <small>Historique illimité</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-share text-success me-2"></i>
                                <small>Programme parrain</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Validation en temps réel du mot de passe
document.getElementById('confirm_password').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmPassword = this.value;
    
    if (confirmPassword && password !== confirmPassword) {
        this.setCustomValidity('Les mots de passe ne correspondent pas');
    } else {
        this.setCustomValidity('');
    }
});

// Toggle password visibility
document.getElementById('togglePassword').addEventListener('click', function() {
    const passwordField = document.getElementById('password');
    const confirmPasswordField = document.getElementById('confirm_password');
    const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
    
    passwordField.setAttribute('type', type);
    confirmPasswordField.setAttribute('type', type);
    
    this.querySelector('i').classList.toggle('bi-eye');
    this.querySelector('i').classList.toggle('bi-eye-slash');
});
</script>

<?php
require_once 'includes/footer-bootstrap.php';
?>