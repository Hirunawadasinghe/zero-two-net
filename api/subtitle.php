<?php
header('Content-Type: application/json');
include '_inc/confic.php';

$d = json_decode(file_get_contents($database_path . '/subtitle.json'), true);
if (json_last_error() === JSON_ERROR_NONE) {
    echo json_encode($d);
} else {
    echo json_encode([]);
}
