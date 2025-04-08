<?php
session_start();

header('Content-Type: application/json');

// Verificamos si el usuario está logueado
if (!isset($_SESSION['usuario'])) {
    echo json_encode([]);
    exit();
}

require_once "db_connect.php";

$usuario = $_SESSION['usuario'];

$sql = "
    SELECT 
        p.id AS partida_id,
        j.nombre AS nombre_juego,
        CASE WHEN p.turno_actual_usuario_id = ? THEN 'Sí' ELSE 'No' END AS es_tu_turno
    FROM partidas_usuarios pu
    INNER JOIN partidas p ON pu.partida_id = p.id
    INNER JOIN juegos j ON p.juego_id = j.id_juego
    WHERE pu.usuario_id = ?
    ORDER BY p.fecha_creacion DESC
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['error' => 'Error en la preparación: ' . $conn->error]);
    exit();
}

$stmt->bind_param("ss", $usuario, $usuario);
$stmt->execute();

$result = $stmt->get_result();
$partidas = [];

while ($row = $result->fetch_assoc()) {
    $partidas[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode($partidas);
?>
