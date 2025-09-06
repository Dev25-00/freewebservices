<?php
// Inclusion du header centralisé

// includes/service-template.php - Template unifié pour les pages de services

/**
 * Configuration requise pour chaque service :
 * 
 * $service_config = [
 *     'service_id' => 'qr-generator',
 *     'service_name' => 'Générateur QR Code',
 *     'service_description' => 'Créez des codes QR personnalisés...',
 *     'service_icon' => 'fas fa-qrcode',
 *     'service_color' => '#2563eb',
 *     'features' => [
 *         'feature1' => 'Description de la fonctionnalité 1',
 *         'feature2' => 'Description de la fonctionnalité 2'
 *     ],
 *     'form_content' => 'chemin/vers/formulaire.php', // Chemin vers le contenu du formulaire
 *     'result_area' => true, // Affichage de la zone de résultat
 *     'upload_area' => false, // Zone d'upload de fichiers
 *     'additional_css' => [], // CSS spécifiques
 *     'additional_js' => [] // JavaScript spécifiques
 * ];
 */

// Vérification de la configuration du service
if (!isset($service_config) || !is_array($service_config)) {
    die('Configuration du service manquante');
}

// Configuration de la page pour le header
$page_title = $service_config['service_name'] . ' - Mini Services';
$page_description = $service_config['service_description'];
$page_keywords = $service_config['keywords'] ?? '';
$body_class = 'service-page service-' . $service_config['service_id'];
if (isset($service_config['body_class_suffix'])) {
    $body_class .= ' service-' . $service_config['body_class_suffix'];
}

// CSS et JS additionnels
$additional_css = $service_config['additional_css'] ?? [];
$additional_js = $service_config['additional_js'] ?? [];

// Configuration pour le tracking
$page_tracking = [
    'service' => $service_config['service_id'],
    'action' => 'view',
    'data' => []
];

require_once __DIR__ . '/header-bootstrap.php';
?>

