<?php
$MAX_REQUESTS = 120;      // allowed requests
$WINDOW = 60;      // per seconds
$BLOCK_TIME = 300;     // block for 5 minutes

$CACHE_PATH = $_SERVER['DOCUMENT_ROOT'] . '/cache';
$STORAGE_FILE = $CACHE_PATH . '/ip_log';

$ip = $_SERVER['REMOTE_ADDR'];
$now = time();

$data = [];
if (file_exists($STORAGE_FILE))
    $data = include $STORAGE_FILE;

// Initialize IP record
if (!isset($data[$ip])) {
    $data[$ip] = [
        'requests' => [],
        'blocked_until' => 0
    ];
}

// Check if blocked
if ($data[$ip]['blocked_until'] > $now) {
    http_response_code(429);
    die('Too many requests. Try again later.');
}

// Clean old requests
$data[$ip]['requests'] = array_filter(
    $data[$ip]['requests'],
    fn($t) => $t > ($now - $WINDOW)
);

$data[$ip]['requests'][] = $now;

if (count($data[$ip]['requests']) > $MAX_REQUESTS) {
    $data[$ip]['blocked_until'] = $now + $BLOCK_TIME;
    $tmp = tempnam(dirname($STORAGE_FILE), 'rl__');
    file_put_contents($tmp, '<?php return ' . var_export($data, true) . ';');
    rename($tmp, $STORAGE_FILE);
    http_response_code(429);
    die('Rate limit exceeded.');
}

if (!is_dir($CACHE_PATH))
    mkdir($CACHE_PATH, 0755, true);

$tmp = tempnam(dirname($STORAGE_FILE), 'rl__');
file_put_contents($tmp, '<?php return ' . var_export($data, true) . ';');
rename($tmp, $STORAGE_FILE);