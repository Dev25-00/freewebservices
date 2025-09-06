<?php
/**
 * Gestionnaire d'optimisation de base de données pour mini-services.tech
 * Indexation, optimisation des requêtes et maintenance automatique
 */

class DatabaseOptimizer {
    private static $instance = null;
    private $connection;
    private $optimizationLog = [];
    
    private function __construct() {
        $this->connection = Database::getInstance()->getConnection();
        $this->initializeOptimizations();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Initialise toutes les optimisations de base de données
     */
    public function initializeOptimizations() {
        try {
            $this->createOptimalTables();
            $this->createIndexes();
            $this->optimizeConfiguration();
            $this->setupAutomaticCleanup();
            
            $this->logOptimization('Base de données optimisée avec succès');
            
        } catch (Exception $e) {
            error_log("Erreur d'optimisation DB: " . $e->getMessage());
            $this->logOptimization('Erreur lors de l\'optimisation: ' . $e->getMessage(), 'error');
        }
    }
    
    /**
     * Crée les tables optimisées pour les performances
     */
    private function createOptimalTables() {
        $queries = [
            // Table des utilisateurs optimisée
            "CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(255) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                username VARCHAR(100) NOT NULL,
                plan_type ENUM('free', 'pro', 'max') DEFAULT 'free',
                credits INT DEFAULT 100,
                email_verified BOOLEAN DEFAULT FALSE,
                referral_code VARCHAR(20) UNIQUE,
                referred_by INT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                last_login TIMESTAMP NULL,
                status ENUM('active', 'suspended', 'deleted') DEFAULT 'active',
                
                INDEX idx_email (email),
                INDEX idx_referral_code (referral_code),
                INDEX idx_plan_type (plan_type),
                INDEX idx_status_created (status, created_at),
                INDEX idx_last_login (last_login),
                FOREIGN KEY (referred_by) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            // Table des sessions optimisée
            "CREATE TABLE IF NOT EXISTS user_sessions (
                id VARCHAR(128) PRIMARY KEY,
                user_id INT NOT NULL,
                ip_address VARCHAR(45) NOT NULL,
                user_agent TEXT,
                last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                expires_at TIMESTAMP NOT NULL,
                
                INDEX idx_user_id (user_id),
                INDEX idx_expires_at (expires_at),
                INDEX idx_last_activity (last_activity),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            // Table des conversions de fichiers
            "CREATE TABLE IF NOT EXISTS file_conversions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NULL,
                session_id VARCHAR(128) NULL,
                original_filename VARCHAR(255) NOT NULL,
                original_format VARCHAR(10) NOT NULL,
                target_format VARCHAR(10) NOT NULL,
                file_size BIGINT NOT NULL,
                conversion_status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
                processing_time DECIMAL(8,3) NULL,
                credits_cost INT DEFAULT 1,
                file_hash VARCHAR(64) NOT NULL,
                storage_path VARCHAR(500),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                completed_at TIMESTAMP NULL,
                expires_at TIMESTAMP NOT NULL,
                
                INDEX idx_user_id_status (user_id, conversion_status),
                INDEX idx_session_id (session_id),
                INDEX idx_file_hash (file_hash),
                INDEX idx_created_at (created_at),
                INDEX idx_expires_at (expires_at),
                INDEX idx_status_created (conversion_status, created_at),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            // Table des transactions de crédits
            "CREATE TABLE IF NOT EXISTS credit_transactions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                transaction_type ENUM('earned', 'spent', 'bonus', 'refund', 'purchase') NOT NULL,
                amount INT NOT NULL,
                balance_after INT NOT NULL,
                description VARCHAR(255) NOT NULL,
                reference_id INT NULL,
                reference_type VARCHAR(50) NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                
                INDEX idx_user_id_type (user_id, transaction_type),
                INDEX idx_created_at (created_at),
                INDEX idx_reference (reference_type, reference_id),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            // Table des abonnements
            "CREATE TABLE IF NOT EXISTS subscriptions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL UNIQUE,
                plan_type ENUM('pro', 'max') NOT NULL,
                status ENUM('active', 'cancelled', 'expired', 'suspended') DEFAULT 'active',
                started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                expires_at TIMESTAMP NOT NULL,
                auto_renew BOOLEAN DEFAULT TRUE,
                payment_method VARCHAR(50),
                stripe_subscription_id VARCHAR(100) UNIQUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                
                INDEX idx_user_id (user_id),
                INDEX idx_status_expires (status, expires_at),
                INDEX idx_stripe_id (stripe_subscription_id),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            // Table du livre d'or optimisée
            "CREATE TABLE IF NOT EXISTS guestbook_entries (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NULL,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(255) NULL,
                rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
                message TEXT NOT NULL,
                ip_address VARCHAR(45) NOT NULL,
                user_agent TEXT,
                status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
                moderated_by INT NULL,
                moderated_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                
                INDEX idx_status_created (status, created_at),
                INDEX idx_rating (rating),
                INDEX idx_user_id (user_id),
                INDEX idx_ip_address (ip_address),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
                FOREIGN KEY (moderated_by) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            // Table des logs de sécurité
            "CREATE TABLE IF NOT EXISTS security_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                event_type ENUM('login_attempt', 'login_success', 'login_failure', 'rate_limit_exceeded', 'suspicious_activity', 'file_quarantine') NOT NULL,
                user_id INT NULL,
                ip_address VARCHAR(45) NOT NULL,
                user_agent TEXT,
                details JSON,
                severity ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                
                INDEX idx_event_type_created (event_type, created_at),
                INDEX idx_user_id (user_id),
                INDEX idx_ip_address (ip_address),
                INDEX idx_severity_created (severity, created_at),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        ];
        
        foreach ($queries as $query) {
            $this->connection->exec($query);
        }
        
        $this->logOptimization('Tables optimisées créées/mises à jour');
    }
    
    /**
     * Crée les index pour optimiser les performances
     */
    private function createIndexes() {
        $indexes = [
            // Index composites pour les requêtes fréquentes
            "CREATE INDEX IF NOT EXISTS idx_users_plan_status ON users (plan_type, status)",
            "CREATE INDEX IF NOT EXISTS idx_users_referral_active ON users (referred_by, status) WHERE referred_by IS NOT NULL",
            "CREATE INDEX IF NOT EXISTS idx_conversions_user_date ON file_conversions (user_id, created_at)",
            "CREATE INDEX IF NOT EXISTS idx_conversions_format_status ON file_conversions (original_format, target_format, conversion_status)",
            "CREATE INDEX IF NOT EXISTS idx_credits_user_date ON credit_transactions (user_id, created_at DESC)",
            "CREATE INDEX IF NOT EXISTS idx_guestbook_status_rating ON guestbook_entries (status, rating, created_at)",
            "CREATE INDEX IF NOT EXISTS idx_security_logs_type_ip ON security_logs (event_type, ip_address, created_at)"
        ];
        
        foreach ($indexes as $index) {
            try {
                $this->connection->exec($index);
            } catch (PDOException $e) {
                // Ignorer si l'index existe déjà
                if (strpos($e->getMessage(), 'Duplicate key name') === false) {
                    throw $e;
                }
            }
        }
        
        $this->logOptimization('Index de performance créés');
    }
    
    /**
     * Optimise la configuration MySQL
     */
    private function optimizeConfiguration() {
        $optimizations = [
            // Configuration pour les performances
            "SET SESSION innodb_buffer_pool_size = '256M'",
            "SET SESSION query_cache_size = '64M'",
            "SET SESSION query_cache_type = ON",
            "SET SESSION tmp_table_size = '64M'",
            "SET SESSION max_heap_table_size = '64M'",
            "SET SESSION key_buffer_size = '32M'",
            "SET SESSION sort_buffer_size = '2M'",
            "SET SESSION read_buffer_size = '1M'",
            "SET SESSION read_rnd_buffer_size = '1M'",
            "SET SESSION join_buffer_size = '1M'"
        ];
        
        foreach ($optimizations as $optimization) {
            try {
                $this->connection->exec($optimization);
            } catch (PDOException $e) {
                // Certaines variables peuvent être en lecture seule
                continue;
            }
        }
        
        $this->logOptimization('Configuration MySQL optimisée');
    }
    
    /**
     * Configure le nettoyage automatique
     */
    private function setupAutomaticCleanup() {
        // Créer un événement MySQL pour le nettoyage automatique
        $cleanupEvent = "
            CREATE EVENT IF NOT EXISTS cleanup_old_data
            ON SCHEDULE EVERY 1 DAY
            STARTS CURRENT_TIMESTAMP
            DO BEGIN
                -- Nettoyer les sessions expirées
                DELETE FROM user_sessions WHERE expires_at < NOW();
                
                -- Nettoyer les conversions expirées
                DELETE FROM file_conversions WHERE expires_at < NOW() AND conversion_status = 'completed';
                
                -- Nettoyer les anciens logs de sécurité (garder 90 jours)
                DELETE FROM security_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY);
                
                -- Optimiser les tables après nettoyage
                OPTIMIZE TABLE user_sessions, file_conversions, security_logs;
            END
        ";
        
        try {
            $this->connection->exec("SET GLOBAL event_scheduler = ON");
            $this->connection->exec($cleanupEvent);
            $this->logOptimization('Nettoyage automatique configuré');
        } catch (PDOException $e) {
            $this->logOptimization('Impossible de configurer le nettoyage automatique: ' . $e->getMessage(), 'warning');
        }
    }
    