<!-- Template de Service -->
<div class="service-template">
     <div class="container">
         
         <!-- En-tête du service -->
         <section class="service-header">
             <div class="service-header-content">
                 <div class="service-icon-large" style="background: linear-gradient(135deg, <?php echo $service_config['service_color']; ?>, <?php echo $service_config['service_color']; ?>aa);">
                     <i class="<?php echo $service_config['service_icon']; ?>"></i>
                 </div>
                 
                 <div class="service-header-text">
                     <h1 class="service-title"><?php echo htmlspecialchars($service_config['service_name']); ?></h1>
                     <p class="service-description"><?php echo htmlspecialchars($service_config['service_description']); ?></p>
                     
                     <?php if (isset($service_config['features']) && !empty($service_config['features'])): ?>
                         <div class="service-features">
                             <?php foreach ($service_config['features'] as $feature => $description): ?>
                                 <div class="feature-tag" title="<?php echo htmlspecialchars($description); ?>">
                                     <?php echo htmlspecialchars($feature); ?>
                                 </div>
                             <?php endforeach; ?>
                         </div>
                     <?php endif; ?>
                 </div>
             </div>
             
             <!-- Indicateurs de service -->
             <div class="service-indicators">
                 <div class="indicator">
                     <i class="fas fa-shield-alt"></i>
                     <span>Sécurisé</span>
                 </div>
                 <div class="indicator">
                     <i class="fas fa-bolt"></i>
                     <span>Rapide</span>
                 </div>
                 <div class="indicator">
                     <i class="fas fa-heart"></i>
                     <span>Gratuit</span>
                 </div>
                 <div class="indicator">
                     <i class="fas fa-mobile-alt"></i>
                     <span>Responsive</span>
                 </div>
             </div>
         </section>
 
         <!-- Zone principale du service -->
         <section class="service-main">
             <div class="service-workspace">
                 
                 <!-- Zone d'upload de fichiers (si activée) -->
                 <?php if (isset($service_config['upload_area']) && $service_config['upload_area']): ?>
                     <div class="upload-section">
                         <div class="upload-zone" id="uploadZone">
                             <div class="upload-content">
                                 <i class="fas fa-cloud-upload-alt"></i>
                                 <h3>Glissez vos fichiers ici</h3>
                                 <p>ou cliquez pour sélectionner</p>
                                 <input type="file" id="fileInput" multiple accept="<?php echo $service_config['file_accept'] ?? '*'; ?>">
                             </div>
                             <div class="upload-progress" id="uploadProgress" style="display: none;">
                                 <div class="progress-bar">
                                     <div class="progress-fill" id="progressFill"></div>
                                 </div>
                                 <span class="progress-text" id="progressText">0%</span>
                             </div>
                         </div>
                         
                         <!-- Informations sur les fichiers acceptés -->
                         <?php if (isset($service_config['file_info'])): ?>
                             <div class="file-info">
                                 <h4>Formats acceptés :</h4>
                                 <p><?php echo htmlspecialchars($service_config['file_info']); ?></p>
                                 <p><strong>Taille maximale :</strong> <?php echo $service_config['max_file_size'] ?? '50 MB'; ?></p>
                             </div>
                         <?php endif; ?>
                     </div>
                 <?php endif; ?>
 
                 <!-- Formulaire principal du service -->
                 <div class="service-form-section">
                     <form id="serviceForm" class="service-form" data-validate="true">
                         <input type="hidden" name="service_type" value="<?php echo $service_config['service_id']; ?>">
                         <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                         
                         <!-- Inclusion du contenu spécifique du service -->
                         <?php
                         // Modification de l'inclusion du formulaire avec vérification
                         $form_path = $service_config['form_content'];
                         if (!file_exists($form_path)) {
                             die("Erreur : Le formulaire {$form_path} n'existe pas");
                         }
 
                         // Inclusion du formulaire
                         include $form_path;
                         ?>
                         
                         <!-- Boutons d'action -->
                         <div class="form-actions">
                             <button type="submit" class="btn btn-primary btn-large" id="submitBtn">
                                 <i class="<?php echo $service_config['service_icon']; ?>"></i>
                                 <?php echo $service_config['submit_text'] ?? 'Générer'; ?>
                             </button>
                             
                             <button type="reset" class="btn btn-outline" id="resetBtn">
                                 <i class="fas fa-redo"></i>
                                 Réinitialiser
                             </button>
                         </div>
                     </form>
                 </div>
 
                 <!-- Zone de résultat -->
                 <?php if (isset($service_config['result_area']) && $service_config['result_area']): ?>
                     <div class="result-section" id="resultSection" style="display: none;">
                         <div class="result-header">
                             <h3>Résultat</h3>
                             <div class="result-actions">
                                 <button class="btn btn-outline btn-small" id="downloadBtn" style="display: none;">
                                     <i class="fas fa-download"></i>
                                     Télécharger
                                 </button>
                                 <button class="btn btn-outline btn-small" id="copyBtn" style="display: none;">
                                     <i class="fas fa-copy"></i>
                                     Copier
                                 </button>
                                 <button class="btn btn-outline btn-small" id="shareBtn" style="display: none;">
                                     <i class="fas fa-share"></i>
                                     Partager
                                 </button>
                             </div>
                         </div>
                         
                         <div class="result-content" id="resultContent">
                             <!-- Le contenu du résultat sera inséré ici -->
                         </div>
                         
                         <div class="result-info" id="resultInfo" style="display: none;">
                             <!-- Informations sur le résultat -->
                         </div>
                     </div>
                 <?php endif; ?>
 
             </div>
 
             <!-- Sidebar avec informations et aide -->
             <aside class="service-sidebar">
                 
                 <!-- Guide d'utilisation -->
                 <div class="sidebar-section">
                     <h3><i class="fas fa-question-circle"></i> Comment utiliser</h3>
                     <div class="usage-steps">
                         <?php if (isset($service_config['usage_steps'])): ?>
                             <?php foreach ($service_config['usage_steps'] as $step => $description): ?>
                                 <div class="usage-step">
                                     <div class="step-number"><?php echo $step; ?></div>
                                     <div class="step-description"><?php echo htmlspecialchars($description); ?></div>
                                 </div>
                             <?php endforeach; ?>
                         <?php endif; ?>
                     </div>
                 </div>
 
                 <!-- Statistiques d'utilisation -->
                 <div class="sidebar-section">
                     <h3><i class="fas fa-chart-line"></i> Statistiques</h3>
                     <div class="service-stats">
                         <div class="stat-item">
                             <span class="stat-value" id="usageCount">-</span>
                             <span class="stat-label">Utilisations aujourd'hui</span>
                         </div>
                         <div class="stat-item">
                             <span class="stat-value" id="totalUsage">-</span>
                             <span class="stat-label">Total utilisations</span>
                         </div>
                     </div>
                 </div>
 
                 <!-- Services connexes -->
                 <div class="sidebar-section">
                     <h3><i class="fas fa-link"></i> Services connexes</h3>
                     <div class="related-services">
                         <?php
                         $all_services = [
                             'qr-generator' => ['name' => 'QR Code', 'icon' => 'fas fa-qrcode'],
                             'hashtag-generator' => ['name' => 'Hashtags', 'icon' => 'fas fa-hashtag'],
                             'file-converter' => ['name' => 'Convertisseur', 'icon' => 'fas fa-exchange-alt'],
                             'bg-remover' => ['name' => 'Suppresseur fond', 'icon' => 'fas fa-magic'],
                             'recipe-finder' => ['name' => 'Recettes', 'icon' => 'fas fa-utensils']
                         ];
                         
                         foreach ($all_services as $id => $service) {
                             if ($id !== $service_config['service_id']) {
                                 echo '<a href="' . $id . '.php" class="related-service">';
                                 echo '<i class="' . $service['icon'] . '"></i>';
                                 echo '<span>' . $service['name'] . '</span>';
                                 echo '</a>';
                             }
                         }
                         ?>
                     </div>
                 </div>
 
                 <!-- Section premium (si applicable) -->
                 <?php if (isset($service_config['premium_features']) && !empty($service_config['premium_features']) && (!$user || $user['plan_type'] === 'free')): ?>
                     <div class="sidebar-section premium-section">
                         <h3><i class="fas fa-star"></i> Fonctionnalités Premium</h3>
                         <div class="premium-features">
                             <?php foreach ($service_config['premium_features'] as $feature): ?>
                                 <div class="premium-feature">
                                     <i class="fas fa-check"></i>
                                     <span><?php echo htmlspecialchars($feature); ?></span>
                                 </div>
                             <?php endforeach; ?>
                         </div>
                         <a href="upgrade.php" class="btn btn-warning btn-small">
                             <i class="fas fa-crown"></i>
                             Passer au Premium
                         </a>
                     </div>
                 <?php endif; ?>
 
             </aside>
         </section>
 
         <!-- Section FAQ spécifique au service -->
         <?php if (isset($service_config['faq']) && !empty($service_config['faq'])): ?>
             <section class="service-faq">
                 <h2>Questions fréquentes</h2>
                 <div class="faq-items">
                     <?php foreach ($service_config['faq'] as $question => $answer): ?>
                         <div class="faq-item">
                             <div class="faq-question">
                                 <span><?php echo htmlspecialchars($question); ?></span>
                                 <i class="fas fa-chevron-down"></i>
                             </div>
                             <div class="faq-answer">
                                 <p><?php echo htmlspecialchars($answer); ?></p>
                             </div>
                         </div>
                     <?php endforeach; ?>
                 </div>
             </section>
         <?php endif; ?>
 
     </div>
 </div>
 
 <!-- CSS spécifique au template de service -->
 <style>
 /* Template de Service */
 .service-template {
     padding: 10px 0;
 }
 
 .service-header {
     background: linear-gradient(135deg, var(--background-light), var(--primary-light));
     border-radius: var(--radius-xl);
     padding: 3rem 2rem;
     margin-bottom: 3rem;
     text-align: center;
 }
 
 .service-header-content {
     display: flex;
     align-items: center;
     justify-content: center;
     gap: 2rem;
     margin-bottom: 2rem;
     flex-wrap: wrap;
 }
 
 .service-icon-large {
     width: 120px;
     height: 120px;
     border-radius: 50%;
     display: flex;
     align-items: center;
     justify-content: center;
     color: white;
     font-size: 3rem;
     box-shadow: var(--shadow-lg);
 }
 
 .service-header-text {
     flex: 1;
     min-width: 300px;
     text-align: left;
 }
 
 .service-title {
     font-size: 2.5rem;
     font-weight: 700;
     margin-bottom: 1rem;
     color: var(--text-dark);
 }
 
 .service-description {
     font-size: 1.1rem;
     color: var(--text-medium);
     margin-bottom: 1.5rem;
     line-height: 1.6;
 }
 
 .service-features {
     display: flex;
     gap: 0.5rem;
     flex-wrap: wrap;
 }
 
 .feature-tag {
     background: var(--primary-color);
     color: white;
     padding: 0.25rem 0.75rem;
     border-radius: var(--radius-full);
     font-size: 0.875rem;
     font-weight: 500;
 }
 
 .service-indicators {
     display: flex;
     justify-content: center;
     gap: 2rem;
     flex-wrap: wrap;
 }
 
 .indicator {
     display: flex;
     align-items: center;
     gap: 0.5rem;
     color: var(--text-medium);
     font-weight: 500;
 }
 
 .indicator i {
     color: var(--accent-color);
 }
 
 .service-main {
     display: grid;
     grid-template-columns: 1fr 300px;
     gap: 3rem;
     margin-bottom: 3rem;
 }
 
 .service-workspace {
     background: var(--background-white);
     border: 2px solid var(--border-color);
     border-radius: var(--radius-lg);
     padding: 2rem;
 }
 
 /* Zone d'upload */
 .upload-section {
     margin-bottom: 2rem;
 }
 
 .upload-zone {
     border: 2px dashed var(--border-color);
     border-radius: var(--radius-lg);
     padding: 3rem;
     text-align: center;
     transition: var(--transition);
     cursor: pointer;
 }
 
 .upload-zone:hover,
 .upload-zone.dragover {
     border-color: var(--primary-color);
     background: var(--primary-light);
 }
 
 .upload-content i {
     font-size: 3rem;
     color: var(--primary-color);
     margin-bottom: 1rem;
 }
 
 .upload-content h3 {
     margin-bottom: 0.5rem;
     color: var(--text-dark);
 }
 
 .upload-content p {
     color: var(--text-light);
     margin: 0;
 }
 
 #fileInput {
     display: none;
 }
 
 .upload-progress {
     margin-top: 1rem;
 }
 
 .progress-bar {
     width: 100%;
     height: 8px;
     background: var(--border-color);
     border-radius: var(--radius-full);
     overflow: hidden;
 }
 
 .progress-fill {
     height: 100%;
     background: var(--primary-color);
     transition: width 0.3s ease;
     width: 0%;
 }
 
 .progress-text {
     display: block;
     text-align: center;
     margin-top: 0.5rem;
     font-weight: 500;
 }
 
 .file-info {
     margin-top: 1rem;
     padding: 1rem;
     background: var(--background-light);
     border-radius: var(--radius);
     font-size: 0.9rem;
 }
 
 .file-info h4 {
     margin-bottom: 0.5rem;
     color: var(--text-dark);
 }
 
 /* Formulaire de service */
 .service-form {
     display: flex;
     flex-direction: column;
     gap: 1.5rem;
 }
 
 .form-actions {
     display: flex;
     gap: 1rem;
     flex-wrap: wrap;
 }
 
 /* Zone de résultat */
 .result-section {
     margin-top: 2rem;
     padding: 2rem;
     background: var(--background-light);
     border-radius: var(--radius-lg);
     border: 2px solid var(--border-color);
 }
 
 .result-header {
     display: flex;
     justify-content: space-between;
     align-items: center;
     margin-bottom: 1.5rem;
     flex-wrap: wrap;
     gap: 1rem;
 }
 
 .result-actions {
     display: flex;
     gap: 0.5rem;
     flex-wrap: wrap;
 }
 
 .result-content {
     min-height: 100px;
     padding: 1rem;
     background: var(--background-white);
     border: 1px solid var(--border-color);
     border-radius: var(--radius);
     text-align: center;
 }
 
 .result-info {
     margin-top: 1rem;
     padding: 1rem;
     background: var(--info-color);
     color: white;
     border-radius: var(--radius);
     font-size: 0.9rem;
 }
 
 /* Sidebar */
 .service-sidebar {
     display: flex;
     flex-direction: column;
     gap: 2rem;
 }
 
 .sidebar-section {
     background: var(--background-white);
     border: 2px solid var(--border-color);
     border-radius: var(--radius-lg);
     padding: 1.5rem;
 }
 
 .sidebar-section h3 {
     display: flex;
     align-items: center;
     gap: 0.5rem;
     margin-bottom: 1rem;
     color: var(--text-dark);
     font-size: 1.1rem;
 }
 
 .usage-steps {
     display: flex;
     flex-direction: column;
     gap: 1rem;
 }
 
 .usage-step {
     display: flex;
     align-items: flex-start;
     gap: 1rem;
 }
 
 .step-number {
     width: 30px;
     height: 30px;
     background: var(--primary-color);
     color: white;
     border-radius: 50%;
     display: flex;
     align-items: center;
     justify-content: center;
     font-weight: 600;
     font-size: 0.9rem;
     flex-shrink: 0;
 }
 
 .step-description {
     color: var(--text-medium);
     font-size: 0.9rem;
     line-height: 1.4;
 }
 
 .service-stats {
     display: flex;
     flex-direction: column;
     gap: 1rem;
 }
 
 .stat-item {
     text-align: center;
 }
 
 .stat-value {
     display: block;
     font-size: 1.5rem;
     font-weight: 700;
     color: var(--primary-color);
 }
 
 .stat-label {
     font-size: 0.8rem;
     color: var(--text-light);
     text-transform: uppercase;
     letter-spacing: 0.5px;
 }
 
 .related-services {
     display: flex;
     flex-direction: column;
     gap: 0.5rem;
 }
 
 .related-service {
     display: flex;
     align-items: center;
     gap: 0.75rem;
     padding: 0.75rem;
     border-radius: var(--radius);
     text-decoration: none;
     color: var(--text-medium);
     transition: var(--transition);
 }
 
 .related-service:hover {
     background: var(--primary-light);
     color: var(--primary-color);
 }
 
 .related-service i {
     width: 20px;
     text-align: center;
 }
 
 .premium-section {
     background: linear-gradient(135deg, var(--warning-color), #f97316);
     color: white;
     border-color: var(--warning-color);
 }
 
 .premium-section h3 {
     color: white;
 }
 
 .premium-features {
     margin-bottom: 1rem;
 }
 
 .premium-feature {
     display: flex;
     align-items: center;
     gap: 0.5rem;
     margin-bottom: 0.5rem;
     font-size: 0.9rem;
 }
 
 /* FAQ */
 .service-faq {
     background: var(--background-white);
     border: 2px solid var(--border-color);
     border-radius: var(--radius-lg);
     padding: 2rem;
     margin-bottom: 2rem;
 }
 
 .service-faq h2 {
     margin-bottom: 1.5rem;
     text-align: center;
 }
 
 .faq-items {
     display: flex;
     flex-direction: column;
     gap: 1rem;
 }
 
 .faq-item {
     border: 1px solid var(--border-color);
     border-radius: var(--radius);
     overflow: hidden;
 }
 
 .faq-question {
     display: flex;
     justify-content: space-between;
     align-items: center;
     padding: 1rem;
     background: var(--background-light);
     cursor: pointer;
     transition: var(--transition);
 }
 
 .faq-question:hover {
     background: var(--primary-light);
 }
 
 .faq-answer {
     padding: 0 1rem;
     max-height: 0;
     overflow: hidden;
     transition: all 0.3s ease;
 }
 
 .faq-item.active .faq-answer {
     padding: 1rem;
     max-height: 200px;
 }
 
 .faq-item.active .faq-question i {
     transform: rotate(180deg);
 }
 
 /* Responsive */
 @media (max-width: 768px) {
     .service-header-content {
         flex-direction: column;
         text-align: center;
     }
     
     .service-header-text {
         text-align: center;
         min-width: auto;
     }
     
     .service-title {
         font-size: 2rem;
     }
     
     .service-main {
         grid-template-columns: 1fr;
         gap: 2rem;
     }
     
     .service-indicators {
         gap: 1rem;
     }
     
     .form-actions {
         flex-direction: column;
     }
     
     .result-header {
         flex-direction: column;
         align-items: flex-start;
     }
 }
 
 @media (max-width: 480px) {
     .service-template {
         padding: 1rem 0;
     }
     
     .service-header {
         padding: 2rem 1rem;
         margin-bottom: 2rem;
     }
     
     .service-workspace,
     .sidebar-section {
         padding: 1rem;
     }
     
     .upload-zone {
         padding: 2rem 1rem;
     }
     
     .service-icon-large {
         width: 80px;
         height: 80px;
         font-size: 2rem;
     }
 }
 </style>
 
 <!-- JavaScript pour le template de service -->
 <script>
 document.addEventListener('DOMContentLoaded', function() {
     // Gestion de l'upload de fichiers
     const uploadZone = document.getElementById('uploadZone');
     const fileInput = document.getElementById('fileInput');
     const uploadProgress = document.getElementById('uploadProgress');
     const progressFill = document.getElementById('progressFill');
     const progressText = document.getElementById('progressText');
     
     if (uploadZone && fileInput) {
         uploadZone.addEventListener('click', () => fileInput.click());
         
         uploadZone.addEventListener('dragover', (e) => {
             e.preventDefault();
             uploadZone.classList.add('dragover');
         });
         
         uploadZone.addEventListener('dragleave', () => {
             uploadZone.classList.remove('dragover');
         });
         
         uploadZone.addEventListener('drop', (e) => {
             e.preventDefault();
             uploadZone.classList.remove('dragover');
             handleFiles(e.dataTransfer.files);
         });
         
         fileInput.addEventListener('change', (e) => {
             handleFiles(e.target.files);
         });
     }
     
     // Gestion du formulaire de service
     const serviceForm = document.getElementById('serviceForm');
     if (serviceForm) {
         serviceForm.addEventListener('submit', handleServiceSubmit);
     }
     
     // Gestion de la FAQ
     const faqQuestions = document.querySelectorAll('.faq-question');
     faqQuestions.forEach(question => {
         question.addEventListener('click', () => {
             const faqItem = question.parentElement;
             faqItem.classList.toggle('active');
         });
     });
     
     // Charger les statistiques d'utilisation
     loadServiceStats();
 });
 
 function handleFiles(files) {
     const uploadProgress = document.getElementById('uploadProgress');
     const progressFill = document.getElementById('progressFill');
     const progressText = document.getElementById('progressText');
     
     if (files.length > 0) {
         uploadProgress.style.display = 'block';
         
         // Simulation de l'upload - à remplacer par la vraie logique
         let progress = 0;
         const interval = setInterval(() => {
             progress += 10;
             progressFill.style.width = progress + '%';
             progressText.textContent = progress + '%';
             
             if (progress >= 100) {
                 clearInterval(interval);
                 setTimeout(() => {
                     uploadProgress.style.display = 'none';
                     showNotification('Fichier(s) uploadé(s) avec succès!', 'success');
                 }, 500);
             }
         }, 200);
     }
 }
 
 function handleServiceSubmit(e) {
     e.preventDefault();
     
     const submitBtn = document.getElementById('submitBtn');
     const resultSection = document.getElementById('resultSection');
     
     // Afficher l'état de chargement
     if (window.miniServices && window.miniServices.showButtonLoading) {
         window.miniServices.showButtonLoading(submitBtn);
     }
     
     // Traitement du service - à implémenter selon le service
     processService(new FormData(e.target));
 }
 
 function processService(formData) {
     const serviceType = formData.get('service_type');
     
     // Simulation du traitement - à remplacer par la vraie logique
     setTimeout(() => {
         showResult('Résultat généré avec succès!');
         
         // Tracking de l'utilisation
         if (window.miniServices && window.miniServices.trackUserInteraction) {
             window.miniServices.trackUserInteraction('service_used', serviceType);
         }
     }, 2000);
 }
 
 function showResult(result) {
     const resultSection = document.getElementById('resultSection');
     const resultContent = document.getElementById('resultContent');
     const submitBtn = document.getElementById('submitBtn');
     
     if (resultSection && resultContent) {
         resultContent.innerHTML = result;
         resultSection.style.display = 'block';
         resultSection.scrollIntoView({ behavior: 'smooth' });
     }
     
     // Masquer l'état de chargement
     if (window.miniServices && window.miniServices.hideButtonLoading && submitBtn) {
         window.miniServices.hideButtonLoading(submitBtn);
     }
 }
 
 function loadServiceStats() {
     // Charger les statistiques depuis l'API
     // fetch('/api/service-stats.php?service=' + serviceType)
     //     .then(response => response.json())
     //     .then(data => {
     //         document.getElementById('usageCount').textContent = data.daily || '0';
     //         document.getElementById('totalUsage').textContent = data.total || '0';
     //     });
 }
 </script>
 
 <?php
 // Inclusion du contenu spécifique du service s'il est défini
 if (isset($service_config['additional_content'])) {
     include $service_config['additional_content'];
 }
 
 // Inclure le footer Bootstrap (wrapper unique)
 require_once __DIR__ . '/footer-bootstrap.php';
 ?>