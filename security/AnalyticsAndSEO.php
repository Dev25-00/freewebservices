<?php
/**
 * Système Google Analytics 4 et SEO pour mini-services.tech
 * Tracking avancé des conversions et optimisation SEO
 */

class AnalyticsAndSEO {
    private static $instance = null;
    private $gaTrackingId;
    private $gtagConfig;
    private $pageData;
    
    private function __construct() {
        $this->gaTrackingId = 'G-XXXXXXXXXX'; // À remplacer par votre ID
        $this->initializePageData();
        $this->setupGtagConfig();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Initialise les données de la page courante
     */
    private function initializePageData() {
        $currentPage = $_SERVER['REQUEST_URI'] ?? '/';
        $this->pageData = [
            'page_title' => $this->getPageTitle(),
            'page_url' => $currentPage,
            'page_type' => $this->getPageType($currentPage),
            'user_id' => $_SESSION['user_id'] ?? null,
            'user_plan' => $this->getUserPlan(),
            'timestamp' => time()
        ];
    }
    
    /**
     * Configure Google Analytics 4
     */
    private function setupGtagConfig() {
        $this->gtagConfig = [
            'page_title' => $this->pageData['page_title'],
            'page_location' => $this->getFullURL(),
            'content_group1' => $this->pageData['page_type'],
            'custom_map' => [
                'custom_parameter_1' => 'user_plan',
                'custom_parameter_2' => 'service_type'
            ]
        ];
        
        if ($this->pageData['user_id']) {
            $this->gtagConfig['user_id'] = $this->pageData['user_id'];
        }
    }
    
    /**
     * Génère le code Google Analytics 4
     */
    public function getAnalyticsCode() {
        $config = json_encode($this->gtagConfig);
        
        return <<<HTML
<!-- Google Analytics 4 -->
<script async src="https://www.googletagmanager.com/gtag/js?id={$this->gaTrackingId}"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  
  gtag('config', '{$this->gaTrackingId}', {$config});
  
  // Configuration personnalisée pour mini-services
  gtag('config', '{$this->gaTrackingId}', {
    custom_map: {'custom_parameter_1': 'user_plan'}
  });
  
  // Tracking des erreurs JavaScript
  window.addEventListener('error', function(e) {
    gtag('event', 'exception', {
      description: e.error.message + ' at ' + e.filename + ':' + e.lineno,
      fatal: false
    });
  });
  
  // Tracking du temps passé sur la page
  let startTime = Date.now();
  window.addEventListener('beforeunload', function() {
    let timeSpent = Math.round((Date.now() - startTime) / 1000);
    gtag('event', 'timing_complete', {
      name: 'page_read_time',
      value: timeSpent
    });
  });
</script>
HTML;
    }
    
    /**
     * Track une conversion de service
     */
    public function trackServiceConversion($serviceName, $conversionType, $value = null) {
        $eventData = [
            'service_name' => $serviceName,
            'conversion_type' => $conversionType,
            'user_plan' => $this->getUserPlan(),
            'timestamp' => date('c')
        ];
        
        if ($value !== null) {
            $eventData['value'] = $value;
        }
        
        // Enregistrer en base pour analyse
        $this->logConversionEvent($eventData);
        
        // JavaScript pour GA4
        return $this->generateGAEvent('conversion', $eventData);
    }
    
    /**
     * Track l'utilisation d'un service
     */
    public function trackServiceUsage($serviceName, $action, $additionalData = []) {
        $eventData = array_merge([
            'service_name' => $serviceName,
            'action' => $action,
            'user_plan' => $this->getUserPlan(),
            'user_id' => $this->pageData['user_id']
        ], $additionalData);
        
        return $this->generateGAEvent('service_usage', $eventData);
    }
    
    /**
     * Track les événements de monétisation
     */
    public function trackMonetizationEvent($eventType, $planType, $amount = null) {
        $eventData = [
            'event_type' => $eventType, // subscription_start, upgrade, payment, etc.
            'plan_type' => $planType,
            'currency' => 'EUR'
        ];
        
        if ($amount) {
            $eventData['value'] = $amount;
        }
        
        // Pour les achats, utiliser l'événement purchase de GA4
        if ($eventType === 'purchase') {
            return $this->generateGAEvent('purchase', [
                'transaction_id' => uniqid(),
                'value' => $amount,
                'currency' => 'EUR',
                'items' => [
                    [
                        'item_id' => $planType,
                        'item_name' => "Plan " . ucfirst($planType),
                        'category' => 'subscription',
                        'quantity' => 1,
                        'price' => $amount
                    ]
                ]
            ]);
        }
        
        return $this->generateGAEvent('monetization', $eventData);
    }
    
    /**
     * Génère le JavaScript pour un événement GA4
     */
    private function generateGAEvent($eventName, $eventData) {
        $dataJson = json_encode($eventData);
        
        return <<<JS
<script>
gtag('event', '{$eventName}', {$dataJson});
</script>
JS;
    }
    
    /**
     * Enregistre les événements en base de données
     */
    private function logConversionEvent($eventData) {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                INSERT INTO analytics_events (
                    user_id, event_type, event_data, created_at
                ) VALUES (?, 'conversion', ?, NOW())
            ");
            
            $stmt->execute([
                $this->pageData['user_id'],
                json_encode($eventData)
            ]);
            
        } catch (PDOException $e) {
            error_log("Erreur enregistrement analytics: " . $e->getMessage());
        }
    }
    
