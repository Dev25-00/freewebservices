<div class="overview-cards">
                            <!-- Carte Crédits -->
                            <div class="overview-card credits-card">
                                <div class="card-header">
                                    <h3><i class="fas fa-coins"></i> Crédits</h3>
                                    <div class="credits-balance"><?php echo number_format($user['credits']); ?></div>
                                </div>
                                <div class="card-body">
                                    <div class="usage-bar">
                                        <div class="usage-fill" style="width: <?php echo $usagePercentage; ?>%"></div>
                                    </div>
                                    <p><?php echo $monthlyUsage; ?> / <?php echo $maxCredits; ?> crédits utilisés ce mois</p>
                                    <?php if ($user['credits'] <= 5): ?>
                                        <a href="#subscription" class="btn btn-primary btn-sm" data-tab-link="subscription">Recharger</a>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Carte Abonnement -->
                            <div class="overview-card subscription-card">
                                <div class="card-header">
                                    <h3><i class="fas fa-crown"></i> Abonnement</h3>
                                    <div class="plan-badge <?php echo $user['subscription_plan']; ?>">
                                        <?php echo strtoupper($user['subscription_plan']); ?>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <?php if ($currentSubscription && $user['subscription_plan'] !== 'free'): ?>
                                        <p><strong>Expire le:</strong> <?php echo date('d/m/Y', strtotime($user['subscription_end_date'])); ?></p>
                                        <p><strong>Renouvellement:</strong> <?php echo $currentSubscription['auto_renew'] ? 'Automatique' : 'Manuel'; ?></p>
                                    <?php else: ?>
                                        <p>Plan gratuit avec 10 crédits de base</p>
                                        <a href="#subscription" class="btn btn-primary btn-sm" data-tab-link="subscription">Upgrader</a>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Carte Parrainage -->
                            <div class="overview-card referral-card">
                                <div class="card-header">
                                    <h3><i class="fas fa-share-alt"></i> Parrainage</h3>
                                    <div class="referral-count"><?php echo $user['total_referrals']; ?></div>
                                </div>
                                <div class="card-body">
                                    <p><strong>Crédits gagnés:</strong> <?php echo number_format($user['referral_earnings']); ?></p>
                                    <a href="#referral" class="btn btn-outline btn-sm" data-tab-link="referral">Voir détails</a>
                                </div>
                            </div>

                            <!-- Carte Services -->
                            <div class="overview-card services-card">
                                <div class="card-header">
                                    <h3><i class="fas fa-tools"></i> Services utilisés</h3>
                                    <div class="services-count"><?php echo count($serviceUsage); ?></div>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($serviceUsage)): ?>
                                        <p><strong>Plus utilisé:</strong> <?php echo $serviceUsage[0]['service_name']; ?></p>
                                        <a href="#usage" class="btn btn-outline btn-sm" data-tab-link="usage">Voir détails</a>
                                    <?php else: ?>
                                        <p>Aucun service utilisé pour le moment</p>
                                        <a href="index.php#services" class="btn btn-primary btn-sm">Découvrir</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Activité récente -->
                        <div class="recent-activity">
                            <h3><i class="fas fa-history"></i> Activité récente</h3>
                            <div class="activity-list">
                                <?php if (!empty($recentTransactions)): ?>
                                    <?php foreach (array_slice($recentTransactions, 0, 5) as $transaction): ?>
                                        <div class="activity-item">
                                            <div class="activity-icon <?php echo $transaction['type']; ?>">
                                                <i class="fas fa-<?php echo getTransactionIcon($transaction['type']); ?>"></i>
                                            </div>
                                            <div class="activity-content">
                                                <div class="activity-title"><?php echo htmlspecialchars($transaction['description']); ?></div>
                                                <div class="activity-date"><?php echo date('d/m/Y H:i', strtotime($transaction['created_at'])); ?></div>
                                            </div>
                                            <div class="activity-amount <?php echo $transaction['amount'] > 0 ? 'positive' : 'negative'; ?>">
                                                <?php echo $transaction['amount'] > 0 ? '+' : ''; ?><?php echo $transaction['amount']; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="empty-state">
                                        <i class="fas fa-inbox"></i>
                                        <p>Aucune activité récente</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Onglet Abonnement -->
                    <div class="tab-content" id="subscription">
                        <h2><i class="fas fa-crown"></i> Gérer mon abonnement</h2>
                        
                        <div class="current-plan-info">
                            <h3>Plan actuel: <span class="plan-name <?php echo $user['subscription_plan']; ?>"><?php echo strtoupper($user['subscription_plan']); ?></span></h3>
                            <?php if ($currentSubscription): ?>
                                <div class="plan-details">
                                    <p><strong>Expire le:</strong> <?php echo date('d/m/Y', strtotime($user['subscription_end_date'])); ?></p>
                                    <p><strong>Crédits mensuels:</strong> <?php echo number_format($maxCredits); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="subscription-plans">
                            <div class="plan-card free <?php echo $user['subscription_plan'] === 'free' ? 'current' : ''; ?>">
                                <div class="plan-header">
                                    <h3>Free</h3>
                                    <div class="plan-price">0 €<span>/mois</span></div>
                                </div>
                                <div class="plan-features">
                                    <ul>
                                        <li><i class="fas fa-check"></i> 10 crédits à l'inscription</li>
                                        <li><i class="fas fa-check"></i> Services de base</li>
                                        <li><i class="fas fa-check"></i> Support communautaire</li>
                                    </ul>
                                </div>
                                <?php if ($user['subscription_plan'] === 'free'): ?>
                                    <div class="plan-current">Plan actuel</div>
                                <?php else: ?>
                                    <button class="btn btn-outline" onclick="downgradeToPlan('free')">Rétrograder</button>
                                <?php endif; ?>
                            </div>

                            <div class="plan-card pro <?php echo $user['subscription_plan'] === 'pro' ? 'current' : ''; ?>">
                                <div class="plan-header">
                                    <h3>Pro</h3>
                                    <div class="plan-price">5 €<span>/mois</span></div>
                                    <div class="plan-price-yearly">3.50 €/mois (facturation annuelle)</div>
                                </div>
                                <div class="plan-features">
                                    <ul>
                                        <li><i class="fas fa-check"></i> 200 crédits par mois</li>
                                        <li><i class="fas fa-check"></i> Tous les services</li>
                                        <li><i class="fas fa-check"></i> Support prioritaire</li>
                                        <li><i class="fas fa-check"></i> Historique étendu</li>
                                        <li><i class="fas fa-check"></i> Export de données</li>
                                    </ul>
                                </div>
                                <?php if ($user['subscription_plan'] === 'pro'): ?>
                                    <div class="plan-current">Plan actuel</div>
                                <?php else: ?>
                                    <button class="btn btn-primary" onclick="upgradeToPlan('pro')">Choisir Pro</button>
                                <?php endif; ?>
                            </div>

                            <div class="plan-card max <?php echo $user['subscription_plan'] === 'max' ? 'current' : ''; ?>">
                                <div class="plan-header">
                                    <h3>Max</h3>
                                    <div class="plan-price">12.50 €<span>/mois</span></div>
                                    <div class="plan-price-yearly">8.75 €/mois (facturation annuelle)</div>
                                </div>
                                <div class="plan-features">
                                    <ul>
                                        <li><i class="fas fa-check"></i> 600 crédits par mois</li>
                                        <li><i class="fas fa-check"></i> Services premium</li>
                                        <li><i class="fas fa-check"></i> Support 24/7</li>
                                        <li><i class="fas fa-check"></i> API access</li>
                                        <li><i class="fas fa-check"></i> Traitement prioritaire</li>
                                        <li><i class="fas fa-check"></i> Stockage illimité</li>
                                    </ul>
                                </div>
                                <?php if ($user['subscription_plan'] === 'max'): ?>
                                    <div class="plan-current">Plan actuel</div>
                                <?php else: ?>
                                    <button class="btn btn-primary" onclick="upgradeToPlan('max')">Choisir Max</button>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="subscription-info">
                            <h4>💡 Économies sur l'abonnement annuel</h4>
                            <p>• <strong>30% de réduction</strong> sur la facturation annuelle</p>
                            <p>• <strong>10% de réduction supplémentaire</strong> avec un code de parrainage</p>
                            <p>• <strong>Jusqu'à 40% d'économies</strong> au total !</p>
                        </div>
                    </div>

                    <!-- Onglet Crédits -->
                    <div class="tab-content" id="credits">
                        <h2><i class="fas fa-coins"></i> Gestion des crédits</h2>
                        
                        <div class="credits-overview">
                            <div class="credits-balance-large">
                                <div class="balance-amount"><?php echo number_format($user['credits']); ?></div>
                                <div class="balance-label">Crédits disponibles</div>
                            </div>
                            
                            <div class="credits-stats">
                                <div class="stat-item">
                                    <div class="stat-value"><?php echo number_format($user['total_credits_used']); ?></div>
                                    <div class="stat-label">Total utilisés</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-value"><?php echo number_format($user['referral_earnings']); ?></div>
                                    <div class="stat-label">Via parrainage</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-value"><?php echo $monthlyUsage; ?></div>
                                    <div class="stat-label">Ce mois</div>
                                </div>
                            </div>
                        </div>

                        <div class="credits-actions">
                            <?php if ($user['credits'] <= 10): ?>
                                <div class="low-credits-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <div>
                                        <h4>Crédits faibles</h4>
                                        <p>Il ne vous reste que <?php echo $user['credits']; ?> crédits. Rechargez pour continuer à utiliser nos services.</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <div class="action-buttons">
                                <button class="btn btn-primary" onclick="openUpgradeModal()">
                                    <i class="fas fa-arrow-up"></i> Upgrader le plan
                                </button>
                                <button class="btn btn-outline" onclick="watchAdForCredits()">
                                    <i class="fas fa-play-circle"></i> Regarder une pub (+1 crédit)
                                </button>
                                <button class="btn btn-outline" onclick="shareReferralLink()">
                                    <i class="fas fa-share"></i> Partager et gagner
                                </button>
                            </div>
                        </div>

                        <div class="credits-history">
                            <h3><i class="fas fa-history"></i> Historique des transactions</h3>
                            <div class="transactions-list">
                                <?php if (!empty($recentTransactions)): ?>
                                    <?php foreach ($recentTransactions as $transaction): ?>
                                        <div class="transaction-item">
                                            <div class="transaction-icon <?php echo $transaction['type']; ?>">
                                                <i class="fas fa-<?php echo getTransactionIcon($transaction['type']); ?>"></i>
                                            </div>
                                            <div class="transaction-content">
                                                <div class="transaction-desc"><?php echo htmlspecialchars($transaction['description']); ?></div>
                                                <div class="transaction-date"><?php echo date('d/m/Y H:i', strtotime($transaction['created_at'])); ?></div>
                                            </div>
                                            <div class="transaction-amount <?php echo $transaction['amount'] > 0 ? 'positive' : 'negative'; ?>">
                                                <?php echo $transaction['amount'] > 0 ? '+' : ''; ?><?php echo $transaction['amount']; ?> crédits
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="empty-state">
                                        <i class="fas fa-receipt"></i>
                                        <p>Aucune transaction pour le moment</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Onglet Parrainage -->
                    <div class="tab-content" id="referral">
                        <h2><i class="fas fa-share-alt"></i> Programme de parrainage</h2>
                        
                        <div class="referral-overview">
                            <div class="referral-card">
                                <h3>Votre code de parrainage</h3>
                                <div class="referral-code-display">
                                    <input type="text" value="<?php echo htmlspecialchars($user['referral_code']); ?>" readonly id="referral-code-input">
                                    <button class="btn btn-primary" onclick="copyReferralCode()">
                                        <i class="fas fa-copy"></i> Copier
                                    </button>
                                </div>
                                <div class="referral-link-display">
                                    <strong>Lien de parrainage:</strong>
                                    <input type="text" value="<?php echo 'https://' . $_SERVER['HTTP_HOST'] . '/register.php?ref=' . urlencode($user['referral_code']); ?>" readonly id="referral-link-input">
                                    <button class="btn btn-outline" onclick="copyReferralLink()">
                                        <i class="fas fa-link"></i> Copier le lien
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="referral-stats">
                            <div class="stat-card">
                                <div class="stat-number"><?php echo $user['total_referrals']; ?></div>
                                <div class="stat-label">Parrainages total</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-number"><?php echo number_format($user['referral_earnings']); ?></div>
                                <div class="stat-label">Crédits gagnés</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-number"><?php echo number_format($user['referral_earnings'] * 0.05, 2); ?> €</div>
                                <div class="stat-label">Valeur estimée</div>
                            </div>
                        </div>

                        <div class="referral-info">
                            <h3>💰 Comment ça marche ?</h3>
                            <div class="info-steps">
                                <div class="step">
                                    <div class="step-number">1</div>
                                    <div class="step-content">
                                        <h4>Partagez votre lien</h4>
                                        <p>Envoyez votre lien de parrainage à vos amis et contacts</p>
                                    </div>
                                </div>
                                <div class="step">
                                    <div class="step-number">2</div>
                                    <div class="step-content">
                                        <h4>Ils s'abonnent</h4>
                                        <p>Quand ils souscrivent à un plan Pro ou Max, vous êtes récompensé</p>
                                    </div>
                                </div>
                                <div class="step">
                                    <div class="step-number">3</div>
                                    <div class="step-content">
                                        <h4>Vous gagnez 20%</h4>
                                        <p>Recevez 20% des crédits de leur abonnement directement</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="referral-examples">
                                <h4>Exemples de gains:</h4>
                                <ul>
                                    <li><strong>Plan Pro (200 crédits):</strong> Vous gagnez 40 crédits</li>
                                    <li><strong>Plan Max (600 crédits):</strong> Vous gagnez 120 crédits</li>
                                    <li><strong>Abonnement annuel:</strong> Bonus immédiat sur 12 mois !</li>
                                </ul>
                            </div>
                        </div>

                        <div class="share-buttons">
                            <h4>Partager maintenant:</h4>
                            <div class="social-share">
                                <button class="btn btn-social facebook" onclick="shareOnFacebook()">
                                    <i class="fab fa-facebook-f"></i> Facebook
                                </button>
                                <button class="btn btn-social twitter" onclick="shareOnTwitter()">
                                    <i class="fab fa-twitter"></i> Twitter
                                </button>
                                <button class="btn btn-social whatsapp" onclick="shareOnWhatsApp()">
                                    <i class="fab fa-whatsapp"></i> WhatsApp
                                </button>
                                <button class="btn btn-social email" onclick="shareByEmail()">
                                    <i class="fas fa-envelope"></i> Email
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Onglet Utilisation -->
                    <div class="tab-content" id="usage">
                        <h2><i class="fas fa-chart-bar"></i> Statistiques d'utilisation</h2>
                        
                        <div class="usage-summary">
                            <div class="usage-chart">
                                <canvas id="usageChart" width="400" height="200"></canvas>
                            </div>
                            
                            <div class="usage-details">
                                <h3>Services les plus utilisés</h3>
                                <?php if (!empty($serviceUsage)): ?>
                                    <div class="service-list">
                                        <?php foreach ($serviceUsage as $service): ?>
                                            <div class="service-item">
                                                <div class="service-info">
                                                    <div class="service-name"><?php echo ucfirst(str_replace('_', ' ', $service['service_name'])); ?></div>
                                                    <div class="service-stats"><?php echo $service['usage_count']; ?> utilisations</div>
                                                </div>
                                                <div class="service-credits"><?php echo $service['total_credits']; ?> crédits</div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="empty-state">
                                        <i class="fas fa-chart-bar"></i>
                                        <p>Aucune donnée d'utilisation disponible</p>
                                        <a href="index.php#services" class="btn btn-primary">Découvrir nos services</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Onglet Paramètres -->
                    <div class="tab-content" id="settings">
                        <h2><i class="fas fa-cog"></i> Paramètres du compte</h2>
                        
                        <?php if ($error): ?>
                            <div class="error-message"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="success-message"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" class="settings-form">
                            <div class="form-section">
                                <h3>Informations personnelles</h3>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="first_name">Prénom</label>
                                        <input type="text" id="first_name" name="first_name" class="form-control" 
                                               value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="last_name">Nom</label>
                                        <input type="text" id="last_name" name="last_name" class="form-control" 
                                               value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>">
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="email">Email</label>
                                        <input type="email" id="email" class="form-control" 
                                               value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                                        <small class="form-hint">Contactez le support pour modifier votre email</small>
                                    </div>
                                    <div class="form-group">
                                        <label for="phone">Téléphone</label>
                                        <input type="tel" id="phone" name="phone" class="form-control" 
                                               value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="country">Pays</label>
                                    <input type="text" id="country" name="country" class="form-control" 
                                           value="<?php echo htmlspecialchars($user['country'] ?? ''); ?>">
                                </div>
                            </div>
                            
                            <div class="form-section">
                                <h3>Préférences de notification</h3>
                                <div class="checkbox-group">
                                    <label class="checkbox-label">
                                        <input type="checkbox" checked>
                                        <span class="checkbox-custom"></span>
                                        Notifications de crédits faibles
                                    </label>
                                    <label class="checkbox-label">
                                        <input type="checkbox" checked>
                                        <span class="checkbox-custom"></span>
                                        Notifications d'expiration d'abonnement
                                    </label>
                                    <label class="checkbox-label">
                                        <input type="checkbox" checked>
                                        <span class="checkbox-custom"></span>
                                        Notifications de parrainage
                                    </label>
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" name="update_profile" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Sauvegarder les modifications
                                </button>
                                <a href="#" class="btn btn-outline" onclick="resetForm()">
                                    <i class="fas fa-undo"></i> Annuler
                                </a>
                            </div>
                        </form>
                        
                        <div class="danger-zone">
                            <h3>Zone de danger</h3>
                            <div class="danger-actions">
                                <button class="btn btn-danger" onclick="confirmDeleteAccount()">
                                    <i class="fas fa-trash-alt"></i> Supprimer mon compte
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal pour regarder une pub -->
    <div class="modal" id="ad-modal" style="display: none;">
        <div class="modal-content ad-modal-content">
            <div class="modal-header">
                <h3>Regarder une publicité</h3>
                <span class="modal-close" onclick="closeAdModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="ad-container">
                    <div class="ad-placeholder">
                        <i class="fas fa-play-circle"></i>
                        <h4>Publicité (30 secondes)</h4>
                        <p>Regardez cette publicité pour gagner 1 crédit gratuit</p>
                        <button class="btn btn-primary" onclick="startAd()">Lancer la publicité</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="js/main.js"></script>
    <script>
        // JavaScript pour la gestion du profil
        document.addEventListener('DOMContentLoaded', function() {
            initProfileTabs();
        });

        function initProfileTabs() {
            const menuItems = document.querySelectorAll('.menu-item');
            const tabContents = document.querySelectorAll('.tab-content');
            
            menuItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetTab = this.dataset.tab;
                    
                    // Retirer les classes actives
                    menuItems.forEach(mi => mi.classList.remove('active'));
                    tabContents.forEach(tc => tc.classList.remove('active'));
                    
                    // Ajouter les classes actives
                    this.classList.add('active');
                    document.getElementById(targetTab).classList.add('active');
                });
            });
            
            // Gestion des liens vers d'autres onglets
            document.querySelectorAll('[data-tab-link]').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetTab = this.dataset.tabLink;
                    const menuItem = document.querySelector(`[data-tab="${targetTab}"]`);
                    if (menuItem) {
                        menuItem.click();
                    }
                });
            });
        }

        function copyReferralCode() {
            const input = document.getElementById('referral-code-input');
            input.select();
            document.execCommand('copy');
            showNotification('Code de parrainage copié !', 'success');
        }

        function copyReferralLink() {
            const input = document.getElementById('referral-link-input');
            input.select();
            document.execCommand('copy');
            showNotification('Lien de parrainage copié !', 'success');
        }

        function watchAdForCredits() {
            document.getElementById('ad-modal').style.display = 'flex';
        }

        function closeAdModal() {
            document.getElementById('ad-modal').style.display = 'none';
        }

        function startAd() {
            // Simulation d'une pub
            const adContainer = document.querySelector('.ad-container');
            adContainer.innerHTML = `
                <div class="ad-video">
                    <div class="ad-progress">
                        <div class="ad-progress-bar"></div>
                    </div>
                    <h4>Publicité en cours...</h4>
                    <p id="ad-timer">30 secondes restantes</p>
                </div>
            `;
            
            let timeLeft = 30;
            const timer = setInterval(() => {
                timeLeft--;
                document.getElementById('ad-timer').textContent = timeLeft + ' secondes restantes';
                
                if (timeLeft <= 0) {
                    clearInterval(timer);
                    rewardUserForAd();
                }
            }, 1000);
        }

        async function rewardUserForAd() {
            try {
                const response = await fetch('api/reward-ad.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        ad_type: 'credit_reward',
                        credits_earned: 1
                    })
                });

                const result = await response.json();
                
                if (result.success) {
                    const adContainer = document.querySelector('.ad-container');
                    adContainer.innerHTML = `
                        <div class="ad-success">
                            <i class="fas fa-check-circle"></i>
                            <h4>Félicitations !</h4>
                            <p>Vous avez gagné 1 crédit</p>
                            <button class="btn btn-primary" onclick="closeAdModal()">Continuer</button>
                        </div>
                    `;
                    
                    // Mettre à jour l'affichage des crédits
                    updateCreditsDisplay(result.new_balance);
                    showNotification('1 crédit ajouté à votre compte !', 'success');
                } else {
                    showNotification('Erreur lors de l\'ajout du crédit', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('Erreur lors de l\'ajout du crédit', 'error');
            }
        }

        function updateCreditsDisplay(newBalance) {
            const creditsDisplays = document.querySelectorAll('.credits-balance, .balance-amount');
            creditsDisplays.forEach(display => {
                display.textContent = new Intl.NumberFormat().format(newBalance);
            });
        }

        function upgradeToPlan(plan) {
            window.location.href = `upgrade.php?plan=${plan}`;
        }

        function downgradeToPlan(plan) {
            if (confirm('Êtes-vous sûr de vouloir rétrograder votre plan ?')) {
                window.location.href = `downgrade.php?plan=${plan}`;
            }
        }

        function shareOnFacebook() {
            const url = encodeURIComponent(document.getElementById('referral-link-input').value);
            const text = encodeURIComponent('Découvrez MultiServices, une plateforme incroyable avec des outils gratuits !');
            window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}&quote=${text}`, '_blank');
        }

        function shareOnTwitter() {
            const url = encodeURIComponent(document.getElementById('referral-link-input').value);
            const text = encodeURIComponent('Découvrez MultiServices avec mon lien de parrainage ! 🚀');
            window.open(`https://twitter.com/intent/tweet?text=${text}&url=${url}`, '_blank');
        }

        function shareOnWhatsApp() {
            const url = document.getElementById('referral-link-input').value;
            const text = encodeURIComponent(`Salut ! Je te recommande MultiServices, une super plateforme avec plein d'outils gratuits. Utilise mon lien : ${url}`);
            window.open(`https://wa.me/?text=${text}`, '_blank');
        }

        function shareByEmail() {
            const url = document.getElementById('referral-link-input').value;
            const subject = encodeURIComponent('Je te recommande MultiServices !');
            const body = encodeURIComponent(`Salut !

J'ai découvert MultiServices, une plateforme qui propose plein d'outils gratuits super utiles : convertisseur de fichiers, générateur de QR codes, suppresseur de fond, et bien plus !

Tu peux essayer gratuitement avec 10 crédits à l'inscription en utilisant mon lien de parrainage :
${url}

À bientôt !`);
            window.location.href = `mailto:?subject=${subject}&body=${body}`;
        }

        function resetForm() {
            document.querySelector('.settings-form').reset();
        }

        function confirmDeleteAccount() {
            if (confirm('⚠️ ATTENTION : Cette action est irréversible !\n\nÊtes-vous absolument certain de vouloir supprimer votre compte ?\n\nToutes vos données seront perdues définitivement.')) {
                if (confirm('Dernière confirmation : Supprimer définitivement mon compte MultiServices ?')) {
                    window.location.href = 'delete-account.php';
                }
            }
        }
    </script>
