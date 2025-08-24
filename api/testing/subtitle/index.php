<?php
header('Content-Type: application/json');
include $_SERVER['DOCUMENT_ROOT'] . '/_db-net/_inc/config.php';

$db = json_decode(file_get_contents($database_path . '/subtitle.json'), true);
if (json_last_error() === JSON_ERROR_NONE) {
    echo json_encode($db);
} else {
    echo json_encode([]);
}