    /**
     * Génère les meta tags SEO optimisés
     */
    public function getSEOMetaTags() {
        $seoData = $this->getSEOData();
        
        $metaTags = <<<HTML
<!-- Meta SEO de base -->
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{$seoData['title']}</title>
<meta name="description" content="{$seoData['description']}">
<meta name="keywords" content="{$seoData['keywords']}">
<meta name="author" content="MultiServices">
<meta name="robots" content="{$seoData['robots']}">
<link rel="canonical" href="{$seoData['canonical_url']}">

<!-- Open Graph (Facebook) -->
<meta property="og:type" content="{$seoData['og_type']}">
<meta property="og:title" content="{$seoData['title']}">
<meta property="og:description" content="{$seoData['description']}">
<meta property="og:url" content="{$seoData['canonical_url']}">
<meta property="og:image" content="{$seoData['og_image']}">
<meta property="og:site_name" content="MultiServices">
<meta property="og:locale" content="fr_FR">

<!-- Twitter Cards -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{$seoData['title']}">
<meta name="twitter:description" content="{$seoData['description']}">
<meta name="twitter:image" content="{$seoData['og_image']}">
<meta name="twitter:site" content="@multiservices">

<!-- Schema.org JSON-LD -->
{$this->getSchemaMarkup()}

<!-- Preconnect pour optimiser les performances -->
<link rel="preconnect" href="https://www.googletagmanager.com">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://cdnjs.cloudflare.com">

<!-- Favicon et icônes -->
<link rel="icon" type="image/x-icon" href="/favicon.ico">
<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
<link rel="manifest" href="/site.webmanifest">

<!-- Meta tags supplémentaires pour le SEO -->
<meta name="theme-color" content="#667eea">
<meta name="msapplication-TileColor" content="#667eea">
<meta name="format-detection" content="telephone=no">
HTML;
        
        return $metaTags;
    }
    
