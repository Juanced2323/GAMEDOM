<?php
session_start();
require_once "db_connect.php";

$nombre = trim($_POST['nombre']);
$descripcion = trim($_POST['descripcion']);

// Procesar el archivo de la imagen (icono)
if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
    $imagen = file_get_contents($_FILES['imagen']['tmp_name']);
} else {
    die("Error al subir la imagen del logro.");
}

// Recoger el tipo de logro
$tipo = $_POST['tipo'] ?? 'global';
$id_juego = null;
if ($tipo === 'juego') {
    $id_juego = intval($_POST['id_juego'] ?? 0);
    // Si no se seleccionó un juego válido, puedes abortar o asignar NULL
}

// Preparar la consulta INSERT
$stmt = $conn->prepare("INSERT INTO logros (nombre, descripcion, imagen, tipo, id_juego) VALUES (?, ?, ?, ?, ?)");
if (!$stmt) {
    die("Error en prepare: " . $conn->error);
}

// Si el logro es global, forzamos id_juego a NULL
if ($tipo === 'global') {
    $id_juego = NULL;
}

// En MySQL, para pasar NULL, se puede utilizar el valor PHP null
$stmt->bind_param("ssssi", $nombre, $descripcion, $imagen, $tipo, $id_juego);
$stmt->execute();
$stmt->close();
$conn->close();

header("Location: ../admin_logros.php?success=added");
exit();
?>
