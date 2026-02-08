<?php
header('Content-Type: application/json');

if (empty($_GET['token']))
    die(json_encode(['status' => false, 'msg' => 'api token not included']));
if (empty($_GET['chat']))
    die(json_encode(['status' => false, 'msg' => 'chat not specified']));
if (empty($_GET['text']))
    die(json_encode(['status' => false, 'msg' => 'text empty']));

$r = json_decode(@file_get_contents('https://api.telegram.org/bot' . $_GET['token'] . '/sendMessage?chat_id=' . $_GET['chat'] . '&text=' . urlencode($_GET['text'])), true);

if (isset($r['ok']) && $r['ok']) {
    echo json_encode(['status' => true, 'msg' => 'message sent', 'result' => $r]);
} else {
    echo json_encode(['status' => false, 'msg' => 'error sending']);
}