    /**
     * Obtient les données SEO pour la page courante
     */
    private function getSEOData() {
        $pageType = $this->pageData['page_type'];
        $baseData = [
            'canonical_url' => $this->getFullURL(),
            'og_image' => $this->getBaseURL() . '/assets/images/og-image.jpg',
            'robots' => 'index, follow',
            'og_type' => 'website'
        ];
        
        switch ($pageType) {
            case 'home':
                return array_merge($baseData, [
                    'title' => 'MultiServices - Tous vos outils numériques en un seul endroit',
                    'description' => 'Convertissez vos fichiers, générez des hashtags, créez des QR codes, supprimez les fonds d\'images et trouvez des recettes. Tous vos outils essentiels gratuits et sécurisés.',
                    'keywords' => 'convertisseur fichier, générateur hashtag, QR code, suppression fond, recettes, outils gratuits, multiservices',
                ]);
                
            case 'file-converter':
                return array_merge($baseData, [
                    'title' => 'Convertisseur de Fichiers Gratuit - MultiServices',
                    'description' => 'Convertissez vos fichiers entre différents formats : PDF, Word, Excel, images, audio, vidéo. Conversion rapide et sécurisée sans inscription.',
                    'keywords' => 'convertisseur fichier, PDF Word, conversion gratuite, formats fichiers, convertir en ligne',
                ]);
                
            case 'hashtag-generator':
                return array_merge($baseData, [
                    'title' => 'Générateur de Hashtags Gratuit - MultiServices',
                    'description' => 'Générez des hashtags pertinents pour Instagram, Twitter, LinkedIn. Boostez votre visibilité sur les réseaux sociaux avec nos suggestions intelligentes.',
                    'keywords' => 'générateur hashtag, Instagram hashtags, Twitter, LinkedIn, réseaux sociaux, visibilité',
                ]);
                
            case 'qr-generator':
                return array_merge($baseData, [
                    'title' => 'Générateur QR Code Gratuit - MultiServices',
                    'description' => 'Créez des codes QR personnalisés pour vos liens, textes, contacts. Téléchargement instantané en haute qualité, sans limite.',
                    'keywords' => 'générateur QR code, code QR gratuit, créer QR code, QR personnalisé, télécharger QR',
                ]);
                
            case 'bg-remover':
                return array_merge($baseData, [
                    'title' => 'Suppresseur de Fond d\'Image Gratuit - MultiServices',
                    'description' => 'Supprimez automatiquement le fond de vos images grâce à l\'IA. Résultats professionnels en quelques secondes, parfait pour e-commerce.',
                    'keywords' => 'supprimer fond image, suppression arrière-plan, IA image, fond transparent, e-commerce',
                ]);
                
            case 'recipe-finder':
                return array_merge($baseData, [
                    'title' => 'Trouveur de Recettes par Ingrédients - MultiServices',
                    'description' => 'Découvrez des recettes délicieuses avec les ingrédients que vous avez. Cuisinez intelligent, évitez le gaspillage alimentaire.',
                    'keywords' => 'recettes ingrédients, trouveur recettes, cuisine, anti-gaspillage, idées repas',
                ]);
                
            case 'guestbook':
                return array_merge($baseData, [
                    'title' => 'Livre d\'Or - Partagez votre Expérience - MultiServices',
                    'description' => 'Partagez votre expérience avec MultiServices. Notez nos services et aidez la communauté à découvrir nos outils.',
                    'keywords' => 'livre or, avis clients, témoignages, évaluation services, retour expérience',
                ]);
                
            default:
                return array_merge($baseData, [
                    'title' => 'MultiServices - Outils Numériques Gratuits',
                    'description' => 'Découvrez notre collection d\'outils numériques gratuits pour simplifier votre quotidien numérique.',
                    'keywords' => 'outils gratuits, services numériques, multiservices, conversion fichiers',
                ]);
        }
    }
    
    /**
     * Génère le balisage Schema.org JSON-LD
     */
    private function getSchemaMarkup() {
        $pageType = $this->pageData['page_type'];
        $baseSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'WebApplication',
            'name' => 'MultiServices',
            'url' => $this->getBaseURL(),
            'description' => 'Plateforme d\'outils numériques gratuits : conversion de fichiers, génération de hashtags, création de QR codes et plus.',
            'applicationCategory' => 'Utility',
            'operatingSystem' => 'Web Browser',
            'offers' => [
                '@type' => 'Offer',
                'price' => '0',
                'priceCurrency' => 'EUR'
            ],
            'provider' => [
                '@type' => 'Organization',
                'name' => 'MultiServices',
                'url' => $this->getBaseURL()
            ]
        ];
        
