<?php
/**
 * Système de validation et sécurisation des fichiers pour mini-services.tech
 * Protection contre les uploads malveillants et validation stricte
 */

class FileValidator {
    private static $instance = null;
    
    // Signatures magiques des fichiers (magic numbers)
    private $magicNumbers = [
        'jpg' => [
            ['offset' => 0, 'signature' => 'FFD8FF'],
        ],
        'jpeg' => [
            ['offset' => 0, 'signature' => 'FFD8FF'],
        ],
        'png' => [
            ['offset' => 0, 'signature' => '89504E470D0A1A0A'],
        ],
        'gif' => [
            ['offset' => 0, 'signature' => '474946383761'],
            ['offset' => 0, 'signature' => '474946383961'],
        ],
        'pdf' => [
            ['offset' => 0, 'signature' => '255044462D'],
        ],
        'mp3' => [
            ['offset' => 0, 'signature' => 'ID3'],
            ['offset' => 0, 'signature' => 'FFFB'],
        ],
        'mp4' => [
            ['offset' => 4, 'signature' => '66747970'],
        ],
        'zip' => [
            ['offset' => 0, 'signature' => '504B0304'],
            ['offset' => 0, 'signature' => '504B0506'],
        ],
        'docx' => [
            ['offset' => 0, 'signature' => '504B0304'], // ZIP signature
        ],
        'webp' => [
            ['offset' => 0, 'signature' => '52494646'],
            ['offset' => 8, 'signature' => '57454250'],
        ]
    ];
    
    // Extensions dangereuses à bloquer absolument
    private $dangerousExtensions = [
        'php', 'php3', 'php4', 'php5', 'php7', 'phtml', 'pht',
        'exe', 'bat', 'cmd', 'com', 'scr', 'vbs', 'js', 'jar',
        'asp', 'aspx', 'jsp', 'cfm', 'cgi', 'pl', 'sh', 'py',
        'rb', 'go', 'swift', 'c', 'cpp', 'h', 'hpp'
    ];
    
    // Tailles maximales par type de fichier (en bytes)
    private $maxSizes = [
        'image' => 10 * 1024 * 1024,    // 10MB pour images
        'document' => 50 * 1024 * 1024, // 50MB pour documents
        'audio' => 100 * 1024 * 1024,   // 100MB pour audio
        'video' => 500 * 1024 * 1024,   // 500MB pour vidéo
        'default' => 50 * 1024 * 1024   // 50MB par défaut
    ];
    
    private function __construct() {
        $this->initializeQuarantine();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function initializeQuarantine() {
        $quarantineDir = __DIR__ . '/../storage/quarantine';
        if (!is_dir($quarantineDir)) {
            mkdir($quarantineDir, 0700, true);
        }
    }
    
    /**
     * Validation complète d'un fichier uploadé
     */
    public function validateFile($file, $allowedTypes = []) {
        $result = [
            'valid' => false,
            'errors' => [],
            'warnings' => [],
            'file_info' => [],
            'security_scan' => []
        ];
        
        // Vérifications de base
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            $result['errors'][] = 'Fichier non valide ou non uploadé correctement';
            return $result;
        }
        
        $filePath = $file['tmp_name'];
        $fileName = $file['name'];
        $fileSize = $file['size'];
        
        // Informations de base du fichier
        $result['file_info'] = [
            'name' => $fileName,
            'size' => $fileSize,
            'size_formatted' => $this->formatFileSize($fileSize),
            'mime_type' => mime_content_type($filePath),
            'extension' => strtolower(pathinfo($fileName, PATHINFO_EXTENSION))
        ];
        
        // 1. Vérification de l'extension
        if (!$this->validateExtension($result['file_info']['extension'], $allowedTypes)) {
            $result['errors'][] = 'Extension de fichier non autorisée: ' . $result['file_info']['extension'];
        }
        
        // 2. Vérification des extensions dangereuses
        if ($this->isDangerousExtension($result['file_info']['extension'])) {
            $result['errors'][] = 'Extension de fichier potentiellement dangereuse détectée';
            $this->quarantineFile($filePath, $fileName, 'dangerous_extension');
        }
        
        // 3. Vérification de la taille
        $sizeCheck = $this->validateFileSize($fileSize, $result['file_info']['extension']);
        if (!$sizeCheck['valid']) {
            $result['errors'][] = $sizeCheck['error'];
        }
        
        // 4. Validation de la signature magique
        $signatureCheck = $this->validateMagicNumber($filePath, $result['file_info']['extension']);
        if (!$signatureCheck['valid']) {
            $result['warnings'][] = $signatureCheck['message'];
        }
        
        // 5. Scan de sécurité avancé
        $securityScan = $this->performSecurityScan($filePath, $fileName);
        $result['security_scan'] = $securityScan;
        
        if (!empty($securityScan['threats'])) {
            $result['errors'] = array_merge($result['errors'], $securityScan['threats']);
            $this->quarantineFile($filePath, $fileName, 'security_threat');
        }
        
        // 6. Validation du contenu selon le type
        $contentValidation = $this->validateFileContent($filePath, $result['file_info']['extension']);
        if (!$contentValidation['valid']) {
            $result['errors'][] = $contentValidation['error'];
        }
        
        $result['valid'] = empty($result['errors']);
        
        return $result;
    }
    
