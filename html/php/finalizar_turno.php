<?php
header('Content-Type: application/json');
require_once 'db_connect.php';

$input = json_decode(file_get_contents("php://input"), true);
$partida_id = $input['partidaId'] ?? null;
$usuario_id = $input['usuarioId'] ?? null;

if (!$partida_id || !$usuario_id) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
    exit;
}

// 1. Obtener el orden_turno actual del usuario
$stmt = $conn->prepare("SELECT orden_turno FROM partidas_usuarios WHERE partida_id = ? AND usuario_id = ?");
$stmt->bind_param("is", $partida_id, $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

if (!$row = $result->fetch_assoc()) {
    echo json_encode(['success' => false, 'message' => 'Usuario no encontrado en la partida.']);
    exit;
}

$orden_actual = $row['orden_turno'];

// 2. Obtener el total de jugadores
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM partidas_usuarios WHERE partida_id = ?");
$stmt->bind_param("i", $partida_id);
$stmt->execute();
$total_jugadores = $stmt->get_result()->fetch_assoc()['total'];

// 3. Calcular el siguiente orden
$orden_siguiente = ($orden_actual + 1) % $total_jugadores;

// 4. Obtener el usuario con ese orden
$stmt = $conn->prepare("SELECT usuario_id FROM partidas_usuarios WHERE partida_id = ? AND orden_turno = ?");
$stmt->bind_param("ii", $partida_id, $orden_siguiente);
$stmt->execute();
$result = $stmt->get_result();

if (!$row = $result->fetch_assoc()) {
    echo json_encode(['success' => false, 'message' => 'No se encontró el siguiente jugador.']);
    exit;
}

$siguiente_usuario = $row['usuario_id'];

// 5. Actualizar el turno_actual_usuario_id
$stmt = $conn->prepare("UPDATE partidas SET turno_actual_usuario_id = ? WHERE id = ?");
$stmt->bind_param("si", $siguiente_usuario, $partida_id);
$stmt->execute();

echo json_encode([
    'success' => true,
    'siguiente' => $siguiente_usuario
]);
?>