    /**
     * Analyse et optimise les requêtes lentes
     */
    public function analyzeSlowQueries() {
        $slowQueries = [];
        
        try {
            // Activer le log des requêtes lentes
            $this->connection->exec("SET GLOBAL slow_query_log = 'ON'");
            $this->connection->exec("SET GLOBAL long_query_time = 1");
            
            // Analyser les requêtes fréquentes avec EXPLAIN
            $commonQueries = [
                "SELECT * FROM users WHERE email = ? AND status = 'active'",
                "SELECT * FROM file_conversions WHERE user_id = ? ORDER BY created_at DESC LIMIT 10",
                "SELECT COUNT(*) FROM credit_transactions WHERE user_id = ? AND transaction_type = 'spent'",
                "SELECT * FROM guestbook_entries WHERE status = 'approved' ORDER BY created_at DESC LIMIT 20"
            ];
            
            foreach ($commonQueries as $query) {
                $explainQuery = "EXPLAIN " . str_replace('?', "'test'", $query);
                $stmt = $this->connection->query($explainQuery);
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($result as $row) {
                    if ($row['type'] === 'ALL' || $row['rows'] > 1000) {
                        $slowQueries[] = [
                            'query' => $query,
                            'issue' => 'Scan complet de table ou trop de lignes examinées',
                            'rows' => $row['rows'],
                            'type' => $row['type']
                        ];
                    }
                }
            }
            
        } catch (PDOException $e) {
            $this->logOptimization('Erreur lors de l\'analyse des requêtes: ' . $e->getMessage(), 'error');
        }
        
        return $slowQueries;
    }
    
