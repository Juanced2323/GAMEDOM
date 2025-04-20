<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: text/plain');

$usuario_actual = $_SESSION['usuario'] ?? null;
$destinatario = $_POST['usuario_destino'] ?? '';

if (!$usuario_actual || empty($destinatario)) {
    echo "Petición inválida.";
    exit;
}

if ($usuario_actual === $destinatario) {
    echo "No puedes enviarte una solicitud a ti mismo.";
    exit;
}

// Verificar que el destinatario exista
$stmt = $conn->prepare("SELECT usuario FROM usuarios WHERE usuario = ?");
$stmt->bind_param("s", $destinatario);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    echo "El usuario '$destinatario' no existe.";
    exit;
}
$stmt->close();

// Verificar que no exista ya una relación
$stmt = $conn->prepare("SELECT estado FROM amistades 
                        WHERE (solicitante = ? AND receptor = ?) OR (solicitante = ? AND receptor = ?)");
$stmt->bind_param("ssss", $usuario_actual, $destinatario, $destinatario, $usuario_actual);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo "Ya existe una solicitud o una amistad con ese usuario.";
    exit;
}
$stmt->close();

// Insertar nueva solicitud
$stmt = $conn->prepare("INSERT INTO amistades (solicitante, receptor, estado) VALUES (?, ?, 'pendiente')");
$stmt->bind_param("ss", $usuario_actual, $destinatario);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo "Solicitud enviada correctamente.";
} else {
    echo "Error al enviar la solicitud.";
}

$stmt->close();
$conn->close();
?>