</body>
</html>

<?php
// Fonctions helper pour l'affichage
function getTransactionIcon($type) {
    $icons = [
        'earned' => 'plus-circle',
        'spent' => 'minus-circle', 
        'bonus' => 'gift',
        'referral' => 'share-alt',
        'purchase' => 'shopping-cart',
        'ad_reward' => 'play-circle'
    ];
    return $icons[$type] ?? 'circle';
}
?>
                <?php
// profile.php
require_once 'config.php';

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$user = getCurrentUser();
$error = '';
$success = '';

// Traitement de la mise à jour du profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $firstName = sanitize($_POST['first_name']);
    $lastName = sanitize($_POST['last_name']);
    $phone = sanitize($_POST['phone']);
    $country = sanitize($_POST['country']);
    
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE users SET first_name = ?, last_name = ?, phone = ?, country = ? WHERE id = ?");
        if ($stmt->execute([$firstName, $lastName, $phone, $country, $user['id']])) {
            $success = 'Profil mis à jour avec succès !';
            $user = getCurrentUser(); // Recharger les données
        } else {
            $error = 'Erreur lors de la mise à jour du profil.';
        }
    } catch (Exception $e) {
        $error = 'Erreur lors de la mise à jour du profil.';
    }
}

// Récupérer les statistiques utilisateur
try {
    $db = Database::getInstance()->getConnection();
    
    // Statistiques de crédits
    $stmt = $db->prepare("
        SELECT 
            (SELECT COUNT(*) FROM credit_transactions WHERE user_id = ? AND type = 'spent') as total_spent,
            (SELECT COUNT(*) FROM credit_transactions WHERE user_id = ? AND type = 'earned') as total_earned,
            (SELECT COUNT(*) FROM credit_transactions WHERE user_id = ? AND type = 'referral') as referral_earned
    ");
    $stmt->execute([$user['id'], $user['id'], $user['id']]);
    $creditStats = $stmt->fetch();
    
    // Services utilisés
    $stmt = $db->prepare("
        SELECT service_name, COUNT(*) as usage_count, SUM(credits_cost) as total_credits
        FROM service_usage 
        WHERE user_id = ? 
        GROUP BY service_name 
        ORDER BY usage_count DESC
    ");
    $stmt->execute([$user['id']]);
    $serviceUsage = $stmt->fetchAll();
    
    // Historique des transactions récentes
    $stmt = $db->prepare("
        SELECT type, amount, description, created_at 
        FROM credit_transactions 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 10
    ");
    $stmt->execute([$user['id']]);
    $recentTransactions = $stmt->fetchAll();
    
    // Informations sur l'abonnement actuel
    $stmt = $db->prepare("
        SELECT s.*, sp.name as plan_name, sp.features
        FROM subscriptions s
        JOIN subscription_plans sp ON s.plan_id = sp.id
        WHERE s.user_id = ? AND s.status = 'active'
        ORDER BY s.created_at DESC
        LIMIT 1
    ");
    $stmt->execute([$user['id']]);
    $currentSubscription = $stmt->fetch();
    
    // Statistiques de parrainage
    $stmt = $db->prepare("
        SELECT COUNT(*) as total_referrals,
               SUM(CASE WHEN r.status = 'completed' THEN r.reward_credits ELSE 0 END) as total_rewards
        FROM referrals r
        WHERE r.referrer_id = ?
    ");
    $stmt->execute([$user['id']]);
    $referralStats = $stmt->fetch();
    
} catch (Exception $e) {
    $creditStats = ['total_spent' => 0, 'total_earned' => 0, 'referral_earned' => 0];
    $serviceUsage = [];
    $recentTransactions = [];
    $currentSubscription = null;
    $referralStats = ['total_referrals' => 0, 'total_rewards' => 0];
}

// Calculer le pourcentage d'utilisation des crédits ce mois-ci
$currentMonth = date('Y-m');
try {
    $stmt = $db->prepare("
        SELECT SUM(amount) as credits_used_this_month
        FROM credit_transactions 
        WHERE user_id = ? AND type = 'spent' AND DATE_FORMAT(created_at, '%Y-%m') = ?
    ");
    $stmt->execute([$user['id'], $currentMonth]);
    $monthlyUsage = $stmt->fetch()['credits_used_this_month'] ?? 0;
} catch (Exception $e) {
    $monthlyUsage = 0;
}

// Déterminer les crédits disponibles selon le plan
$planCredits = [
    'free' => 10,
    'pro' => 200,
    'max' => 600
];
$maxCredits = $planCredits[$user['subscription_plan']] ?? 10;
$usagePercentage = $maxCredits > 0 ? min(100, ($monthlyUsage / $maxCredits) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - MultiServices</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
                    <li><a href="index.php#services" class="nav-link">Services</a></li>
                    <li><a href="guestbook.php" class="nav-link">Livre d'or</a></li>
                    <li><a href="donation.php" class="nav-link">Donation</a></li>
                    <li><a href="profile.php" class="nav-link active">Profil</a></li>
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

    <main class="profile-page">
        <div class="container">
            <div class="profile-header">
                <div class="profile-avatar">
                    <img src="<?php echo $user['avatar'] ?: 'https://ui-avatars.com/api/?name=' . urlencode($user['username']) . '&background=667eea&color=fff&size=120'; ?>" 
                         alt="Avatar de <?php echo htmlspecialchars($user['username']); ?>">
                    <div class="avatar-badge <?php echo $user['subscription_plan']; ?>">
                        <?php echo strtoupper($user['subscription_plan']); ?>
                    </div>
                </div>
                <div class="profile-info">
                    <h1>Bonjour, <?php echo htmlspecialchars($user['username']); ?> !</h1>
                    <p class="profile-subtitle">
                        Membre depuis le <?php echo date('d/m/Y', strtotime($user['created_at'])); ?>
                    </p>
                    <div class="profile-stats-quick">
                        <div class="stat-item">
                            <div class="stat-value"><?php echo number_format($user['credits']); ?></div>
                            <div class="stat-label">Crédits</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?php echo $user['total_referrals']; ?></div>
                            <div class="stat-label">Parrainages</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?php echo number_format($user['total_credits_used']); ?></div>
                            <div class="stat-label">Crédits utilisés</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="profile-content">
                <!-- Sidebar avec navigation -->
                <div class="profile-sidebar">
                    <div class="sidebar-menu">
                        <a href="#overview" class="menu-item active" data-tab="overview">
                            <i class="fas fa-chart-pie"></i> Vue d'ensemble
                        </a>
                        <a href="#subscription" class="menu-item" data-tab="subscription">
                            <i class="fas fa-crown"></i> Abonnement
                        </a>
                        <a href="#credits" class="menu-item" data-tab="credits">
                            <i class="fas fa-coins"></i> Crédits
                        </a>
                        <a href="#referral" class="menu-item" data-tab="referral">
                            <i class="fas fa-share-alt"></i> Parrainage
                        </a>
                        <a href="#usage" class="menu-item" data-tab="usage">
                            <i class="fas fa-chart-bar"></i> Utilisation
                        </a>
                        <a href="#settings" class="menu-item" data-tab="settings">
                            <i class="fas fa-cog"></i> Paramètres
                        </a>
                    </div>
                </div>

                <!-- Contenu principal -->
                <div class="profile-main">
                    <!-- Onglet Vue d'ensemble -->
                    <div class="tab-content active" id="overview">
                        <h2><i class="fas fa-chart-pie"></i> Vue d'ensemble</h2>
                        
                        <div class="overview-cards">
                            <!--