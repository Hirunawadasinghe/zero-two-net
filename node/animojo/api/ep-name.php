<?php
header('Content-Type: application/json');

if (empty($_GET['id'])) {
    die(json_encode(["status" => false, "msg" => "id not specified"]));
}

include $_SERVER['DOCUMENT_ROOT'] . '/_inc/function.php';

$f = find_movie($_GET['id']);
if (!$f) {
    die(json_encode(["status" => false, "msg" => "invalid id"]));
}
$id = $f['mal_id'];
if (!$id) {
    die(json_encode(["status" => false, "msg" => "no data"]));
}

require_once 'cache/api_cache.php';
$cache = handleApiCaching(60 * 60 * 24);
$response_data = [];

$page = 1;
do {
    $response = @file_get_contents("https://api.jikan.moe/v4/anime/{$id}/episodes?page={$page}");
    if (!$response) {
        die(json_encode(["status" => false, "msg" => "source api error"]));
    }
    $data = json_decode($response, true);
    if (isset($data['data'])) {
        foreach ($data['data'] as $e) {
            $response_data['data'][$e['mal_id']] = ($e['title'] ?? null);
        }
        $hasNextPage = $data['pagination']['has_next_page'];
        $page++;
    } else {
        $hasNextPage = false;
    }
    sleep(0.5);
} while ($hasNextPage);

if ($response_data === []) {
    $response = json_encode(["status" => false, "msg" => "api error"]);
} else {
    $response_data['status'] = true;
    $response = json_encode($response_data);
    $cache->set($cache->getCacheKey(), $response);
}

echo $response;