<?php
// api/remove-background.php
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

if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Aucune image reçue ou erreur d\'upload']);
    exit;
}

$file = $_FILES['image'];
$mode = isset($_POST['mode']) ? sanitize($_POST['mode']) : 'auto';

// Vérifier que c'est une image
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($file['type'], $allowedTypes)) {
    echo json_encode(['success' => false, 'message' => 'Type de fichier non supporté. Utilisez JPG, PNG, GIF ou WebP.']);
    exit;
}

// Vérifier la taille
if ($file['size'] > 10 * 1024 * 1024) { // 10MB max pour les images
    echo json_encode(['success' => false, 'message' => 'Image trop volumineuse (max 10MB)']);
    exit;
}

try {
    // Générer un nom unique
    $uniqueId = generateRandomString(16);
    $extension = getFileExtension($file['name']);
    
    $uploadedPath = UPLOAD_PATH . $uniqueId . '.' . $extension;
    $processedPath = BG_REMOVED_PATH . $uniqueId . '_nobg.png';
    
    // Déplacer le fichier uploadé
    if (!move_uploaded_file($file['tmp_name'], $uploadedPath)) {
        throw new Exception('Erreur lors de l\'upload');
    }
    
    // Traitement de suppression de fond
    $success = removeBackground($uploadedPath, $processedPath, $mode);
    
    if ($success) {
        // Enregistrer dans la BDD si connecté
        if (isLoggedIn()) {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("INSERT INTO uploaded_files (user_id, original_name, file_path, file_type, file_size, service_type) VALUES (?, ?, ?, 'png', ?, 'background_removal')");
            $stmt->execute([$_SESSION['user_id'], $file['name'], $processedPath, filesize($processedPath)]);
        }
        
        // Nettoyer le fichier original
        if (file_exists($uploadedPath)) {
            unlink($uploadedPath);
        }
        
        echo json_encode([
            'success' => true,
            'processed_image_path' => $processedPath,
            'original_filename' => $file['name'],
            'mode' => $mode
        ]);
        
    } else {
        throw new Exception('Erreur lors du traitement de l\'image');
    }
    
} catch (Exception $e) {
    // Nettoyer en cas d'erreur
    if (isset($uploadedPath) && file_exists($uploadedPath)) {
        unlink($uploadedPath);
    }
    if (isset($processedPath) && file_exists($processedPath)) {
        unlink($processedPath);
    }
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

function removeBackground($sourcePath, $targetPath, $mode) {
    try {
        // Charger l'image source
        $sourceImage = createImageFromFile($sourcePath);
        if (!$sourceImage) {
            return false;
        }
        
        $width = imagesx($sourceImage);
        $height = imagesy($sourceImage);
        
        // Créer une image de destination avec transparence
        $destImage = imagecreatetruecolor($width, $height);
        imagealphablending($destImage, false);
        imagesavealpha($destImage, true);
        
        // Couleur transparente
        $transparent = imagecolorallocatealpha($destImage, 0, 0, 0, 127);
        imagefill($destImage, 0, 0, $transparent);
        
        // Algorithme de suppression selon le mode
        switch ($mode) {
            case 'auto':
                processAutoMode($sourceImage, $destImage, $width, $height);
                break;
            case 'precise':
                processPreciseMode($sourceImage, $destImage, $width, $height);
                break;
            case 'portrait':
                processPortraitMode($sourceImage, $destImage, $width, $height);
                break;
            default:
                processAutoMode($sourceImage, $destImage, $width, $height);
        }
        
        // Sauvegarder l'image résultante en PNG
        $result = imagepng($destImage, $targetPath, 6);
        
        // Nettoyer la mémoire
        imagedestroy($sourceImage);
        imagedestroy($destImage);
        
        return $result;
        
    } catch (Exception $e) {
        return false;
    }
}

function createImageFromFile($filepath) {
    $imageInfo = getimagesize($filepath);
    if (!$imageInfo) {
        return false;
    }
    
    switch ($imageInfo[2]) {
        case IMAGETYPE_JPEG:
            return imagecreatefromjpeg($filepath);
        case IMAGETYPE_PNG:
            return imagecreatefrompng($filepath);
        case IMAGETYPE_GIF:
            return imagecreatefromgif($filepath);
        case IMAGETYPE_WEBP:
            if (function_exists('imagecreatefromwebp')) {
                return imagecreatefromwebp($filepath);
            }
            break;
    }
    
    return false;
}

function processAutoMode($sourceImage, $destImage, $width, $height) {
    // Mode automatique : détection des bords et suppression du fond
    $cornerColors = getCornerColors($sourceImage, $width, $height);
    $backgroundColor = getMostCommonCornerColor($cornerColors);
    
    for ($x = 0; $x < $width; $x++) {
        for ($y = 0; $y < $height; $y++) {
            $rgb = imagecolorat($sourceImage, $x, $y);
            $colors = imagecolorsforindex($sourceImage, $rgb);
            
            // Calculer la différence avec la couleur de fond
            $diff = calculateColorDistance($colors, $backgroundColor);
            
            // Si la différence est faible, rendre transparent
            if ($diff < 30) { // Seuil ajustable
                $alpha = 127; // Complètement transparent
            } else {
                $alpha = max(0, min(127, ($diff - 30) * 2));
            }
            
            if ($alpha < 127) {
                $newColor = imagecolorallocatealpha($destImage, 
                    $colors['red'], $colors['green'], $colors['blue'], $alpha);
                imagesetpixel($destImage, $x, $y, $newColor);
            }
        }
    }
}

function processPreciseMode($sourceImage, $destImage, $width, $height) {
    // Mode précis : algorithme plus sophistiqué avec détection de contours
    $edges = detectEdges($sourceImage, $width, $height);
    
    for ($x = 0; $x < $width; $x++) {
        for ($y = 0; $y < $height; $y++) {
            $rgb = imagecolorat($sourceImage, $x, $y);
            $colors = imagecolorsforindex($sourceImage, $rgb);
            
            // Si c'est un bord, conserver le pixel
            if ($edges[$x][$y]) {
                $newColor = imagecolorallocate($destImage, 
                    $colors['red'], $colors['green'], $colors['blue']);
                imagesetpixel($destImage, $x, $y, $newColor);
            } else {
                // Vérifier si le pixel fait partie du sujet principal
                $isSubject = isPartOfMainSubject($sourceImage, $x, $y, $width, $height);
                
                if ($isSubject) {
                    $newColor = imagecolorallocate($destImage, 
                        $colors['red'], $colors['green'], $colors['blue']);
                    imagesetpixel($destImage, $x, $y, $newColor);
                }
                // Sinon, reste transparent
            }
        }
    }
}

function processPortraitMode($sourceImage, $destImage, $width, $height) {
    // Mode portrait : optimisé pour les visages et personnes
    $centerX = $width / 2;
    $centerY = $height / 2;
    
    for ($x = 0; $x < $width; $x++) {
        for ($y = 0; $y < $height; $y++) {
            $rgb = imagecolorat($sourceImage, $x, $y);
            $colors = imagecolorsforindex($sourceImage, $rgb);
            
            // Distance du centre (favorise le centre de l'image)
            $distanceFromCenter = sqrt(pow($x - $centerX, 2) + pow($y - $centerY, 2));
            $maxDistance = sqrt(pow($centerX, 2) + pow($centerY, 2));
            $centerWeight = 1 - ($distanceFromCenter / $maxDistance);
            
            // Détecter les couleurs de peau
            $isSkinTone = detectSkinTone($colors);
            
            // Algorithme pour portraits
            $keepPixel = false;
            
            if ($isSkinTone && $centerWeight > 0.3) {
                $keepPixel = true;
            } elseif ($centerWeight > 0.6) {
                // Zone centrale, probablement le sujet
                $keepPixel = true;
            } elseif (isEdgePixel($sourceImage, $x, $y, $width, $height)) {
                // Conserver les contours nets
                $keepPixel = true;
            }
            
            if ($keepPixel) {
                $newColor = imagecolorallocate($destImage, 
                    $colors['red'], $colors['green'], $colors['blue']);
                imagesetpixel($destImage, $x, $y, $newColor);
            }
        }
    }
}

function getCornerColors($image, $width, $height) {
    $corners = [];
    $sampleSize = min(50, $width / 10, $height / 10);
    
    // Échantillonner les coins
    for ($i = 0; $i < $sampleSize; $i++) {
        for ($j = 0; $j < $sampleSize; $j++) {
            // Coin haut-gauche
            $rgb = imagecolorat($image, $i, $j);
            $corners[] = imagecolorsforindex($image, $rgb);
            
            // Coin haut-droite
            $rgb = imagecolorat($image, $width - 1 - $i, $j);
            $corners[] = imagecolorsforindex($image, $rgb);
            
            // Coin bas-gauche
            $rgb = imagecolorat($image, $i, $height - 1 - $j);
            $corners[] = imagecolorsforindex($image, $rgb);
            
            // Coin bas-droite
            $rgb = imagecolorat($image, $width - 1 - $i, $height - 1 - $j);
            $corners[] = imagecolorsforindex($image, $rgb);
        }
    }
    
    return $corners;
}

function getMostCommonCornerColor($colors) {
    // Simplification : moyenne des couleurs des coins
    $totalR = $totalG = $totalB = 0;
    $count = count($colors);
    
    foreach ($colors as $color) {
        $totalR += $color['red'];
        $totalG += $color['green'];
        $totalB += $color['blue'];
    }
    
    return [
        'red' => intval($totalR / $count),
        'green' => intval($totalG / $count),
        'blue' => intval($totalB / $count)
    ];
}

function calculateColorDistance($color1, $color2) {
    return sqrt(
        pow($color1['red'] - $color2['red'], 2) +
        pow($color1['green'] - $color2['green'], 2) +
        pow($color1['blue'] - $color2['blue'], 2)
    );
}

function detectEdges($image, $width, $height) {
    $edges = [];
    
    for ($x = 1; $x < $width - 1; $x++) {
        for ($y = 1; $y < $height - 1; $y++) {
            $center = imagecolorat($image, $x, $y);
            $centerColors = imagecolorsforindex($image, $center);
            
            $isEdge = false;
            
            // Vérifier les pixels adjacents
            for ($dx = -1; $dx <= 1; $dx++) {
                for ($dy = -1; $dy <= 1; $dy++) {
                    if ($dx === 0 && $dy === 0) continue;
                    
                    $adjacent = imagecolorat($image, $x + $dx, $y + $dy);
                    $adjacentColors = imagecolorsforindex($image, $adjacent);
                    
                    $diff = calculateColorDistance($centerColors, $adjacentColors);
                    if ($diff > 50) { // Seuil pour détecter un bord
                        $isEdge = true;
                        break 2;
                    }
                }
            }
            
            $edges[$x][$y] = $isEdge;
        }
    }
    
    return $edges;
}

function isPartOfMainSubject($image, $x, $y, $width, $height) {
    // Algorithme simplifié pour déterminer si un pixel fait partie du sujet principal
    $centerX = $width / 2;
    $centerY = $height / 2;
    
    // Distance du centre
    $distance = sqrt(pow($x - $centerX, 2) + pow($y - $centerY, 2));
    $maxDistance = sqrt(pow($centerX, 2) + pow($centerY, 2));
    $centerWeight = 1 - ($distance / $maxDistance);
    
    // Plus proche du centre = plus probable d'être le sujet
    return $centerWeight > 0.4;
}

function detectSkinTone($colors) {
    $r = $colors['red'];
    $g = $colors['green'];
    $b = $colors['blue'];
    
    // Algorithme simplifié de détection de couleur de peau
    // Basé sur les plages RGB typiques des tons de peau
    return ($r > 95 && $g > 40 && $b > 20 &&
            $r > $g && $r > $b &&
            $r - $g > 15 && 
            abs($r - $g) > 15);
}

function isEdgePixel($image, $x, $y, $width, $height) {
    if ($x === 0 || $x === $width - 1 || $y === 0 || $y === $height - 1) {
        return false;
    }
    
    $center = imagecolorat($image, $x, $y);
    $centerColors = imagecolorsforindex($image, $center);
    
    // Vérifier le contraste avec les pixels adjacents
    $neighbors = [
        [-1, 0], [1, 0], [0, -1], [0, 1]
    ];
    
    foreach ($neighbors as $neighbor) {
        $nx = $x + $neighbor[0];
        $ny = $y + $neighbor[1];
        
        if ($nx >= 0 && $nx < $width && $ny >= 0 && $ny < $height) {
            $neighborRgb = imagecolorat($image, $nx, $ny);
            $neighborColors = imagecolorsforindex($image, $neighborRgb);
            
            $diff = calculateColorDistance($centerColors, $neighborColors);
            if ($diff > 40) {
                return true;
            }
        }
    }
    
    return false;
}
?>