    /**
     * Optimise une table spécifique
     */
    public function optimizeTable($tableName) {
        try {
            // Analyser la table
            $stmt = $this->connection->query("ANALYZE TABLE $tableName");
            $analyzeResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Optimiser la table
            $stmt = $this->connection->query("OPTIMIZE TABLE $tableName");
            $optimizeResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Obtenir les statistiques de la table
            $stmt = $this->connection->query("SHOW TABLE STATUS LIKE '$tableName'");
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $this->logOptimization("Table $tableName optimisée - Taille: " . $this->formatBytes($stats['Data_length']));
            
            return [
                'table' => $tableName,
                'analyze_result' => $analyzeResult,
                'optimize_result' => $optimizeResult,
                'stats' => $stats
            ];
            
        } catch (PDOException $e) {
            $this->logOptimization("Erreur lors de l'optimisation de $tableName: " . $e->getMessage(), 'error');
            return false;
        }
    }
    
    /**
     * Nettoie manuellement les données anciennes
     */
    public function cleanupOldData($days = 30) {
        $cleanupStats = [];
        
        try {
            // Nettoyer les sessions expirées
            $stmt = $this->connection->prepare("DELETE FROM user_sessions WHERE expires_at < NOW()");
            $stmt->execute();
            $cleanupStats['expired_sessions'] = $stmt->rowCount();
            
            // Nettoyer les conversions anciennes terminées
            $stmt = $this->connection->prepare("DELETE FROM file_conversions WHERE expires_at < NOW() AND conversion_status IN ('completed', 'failed')");
            $stmt->execute();
            $cleanupStats['old_conversions'] = $stmt->rowCount();
            
            // Nettoyer les anciens logs de sécurité
            $stmt = $this->connection->prepare("DELETE FROM security_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)");
            $stmt->execute([$days]);
            $cleanupStats['old_security_logs'] = $stmt->rowCount();
            
            // Nettoyer les anciennes entrées du livre d'or supprimées
            $stmt = $this->connection->prepare("DELETE FROM guestbook_entries WHERE status = 'rejected' AND created_at < DATE_SUB(NOW(), INTERVAL ? DAY)");
            $stmt->execute([$days * 2]); // Garder plus longtemps pour audit
            $cleanupStats['rejected_guestbook'] = $stmt->rowCount();
            
            $this->logOptimization("Nettoyage terminé: " . json_encode($cleanupStats));
            
        } catch (PDOException $e) {
            $this->logOptimization("Erreur lors du nettoyage: " . $e->getMessage(), 'error');
        }
        
        return $cleanupStats;
    }
    
