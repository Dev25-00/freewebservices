<?php
// Traitement de l'inscription
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation CSRF
    $csrf = CSRFProtection::getInstance();
    
    if (!$csrf->validateToken($_POST['csrf_token'] ?? null)) {
        $error = 'Erreur de sécurité. Veuillez recharger la page.';
    } else {
        $username = sanitize($_POST['username']);
        $email = sanitize($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (empty($username) || empty($email) || empty($password)) {
            $error = 'Tous les champs sont requis.';
        } elseif (strlen($username) < 3) {
            $error = 'Le nom d\'utilisateur doit contenir au moins 3 caractères.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Email invalide.';
        } elseif (strlen($password) < 6) {
            $error = 'Le mot de passe doit contenir au moins 6 caractères.';
        } elseif ($password !== $confirm_password) {
            $error = 'Les mots de passe ne correspondent pas.';
        } else {
            try {
                $db = Database::getInstance()->getConnection();
                $db->beginTransaction();

                // Vérifier si l'email existe
                $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    throw new Exception('Cet email est déjà utilisé.');
                }

                // Créer l'utilisateur
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $referral_code = 'REF' . strtoupper(substr(md5(uniqid()), 0, 8));
                
                $stmt = $db->prepare("INSERT INTO users (username, email, password_hash, credits, referral_code) VALUES (?, ?, ?, 100, ?)");
                if ($stmt->execute([$username, $email, $password_hash, $referral_code])) {
                    $userId = $db->lastInsertId();
                    $_SESSION['user_id'] = $userId;
                    $_SESSION['username'] = $username;
                    
                    $db->commit();
                    $success = true;
                    echo json_encode(['success' => true]);
                    exit;
                }
            } catch (Exception $e) {
                $db->rollBack();
                $error = $e->getMessage();
            }
        }
    }
    
    if (isset($error)) {
        echo json_encode(['success' => false, 'error' => $error]);
        exit;
    }
}
?>

<div class="modal-header">
    <h2><i class="fas fa-user-plus"></i> Inscription</h2>
    <button type="button" class="close" onclick="closeRegisterModal()">&times;</button>
</div>

<div class="auth-content">
    <p class="text-muted mt-3">Rejoignez Mini Services et obtenez 100 crédits gratuits</p>
    
    <div id="registerError" class="alert alert-danger d-flex align-items-center alert-hidden">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <div></div>
    </div>
    
    <form id="registerForm" class="auth-form" method="POST" onsubmit="handleRegister(event)" novalidate>
        <input type="hidden" name="csrf_token" value="<?php echo CSRFProtection::getInstance()->generateToken(); ?>">
        
        <div class="form-group">
            <label for="username">
                <i class="fas fa-user"></i>
                Nom d'utilisateur *
            </label>
            <input type="text" id="username" name="username" class="form-control" required minlength="3"
                   placeholder="Votre nom d'utilisateur">
        </div>
        
        <div class="form-group">
            <label for="reg-email">
                <i class="fas fa-envelope"></i>
                Email *
            </label>
            <input type="email" id="reg-email" name="email" class="form-control" required 
                   placeholder="votre@email.com">
        </div>
        
        <div class="form-group">
            <label for="reg-password">
                <i class="fas fa-lock"></i>
                Mot de passe *
            </label>
            <div class="input-group">
                <input type="password" id="reg-password" name="password" class="form-control" required minlength="6"
                       placeholder="Au moins 6 caractères">
                <button class="btn btn-outline-secondary" type="button" id="toggleRegPassword">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
        </div>

        <div class="form-group">
            <label for="reg-confirm-password">
                <i class="fas fa-lock"></i>
                Confirmer le mot de passe *
            </label>
            <input type="password" id="reg-confirm-password" name="confirm_password" class="form-control" required
                   placeholder="Confirmez votre mot de passe">
            <div class="invalid-feedback">Les mots de passe ne correspondent pas</div>
        </div>
        
        <button type="submit" class="btn btn-primary btn-auth">
            <i class="fas fa-rocket"></i>
            S'inscrire et obtenir 100 crédits
        </button>
    </form>
    
    <div class="auth-links">
        <p>Déjà un compte ? <a href="#" onclick="closeRegisterModal(); openLoginModal();">Se connecter</a></p>
    </div>
</div>

<script>
// Validation des mots de passe en temps réel
document.getElementById('reg-confirm-password').addEventListener('input', function() {
    const password = document.getElementById('reg-password').value;
    const confirmPassword = this.value;
    const feedback = this.nextElementSibling;
    
    if (password !== confirmPassword) {
        this.setCustomValidity('Les mots de passe ne correspondent pas');
        feedback.style.display = 'block';
    } else {
        this.setCustomValidity('');
        feedback.style.display = 'none';
    }
});

// Toggle password visibility
document.getElementById('toggleRegPassword').addEventListener('click', function() {
    const passwordField = document.getElementById('reg-password');
    const confirmPasswordField = document.getElementById('reg-confirm-password');
    const icon = this.querySelector('i');
    
    const type = passwordField.type === 'password' ? 'text' : 'password';
    passwordField.type = type;
    confirmPasswordField.type = type;
    
    icon.classList.toggle('bi-eye');
    icon.classList.toggle('bi-eye-slash');
});

async function handleRegister(e) {
    e.preventDefault();
    const form = e.target;
    const errorDiv = document.getElementById('registerError');
    
    try {
        const formData = new FormData(form);
        const response = await fetch(BASE_URL + '/register.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            location.reload(); // Rafraîchir la page après inscription réussie
        } else {
            errorDiv.innerHTML = `<i class="bi bi-exclamation-triangle-fill me-2"></i>${data.error}`;
            errorDiv.classList.remove('alert-hidden');
        }
    } catch (err) {
        errorDiv.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2"></i>Erreur technique. Veuillez réessayer.';
        errorDiv.classList.remove('alert-hidden');
    }
}
</script>
        if (data.success) {
            location.reload(); // Rafraîchir la page après inscription réussie
        } else {
            errorDiv.innerHTML = `<i class="bi bi-exclamation-triangle-fill me-2"></i>${data.error}`;
            errorDiv.classList.remove('alert-hidden');
        }
    } catch (err) {
        errorDiv.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2"></i>Erreur technique. Veuillez réessayer.';
        errorDiv.classList.remove('alert-hidden');
    }
}
</script>
