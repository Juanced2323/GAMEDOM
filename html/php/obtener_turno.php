<?php
header('Content-Type: application/json');
require_once 'db_connect.php';

$input = json_decode(file_get_contents("php://input"), true);
$partida_id = $input['partidaId'] ?? null;

if (!$partida_id) {
    echo json_encode(['success' => false, 'message' => 'ID de partida no proporcionado']);
    exit;
}

$stmt = $conn->prepare("SELECT turno_actual_usuario_id FROM partidas WHERE id = ?");
$stmt->bind_param("i", $partida_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode(['success' => true, 'turno' => $row['turno_actual_usuario_id']]);
} else {
    echo json_encode(['success' => false, 'message' => 'Partida no encontrada']);
}
?>
