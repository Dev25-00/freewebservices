<?php
// api/download.php
// API pour télécharger les fichiers convertis

require_once '../config.php';

// Headers de sécurité
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    die('Méthode non autorisée');
}

// Expect a basename (no paths) for security
if (!isset($_GET['file']) || empty($_GET['file'])) {
    http_response_code(400);
    die('Paramètre fichier manquant');
}

$fileBase = basename($_GET['file']);
$uploadDir = realpath(__DIR__ . '/../storage/temp_conversions/');
$requestedPath = $uploadDir . DIRECTORY_SEPARATOR . $fileBase;

// Vérifier que le fichier existe
if (!file_exists($requestedPath)) {
    http_response_code(404);
    die('Fichier non trouvé');
}

// Obtenir les informations du fichier
$fileInfo = pathinfo($requestedPath);
$fileName = $fileInfo['basename'];
$fileSize = filesize($requestedPath);
$mimeType = getMimeType($fileInfo['extension']);

// If AJAX request wants JSON (for client-side tracking), return info
if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'file' => $fileName, 'size' => $fileSize, 'mime' => $mimeType]);
    exit;
}

// Headers pour le téléchargement
header('Content-Type: ' . $mimeType);
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Content-Length: ' . $fileSize);
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

// Lecture et envoi du fichier par chunks pour les gros fichiers
$fileHandle = fopen($requestedPath, 'rb');
if ($fileHandle === false) {
    http_response_code(500);
    die('Erreur lors de la lecture du fichier');
}

while (!feof($fileHandle)) {
    echo fread($fileHandle, 8192); // Lire par chunks de 8KB
    flush();
}

fclose($fileHandle);

// Marquer suppression différée via modification du mtime (le cleanup supprimera)
@touch($requestedPath);

function getMimeType($extension) {
    $mimeTypes = [
        // Images
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'bmp' => 'image/bmp',
        
        // Documents
        'pdf' => 'application/pdf',
        'txt' => 'text/plain',
        'rtf' => 'application/rtf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        
        // Audio
        'mp3' => 'audio/mpeg',
        'wav' => 'audio/wav',
        'ogg' => 'audio/ogg',
        'flac' => 'audio/flac',
        'aac' => 'audio/aac',
        
        // Vidéo
        'mp4' => 'video/mp4',
        'avi' => 'video/x-msvideo',
        'mkv' => 'video/x-matroska',
        'webm' => 'video/webm',
        'mov' => 'video/quicktime'
    ];
    
    return $mimeTypes[strtolower($extension)] ?? 'application/octet-stream';
}
?>
