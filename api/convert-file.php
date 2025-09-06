<?php
// api/convert-file.php - Version de récupération
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

// Nettoyage opportuniste des fichiers temporaires plus anciens
cleanupOldTempFiles(__DIR__ . '/../storage/temp_conversions/', 60 * 10); // 10 minutes

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Aucun fichier reçu ou erreur d\'upload']);
    exit;
}

if (!isset($_POST['target_format']) || empty($_POST['target_format'])) {
    echo json_encode(['success' => false, 'message' => 'Format de conversion requis']);
    exit;
}

$file = $_FILES['file'];
$targetFormat = sanitize($_POST['target_format']);
$originalName = $file['name'];
$tempPath = $file['tmp_name'];
$fileSize = $file['size'];

// Vérification basique de la taille du fichier
if ($fileSize > MAX_FILE_SIZE) {
    echo json_encode(['success' => false, 'message' => 'Fichier trop volumineux']);
    exit;
}

// Détecter le type de fichier original
$originalExtension = getFileExtension($originalName);

try {
    // Générer un nom unique pour le fichier
    $uniqueId = generateRandomString(16);
    $uploadedFileName = $uniqueId . '.' . $originalExtension;
    $convertedFileName = $uniqueId . '_converted.' . $targetFormat;
    
    // Utiliser les chemins de stockage sécurisés
    $uploadDir = __DIR__ . '/../storage/temp_conversions/';
    $uploadedPath = $uploadDir . $uploadedFileName;
    $convertedPath = $uploadDir . $convertedFileName;
    
    // Créer les dossiers si nécessaire
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Déplacer le fichier uploadé
    if (!move_uploaded_file($tempPath, $uploadedPath)) {
        throw new Exception('Erreur lors de l\'upload du fichier');
    }
    
    // Effectuer la conversion selon le type
    $conversionResult = performConversion($uploadedPath, $convertedPath, $originalExtension, $targetFormat);
    
    if ($conversionResult) {
        // Enregistrer dans la base de données si l'utilisateur est connecté
        if (isLoggedIn()) {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("INSERT INTO uploaded_files (user_id, original_name, file_path, file_type, file_size, service_type) VALUES (?, ?, ?, ?, ?, 'conversion')");
            $stmt->execute([$_SESSION['user_id'], $originalName, $convertedPath, $targetFormat, $fileSize]);
        }
        
        // Nettoyer le fichier original
        if (file_exists($uploadedPath)) {
            unlink($uploadedPath);
        }
        
        // Return only the downloadable basename for security
        echo json_encode([
            'success' => true,
            'download_name' => basename($convertedPath),
            'display_name' => pathinfo($originalName, PATHINFO_FILENAME) . '.' . $targetFormat,
            'original_format' => $originalExtension,
            'target_format' => $targetFormat
        ]);
        
    } else {
        throw new Exception('Erreur lors de la conversion');
    }
    
} catch (Exception $e) {
    // Nettoyer les fichiers en cas d'erreur
    if (isset($uploadedPath) && file_exists($uploadedPath)) {
        unlink($uploadedPath);
    }
    if (isset($convertedPath) && file_exists($convertedPath)) {
        unlink($convertedPath);
    }
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Supprime les fichiers plus vieux que le nombre de secondes fourni
 */
function cleanupOldTempFiles($dir, $maxAgeSeconds = 600)
{
    try {
        if (!is_dir($dir)) return;
        $now = time();
        $it = new DirectoryIterator($dir);
        foreach ($it as $fileinfo) {
            if ($fileinfo->isDot()) continue;
            $filePath = $fileinfo->getPathname();
            // Ne supprime que les fichiers (pas les dossiers)
            if ($fileinfo->isFile()) {
                $age = $now - $fileinfo->getMTime();
                if ($age > $maxAgeSeconds) {
                    @unlink($filePath);
                }
            }
        }
    } catch (Exception $e) {
        // silent
    }
}

function performConversion($sourcePath, $targetPath, $sourceFormat, $targetFormat) {
    $sourceFormat = strtolower($sourceFormat);
    $targetFormat = strtolower($targetFormat);
    
    // Conversions d'images
    if (in_array($sourceFormat, ALLOWED_IMAGE_TYPES) && in_array($targetFormat, ALLOWED_IMAGE_TYPES)) {
        return convertImage($sourcePath, $targetPath, $sourceFormat, $targetFormat);
    }
    
    // Conversions de documents (simulation)
    if (in_array($sourceFormat, ALLOWED_DOCUMENT_TYPES) && in_array($targetFormat, ALLOWED_DOCUMENT_TYPES)) {
        return convertDocument($sourcePath, $targetPath, $sourceFormat, $targetFormat);
    }
    
    // Conversions audio (simulation)
    if (in_array($sourceFormat, ALLOWED_AUDIO_TYPES) && in_array($targetFormat, ALLOWED_AUDIO_TYPES)) {
        return convertAudio($sourcePath, $targetPath, $sourceFormat, $targetFormat);
    }
    
    // Conversions vidéo (simulation)
    if (in_array($sourceFormat, ALLOWED_VIDEO_TYPES) && in_array($targetFormat, ALLOWED_VIDEO_TYPES)) {
        return convertVideo($sourcePath, $targetPath, $sourceFormat, $targetFormat);
    }
    
    return false;
}

function convertImage($sourcePath, $targetPath, $sourceFormat, $targetFormat) {
    try {
        $sourceImage = null;
        switch ($sourceFormat) {
            case 'jpg':
            case 'jpeg':
                $sourceImage = imagecreatefromjpeg($sourcePath);
                break;
            case 'png':
                $sourceImage = imagecreatefrompng($sourcePath);
                break;
            case 'gif':
                $sourceImage = imagecreatefromgif($sourcePath);
                break;
            case 'webp':
                if (function_exists('imagecreatefromwebp')) {
                    $sourceImage = imagecreatefromwebp($sourcePath);
                }
                break;
        }
        
        if (!$sourceImage) {
            return false;
        }
        
        if ($targetFormat === 'png') {
            imagealphablending($sourceImage, false);
            imagesavealpha($sourceImage, true);
        }
        
        $result = false;
        switch ($targetFormat) {
            case 'jpg':
            case 'jpeg':
                $result = imagejpeg($sourceImage, $targetPath, 90);
                break;
            case 'png':
                $result = imagepng($sourceImage, $targetPath, 6);
                break;
            case 'gif':
                $result = imagegif($sourceImage, $targetPath);
                break;
            case 'webp':
                if (function_exists('imagewebp')) {
                    $result = imagewebp($sourceImage, $targetPath, 90);
                }
                break;
        }
        
        imagedestroy($sourceImage);
        return $result;
        
    } catch (Exception $e) {
        return false;
    }
}

function convertDocument($sourcePath, $targetPath, $sourceFormat, $targetFormat) {
    $content = "Fichier converti de $sourceFormat vers $targetFormat\n";
    $content .= "Date: " . date('Y-m-d H:i:s') . "\n";
    $content .= "Fichier original: " . basename($sourcePath) . "\n\n";
    $content .= "Contenu simulé pour démonstration.\n";
    
    return file_put_contents($targetPath, $content) !== false;
}

function convertAudio($sourcePath, $targetPath, $sourceFormat, $targetFormat) {
    $metadata = [
        'title' => 'Audio converti',
        'format' => $targetFormat,
        'source_format' => $sourceFormat,
        'converted_at' => date('Y-m-d H:i:s'),
        'file_size' => filesize($sourcePath)
    ];
    
    $content = "Fichier audio converti\n";
    $content .= "Format original: $sourceFormat\n";
    $content .= "Format cible: $targetFormat\n";
    $content .= "Taille originale: " . formatFileSize(filesize($sourcePath)) . "\n";
    $content .= json_encode($metadata, JSON_PRETTY_PRINT);
    
    return file_put_contents($targetPath, $content) !== false;
}

function convertVideo($sourcePath, $targetPath, $sourceFormat, $targetFormat) {
    $metadata = [
        'title' => 'Vidéo convertie',
        'format' => $targetFormat,
        'source_format' => $sourceFormat,
        'converted_at' => date('Y-m-d H:i:s'),
        'file_size' => filesize($sourcePath)
    ];
    
    $content = "Fichier vidéo converti\n";
    $content .= "Format original: $sourceFormat\n";
    $content .= "Format cible: $targetFormat\n";
    $content .= "Taille originale: " . formatFileSize(filesize($sourcePath)) . "\n";
    $content .= json_encode($metadata, JSON_PRETTY_PRINT);
    
    return file_put_contents($targetPath, $content) !== false;
}
?>