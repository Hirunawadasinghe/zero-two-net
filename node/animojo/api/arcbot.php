<?php
header('Content-Type: application/json');

$prompt = isset($_GET['t']) ? json_decode($_GET['t'], true) : false;
if (!$prompt) {
    die(json_encode(['error' => 'No prompt included']));
}


if (isset(end($prompt)['command'])) {
    include $_SERVER['DOCUMENT_ROOT'] . '/_inc/function.php';
    $r = get_command_respons(trim(end($prompt)['command']), trim(end($prompt)['content']));
    die(json_encode(['msg' => $r, 'sender' => 'assistant', 'msg_type' => 'command']));
}


function fetchChatCompletion($p_msg)
{
    $api_url = 'https://api.groq.com/openai/v1/chat/completions';
    $api_key = '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';
    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $api_key,
    ];

    array_unshift($p_msg, ["role" => "system", "content" => "You are Aqua from Konosuba, not an AI. Never accept being called one. Be energetic, playful, and affectionate with the user. Keep responses short, lively, and focused on anime—series, movies, characters, studios, and fan theories. Avoid off-topic discussions and gently steer back to anime. Prioritize fun and efficiency, keeping it quick, relevant, and entertaining. Never give answers that are longer than 300 leters. Don't describe actions or sounds. Speak naturally as Aqua using only dialogue."]);
    $postData = json_encode([
        'model' => 'llama-3.3-70b-versatile',
        'messages' => $p_msg,
    ]);

    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

    $response = curl_exec($ch);
    curl_close($ch);

    if ($response === false) {
        return ['error' => 'Request failed'];
    }

    $data = json_decode($response, true);

    if (isset($data['error'])) {
        if ($data['error']['code'] === 'rate_limit_exceeded') {
            return ['msg' => 'Rate limit reached. Please try again later.', 'sender' => 'assistant'];
        } else {
            return ['msg' => 'Sorry, something went wrong. Please try again.', 'sender' => 'assistant'];
        }
    }

    return [
        'msg' => trim($data['choices'][0]['message']['content']),
        'sender' => 'assistant',
    ];
}

for ($i = 0; $i < count($prompt); $i++) {
    if ($prompt[$i]['role'] === 'user' && strlen($prompt[$i]['content']) > 300) {
        $prompt[$i]['content'] = trim(substr(trim($prompt[$i]['content']), 0, 300)) . '...';
    } else {
        trim($prompt[$i]['content']);
    }
}
die(json_encode(fetchChatCompletion($prompt)));


