<?php
session_start();
require_once "db_connect.php";

if (!isset($_SESSION['usuario']) || !isset($_POST['id_torneo'])) {
    header("Location: ../torneos.php?msg=acceso_denegado");
    exit;
}

$usuario = $_SESSION['usuario'];
$id_torneo = intval($_POST['id_torneo']);

// Verificar si ya está inscrito
$stmt = $conn->prepare("SELECT 1 FROM inscripciones_torneo WHERE id_torneo = ? AND usuario = ?");
if (!$stmt) {
    die("Error al preparar verificación: " . $conn->error);
}
$stmt->bind_param("is", $id_torneo, $usuario);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    header("Location: ../torneos.php?msg=ya_inscrito");
    exit;
}
$stmt->close();

// Obtener datos del torneo y nombre del juego
$stmt = $conn->prepare("
    SELECT t.jugadores_actuales, t.max_jugadores, j.nombre AS nombre_juego
    FROM torneos t
    JOIN juegos j ON t.id_juego = j.id_juego
    WHERE t.id_torneo = ?
");
if (!$stmt) {
    die("Error al preparar consulta de torneo: " . $conn->error);
}
$stmt->bind_param("i", $id_torneo);
$stmt->execute();
$stmt->bind_result($actual, $max, $nombre_juego);
$stmt->fetch();
$stmt->close();

// Si ya está lleno, redirigir al juego
if ($actual >= $max) {
    $carpetaJuego = str_replace(' ', '', $nombre_juego);
    header("Location: ../games/$carpetaJuego/index.html");
    exit;
}

// Intentar registrar la inscripción
$stmt = $conn->prepare("INSERT INTO inscripciones_torneo (id_torneo, usuario) VALUES (?, ?)");
if (!$stmt) {
    die("Error al preparar inserción: " . $conn->error);
}
$stmt->bind_param("is", $id_torneo, $usuario);

if (!$stmt->execute()) {
    if ($conn->errno === 1062) {
        header("Location: ../torneos.php?msg=ya_inscrito");
        exit;
    } else {
        die("Error al ejecutar inserción: " . $conn->error);
    }
}
$stmt->close();

// Actualizar el contador de jugadores
$conn->query("UPDATE torneos SET jugadores_actuales = jugadores_actuales + 1 WHERE id_torneo = $id_torneo");

// Activar el torneo si se llenó
$conn->query("UPDATE torneos SET estado = 'activo' WHERE id_torneo = $id_torneo AND jugadores_actuales >= max_jugadores");

// Verificar si se llenó justo ahora
$stmt = $conn->prepare("SELECT jugadores_actuales, max_jugadores FROM torneos WHERE id_torneo = ?");
$stmt->bind_param("i", $id_torneo);
$stmt->execute();
$stmt->bind_result($actualFinal, $maxFinal);
$stmt->fetch();
$stmt->close();

if ($actualFinal >= $maxFinal) {
    $carpetaJuego = str_replace(' ', '', $nombre_juego);
    header("Location: ../games/$carpetaJuego/index.html");
    exit;
}

// Si aún no se ha llenado
header("Location: ../torneos.php?msg=esperando_jugadores");
exit;
?>
