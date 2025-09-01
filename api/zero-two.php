<?php
include $_SERVER['DOCUMENT_ROOT'] . '/_inc/config.php';
include $_SERVER['DOCUMENT_ROOT'] . '/api/zero-two/inc.php';

$botToken = '8160461478:AAGETJCnv58YX-2g_bL71dyvf7_JzTRS_hA';
$tg_url = "https://api.telegram.org/bot$botToken";
$bot_username = '@AnimeArcadia_bot';
$admin_chat_id = 5922865116;

$update = json_decode(file_get_contents("php://input"), true);

if (isset($update["message"])) {
    $chat_id = $update["message"]["chat"]["id"];
    $message_id = $update["message"]["message_id"];
    $parts = explode(" ", trim($update["message"]["text"]), 2);
    $command = trim($parts[0]);
    $query = isset($parts[1]) ? trim($parts[1]) : false;
    get_command_response($command, $query);
} else {
    sendMessage(['chat_id' => $admin_chat_id, 'text' => 'Bot successfully hooked']);
}

function get_command_response($command, $content)
{
    global $database_path;
    $parts = [
        '/main/ajax1.json',
        '/main/ajax2.json'
    ];
    $main_data = [];
    foreach ($parts as $p) {
        $d = json_decode(file_get_contents($database_path . $p), true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $main_data = array_merge($main_data, $d);
        }
    }
    require 'inc.php';
    global $bot_username;

    switch ($command) {
        case '/search':
        case ('/search' . $bot_username):
            handle_search($content, $main_data);
            break;

        case '/details':
        case ('/details' . $bot_username):
            handle_details($content, $main_data);
            break;

        case '/similar':
        case ('/similar' . $bot_username):
            handle_similar($content, $main_data);
            break;

        case '/random':
        case ('/random' . $bot_username):
            handle_random($main_data);
            break;
    }
}

function sendMessage($params)
{
    global $tg_url;
    file_get_contents($tg_url . "/sendMessage?" . http_build_query($params));
    exit;
}

function send_error_Message($command)
{
    global $tg_url, $chat_id, $message_id;
    $params = [
        'chat_id' => $chat_id,
        'reply_to_message_id' => $message_id,
        'text' => "Command Error 🚫\nCorrect way : " . $command . " <anime name>"
    ];
    file_get_contents($tg_url . "/sendMessage?" . http_build_query($params));
    exit;
}

function format_title_print($entry)
{
    $title = $entry['language'] === 'Japanese' ? $entry['alt_name'] : $entry['name'];
    if (strlen($title) > 34) {
        $title = substr($title, 0, 15) . '....' . substr($title, -15);
    }
    $title .= $entry['language'] !== 'Japanese' ? ' (Dub)' : '';
    return $title;
}

function handle_search($content, $main_data)
{
    if (!$content) {
        send_error_Message('/search');
    }
    global $tg_url, $chat_id, $message_id, $main_site_url;
    $result = search([["type" => "text", "values" => $content]], 'main', $main_data);
    if (count($result) > 0) {
        $keyboard = ['inline_keyboard' => []];
        for ($i = 0; $i < count($result) && $i < 10; $i++) {
            $keyboard['inline_keyboard'][] = [['text' => format_title_print($result[$i]), 'url' => $main_site_url . '/watch/' . format_title($result[$i])]];
        }
        $keyboard['inline_keyboard'][] = [['text' => '🌐 See More', 'url' => $main_site_url . '/search?text=' . urlencode($content)]];
        $params = [
            'chat_id' => $chat_id,
            'reply_to_message_id' => $message_id,
            'text' => 'Search results for : ' . $content,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode($keyboard),
        ];
        file_get_contents($tg_url . "/sendMessage?" . http_build_query($params));
    } else {
        sendMessage(['chat_id' => $chat_id, 'text' => 'No results found. Try a different title.']);
    }
}

