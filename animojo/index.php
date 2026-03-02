<?php
function GET_URL($k = null)
{
    $a = explode("/", trim($_GET['url'] ?? 'intro', "/"));
    if (!is_numeric($k)) {
        return $a;
    } else {
        return $a[$k] ?? null;
    }
}

$f = GET_URL(0) . '.php';
if (file_exists($f)) {
    require $f;
} else {
    require 'error.php';
}