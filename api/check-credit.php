<?php
// api/check-credits.php - Middleware pour vérifier les crédits

function checkUserCredits($serviceName, $creditsRequired = 1) {
    if (!isLoggedIn()) {
        return [
            'success' => false,
            'error' => 'login_required',
            'message' => 'Connexion requise pour utiliser ce service'
        ];
    }
    
    $user = getCurrentUser();
    
    if ($user['credits'] < $creditsRequired) {
        return [
            'success' => false,
            'error' => 'insufficient_credits',
            'message' => 'Crédits insuffisants',
            'current_credits' => $user['credits'],
            'required_credits' => $creditsRequired,
            'can_watch_ad' => canWatchAdToday($user['id']),
            'upgrade_options' => getUpgradeOptions($user['subscription_plan'])
        ];
    }
    
    return ['success' => true];
}

function deductCredits($userId, $serviceName, $creditsUsed = 1, $additionalInfo = []) {
    try {
        $db = Database::getInstance()->getConnection();
        $db->beginTransaction();
        
        // Vérifier encore une fois les crédits (pour éviter les conditions de course)
        $stmt = $db->prepare("SELECT credits FROM users WHERE id = ? FOR UPDATE");
        $stmt->execute([$userId]);
        $currentCredits = $stmt->fetch()['credits'];
        
        if ($currentCredits < $creditsUsed) {
            $db->rollback();
            return [
                'success' => false,
                'error' => 'insufficient_credits',
                'message' => 'Crédits insuffisants'
            ];
        }
        
        // Déduire les crédits
        $stmt = $db->prepare("UPDATE users SET credits = credits - ?, total_credits_used = total_credits_used + ? WHERE id = ?");
        $stmt->execute([$creditsUsed, $creditsUsed, $userId]);
        
        // Nouveau solde
        $stmt = $db->prepare("SELECT credits FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $newBalance = $stmt->fetch()['credits'];
        
        // Enregistrer la transaction
        $description = "Service utilisé: " . ucfirst(str_replace('_', ' ', $serviceName));
        $stmt = $db->prepare("
            INSERT INTO credit_transactions (user_id, type, amount, balance_after, description, service_used)
            VALUES (?, 'spent', ?, ?, ?, ?)
        ");
        $stmt->execute([$userId, -$creditsUsed, $newBalance, $description, $serviceName]);
        
        // Enregistrer l'utilisation du service
        $stmt = $db->prepare("
            INSERT INTO service_usage (user_id, service_name, credits_cost, file_size_mb, processing_time_seconds)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $userId, 
            $serviceName, 
            $creditsUsed,
            $additionalInfo['file_size_mb'] ?? null,
            $additionalInfo['processing_time'] ?? null
        ]);
        
        $db->commit();
        
        return [
            'success' => true,
            'new_balance' => $newBalance,
            'credits_used' => $creditsUsed
        ];
        
    } catch (Exception $e) {
        if (isset($db)) {
            $db->rollback();
        }
        return [
            'success' => false,
            'error' => 'database_error',
            'message' => 'Erreur lors de la déduction des crédits'
        ];
    }
}

function canWatchAdToday($userId) {
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT COUNT(*) as ads_today 
            FROM ad_views 
            WHERE user_id = ? AND DATE(viewed_at) = CURDATE()
        ");
        $stmt->execute([$userId]);
        $adsToday = $stmt->fetch()['ads_today'];
        
        return $adsToday < 5; // Limite de 5 pubs par jour
        
    } catch (Exception $e) {
        return false;
    }
}

function getUpgradeOptions($currentPlan) {
    $options = [];
    
    switch ($currentPlan) {
        case 'free':
            $options[] = [
                'plan' => 'pro',
                'name' => 'Pro',
                'credits' => 200,
                'price' => 5.00,
                'savings' => 'Plus de crédits et fonctionnalités avancées'
            ];
            $options[] = [
                'plan' => 'max',
                'name' => 'Max',
                'credits' => 600,
                'price' => 12.50,
                'savings' => 'Crédits illimités et support prioritaire'
            ];
            break;
            
        case 'pro':
            $options[] = [
                'plan' => 'max',
                'name' => 'Max',
                'credits' => 600,
                'price' => 12.50,
                'savings' => '400 crédits supplémentaires par mois'
            ];
            break;
    }
    
    return $options;
}

function sendLowCreditsNotification($userId, $currentCredits) {
    if ($currentCredits <= 5 && $currentCredits > 0) {
        // Envoyer une notification (email, push, etc.)
        // Implementation dépendante du système de notification choisi
        
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                SELECT email, username, subscription_plan 
                FROM users 
                WHERE id = ?
            ");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            if ($user && $user['subscription_plan'] === 'free') {
                // Envoyer un email de notification pour encourager l'upgrade
                sendLowCreditsEmail($user['email'], $user['username'], $currentCredits);
            }
            
        } catch (Exception $e) {
            // Log l'erreur mais ne pas faire échouer la requête principale
            error_log("Erreur notification crédits faibles: " . $e->getMessage());
        }
    }
}

function sendLowCreditsEmail($email, $username, $credits) {
    $subject = "MultiServices - Crédits faibles";
    $message = "
    <html>
    <head><title>Crédits faibles</title></head>
    <body>
        <h2>Bonjour " . htmlspecialchars($username) . " !</h2>
        <p>Il ne vous reste que <strong>" . $credits . " crédit" . ($credits > 1 ? 's' : '') . "</strong> sur votre compte MultiServices.</p>
        
        <h3>Comment recharger vos crédits ?</h3>
        <ul>
            <li><strong>Regardez des publicités</strong> - Gagnez 1 crédit par pub (max 5/jour)</li>
            <li><strong>Parrainez des amis</strong> - Gagnez 20% de leurs crédits d'abonnement</li>
            <li><strong>Upgradez votre plan</strong> - Obtenez 200 ou 600 crédits par mois</li>
        </ul>
        
        <p><a href='" . $_SERVER['HTTP_HOST'] . "/profile.php' style='background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Voir mon profil</a></p>
        
        <p>Cordialement,<br>L'équipe MultiServices</p>
    </body>
    </html>
    ";
    
    $headers = array(
        'MIME-Version' => '1.0',
        'Content-type' => 'text/html; charset=UTF-8',
        'From' => 'MultiServices <noreply@multiservices.com>'
    );
    
    // En production, utiliser un vrai service d'email
    // mail($email, $subject, $message, $headers);
}

// Fonction utilitaire pour afficher une popup de crédits insuffisants
function generateInsufficientCreditsResponse($currentCredits, $requiredCredits, $serviceName) {
    $canWatchAd = canWatchAdToday($_SESSION['user_id']);
    
    return [
        'success' => false,
        'error' => 'insufficient_credits',
        'message' => 'Crédits insuffisants pour utiliser ce service',
        'data' => [
            'current_credits' => $currentCredits,
            'required_credits' => $requiredCredits,
            'service_name' => $serviceName,
            'can_watch_ad' => $canWatchAd,
            'ads_remaining' => $canWatchAd ? (5 - getTodayAdCount($_SESSION['user_id'])) : 0,
            'upgrade_options' => getUpgradeOptions(getCurrentUser()['subscription_plan']),
            'referral_link' => 'https://' . $_SERVER['HTTP_HOST'] . '/register.php?ref=' . getCurrentUser()['referral_code']
        ]
    ];
}

function getTodayAdCount($userId) {
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT COUNT(*) as count 
            FROM ad_views 
            WHERE user_id = ? AND DATE(viewed_at) = CURDATE()
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch()['count'];
    } catch (Exception $e) {
        return 0;
    }
}
?>