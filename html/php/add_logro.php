<?php
session_start();
require_once "db_connect.php";

$nombre = trim($_POST['nombre']);
$descripcion = trim($_POST['descripcion']);

if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
    $imagen = file_get_contents($_FILES['imagen']['tmp_name']);
} else {
    die("Error al subir la imagen del logro.");
}

$stmt = $conn->prepare("INSERT INTO logros (nombre, descripcion, imagen) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $nombre, $descripcion, $imagen);
$stmt->execute();
$stmt->close();
$conn->close();

header("Location: ../admin_logros.php?success=added");
exit();
?>
