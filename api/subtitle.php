<?php
header('Content-Type: application/json');
include $_SERVER['DOCUMENT_ROOT'] . '/_inc/b2-function.php';

$selected_id = empty($_GET['id']) ? null : $_GET['id'];
$subtitle_data = json_decode(file_get_contents($database_path . '/subtitle.json'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    die(json_encode(['status' => false, 'msg' => 'error loading database']));
} else if (!$selected_id) {
    die(json_encode(['status' => true, 'data' => $subtitle_data]));
}

// AUTH & BUCKET
$auth = b2_authorize();
if (!$auth)
    die(json_encode(['status' => false, 'msg' => 'b2 auth failed']));
$bucket_id = b2_get_bucket_id($auth['apiUrl'], $auth['authorizationToken']);
if (!$bucket_id)
    die(json_encode(['status' => false, 'msg' => 'bucket not found']));

$download_url = $auth['downloadUrl'];
$auth_token = $auth['authorizationToken'];
$supported_ext = ['srt', 'ass', 'ssa', 'sub', 'vtt', 'txt', 'dfxp', 'xml'];

function get_author($id, $subtitle_data)
{
    foreach ($subtitle_data['authors'] as $e) {
        if ($e['id'] === $id)
            return $e['name'];
    }
    return 'Unknown';
}

$sub_data = [];
foreach ($subtitle_data['subtitles'] as $e) {
    if (!in_array($selected_id, $e['id']))
        continue;

    foreach ($e['sub'] as $sub) {
        $lang_folder = strtolower($subtitle_data['iso_codes'][$sub['lan']]) . '/' . $sub['url'];
        $files = b2_list_files($auth['apiUrl'], $auth_token, $bucket_id, $lang_folder);

        $file_d = [];
        foreach ($files as $file) {
            $ext = pathinfo($file['fileName'], PATHINFO_EXTENSION);
            if (!in_array(strtolower($ext), $supported_ext))
                continue;

            // Build server-side download URL
            $file_d[] = [
                'date' => $file['uploadTimestamp'],
                'file' => basename($file['fileName'])
            ];
        }

        if (count($file_d) > 0) {
            usort($file_d, fn($a, $b) => strnatcmp($b['file'], $a['file']));
            $sub_data[] = [
                'author' => get_author($sub['auth'], $subtitle_data),
                'lan_code' => $sub['lan'],
                'language' => $subtitle_data['iso_codes'][$sub['lan']] ?? $sub['lan'],
                'folder' => $sub['url'],
                'base' => '//' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/sub-dl?f=' . $lang_folder,
                'data' => $file_d
            ];
        }
    }
}

echo json_encode(['status' => true, 'data' => $sub_data]);