    /**
     * Valide l'extension du fichier
     */
    private function validateExtension($extension, $allowedTypes = []) {
        if (empty($allowedTypes)) {
            $allowedTypes = array_merge(
                ALLOWED_IMAGE_TYPES,
                ALLOWED_DOCUMENT_TYPES,
                ALLOWED_AUDIO_TYPES,
                ALLOWED_VIDEO_TYPES
            );
        }
        
        return in_array($extension, $allowedTypes);
    }
    
    /**
     * Vérifie si l'extension est dangereuse
     */
    private function isDangerousExtension($extension) {
        return in_array($extension, $this->dangerousExtensions);
    }
    
    /**
     * Valide la taille du fichier selon son type
     */
    private function validateFileSize($size, $extension) {
        $maxSize = $this->getMaxSizeForExtension($extension);
        
        if ($size > $maxSize) {
            return [
                'valid' => false,
                'error' => sprintf(
                    'Fichier trop volumineux (%s). Taille maximale autorisée: %s',
                    $this->formatFileSize($size),
                    $this->formatFileSize($maxSize)
                )
            ];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Obtient la taille maximale autorisée pour une extension
     */
    private function getMaxSizeForExtension($extension) {
        if (in_array($extension, ALLOWED_IMAGE_TYPES)) {
            return $this->maxSizes['image'];
        } elseif (in_array($extension, ALLOWED_DOCUMENT_TYPES)) {
            return $this->maxSizes['document'];
        } elseif (in_array($extension, ALLOWED_AUDIO_TYPES)) {
            return $this->maxSizes['audio'];
        } elseif (in_array($extension, ALLOWED_VIDEO_TYPES)) {
            return $this->maxSizes['video'];
        }
        
        return $this->maxSizes['default'];
    }
    
    /**
     * Valide la signature magique du fichier
     */
    private function validateMagicNumber($filePath, $extension) {
        if (!isset($this->magicNumbers[$extension])) {
            return ['valid' => true, 'message' => 'Aucune signature magique définie pour ce type'];
        }
        
        $fileHandle = fopen($filePath, 'rb');
        if (!$fileHandle) {
            return ['valid' => false, 'message' => 'Impossible de lire le fichier'];
        }
        
        $signatures = $this->magicNumbers[$extension];
        $valid = false;
        
        foreach ($signatures as $sig) {
            fseek($fileHandle, $sig['offset']);
            $bytes = fread($fileHandle, strlen($sig['signature']) / 2);
            $hex = strtoupper(bin2hex($bytes));
            
            if (strpos($hex, $sig['signature']) === 0) {
                $valid = true;
                break;
            }
        }
        
        fclose($fileHandle);
        
        return [
            'valid' => $valid,
            'message' => $valid ? 'Signature magique valide' : 'Signature magique non conforme au type déclaré'
        ];
    }
    
    /**
     * Effectue un scan de sécurité avancé
     */
    private function performSecurityScan($filePath, $fileName) {
        $threats = [];
        $warnings = [];
        
        // 1. Recherche de séquences suspectes
        $suspiciousPatterns = [
            'eval\s*\(',
            'exec\s*\(',
            'system\s*\(',
            'shell_exec\s*\(',
            'passthru\s*\(',
            'base64_decode\s*\(',
            '<script[^>]*>',
            'javascript:',
            'data:text/html',
            'vbscript:',
            '<%.*%>',
            '<\?php'
        ];
        
        $content = file_get_contents($filePath, false, null, 0, 1024 * 1024); // Premier 1MB
        
        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match('/' . $pattern . '/i', $content)) {
                $threats[] = 'Contenu potentiellement malveillant détecté: ' . $pattern;
            }
        }
        
        // 2. Vérification du nom de fichier
        if (preg_match('/[<>:"|?*\x00-\x1f]/', $fileName)) {
            $warnings[] = 'Nom de fichier contient des caractères suspects';
        }
        
        // 3. Vérification de la longueur du nom
        if (strlen($fileName) > 255) {
            $threats[] = 'Nom de fichier trop long (potentielle attaque buffer overflow)';
        }
        
        // 4. Recherche de doubles extensions
        if (preg_match('/\.[a-z0-9]+\.[a-z0-9]+$/i', $fileName)) {
            $warnings[] = 'Fichier avec double extension détecté';
        }
        
        return [
            'threats' => $threats,
            'warnings' => $warnings,
            'scanned_bytes' => strlen($content)
        ];
    }
    
