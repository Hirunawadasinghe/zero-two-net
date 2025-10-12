<?php
header('Content-Type: application/json');
include $_SERVER['DOCUMENT_ROOT'] . '/_inc/b2-function.php';

$file_path = empty($_GET['f']) ? null : $_GET['f'];
if (!$file_path)
    die(json_encode(['status' => false, 'msg' => 'file not specified']));

// AUTH & BUCKET
$auth = b2_authorize();
if (!$auth)
    die('Auth failed');
$bucket_id = b2_get_bucket_id($auth['apiUrl'], $auth['authorizationToken']);
if (!$bucket_id)
    die('Bucket not found');

$auth_token = $auth['authorizationToken'];
$download_url = $auth['downloadUrl'];
$url = $download_url . '/file/' . $B2_BUCKET_NAME . '/' . $_GET['f'];

// Stream file
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: ' . $auth_token]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$data = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200) {
    http_response_code($http_code);
    die(json_encode(['status' => false, 'data' => $data]));
}

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . rand(1000, 9999) . '.' . pathinfo($file_path, PATHINFO_EXTENSION) . '"');
echo $data;