        // Ajouter des schémas spécifiques selon la page
        if ($pageType === 'home') {
            $baseSchema['potentialAction'] = [
                [
                    '@type' => 'ConvertAction',
                    'name' => 'Convertir des fichiers',
                    'target' => $this->getBaseURL() . '/services/file-converter.php'
                ],
                [
                    '@type' => 'CreateAction',
                    'name' => 'Générer des hashtags',
                    'target' => $this->getBaseURL() . '/services/hashtag-generator.php'
                ]
            ];
        }
        
        if ($pageType === 'guestbook') {
            $reviewSchema = [
                '@context' => 'https://schema.org',
                '@type' => 'WebPage',
                'name' => 'Livre d\'Or MultiServices',
                'description' => 'Avis et témoignages des utilisateurs de MultiServices',
                'mainEntity' => [
                    '@type' => 'Review',
                    'itemReviewed' => [
                        '@type' => 'WebApplication',
                        'name' => 'MultiServices'
                    ]
                ]
            ];
            
            return '<script type="application/ld+json">' . json_encode([$baseSchema, $reviewSchema], JSON_UNESCAPED_SLASHES) . '</script>';
        }
        
        return '<script type="application/ld+json">' . json_encode($baseSchema, JSON_UNESCAPED_SLASHES) . '</script>';
    }
    
    /**
     * Génère le sitemap XML
     */
    public function generateSitemap() {
        $urls = [
            ['loc' => $this->getBaseURL(), 'priority' => '1.0', 'changefreq' => 'daily'],
            ['loc' => $this->getBaseURL() . '/services/file-converter.php', 'priority' => '0.9', 'changefreq' => 'weekly'],
            ['loc' => $this->getBaseURL() . '/services/hashtag-generator.php', 'priority' => '0.9', 'changefreq' => 'weekly'],
            ['loc' => $this->getBaseURL() . '/services/qr-generator.php', 'priority' => '0.9', 'changefreq' => 'weekly'],
            ['loc' => $this->getBaseURL() . '/services/bg-remover.php', 'priority' => '0.9', 'changefreq' => 'weekly'],
            ['loc' => $this->getBaseURL() . '/services/recipe-finder.php', 'priority' => '0.9', 'changefreq' => 'weekly'],
            ['loc' => $this->getBaseURL() . '/guestbook.php', 'priority' => '0.7', 'changefreq' => 'weekly'],
            ['loc' => $this->getBaseURL() . '/login.php', 'priority' => '0.5', 'changefreq' => 'monthly'],
            ['loc' => $this->getBaseURL() . '/register.php', 'priority' => '0.5', 'changefreq' => 'monthly']
        ];
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>{$url['loc']}</loc>\n";
            $xml .= "    <lastmod>" . date('Y-m-d') . "</lastmod>\n";
            $xml .= "    <changefreq>{$url['changefreq']}</changefreq>\n";
            $xml .= "    <priority>{$url['priority']}</priority>\n";
            $xml .= "  </url>\n";
        }
        
        $xml .= '</urlset>';
        
        return $xml;
    }
    
    /**
     * Génère le fichier robots.txt
     */
    public function generateRobotsTxt() {
        $robotsTxt = <<<TXT
User-agent: *
Allow: /

# Sitemap
Sitemap: {$this->getBaseURL()}/sitemap.xml

# Délai entre les requêtes (en secondes)
Crawl-delay: 1

# Fichiers et dossiers à ne pas indexer
Disallow: /storage/
Disallow: /config/
Disallow: /security/
Disallow: /api/
Disallow: /temp/
Disallow: /logs/

# Pages de gestion
Disallow: /admin/
Disallow: /login.php
Disallow: /register.php
Disallow: /logout.php

# Paramètres URL à ignorer
Disallow: /*?debug=
Disallow: /*?test=
Disallow: /*&debug=
Disallow: /*&test=
TXT;
        
        return $robotsTxt;
    }
    
    /**
     * Fonctions utilitaires
     */
    private function getPageTitle() {
        $page = basename($_SERVER['PHP_SELF'], '.php');
        $titles = [
            'index' => 'MultiServices - Tous vos outils numériques',
            'file-converter' => 'Convertisseur de Fichiers',
            'hashtag-generator' => 'Générateur de Hashtags',
            'qr-generator' => 'Générateur QR Code',
            'bg-remover' => 'Suppresseur de Fond',
            'recipe-finder' => 'Trouveur de Recettes',
            'guestbook' => 'Livre d\'Or',
            'login' => 'Connexion',
            'register' => 'Inscription'
        ];
        
        return $titles[$page] ?? 'MultiServices';
    }
    
    private function getPageType($url) {
        if ($url === '/' || strpos($url, 'index') !== false) {
            return 'home';
        }
        
        if (strpos($url, 'file-converter') !== false) return 'file-converter';
        if (strpos($url, 'hashtag-generator') !== false) return 'hashtag-generator';
        if (strpos($url, 'qr-generator') !== false) return 'qr-generator';
        if (strpos($url, 'bg-remover') !== false) return 'bg-remover';
        if (strpos($url, 'recipe-finder') !== false) return 'recipe-finder';
        if (strpos($url, 'guestbook') !== false) return 'guestbook';
        
        return 'other';
    }
    
    private function getUserPlan() {
        if (!isset($_SESSION['user_id'])) {
            return 'guest';
        }
        
        $user = getCurrentUser();
        return $user['plan_type'] ?? 'free';
    }
    
    private function getFullURL() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }
    
    private function getBaseURL() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        return $protocol . '://' . $_SERVER['HTTP_HOST'];
    }
    
    /**
     * Obtient les statistiques Analytics
     */
    public function getAnalyticsStats($days = 30) {
        try {
            $db = Database::getInstance()->getConnection();
            
            // Événements par service
            $stmt = $db->prepare("
                SELECT 
                    JSON_EXTRACT(event_data, '$.service_name') as service_name,
                    COUNT(*) as event_count
                FROM analytics_events 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                    AND event_type = 'conversion'
                GROUP BY service_name
                ORDER BY event_count DESC
            ");
            $stmt->execute([$days]);
            $serviceStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Conversions par jour
            $stmt = $db->prepare("
                SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as conversions
                FROM analytics_events 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                    AND event_type = 'conversion'
                GROUP BY DATE(created_at)
                ORDER BY date DESC
            ");
            $stmt->execute([$days]);
            $dailyStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'service_stats' => $serviceStats,
                'daily_stats' => $dailyStats,
                'total_events' => array_sum(array_column($serviceStats, 'event_count'))
            ];
            
        } catch (PDOException $e) {
            error_log("Erreur récupération stats analytics: " . $e->getMessage());
            return [];
        }
    }
}

/**
 * Fonctions helper globales
 */
function get_analytics_code() {
    $analytics = AnalyticsAndSEO::getInstance();
    return $analytics->getAnalyticsCode();
}

function get_seo_meta_tags() {
    $analytics = AnalyticsAndSEO::getInstance();
    return $analytics->getSEOMetaTags();
}

function track_service_conversion($serviceName, $conversionType, $value = null) {
    $analytics = AnalyticsAndSEO::getInstance();
    return $analytics->trackServiceConversion($serviceName, $conversionType, $value);
}

function track_service_usage($serviceName, $action, $additionalData = []) {
    $analytics = AnalyticsAndSEO::getInstance();
    return $analytics->trackServiceUsage($serviceName, $action, $additionalData);
}

function track_monetization_event($eventType, $planType, $amount = null) {
    $analytics = AnalyticsAndSEO::getInstance();
    return $analytics->trackMonetizationEvent($eventType, $planType, $amount);
}
?>
