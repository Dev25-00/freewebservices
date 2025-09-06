<?php
// services/configs/file-converter-config.php
// Configuration pour le convertisseur de fichiers

$service_config = [
    'service_id' => 'file-converter',
    'service_name' => 'Convertisseur de Fichiers',
    'service_description' => 'Convertissez vos fichiers en différents formats facilement et gratuitement.',
    'service_icon' => 'fas fa-exchange-alt',
    'service_color' => '#f59e0b',
    'keywords' => 'convertisseur, fichiers, PDF, images, vidéo, audio, format, conversion',
    
    'features' => [
        'Multi-formats' => 'Images, documents, audio, vidéo',
        'Qualité préservée' => 'Conversion sans perte de qualité',
        'Rapide' => 'Traitement en quelques secondes',
        'Sécurisé' => 'Fichiers supprimés automatiquement',
        'Sans limite' => 'Convertissez autant que vous voulez'
    ],
    
    'form_content' => __DIR__ . '/../forms/file-converter-form.php',
    'submit_text' => 'Convertir le fichier',
    'result_area' => true,
    'upload_area' => true,
    'file_accept' => '.pdf,.jpg,.jpeg,.png,.gif,.webp,.mp4,.avi,.mp3,.wav,.doc,.docx,.xls,.xlsx,.ppt,.pptx',
    'file_info' => 'PDF, Images (JPG, PNG, GIF, WebP), Vidéos (MP4, AVI), Audio (MP3, WAV), Documents Office',
    'max_file_size' => '100 MB',
    
    'usage_steps' => [
        '1' => 'Glissez votre fichier ou cliquez pour le sélectionner',
        '2' => 'Choisissez le format de sortie souhaité',
        '3' => 'Ajustez les options de qualité si nécessaire',
        '4' => 'Lancez la conversion',
        '5' => 'Téléchargez votre fichier converti'
    ],
    
    'faq' => [
        'Quels formats sont supportés ?' => 'Images (JPG, PNG, GIF, WebP), Documents (PDF, DOC, XLS, PPT), Audio (MP3, WAV), Vidéo (MP4, AVI).',
        'Quelle est la taille maximale ?' => 'Les fichiers peuvent faire jusqu\'à 100 MB pour les utilisateurs gratuits, 500 MB pour les premium.',
        'Mes fichiers sont-ils sauvegardés ?' => 'Non, tous les fichiers sont automatiquement supprimés de nos serveurs après conversion.',
        'La qualité est-elle préservée ?' => 'Nous utilisons les meilleurs algorithmes pour maintenir la qualité lors de la conversion.',
        'Combien de temps prend une conversion ?' => 'Généralement quelques secondes à quelques minutes selon la taille et le format.'
    ],
    
    'premium_features' => [
        'Conversion en lot (plusieurs fichiers)',
        'Formats avancés (RAW, FLAC, etc.)',
        'Options de qualité personnalisées',
        'Fichiers jusqu\'à 500 MB',
        'Conversion plus rapide (serveurs dédiés)'
    ],
    
    'additional_css' => [
        'css/file-converter-styles.css'
    ],
    
    'additional_js' => [
        'js/file-converter-script.js'
    ]
];
?>