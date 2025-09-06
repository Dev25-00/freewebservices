<?php
// services/configs/hashtag-generator-config.php
// Configuration pour le générateur de hashtags

$service_config = [
    'service_id' => 'hashtag-generator',
    'service_name' => 'Générateur de Hashtags',
    'service_description' => 'Générez des hashtags pertinents et populaires pour booster vos publications sur les réseaux sociaux.',
    'service_icon' => 'fas fa-hashtag',
    'service_color' => '#10b981',
    'keywords' => 'hashtags, réseaux sociaux, instagram, twitter, tiktok, linkedin, marketing, social media',
    
    'features' => [
        'Gratuit' => 'Génération illimitée de hashtags',
        'Intelligent' => 'Suggestions basées sur vos mots-clés',
        'Multi-plateformes' => 'Optimisé pour Instagram, Twitter, TikTok, LinkedIn',
        'Catégorisé' => 'Hashtags par thématiques et popularité',
        'Sélection' => 'Choisissez vos hashtags préférés'
    ],
    
    'form_content' => __DIR__ . '/../forms/hashtag-generator-form.php',
    'submit_text' => 'Générer des hashtags',
    'result_area' => true,
    'upload_area' => false,
    
    'usage_steps' => [
        '1' => 'Entrez vos mots-clés séparés par des virgules',
        '2' => 'Choisissez une catégorie (optionnel) pour cibler votre niche',
        '3' => 'Sélectionnez le nombre de hashtags souhaité',
        '4' => 'Cliquez sur "Générer" pour obtenir vos hashtags',
        '5' => 'Sélectionnez et copiez les hashtags qui vous conviennent'
    ],
    
    'faq' => [
        'Combien de hashtags puis-je utiliser ?' => 'Instagram autorise jusqu\'à 30 hashtags, Twitter recommande 2-3, TikTok 3-5, et LinkedIn 3-5 hashtags.',
        'Comment choisir les bons hashtags ?' => 'Mélangez des hashtags populaires et de niche. Utilisez des hashtags pertinents pour votre contenu et votre audience.',
        'Quelle est la différence entre les types de popularité ?' => 'Populaires = très utilisés, Modérés = équilibre engagement/concurrence, Niches = spécialisés avec moins de concurrence.',
        'Puis-je sauvegarder mes hashtags ?' => 'Vous pouvez copier vos hashtags sélectionnés et les sauvegarder dans un document ou une note.',
        'Les hashtags sont-ils mis à jour ?' => 'Oui, notre base de données de hashtags est régulièrement mise à jour selon les tendances actuelles.'
    ],
    
    'premium_features' => [
        'Hashtags tendances en temps réel',
        'Analyse de performance des hashtags',
        'Suggestions personnalisées basées sur votre historique',
        'Export en différents formats',
        'Hashtags par géolocalisation'
    ],
    
    'additional_css' => [
        'hashtag-final.css'
    ],
    
    'body_class_suffix' => 'hashtag-generator',
    
    'additional_js' => [
        'js/services/hashtag-generator-script.js'
    ]
];
?>