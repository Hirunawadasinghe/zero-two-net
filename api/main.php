<?php
header('Content-Type: application/json');
include '../_inc/config.php';

$parts = [
    '/main/ajax1.json',
    '/main/ajax0.json',
    '/main/ajax2.json'
];
$main_data = [];
foreach ($parts as $p) {
    $d = json_decode(file_get_contents($database_path . $p), true);
    if (json_last_error() === JSON_ERROR_NONE) {
        $main_data = array_merge($main_data, $d);
    }
}

echo json_encode($main_data);