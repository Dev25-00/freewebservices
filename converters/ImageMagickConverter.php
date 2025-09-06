<?php
/**
 * Gestionnaire de conversions ImageMagick pour mini-services.tech
 * Traitement professionnel d'images avec ImageMagick
 */

class ImageMagickConverter {
    private static $instance = null;
    private $convertPath;
    private $identifyPath;
    private $compositePath;
    private $tempDir;
    private $outputDir;
    private $maxExecutionTime = 120; // 2 minutes
    private $supportedFormats;
    private $qualitySettings;
    
    private function __construct() {
        $this->initializePaths();
        $this->initializeDirectories();
        $this->initializeSupportedFormats();
        $this->initializeQualitySettings();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function initializePaths() {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->convertPath = 'C:\\ImageMagick\\convert.exe';
            $this->identifyPath = 'C:\\ImageMagick\\identify.exe';
            $this->compositePath = 'C:\\ImageMagick\\composite.exe';
        } else {
            $this->convertPath = '/usr/bin/convert';
            $this->identifyPath = '/usr/bin/identify';
            $this->compositePath = '/usr/bin/composite';
        }
        
        // Recherche automatique si les chemins par défaut n'existent pas
        if (!file_exists($this->convertPath)) {
            $this->convertPath = $this->findExecutable('convert');
            $this->identifyPath = $this->findExecutable('identify');
            $this->compositePath = $this->findExecutable('composite');
        }
    }
    
    private function initializeDirectories() {
        $this->tempDir = __DIR__ . '/../storage/temp_images';
        $this->outputDir = __DIR__ . '/../converted';
        
        if (!is_dir($this->tempDir)) {
            mkdir($this->tempDir, 0755, true);
        }
        if (!is_dir($this->outputDir)) {
            mkdir($this->outputDir, 0755, true);
        }
    }
    
    private function initializeSupportedFormats() {
        $this->supportedFormats = [
            'input' => [
                'jpg', 'jpeg', 'png', 'gif', 'bmp', 'tiff', 'tif', 
                'webp', 'svg', 'pdf', 'eps', 'psd', 'xcf', 'raw',
                'cr2', 'nef', 'arw', 'dng', 'ico', 'cur'
            ],
            'output' => [
                'jpg', 'jpeg', 'png', 'gif', 'bmp', 'tiff', 'webp', 
                'pdf', 'svg', 'ico', 'avif', 'heic'
            ]
        ];
    }
    
    private function initializeQualitySettings() {
        $this->qualitySettings = [
            'jpg' => [
                'low' => 60,
                'medium' => 80,
                'high' => 95,
                'maximum' => 98
            ],
            'webp' => [
                'low' => 50,
                'medium' => 75,
                'high' => 90,
                'maximum' => 95
            ],
            'png' => [
                'compression' => 6  // 0-9, 6 est optimal
            ]
        ];
    }
    
    /**
     * Convertit une image avec options avancées
     */
    public function convert($inputPath, $outputPath, $targetFormat, $options = []) {
        if (!$this->isImageMagickAvailable()) {
            throw new Exception('ImageMagick n\'est pas disponible sur ce système');
        }
        
        if (!file_exists($inputPath)) {
            throw new Exception('Fichier source introuvable');
        }
        
        $imageInfo = $this->getImageInfo($inputPath);
        $command = $this->buildConvertCommand($inputPath, $outputPath, $targetFormat, $options, $imageInfo);
        
        return $this->executeCommand($command, $outputPath);
    }
    
    /**
     * Redimensionne une image avec préservation du ratio
     */
    public function resize($inputPath, $outputPath, $width, $height = null, $options = []) {
        if (!$height) {
            $height = $width; // Carré si hauteur non spécifiée
        }
        
        $resizeOption = isset($options['force_dimensions']) && $options['force_dimensions'] 
            ? sprintf('%dx%d!', $width, $height)  // Force les dimensions exactes
            : sprintf('%dx%d>', $width, $height); // Préserve le ratio, ne grandit pas
        
        $command = sprintf(
            '%s %s -resize %s %s',
            escapeshellarg($this->convertPath),
            escapeshellarg($inputPath),
            $resizeOption,
            escapeshellarg($outputPath)
        );
        
        return $this->executeCommand($command, $outputPath);
    }
    
