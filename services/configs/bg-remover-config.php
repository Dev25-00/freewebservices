<?php
// services/configs/bg-remover-config.php
// Configuration pour le suppresseur de fond

$service_config = [
    'service_id' => 'bg-remover',
    'service_name' => 'Suppresseur de Fond',
    'service_description' => 'Supprimez automatiquement l\'arrière-plan de vos images en quelques secondes avec l\'IA.',
    'service_icon' => 'fas fa-magic',
    'service_color' => '#8b5cf6',
    'keywords' => 'suppression fond, background remover, IA, images, détourage, transparent',
    
    'features' => [
        'IA Avancée' => 'Détection automatique des contours',
        'Haute précision' => 'Résultats professionnels',
        'Instantané' => 'Traitement en quelques secondes',
        'PNG transparent' => 'Export avec fond transparent',
        'Gratuit' => 'Jusqu\'à 10 images par jour'
    ],
    
    'form_content' => __DIR__ . '/../forms/bg-remover-form.php',
    'submit_text' => 'Supprimer le fond',
    'result_area' => true,
    'upload_area' => true,
    'file_accept' => '.jpg,.jpeg,.png,.gif,.webp',
    'file_info' => 'Images JPG, PNG, GIF, WebP',
    'max_file_size' => '10 MB',
    
    'usage_steps' => [
        '1' => 'Uploadez votre image (JPG, PNG, GIF, WebP)',
        '2' => 'L\'IA analyse automatiquement l\'image',
        '3' => 'Le fond est supprimé en quelques secondes',
        '4' => 'Prévisualisez le résultat',
        '5' => 'Téléchargez votre image avec fond transparent'
    ],
    
    'faq' => [
        'Quels types d\'images fonctionnent le mieux ?' => 'Les images avec des sujets bien définis et des contrastes nets donnent les meilleurs résultats.',
        'Puis-je traiter des photos de personnes ?' => 'Oui, notre IA est optimisée pour les portraits et détecte très bien les contours humains.',
        'Le résultat est-il modifiable ?' => 'Vous pouvez télécharger l\'image et l\'ajuster dans un éditeur d\'images si nécessaire.',
        'Combien d\'images puis-je traiter ?' => 'Les utilisateurs gratuits peuvent traiter 10 images par jour, illimité pour les premium.',
        'Dans quel format est l\'image finale ?' => 'L\'image est exportée en PNG avec un fond transparent pour une utilisation optimale.'
    ],
    
    'premium_features' => [
        'Traitement illimité d\'images',
        'Images haute résolution (jusqu\'à 25 MB)',
        'Ajustements manuels des contours',
        'Traitement en lot',
        'Priorité de traitement'
    ],
    
    'additional_css' => [
        'bg-remover-styles.css'
    ],
    
    'additional_js' => [
        'bg-remover-script.js'
    ]
];
?>