    /**
     * Valide le contenu du fichier selon son type
     */
    private function validateFileContent($filePath, $extension) {
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                return $this->validateImageContent($filePath, 'jpeg');
            case 'png':
                return $this->validateImageContent($filePath, 'png');
            case 'gif':
                return $this->validateImageContent($filePath, 'gif');
            case 'pdf':
                return $this->validatePdfContent($filePath);
            default:
                return ['valid' => true];
        }
    }
    
    /**
     * Valide le contenu d'une image
     */
    private function validateImageContent($filePath, $type) {
        $imageInfo = @getimagesize($filePath);
        
        if ($imageInfo === false) {
            return ['valid' => false, 'error' => 'Fichier image corrompu ou invalide'];
        }
        
        // Vérifier les dimensions maximales
        $maxWidth = 10000;
        $maxHeight = 10000;
        
        if ($imageInfo[0] > $maxWidth || $imageInfo[1] > $maxHeight) {
            return [
                'valid' => false,
                'error' => sprintf(
                    'Dimensions de l\'image trop importantes (%dx%d). Maximum autorisé: %dx%d',
                    $imageInfo[0], $imageInfo[1], $maxWidth, $maxHeight
                )
            ];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Valide le contenu d'un PDF
     */
    private function validatePdfContent($filePath) {
        $handle = fopen($filePath, 'rb');
        if (!$handle) {
            return ['valid' => false, 'error' => 'Impossible de lire le fichier PDF'];
        }
        
        $header = fread($handle, 8);
        fclose($handle);
        
        if (strpos($header, '%PDF-') !== 0) {
            return ['valid' => false, 'error' => 'Fichier PDF invalide - en-tête manquant'];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Met un fichier en quarantaine
     */
    private function quarantineFile($filePath, $fileName, $reason) {
        $quarantineDir = __DIR__ . '/../storage/quarantine';
        $timestamp = date('Y-m-d_H-i-s');
        $safeFileName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileName);
        $quarantinePath = $quarantineDir . '/' . $timestamp . '_' . $reason . '_' . $safeFileName;
        
        if (copy($filePath, $quarantinePath)) {
            // Créer un fichier de log avec les détails
            $logData = [
                'timestamp' => time(),
                'original_name' => $fileName,
                'reason' => $reason,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                'user_id' => $_SESSION['user_id'] ?? null
            ];
            
            file_put_contents($quarantinePath . '.log', json_encode($logData, JSON_PRETTY_PRINT));
        }
    }
    
    /**
     * Formate la taille d'un fichier
     */
    private function formatFileSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
        return number_format($bytes / pow(1024, $power), 2) . ' ' . $units[$power];
    }
    
    /**
     * Nettoie la quarantaine (supprime les fichiers anciens)
     */
    public function cleanQuarantine($maxAge = 604800) { // 7 jours par défaut
        $quarantineDir = __DIR__ . '/../storage/quarantine';
        $files = glob($quarantineDir . '/*');
        $currentTime = time();
        
        foreach ($files as $file) {
            if (($currentTime - filemtime($file)) > $maxAge) {
                unlink($file);
            }
        }
    }
    
    /**
     * Obtient les statistiques de sécurité
     */
    public function getSecurityStats() {
        $quarantineDir = __DIR__ . '/../storage/quarantine';
        $quarantinedFiles = glob($quarantineDir . '/*.log');
        
        $stats = [
            'quarantined_files' => count($quarantinedFiles),
            'last_cleanup' => time(),
            'supported_formats' => count($this->magicNumbers),
            'dangerous_extensions' => count($this->dangerousExtensions)
        ];
        
        return $stats;
    }
}

/**
 * Fonctions helper globales
 */
function validateUploadedFile($file, $allowedTypes = []) {
    $validator = FileValidator::getInstance();
    return $validator->validateFile($file, $allowedTypes);
}

function sanitizeFileName($fileName) {
    // Nettoyer le nom de fichier
    $fileName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileName);
    $fileName = preg_replace('/_+/', '_', $fileName);
    $fileName = trim($fileName, '_');
    
    // Limiter la longueur
    if (strlen($fileName) > 100) {
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $baseName = pathinfo($fileName, PATHINFO_FILENAME);
        $fileName = substr($baseName, 0, 100 - strlen($extension) - 1) . '.' . $extension;
    }
    
    return $fileName;
}
?>