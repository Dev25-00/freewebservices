<?php
// api/mark-download.php - marque qu'un fichier a été téléchargé (logs/minimal)
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$file = isset($data['file']) ? basename($data['file']) : null;

if (!$file) {
    echo json_encode(['success' => false, 'message' => 'Fichier requis']);
    exit;
}

$logDir = __DIR__ . '/../storage/logs/';
if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
$entry = date('c') . "\t" . $file . "\t" . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "\n";
file_put_contents($logDir . 'download_events.log', $entry, FILE_APPEND | LOCK_EX);

echo json_encode(['success' => true]);

?>
