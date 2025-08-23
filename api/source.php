<?php
header('Content-Type: application/json');
include '_inc/confic.php';

$source_data = [];
$parts = [
    '/vid-src/bjax0.json',
    '/vid-src/bjax1.json',
    '/vid-src/bjax2.json',
    '/vid-src/bjax3.json',
    '/vid-src/bjax4.json'
];
foreach ($parts as $p) {
    $d = json_decode(file_get_contents($database_path . $p), true);
    if (json_last_error() === JSON_ERROR_NONE) {
        $source_data = array_merge($source_data, $d);
    }
}

echo json_encode($source_data);