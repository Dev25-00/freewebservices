<?php
/**
 * Gestionnaire de conversions FFmpeg pour mini-services.tech
 * Traitement professionnel audio et vidéo avec FFmpeg
 */

class FFmpegConverter {
    private static $instance = null;
    private $ffmpegPath;
    private $ffprobePath;
    private $tempDir;
    private $outputDir;
    private $maxExecutionTime = 300; // 5 minutes
    private $supportedFormats;
    
    private function __construct() {
        $this->initializePaths();
        $this->initializeDirectories();
        $this->initializeSupportedFormats();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function initializePaths() {
        // Configuration des chemins FFmpeg selon l'environnement
        if (PHP_OS_FAMILY === 'Windows') {
            $this->ffmpegPath = 'C:\\ffmpeg\\bin\\ffmpeg.exe';
            $this->ffprobePath = 'C:\\ffmpeg\\bin\\ffprobe.exe';
        } else {
            $this->ffmpegPath = '/usr/bin/ffmpeg';
            $this->ffprobePath = '/usr/bin/ffprobe';
        }
        
        // Alternative: recherche automatique dans le PATH
        if (!file_exists($this->ffmpegPath)) {
            $this->ffmpegPath = $this->findExecutable('ffmpeg');
            $this->ffprobePath = $this->findExecutable('ffprobe');
        }
    }
    
    private function initializeDirectories() {
        $this->tempDir = __DIR__ . '/../storage/temp_conversions';
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
            'audio' => [
                'input' => ['mp3', 'wav', 'flac', 'aac', 'm4a', 'ogg', 'wma'],
                'output' => ['mp3', 'wav', 'flac', 'aac', 'm4a', 'ogg']
            ],
            'video' => [
                'input' => ['mp4', 'avi', 'mkv', 'mov', 'wmv', 'flv', 'webm', '3gp'],
                'output' => ['mp4', 'avi', 'mkv', 'mov', 'webm']
            ]
        ];
    }
    
    /**
     * Convertit un fichier audio ou vidéo
     */
    public function convert($inputPath, $outputPath, $targetFormat, $options = []) {
        if (!$this->isFFmpegAvailable()) {
            throw new Exception('FFmpeg n\'est pas disponible sur ce système');
        }
        
        if (!file_exists($inputPath)) {
            throw new Exception('Fichier source introuvable');
        }
        
        $mediaInfo = $this->getMediaInfo($inputPath);
        $conversionType = $this->determineConversionType($mediaInfo, $targetFormat);
        
        $command = $this->buildFFmpegCommand($inputPath, $outputPath, $targetFormat, $conversionType, $options);
        
        return $this->executeFFmpegCommand($command, $inputPath, $outputPath);
    }
    
    /**
     * Obtient les informations détaillées d'un fichier multimédia
     */
    public function getMediaInfo($filePath) {
        if (!file_exists($filePath)) {
            throw new Exception('Fichier introuvable pour l\'analyse');
        }
        
        $command = sprintf(
            '%s -v quiet -print_format json -show_format -show_streams %s',
            escapeshellarg($this->ffprobePath),
            escapeshellarg($filePath)
        );
        
        $output = shell_exec($command);
        
        if (empty($output)) {
            throw new Exception('Impossible d\'analyser le fichier multimédia');
        }
        
        $info = json_decode($output, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Erreur lors de l\'analyse du fichier multimédia');
        }
        
