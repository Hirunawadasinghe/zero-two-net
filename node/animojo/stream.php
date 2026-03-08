<?php
$movie = GET_URL(1);
switch ($movie) {
    case 'demon-slayer-kimetsu-no-yaiba-infinity-castle':
        header('Location: /watch/boku-no-pico-h2');
        break;
    default:
        header('Location: /');
        break;
}