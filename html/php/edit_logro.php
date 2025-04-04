<?php
session_start();
require_once "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['id_logro'])) {
    header("Location: ../admin_logros.php");
    exit();
}

$id_logro = intval($_POST['id_logro']);
$nombre = trim($_POST['nombre'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');

// Recoger el tipo de logro
$tipo = $_POST['tipo'] ?? 'global';
$id_juego = null;
if ($tipo === 'juego') {
    $id_juego = intval($_POST['id_juego'] ?? 0);
}

// Preparar la consulta UPDATE de forma dinámica
$sql = "UPDATE logros 
        SET nombre = ?, descripcion = ?, tipo = ?, id_juego = ?";
$types = "sssi";
$params = [$nombre, $descripcion, $tipo, $id_juego];

// Si se sube una nueva imagen, actualizamos la columna imagen
if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
    $imagenContent = file_get_contents($_FILES['imagen']['tmp_name']);
    $sql .= ", imagen = ?";
    $types .= "s";
    $params[] = $imagenContent;
}

$sql .= " WHERE id_logro = ?";
$types .= "i";
$params[] = $id_logro;

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error en prepare: " . $conn->error);
}

$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    header("Location: ../admin_logros.php?success=updated");
    exit();
} else {
    die("Error al actualizar el logro: " . $stmt->error);
}

$stmt->close();
$conn->close();
?>