    /**
     * Applique un filigrane sur une image
     */
    public function addWatermark($inputPath, $watermarkPath, $outputPath, $options = []) {
        $position = $options['position'] ?? 'southeast';
        $opacity = $options['opacity'] ?? 50;
        $offset = $options['offset'] ?? '+10+10';
        
        $command = sprintf(
            '%s %s %s -geometry %s -dissolve %d -gravity %s %s',
            escapeshellarg($this->compositePath),
            escapeshellarg($watermarkPath),
            escapeshellarg($inputPath),
            $offset,
            $opacity,
            $position,
            escapeshellarg($outputPath)
        );
        
        return $this->executeCommand($command, $outputPath);
    }
    
    /**
     * Applique des filtres et effets à une image
     */
    public function applyFilter($inputPath, $outputPath, $filterType, $options = []) {
        $filterCommand = '';
        
        switch ($filterType) {
            case 'blur':
                $radius = $options['radius'] ?? 2;
                $sigma = $options['sigma'] ?? 1;
                $filterCommand = sprintf('-blur %sx%s', $radius, $sigma);
                break;
                
            case 'sharpen':
                $radius = $options['radius'] ?? 1;
                $sigma = $options['sigma'] ?? 1;
                $filterCommand = sprintf('-sharpen %sx%s', $radius, $sigma);
                break;
                
            case 'brightness':
                $brightness = $options['brightness'] ?? 110;
                $filterCommand = sprintf('-modulate %s', $brightness);
                break;
                
            case 'contrast':
                $contrast = $options['contrast'] ?? 1.2;
                $filterCommand = sprintf('-sigmoidal-contrast %s', $contrast);
                break;
                
            case 'sepia':
                $threshold = $options['threshold'] ?? 80;
                $filterCommand = sprintf('-sepia-tone %s%%', $threshold);
                break;
                
            case 'grayscale':
                $filterCommand = '-colorspace Gray';
                break;
                
            case 'vintage':
                $filterCommand = '-sepia-tone 80% -modulate 90,130,100';
                break;
                
            case 'emboss':
                $filterCommand = '-emboss 2';
                break;
                
            default:
                throw new Exception('Filtre non supporté: ' . $filterType);
        }
        
        $command = sprintf(
            '%s %s %s %s',
            escapeshellarg($this->convertPath),
            escapeshellarg($inputPath),
            $filterCommand,
            escapeshellarg($outputPath)
        );
        
        return $this->executeCommand($command, $outputPath);
    }
    
    /**
     * Crée une miniature optimisée
     */
    public function createThumbnail($inputPath, $outputPath, $size = 150, $quality = 'medium') {
        $qualityValue = $this->getQualityValue('jpg', $quality);
        
        $command = sprintf(
            '%s %s -thumbnail %dx%d^ -gravity center -extent %dx%d -quality %d %s',
            escapeshellarg($this->convertPath),
            escapeshellarg($inputPath),
            $size, $size,
            $size, $size,
            $qualityValue,
            escapeshellarg($outputPath)
        );
        
        return $this->executeCommand($command, $outputPath);
    }
    
