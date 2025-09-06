<?php
// test-hashtag-api.php - Test de l'API de génération de hashtags avec formats

require_once 'config.php';

// Test des données
$test_data = [
    'keywords' => 'cooking, recipe, delicious, youtube, food',
    'category' => 'food',
    'quantity' => 10,
    'popularity' => 'mixed',
    'platform' => 'youtube'
];

echo "<h2>Test de l'API Générateur de Hashtags avec Formatage Multi-Plateforme</h2>";

echo "<h3>Données de test :</h3>";
echo "<pre>" . json_encode($test_data, JSON_PRETTY_PRINT) . "</pre>";

// Simuler l'appel POST
$_SERVER['REQUEST_METHOD'] = 'POST';
file_put_contents('php://input', json_encode($test_data));

// Capturer la sortie de l'API
ob_start();
include 'api/generate-hashtags.php';
$api_output = ob_get_clean();

echo "<h3>Réponse de l'API :</h3>";
echo "<pre>" . htmlspecialchars($api_output) . "</pre>";

// Décoder et afficher de manière plus lisible
$response = json_decode($api_output, true);
if ($response) {
    echo "<h3>Résultat décodé :</h3>";
    if ($response['success']) {
        echo "<p><strong>Succès :</strong> " . $response['count'] . " hashtags générés</p>";
        
        $hashtags = $response['hashtags'];
        
        echo "<div style='display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 10px; margin: 20px 0;'>";
        foreach ($hashtags as $hashtag) {
            echo "<span style='background: #e3f2fd; padding: 5px 10px; border-radius: 15px; text-align: center;'>" . htmlspecialchars($hashtag) . "</span>";
        }
        echo "</div>";
        
        // Test des différents formats
        echo "<h3>Test des formats de copie :</h3>";
        
        $formats = [
            'YouTube (optimisé)' => "\n\n",
            'TikTok (espaces)' => " ",
            'LinkedIn (lignes)' => "\n",
            'Point-virgules' => "; ",
            'Virgules' => ", "
        ];
        
        foreach ($formats as $formatName => $delimiter) {
            echo "<div style='margin: 15px 0; padding: 15px; background: #f5f5f5; border-radius: 8px;'>";
            echo "<h4>Format " . $formatName . " :</h4>";
            
            if ($formatName === 'YouTube (optimisé)') {
                $formatted = implode("\n\n", array_slice($hashtags, 0, 5));
            } elseif ($formatName === 'Instagram (groupes)') {
                $groups = array_chunk(array_slice($hashtags, 0, 10), 5);
                $formatted = implode("\n\n", array_map(function($group) {
                    return implode(' ', $group);
                }, $groups));
            } else {
                $formatted = implode($delimiter, array_slice($hashtags, 0, 5));
            }
            
            echo "<pre style='background: white; padding: 10px; border: 1px solid #ddd; border-radius: 4px; color: #10b981; font-family: monospace;'>" . htmlspecialchars($formatted) . "</pre>";
            echo "</div>";
        }
        
    } else {
        echo "<p style='color: red;'><strong>Erreur :</strong> " . $response['message'] . "</p>";
    }
} else {
    echo "<p style='color: red;'>Erreur : Impossible de décoder la réponse JSON</p>";
}

echo "<h3>Test de l'interface complète :</h3>";
echo "<p><a href='services/hashtag-generator.php' target='_blank' style='background: #10b981; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Ouvrir le générateur de hashtags</a></p>";

echo "<h3>Instructions de test :</h3>";
echo "<ol>";
echo "<li>Ouvrez le générateur ci-dessus</li>";
echo "<li>Entrez : <code>cooking, recipe, delicious</code></li>";
echo "<li>Sélectionnez <strong>YouTube</strong> comme plateforme</li>";
echo "<li>Cliquez <strong>Générer des hashtags</strong></li>";
echo "<li>Vérifiez que le format <strong>YouTube optimisé</strong> est pré-sélectionné</li>";
echo "<li>Testez les différents formats et l'aperçu</li>";
echo "<li>Testez la copie avec les boutons</li>";
echo "</ol>";
?>
