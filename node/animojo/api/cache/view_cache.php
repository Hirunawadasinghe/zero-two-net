<?php
include $_SERVER['DOCUMENT_ROOT'] . '/_config.php';
if (!isset($_GET['auth']) || $_GET['auth'] !== $cache_author_key) {
    echo 'error';
    exit;
}

require_once 'api_cache.php';
$cache = new ApiCache();

if (!isset($_GET['key'])) {
    die('Cache key not specified');
}

$key = $_GET['key'];
$content = $cache->get($key);

if ($content === false) {
    die('Cache item not found or expired');
}

// Try to detect if content is JSON
$isJson = json_decode($content) !== null;

header('Content-Type: ' . ($isJson ? 'application/json' : 'text/plain'));
echo $isJson ? json_encode(json_decode($content), JSON_PRETTY_PRINT) : $content;
?>