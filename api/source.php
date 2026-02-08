<?php
header('Content-Type: application/json');
include dirname(__DIR__, 1) . '/_inc/config.php';

$parts = [
    'vid-src/0.json',
    'vid-src/1.json',
    'vid-src/2.json',
    'vid-src/3.json',
    'vid-src/4.json'
];

$db = [];

foreach ($parts as $p) {
    $d = json_decode(@file_get_contents("$database_path/$p"), true);

    if (json_last_error() === JSON_ERROR_NONE)
        $db = array_merge($db, $d);
}

$r = ['status' => true];
$page = empty($_GET['page']) ? 1 : max($_GET['page'], 1);

for ($i = $page * $max_elements - $max_elements; $i < $max_elements * $page && $i < count($db); $i++)
    $r['data'][] = $db[$i];

$r['next_page'] = $page < count($db) / $max_elements ? $page + 1 : false;

echo json_encode($r);