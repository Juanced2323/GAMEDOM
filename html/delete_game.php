<?php
session_start();
require_once "php/db_connect.php";

if (!isset($_GET['id'])) {
    header("Location: admin_juegos.php");
    exit();
}

$id_juego = intval($_GET['id']);

$stmt = $conn->prepare("DELETE FROM juegos WHERE id_juego = ?");
$stmt->bind_param("i", $id_juego);

if ($stmt->execute()) {
    header("Location: ../admin_juegos.php?success=deleted");
    exit();
} else {
    echo "Error al eliminar el juego: " . $stmt->error;
}
$stmt->close();
$conn->close();
?>