    /**
     * Optimise une image pour le web
     */
    public function optimizeForWeb($inputPath, $outputPath, $targetFormat, $options = []) {
        $quality = $options['quality'] ?? 'medium';
        $maxWidth = $options['max_width'] ?? 1920;
        $maxHeight = $options['max_height'] ?? 1080;
        $progressive = $options['progressive'] ?? true;
        
        $command = sprintf('%s %s', 
            escapeshellarg($this->convertPath),
            escapeshellarg($inputPath)
        );
        
        // Redimensionnement si nécessaire
        $command .= sprintf(' -resize %dx%d>', $maxWidth, $maxHeight);
        
        // Paramètres selon le format
        switch ($targetFormat) {
            case 'jpg':
            case 'jpeg':
                $qualityValue = $this->getQualityValue('jpg', $quality);
                $command .= sprintf(' -quality %d', $qualityValue);
                if ($progressive) {
                    $command .= ' -interlace Plane';
                }
                break;
                
            case 'webp':
                $qualityValue = $this->getQualityValue('webp', $quality);
                $command .= sprintf(' -quality %d', $qualityValue);
                break;
                
            case 'png':
                $compression = $this->qualitySettings['png']['compression'];
                $command .= sprintf(' -compress PNG -define png:compression-level=%d', $compression);
                break;
        }
        
        // Suppression des métadonnées pour réduire la taille
        if ($options['strip_metadata'] !== false) {
            $command .= ' -strip';
        }
        
        $command .= sprintf(' %s', escapeshellarg($outputPath));
        
        return $this->executeCommand($command, $outputPath);
    }
    
    /**
     * Obtient les informations détaillées d'une image
     */
    public function getImageInfo($imagePath) {
        if (!file_exists($imagePath)) {
            throw new Exception('Image introuvable pour l\'analyse');
        }
        
        $command = sprintf(
            '%s -ping -format "%%w:%%h:%%m:%%[colorspace]:%%[bit-depth]:%%b" %s',
            escapeshellarg($this->identifyPath),
            escapeshellarg($imagePath)
        );
        
        $output = shell_exec($command);
        
        if (empty($output)) {
            throw new Exception('Impossible d\'analyser l\'image');
        }
        
        $parts = explode(':', trim($output));
        
        if (count($parts) < 6) {
            throw new Exception('Réponse invalide de ImageMagick');
        }
        
        return [
            'width' => intval($parts[0]),
            'height' => intval($parts[1]),
            'format' => $parts[2],
            'colorspace' => $parts[3],
            'bit_depth' => intval($parts[4]),
            'file_size' => $parts[5],
            'aspect_ratio' => $parts[0] > 0 ? round($parts[0] / $parts[1], 2) : 0
        ];
    }
    
    /**
     * Construit la commande de conversion optimisée
     */
    private function buildConvertCommand($inputPath, $outputPath, $targetFormat, $options, $imageInfo) {
        $command = sprintf('%s %s', 
            escapeshellarg($this->convertPath),
            escapeshellarg($inputPath)
        );
        
        // Redimensionnement si spécifié
        if (isset($options['width']) || isset($options['height'])) {
            $width = $options['width'] ?? $imageInfo['width'];
            $height = $options['height'] ?? $imageInfo['height'];
            
            if (isset($options['crop']) && $options['crop']) {
                $command .= sprintf(' -resize %dx%d^ -gravity center -crop %dx%d+0+0', 
                    $width, $height, $width, $height);
            } else {
                $command .= sprintf(' -resize %dx%d>', $width, $height);
            }
        }
        
        // Rotation si spécifiée
        if (isset($options['rotate'])) {
            $angle = intval($options['rotate']);
            if ($angle !== 0) {
                $command .= sprintf(' -rotate %d', $angle);
            }
        }
        
        // Qualité selon le format
        $quality = $options['quality'] ?? 'medium';
        $qualityValue = $this->getQualityValue($targetFormat, $quality);
        if ($qualityValue !== null) {
            $command .= sprintf(' -quality %d', $qualityValue);
        }
        
        // Paramètres spécifiques selon le format de sortie
        switch ($targetFormat) {
            case 'jpg':
            case 'jpeg':
                if ($options['progressive'] ?? true) {
                    $command .= ' -interlace Plane';
                }
                break;
                
            case 'png':
                $compression = $this->qualitySettings['png']['compression'];
                $command .= sprintf(' -compress PNG -define png:compression-level=%d', $compression);
                break;
                
            case 'webp':
                $command .= ' -define webp:method=6';
                break;
        }
        
        // Suppression des métadonnées pour réduire la taille
        if ($options['strip_metadata'] !== false) {
            $command .= ' -strip';
        }
        
        $command .= sprintf(' %s', escapeshellarg($outputPath));
        
        return $command;
    }
    
