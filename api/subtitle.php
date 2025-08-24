<?php
header('Content-Type: application/json');
include '_inc/confic.php';

$db = json_decode(file_get_contents($database_path . '/subtitle.json'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    die(json_encode([]));
} else if (empty($_GET['id'])) {
    die(json_encode($db));
}

$files = scandir($_SERVER['DOCUMENT_ROOT'] . '/subtitle');

$filesArray = [];
foreach ($files as $file) {
    if ($file !== '.' && $file !== '..') { // skip current and parent directory
        $filesArray[] = $file;
    }
}

echo json_encode($filesArray);