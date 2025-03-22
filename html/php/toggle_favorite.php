<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    echo json_encode(["status" => "error", "message" => "Usuario no autenticado"]);
    exit();
}
require_once "db_connect.php";

$usuario = $_SESSION['usuario'];
$id_juego = intval($_POST['id_juego'] ?? 0);
if ($id_juego == 0) {
    echo json_encode(["status" => "error", "message" => "ID de juego inválido"]);
    exit();
}

// Comprobar si el juego ya está en favoritos
$stmt = $conn->prepare("SELECT * FROM favoritos WHERE usuario = ? AND id_juego = ?");
if (!$stmt) {
    echo json_encode(["status" => "error", "message" => $conn->error]);
    exit();
}
$stmt->bind_param("si", $usuario, $id_juego);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // El juego ya está en favoritos, se elimina
    $stmt->close();
    $stmt = $conn->prepare("DELETE FROM favoritos WHERE usuario = ? AND id_juego = ?");
    $stmt->bind_param("si", $usuario, $id_juego);
    if ($stmt->execute()) {
        echo json_encode(["status" => "removed", "message" => "Favorito eliminado"]);
    } else {
        echo json_encode(["status" => "error", "message" => $stmt->error]);
    }
} else {
    // No está en favoritos, se añade
    $stmt->close();
    $stmt = $conn->prepare("INSERT INTO favoritos (usuario, id_juego) VALUES (?, ?)");
    $stmt->bind_param("si", $usuario, $id_juego);
    if ($stmt->execute()) {
        echo json_encode(["status" => "added", "message" => "Favorito añadido"]);
    } else {
        echo json_encode(["status" => "error", "message" => $stmt->error]);
    }
}
$stmt->close();
$conn->close();
?>
