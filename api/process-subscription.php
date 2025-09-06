<?php
// api/process-subscription.php
require_once '../config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if (empty($subscriptionId) || empty($planSlug)) {
    echo json_encode(['success' => false, 'message' => 'Données manquantes']);
    exit;
}

$userId = $_SESSION['user_id'];

try {
    $db = Database::getInstance()->getConnection();
    
    // Récupérer les détails du plan
    $stmt = $db->prepare("SELECT * FROM subscription_plans WHERE slug = ?");
    $stmt->execute([$planSlug]);
    $plan = $stmt->fetch();
    
    if (!$plan) {
        echo json_encode(['success' => false, 'message' => 'Plan non trouvé']);
        exit;
    }
    
    // Vérifier le parrainage
    $referrer = null;
    if (!empty($referralCode)) {
        $stmt = $db->prepare("SELECT id FROM users WHERE referral_code = ? AND id != ?");
        $stmt->execute([$referralCode, $userId]);
        $referrer = $stmt->fetch();
    }
    
    // Calculer les prix et durées
    $durationMonths = $duration === 'yearly' ? 12 : 1;
    $basePrice = $duration === 'yearly' ? $plan['yearly_price'] : $plan['monthly_price'];
    $originalPrice = $basePrice * ($duration === 'yearly' ? 12 : 1);
    
    // Appliquer les réductions
    $discountPercent = 0;
    $referralDiscountPercent = 0;
    $finalPrice = $originalPrice;
    
    if ($duration === 'yearly') {
        $discountPercent = 30;
        $finalPrice *= 0.7; // 30% de réduction
    }
    
    if ($referrer) {
        $referralDiscountPercent = 10;
        $finalPrice *= 0.9; // 10% de réduction supplémentaire
    }
    
    $startDate = new DateTime();
    $endDate = clone $startDate;
    $endDate->add(new DateInterval('P' . $durationMonths . 'M'));
    
    // Commencer une transaction
    $db->beginTransaction();
    
    try {
        // Créer l'abonnement
        $stmt = $db->prepare("
            INSERT INTO subscriptions (
                user_id, plan_id, duration_months, original_price, discount_percent,
                referral_discount_percent, final_price, paypal_subscription_id,
                status, start_date, end_date, referred_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active', ?, ?, ?)
        ");
        
        $stmt->execute([
            $userId, $plan['id'], $durationMonths, $originalPrice, $discountPercent,
            $referralDiscountPercent, $finalPrice, $subscriptionId,
            $startDate->format('Y-m-d H:i:s'), $endDate->format('Y-m-d H:i:s'),
            $referrer ? $referrer['id'] : null
        ]);
        
        $newSubscriptionId = $db->lastInsertId();
        
        // Mettre à jour l'utilisateur
        $stmt = $db->prepare("
            UPDATE users SET 
                subscription_plan = ?,
                subscription_status = 'active',
                subscription_start_date = ?,
                subscription_end_date = ?,
                credits = credits + ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            $planSlug,
            $startDate->format('Y-m-d H:i:s'),
            $endDate->format('Y-m-d H:i:s'),
            $plan['credits'],
            $userId
        ]);
        
        // Enregistrer la transaction de crédits
        $stmt = $db->prepare("
            SELECT credits FROM users WHERE id = ?
        ");
        $stmt->execute([$userId]);
        $newBalance = $stmt->fetch()['credits'];
        
        $stmt = $db->prepare("
            INSERT INTO credit_transactions (user_id, type, amount, balance_after, description, reference_id)
            VALUES (?, 'purchase', ?, ?, ?, ?)
        ");
        $stmt->execute([
            $userId, $plan['credits'], $newBalance,
            'Crédits d\'abonnement ' . $plan['name'],
            $newSubscriptionId
        ]);
        
        // Traiter le parrainage si applicable
        if ($referrer) {
            $referralCredits = intval($plan['credits'] * 0.2); // 20% des crédits
            
            // Créer l'enregistrement de parrainage
            $stmt = $db->prepare("
                INSERT INTO referrals (referrer_id, referred_id, subscription_id, reward_credits, status)
                VALUES (?, ?, ?, ?, 'completed')
            ");
            $stmt->execute([$referrer['id'], $userId, $newSubscriptionId, $referralCredits]);
            
            // Récompenser le parrain
            $stmt = $db->prepare("CALL process_referral_reward(?, ?, ?)");
            $stmt->execute([$referrer['id'], $newSubscriptionId, $referralCredits]);
            
            // Mettre à jour le compteur de parrainages
            $stmt = $db->prepare("UPDATE users SET total_referrals = total_referrals + 1 WHERE id = ?");
            $stmt->execute([$referrer['id']]);
        }
        
        $db->commit();
        
        echo json_encode([
            'success' => true,
            'subscription_id' => $newSubscriptionId,
            'credits_added' => $plan['credits'],
            'new_balance' => $newBalance,
            'plan_name' => $plan['name'],
            'end_date' => $endDate->format('d/m/Y'),
            'referral_reward' => $referrer ? $referralCredits : 0
        ]);
        
    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la création de l\'abonnement: ' . $e->getMessage()
    ]);
}
?>$_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Connexion requise']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$subscriptionId = isset($input['subscription_id']) ? sanitize($input['subscription_id']) : '';
$planSlug = isset($input['plan_slug']) ? sanitize($input['plan_slug']) : '';
$duration = isset($input['duration']) ? sanitize($input['duration']) : 'monthly';
$referralCode = isset($input['referral_code']) ? sanitize($input['referral_code']) : '';
$paypalData = isset($input['paypal_data']) ? $input['paypal_data'] : null;

if (