<?php
require_once "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST["nombre"]);
    $id_juego = intval($_POST["id_juego"]);
    $descripcion = $_POST["descripcion"];
    $fecha_inicio = $_POST["fecha_inicio"];
    $fecha_fin = $_POST["fecha_fin"];
    $estado = $_POST["estado"];
    $elo_minimo = intval($_POST["elo_minimo"]);
    $max_jugadores = intval($_POST["max_jugadores"]);

    // Preparamos la consulta con verificación de error
    $stmt = $conn->prepare("INSERT INTO torneos (id_juego, nombre_torneo, fecha_inicio, fecha_fin, estado, descripcion, elo_minimo, max_jugadores)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        die("Error en prepare: " . $conn->error);
    }

    $stmt->bind_param("isssssii", $id_juego, $nombre, $fecha_inicio, $fecha_fin, $estado, $descripcion, $elo_minimo, $max_jugadores);

    if ($stmt->execute()) {
        header("Location: ../admin_torneos.php?success=1");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>
