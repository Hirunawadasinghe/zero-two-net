<?php
header('Content-Type: application/json');

if (empty($_GET['d']))
    die(json_encode(["status" => false, "msg" => "input data not found"]));

$inputs = json_decode($_GET['d'], true);
if (json_last_error() !== JSON_ERROR_NONE)
    die(json_encode(["status" => false, "msg" => "invalid input type"]));

include $_SERVER['DOCUMENT_ROOT'] . '/_inc/function.php';

$inputs = array_slice($inputs, 0, 5);
$limit = min(10, $_GET['limit'] ?? 10);

$response_data = format_element(get_suggestions($inputs, $limit, false), ['link', 'name', 'thumbnail_image', 'language', 'type', 'episodes', 'tags', 'subtitle']);

echo json_encode(['status' => true, 'data' => $response_data]);