<?php
session_start();
// Opcional: Validar permisos de administrador

require_once "db_connect.php";

$nombre = trim($_POST['nombre']);
$descripcion = trim($_POST['descripcion']);
$ruta_index = trim($_POST['ruta_index']);

// Procesar el archivo del icono
if (isset($_FILES['icono']) && $_FILES['icono']['error'] === UPLOAD_ERR_OK) {
    $iconoContent = file_get_contents($_FILES['icono']['tmp_name']);
} else {
    $iconoContent = null;
}

$stmt = $conn->prepare("INSERT INTO juegos (nombre, icono, descripcion, ruta_index) VALUES (?, ?, ?, ?)");
if (!$stmt) {
    die("Error en prepare: " . $conn->error);
}
$stmt->bind_param("ssss", $nombre, $iconoContent, $descripcion, $ruta_index);

if ($stmt->execute()) {
    header("Location: ../admin_juegos.php?success=1");
    exit();
} else {
    echo "Error al agregar el juego: " . $stmt->error;
}
$stmt->close();
$conn->close();
?>
