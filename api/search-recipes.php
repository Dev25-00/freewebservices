<?php
// api/search-recipes.php
require_once '../config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['ingredients']) || empty($input['ingredients'])) {
    echo json_encode(['success' => false, 'message' => 'Au moins un ingrédient est requis']);
    exit;
}

$ingredients = $input['ingredients'];
$mealType = isset($input['meal_type']) ? sanitize($input['meal_type']) : '';
$difficulty = isset($input['difficulty']) ? sanitize($input['difficulty']) : '';
$prepTime = isset($input['prep_time']) ? intval($input['prep_time']) : 0;
$servings = isset($input['servings']) ? intval($input['servings']) : 0;
$dietaryPreferences = isset($input['dietary_preferences']) ? $input['dietary_preferences'] : [];

try {
    $db = Database::getInstance()->getConnection();
    
    // Construction de la requête SQL
    $sql = "SELECT * FROM recipes WHERE 1=1";
    $params = [];
    
    // Filtrer par type de repas
    if (!empty($mealType)) {
        $sql .= " AND meal_type = ?";
        $params[] = $mealType;
    }
    
    // Filtrer par difficulté
    if (!empty($difficulty)) {
        $sql .= " AND difficulty = ?";
        $params[] = $difficulty;
    }
    
    // Filtrer par temps de préparation
    if ($prepTime > 0) {
        $sql .= " AND (prep_time + cook_time) <= ?";
        $params[] = $prepTime;
    }
    
    // Filtrer par nombre de portions
    if ($servings > 0) {
        $sql .= " AND servings >= ?";
        $params[] = $servings;
    }
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $allRecipes = $stmt->fetchAll();
    
    // Filtrer les recettes par ingrédients et calculer la pertinence
    $matchingRecipes = [];
    
    foreach ($allRecipes as $recipe) {
        $recipeIngredients = json_decode($recipe['ingredients'], true);
        $matchScore = calculateIngredientMatch($ingredients, $recipeIngredients);
        
        // Ne garder que les recettes avec au moins 1 ingrédient en commun
        if ($matchScore > 0) {
            // Vérifier les préférences alimentaires
            if (matchesDietaryPreferences($recipe, $recipeIngredients, $dietaryPreferences)) {
                $recipe['match_score'] = $matchScore;
                $recipe['matching_ingredients'] = getMatchingIngredients($ingredients, $recipeIngredients);
                $matchingRecipes[] = $recipe;
            }
        }
    }
    
    // Trier par score de correspondance décroissant
    usort($matchingRecipes, function($a, $b) {
        return $b['match_score'] - $a['match_score'];
    });
    
    echo json_encode([
        'success' => true,
        'recipes' => $matchingRecipes,
        'total_count' => count($matchingRecipes)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la recherche: ' . $e->getMessage()
    ]);
}

function calculateIngredientMatch($userIngredients, $recipeIngredients) {
    $score = 0;
    $userIngredientsLower = array_map('strtolower', $userIngredients);
    
    foreach ($recipeIngredients as $recipeIngredient) {
        $recipeIngredientLower = strtolower($recipeIngredient);
        
        foreach ($userIngredientsLower as $userIngredient) {
            // Correspondance exacte
            if (strpos($recipeIngredientLower, $userIngredient) !== false || 
                strpos($userIngredient, $recipeIngredientLower) !== false) {
                $score += 10;
                break;
            }
            
            // Correspondance partielle (pour les ingrédients composés)
            $userWords = explode(' ', $userIngredient);
            $recipeWords = explode(' ', $recipeIngredientLower);
            
            foreach ($userWords as $userWord) {
                if (strlen($userWord) > 3) {
                    foreach ($recipeWords as $recipeWord) {
                        if (strpos($recipeWord, $userWord) !== false || 
                            strpos($userWord, $recipeWord) !== false) {
                            $score += 5;
                            break 2;
                        }
                    }
                }
            }
        }
    }
    
    return $score;
}

function getMatchingIngredients($userIngredients, $recipeIngredients) {
    $matching = [];
    $userIngredientsLower = array_map('strtolower', $userIngredients);
    
    foreach ($recipeIngredients as $recipeIngredient) {
        $recipeIngredientLower = strtolower($recipeIngredient);
        
        foreach ($userIngredientsLower as $userIngredient) {
            if (strpos($recipeIngredientLower, $userIngredient) !== false || 
                strpos($userIngredient, $recipeIngredientLower) !== false) {
                $matching[] = $recipeIngredient;
                break;
            }
        }
    }
    
    return $matching;
}

function matchesDietaryPreferences($recipe, $recipeIngredients, $preferences) {
    if (empty($preferences)) {
        return true;
    }
    
    $recipeText = strtolower($recipe['title'] . ' ' . $recipe['description'] . ' ' . implode(' ', $recipeIngredients));
    
    foreach ($preferences as $preference) {
        switch ($preference) {
            case 'vegetarian':
                $meatKeywords = ['viande', 'bœuf', 'porc', 'agneau', 'veau', 'poisson', 'crevettes', 'moules', 'saumon', 'thon', 'poulet', 'dinde', 'canard', 'jambon', 'bacon', 'chorizo'];
                foreach ($meatKeywords as $keyword) {
                    if (strpos($recipeText, $keyword) !== false) {
                        return false;
                    }
                }
                break;
                
            case 'vegan':
                $animalKeywords = ['viande', 'bœuf', 'porc', 'agneau', 'poisson', 'poulet', 'œuf', 'lait', 'crème', 'fromage', 'beurre', 'yaourt', 'miel'];
                foreach ($animalKeywords as $keyword) {
                    if (strpos($recipeText, $keyword) !== false) {
                        return false;
                    }
                }
                break;
                
            case 'gluten-free':
                $glutenKeywords = ['blé', 'farine', 'pain', 'pâtes', 'semoule', 'orge', 'avoine', 'seitan'];
                foreach ($glutenKeywords as $keyword) {
                    if (strpos($recipeText, $keyword) !== false) {
                        return false;
                    }
                }
                break;
                
            case 'dairy-free':
                $dairyKeywords = ['lait', 'crème', 'fromage', 'beurre', 'yaourt', 'mascarpone', 'ricotta'];
                foreach ($dairyKeywords as $keyword) {
                    if (strpos($recipeText, $keyword) !== false) {
                        return false;
                    }
                }
                break;
                
            case 'low-carb':
                $carbKeywords = ['riz', 'pâtes', 'pain', 'pommes de terre', 'quinoa', 'avoine', 'céréales'];
                foreach ($carbKeywords as $keyword) {
                    if (strpos($recipeText, $keyword) !== false) {
                        return false;
                    }
                }
                break;
                
            case 'keto':
                $highCarbKeywords = ['riz', 'pâtes', 'pain', 'pommes de terre', 'quinoa', 'avoine', 'fruits', 'sucre', 'miel'];
                foreach ($highCarbKeywords as $keyword) {
                    if (strpos($recipeText, $keyword) !== false) {
                        return false;
                    }
                }
                break;
        }
    }
    
    return true;
}
?>