<?php
header('Content-Type: application/json');

if (!isset($_SERVER['HTTP_REFERER']) || strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) === false)
    die(json_encode(['status' => true]));

// randomly stop process
if (rand(1, 5) === 1)
    die(json_encode(['status' => true]));

$id = !empty($_GET['id']) ? $_GET['id'] : null;
if (!$id)
    die(json_encode(['status' => false, 'msg' => 'id not specified']));

include $_SERVER['DOCUMENT_ROOT'] . '/_inc/function.php';

$e = find_movie($id);
if (!$e)
    die(json_encode(['status' => false, 'msg' => 'invalid id']));

include $_SERVER['DOCUMENT_ROOT'] . '/_inc/bot_comment.php';
include $_SERVER['DOCUMENT_ROOT'] . '/_inc/dbh.php';

function post_comment($p_id, $username, $email, $comment)
{
    global $conn;
    $stmt = $conn->prepare("INSERT INTO ep_comments (page_id, name, email, comment) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $p_id, $username, $email, $comment);

    $r = $stmt->execute();
    $stmt->close();
    $conn->close();

    return $r;
}

$bot_r = genarate_bot_comment($e);
if ($bot_r)
    post_comment($id, $bot_r['name'], strtolower(str_replace(' ', '', $bot_r['name'])) . '@bot.bot', $bot_r['comment']);

echo json_encode(['status' => $bot_r ? true : false]);