<?php
session_start();
require_once "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['id_logro'])) {
    header("Location: ../admin_logros.php");
    exit();
}

$id_logro    = intval($_POST['id_logro']);
$nombre      = trim($_POST['nombre']);
$descripcion = trim($_POST['descripcion']);

// Verificar si se ha subido una nueva imagen
if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
    $imagenContent = file_get_contents($_FILES['imagen']['tmp_name']);
    $stmt = $conn->prepare("UPDATE logros SET nombre = ?, descripcion = ?, imagen = ? WHERE id_logro = ?");
    $stmt->bind_param("sssi", $nombre, $descripcion, $imagenContent, $id_logro);
} else {
    // Si no se subió nueva imagen, actualizamos solo nombre y descripción
    $stmt = $conn->prepare("UPDATE logros SET nombre = ?, descripcion = ? WHERE id_logro = ?");
    $stmt->bind_param("ssi", $nombre, $descripcion, $id_logro);
}

if ($stmt->execute()) {
    header("Location: ../admin_logros.php?success=updated");
    exit();
} else {
    die("Error al actualizar el logro: " . $stmt->error);
}

$stmt->close();
$conn->close();
?>
