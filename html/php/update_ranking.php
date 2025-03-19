<?php
session_start();

// Verificamos que el usuario esté logueado
if (!isset($_SESSION['usuario'])) {
    echo json_encode([
        "status" => "error",
        "message" => "No hay sesión de usuario"
    ]);
    exit();
}

require_once "db_connect.php";

// Obtenemos el id del juego enviado por POST
$id_juego = intval($_POST['id_juego'] ?? 0);
$usuario = $_SESSION['usuario'];
$elo_a_sumar = 50; // Cantidad de elo que sumamos al jugar

// Verificamos si ya existe un registro para (id_juego, usuario)
$stmt = $conn->prepare("SELECT id_ranking, elo FROM ranking WHERE id_juego = ? AND usuario = ?");
if (!$stmt) {
    echo json_encode([
        "status" => "error",
        "message" => "Error en prepare: " . $conn->error
    ]);
    exit();
}
$stmt->bind_param("is", $id_juego, $usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    // Existe registro, sumamos 50 al elo actual
    $row = $result->fetch_assoc();
    $nuevo_elo = $row['elo'] + $elo_a_sumar;

    $stmt->close();
    $stmt = $conn->prepare("UPDATE ranking SET elo = ? WHERE id_ranking = ?");
    if (!$stmt) {
        echo json_encode([
            "status" => "error",
            "message" => "Error en prepare (update): " . $conn->error
        ]);
        exit();
    }
    $stmt->bind_param("ii", $nuevo_elo, $row['id_ranking']);
} else {
    // No existe registro, insertamos uno nuevo con elo = 50
    $stmt->close();
    $stmt = $conn->prepare("INSERT INTO ranking (id_juego, usuario, elo) VALUES (?, ?, ?)");
    if (!$stmt) {
        echo json_encode([
            "status" => "error",
            "message" => "Error en prepare (insert): " . $conn->error
        ]);
        exit();
    }
    $stmt->bind_param("isi", $id_juego, $usuario, $elo_a_sumar);
}

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Ranking actualizado correctamente."
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>
