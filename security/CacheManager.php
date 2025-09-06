<?php
/**
 * Gestionnaire de cache pour mini-services.tech
 * Support pour Redis et fallback sur fichiers
 */

class CacheManager {
    private static $instance = null;
    private $redis = null;
    private $useRedis = false;
    private $cacheDir;
    private $defaultTTL = 3600; // 1 heure par défaut
    
    private function __construct() {
        $this->cacheDir = __DIR__ . '/../storage/cache';
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
        
        $this->initializeRedis();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function initializeRedis() {
        if (class_exists('Redis')) {
            try {
                $this->redis = new Redis();
                $this->redis->connect('127.0.0.1', 6379);
                $this->redis->select(0); // Base de données 0
                $this->useRedis = true;
                
                // Test de connexion
                $this->redis->ping();
                
            } catch (Exception $e) {
                $this->useRedis = false;
                error_log("Redis non disponible, utilisation du cache fichier: " . $e->getMessage());
            }
        }
    }
    
    /**
     * Stocke une valeur dans le cache
     */
    public function set($key, $value, $ttl = null) {
        $ttl = $ttl ?: $this->defaultTTL;
        $key = $this->normalizeKey($key);
        
        if ($this->useRedis) {
            return $this->redis->setex($key, $ttl, serialize($value));
        } else {
            return $this->setFile($key, $value, $ttl);
        }
    }
    
    /**
     * Récupère une valeur du cache
     */
    public function get($key) {
        $key = $this->normalizeKey($key);
        
        if ($this->useRedis) {
            $value = $this->redis->get($key);
            return $value !== false ? unserialize($value) : false;
        } else {
            return $this->getFile($key);
        }
    }
    
    /**
     * Vérifie si une clé existe dans le cache
     */
    public function exists($key) {
        $key = $this->normalizeKey($key);
        
        if ($this->useRedis) {
            return $this->redis->exists($key) > 0;
        } else {
            return $this->existsFile($key);
        }
    }
    
    /**
     * Supprime une clé du cache
     */
    public function delete($key) {
        $key = $this->normalizeKey($key);
        
        if ($this->useRedis) {
            return $this->redis->del($key) > 0;
        } else {
            return $this->deleteFile($key);
        }
    }
    
    /**
     * Vide tout le cache
     */
    public function flush() {
        if ($this->useRedis) {
            return $this->redis->flushDB();
        } else {
            return $this->flushFiles();
        }
    }
    
    /**
     * Incrémente une valeur numérique
     */
    public function increment($key, $value = 1) {
        $key = $this->normalizeKey($key);
        
        if ($this->useRedis) {
            return $this->redis->incrBy($key, $value);
        } else {
            $current = $this->get($key) ?: 0;
            $new = $current + $value;
            $this->set($key, $new);
            return $new;
        }
    }
    
    /**
     * Cache avec callback (pattern cache-aside)
     */
    public function remember($key, $callback, $ttl = null) {
        $value = $this->get($key);
        
        if ($value === false) {
            $value = call_user_func($callback);
            $this->set($key, $value, $ttl);
        }
        
        return $value;
    }
    
    /**
     * Cache spécifique pour les conversions de fichiers
     */
    public function cacheConversion($sourceHash, $targetFormat, $conversionData, $ttl = 86400) {
        $key = "conversion:{$sourceHash}:{$targetFormat}";
        return $this->set($key, $conversionData, $ttl);
    }
    
    /**
     * Récupère une conversion en cache
     */
    public function getCachedConversion($sourceHash, $targetFormat) {
        $key = "conversion:{$sourceHash}:{$targetFormat}";
        return $this->get($key);
    }
    
    /**
     * Cache pour les résultats de hashtags
     */
    public function cacheHashtags($keywords, $category, $hashtags, $ttl = 3600) {
        $key = "hashtags:" . md5($keywords . $category);
        return $this->set($key, $hashtags, $ttl);
    }
    
    /**
     * Récupère des hashtags en cache
     */
    public function getCachedHashtags($keywords, $category) {
        $key = "hashtags:" . md5($keywords . $category);
        return $this->get($key);
    }
    
    /**
     * Cache pour les codes QR
     */
    public function cacheQRCode($content, $qrData, $ttl = 86400) {
        $key = "qr:" . md5($content);
        return $this->set($key, $qrData, $ttl);
    }
    
    /**
     * Normalise une clé de cache
     */
    private function normalizeKey($key) {
        return 'miniservices:' . preg_replace('/[^a-zA-Z0-9:._-]/', '_', $key);
    }
    
    // Méthodes pour le cache fichier (fallback)
    
    private function setFile($key, $value, $ttl) {
        $filename = $this->getFilename($key);
        $data = [
            'value' => $value,
            'expires' => time() + $ttl
        ];
        
        return file_put_contents($filename, serialize($data)) !== false;
    }
    
    private function getFile($key) {
        $filename = $this->getFilename($key);
        
        if (!file_exists($filename)) {
            return false;
        }
        
        $data = unserialize(file_get_contents($filename));
        
        if (!$data || $data['expires'] < time()) {
            $this->deleteFile($key);
            return false;
        }
        
        return $data['value'];
    }
    
    private function existsFile($key) {
        $filename = $this->getFilename($key);
        
        if (!file_exists($filename)) {
            return false;
        }
        
        $data = unserialize(file_get_contents($filename));
        
        if (!$data || $data['expires'] < time()) {
            $this->deleteFile($key);
            return false;
        }
        
        return true;
    }
    
    private function deleteFile($key) {
        $filename = $this->getFilename($key);
        
        if (file_exists($filename)) {
            return unlink($filename);
        }
        
        return true;
    }
    
    private function flushFiles() {
        $files = glob($this->cacheDir . '/*.cache');
        $success = true;
        
        foreach ($files as $file) {
            if (!unlink($file)) {
                $success = false;
            }
        }
        
        return $success;
    }
    
    private function getFilename($key) {
        return $this->cacheDir . '/' . md5($key) . '.cache';
    }
    
    /**
     * Nettoie les fichiers de cache expirés
     */
    public function cleanup() {
        if ($this->useRedis) {
            return true; // Redis gère automatiquement l'expiration
        }
        
        $files = glob($this->cacheDir . '/*.cache');
        $cleaned = 0;
        
        foreach ($files as $file) {
            $data = unserialize(file_get_contents($file));
            
            if (!$data || $data['expires'] < time()) {
                unlink($file);
                $cleaned++;
            }
        }
        
        return $cleaned;
    }
    
    /**
     * Obtient les statistiques du cache
     */
    public function getStats() {
        if ($this->useRedis) {
            $info = $this->redis->info();
            return [
                'type' => 'redis',
                'used_memory' => $info['used_memory_human'] ?? 'N/A',
                'connected_clients' => $info['connected_clients'] ?? 0,
                'total_commands_processed' => $info['total_commands_processed'] ?? 0,
                'keyspace_hits' => $info['keyspace_hits'] ?? 0,
                'keyspace_misses' => $info['keyspace_misses'] ?? 0
            ];
        } else {
            $files = glob($this->cacheDir . '/*.cache');
            $totalSize = 0;
            $validFiles = 0;
            
            foreach ($files as $file) {
                $totalSize += filesize($file);
                $data = unserialize(file_get_contents($file));
                if ($data && $data['expires'] >= time()) {
                    $validFiles++;
                }
            }
            
            return [
                'type' => 'file',
                'total_files' => count($files),
                'valid_files' => $validFiles,
                'total_size' => $this->formatBytes($totalSize),
                'cache_dir' => $this->cacheDir
            ];
        }
    }
    
    private function formatBytes($size, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $size > 0 ? floor(log($size, 1024)) : 0;
        return number_format($size / pow(1024, $power), $precision, '.', ',') . ' ' . $units[$power];
    }
    
    /**
     * Méthode pour tester la disponibilité de Redis
     */
    public function isRedisAvailable() {
        return $this->useRedis;
    }
}

/**
 * Fonctions helper globales pour le cache
 */
function cache_set($key, $value, $ttl = null) {
    return CacheManager::getInstance()->set($key, $value, $ttl);
}

function cache_get($key) {
    return CacheManager::getInstance()->get($key);
}

function cache_remember($key, $callback, $ttl = null) {
    return CacheManager::getInstance()->remember($key, $callback, $ttl);
}

function cache_forget($key) {
    return CacheManager::getInstance()->delete($key);
}

function cache_flush() {
    return CacheManager::getInstance()->flush();
}
?>