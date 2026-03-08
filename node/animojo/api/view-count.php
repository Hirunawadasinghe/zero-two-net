<?php
if (!isset($_SERVER['HTTP_REFERER']) || strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) === false) {
    http_response_code(403);
    exit('<h1>Forbidden</h1>');
}

header('Content-Type: application/json');

$page_id = !empty($_GET['id']) ? $_GET['id'] : null;
if (!$page_id) {
    die(json_encode(["status" => false, "msg" => "id not specified"]));
}
if (empty($_GET['ep'])) {
    die(json_encode(["status" => false, "msg" => "episode not specified"]));
}

$response_data = [];

include $_SERVER['DOCUMENT_ROOT'] . '/_inc/function.php';

$element = find_movie($page_id);
if (!$element) {
    die(json_encode(["status" => false, "msg" => "invalid id"]));
}

$response_data['data'] = update_ep_views($page_id, $_GET['ep']);

if ($response_data === []) {
    $response = json_encode(["status" => false, "msg" => "api error"]);
} else {
    $response_data['status'] = true;
    $response = json_encode($response_data);
}
echo $response;