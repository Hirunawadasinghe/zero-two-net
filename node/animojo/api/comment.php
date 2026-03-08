<?php
header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST')
    die(json_encode(["status" => false, "msg" => "Invalid request"]));

$name = empty($_POST['username']) ? null : trim($_POST['username']);
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$comment = empty($_POST['comment']) ? null : trim($_POST['comment']);
$page_id = empty($_POST['page_id']) ? null : trim($_POST['page_id']);

if (!$name || !$email || !$comment || !$page_id)
    die(json_encode(['status' => false, 'msg' => 'Invalid input']));
if (strlen($name) > 50)
    die(json_encode(['status' => false, 'msg' => 'Username is too long']));
if (strlen($comment) > 1000)
    die(json_encode(['status' => false, 'msg' => 'Comment is too long']));

include $_SERVER['DOCUMENT_ROOT'] . '/_inc/dbh.php';

$stmt = $conn->prepare("INSERT INTO ep_comments (page_id, name, email, comment) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $page_id, $name, $email, $comment);

$response_data = [];
if ($stmt->execute()) {
    $response_data['status'] = true;
    $response_data['data'] = [
        'username' => htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
        'comment' => nl2br(htmlspecialchars($comment, ENT_QUOTES, 'UTF-8'))
    ];
} else {
    $response_data['status'] = false;
    $response_data['msg'] = "api error";
}

$stmt->close();
$conn->close();

echo json_encode($response_data);