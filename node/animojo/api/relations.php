<?php
header('Content-Type: application/json');

$id = empty($_GET['id']) ? null : $_GET['id'];
$limit = empty($_GET['limit']) ? null : $_GET['limit'];

if (!$id)
    die(json_encode(['status' => false, 'msg' => 'id is missing']));

require 'cache/api_cache.php';
$cache = handleApiCaching(86400); // 1 day
$response_data = [];

include $_SERVER['DOCUMENT_ROOT'] . '/_inc/function.php';

$entry = find_movie($id);
if (!$entry)
    die(json_encode(['status' => false, 'msg' => 'invalid id']));

$query = '
query ($malId: Int) {
  Media(idMal: $malId, type: ANIME) {
    relations {
      edges {
        relationType
        node {
          idMal
        }
      }
    }
  }
}';

$payload = json_encode([
    'query' => $query,
    'variables' => ['malId' => (int) $entry['mal_id']]
]);

$ch = curl_init("https://graphql.anilist.co");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
$anilistRes = curl_exec($ch);
curl_close($ch);

if (!$anilistRes)
    die(json_encode(['status' => false, 'msg' => 'api error']));

$anilist = json_decode($anilistRes, true);

if (!isset($anilist['data']['Media']['relations']['edges']))
    die(json_encode(['status' => false, 'msg' => 'api error']));

$id_hash_map = [];
foreach ($main_data as $e) {
    $mal_id = $e['mal_id'];
    if (!isset($id_hash_map[$mal_id]) || $entry['language'] === $e['language'])
        $id_hash_map[$mal_id] = $e;
}

$data = [];
foreach ($anilist['data']['Media']['relations']['edges'] as $edge) {
    $relMal = $edge['node']['idMal'] ?? null;
    if ($relMal && isset($id_hash_map[$relMal]))
        $data[] = $id_hash_map[$relMal];
}

$response_data['data'] = format_element(
    $data,
    ['link', 'name', 'thumbnail_image', 'language', 'type', 'episodes', 'tags']
);

if (empty($response_data['data'])) {
    $response = json_encode(['status' => false, 'msg' => 'api error']);
} else {
    $response_data['status'] = true;
    $response = json_encode($response_data);
    $cache->set($cache->getCacheKey(), $response);
}

echo $response;