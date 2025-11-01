<?php
include 'config.php';

$B2_ACCOUNT_ID = 'c19d497449af';
$B2_APP_KEY = '003569faa53cbb929971bf6948b83010350d5e4a28';
$B2_BUCKET_NAME = 'zero-two-net-subtitle';
$B2_BUCKET_ID = 'cc31491d3419d78494990a1f';

// ----------------------
//  AUTHENTICATION
// ----------------------
function b2_authorize($maxRetries = 3)
{
    global $B2_ACCOUNT_ID, $B2_APP_KEY;
    $url = 'https://api.backblazeb2.com/b2api/v2/b2_authorize_account';
    $headers = [
        'Authorization: Basic ' . base64_encode($B2_ACCOUNT_ID . ':' . $B2_APP_KEY)
    ];

    return b2_api_request($url, 'GET', $headers, null, $maxRetries);
}

// ----------------------
//  LIST FILES
// ----------------------
function b2_list_files($apiUrl, $authToken, $prefix, $maxRetries = 3)
{
    global $B2_BUCKET_ID;
    $url = rtrim($apiUrl, '/') . '/b2api/v2/b2_list_file_names';

    $data = [
        'bucketId' => $B2_BUCKET_ID,
        'maxFileCount' => 1000
    ];

    if (!empty($prefix)) {
        $data['prefix'] = $prefix;
    }

    $headers = [
        'Authorization: ' . $authToken,
        'Content-Type: application/json'
    ];

    $response = b2_api_request($url, 'POST', $headers, json_encode($data), $maxRetries);

    if (!$response || !isset($response['files'])) {
        return false;
    }

    return $response['files'];
}

// ----------------------
//  DOWNLOAD FILE
// ----------------------
function b2_download_file($downloadUrl, $authToken, $fileName, $maxRetries = 3)
{
    $url = rtrim($downloadUrl, '/') . '/file/' . $fileName;
    $headers = [
        'Authorization: ' . $authToken
    ];

    return b2_api_request($url, 'GET', $headers, null, $maxRetries, false);
}

// ----------------------
//  GENERIC REQUEST WRAPPER
// ----------------------
function b2_api_request($url, $method, $headers = [], $body = null, $maxRetries = 3, $decodeJson = true)
{
    $try = 0;
    $delay = 0.3; // seconds (initial delay for backoff)

    while ($try < $maxRetries) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        // If success
        if ($httpCode >= 200 && $httpCode < 300 && $response) {
            return $decodeJson ? json_decode($response, true) : $response;
        }

        // Retry if failed or transient error
        $shouldRetry = (
            $httpCode == 0 ||               // network failure
            ($httpCode >= 500 && $httpCode < 600) || // server error
            in_array($httpCode, [408, 429]) // timeout or rate limit
        );

        if ($shouldRetry) {
            $try++;
            usleep($delay * 1_000_000); // sleep before retry
            $delay *= 2; // exponential backoff
        } else {
            break; // non-retryable error
        }
    }

    // Final fail
    if (isset($curlError) && $curlError !== '') {
        error_log("B2 API Error ({$url}): {$curlError}");
    } elseif (isset($httpCode)) {
        error_log("B2 API HTTP Error ({$url}): {$httpCode}");
    }

    return false;
}