        return $this->parseMediaInfo($info);
    }
    
    /**
     * Construit la commande FFmpeg optimisée
     */
    private function buildFFmpegCommand($inputPath, $outputPath, $targetFormat, $conversionType, $options) {
        $command = sprintf('%s -i %s', 
            escapeshellarg($this->ffmpegPath),
            escapeshellarg($inputPath)
        );
        
        // Application des paramètres selon le type de conversion
        switch ($conversionType) {
            case 'audio':
                $command .= $this->buildAudioParameters($targetFormat, $options);
                break;
            case 'video':
                $command .= $this->buildVideoParameters($targetFormat, $options);
                break;
            case 'video_to_audio':
                $command .= $this->buildVideoToAudioParameters($targetFormat, $options);
                break;
        }
        
        // Paramètres de sortie
        $command .= sprintf(' -y %s', escapeshellarg($outputPath));
        
        return $command;
    }
    
    /**
     * Paramètres optimisés pour les conversions audio
     */
    private function buildAudioParameters($targetFormat, $options) {
        $params = '';
        
        switch ($targetFormat) {
            case 'mp3':
                $bitrate = $options['audio_bitrate'] ?? '192k';
                $params .= sprintf(' -codec:a libmp3lame -b:a %s', $bitrate);
                break;
            case 'wav':
                $params .= ' -codec:a pcm_s16le';
                break;
            case 'flac':
                $compression = $options['compression_level'] ?? 5;
                $params .= sprintf(' -codec:a flac -compression_level %d', $compression);
                break;
            case 'aac':
                $bitrate = $options['audio_bitrate'] ?? '128k';
                $params .= sprintf(' -codec:a aac -b:a %s', $bitrate);
                break;
            case 'ogg':
                $quality = $options['vorbis_quality'] ?? 5;
                $params .= sprintf(' -codec:a libvorbis -q:a %d', $quality);
                break;
        }
        
        // Paramètres audio communs
        if (isset($options['sample_rate'])) {
            $params .= sprintf(' -ar %d', $options['sample_rate']);
        }
        
        if (isset($options['channels'])) {
            $params .= sprintf(' -ac %d', $options['channels']);
        }
        
        return $params;
    }
    
    /**
     * Paramètres optimisés pour les conversions vidéo
     */
    private function buildVideoParameters($targetFormat, $options) {
        $params = '';
        
        switch ($targetFormat) {
            case 'mp4':
                $videoCodec = $options['video_codec'] ?? 'libx264';
                $audioCodec = $options['audio_codec'] ?? 'aac';
                $crf = $options['crf'] ?? 23;
                $params .= sprintf(' -codec:v %s -crf %d -codec:a %s', $videoCodec, $crf, $audioCodec);
                break;
            case 'webm':
                $videoBitrate = $options['video_bitrate'] ?? '1M';
                $audioBitrate = $options['audio_bitrate'] ?? '128k';
                $params .= sprintf(' -codec:v libvpx-vp9 -b:v %s -codec:a libvorbis -b:a %s', $videoBitrate, $audioBitrate);
                break;
            case 'avi':
                $params .= ' -codec:v libx264 -codec:a mp3 -b:a 192k';
                break;
            case 'mkv':
                $params .= ' -codec:v libx264 -codec:a aac -crf 20';
                break;
        }
        
        // Paramètres de résolution
        if (isset($options['width']) && isset($options['height'])) {
            $params .= sprintf(' -s %dx%d', $options['width'], $options['height']);
        }
        
        // Paramètres de framerate
        if (isset($options['framerate'])) {
            $params .= sprintf(' -r %d', $options['framerate']);
        }
        
        return $params;
    }
    
    /**
     * Paramètres pour extraire l'audio d'une vidéo
     */
    private function buildVideoToAudioParameters($targetFormat, $options) {
        $params = ' -vn'; // Désactiver la vidéo
        $params .= $this->buildAudioParameters($targetFormat, $options);
        return $params;
    }
    
    /**
     * Exécute la commande FFmpeg avec monitoring
     */
    private function executeFFmpegCommand($command, $inputPath, $outputPath) {
        // Ajout du monitoring de progression
        $progressFile = $this->tempDir . '/progress_' . uniqid() . '.txt';
        $command .= sprintf(' -progress %s', escapeshellarg($progressFile));
        
        // Exécution avec timeout
        $startTime = time();
        set_time_limit($this->maxExecutionTime + 30);
        
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w']
        ];
        
        $process = proc_open($command, $descriptors, $pipes);
        
        if (!is_resource($process)) {
            throw new Exception('Impossible de démarrer FFmpeg');
        }
        
        fclose($pipes[0]);
        
        $output = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        
        fclose($pipes[1]);
        fclose($pipes[2]);
        
        $returnCode = proc_close($process);
        
        // Nettoyage du fichier de progression
        if (file_exists($progressFile)) {
            unlink($progressFile);
        }
        
        if ($returnCode !== 0) {
            throw new Exception('Erreur de conversion FFmpeg: ' . $errors);
        }
        
        if (!file_exists($outputPath)) {
            throw new Exception('Le fichier de sortie n\'a pas été créé');
        }
        
        return [
            'success' => true,
            'output_path' => $outputPath,
            'file_size' => filesize($outputPath),
            'conversion_time' => time() - $startTime,
            'ffmpeg_output' => $output
        ];
    }
    
    /**
     * Détermine le type de conversion nécessaire
     */
    private function determineConversionType($mediaInfo, $targetFormat) {
        $hasVideo = !empty($mediaInfo['video_streams']);
        $hasAudio = !empty($mediaInfo['audio_streams']);
        
        $isTargetAudio = in_array($targetFormat, $this->supportedFormats['audio']['output']);
        $isTargetVideo = in_array($targetFormat, $this->supportedFormats['video']['output']);
        
        if ($hasVideo && $isTargetAudio) {
            return 'video_to_audio';
        } elseif ($hasVideo && $isTargetVideo) {
            return 'video';
        } elseif ($hasAudio && $isTargetAudio) {
            return 'audio';
        } else {
            throw new Exception('Type de conversion non supporté');
        }
    }
    
    /**
     * Parse les informations retournées par ffprobe
     */
    private function parseMediaInfo($info) {
        $parsed = [
            'format' => $info['format'] ?? [],
            'video_streams' => [],
            'audio_streams' => []
        ];
        
        if (isset($info['streams'])) {
            foreach ($info['streams'] as $stream) {
                if ($stream['codec_type'] === 'video') {
                    $parsed['video_streams'][] = [
                        'codec' => $stream['codec_name'] ?? 'unknown',
                        'width' => $stream['width'] ?? 0,
                        'height' => $stream['height'] ?? 0,
                        'duration' => $stream['duration'] ?? 0,
                        'bitrate' => $stream['bit_rate'] ?? 0,
                        'framerate' => $this->parseFramerate($stream['r_frame_rate'] ?? '0/1')
                    ];
                } elseif ($stream['codec_type'] === 'audio') {
                    $parsed['audio_streams'][] = [
                        'codec' => $stream['codec_name'] ?? 'unknown',
                        'sample_rate' => $stream['sample_rate'] ?? 0,
                        'channels' => $stream['channels'] ?? 0,
                        'duration' => $stream['duration'] ?? 0,
                        'bitrate' => $stream['bit_rate'] ?? 0
                    ];
                }
            }
        }
        
        return $parsed;
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
     * Parse le framerate depuis le format FFmpeg
     */
    private function parseFramerate($frameRateString) {
        if (strpos($frameRateString, '/') !== false) {
            list($num, $den) = explode('/', $frameRateString);
            return $den > 0 ? round($num / $den, 2) : 0;
        }
        return floatval($frameRateString);
    }
    
    /**
     * Vérifie la disponibilité de FFmpeg
     */
    public function isFFmpegAvailable() {
        return !empty($this->ffmpegPath) && 
               !empty($this->ffprobePath) && 
               file_exists($this->ffmpegPath) && 
               file_exists($this->ffprobePath);
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
            'ffmpeg_available' => $this->isFFmpegAvailable(),
            'ffmpeg_path' => $this->ffmpegPath,
            'ffprobe_path' => $this->ffprobePath,
            'temp_dir' => $this->tempDir,
            'output_dir' => $this->outputDir,
            'supported_formats' => $this->supportedFormats,
            'max_execution_time' => $this->maxExecutionTime
        ];
    }
}

/**
 * Fonctions helper pour l'utilisation simplifiée
 */
function convertMediaFile($inputPath, $outputPath, $targetFormat, $options = []) {
    $converter = FFmpegConverter::getInstance();
    return $converter->convert($inputPath, $outputPath, $targetFormat, $options);
}

function getMediaInfo($filePath) {
    $converter = FFmpegConverter::getInstance();
    return $converter->getMediaInfo($filePath);
}

function isMediaConversionAvailable() {
    $converter = FFmpegConverter::getInstance();
    return $converter->isFFmpegAvailable();
}
?>