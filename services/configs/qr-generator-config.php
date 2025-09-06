<?php
// services/configs/qr-generator-config.php
// Configuration pour le générateur de QR Code

$service_config = [
    'service_id' => 'qr-generator',
    'service_name' => 'Générateur QR Code',
    'service_description' => 'Créez des codes QR personnalisés pour vos liens, textes, contacts et plus encore.',
    'service_icon' => 'fas fa-qrcode',
    'service_color' => '#2563eb',
    'keywords' => 'qr code, générateur, url, contact, wifi, vcard, texte, email, téléphone',
    
    'features' => [
        'Gratuit' => 'Génération illimitée de codes QR',
        'Multi-types' => 'URL, texte, email, téléphone, WiFi, contact',
        'Personnalisable' => 'Taille, format et correction d\'erreur',
        'Haute qualité' => 'Export en PNG, JPG ou SVG',
        'Sécurisé' => 'Traitement local, pas de stockage de données'
    ],
    
    'form_content' => __DIR__ . '/../forms/qr-generator-form.php',
    'submit_text' => 'Générer le QR Code',
    'result_area' => true,
    'upload_area' => false,
    
    'usage_steps' => [
        '1' => 'Choisissez le type de QR Code à créer (URL, texte, contact, etc.)',
        '2' => 'Remplissez les informations nécessaires dans le formulaire',
        '3' => 'Personnalisez les options (taille, format, correction d\'erreur)',
        '4' => 'Cliquez sur "Générer" pour créer votre QR Code',
        '5' => 'Téléchargez votre QR Code dans le format souhaité'
    ],
    
    'faq' => [
        'Quels types de QR Codes puis-je créer ?' => 'Vous pouvez créer des QR Codes pour des URLs, du texte, des emails, des numéros de téléphone, des informations WiFi et des cartes de contact (vCard).',
        'Quel format choisir pour mon QR Code ?' => 'PNG pour la qualité avec transparence, JPG pour la taille optimisée, SVG pour le vectoriel redimensionnable.',
        'Qu\'est-ce que la correction d\'erreur ?' => 'Plus le niveau est élevé, plus le QR Code restera lisible même s\'il est partiellement endommagé ou sale.',
        'Quelle taille recommandée ?' => '300x300px est idéal pour l\'impression et l\'affichage numérique. 200px pour les petits supports, 500px pour les grandes affiches.',
        'Mes données sont-elles stockées ?' => 'Non, la génération se fait localement dans votre navigateur. Vos informations ne sont jamais transmises à nos serveurs.'
    ],
    
    'premium_features' => [
        'QR Codes personnalisés avec logo',
        'Couleurs et styles avancés',
        'QR Codes en lot (batch)',
        'Statistiques de scan',
        'QR Codes dynamiques modifiables'
    ],
    
    'additional_css' => [
        'qr-generator-styles.css'
    ],
    
    'body_class_suffix' => 'qr-generator',
    
    'additional_js' => [
        'js/qr-generator-fix.js',
        'js/qr-generator-script.js'
    ]
];
?>