<?php
include $_SERVER['DOCUMENT_ROOT'] . '/_config.php';
if (!isset($_GET['auth']) || $_GET['auth'] !== $cache_author_key) {
    echo 'error';
    exit;
}

header('Content-Type: application/json');
require_once 'api_cache.php';
$cache = new ApiCache();

if (isset($_GET['all']) && $_GET['all'] == 1) {
    $cache->clear();
    echo json_encode(['success' => true]);
} elseif (isset($_GET['key'])) {
    $cache->clear($_GET['key']);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}
?>