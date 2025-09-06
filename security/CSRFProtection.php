<?php
/**
 * Classe de protection CSRF pour mini-services.tech
 * Implémentation robuste avec rotation des tokens et validation stricte
 */

class CSRFProtection {
    private static $instance = null;
    private $tokenLifetime = 3600; // 1 heure
    private $tokenName = 'csrf_token';
    private $sessionKey = 'csrf_tokens';
    
    private function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->initializeTokenStorage();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Initialise le stockage des tokens CSRF
     */
    private function initializeTokenStorage() {
        if (!isset($_SESSION[$this->sessionKey])) {
            $_SESSION[$this->sessionKey] = [];
        }
        $this->cleanExpiredTokens();
    }
    
    /**
     * Génère un nouveau token CSRF sécurisé
     */
    public function generateToken() {
        $token = bin2hex(random_bytes(32));
        $timestamp = time();
        
        $_SESSION[$this->sessionKey][$token] = [
            'timestamp' => $timestamp,
            'used' => false
        ];
        
        // Limiter le nombre de tokens actifs
        $this->limitActiveTokens();
        
        return $token;
    }
    
    /**
     * Valide un token CSRF
     */
    public function validateToken($token) {
        if (empty($token) || !is_string($token)) {
            return false;
        }
        
        if (!isset($_SESSION[$this->sessionKey][$token])) {
            return false;
        }
        
        $tokenData = $_SESSION[$this->sessionKey][$token];
        
        // Vérifier l'expiration
        if ((time() - $tokenData['timestamp']) > $this->tokenLifetime) {
            unset($_SESSION[$this->sessionKey][$token]);
            return false;
        }
        
        // Vérifier si déjà utilisé (protection double soumission)
        if ($tokenData['used']) {
            unset($_SESSION[$this->sessionKey][$token]);
            return false;
        }
        
        // Marquer comme utilisé et supprimer
        unset($_SESSION[$this->sessionKey][$token]);
        
        return true;
    }
    
    /**
     * Nettoie les tokens expirés
     */
    private function cleanExpiredTokens() {
        $currentTime = time();
        foreach ($_SESSION[$this->sessionKey] as $token => $data) {
            if (($currentTime - $data['timestamp']) > $this->tokenLifetime) {
                unset($_SESSION[$this->sessionKey][$token]);
            }
        }
    }
    
    /**
     * Limite le nombre de tokens actifs par session
     */
    private function limitActiveTokens($maxTokens = 10) {
        if (count($_SESSION[$this->sessionKey]) > $maxTokens) {
            // Supprimer les plus anciens
            uasort($_SESSION[$this->sessionKey], function($a, $b) {
                return $a['timestamp'] - $b['timestamp'];
            });
            
            $_SESSION[$this->sessionKey] = array_slice($_SESSION[$this->sessionKey], -$maxTokens, null, true);
        }
    }
    
    /**
     * Génère un input hidden HTML pour les formulaires
     */
    public function getTokenField() {
        $token = $this->generateToken();
        return sprintf('<input type="hidden" name="%s" value="%s">', 
                      htmlspecialchars($this->tokenName), 
                      htmlspecialchars($token));
    }
    
    /**
     * Génère un token pour les requêtes AJAX
     */
    public function getTokenForAjax() {
        return [
            'name' => $this->tokenName,
            'value' => $this->generateToken()
        ];
    }
    
    /**
     * Middleware pour valider automatiquement les requêtes POST
     */
    public function validateRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST[$this->tokenName] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
            
            if (!$this->validateToken($token)) {
                http_response_code(403);
                die(json_encode([
                    'success' => false, 
                    'error' => 'CSRF token validation failed',
                    'code' => 'CSRF_INVALID'
                ]));
            }
        }
    }
    
    /**
     * Valide spécifiquement pour les requêtes AJAX
     */
    public function validateAjaxRequest() {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST[$this->tokenName] ?? null;
        
        if (!$this->validateToken($token)) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'error' => 'CSRF token validation failed',
                'code' => 'CSRF_INVALID'
            ]);
            exit;
        }
    }
    
    /**
     * Retourne des statistiques de sécurité
     */
    public function getSecurityStats() {
        return [
            'active_tokens' => count($_SESSION[$this->sessionKey]),
            'session_id' => session_id(),
            'last_cleanup' => time()
        ];
    }
}

/**
 * Fonction helper globale pour faciliter l'usage
 */
function csrf_token() {
    return CSRFProtection::getInstance()->generateToken();
}

function csrf_field() {
    return CSRFProtection::getInstance()->getTokenField();
}

function csrf_validate($token = null) {
    if ($token === null) {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
    }
    return CSRFProtection::getInstance()->validateToken($token);
}
?>