<?php
header('Content-Type: application/json');

// Enable all error reporting (for debugging)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 🔍 Log to error log (Vercel shows these in `vercel logs`)
function debug_log($msg) {
    error_log('[DEBUG] ' . (is_array($msg) || is_object($msg) ? print_r($msg, true) : $msg));
}

// 🔹 Safer include (DOCUMENT_ROOT may not work on Vercel)
$inc_path = $_SERVER['DOCUMENT_ROOT'] . '/_inc/b2-function.php';
if (!file_exists($inc_path)) {
    die(json_encode(['status' => false, 'msg' => 'Include file not found', 'path' => $inc_path]));
}
include $inc_path;

// 🔹 Define database path safely
$database_path = $_SERVER['DOCUMENT_ROOT'] . '/data'; // change if your subtitle.json lives elsewhere
$file_path = $database_path . '/subtitle.json';

debug_log('Database path: ' . $database_path);
debug_log('Looking for: ' . $file_path);

// 🔹 Load subtitle database
if (!file_exists($file_path)) {
    die(json_encode(['status' => false, 'msg' => 'Database file not found', 'path' => $file_path]));
}

$subtitle_data = json_decode(file_get_contents($file_path), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    die(json_encode(['status' => false, 'msg' => 'Error parsing JSON: ' . json_last_error_msg()]));
}

// 🔹 Read query param
$selected_id = empty($_GET['id']) ? null : $_GET['id'];
debug_log('Selected ID: ' . $selected_id);

if (!$selected_id) {
    die(json_encode(['status' => true, 'msg' => 'No ID selected, returning full data', 'data' => $subtitle_data]));
}

// 🔹 AUTH & BUCKET
$auth = b2_authorize();
debug_log(['auth' => $auth]);

if (!$auth) {
    die(json_encode(['status' => false, 'msg' => 'b2 auth failed']));
}

$bucket_id = b2_get_bucket_id($auth['apiUrl'], $auth['authorizationToken']);
debug_log(['bucket_id' => $bucket_id]);

if (!$bucket_id) {
    die(json_encode(['status' => false, 'msg' => 'bucket not found']));
}

$download_url = $auth['downloadUrl'];
$auth_token = $auth['authorizationToken'];
$supported_ext = ['srt', 'ass', 'ssa', 'sub', 'vtt', 'txt', 'dfxp', 'xml'];

// 🔹 Helper: Get author
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
    // fixed check
    if ($e['id'] !== $selected_id) continue;

    debug_log(['matching_subtitle' => $e]);

    foreach ($e['sub'] as $sub) {
        $lang_folder = strtolower($subtitle_data['iso_codes'][$sub['lan']] ?? $sub['lan']) . '/' . $sub['url'];
        debug_log('Listing B2 files for folder: ' . $lang_folder);

        $files = b2_list_files($auth['apiUrl'], $auth_token, $bucket_id, $lang_folder);
        debug_log(['files_from_b2' => $files]);

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
            $sub_data[] = [
                'author' => get_author($sub['auth'], $subtitle_data),
                'lan_code' => $sub['lan'],
                'language' => $subtitle_data['iso_codes'][$sub['lan']] ?? $sub['lan'],
                'folder' => $sub['url'],
                'base' => '//' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/sub-dl?f=' . $lang_folder,
                'data' => $file_d
            ];
        } else {
            debug_log('No valid subtitle files found for: ' . $lang_folder);
        }
    }
}

debug_log(['final_sub_data' => $sub_data]);
echo json_encode(['status' => true, 'data' => $sub_data]);
