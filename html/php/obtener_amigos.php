<?php
session_start();
header('Content-Type: application/json');

require_once 'db_connect.php';

$usuario = $_SESSION['usuario'] ?? null;
if (!$usuario) {
    echo json_encode([]);
    exit;
}

$sql = "SELECT u.usuario, u.imagen
        FROM amistades a
        JOIN usuarios u ON u.usuario = IF(a.solicitante = ?, a.receptor, a.solicitante)
        WHERE (a.solicitante = ? OR a.receptor = ?) AND a.estado = 'aceptada'
        LIMIT 5";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $usuario, $usuario, $usuario);
$stmt->execute();
$result = $stmt->get_result();

$amigos = [];
while ($row = $result->fetch_assoc()) {
    $amigos[] = [
        'usuario' => $row['usuario'],
        'imagen' => $row['imagen'] ? 'data:image/jpeg;base64,' . base64_encode($row['imagen']) : 'images/default-profile.png'
    ];
}

echo json_encode($amigos);
?>
