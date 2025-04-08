<?php
session_start();
require_once "db_connect.php"; // Conexión a la base de datos

$usuario_id = $_SESSION['usuario']; // Usuario actual
$juego_id = intval($_POST['juego_id']);

// 1. Obtener el número máximo de jugadores del juego
$stmt = $conn->prepare("SELECT max_jugadores, ruta_index FROM juegos WHERE id_juego = ?");
$stmt->bind_param("i", $juego_id);
$stmt->execute();
$result = $stmt->get_result();
$juego = $result->fetch_assoc();
$max_jugadores = $juego['max_jugadores'];
$ruta_index = $juego['ruta_index'];

// 2. Buscar una partida en progreso que no esté llena
$sql = "
    SELECT p.id, COUNT(pu.usuario_id) as jugadores_actuales
    FROM partidas p
    LEFT JOIN partidas_usuarios pu ON p.id = pu.partida_id
    WHERE p.juego_id = ? AND p.estado = 'en_progreso'
    GROUP BY p.id
    HAVING jugadores_actuales < ?
    LIMIT 1
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $juego_id, $max_jugadores);
$stmt->execute();
$result = $stmt->get_result();
$partida = $result->fetch_assoc();

if ($partida) {
    // 3. Agregar al usuario a la partida existente
    $partida_id = $partida['id'];

    // Verificar que el usuario no esté ya en esa partida
    $check = $conn->prepare("SELECT * FROM partidas_usuarios WHERE partida_id = ? AND usuario_id = ?");
    $check->bind_param("is", $partida_id, $usuario_id);
    $check->execute();
    if (!$check->get_result()->num_rows) {
        // Obtener orden_turno siguiente
        $orden = $partida['jugadores_actuales'];
        $stmt = $conn->prepare("INSERT INTO partidas_usuarios (partida_id, usuario_id, orden_turno) VALUES (?, ?, ?)");
        $stmt->bind_param("isi", $partida_id, $usuario_id, $orden);
        $stmt->execute();
    }
} else {
    // 4. Crear nueva partida
    $stmt = $conn->prepare("INSERT INTO partidas (juego_id, turno_actual_usuario_id) VALUES (?, ?)");
    $stmt->bind_param("is", $juego_id, $usuario_id);
    $stmt->execute();
    $partida_id = $conn->insert_id;

    // Insertar al primer jugador con orden_turno = 0
    $stmt = $conn->prepare("INSERT INTO partidas_usuarios (partida_id, usuario_id, orden_turno) VALUES (?, ?, 0)");
    $stmt->bind_param("is", $partida_id, $usuario_id);
    $stmt->execute();
}

// Redirigir al juego
echo json_encode([
    'status' => 'success',
    'redirect' => $ruta_index . "?partida_id=" . $partida_id
]);

?>