    /**
     * Obtient les statistiques de performance de la base de données
     */
    public function getPerformanceStats() {
        $stats = [];
        
        try {
            // Statistiques générales
            $stmt = $this->connection->query("SHOW GLOBAL STATUS LIKE 'Questions'");
            $stats['total_queries'] = $stmt->fetch(PDO::FETCH_ASSOC)['Value'];
            
            $stmt = $this->connection->query("SHOW GLOBAL STATUS LIKE 'Uptime'");
            $uptime = $stmt->fetch(PDO::FETCH_ASSOC)['Value'];
            $stats['uptime_hours'] = round($uptime / 3600, 2);
            $stats['queries_per_second'] = round($stats['total_queries'] / $uptime, 2);
            
            // Statistiques InnoDB
            $stmt = $this->connection->query("SHOW GLOBAL STATUS LIKE 'Innodb_buffer_pool%'");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $stats['innodb'][$row['Variable_name']] = $row['Value'];
            }
            
            // Statistiques des tables
            $stmt = $this->connection->query("
                SELECT 
                    table_name,
                    table_rows,
                    data_length,
                    index_length,
                    (data_length + index_length) as total_size
                FROM information_schema.tables 
                WHERE table_schema = DATABASE()
                ORDER BY total_size DESC
            ");
            $stats['tables'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Statistiques de cache
            $stmt = $this->connection->query("SHOW GLOBAL STATUS LIKE 'Qcache%'");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $stats['query_cache'][$row['Variable_name']] = $row['Value'];
            }
            
        } catch (PDOException $e) {
            $this->logOptimization("Erreur lors de la récupération des statistiques: " . $e->getMessage(), 'error');
        }
        
        return $stats;
    }
    
