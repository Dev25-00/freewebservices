<?php
// services/configs/recipe-finder-config.php
// Configuration pour le trouveur de recettes

$service_config = [
    'service_id' => 'recipe-finder',
    'service_name' => 'Trouveur de Recettes',
    'service_description' => 'Découvrez des recettes délicieuses selon vos ingrédients, régimes alimentaires et préférences.',
    'service_icon' => 'fas fa-utensils',
    'service_color' => '#ef4444',
    'keywords' => 'recettes, cuisine, ingrédients, régime, végétarien, sans gluten, cooking',
    
    'features' => [
        'Recherche par ingrédients' => 'Trouvez des recettes avec ce que vous avez',
        'Filtres diététiques' => 'Végétarien, vegan, sans gluten, etc.',
        'Temps de préparation' => 'Recettes rapides ou élaborées',
        'Difficulté' => 'Du débutant au chef expert',
        'Base étendue' => 'Milliers de recettes du monde entier'
    ],
    
    'form_content' => __DIR__ . '/../forms/recipe-finder-form.php',
    'submit_text' => 'Trouver des recettes',
    'result_area' => true,
    'upload_area' => false,
    
    'usage_steps' => [
        '1' => 'Entrez les ingrédients que vous avez sous la main',
        '2' => 'Sélectionnez vos préférences alimentaires',
        '3' => 'Choisissez le temps de préparation souhaité',
        '4' => 'Lancez la recherche',
        '5' => 'Découvrez des recettes personnalisées'
    ],
    
    'faq' => [
        'Comment fonctionne la recherche par ingrédients ?' => 'Entrez simplement les ingrédients séparés par des virgules, et nous trouvons des recettes qui les utilisent.',
        'Puis-je exclure certains ingrédients ?' => 'Oui, vous pouvez spécifier les ingrédients à éviter dans le champ "Allergies/Exclusions".',
        'Les recettes incluent-elles les temps de cuisson ?' => 'Oui, chaque recette affiche le temps de préparation et de cuisson.',
        'Y a-t-il des recettes végétariennes ?' => 'Absolument ! Utilisez les filtres pour trouver des recettes végétariennes, vegans ou autres régimes spéciaux.',
        'Puis-je sauvegarder mes recettes préférées ?' => 'Les utilisateurs connectés peuvent sauvegarder leurs recettes favorites pour plus tard.'
    ],
    
    'premium_features' => [
        'Recettes premium exclusives',
        'Sauvegarde illimitée de favoris',
        'Suggestions personnalisées',
        'Planificateur de repas',
        'Liste de courses automatique'
    ],
    
    'additional_css' => [
        'recipe-finder-styles.css'
    ],
    
    'additional_js' => [
        'recipe-finder-script.js'
    ]
];
?>