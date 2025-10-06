<?php
header('Content-Type: application/json');
include $_SERVER['DOCUMENT_ROOT'] . '/_inc/config.php';

$subtitle_data = json_decode(file_get_contents($database_path . '/subtitle.json'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    die(json_encode([]));
} else if (empty($_GET['id'])) {
    die(json_encode(['data' => $subtitle_data]));
}

include $_SERVER['DOCUMENT_ROOT'] . '/_inc/php-mega-nz-api-master/src/Mega.php';

// --- Configuration ---
$megaEmail = 'your_mega_email@example.com';
$megaPassword = 'your_mega_password';
$megaFolderId = 'YOUR_MEGA_FOLDER_ID'; // folder containing subtitle files

// --- Connect to MEGA ---
$mega = new Mega();
try {
    $mega->login($megaEmail, $megaPassword);
} catch (Exception $e) {
    die(json_encode(['error' => 'Failed to login to MEGA: ' . $e->getMessage()]));
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

// --- Build subtitle data from MEGA ---
$sub_data = [];
foreach ($subtitle_data['subtitles'] as $e) {
    if (!in_array($_GET['id'], $e['id'])) continue;

    foreach ($e['sub'] as $sub) {
        $folder = $mega->getFolder($megaFolderId . '/' . $sub['url']);
        $files = $folder->getFiles();
        if (!$files) continue;

        $file_d = [];
        foreach ($files as $file) {
            $ext = pathinfo($file->getName(), PATHINFO_EXTENSION);
            if (!in_array(strtolower($ext), ['srt', 'ass', 'ssa', 'sub', 'vtt', 'txt', 'dfxp', 'xml'])) continue;

            $file_d[] = [
                'date' => strtotime($file->getCreatedTime()), // MEGA file creation time
                'file' => $file->getName(),
                'url' => $file->getUrl() // public MEGA link
            ];
        }

        if (count($file_d) > 0) {
            usort($file_d, fn($a, $b) => strnatcmp($b['file'], $a['file']));
            $sub_data[] = [
                'author' => get_author($sub['auth']),
                'lan_code' => $sub['lan'],
                'language' => $subtitle_data['iso_codes'][$sub['lan']] ?? $sub['lan'],
                'base' => 'https://mega.nz/folder/', // MEGA base folder URL
                'folder' => $sub['url'],
                'data' => $file_d
            ];
        }
    }
}

echo json_encode(['data' => $sub_data]);
