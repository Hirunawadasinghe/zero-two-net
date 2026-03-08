<?php
header('Content-Type: application/json');

include $_SERVER['DOCUMENT_ROOT'] . '/_inc/_config.php';
require $_SERVER['DOCUMENT_ROOT'] . '/_inc/db_cache.php';

foreach ($db_net_api_endpoints as $e) {
    $r = get_db_net_cache($e['path'], $e['file'], $e['ep'], $e['time']);
    $result[$r ? 'success' : 'error'][] = $e['file'];
}

$result['success_rate'] = (isset($result['success']) ? count($result['success']) : 0) / count($db_net_api_endpoints);

echo json_encode($result);