<?php
header('Content-Type: application/json');

if (empty($_GET['timezone']))
    die(json_encode(["status" => false, "msg" => "timezone not included"]));
if (empty($_GET['date']))
    die(json_encode(["status" => false, "msg" => "date not included"]));

include $_SERVER['DOCUMENT_ROOT'] . '/_inc/schedule.php';
include $_SERVER['DOCUMENT_ROOT'] . '/_inc/function.php';

$response_data = [];

$d = get_schedule($tz = $_GET['timezone'], [$_GET['date']]);

foreach ($d[0]['data'] as $e) {
    $r = get_by_mal_id($e['mal_id']);

    if (!$r)
        continue;

    $t = format_title($r[0]);
    $response_data['data'][] = [
        'name' => $r[0]['name'],
        'episode' => $e['episode'],
        'time' => $e['time'],
        'link' => $_GET['date'] <= date("j") ? ("/watch/$t?episode=" . $e['episode']) : "/anime/$t"
    ];
}

if (!isset($response_data['data']))
    die(json_encode(["status" => false, "msg" => "no scheduled episodes"]));

if ($response_data === []) {
    $response = json_encode(["status" => false, "msg" => "api error"]);
} else {
    $response_data['status'] = true;
    $response = json_encode($response_data);
}

echo $response;