<?php
// includes/page-helper.php
// Fonctions d'aide pour l'intégration des pages avec le système centralisé

/**
 * Initialise une page avec les paramètres par défaut
 */
function initializePage($pageConfig = []) {
    // Configuration par défaut
    global $page_title, $page_description, $page_keywords, $body_class, $additional_css, $additional_js;
    
    $page_title = $pageConfig['title'] ?? 'Mini Services - Tous vos outils numériques';
    $page_description = $pageConfig['description'] ?? 'Mini Services - Tous vos outils numériques favoris réunis en un seul endroit. Convertisseur, générateur de hashtags, QR codes et plus encore.';
    $page_keywords = $pageConfig['keywords'] ?? 'convertisseur fichiers, hashtags, QR code, suppression fond, recettes, outils en ligne, gratuit';
    $body_class = $pageConfig['body_class'] ?? '';
    $additional_css = $pageConfig['additional_css'] ?? [];
    $additional_js = $pageConfig['additional_js'] ?? [];
    
    // Ajouter des CSS/JS spécifiques selon le type de page
    if (isset($pageConfig['page_type'])) {
        switch ($pageConfig['page_type']) {
            case 'auth':
                $additional_css[] = 'assets/css/auth.css';
                break;
            case 'service':
                $additional_css[] = 'assets/css/service.css';
                break;
            case 'profile':
                $additional_css[] = 'assets/css/profile.css';
                break;
        }
    }
}

/**
 * Inclut le header avec les bonnes configurations
 */
function includeHeader($pageConfig = []) {
    initializePage($pageConfig);
    require_once __DIR__ . '/header.php';
}

/**
 * Inclut le footer
 */
function includeFooter() {
    require_once __DIR__ . '/footer.php';
}

/**
 * Crée une page complète avec header et footer
 */
function createPage($pageConfig, $contentCallback) {
    includeHeader($pageConfig);
    
    if (is_callable($contentCallback)) {
        $contentCallback();
    } elseif (is_string($contentCallback)) {
        echo $contentCallback;
    }
    
    includeFooter();
}

/**
 * Démarre le contenu principal d'une page
 */
function startMainContent($classes = '') {
    echo '<main class="main-content ' . htmlspecialchars($classes) . '">';
}

/**
 * Ferme le contenu principal d'une page
 */
function endMainContent() {
    echo '</main>';
}
?>