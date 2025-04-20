<?php
session_start();
require_once "db_connect.php";

if (!isset($_SESSION['usuario']) || !isset($_POST['correo']) || !isset($_POST['accion'])) {
    http_response_code(400);
    exit("Solicitud inválida");
}

$correoActual = $_SESSION['correo'];
$correoOtro = $_POST['correo'];
$accion = $_POST['accion'];

if ($accion === 'aceptar') {
    $stmt = $conn->prepare("UPDATE amistades SET estado = 'aceptada' WHERE solicitante = ? AND destinatario = ?");
    $stmt->bind_param("ss", $correoOtro, $correoActual);
} elseif ($accion === 'eliminar') {
    $stmt = $conn->prepare("DELETE FROM amistades WHERE solicitante = ? AND destinatario = ?");
    $stmt->bind_param("ss", $correoOtro, $correoActual);
} else {
    http_response_code(400);
    exit("Acción no válida");
}

if ($stmt->execute()) {
    header("Location: ../perfil.php");
    exit();
} else {
    echo "Error al procesar la solicitud.";
}
