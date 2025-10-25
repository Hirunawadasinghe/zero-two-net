<?php
include 'config.php';

$B2_ACCOUNT_ID = 'c19d497449af';
$B2_APP_KEY = '003569faa53cbb929971bf6948b83010350d5e4a28';
$B2_BUCKET_NAME = 'zero-two-net-subtitle';

// === BACKBLAZE AUTH ===
function b2_authorize()
{
    global $B2_ACCOUNT_ID, $B2_APP_KEY;

    $auth_encoded = base64_encode($B2_ACCOUNT_ID . ':' . $B2_APP_KEY);
    $ch = curl_init('https://api.backblazeb2.com/b2api/v2/b2_authorize_account');
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Basic ' . $auth_encoded]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $res = json_decode(curl_exec($ch), true);
    curl_close($ch);

    if (empty($res['apiUrl'])) {
        return null;
    }
    return $res;
}

// === GET BUCKET ID ===
function b2_get_bucket_id($api_url, $auth_token)
{
    global $B2_ACCOUNT_ID, $B2_BUCKET_NAME;
    $ch = curl_init($api_url . '/b2api/v2/b2_list_buckets');
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: ' . $auth_token]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['accountId' => $B2_ACCOUNT_ID]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $res = json_decode(curl_exec($ch), true);
    curl_close($ch);

    foreach ($res['buckets'] as $b) {
        if ($b['bucketName'] === $B2_BUCKET_NAME) {
            return $b['bucketId'];
        }
    }
    return null;
}

// === LIST FILES IN A FOLDER ===
function b2_list_files($api_url, $auth_token, $bucket_id, $prefix)
{
    $all_files = [];
    $startFileName = null;

    do {
        $post_data = [
            'bucketId' => $bucket_id,
            'prefix' => rtrim($prefix, '/') . '/',
            'maxFileCount' => 1000  // Max allowed by B2
        ];
        if ($startFileName)
            $post_data['startFileName'] = $startFileName;

        $ch = curl_init($api_url . '/b2api/v2/b2_list_file_names');
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: ' . $auth_token]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $res = json_decode(curl_exec($ch), true);
        curl_close($ch);

        if (isset($res['files'])) {
            $all_files = array_merge($all_files, $res['files']);
        }

        $startFileName = $res['nextFileName'] ?? null;
    } while ($startFileName);

    return $all_files;
}
