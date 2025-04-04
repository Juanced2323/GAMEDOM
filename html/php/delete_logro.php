<?php
session_start();
require_once "db_connect.php";

if (!isset($_GET['id'])) {
    header("Location: ../admin_logros.php");
    exit();
}

$id_logro = intval($_GET['id']);

$stmt = $conn->prepare("DELETE FROM logros WHERE id_logro = ?");
$stmt->bind_param("i", $id_logro);

if ($stmt->execute()) {
    header("Location: ../admin_logros.php?success=deleted");
    exit();
} else {
    die("Error al eliminar el logro: " . $stmt->error);
}

$stmt->close();
$conn->close();
?>

