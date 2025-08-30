<?php
header('Content-Type: application/json');
include $_SERVER['DOCUMENT_ROOT'] . '/_inc/config.php';

$subtitle_data = json_decode(file_get_contents($database_path . '/subtitle.json'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    die(json_encode([]));
} else if (empty($_GET['id'])) {
    die(json_encode(['data' => $subtitle_data]));
}

$sub_db_path = '/subtitle/';

function get_author($id)
{
    global $subtitle_data;
    foreach ($subtitle_data['authors'] as $e) {
        if ($e['id'] === $id) {
            return $e['name'];
        }
    }
    return 'Unknown';
}

$sub_data = [];
foreach ($subtitle_data['subtitles'] as $e) {
    if (!in_array($_GET['id'], $e['id'])) {
        continue;
    }
    foreach ($e['sub'] as $sub) {
        $folder_path = $_SERVER['DOCUMENT_ROOT'] . '/_db-net/' . $sub_db_path . $sub['url'];
        if (!file_exists($folder_path)) {
            continue;
        }
        $file_d = [];
        $files = glob($folder_path . '/*');
        foreach ($files as $file) {
            if (!in_array(pathinfo($file, PATHINFO_EXTENSION), ['srt', 'ass', 'ssa', 'sub', 'vtt', 'txt', 'dfxp', 'xml'])) {
                continue;
            }
            $t = explode('/', $file);
            $file_d[] = [
                'date' => filectime($file),
                'file' => basename($file)
            ];
        }
        if (count($file_d) > 0) {
            usort($file_d, function ($a, $b) {
                return strnatcmp($b['file'], $a['file']);
            });
            $sub_data[] = [
                'author' => get_author($sub['auth']),
                'lan_code' => $sub['lan'],
                'language' => isset($subtitle_data['iso_codes'][$sub['lan']]) ? $subtitle_data['iso_codes'][$sub['lan']] : $sub['lan'],
                'base' => 'http://' . $_SERVER['HTTP_HOST'] . $sub_db_path,
                'folder' => $sub['url'],
                'data' => $file_d
            ];
        }
    }
}

echo json_encode(['data' => $sub_data]);
