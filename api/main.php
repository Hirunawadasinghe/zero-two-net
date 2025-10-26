<?php
header('Content-Type: application/json');
include $_SERVER['DOCUMENT_ROOT'] . '/_inc/config.php';

$parts = [
    '/main/ajax0.json',
    '/main/ajax1.json',
    '/main/ajax2.json',
    '/main/ajax3.json'
];
$db = [];
foreach ($parts as $p) {
    $d = json_decode(file_get_contents($database_path . $p), true);
    if (json_last_error() === JSON_ERROR_NONE) {
        $db = array_merge($db, $d);
    }
}

$page = empty($_GET['page']) ? 1 : max($_GET['page'], 1);
$r = [];
for ($i = $page * $max_elements - $max_elements; $i < $max_elements * $page && $i < count($db); $i++) {
    $r['data'][] = $db[$i];
}
$r['next_page'] = $page < count($db) / $max_elements ? $page + 1 : false;

echo json_encode($r);
