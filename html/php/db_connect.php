<?php
$servername = "db";  // No "localhost"
$username   = "usuario";
$password   = "usuario";
$dbname     = "gamedom_users";
$port       = 3306;

$conn = new mysqli($servername, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>
