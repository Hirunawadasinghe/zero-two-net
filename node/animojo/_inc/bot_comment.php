<?php
include_once '_config.php';

function genarate_bot_comment($element)
{
    global $site_name;

    $personalities = [
        'teasing',
        'emotional',
        'playful',
        'hyped-up action lover',
        'romance addict',
        'deep thinker who overanalyzes scenes',
        'just started anime',
        'meme-lover',
        'angry complainer',
        'curious',
        'cute femboy actor',
        'lgbtq speeker',
        'racist',
        'heartbroken after an affair'
    ];

    $languages = [
        'English',
        'English (heavy emoji use)',
        'Japanese',
        'Chinese',
        'Mixed language'
    ];

    $comment_types = [
        'question',
        'reaction',
        'opinion',
        'suggestion',
        'criticism',
        'praise',
        'informative',
        'story',
        'experience',
        'humor',
        'calls to action'
    ];

    $personality = $personalities[array_rand($personalities)];
    $gender = rand(1, 5) > 2 ? 'boy' : 'girl';
    $language = $languages[array_rand($languages)];
    $comment_type = $comment_types[array_rand($comment_types)];
    $typo = rand(1, 5) > 1 ? 'typos and slang' : 'normal spelling';
    $grammar = rand(1, 5) > 2 ? 'few grammar slips' : 'clean grammar';
    $character_limit = rand(5, 150);

    $source = get_sources($element['movie_id']);
    $episode = null;
    if ($source) {
        $last_ep = array_key_last($source);
        $episode = rand(1, is_int($last_ep) ?? $element['episodes']);
    }

    $prompt = trim("
    You are a real person commenting on an anime site. Your comment must be short, casual, and real.

    Your identity:
    Personality - $personality
    Gender - $gender

    Anime details:
    Title - " . $element['name'] . "
    Type - " . $element['type'] . "
    Available to watch - " . ($episode ? 'true' : 'false') . "
    " . ($element['type'] != 'Movie' && $episode ? "Current episode - $episode" : '') . "

    Comment details:
    Language - $language
    Mistake intensity - $typo, $grammar (Include these errors naturally)
    Comment type - $comment_type
    Website - " . strtolower($site_name) . "
    Time - " . time() . "

    When writing the commment, deeply think about your identity like personality, gender and all the other details like the details about anime and details about comment.
    You can talk about things like the episode or the movie, character('s) in the anime, anime studio, other similer anime titles, website, etc.
    Keep it concise and casual under " . $character_limit . " characters.
    Don't always say 'Yo'. Do not use hard words.
    Don't overthink and shit on it.
    If the anime isn't available to watch, do not say anything like you watch it. Instead make comments like asking to upload it, not begging.
    When making the name, make totaly random name. It's not a username it's a real name.

    Respond only in the following JSON format: { 'comment': 'your comment', 'name': 'username', 'description': 'critically evaluate why it can not be identify as written by AI, How you avoid getting cought by using human techniques like not using many symbols and dumb emojis and how is it match to your identity, anime details and comment details' }");

    $r = @file_get_contents("https://text.pollinations.ai/" . urlencode($prompt));
    $response = json_decode($r, true);
    if (json_last_error() !== JSON_ERROR_NONE || !isset($response['comment']) || !isset($response['name']))
        return false;

    $output = trim($response['comment']);
    $output = str_replace(['*', '`', '"'], '', $output);
    $output = str_replace(',', rand(1, 5) > 1 ? '' : ',', $output);
    $output = str_replace('’', rand(1, 5) > 3 ? '' : "'", $output);
    $output = str_replace('?', rand(1, 5) > 2 ? '' : "?", $output);
    $output = str_replace('.', rand(1, 5) > 2 ? '' : ".", $output);
    $output = str_replace(['-', '–', '—'], rand(1, 5) > 1 ? ' ' : '-', $output);
    $output = str_replace(['“', '”'], rand(1, 5) > 2 ? '' : '"', $output);
    $output = str_replace(['✨', '🔥', '🤩', '😍', '🤪', '🥰', '💥', '🚀'], '', $output);

    // randomly lower case
    if (rand(1, 5) > 2)
        $output = strtolower($output);
    // randomly remove emojis
    if (rand(1, 5) > 3)
        $output = preg_replace('/[^\p{L}\p{N}\p{P}\p{Z}]/u', '', $output);
    // randomly remove last symbol
    $last_char = substr($output, -1);
    if (strpbrk($last_char, '.!') && random_int(1, 5) > 1)
        $output = substr($output, 0, -1);

    return [
        'name' => rand(1, 5) > 3 ? explode(' ', $response['name'])[0] : $response['name'],
        'gender' => $gender,
        'personality' => $personality,
        'language' => $language,
        'comment_type' => $comment_type,
        'typo' => $typo,
        'grammar' => $grammar,
        'character_limit' => $character_limit,
        'comment' => $output,
        'description' => $response['description']
    ];
}