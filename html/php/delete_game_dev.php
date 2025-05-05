<?php
session_start();

// 1) Conexión
require_once __DIR__ . '/db_connect.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: ../login.html");
    exit();
}

$usuario = $_SESSION['usuario'];

// 2) Obtener ID desde query string
if (!isset($_GET['id'])) {
    die("❌ ID de juego no especificado.");
}
$id_juego = intval($_GET['id']);

// 3) Verificar que el desarrollador es el propietario del juego
$stmtChk = $conn->prepare("
    SELECT dj.id_juego
      FROM desarrolladores_juegos dj
      JOIN desarrolladores d
        ON d.id_desarrollador = dj.id_desarrollador
     WHERE d.usuario = ?
       AND dj.id_juego = ?
    LIMIT 1
");
$stmtChk->bind_param("si", $usuario, $id_juego);
$stmtChk->execute();
$resChk = $stmtChk->get_result();
if (!$resChk->fetch_assoc()) {
    die("❌ No tienes permisos para eliminar este juego.");
}
$stmtChk->close();

// 4) Eliminar el juego (cascade en las tablas relacionadas)
$stmtDel = $conn->prepare("DELETE FROM juegos WHERE id_juego = ?");
$stmtDel->bind_param("i", $id_juego);
if (!$stmtDel->execute()) {
    die("❌ Error al eliminar el juego: " . $stmtDel->error);
}
$stmtDel->close();
$conn->close();

// 5) Redirigir a Mis Juegos
header("Location: ../mis_juegos.php?success=deleted");
exit();
