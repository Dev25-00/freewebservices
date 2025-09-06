<?php
/**
 * Système de Rate Limiting pour mini-services.tech
 * Protection contre les abus et attaques DoS
 */

class RateLimiter {
    private static $instance = null;
    private $storage;
    private $defaultLimits = [
        'api' => ['requests' => 60, 'window' => 3600], // 60 req/heure
        'conversion' => ['requests' => 10, 'window' => 3600], // 10 conversions/heure
        'upload' => ['requests' => 20, 'window' => 3600], // 20 uploads/heure
        'auth' => ['requests' => 20, 'window' => 3600], // 20 tentatives/heure (augmenté)
        'auth_attempts' => ['requests' => 5, 'window' => 900], // 5 tentatives POST/15min
        'general' => ['requests' => 200, 'window' => 3600] // 200 req/heure général (augmenté)
    ];
    
    private function __construct() {
        // Utilisation de fichiers pour le stockage (sera remplacé par Redis plus tard)
        $this->initializeStorage();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function initializeStorage() {
        $storageDir = __DIR__ . '/../storage/rate_limits';
        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0755, true);
        }
        $this->storage = $storageDir;
    }
    
    /**
     * Vérifie si une action est autorisée selon les limites de taux
     */
    public function isAllowed($identifier, $action = 'general', $customLimit = null) {
        $limit = $customLimit ?: $this->defaultLimits[$action] ?? $this->defaultLimits['general'];
        
        $key = $this->generateKey($identifier, $action);
        $data = $this->getData($key);
        
        $currentTime = time();
        $windowStart = $currentTime - $limit['window'];
        
        // Nettoyer les anciennes entrées
        $data = array_filter($data, function($timestamp) use ($windowStart) {
            return $timestamp > $windowStart;
        });
        
        // Vérifier la limite
        if (count($data) >= $limit['requests']) {
            $this->saveData($key, $data);
            return [
                'allowed' => false,
                'remaining' => 0,
                'reset_time' => min($data) + $limit['window'],
                'retry_after' => min($data) + $limit['window'] - $currentTime
            ];
        }
        
        // Ajouter la nouvelle requête
        $data[] = $currentTime;
        $this->saveData($key, $data);
        
        return [
            'allowed' => true,
            'remaining' => $limit['requests'] - count($data),
            'reset_time' => $currentTime + $limit['window'],
            'retry_after' => 0
        ];
    }
    
    /**
     * Enregistre une tentative d'accès
     */
    public function hit($identifier, $action = 'general') {
        return $this->isAllowed($identifier, $action);
    }
    
    /**
     * Vérifie et applique les limites avec headers HTTP
     */
    public function checkAndApply($identifier, $action = 'general') {
        $result = $this->isAllowed($identifier, $action);
        
        // Ajouter les headers de rate limiting
        header('X-RateLimit-Limit: ' . $this->defaultLimits[$action]['requests']);
        header('X-RateLimit-Remaining: ' . $result['remaining']);
        header('X-RateLimit-Reset: ' . $result['reset_time']);
        
        if (!$result['allowed']) {
            header('Retry-After: ' . $result['retry_after']);
            http_response_code(429);
            
            die(json_encode([
                'success' => false,
                'error' => 'Rate limit exceeded',
                'code' => 'RATE_LIMIT_EXCEEDED',
                'retry_after' => $result['retry_after'],
                'reset_time' => $result['reset_time']
            ]));
        }
        
        return $result;
    }
    
    /**
     * Obtient l'identifiant client (IP + User-Agent + User ID si connecté)
     */
    public function getClientIdentifier() {
        $ip = $this->getClientIP();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $userId = $_SESSION['user_id'] ?? null;
        
        // Créer un identifiant unique mais anonymisé
        $identifier = $ip . '|' . substr(md5($userAgent), 0, 8);
        
        if ($userId) {
            $identifier .= '|user_' . $userId;
        }
        
        return $identifier;
    }
    
    /**
     * Obtient l'adresse IP réelle du client
     */
    private function getClientIP() {
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ips = explode(',', $_SERVER[$key]);
                $ip = trim($ips[0]);
                
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * Génère une clé de stockage pour un identifiant et action
     */
    private function generateKey($identifier, $action) {
        return md5($identifier . '_' . $action);
    }
    
    /**
     * Récupère les données stockées pour une clé
     */
    private function getData($key) {
        $file = $this->storage . '/' . $key . '.json';
        
        if (!file_exists($file)) {
            return [];
        }
        
        $content = file_get_contents($file);
        $data = json_decode($content, true);
        
        return is_array($data) ? $data : [];
    }
    
    /**
     * Sauvegarde les données pour une clé
     */
    private function saveData($key, $data) {
        $file = $this->storage . '/' . $key . '.json';
        file_put_contents($file, json_encode($data, JSON_NUMERIC_CHECK));
    }
    
    /**
     * Nettoie les anciens fichiers de rate limiting
     */
    public function cleanup() {
        $files = glob($this->storage . '/*.json');
        $currentTime = time();
        
        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            if (is_array($data)) {
                // Supprimer si toutes les entrées sont anciennes
                $maxTime = max($data);
                if (($currentTime - $maxTime) > 7200) { // 2 heures
                    unlink($file);
                }
            }
        }
    }
    
    /**
     * Whitelist temporaire d'IP (pour tests ou situations d'urgence)
     */
    public function addToWhitelist($ip, $duration = 3600) {
        $whitelistFile = $this->storage . '/whitelist.json';
        $whitelist = [];
        
        if (file_exists($whitelistFile)) {
            $whitelist = json_decode(file_get_contents($whitelistFile), true) ?: [];
        }
        
        $whitelist[$ip] = time() + $duration;
        file_put_contents($whitelistFile, json_encode($whitelist));
    }
    
    /**
     * Vérifie si une IP est en whitelist
     */
    public function isWhitelisted($ip) {
        $whitelistFile = $this->storage . '/whitelist.json';
        
        if (!file_exists($whitelistFile)) {
            return false;
        }
        
        $whitelist = json_decode(file_get_contents($whitelistFile), true) ?: [];
        
        return isset($whitelist[$ip]) && $whitelist[$ip] > time();
    }
    
    /**
     * Obtient les statistiques de rate limiting
     */
    public function getStats() {
        $files = glob($this->storage . '/*.json');
        $activeClients = count($files) - 1; // -1 pour whitelist.json
        
        return [
            'active_clients' => $activeClients,
            'storage_path' => $this->storage,
            'limits' => $this->defaultLimits
        ];
    }
}

/**
 * Middleware automatique de rate limiting
 */
function applyRateLimiting($action = 'general') {
    $rateLimiter = RateLimiter::getInstance();
    $clientId = $rateLimiter->getClientIdentifier();
    
    // Vérifier whitelist
    $ip = explode('|', $clientId)[0];
    if ($rateLimiter->isWhitelisted($ip)) {
        return;
    }
    
    $rateLimiter->checkAndApply($clientId, $action);
}

/**
 * Fonction helper pour vérifier les limites sans les appliquer
 */
function checkRateLimit($action = 'general') {
    $rateLimiter = RateLimiter::getInstance();
    $clientId = $rateLimiter->getClientIdentifier();
    
    return $rateLimiter->isAllowed($clientId, $action);
}
?>