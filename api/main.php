<?php
header('Content-Type: application/json');
include dirname(__DIR__, 1) . '/_inc/config.php';

$parts = [
    'main/0.json',
    'main/1.json',
    'main/2.json',
    'main/3.json'
];

$db = [];

foreach ($parts as $p) {
    $d = json_decode(@file_get_contents("$database_path/$p"), true);

    if (json_last_error() === JSON_ERROR_NONE) {
        // remove entry
        $d = array_values(array_filter($d, function ($e) {
            return isset($e['hide']) ? false : true;
        }));

        $db = array_merge($db, $d);
    }
}

$r = ['status' => true];
$page = empty($_GET['page']) ? 1 : max($_GET['page'], 1);

for ($i = $page * $max_elements - $max_elements; $i < $max_elements * $page && $i < count($db); $i++)
    $r['data'][] = $db[$i];

$r['next_page'] = $page < count($db) / $max_elements ? $page + 1 : false;

echo json_encode($r, JSON_UNESCAPED_UNICODE);