    /**
     * Exécute une commande ImageMagick avec monitoring
     */
    private function executeCommand($command, $outputPath) {
        set_time_limit($this->maxExecutionTime + 30);
        
        $startTime = microtime(true);
        
        $output = shell_exec($command . ' 2>&1');
        
        $executionTime = microtime(true) - $startTime;
        
        if (!file_exists($outputPath)) {
            throw new Exception('Échec de la conversion ImageMagick: ' . ($output ?: 'Erreur inconnue'));
        }
        
        return [
            'success' => true,
            'output_path' => $outputPath,
            'file_size' => filesize($outputPath),
            'execution_time' => round($executionTime, 2),
            'imagemagick_output' => $output
        ];
    }
    
    /**
     * Obtient la valeur de qualité pour un format donné
     */
    private function getQualityValue($format, $quality) {
        $format = strtolower($format);
        
        if (!isset($this->qualitySettings[$format])) {
            return null;
        }
        
        $settings = $this->qualitySettings[$format];
        
        if (is_numeric($quality)) {
            return min(100, max(1, intval($quality)));
        }
        
        return $settings[$quality] ?? $settings['medium'];
    }
    
    /**
     * Recherche un exécutable dans le PATH système
     */
    private function findExecutable($name) {
        $pathSeparator = (PHP_OS_FAMILY === 'Windows') ? ';' : ':';
        $executable = (PHP_OS_FAMILY === 'Windows') ? $name . '.exe' : $name;
        
        $paths = explode($pathSeparator, $_SERVER['PATH'] ?? '');
        
        foreach ($paths as $path) {
            $fullPath = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $executable;
            if (file_exists($fullPath) && is_executable($fullPath)) {
                return $fullPath;
            }
        }
        
        return null;
    }
    
    /**
     * Vérifie la disponibilité d'ImageMagick
     */
    public function isImageMagickAvailable() {
        return !empty($this->convertPath) && 
               !empty($this->identifyPath) && 
               file_exists($this->convertPath) && 
               file_exists($this->identifyPath);
    }
    
    /**
     * Retourne les formats supportés
     */
    public function getSupportedFormats() {
        return $this->supportedFormats;
    }
    
    /**
     * Nettoie les fichiers temporaires anciens
     */
    public function cleanupTempFiles($maxAge = 86400) {
        $files = glob($this->tempDir . '/*');
        $cleaned = 0;
        
        foreach ($files as $file) {
            if (filemtime($file) < (time() - $maxAge)) {
                unlink($file);
                $cleaned++;
            }
        }
        
        return $cleaned;
    }
    
    /**
     * Obtient les statistiques d'utilisation
     */
    public function getStats() {
        return [
            'imagemagick_available' => $this->isImageMagickAvailable(),
            'convert_path' => $this->convertPath,
            'identify_path' => $this->identifyPath,
            'composite_path' => $this->compositePath,
            'temp_dir' => $this->tempDir,
            'output_dir' => $this->outputDir,
            'supported_formats' => $this->supportedFormats,
            'quality_settings' => $this->qualitySettings,
            'max_execution_time' => $this->maxExecutionTime
        ];
    }
}

/**
 * Fonctions helper pour l'utilisation simplifiée
 */
function convertImageAdvanced($inputPath, $outputPath, $targetFormat, $options = []) {
    $converter = ImageMagickConverter::getInstance();
    return $converter->convert($inputPath, $outputPath, $targetFormat, $options);
}

function resizeImage($inputPath, $outputPath, $width, $height = null, $options = []) {
    $converter = ImageMagickConverter::getInstance();
    return $converter->resize($inputPath, $outputPath, $width, $height, $options);
}

function getAdvancedImageInfo($imagePath) {
    $converter = ImageMagickConverter::getInstance();
    return $converter->getImageInfo($imagePath);
}

function isImageMagickAvailable() {
    $converter = ImageMagickConverter::getInstance();
    return $converter->isImageMagickAvailable();
}
?>