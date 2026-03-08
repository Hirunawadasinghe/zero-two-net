<?php
header('Content-Type: application/json');

require_once 'cache/api_cache.php';
$cache = handleApiCaching(60 * 60 * 12); // store cache for 1 day
$response_data = [];

include $_SERVER['DOCUMENT_ROOT'] . '/_inc/function.php';

// input attributes
// from -> starting point
// limit -> element limit

$start = isset($_GET['from']) ? ($_GET['from'] >= 0 ? $_GET['from'] : 0) : 0;
$limit = isset($_GET['limit']) ? $_GET['limit'] : 99999;

for ($i = $start; $i < $start + $limit && $i < count($main_data); $i++) {
    $response_data['data'][] = ['id' => format_title($main_data[$i]), 'name' => $main_data[$i]['name'], 'lan' => $main_data[$i]['language']];
}
usort($response_data['data'], function ($a, $b) {
    return strcmp($a['name'], $b['name']);
});

if ($response_data === []) {
    $response = json_encode(["status" => false, "msg" => "api error"]);
} else {
    $response_data['status'] = true;
    $response = json_encode($response_data);
    $cache->set($cache->getCacheKey(), $response);
}
echo $response;