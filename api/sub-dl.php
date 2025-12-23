<?php
header('Content-Type: application/json');

$file_path = $_GET['f'] ?? null;
if (!$file_path)
    die(json_encode(['status' => false, 'msg' => 'file not specified']));

include dirname(__DIR__, 1) . '/_inc/b2-function.php';

$auth = b2_authorize();
if (!$auth)
    die(json_encode(['status' => false, 'msg' => 'B2 authorization failed']));

$auth_token = $auth['authorizationToken'];
$download_url = $auth['downloadUrl'];
$fileName = $B2_BUCKET_NAME . '/' . $file_path;

$file_data = b2_download_file($download_url, $auth_token, $fileName, 3);
if ($file_data === false)
    die(json_encode(['status' => false, 'msg' => 'failed to retrieve file']));

echo json_encode(['status' => true, 'data' => $file_data], JSON_UNESCAPED_UNICODE);
