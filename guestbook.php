<?php
// guestbook.php
require_once 'config.php';

$user = getCurrentUser();
$error = '';
$success = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_message'])) {
    $name = sanitize($_POST['name']);
    $email = isset($_POST['email']) ? sanitize($_POST['email']) : '';
    $message = sanitize($_POST['message']);
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 5;
    
    if (empty($name) || empty($message)) {
        $error = 'Le nom et le message sont requis.';
    } elseif (strlen($message) < 10) {
        $error = 'Le message doit contenir au moins 10 caractères.';
    } elseif (strlen($message) > 1000) {
        $error = 'Le message ne peut pas dépasser 1000 caractères.';
    } else {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("INSERT INTO guestbook_messages (name, email, message, rating, user_id, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $userId = $user ? $user['id'] : null;
            
            if ($stmt->execute([$name, $email, $message, $rating, $userId])) {
                $success = 'Merci pour votre message ! Il apparaîtra après modération.';
                // Reset des champs
                $_POST = [];
            } else {
                $error = 'Erreur lors de l\'envoi du message.';
            }
        } catch (Exception $e) {
            $error = 'Erreur lors de l\'envoi du message.';
        }
    }
}

// Récupérer les messages approuvés
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT * FROM guestbook_messages WHERE is_approved = 1 ORDER BY created_at DESC LIMIT 50");
    $stmt->execute();
    $messages = $stmt->fetchAll();
} catch (Exception $e) {
    $messages = [];
}

// Statistiques
try {
    $stmt = $db->prepare("SELECT COUNT(*) as total, AVG(rating) as avg_rating FROM guestbook_messages WHERE is_approved = 1");
    $stmt->execute();
    $stats = $stmt->fetch();
    $totalMessages = $stats['total'] ?? 0;
    $averageRating = round($stats['avg_rating'] ?? 5, 1);
} catch (Exception $e) {
    $totalMessages = 0;
    $averageRating = 5.0;
}

// Configuration de la page
$page_title = 'Livre d\'or - Mini Services';
$page_description = 'Partagez votre expérience avec Mini Services et laissez un message d\'encouragement';
$body_class = 'd-flex flex-column min-vh-100';

// Inclure le header Bootstrap
require_once 'includes/header-bootstrap.php';
?>

<div class="container py-5">
    <div class="row justify-content-center mb-5">
        <div class="col-lg-8 text-center">
            <div class="mb-4">
                <i class="bi bi-book display-1 text-primary"></i>
            </div>
            <h1 class="h2 mb-4">Livre d'or</h1>
            <p class="lead text-muted">Partagez votre expérience et encouragez notre communauté</p>
            
            <!-- Statistiques -->
            <div class="row g-4 mt-4">
                <div class="col-md-4">
                    <div class="card border-0 bg-light">
                        <div class="card-body">
                            <h3 class="display-4 fw-bold text-primary"><?php echo $totalMessages; ?></h3>
                            <p class="text-muted mb-0">Messages</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 bg-light">
                        <div class="card-body">
                            <div class="text-warning mb-2 h3">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <i class="bi bi-star-fill <?php echo $i <= $averageRating ? 'text-warning' : 'text-muted'; ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <p class="text-muted mb-0"><?php echo number_format($averageRating, 1); ?>/5</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 bg-light">
                        <div class="card-body">
                            <div class="display-4 fw-bold text-success">🎉</div>
                            <p class="text-muted mb-0">Merci !</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row gy-4">
        <!-- Formulaire -->
        <div class="col-lg-5">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h2 class="h4 mb-4"><i class="bi bi-pencil me-2"></i>Laissez votre message</h2>
                    
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
                        <div class="mb-3">
                            <label for="name" class="form-label">Votre nom *</label>
                            <input type="text" class="form-control" id="name" name="name" required
                                   value="<?php echo $user ? htmlspecialchars($user['username']) : ''; ?>"
                                   <?php echo $user ? 'readonly' : ''; ?>>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email (optionnel)</label>
                            <input type="email" class="form-control" id="email" name="email"
                                   value="<?php echo $user ? htmlspecialchars($user['email']) : ''; ?>"
                                   <?php echo $user ? 'readonly' : ''; ?>>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Note</label>
                            <div class="rating-input text-warning h4" id="ratingInput">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <i class="bi bi-star rating-star" data-value="<?php echo $i; ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <input type="hidden" name="rating" id="ratingValue" value="5">
                        </div>

                        <div class="mb-4">
                            <label for="message" class="form-label">Votre message *</label>
                            <textarea class="form-control" id="message" name="message" rows="4" required
                                      minlength="10" maxlength="1000"></textarea>
                            <div class="form-text text-end">
                                <span id="charCount">0</span>/1000
                            </div>
                        </div>

                        <button type="submit" name="add_message" class="btn btn-primary w-100">
                            <i class="bi bi-send me-2"></i>Envoyer le message
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Messages -->
        <div class="col-lg-7">
            <h2 class="h4 mb-4"><i class="bi bi-chat-quote me-2"></i>Messages de la communauté</h2>
            
            <?php if (empty($messages)): ?>
                <div class="text-center p-5 bg-light rounded">
                    <div class="display-1 text-muted mb-4">
                        <i class="bi bi-chat-square"></i>
                    </div>
                    <h3 class="h5">Aucun message pour le moment</h3>
                    <p class="text-muted">Soyez le premier à partager votre expérience !</p>
                </div>
            <?php else: ?>
                <div class="messages-list">
                    <?php foreach ($messages as $msg): ?>
                        <div class="card shadow-sm border-0 mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary rounded-circle text-white d-flex align-items-center justify-content-center me-3" 
                                             style="width: 40px; height: 40px;">
                                            <?php echo strtoupper(substr($msg['name'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <h5 class="card-title mb-0"><?php echo htmlspecialchars($msg['name']); ?></h5>
                                            <small class="text-muted">
                                                <?php echo date('d/m/Y H:i', strtotime($msg['created_at'])); ?>
                                            </small>
                                        </div>
                                    </div>
                                    <div class="text-warning">
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                            <i class="bi bi-star-fill <?php echo $i <= $msg['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <p class="card-text"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Système de notation
    const stars = document.querySelectorAll('.rating-star');
    const ratingValue = document.getElementById('ratingValue');

    stars.forEach(star => {
        star.addEventListener('mouseover', function() {
            const value = this.dataset.value;
            highlightStars(value);
        });

        star.addEventListener('click', function() {
            const value = this.dataset.value;
            ratingValue.value = value;
            highlightStars(value);
            stars.forEach(s => s.classList.remove('locked'));
            this.classList.add('locked');
        });
    });

    document.getElementById('ratingInput').addEventListener('mouseout', function() {
        if (!document.querySelector('.rating-star.locked')) {
            highlightStars(ratingValue.value);
        }
    });

    function highlightStars(count) {
        stars.forEach((star, index) => {
            if (index < count) {
                star.classList.remove('bi-star');
                star.classList.add('bi-star-fill');
            } else {
                star.classList.remove('bi-star-fill');
                star.classList.add('bi-star');
            }
        });
    }

    // Compteur de caractères
    const messageArea = document.getElementById('message');
    const charCount = document.getElementById('charCount');

    messageArea.addEventListener('input', function() {
        const count = this.value.length;
        charCount.textContent = count;
        
        if (count > 900) {
            charCount.classList.add('text-danger');
        } else {
            charCount.classList.remove('text-danger');
        }
    });
});
</script>

<?php
require_once 'includes/footer-bootstrap.php';
?>