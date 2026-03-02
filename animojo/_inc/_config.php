<?php
// rate limit
// include 'rate_limit.php';

$version = "1";

$database_path = $_SERVER['DOCUMENT_ROOT'] . "/database";
// $db_net_path = 'https://zero-two-net.vercel.app/api';
$db_net_path = 'http://localhost/_db-net/api';

$db_net_api_endpoints = [
    // ['path' => 'database', 'file' => 'main', 'ep' => $db_net_path . '/main', 'time' => 60 * 60 * 4], // 4 hours
    ['path' => 'database', 'file' => 'main', 'ep' => $db_net_path . '/main', 'time' => 10],
    // ['path' => 'database', 'file' => 'source', 'ep' => $db_net_path . '/source', 'time' => 60 * 60], // 1 hour
    ['path' => 'database', 'file' => 'source', 'ep' => $db_net_path . '/source', 'time' => 10],
    ['path' => 'database', 'file' => 'featured', 'ep' => $db_net_path . '/featured', 'time' => 60 * 60 * 24], // 1 day
    ['path' => 'database', 'file' => 'section', 'ep' => $db_net_path . '/section', 'time' => 60 * 60 * 24], // 1 day
    // ['path' => 'database', 'file' => 'subtitle', 'ep' => $db_net_path . '/subtitle', 'time' => 60 * 60 * 4] // 4 hours
    ['path' => 'database', 'file' => 'subtitle', 'ep' => $db_net_path . '/subtitle', 'time' => 10],
];

// for testing
for ($i = 0; $i < count($db_net_api_endpoints); $i++) {
    $db_net_api_endpoints[$i]['ep'] = $db_net_api_endpoints[$i]['ep'] . '.php';
}

$site_name = "AniMojo";
$site_alt_name = "animojo lk";
$stylish_url = "AniMojo.free.nf";
$site_url = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];

$meta_keywords = [
    "watch anime online",
    "anime site",
    "free anime",
    "anime to watch",
    "online anime",
    "anime streaming",
    "stream anime online",
    "english anime",
    "english dubbed anime",
    "animojo",
    "animojo lk",
    "anime sinhala",
    "sinhala subtitle"
];

$ad_link = [
    'video_popup' => 'https://otieu.com/4/9666604',
    'sub_download' => 'https://otieu.com/4/9458938'
];

$imdb_api_key = '217028fd';

$db_cache_encryption_key = 'm1_Fak1ng_bd';
$cache_author_key = 'Php_C@che_sY$tem-@uth0r_kei';
$encryption_key = 'Wh@t_tha_4k_i$_TomB0y_Me6n';

$advance_settings = [
    'bot_comment' => true,
    'tags' => [
        'adult_only' => ['ecchi', 'erotica', 'hentai'],
        'visibility_reduced' => ['ecchi', 'erotica', 'hentai', 'boys love', 'girls love']
    ]
];


// get user language
$preff_lang_post = $_POST['preferredLanguage'] ?? null;
if (in_array($preff_lang_post, ['si', 'en'], true)) {
    setcookie(
        'preferredLanguage',
        $preff_lang_post,
        [
            'expires' => time() + 86400 * 30, // 30 days
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Lax'
        ]
    );
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

$preff_lang = $_COOKIE['preferredLanguage'] ?? null;