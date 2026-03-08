<?php
header('Content-Type: application/json');

include $_SERVER['DOCUMENT_ROOT'] . '/_inc/_config.php';

$file = "$database_path/notice.json";

$d = json_decode(@file_get_contents($file), true);
if (json_last_error() !== JSON_ERROR_NONE)
    die(json_encode(['status' => false, 'msg' => 'no notice']));

echo json_encode([
    'data' => [
        'msg' => $d['msg'],
        'id' => filemtime($file)
    ],
    'status' => true
]);