    /**
     * Sauvegarde optimisée de la base de données
     */
    public function createOptimizedBackup($outputPath = null) {
        if (!$outputPath) {
            $outputPath = __DIR__ . '/../storage/backups/db_backup_' . date('Y-m-d_H-i-s') . '.sql';
        }
        
        $backupDir = dirname($outputPath);
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME;
            
            // Commandes mysqldump optimisées
            $command = sprintf(
                'mysqldump --host=%s --user=%s --password=%s --single-transaction --routines --triggers --add-drop-table --extended-insert --quick --lock-tables=false %s > %s',
                escapeshellarg(DB_HOST),
                escapeshellarg(DB_USER),
                escapeshellarg(DB_PASS),
                escapeshellarg(DB_NAME),
                escapeshellarg($outputPath)
            );
            
            exec($command, $output, $returnCode);
            
            if ($returnCode === 0 && file_exists($outputPath)) {
                $this->logOptimization("Sauvegarde créée: $outputPath (" . $this->formatBytes(filesize($outputPath)) . ")");
                return $outputPath;
            } else {
                throw new Exception("Échec de la création de la sauvegarde");
            }
            
        } catch (Exception $e) {
            $this->logOptimization("Erreur lors de la sauvegarde: " . $e->getMessage(), 'error');
            return false;
        }
    }
    
    /**
     * Formate les octets en format lisible
     */
    private function formatBytes($size, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = $size > 0 ? floor(log($size, 1024)) : 0;
        return number_format($size / pow(1024, $power), $precision, '.', ',') . ' ' . $units[$power];
    }
    
    /**
     * Enregistre les optimisations dans le log
     */
    private function logOptimization($message, $level = 'info') {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'level' => $level,
            'message' => $message
        ];
        
        $this->optimizationLog[] = $logEntry;
        
        // Écrire dans le fichier de log
        $logFile = __DIR__ . '/../storage/logs/db_optimization.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logLine = "[{$logEntry['timestamp']}] {$logEntry['level']}: {$logEntry['message']}\n";
        file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Obtient le log des optimisations
     */
    public function getOptimizationLog() {
        return $this->optimizationLog;
    }
    
    /**
     * Teste les performances avec une série de requêtes
     */
    public function runPerformanceTest() {
        $testResults = [];
        
        $testQueries = [
            'user_lookup' => "SELECT * FROM users WHERE email = 'test@example.com' AND status = 'active'",
            'user_conversions' => "SELECT * FROM file_conversions WHERE user_id = 1 ORDER BY created_at DESC LIMIT 10",
            'credit_balance' => "SELECT SUM(amount) FROM credit_transactions WHERE user_id = 1",
            'guestbook_recent' => "SELECT * FROM guestbook_entries WHERE status = 'approved' ORDER BY created_at DESC LIMIT 20",
            'security_logs' => "SELECT * FROM security_logs WHERE ip_address = '127.0.0.1' AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)"
        ];
        
        foreach ($testQueries as $testName => $query) {
            $startTime = microtime(true);
            
            try {
                $stmt = $this->connection->query($query);
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $endTime = microtime(true);
                
                $testResults[$testName] = [
                    'execution_time' => round(($endTime - $startTime) * 1000, 3), // ms
                    'rows_returned' => count($results),
                    'status' => 'success'
                ];
                
            } catch (PDOException $e) {
                $testResults[$testName] = [
                    'execution_time' => 0,
                    'rows_returned' => 0,
                    'status' => 'error',
                    'error' => $e->getMessage()
                ];
            }
        }
        
        $this->logOptimization("Test de performance terminé: " . json_encode($testResults));
        
        return $testResults;
    }
}

/**
 * Fonctions helper globales pour l'optimisation
 */
function optimize_database() {
    $optimizer = DatabaseOptimizer::getInstance();
    return $optimizer->initializeOptimizations();
}

function cleanup_old_data($days = 30) {
    $optimizer = DatabaseOptimizer::getInstance();
    return $optimizer->cleanupOldData($days);
}

function get_db_performance_stats() {
    $optimizer = DatabaseOptimizer::getInstance();
    return $optimizer->getPerformanceStats();
}

function run_db_performance_test() {
    $optimizer = DatabaseOptimizer::getInstance();
    return $optimizer->runPerformanceTest();
}
?>
