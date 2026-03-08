<?php
header('Content-Type: application/json');

if (empty($_GET['m'])) {
    die(json_encode(['error' => 'method not specified']));
}
if (!in_array($_GET['m'], ['main', 'metaphone'])) {
    die(json_encode(['error' => 'invalid method']));
}

$response_data = [];

include $_SERVER['DOCUMENT_ROOT'] . '/_inc/function.php';

if ($_GET['m'] === 'main') {
    $r = search(getAllQueryParams(), 'main', $main_data);
    $response_data['data'] = format_element(filter_duplicates($r), ['link', 'name', 'alt_name', 'thumbnail_image', 'language', 'type', 'episodes', 'tags']);
} else {
    $r = search($_GET['text'], 'metaphone', $main_data);
    $response_data['data'] = format_element(filter_duplicates(array_slice($r, 0, 5)), ['link', 'name', 'alt_name', 'thumbnail_image', 'language', 'type', 'episodes', 'release_year']);
}

if ($response_data === []) {
    $response = ["status" => false, "msg" => "api error"];
} else {
    $response_data['status'] = true;
    $response = $response_data;
}
echo json_encode($response);