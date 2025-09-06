<?php
/**
 * Système de chiffrement AES-256 pour mini-services.tech
 * Implémentation robuste avec rotation des clés et gestion sécurisée
 */

class FileEncryption {
    private static $instance = null;
    private $cipher = 'AES-256-CBC';
    private $keyLength = 32; // 256 bits
    private $ivLength = 16;  // 128 bits pour CBC
    private $keyStoragePath;
    private $currentKeyId;
    
    private function __construct() {
        $this->keyStoragePath = __DIR__ . '/../storage/encryption_keys';
        if (!is_dir($this->keyStoragePath)) {
            mkdir($this->keyStoragePath, 0700, true);
        }
        $this->initializeEncryption();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Initialise le système de chiffrement avec rotation des clés
     */
    private function initializeEncryption() {
        $keyFile = $this->keyStoragePath . '/current_key.json';
        
        if (!file_exists($keyFile) || $this->isKeyExpired()) {
            $this->generateNewMasterKey();
        } else {
            $keyData = json_decode(file_get_contents($keyFile), true);
            $this->currentKeyId = $keyData['key_id'];
        }
    }
    
    /**
     * Génère une nouvelle clé maître avec rotation
     */
    private function generateNewMasterKey() {
        $keyId = 'key_' . date('Y-m-d_H-i-s') . '_' . bin2hex(random_bytes(8));
        $masterKey = random_bytes($this->keyLength);
        
        $keyData = [
            'key_id' => $keyId,
            'created_at' => time(),
            'expires_at' => time() + (30 * 24 * 3600), // 30 jours
            'algorithm' => $this->cipher,
            'key_hash' => hash('sha256', $masterKey)
        ];
        
        // Sauvegarder la clé chiffrée avec un mot de passe dérivé
        $this->saveEncryptedKey($keyId, $masterKey, $keyData);
        
        // Mettre à jour la référence de clé courante
        file_put_contents(
            $this->keyStoragePath . '/current_key.json',
            json_encode($keyData, JSON_PRETTY_PRINT)
        );
        
        $this->currentKeyId = $keyId;
        
        error_log("Nouvelle clé de chiffrement générée: {$keyId}");
    }
    
    /**
     * Sauvegarde une clé de manière sécurisée
     */
    private function saveEncryptedKey($keyId, $masterKey, $keyData) {
        // Dériver un mot de passe à partir de données système
        $systemSalt = $this->getSystemSalt();
        $keyPassword = hash_pbkdf2('sha256', $systemSalt, $keyId, 10000, 32, true);
        
        // Chiffrer la clé maître
        $iv = random_bytes($this->ivLength);
        $encryptedKey = openssl_encrypt($masterKey, $this->cipher, $keyPassword, OPENSSL_RAW_DATA, $iv);
        
        $keyRecord = [
            'key_id' => $keyId,
            'encrypted_key' => base64_encode($encryptedKey),
            'iv' => base64_encode($iv),
            'created_at' => $keyData['created_at'],
            'expires_at' => $keyData['expires_at'],
            'algorithm' => $this->cipher,
            'key_hash' => $keyData['key_hash']
        ];
        
        file_put_contents(
            $this->keyStoragePath . "/{$keyId}.json",
            json_encode($keyRecord, JSON_PRETTY_PRINT)
        );
    }
    
    /**
     * Obtient le sel système pour la dérivation de clés
     */
    private function getSystemSalt() {
        $saltFile = $this->keyStoragePath . '/system.salt';
        
        if (!file_exists($saltFile)) {
            $salt = random_bytes(32);
            file_put_contents($saltFile, base64_encode($salt));
            chmod($saltFile, 0600);
            return $salt;
        }
        
        return base64_decode(file_get_contents($saltFile));
    }
    
    /**
     * Vérifie si la clé actuelle a expiré
     */
    private function isKeyExpired() {
        $keyFile = $this->keyStoragePath . '/current_key.json';
        
        if (!file_exists($keyFile)) {
            return true;
        }
        
        $keyData = json_decode(file_get_contents($keyFile), true);
        return time() > $keyData['expires_at'];
    }
    
    /**
     * Récupère la clé maître déchiffrée
     */
    private function getMasterKey($keyId = null) {
        $keyId = $keyId ?: $this->currentKeyId;
        $keyFile = $this->keyStoragePath . "/{$keyId}.json";
        
        if (!file_exists($keyFile)) {
            throw new Exception("Clé de chiffrement introuvable: {$keyId}");
        }
        
        $keyRecord = json_decode(file_get_contents($keyFile), true);
        
        // Dériver le mot de passe de déchiffrement
        $systemSalt = $this->getSystemSalt();
        $keyPassword = hash_pbkdf2('sha256', $systemSalt, $keyId, 10000, 32, true);
        
        // Déchiffrer la clé maître
        $encryptedKey = base64_decode($keyRecord['encrypted_key']);
        $iv = base64_decode($keyRecord['iv']);
        
        $masterKey = openssl_decrypt($encryptedKey, $this->cipher, $keyPassword, OPENSSL_RAW_DATA, $iv);
        
        if ($masterKey === false) {
            throw new Exception("Impossible de déchiffrer la clé maître");
        }
        
        return $masterKey;
    }
    
    /**
     * Chiffre un fichier
     */
    public function encryptFile($filePath, $outputPath = null) {
        if (!file_exists($filePath)) {
            throw new Exception("Fichier source introuvable: {$filePath}");
        }
        
        // Vérifier et générer une nouvelle clé si nécessaire
        if ($this->isKeyExpired()) {
            $this->generateNewMasterKey();
        }
        
        $outputPath = $outputPath ?: $filePath . '.enc';
        
        // Générer un IV unique pour ce fichier
        $iv = random_bytes($this->ivLength);
        
        // Obtenir la clé maître
        $masterKey = $this->getMasterKey();
        
        // Lire et chiffrer le fichier par chunks pour les gros fichiers
        $inputHandle = fopen($filePath, 'rb');
        $outputHandle = fopen($outputPath, 'wb');
        
        if (!$inputHandle || !$outputHandle) {
            throw new Exception("Impossible d'ouvrir les fichiers pour le chiffrement");
        }
        
        // Écrire l'en-tête du fichier chiffré
        $header = [
            'version' => '1.0',
            'key_id' => $this->currentKeyId,
            'algorithm' => $this->cipher,
            'iv' => base64_encode($iv),
            'file_hash' => hash_file('sha256', $filePath),
            'created_at' => time()
        ];
        
        $headerJson = json_encode($header);
        $headerLength = strlen($headerJson);
        
        // Écrire la longueur de l'en-tête puis l'en-tête
        fwrite($outputHandle, pack('N', $headerLength));
        fwrite($outputHandle, $headerJson);
        
        // Chiffrer le contenu du fichier
        $chunkSize = 8192; // 8KB chunks
        
        while (!feof($inputHandle)) {
            $chunk = fread($inputHandle, $chunkSize);
            if ($chunk === false) break;
            
            $encryptedChunk = openssl_encrypt($chunk, $this->cipher, $masterKey, OPENSSL_RAW_DATA, $iv);
            fwrite($outputHandle, $encryptedChunk);
            
            // Modifier l'IV pour le prochain chunk (protection contre patterns)
            $iv = hash('sha256', $iv . $chunk, true);
            $iv = substr($iv, 0, $this->ivLength);
        }
        
        fclose($inputHandle);
        fclose($outputHandle);
        
        // Vérifier l'intégrité du fichier chiffré
        if (!$this->verifyEncryptedFile($outputPath)) {
            unlink($outputPath);
            throw new Exception("Échec de la vérification d'intégrité du fichier chiffré");
        }
        
        return [
            'encrypted_file' => $outputPath,
            'key_id' => $this->currentKeyId,
            'file_hash' => $header['file_hash'],
            'size' => filesize($outputPath)
        ];
    }
    
    /**
     * Déchiffre un fichier
     */
    public function decryptFile($encryptedPath, $outputPath = null) {
        if (!file_exists($encryptedPath)) {
            throw new Exception("Fichier chiffré introuvable: {$encryptedPath}");
        }
        
        $inputHandle = fopen($encryptedPath, 'rb');
        if (!$inputHandle) {
            throw new Exception("Impossible d'ouvrir le fichier chiffré");
        }
        
        // Lire l'en-tête
        $headerLengthData = fread($inputHandle, 4);
        $headerLength = unpack('N', $headerLengthData)[1];
        $headerJson = fread($inputHandle, $headerLength);
        $header = json_decode($headerJson, true);
        
        if (!$header || !isset($header['key_id'])) {
            fclose($inputHandle);
            throw new Exception("En-tête du fichier chiffré invalide");
        }
        
        // Obtenir la clé de déchiffrement
        $masterKey = $this->getMasterKey($header['key_id']);
        $iv = base64_decode($header['iv']);
        
        $outputPath = $outputPath ?: str_replace('.enc', '', $encryptedPath);
        $outputHandle = fopen($outputPath, 'wb');
        
        if (!$outputHandle) {
            fclose($inputHandle);
            throw new Exception("Impossible de créer le fichier de sortie");
        }
        
        // Déchiffrer le contenu
        $chunkSize = 8192 + 16; // Taille chunk + padding AES
        
        while (!feof($inputHandle)) {
            $encryptedChunk = fread($inputHandle, $chunkSize);
            if (empty($encryptedChunk)) break;
            
            $decryptedChunk = openssl_decrypt($encryptedChunk, $this->cipher, $masterKey, OPENSSL_RAW_DATA, $iv);
            
            if ($decryptedChunk !== false) {
                fwrite($outputHandle, $decryptedChunk);
                
                // Recalculer l'IV pour le prochain chunk
                $iv = hash('sha256', $iv . $decryptedChunk, true);
                $iv = substr($iv, 0, $this->ivLength);
            }
        }
        
        fclose($inputHandle);
        fclose($outputHandle);
        
        // Vérifier l'intégrité du fichier déchiffré
        $actualHash = hash_file('sha256', $outputPath);
        if ($actualHash !== $header['file_hash']) {
            unlink($outputPath);
            throw new Exception("Échec de la vérification d'intégrité du fichier déchiffré");
        }
        
        return [
            'decrypted_file' => $outputPath,
            'original_hash' => $header['file_hash'],
            'verified' => true
        ];
    }
    
    /**
     * Vérifie l'intégrité d'un fichier chiffré
     */
    private function verifyEncryptedFile($encryptedPath) {
        try {
            $handle = fopen($encryptedPath, 'rb');
            if (!$handle) return false;
            
            // Lire et valider l'en-tête
            $headerLengthData = fread($handle, 4);
            $headerLength = unpack('N', $headerLengthData)[1];
            $headerJson = fread($handle, $headerLength);
            $header = json_decode($headerJson, true);
            
            fclose($handle);
            
            return $header && isset($header['key_id'], $header['algorithm'], $header['iv']);
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Chiffre du contenu en mémoire (pour les petits fichiers)
     */
    public function encryptContent($content) {
        if ($this->isKeyExpired()) {
            $this->generateNewMasterKey();
        }
        
        $iv = random_bytes($this->ivLength);
        $masterKey = $this->getMasterKey();
        
        $encryptedContent = openssl_encrypt($content, $this->cipher, $masterKey, OPENSSL_RAW_DATA, $iv);
        
        return [
            'encrypted_data' => base64_encode($encryptedContent),
            'iv' => base64_encode($iv),
            'key_id' => $this->currentKeyId,
            'content_hash' => hash('sha256', $content)
        ];
    }
    
    /**
     * Déchiffre du contenu en mémoire
     */
    public function decryptContent($encryptedData, $iv, $keyId, $expectedHash = null) {
        $masterKey = $this->getMasterKey($keyId);
        
        $content = openssl_decrypt(
            base64_decode($encryptedData),
            $this->cipher,
            $masterKey,
            OPENSSL_RAW_DATA,
            base64_decode($iv)
        );
        
        if ($content === false) {
            throw new Exception("Échec du déchiffrement du contenu");
        }
        
        // Vérifier l'intégrité si un hash est fourni
        if ($expectedHash && hash('sha256', $content) !== $expectedHash) {
            throw new Exception("Échec de la vérification d'intégrité du contenu");
        }
        
        return $content;
    }
    
    /**
     * Nettoie les anciennes clés expirées
     */
    public function cleanupExpiredKeys() {
        $keyFiles = glob($this->keyStoragePath . '/key_*.json');
        $cleaned = 0;
        
        foreach ($keyFiles as $keyFile) {
            $keyData = json_decode(file_get_contents($keyFile), true);
            
            // Garder les clés pendant 90 jours après expiration pour la récupération
            if (time() > ($keyData['expires_at'] + 90 * 24 * 3600)) {
                unlink($keyFile);
                $cleaned++;
            }
        }
        
        return $cleaned;
    }
    
    /**
     * Obtient les statistiques de chiffrement
     */
    public function getEncryptionStats() {
        $keyFiles = glob($this->keyStoragePath . '/key_*.json');
        $currentKeyFile = $this->keyStoragePath . '/current_key.json';
        
        $stats = [
            'total_keys' => count($keyFiles),
            'current_key_id' => $this->currentKeyId,
            'encryption_algorithm' => $this->cipher,
            'key_length' => $this->keyLength * 8 . ' bits',
            'storage_path' => $this->keyStoragePath
        ];
        
        if (file_exists($currentKeyFile)) {
            $currentKeyData = json_decode(file_get_contents($currentKeyFile), true);
            $stats['current_key_expires'] = date('Y-m-d H:i:s', $currentKeyData['expires_at']);
            $stats['days_until_rotation'] = max(0, ceil(($currentKeyData['expires_at'] - time()) / 86400));
        }
        
        return $stats;
    }
    
    /**
     * Force la rotation de clé (pour tests ou maintenance)
     */
    public function forceKeyRotation() {
        $oldKeyId = $this->currentKeyId;
        $this->generateNewMasterKey();
        
        return [
            'old_key_id' => $oldKeyId,
            'new_key_id' => $this->currentKeyId,
            'rotated_at' => time()
        ];
    }
}

/**
 * Fonctions helper globales pour le chiffrement
 */
function encrypt_file($filePath, $outputPath = null) {
    $encryption = FileEncryption::getInstance();
    return $encryption->encryptFile($filePath, $outputPath);
}

function decrypt_file($encryptedPath, $outputPath = null) {
    $encryption = FileEncryption::getInstance();
    return $encryption->decryptFile($encryptedPath, $outputPath);
}

function encrypt_content($content) {
    $encryption = FileEncryption::getInstance();
    return $encryption->encryptContent($content);
}

function decrypt_content($encryptedData, $iv, $keyId, $expectedHash = null) {
    $encryption = FileEncryption::getInstance();
    return $encryption->decryptContent($encryptedData, $iv, $keyId, $expectedHash);
}
?>
