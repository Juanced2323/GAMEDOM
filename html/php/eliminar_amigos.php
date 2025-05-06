<?php
session_start();
require_once 'db_connect.php';

$usuario = $_SESSION['usuario'] ?? null;
if (!$usuario || !isset($_POST['amigos'])) {
    http_response_code(400);
    echo "Petición inválida.";
    exit;
}

$amigos = $_POST['amigos'];
$eliminados = 0;

$stmt = $conn->prepare("DELETE FROM amistades 
                        WHERE estado = 'aceptada' AND 
                        ((solicitante = ? AND receptor = ?) OR (solicitante = ? AND receptor = ?))");

foreach ($amigos as $otroUsuario) {
    $stmt->bind_param("ssss", $usuario, $otroUsuario, $otroUsuario, $usuario);
    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        $eliminados++;
    }
}

$stmt->close();
$conn->close();

echo "$eliminados amigo(s) eliminado(s) correctamente.";
?>