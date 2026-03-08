<?php
header('Content-Type: application/json');

if (empty($_GET['id']))
    die(json_encode(["status" => false, "msg" => "section id not specified"]));

require 'cache/api_cache.php';
$cache = handleApiCaching(60 * 60 * 24); // 1 day
$response_data = [];

include $_SERVER['DOCUMENT_ROOT'] . '/_inc/function.php';

$db = get_db_net_cache('database', 'section');
if (empty($db))
    die(json_encode(["status" => false, "msg" => "section database error"]));

foreach ($db as $itm) {
    if ((string) $itm['id'] === $_GET['id']) {
        foreach ($itm['element'] as $e) {
            $f = find_movie($e);
            if ($f)
                $response_data['data'][] = $f;
        }
        $response_data['data'] = format_element(array_slice($response_data['data'], 0, 10), ['movie_id', 'name', 'thumbnail_image', 'language', 'type', 'episodes', 'tags', 'link']);
        break;
    }
}

if (!isset($response_data['data'])) {
    $response = json_encode(["status" => false, "msg" => "api error"]);
} else {
    $response_data['status'] = true;
    $response = json_encode($response_data);
    $cache->set($cache->getCacheKey(), $response);
}
echo $response;