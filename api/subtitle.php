<?php
header('Content-Type: application/json');
include dirname(__DIR__, 1) . '/_inc/b2-function.php';

$subtitle_data = json_decode(@file_get_contents("$database_path/subtitle.json"), true);
if (json_last_error() !== JSON_ERROR_NONE)
    die(json_encode(['status' => false, 'msg' => 'error loading database']));

$selected_id = empty($_GET['id']) ? null : $_GET['id'];
if (!$selected_id)
    die(json_encode(['status' => true, 'data' => $subtitle_data]));

$auth = b2_authorize();
if (!$auth)
    die(json_encode(['status' => false, 'msg' => 'b2 auth failed']));

function sendTelegramMessage($botToken, $chatId, $message)
{
    $context = stream_context_create([
        'http' => [
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query([
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML'
            ]),
        ]
    ]);

    $result = file_get_contents("https://api.telegram.org/bot$botToken/sendMessage", false, $context);
    return $result ? json_decode($result, true) : false;
}

$download_url = $auth['downloadUrl'];
$auth_token = $auth['authorizationToken'];
$supported_ext = ['srt', 'ass', 'ssa', 'sub', 'idx', 'vtt', 'txt'];

$result = [];

foreach ($subtitle_data['subtitles'] as $e) {
    if (!in_array($selected_id, $e['id']))
        continue;

    foreach ($e['sub'] as $sub) {
        $folder_path = strtolower($subtitle_data['iso_codes'][$sub['lan']]) . '/' . $sub['url'];

        $files = b2_list_files($auth['apiUrl'], $auth_token, $folder_path);
        $result['entry'][$sub['lan']] = $files ? true : false;
        if (!$files) {
            sendTelegramMessage('7828957014:AAGC07fKmYcpiHNT_9UV1qTVy6YIQ1N5O7Q', '5922865116', "subtitle listing error: $folder_path");
            continue;
        }

        $file_d = [];
        foreach ($files as $file) {
            $ext = pathinfo($file['fileName'], PATHINFO_EXTENSION);
            if (!in_array(strtolower($ext), $supported_ext))
                continue;

            $file_d[] = [
                'date' => $file['uploadTimestamp'],
                'file' => basename($file['fileName'])
            ];
        }

        if (count($file_d) > 0) {
            usort($file_d, fn($a, $b) => strnatcmp($b['file'], $a['file']));
            $result['data'][] = [
                'author' => $subtitle_data['author'][$sub['auth']] ?? 'Unknown',
                'lan_code' => $sub['lan'],
                'language' => $subtitle_data['iso_codes'][$sub['lan']] ?? $sub['lan'],
                'folder' => $sub['url'],
                'base' => dirname('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) . '/sub-dl?f=' . $folder_path,
                'data' => $file_d
            ];
        }
    }
    break;
}

$result['status'] = isset($result['data']);
if (!isset($result['data']))
    $result['msg'] = 'no subtitle files found in the directory';


echo json_encode($result);
