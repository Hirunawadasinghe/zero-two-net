<?php
$servername = "localhost";
$db_name = "animojo";
$username = "root";
$password = "";

$conn = new mysqli($servername, $username, $password, $db_name);
if ($conn->connect_error) {
    die("db connection failed: " . $conn->connect_error);
}