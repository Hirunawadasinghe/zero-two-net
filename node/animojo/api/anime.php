<?php
header('Content-Type: application/json');

$input = isset($_GET['d']) ? $_GET['d'] : false;
if (!$input) {
    die(json_encode(["status" => false, "msg" => "no data provided"]));
}
$input = json_decode($input);
if (json_last_error() !== JSON_ERROR_NONE) {
    die(json_encode(["status" => false, "msg" => "invalid input type"]));
}

$response_data = [];

include $_SERVER['DOCUMENT_ROOT'] . '/_inc/function.php';

$query = [];
parse_str($_SERVER['QUERY_STRING'], $q);
foreach ($q as $k => $v) {
    if ($v === '1') {
        $query[] = $k;
    }
}

for ($i = 0; $i < count($input) && $i < 100; $i++) {
    $f = find_movie($input[$i]);
    if ($f) {
        $d = [];
        $source = get_sources($f['movie_id']);
        foreach ($query as $q) {
            switch ($q) {
                case 'link':
                    $d[$q] = format_title($f);
                    break;
                case 'episodes':
                    $d[$q] = $source ? array_key_last($source) : '...';
                    break;
                case 'status':
                    $d[$q] = $source ? true : false;
                    break;
                default:
                    if (isset($f[$q])) {
                        $d[$q] = $f[$q];
                    }
                    break;
            }
        }
        $response_data['data'][] = $d;
    }
}

if ($response_data === []) {
    $response = json_encode(["status" => false, "msg" => "api error"]);
} else {
    $response_data['status'] = true;
    $response = json_encode($response_data);
}
echo $response;