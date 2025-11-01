<?php
header('Content-Type: application/json');
include dirname(__DIR__, 1) . '/_inc/config.php';

$db = json_decode(file_get_contents($database_path . '/featured.json'), true);
if (json_last_error() === JSON_ERROR_NONE) {
    echo json_encode(['status' => true, 'data' => $db]);
} else {
    echo json_encode(['status' => false, 'msg' => 'error loading database']);
}