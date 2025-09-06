<?php
// upgrade.php
require_once 'config.php';

if (!isLoggedIn()) {
    header('Location: login.php?redirect=upgrade.php');
    exit();
}

$user = getCurrentUser();
$selectedPlan = isset($_GET['plan']) ? sanitize($_GET['plan']) : 'pro';
$referralCode = isset($_GET['ref']) ? sanitize($_GET['ref']) : '';

// Récupérer les détails des plans
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT * FROM subscription_plans WHERE slug = ? AND is_active = TRUE");
    $stmt->execute([$selectedPlan]);
    $plan = $stmt->fetch();
    
    if (!$plan) {
        header('Location: profile.php');
        exit();
    }
    
    // Si un code de parrainage est fourni, vérifier qu'il est valide
    $referrer = null;
    if ($referralCode) {
        $stmt = $db->prepare("SELECT id, username FROM users WHERE referral_code = ? AND id != ?");
        $stmt->execute([$referralCode, $user['id']]);
        $referrer = $stmt->fetch();
    }
    
} catch (Exception $e) {
    header('Location: profile.php');
    exit();
}

// Calculer les prix avec réductions
function calculatePrice($basePrice, $isYearly = false, $hasReferral = false) {
    $price = $basePrice;
    
    if ($isYearly) {
        $price *= 12; // Prix pour 12 mois
        $price *= 0.7; // 30% de réduction
    }
    
    if ($hasReferral) {
        $price *= 0.9; // 10% de réduction supplémentaire
    }
    
    return $price;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upgrader vers <?php echo ucfirst($plan['name']); ?> - MultiServices</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://www.paypal.com/sdk/js?client-id=YOUR_PAYPAL_CLIENT_ID&currency=EUR&components=buttons&vault=true&intent=subscription"></script>
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <div class="nav-container">
                <div class="nav-brand">
                    <a href="index.php"><h1><i class="fas fa-tools"></i> MultiServices</h1></a>
                </div>
                <ul class="nav-menu">
                    <li><a href="index.php" class="nav-link">Accueil</a></li>
                    <li><a href="profile.php" class="nav-link">Profil</a></li>
                    <li><a href="logout.php" class="nav-link">Déconnexion</a></li>
                </ul>
                <div class="hamburger">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </nav>
    </header>

    <main class="upgrade-page">
        <div class="container">
            <div class="upgrade-header">
                <h1><i class="fas fa-arrow-up"></i> Upgrader vers <?php echo $plan['name']; ?></h1>
                <p>Débloquez tous les avantages et obtenez plus de crédits pour vos projets</p>
            </div>

            <div class="upgrade-content">
                <div class="plan-comparison">
                    <!-- Plan actuel -->
                    <div class="plan-card current-plan">
                        <div class="plan-header">
                            <h3>Votre plan actuel</h3>
                            <div class="plan-name"><?php echo ucfirst($user['subscription_plan']); ?></div>
                            <div class="plan-credits"><?php echo $user['credits']; ?> crédits restants</div>
                        </div>
                        <div class="plan-features">
                            <h4>Ce que vous avez:</h4>
                            <ul>
                                <?php if ($user['subscription_plan'] === 'free'): ?>
                                    <li><i class="fas fa-check"></i> 10 crédits de base</li>
                                    <li><i class="fas fa-times"></i> Support limité</li>
                                    <li><i class="fas fa-times"></i> Historique limité</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>

                    <!-- Plan cible -->
                    <div class="plan-card target-plan">
                        <div class="plan-header">
                            <h3>Plan <?php echo $plan['name']; ?></h3>
                            <div class="plan-credits"><?php echo number_format($plan['credits']); ?> crédits/mois</div>
                        </div>
                        <div class="plan-features">
                            <h4>Ce que vous obtiendrez:</h4>
                            <ul>
                                <?php
                                $features = json_decode($plan['features'], true);
                                foreach ($features as $feature):
                                ?>
                                    <li><i class="fas fa-check"></i> <?php echo $feature; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="pricing-options">
                    <h3>Choisissez votre durée d'abonnement</h3>
                    
                    <div class="pricing-cards">
                        <!-- Option mensuelle -->
                        <div class="pricing-card monthly" data-duration="monthly">
                            <div class="pricing-header">
                                <h4>Mensuel</h4>
                                <div class="price">
                                    <span class="amount"><?php echo number_format(calculatePrice($plan['monthly_price'], false, $referrer !== null), 2); ?></span>
                                    <span class="currency">€/mois</span>
                                </div>
                                <?php if ($referrer): ?>
                                    <div class="discount-badge">-10% avec parrainage</div>
                                <?php endif; ?>
                            </div>
                            <div class="pricing-details">
                                <p>Facturation mensuelle</p>
                                <p>Résiliation possible à tout moment</p>
                            </div>
                        </div>

                        <!-- Option annuelle (recommandée) -->
                        <div class="pricing-card yearly recommended" data-duration="yearly">
                            <div class="recommended-badge">Recommandé</div>
                            <div class="pricing-header">
                                <h4>Annuel</h4>
                                <div class="price">
                                    <span class="amount"><?php echo number_format(calculatePrice($plan['yearly_price'], false, $referrer !== null), 2); ?></span>
                                    <span class="currency">€/mois</span>
                                </div>
                                <div class="total-price">
                                    <?php echo number_format(calculatePrice($plan['yearly_price'], true, $referrer !== null), 2); ?>€ facturé annuellement
                                </div>
                                <div class="savings">
                                    Économisez <?php echo number_format(($plan['monthly_price'] * 12) - calculatePrice($plan['yearly_price'], true, $referrer !== null), 2); ?>€ par an !
                                </div>
                            </div>
                            <div class="pricing-details">
                                <p><strong>30% de réduction</strong> sur le prix mensuel</p>
                                <?php if ($referrer): ?>
                                    <p><strong>10% de réduction supplémentaire</strong> avec parrainage</p>
                                <?php endif; ?>
                                <p>Facturation annuelle unique</p>
                            </div>
                        </div>
                    </div>

                    <?php if ($referrer): ?>
                        <div class="referral-info">
                            <div class="referral-card">
                                <i class="fas fa-user-friends"></i>
                                <div class="referral-content">
                                    <h4>Code de parrainage appliqué !</h4>
                                    <p>Vous bénéficiez de <strong>10% de réduction supplémentaire</strong> grâce à <strong><?php echo htmlspecialchars($referrer['username']); ?></strong></p>
                                    <p><small>Votre parrain recevra également 20% de vos crédits en bonus</small></p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="payment-section">
                        <h3>Finaliser votre abonnement</h3>
                        
                        <div class="payment-summary" id="payment-summary">
                            <div class="summary-item">
                                <span>Plan sélectionné:</span>
                                <span id="selected-plan"><?php echo $plan['name']; ?> - Mensuel</span>
                            </div>
                            <div class="summary-item">
                                <span>Crédits par mois:</span>
                                <span><?php echo number_format($plan['credits']); ?> crédits</span>
                            </div>
                            <?php if ($referrer): ?>
                                <div class="summary-item discount">
                                    <span>Réduction parrainage:</span>
                                    <span>-10%</span>
                                </div>
                            <?php endif; ?>
                            <div class="summary-item total">
                                <span>Total:</span>
                                <span id="total-price"><?php echo number_format(calculatePrice($plan['monthly_price'], false, $referrer !== null), 2); ?>€</span>
                            </div>
                        </div>

                        <!-- Boutons PayPal -->
                        <div id="paypal-button-container">
                            <!-- Les boutons PayPal seront insérés ici -->
                        </div>

                        <div class="payment-security">
                            <div class="security-info">
                                <i class="fas fa-shield-alt"></i>
                                <div>
                                    <h4>Paiement 100% sécurisé</h4>
                                    <p>Vos données sont protégées par le cryptage SSL et PayPal</p>
                                </div>
                            </div>
                            <div class="guarantee-info">
                                <i class="fas fa-undo-alt"></i>
                                <div>
                                    <h4>Satisfait ou remboursé</h4>
                                    <p>30 jours pour changer d'avis, remboursement intégral</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="upgrade-benefits">
                    <h3>Pourquoi upgrader maintenant ?</h3>
                    <div class="benefits-grid">
                        <div class="benefit-item">
                            <i class="fas fa-bolt"></i>
                            <h4>Plus de crédits</h4>
                            <p>Jusqu'à 600 crédits par mois pour tous vos projets</p>
                        </div>
                        <div class="benefit-item">
                            <i class="fas fa-headset"></i>
                            <h4>Support prioritaire</h4>
                            <p>Réponse garantie en moins de 24h</p>
                        </div>
                        <div class="benefit-item">
                            <i class="fas fa-chart-line"></i>
                            <h4>Statistiques avancées</h4>
                            <p>Analysez votre utilisation en détail</p>
                        </div>
                        <div class="benefit-item">
                            <i class="fas fa-cloud-download-alt"></i>
                            <h4>Export de données</h4>
                            <p>Téléchargez toutes vos créations</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let selectedDuration = 'monthly';
            let selectedPlan = '<?php echo $plan['slug']; ?>';
            let hasReferral = <?php echo $referrer ? 'true' : 'false'; ?>;
            
            const monthlyPrice = <?php echo $plan['monthly_price']; ?>;
            const yearlyPrice = <?php echo $plan['yearly_price']; ?>;
            
            // Gestion de la sélection de durée
            document.querySelectorAll('.pricing-card').forEach(card => {
                card.addEventListener('click', function() {
                    document.querySelectorAll('.pricing-card').forEach(c => c.classList.remove('selected'));
                    this.classList.add('selected');
                    selectedDuration = this.dataset.duration;
                    updatePaymentSummary();
                    renderPayPalButtons();
                });
            });
            
            // Sélectionner l'option annuelle par défaut
            document.querySelector('.pricing-card.yearly').click();
            
            function updatePaymentSummary() {
                const planName = '<?php echo $plan['name']; ?>';
                const durationText = selectedDuration === 'monthly' ? 'Mensuel' : 'Annuel';
                
                document.getElementById('selected-plan').textContent = `${planName} - ${durationText}`;
                
                let price = selectedDuration === 'monthly' ? monthlyPrice : yearlyPrice;
                
                if (selectedDuration === 'yearly') {
                    price *= 0.7; // 30% de réduction
                }
                
                if (hasReferral) {
                    price *= 0.9; // 10% de réduction supplémentaire
                }
                
                document.getElementById('total-price').textContent = price.toFixed(2) + '€';
                
                if (selectedDuration === 'yearly') {
                    const totalPrice = (price * 12).toFixed(2);
                    document.getElementById('total-price').textContent = `${price.toFixed(2)}€/mois (${totalPrice}€/an)`;
                }
            }
            
            function renderPayPalButtons() {
                // Vider le conteneur
                document.getElementById('paypal-button-container').innerHTML = '';
                
                let price = selectedDuration === 'monthly' ? monthlyPrice : yearlyPrice;
                let billingCycle = selectedDuration === 'monthly' ? 1 : 12;
                
                if (selectedDuration === 'yearly') {
                    price *= 0.7; // 30% de réduction
                }
                
                if (hasReferral) {
                    price *= 0.9; // 10% de réduction supplémentaire
                }
                
                paypal.Buttons({
                    style: {
                        layout: 'vertical',
                        color: 'gold',
                        shape: 'rect',
                        label: 'subscribe'
                    },
                    createSubscription: function(data, actions) {
                        return actions.subscription.create({
                            'plan_id': 'YOUR_PAYPAL_PLAN_ID', // À remplacer par vos vrais IDs de plan PayPal
                            'quantity': '1',
                            'custom_id': `user_${<?php echo $user['id']; ?>}_${selectedPlan}_${selectedDuration}`,
                            'application_context': {
                                'brand_name': 'MultiServices',
                                'locale': 'fr-FR',
                                'shipping_preference': 'NO_SHIPPING',
                                'user_action': 'SUBSCRIBE_NOW'
                            }
                        });
                    },
                    onApprove: function(data, actions) {
                        handleSuccessfulSubscription(data);
                    },
                    onError: function(err) {
                        console.error('PayPal error:', err);
                        showNotification('Erreur lors du paiement', 'error');
                    }
                }).render('#paypal-button-container');
            }
            
            async function handleSuccessfulSubscription(data) {
                try {
                    const response = await fetch('api/process-subscription.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            subscription_id: data.subscriptionID,
                            plan_slug: selectedPlan,
                            duration: selectedDuration,
                            referral_code: '<?php echo $referralCode; ?>',
                            paypal_data: data
                        })
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        showSuccessModal(result);
                    } else {
                        showNotification(result.message || 'Erreur lors de l\'activation', 'error');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showNotification('Erreur lors de l\'activation de l\'abonnement', 'error');
                }
            }
            
            function showSuccessModal(result) {
                const modal = document.createElement('div');
                modal.className = 'success-modal';
                modal.innerHTML = `
                    <div class="modal-content">
                        <div class="success-header">
                            <i class="fas fa-crown"></i>
                            <h2>Abonnement activé !</h2>
                        </div>
                        <div class="success-body">
                            <p>Félicitations ! Votre abonnement <strong>${selectedPlan.toUpperCase()}</strong> est maintenant actif.</p>
                            <p>Vous avez reçu <strong>${result.credits_added}</strong> crédits sur votre compte.</p>
                            <div class="success-actions">
                                <a href="profile.php" class="btn btn-primary">Voir mon profil</a>
                                <a href="index.php#services" class="btn btn-outline">Utiliser mes crédits</a>
                            </div>
                        </div>
                    </div>
                `;
                
                document.body.appendChild(modal);
                modal.style.display = 'flex';
            }
            
            // Initialiser PayPal
            renderPayPalButtons();
        });
    </script>
</body>
</html>