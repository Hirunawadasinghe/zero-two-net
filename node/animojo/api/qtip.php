<?php
header('Content-Type: application/json');

$id = empty($_GET['id']) ? null : $_GET['id'];
if (!$id) {
    die(json_encode(['status' => false, 'msg' => 'id not specified']));
}

require_once 'cache/api_cache.php';
$cache = handleApiCaching(60 * 60 * 24); // 1 day
$response_data = [];

include $_SERVER['DOCUMENT_ROOT'] . '/_inc/function.php';

$element = find_movie($id);
if (!$element) {
    die(json_encode(['status' => false, 'msg' => 'database error']));
}

$tags = '';
for ($i = 0; $i < count($element['tags']); $i++) {
    $tags .= '<span><a href="/genre/' . format_url_str($element['tags'][$i]) . '" class="di">' . $element['tags'][$i] . '</a>' . ($i + 1 < count($element['tags']) ? ',' : '') . '</span>';
}

$response_data['data'] = trim('
<div class="qtip-h"><p>' . $element['name'] . '</p><span>' . $element['type'] . '</span></div>
<p class="qtip-des">' . $element['description'] . '</p>
<div class="qtip-info">
    <div>Language:<span class="di">' . $element['language'] . '</span></div>
    <div>Aired:<span class="di">' . $element['release_year'] . '</span></div>
    <div>Duration:<span class="di">' . ($element['duration'] ?? 'N/A') . '</span></div>
    <div>Genre:' . $tags . '</div>
</div>
<div class="qtip-btn-c">
    <a href="/watch/' . format_title($element) . '" class="qtip-btn"><i class="fa-solid fa-play"></i>Watch Now</a>
    <div class="popup-list" tabindex="0">
        <button class="qtip-btn add"><i class="fa-solid fa-plus"></i></button>
        <ul>
            <li class="qtip-wsl">Watching</li>
            <li class="qtip-wsl">On-Hold</li>
            <li class="qtip-wsl">Plan to watch</liHold>
            <li class="qtip-wsl">Dropped</li>
            <li class="qtip-wsl">Completed</li>
        </ul>
    </div>
</div>');

if ($response_data === []) {
    $response = json_encode(["status" => false, "msg" => "api error"]);
} else {
    $response_data['status'] = true;
    $response = json_encode($response_data);
    $cache->set($cache->getCacheKey(), $response);
}
echo $response;