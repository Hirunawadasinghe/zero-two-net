<?php
header('Content-Type: application/json');
include $_SERVER['DOCUMENT_ROOT'] . '/_inc/_config.php';

if (empty($_GET['text']))
    die(json_encode(['status' => false, 'msg' => 'text empty']));

$msg = json_decode($_GET['text']);
if (json_last_error() !== JSON_ERROR_NONE)
    die(json_encode(['status' => false, 'msg' => 'invalid input']));

$text = '';
foreach ($msg as $key => $value)
    $text .= "$key: $value\n";

$r = json_decode(@file_get_contents($db_net_path . '/post-msg?token=7878460921:AAFgb0tKfC886Gw3gbByzMRPmrDDz2tRmh8&chat=5922865116&text=' . rawurlencode($text)), true);
if (isset($r['status']) && $r['status']) {
    die(json_encode(['status' => true, 'msg' => 'message sent']));
} else {
    die(json_encode(['status' => false, 'msg' => 'error sending']));
}