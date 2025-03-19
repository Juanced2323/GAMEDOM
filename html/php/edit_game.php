<?php
session_start();
require_once "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['id_juego'])) {
    header("Location: ../admin_juegos.php");
    exit();
}

$id_juego = intval($_POST['id_juego']);
$nombre = trim($_POST['nombre']);
$descripcion = trim($_POST['descripcion']);
$ruta_index = trim($_POST['ruta_index']);

// Si se sube un nuevo icono, procesarlo; de lo contrario, no modificar el icono
if (isset($_FILES['icono']) && $_FILES['icono']['error'] === UPLOAD_ERR_OK) {
    $iconoContent = file_get_contents($_FILES['icono']['tmp_name']);
    $stmt = $conn->prepare("UPDATE juegos SET nombre = ?, icono = ?, descripcion = ?, ruta_index = ? WHERE id_juego = ?");
    $stmt->bind_param("ssssi", $nombre, $iconoContent, $descripcion, $ruta_index, $id_juego);
} else {
    $stmt = $conn->prepare("UPDATE juegos SET nombre = ?, descripcion = ?, ruta_index = ? WHERE id_juego = ?");
    $stmt->bind_param("sssi", $nombre, $descripcion, $ruta_index, $id_juego);
}

if ($stmt->execute()) {
    header("Location: ../admin_juegos.php?success=updated");
    exit();
} else {
    echo "Error al actualizar el juego: " . $stmt->error;
}
$stmt->close();
$conn->close();
?>