function handle_details($content, $main_data)
{
    if (!$content) {
        send_error_Message('/details');
    }
    global $tg_url, $chat_id, $message_id, $main_site_url;
    $result = search([["type" => "text", "values" => trim($content)]], 'main', $main_data);
    if (count($result) > 0) {
        $text = "Here are the details about : " . $result[0]['name'] . "\n\nType: " . $result[0]['type'] . "\nReleased: " . $result[0]['release_year'] . "\nEpisodes: " . $result[0]['episodes'] . "\nGenres: " . implode(', ', $result[0]['tags']) . "";
        $imdb_data = json_decode(file_get_contents('https://www.omdbapi.com/?t=' . rawurlencode($result[0]['name']) . '&apikey=217028fd'), true);
        if ($imdb_data['Response'] === "True") {
            $text .= "\nCountry: " . $imdb_data['Country'] . "\nDirector: " . $imdb_data['Director'] . "\nWriter: " . $imdb_data['Writer'] . "\nVoice Actors: " . $imdb_data['Actors'] . "\nRuntime: " . $imdb_data['Runtime'] . "\nIMDB Rating: " . $imdb_data['imdbRating'] . "\nIMDB Votes: " . $imdb_data['imdbVotes'] . "\nAwards: " . $imdb_data['Awards'];
        }
        $text .= "\n\n📝 Synopsis: " . substr($result[0]['description'], 0, 200) . (strlen($result[0]['description']) > 200 ? "..." : "");
        $keyboard = ['inline_keyboard' => [[['text' => 'START WATCHING', 'url' => $main_site_url . '/watch/' . format_title($result[0])]]]];
        $params = [
            'chat_id' => $chat_id,
            'reply_to_message_id' => $message_id,
            'photo' => $result[0]['thumbnail_image'],
            'caption' => $text,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode($keyboard),
        ];
        file_get_contents($tg_url . "/sendPhoto?" . http_build_query($params));
    } else {
        sendMessage(['chat_id' => $chat_id, 'text' => 'No anime found with that title. Try a different title.']);
    }
}

function handle_similar($content, $main_data)
{
    if (!$content) {
        send_error_Message('/similar');
    }
    global $tg_url, $chat_id, $message_id, $main_site_url;
    $t_d = $main_data;
    $r = search([["type" => "text", "values" => trim($content)]], 'main', $main_data);
    if (count($r) > 0) {
        $d = get_similar([['type' => 'tags', 'values' => implode(",", $r[0]['tags'])]], $r[0]['movie_id'], $t_d);
        $keyboard = ['inline_keyboard' => []];
        for ($i = 0; $i < count($d) && $i < 10; $i++) {
            $keyboard['inline_keyboard'][] = [['text' => format_title_print($d[$i]), 'url' => $main_site_url . '/watch/' . format_title($d[$i])]];
        }
        $p = [
            'chat_id' => $chat_id,
            'reply_to_message_id' => $message_id,
            'photo' => $r[0]['thumbnail_image'],
            'caption' => 'Here are some anime similar to : ' . $r[0]['name'],
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode($keyboard),
        ];
        file_get_contents($tg_url . "/sendPhoto?" . http_build_query($p));
    } else {
        sendMessage(['chat_id' => $chat_id, 'text' => 'No anime found with that title. Try a different title.']);
    }
}

function handle_random($main_data)
{
    global $tg_url, $chat_id, $message_id, $main_site_url;
    $r = $main_data[array_rand($main_data)];
    $text = "Here's a random anime ✨\n\nTitle: " . $r['name'] . "\nType: " . $r['type'] . "\nLanguage: " . $r['language'] . "\nReleased: " . $r['release_year'] . "\nEpisodes: " . $r['episodes'] . "\nGenres: " . implode(', ', $r['tags']) . "\n\n📝 Synopsis: " . substr($r['description'], 0, 200) . (strlen($r['description']) > 200 ? "..." : "");
    $keyboard = ['inline_keyboard' => [[['text' => 'START WATCHING', 'url' => $main_site_url . '/watch/' . format_title($r)]]]];
    $p = [
        'chat_id' => $chat_id,
        'reply_to_message_id' => $message_id,
        'photo' => $r['thumbnail_image'],
        'caption' => $text,
        'parse_mode' => 'HTML',
        'reply_markup' => json_encode($keyboard),
    ];
    file_get_contents($tg_url . "/sendPhoto?" . http_build_query($p));
}