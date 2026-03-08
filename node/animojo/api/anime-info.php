<?php
header('Content-Type: application/json');

$id = !empty($_GET['id']) ? $_GET['id'] : null;
if (!$id) {
    die(json_encode(["status" => false, "msg" => "id not specified"]));
}

include $_SERVER['DOCUMENT_ROOT'] . '/_inc/function.php';
$e = find_movie($id);
if (!$e) {
    die(json_encode(["status" => false, "msg" => "invalid id"]));
}

require 'cache/api_cache.php';
$cache = handleApiCaching(60 * 60 * 12); // 1 day
$response_data = [];

include $_SERVER['DOCUMENT_ROOT'] . '/_inc/_config.php';

$d = json_decode(@file_get_contents('https://www.omdbapi.com/?t=' . rawurlencode($e['name']) . '&apikey=' . $imdb_api_key), true);
if ($d && $d['Response'] === "True") {
    $response_data['data'] = [
        'Year' => $d['Year'] ?? 'N/A',
        'Rated' => $d['Rated'] ?? 'N/A',
        'Runtime' => $d['Runtime'] ?? 'N/A',
        'Seasons' => $d['totalSeasons'] ?? 'N/A',
        'Director' => $d['Director'] ?? 'N/A',
        'Writer' => $d['Writer'] ?? 'N/A',
        'Actors' => $d['Actors'] ?? 'N/A',
        'Country' => $d['Country'] ?? 'N/A',
        'Language' => $d['Language'] ?? 'N/A',
        'Awards' => $d['Awards'] ?? 'N/A',
        'Meta Score' => $d['Metascore'] ?? 'N/A',
        'IMDB Votes' => $d['imdbVotes'] ?? 'N/A',
        'IMDB_Rating' => $d['imdbRating'] ?? 'N/A',
        'DVD' => $d['DVD'] ?? 'N/A',
        'BoxOffice' => $d['BoxOffice'] ?? 'N/A',
        'Production' => $d['Production'] ?? 'N/A',
        'Website' => $d['Website'] ?? 'N/A'
    ];
}

if ($response_data === []) {
    $response = json_encode(["status" => false, "msg" => "api error"]);
} else {
    $response_data['status'] = true;
    $response = json_encode($response_data);
    $cache->set($cache->getCacheKey(), $response);
}
echo $response;