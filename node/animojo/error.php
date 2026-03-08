<?php
include_once '_inc/_config.php';
include_once '_inc/minify.php';

$error_code = 404;

if (function_exists('GET_URL')) {
    if (GET_URL(0) === 'error')
        $error_code = GET_URL(1) ?? 404;
}

$errors_messages = [
    404 => "Oh no! Looks like you got lost",
    500 => "Something went wrong on our end",
    400 => "Bad request! Let’s try that properly",
    401 => "Unauthorized! You need to log in first",
    403 => "Access denied! You don’t have permission here",
    503 => "Service unavailable! We’ll be back soon"
];

if (!isset($errors_messages[$error_code]))
    $error_code = 404;

$errors_message = $errors_messages[$error_code];

http_response_code($error_code);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <title><?= $error_code ?> Error - <?= $site_name ?></title>
    <link rel="shortcut icon" href="/images/favicon.png?v=<?= $version ?>" type="image/x-icon">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v7.0.0/css/all.css">
    <style>
        <?php
        echo minify($_SERVER['DOCUMENT_ROOT'] . '/css/style.css');
        echo minify($_SERVER['DOCUMENT_ROOT'] . '/css/error.css');
        ?>
    </style>
</head>

<body>
    <div class="err-w">
        <div class="err-c">
            <img src="/images/characters/no-idea.png">
            <h1><?= $error_code ?> Error</h1>
            <p><?= $errors_message ?></p>
            <a href="/"><i class="fa-solid fa-angle-left"></i>Go to Home</a>
        </div>
    </div>
</body>

</html>
<?php exit; ?>