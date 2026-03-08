<?php
header('Content-Type: application/json');

$id = empty($_GET['id']) ? null : $_GET['id'];
if (!$id)
  die(json_encode(['status' => false, 'msg' => 'id is missing']));

require_once 'cache/api_cache.php';
$cache = handleApiCaching(60 * 60 * 24 * 7); // 7 days
$response_data = [];

include $_SERVER['DOCUMENT_ROOT'] . '/_inc/function.php';

$entry = find_movie($id);
if (!$entry)
  die(json_encode(['status' => false, 'msg' => 'invalid id']));
if (!$entry['mal_id'])
  die(json_encode(['status' => false, 'msg' => 'no data']));

$query = '
query ($id: Int) {
  Media(idMal: $id, type: ANIME) {
    characters {
      edges {
        role
        node {
          name {
            full
          }
          image {
            medium
          }
        }
        voiceActors {
          name {
            full
          }
          language
          image {
            medium
          }
        }
      }
    }
  }
}';

$ch = curl_init('https://graphql.anilist.co');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['query' => $query, 'variables' => ['id' => $entry['mal_id']]]));

$r = curl_exec($ch);
if (curl_errno($ch))
  die('Request Error: ' . curl_error($ch));
curl_close($ch);

$data = json_decode($r, true);

if (!isset($data['data']['Media']['characters']['edges']))
  die(json_encode(['status' => false, 'msg' => 'no character info found']));

foreach ($data['data']['Media']['characters']['edges'] as $edge) {
  $character = [
    'name' => $edge['node']['name']['full'],
    'role' => ucfirst(strtolower($edge['role'])),
    'image' => $edge['node']['image']['medium'] ?? null,
    'voice_actors' => []
  ];

  if (!empty($edge['voiceActors'])) {
    foreach ($edge['voiceActors'] as $va) {
      $character['voice_actors'][] = [
        'name' => $va['name']['full'],
        'language' => ucfirst(strtolower($va['language'])),
        'image' => $va['image']['medium'] ?? null
      ];
    }
  }

  $response_data['data'][] = $character;
}

if ($response_data === []) {
  $response = json_encode(["status" => false, "msg" => "api error"]);
} else {
  $response_data['status'] = true;
  $response = json_encode($response_data);
  $cache->set($cache->getCacheKey(), $response);
}

echo $response;