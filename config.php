<?php
// config.php - Configuration de base RESTAURÉE (Sans nouvelles fonctionnalités)

// Détection de l'environnement
define('IS_LOCAL', $_SERVER['HTTP_HOST'] === 'localhost' || str_contains($_SERVER['HTTP_HOST'], '127.0.0.1'));

// Définition des chemins absolus et URL
define('ROOT_PATH', __DIR__);
define('INCLUDE_PATH', ROOT_PATH . '/includes');
define('IS_LOCALHOST', in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1']));
define('BASE_URL', IS_LOCALHOST ? '/miniservices' : '');

// Configuration du site
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
define('SITE_URL', IS_LOCAL ? 'http://localhost/miniservices' : 'https://mini-services.tech');
define('SITE_NAME', 'Mini Services');
define('SITE_EMAIL', 'contact@mini-services.tech');

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'multiservices');
define('DB_USER', 'root');
define('DB_PASS', 'root');

// Activation du debugging en dev
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/error.log');

// Vérifier que le dossier logs existe, sinon le créer
if (!is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0777, true);
}

// Fonction pour le logging d'erreurs
function logError($message) {
    error_log(date('Y-m-d H:i:s') . ' - ' . $message . "\n");
}

// Connexion à la base de données avec PDO
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $this->connection = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        } catch(PDOException $e) {
            die("Erreur de connexion : " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
}

// Initialisation de la connexion principale
try {
    $db = Database::getInstance()->getConnection();
} catch (Exception $e) {
    error_log("Erreur de connexion DB: " . $e->getMessage());
    die("Erreur de connexion à la base de données");
}

// Configuration des chemins
define('UPLOAD_PATH', 'uploads/');
define('CONVERTED_PATH', 'converted/');
define('QR_PATH', 'qr_codes/');
define('BG_REMOVED_PATH', 'bg_removed/');

// Tailles maximales de fichiers (en bytes)
define('MAX_FILE_SIZE', 50 * 1024 * 1024); // 50MB

// Types de fichiers autorisés
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp']);
define('ALLOWED_AUDIO_TYPES', ['mp3', 'wav', 'ogg', 'flac', 'aac']);
define('ALLOWED_VIDEO_TYPES', ['mp4', 'avi', 'mkv', 'webm', 'mov']);
define('ALLOWED_DOCUMENT_TYPES', ['pdf', 'doc', 'docx', 'txt', 'rtf']);

// Configuration de session
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0);
    session_start();
}

// Fonctions utilitaires
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

function generateRandomString($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

function getFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

function formatFileSize($size) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $power = $size > 0 ? floor(log($size, 1024)) : 0;
    return number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
}

function createDirectories() {
    $dirs = [UPLOAD_PATH, CONVERTED_PATH, QR_PATH, BG_REMOVED_PATH];
    foreach ($dirs as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}

// Inclusion des systèmes de sécurité et optimisation
require_once __DIR__ . '/security/CSRFProtection.php';
require_once __DIR__ . '/security/RateLimiter.php';
require_once __DIR__ . '/security/FileValidator.php';
require_once __DIR__ . '/security/CacheManager.php';
require_once __DIR__ . '/security/FileEncryption.php';
require_once __DIR__ . '/security/DatabaseOptimizer.php';
require_once __DIR__ . '/security/AnalyticsAndSEO.php';

// Initialisation automatique des optimisations
try {
    // Optimisation de la base de données
    $dbOptimizer = DatabaseOptimizer::getInstance();
    
    // Nettoyage du cache et rate limiting
    $cacheManager = CacheManager::getInstance();
    $rateLimiter = RateLimiter::getInstance();
    
    // Nettoyage automatique périodique
    if (rand(1, 100) === 1) { // 1% de chance à chaque chargement
        $cacheManager->cleanup();
        $rateLimiter->cleanup();
        
        $fileValidator = FileValidator::getInstance();
        $fileValidator->cleanQuarantine();
        
        $fileEncryption = FileEncryption::getInstance();
        $fileEncryption->cleanupExpiredKeys();
    }
    
} catch (Exception $e) {
    error_log('Erreur initialisation optimisations: ' . $e->getMessage());
}

// Fonctions SEO et Analytics pour compatibilité - uniquement si pas déjà définies
if (!function_exists('get_seo_meta_tags')) {
    function get_seo_meta_tags() {
        // Fonction de compatibilité pour les meta tags SEO
        return '';
    }
}

if (!function_exists('get_analytics_code')) {
    function get_analytics_code() {
        // Fonction de compatibilité pour Google Analytics
        return '';
    }
}

if (!function_exists('track_service_usage')) {
    function track_service_usage($service, $action, $data = []) {
        // Fonction de compatibilité pour le tracking
        return '';
    }
}
?>
