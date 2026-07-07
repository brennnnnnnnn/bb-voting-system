<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = getenv("DB_HOST") ?: "localhost";
$username = getenv("DB_USER") ?: "root";
$password = getenv("DB_PASSWORD") ?: "";
$dbname = getenv("DB_NAME") ?: "sistem_pengundian_jawatankuasa_briged_putera";
$port = getenv("DB_PORT") ?: "3306";

$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>