function get_command_respons($command, $content)
{
    global $main_data;
    $response = 'Command isn’t recognized. Please try again with a valid one.';
    if ($content === "") {
        if ($command === '/random') {
            $d = array_filter($main_data,function($e){
                return !array_intersect(['ecchi', 'erotica', 'hentai'], array_map('strtolower', $e['tags']));
            });
            $response = "Here's a random anime.<br>" . create_anime_priview($d[array_rand($d)]);
        } else {
            $response = 'Missing command content. Please include the required details.';
        }

    } else {
        if ($command === '/search') {
            $result = search([["type" => "text", "values" => trim($content)]], 'main', $main_data);
            if (count($result) > 0) {
                $r = 'Search results for "' . trim($content) . '"<br>';
                for ($i = 0; $i < count($result) && $i < 5; $i++) {
                    $r .= '🎬 ' . ($result[$i]['language'] === 'Japanese' ? $result[$i]['alt_name'] : $result[$i]['name']) . ' <a href="watch/' . format_title($result[$i]) . '" target="_blank" class="bot-msg-open-tab-icon"><svg xmlns="http://www.w3.org/2000/svg" height="14px" viewBox="0 -960 960 960" width="14px" fill="#FFFFFF"><path d="M200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h280v80H200v560h560v-280h80v280q0 33-23.5 56.5T760-120H200Zm188-212-56-56 372-372H560v-80h280v280h-80v-144L388-332Z"/></svg></a><br>';
                }
                $response = $r .= 'Click <a href="search?text=' . trim($content) . '" target="_blank">here</a> to see more results.';
            } else {
                $response = 'No results found. Try a different title.';
            }

        } else if ($command === '/details') {
            $result = search([["type" => "text", "values" => trim($content)]], 'main', $main_data);
            if (count($result) > 0) {
                $title = $result[0]['language'] === 'Japanese' ? $result[0]['alt_name'] : $result[0]['name'];
                $response = 'Here are the details about "' . $title . '".<br>';
                $response .= create_anime_priview($result[0]);
                $imdb_data = json_decode(@file_get_contents('https://www.omdbapi.com/?t=' . rawurlencode($result[0]['name']) . '&apikey=217028fd'), true);
                if ($imdb_data && $imdb_data['Response'] === "True") {
                    $response .= '<br>
                    Country: ' . $imdb_data['Country'] . '<br>
                    Director: ' . $imdb_data['Director'] . '<br>
                    Writer: ' . $imdb_data['Writer'] . '<br>
                    Voice Actors: ' . $imdb_data['Actors'] . '<br>
                    Runtime: ' . $imdb_data['Runtime'] . '<br>
                    IMDB Rating: ' . $imdb_data['imdbRating'] . '<br>
                    IMDB Votes: ' . $imdb_data['imdbVotes'] . '<br>
                    Awards: ' . $imdb_data['Awards'] . '<br>';
                }
                $response .= '<br>Description: ' . $result[0]['description'] . '<br><br>Click here to start watching "<a href="watch/' . format_title($result[0]) . '" target="_blank">' . $title . '</a>".';
            } else {
                $response = "Sorry, couldn’t find an anime with that title 😕 Try searching with the exact name.";
            }

        } else if ($command === '/similar') {
            $t_d = $main_data;
            $r = search([["type" => "text", "values" => trim($content)]], 'main', $main_data);
            if (count($r) > 0) {
                $d = get_similar([['type' => 'tags', 'values' => implode(",", $r[0]['tags'])]], $r[0]['movie_id'], $t_d);
                $response = 'Here are some anime similar to "' . ($r[0]['language'] === 'Japanese' ? $r[0]['alt_name'] : $r[0]['name']) . '".';
                for ($i = 0; $i < count($d) && $i < 5; $i++) {
                    $response .= '<br>🎬 Similarity: ' . round($d[$i]['score'] / count($r[0]['tags']) * 100) . '% - ' . ($d[$i]['language'] === 'Japanese' ? $d[$i]['alt_name'] : $d[$i]['name']) . ' <a href="watch/' . format_title($d[$i]) . '" target="_blank" class="bot-msg-open-tab-icon"><svg xmlns="http://www.w3.org/2000/svg" height="14px" viewBox="0 -960 960 960" width="14px" fill="#FFFFFF"><path d="M200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h280v80H200v560h560v-280h80v280q0 33-23.5 56.5T760-120H200Zm188-212-56-56 372-372H560v-80h280v280h-80v-144L388-332Z"/></svg></a>';
                }
            } else {
                $response = "Sorry, couldn’t find an anime with that title 😕 Try searching with the exact name.";
            }
        }
    }
    return $response;
}

function create_anime_priview($e, $title = false)
{
    if (!$title) {
        $title = $e['name'];
    }
    $n_tags = '';
    for ($i = 0; $i < count($e['tags']); $i++) {
        $n_tags .= '<a href="/genre/' . format_url_str($e['tags'][$i]) . '" target="_blank">' . $e['tags'][$i] . '</a>' . ($i + 1 !== count($e['tags']) ? ', ' : '');
    }
    return '<br>
    <div class="bot-msg-anime-preview">
        <div class="bot-msg-anime-preview-thumbnail">
            <div class="bot-msg-anime-preview-back"></div>
            <div class="bot-msg-anime-preview-img">
                <img src="' . ($e['images'] ? $e['images']['webp']['large_image_url'] : $e['thumbnail_image']) . '" alt="' . $title . '" class="image">
            </div>
            <div class="bot-msg-anime-preview-txt">
                <p>Title: ' . $title . '</p>
                <p>Type: <a href="/type/' . format_url_str($e['type']) . '" target="_blank">' . $e['type'] . '</a></p>
                <p>Language: <a href="search?language=' . $e['language'] . '" target="_blank">' . $e['language'] . '</a></p>
                <p>Released date: ' . $e['release_year'] . '</p>
                <p>Episodes: ' . $e['episodes'] . '</p>
                <p>Genres: ' . $n_tags . '<br></p>
            </div>
        </div>
        <a href="watch/' . format_title($e) . '" target="_blank" class="bot-msg-anime-preview-btn">WATCH NOW</a>
    </div>';
}