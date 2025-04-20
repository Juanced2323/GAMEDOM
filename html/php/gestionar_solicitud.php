<?php
session_start();
require_once 'db_connect.php';

$usuario_actual = $_SESSION['usuario'] ?? null;

if (!$usuario_actual || !isset($_POST['solicitante'], $_POST['accion'])) {
    http_response_code(400);
    echo "Petición inválida.";
    exit;
}

$solicitante = $_POST['solicitante'];
$accion = $_POST['accion'];

if (!in_array($accion, ['aceptada', 'rechazada'])) {
    http_response_code(400);
    echo "Acción no válida.";
    exit;
}

$sql = "UPDATE amistades SET estado = ? 
        WHERE solicitante = ? AND receptor = ? AND estado = 'pendiente'";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $accion, $solicitante, $usuario_actual);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo "Solicitud $accion correctamente.";
} else {
    echo "No se encontró la solicitud o ya fue procesada.";
}

$stmt->close();
$conn->close();
?>
