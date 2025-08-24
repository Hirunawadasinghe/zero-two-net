<?php
header('Content-Type: application/json');
include $_SERVER['DOCUMENT_ROOT'] . '/_inc/confic.php';

$subtitle_data = json_decode(file_get_contents($database_path . '/subtitle.json'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    die(json_encode([]));
} else if (empty($_GET['id'])) {
    die(json_encode($subtitle_data));
}

include $_SERVER['DOCUMENT_ROOT'] . '/_inc/encrypt.php';
$sub_db_path = $_SERVER['DOCUMENT_ROOT'] . '/subtitle/';
$file_expire_time = 60 * 10;

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
        if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $sub_db_path . $sub['url'])) {
            continue;
        }
        $file_d = [];
        $files = glob($_SERVER['DOCUMENT_ROOT'] . $sub_db_path . $sub['url'] . '/*');
        foreach ($files as $file) {
            if (!in_array(pathinfo($file, PATHINFO_EXTENSION), ['srt', 'ass', 'ssa', 'sub', 'vtt', 'txt', 'dfxp', 'xml'])) {
                continue;
            }
            $t = explode('/', $file);
            $file_d[] = [
                'title' => $t[count($t) - 1],
                'date' => filectime($file),
                'url' => 'subtitles/download.php?f=' . encrypt_srt(json_encode([
                    'i' => $e['id'][0],
                    'p' => $sub['url'],
                    'f' => $t[count($t) - 1],
                    'l' => $sub['lan'],
                    't' => time() + $file_expire_time
                ]), $encryption_key),
                'downloads' => null
            ];
        }
        if (count($file_d) > 0) {
            usort($file_d, function ($a, $b) {
                return strnatcmp($b['title'], $a['title']);
            });
            $sub_data[] = [
                'author' => get_author($sub['auth']),
                'language' => isset($subtitle_data['iso_codes'][$sub['lan']]) ? $subtitle_data['iso_codes'][$sub['lan']] : $sub['lan'],
                'data' => $file_d
            ];
        }
    }
}