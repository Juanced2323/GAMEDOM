<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    echo json_encode(["status" => "error", "message" => "Usuario no autenticado."]);
    exit();
}

require_once "db_connect.php";

$username = $_SESSION['usuario'];

// Recoger y limpiar los datos enviados vía POST
$nombre    = isset($_POST['nombre']) ? trim($_POST['nombre']) : "";
$apellidos = isset($_POST['apellidos']) ? trim($_POST['apellidos']) : "";
$edad      = isset($_POST['edad']) ? intval($_POST['edad']) : 0;
$telefono  = isset($_POST['telefono']) ? trim($_POST['telefono']) : "";

// Preparar y ejecutar la actualización
$stmt = $conn->prepare("UPDATE usuarios SET nombre = ?, apellidos = ?, edad = ?, telefono = ? WHERE usuario = ?");
if (!$stmt) {
    echo json_encode(["status" => "error", "message" => "Error en prepare: " . $conn->error]);
    exit();
}
$stmt->bind_param("ssiss", $nombre, $apellidos, $edad, $telefono, $username);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Perfil actualizado correctamente."]);
} else {
    echo json_encode(["status" => "error", "message" => "Error al actualizar: " . $stmt->error]);
}
$stmt->close();
$conn->close();
?>
