<?php
// api/generate-hashtags.php
require_once '../config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['keywords']) || empty(trim($input['keywords']))) {
    echo json_encode(['success' => false, 'message' => 'Les mots-clés sont requis']);
    exit;
}

$keywords = sanitize($input['keywords']);
$category = isset($input['category']) ? sanitize($input['category']) : '';
$quantity = isset($input['quantity']) ? intval($input['quantity']) : 30;
$popularity = isset($input['popularity']) ? sanitize($input['popularity']) : 'mixed';

// Limiter la quantité
$quantity = max(10, min(50, $quantity));

try {
    $hashtags = generateHashtags($keywords, $category, $quantity, $popularity);
    
    echo json_encode([
        'success' => true,
        'hashtags' => $hashtags,
        'count' => count($hashtags)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la génération: ' . $e->getMessage()
    ]);
}

function generateHashtags($keywords, $category, $quantity, $popularity) {
    // Séparer les mots-clés
    $keywordArray = array_map('trim', explode(',', $keywords));
    $keywordArray = array_filter($keywordArray);
    
    // Base de données de hashtags par catégorie
    $hashtagDatabase = [
        'food' => [
            'popular' => ['#food', '#foodie', '#delicious', '#yummy', '#cooking', '#recipe', '#instafood', '#foodlover', '#homemade', '#tasty', '#foodporn', '#chef', '#cuisine', '#meal', '#dinner'],
            'moderate' => ['#foodblogger', '#foodphotography', '#foodstagram', '#foodgasm', '#foodaddict', '#foodart', '#foodculture', '#localfood', '#organicfood', '#healthyfood', '#comfortfood', '#streetfood', '#finedining', '#seasonal', '#fresh'],
            'niche' => ['#slowfood', '#plantbased', '#glutenfree', '#vegan', '#keto', '#paleo', '#rawfood', '#fermented', '#artisanal', '#farmtotable', '#zerowaste', '#mindfuleating', '#superfood', '#ancestraldiet', '#wholefood']
        ],
        'travel' => [
            'popular' => ['#travel', '#wanderlust', '#adventure', '#explore', '#vacation', '#trip', '#traveling', '#nature', '#photography', '#beautiful', '#landscape', '#journey', '#holiday', '#tourism', '#wanderer'],
            'moderate' => ['#backpacking', '#roadtrip', '#citybreak', '#mountains', '#beach', '#culture', '#heritage', '#local', '#authentic', '#discovery', '#expedition', '#nomad', '#explorer', '#globetrotter', '#passport'],
            'niche' => ['#offthebeatenpath', '#solotravel', '#sustainabletravel', '#ecotourism', '#culturalimmersion', '#slowtravel', '#microadventure', '#hiddengems', '#undiscovered', '#remoteplaces', '#wildernessadventure', '#digitalnomad', '#workation', '#minimalisttravel', '#conscioustravel']
        ],
        'fitness' => [
            'popular' => ['#fitness', '#workout', '#gym', '#health', '#motivation', '#training', '#exercise', '#strong', '#fit', '#healthy', '#lifestyle', '#bodybuilding', '#cardio', '#strength', '#wellness'],
            'moderate' => ['#fitnessjourney', '#transformation', '#muscle', '#gains', '#fitnessmotivation', '#personaltrainer', '#nutrition', '#supplements', '#recovery', '#endurance', '#flexibility', '#balance', '#mindfulness', '#selfcare', '#discipline'],
            'niche' => ['#calisthenics', '#crossfit', '#powerlifting', '#yoga', '#pilates', '#martialarts', '#swimming', '#running', '#cycling', '#hiking', '#climbing', '#functionaltraining', '#mobility', '#prehab', '#biohacking']
        ],
        'business' => [
            'popular' => ['#business', '#entrepreneur', '#success', '#marketing', '#innovation', '#leadership', '#startup', '#growth', '#professional', '#work', '#career', '#money', '#investing', '#finance', '#networking'],
            'moderate' => ['#smallbusiness', '#hustle', '#grind', '#motivation', '#goals', '#strategy', '#branding', '#sales', '#productivity', '#mindset', '#coaching', '#consulting', '#freelance', '#remotework', '#digitalmarketing'],
            'niche' => ['#solopreneur', '#bootstrapping', '#mvp', '#lean startup', '#growth hacking', '#b2b', '#saas', '#ecommerce', '#affiliate marketing', '#passive income', '#scalability', '#automation', '#ai business', '#blockchain', '#fintech']
        ],
        'technology' => [
            'popular' => ['#technology', '#tech', '#innovation', '#digital', '#coding', '#programming', '#AI', '#software', '#developer', '#future', '#startup', '#app', '#web', '#mobile', '#computer'],
            'moderate' => ['#artificialintelligence', '#machinelearning', '#blockchain', '#cryptocurrency', '#cybersecurity', '#cloud', '#iot', '#bigdata', '#automation', '#robotics', '#vr', '#ar', '#gaming', '#opensource', '#devlife'],
            'niche' => ['#deeplearning', '#neuralnetworks', '#quantumcomputing', '#edge computing', '#containerization', '#microservices', '#serverless', '#devops', '#kubernetes', '#terraform', '#rust', '#golang', '#webassembly', '#nft', '#defi']
        ],
        'youtube' => [
            'popular' => ['#youtube', '#youtuber', '#subscribe', '#content', '#creator', '#video', '#vlog', '#tutorial', '#review', '#entertainment', '#trending', '#viral', '#like', '#share', '#comment'],
            'moderate' => ['#youtubechannel', '#contentcreator', '#videomaker', '#youtubers', '#youtubevideo', '#youtubecommunity', '#youtubelife', '#createcontent', '#videoediting', '#filming', '#camera', '#editing', '#production', '#storytelling', '#audience'],
            'niche' => ['#smallyoutuber', '#youtubetips', '#youtubegrowth', '#youtubealgorithm', '#youtubeshorts', '#youtubepremiere', '#youtubethumbnail', '#youtubetags', '#youtubeseo', '#youtubemonetization', '#youtubepartner', '#youtubecreator', '#videoproduction', '#contentmarketing', '#digitalcreator']
        ],
        'lifestyle' => [
            'popular' => ['#lifestyle', '#inspiration', '#motivation', '#life', '#happy', '#love', '#mindfulness', '#wellness', '#selfcare', '#positivity', '#gratitude', '#mindset', '#peace', '#joy', '#balance'],
            'moderate' => ['#minimalism', '#sustainability', '#conscious living', '#slow living', '#simple life', '#intentional', '#authentic', '#self improvement', '#personal growth', '#meditation', '#spirituality', '#nature lover', '#eco friendly', '#zero waste', '#hygge'],
            'niche' => ['#lagom', '#ikigai', '#wabisabi', '#forest bathing', '#digital detox', '#mindful parenting', '#conscious consumption', '#upcycling', '#permaculture', '#biophilic', '#slow fashion', '#ethical living', '#plant based', '#holistic health', '#energy healing']
        ],
        'facebook' => [
            'popular' => ['#facebook', '#socialmedia', '#family', '#friends', '#memories', '#life', '#community', '#share', '#connect', '#together', '#celebration', '#moments', '#happiness', '#love', '#gratitude'],
            'moderate' => ['#facebookpost', '#socialnetwork', '#friendship', '#familytime', '#memories', '#nostalgia', '#reunion', '#milestone', '#achievement', '#announcement', '#celebration', '#community', '#local', '#neighborhood', '#events'],
            'niche' => ['#facebooklive', '#facebookstory', '#socialmediamarketing', '#facebookads', '#facebookpage', '#socialmediatips', '#digitalmarketing', '#facebookgroups', '#onlinecommunity', '#socialmediamanager', '#contentmarketing', '#engagement', '#reach', '#impressions', '#conversion']
        ],
        'pinterest' => [
            'popular' => ['#pinterest', '#pinit', '#inspiration', '#ideas', '#diy', '#design', '#decor', '#recipe', '#style', '#fashion', '#home', '#wedding', '#party', '#craft', '#tutorial'],
            'moderate' => ['#pinterestboard', '#pinterestpin', '#homedecor', '#interiordesign', '#homedesign', '#decorating', '#styling', '#organize', '#makeover', '#renovation', '#gardening', '#plants', '#flowers', '#outdoor', '#landscape'],
            'niche' => ['#pinterestmarketing', '#pinteresttips', '#pinterestseo', '#richpins', '#pinterestbusiness', '#pinterestads', '#pinterestgrowth', '#pintereststrategies', '#visualmarketing', '#brandawareness', '#pinterestanalytics', '#socialmediamarketing', '#contentcuration', '#brandvisibility', '#onlinemarketing']
        ]
    ];
    
    // Hashtags génériques basés sur les mots-clés
    $generatedHashtags = [];
    foreach ($keywordArray as $keyword) {
        $keyword = strtolower(trim($keyword));
        if (strlen($keyword) > 2) {
            $generatedHashtags[] = '#' . str_replace(' ', '', $keyword);
            // Ajouter des variantes
            $generatedHashtags[] = '#' . str_replace(' ', '', $keyword) . 'life';
            $generatedHashtags[] = '#' . str_replace(' ', '', $keyword) . 'lover';
            if (strlen($keyword) > 4) {
                $generatedHashtags[] = '#love' . str_replace(' ', '', $keyword);
            }
        }
    }
    
    // Hashtags de catégorie
    $categoryHashtags = [];
    if ($category && isset($hashtagDatabase[$category])) {
        $catData = $hashtagDatabase[$category];
        
        switch ($popularity) {
            case 'popular':
                $categoryHashtags = $catData['popular'];
                break;
            case 'moderate':
                $categoryHashtags = $catData['moderate'];
                break;
            case 'niche':
                $categoryHashtags = $catData['niche'];
                break;
            default: // mixed
                $categoryHashtags = array_merge(
                    $catData['popular'],
                    $catData['moderate'],
                    $catData['niche']
                );
        }
    }
    
    // Hashtags génériques populaires
    $genericHashtags = [
        '#photooftheday', '#instagood', '#picoftheday', '#amazing', '#awesome',
        '#beautiful', '#cool', '#fun', '#happy', '#love', '#nice', '#good',
        '#best', '#great', '#perfect', '#wonderful', '#fantastic', '#incredible',
        '#inspiring', '#creative', '#unique', '#special', '#memorable', '#unforgettable',
        '#moment', '#experience', '#adventure', '#journey', '#discovery', '#magic'
    ];
    
    // Combiner tous les hashtags
    $allHashtags = array_merge($generatedHashtags, $categoryHashtags, $genericHashtags);
    
    // Supprimer les doublons et mélanger
    $allHashtags = array_unique($allHashtags);
    shuffle($allHashtags);
    
    // Limiter à la quantité demandée
    $result = array_slice($allHashtags, 0, $quantity);
    
    // S'assurer qu'on a assez de hashtags
    if (count($result) < $quantity) {
        // Ajouter des hashtags supplémentaires générés
        $additional = generateAdditionalHashtags($keywordArray, $quantity - count($result));
        $result = array_merge($result, $additional);
    }
    
    return array_slice($result, 0, $quantity);
}

function generateAdditionalHashtags($keywords, $needed) {
    $additional = [];
    $prefixes = ['best', 'top', 'amazing', 'cool', 'awesome', 'great', 'perfect', 'ultimate', 'incredible', 'fantastic'];
    $suffixes = ['time', 'moment', 'experience', 'life', 'world', 'ever', 'today', 'now', 'vibes', 'goals'];
    
    foreach ($keywords as $keyword) {
        if (count($additional) >= $needed) break;
        
        $keyword = strtolower(str_replace(' ', '', trim($keyword)));
        if (strlen($keyword) > 2) {
            // Ajouter avec préfixes
            foreach ($prefixes as $prefix) {
                if (count($additional) >= $needed) break;
                $additional[] = '#' . $prefix . $keyword;
            }
            
            // Ajouter avec suffixes
            foreach ($suffixes as $suffix) {
                if (count($additional) >= $needed) break;
                $additional[] = '#' . $keyword . $suffix;
            }
        }
    }
    
    return $additional;
}
?>