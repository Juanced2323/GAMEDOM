<?php
session_start();

// Verificar que el usuario esté logueado
if (!isset($_SESSION['usuario'])) {
    echo json_encode([
        "status"  => "error",
        "message" => "No hay sesión de usuario"
    ]);
    exit();
}

require_once "db_connect.php";
require_once "logros.php"; // Ahora se carga desde el mismo directorio

$usuario = $_SESSION['usuario'];
$id_juego = intval($_POST['id_juego'] ?? 0);
$resultadoPartida = trim($_POST['resultado'] ?? 'derrota'); // Se espera 'victoria' o 'derrota'
$elo_a_sumar = 50; // Puntos a sumar

// Array para logros otorgados (para notificaciones)
$logros_otorgados = [];

// ----------------------------------------------------
// 1. Actualización del Ranking
// ----------------------------------------------------
$stmt = $conn->prepare("SELECT id_ranking, elo FROM ranking WHERE id_juego = ? AND usuario = ?");
if (!$stmt) {
    echo json_encode([
        "status"  => "error",
        "message" => "Error en prepare (ranking SELECT): " . $conn->error
    ]);
    exit();
}
$stmt->bind_param("is", $id_juego, $usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    // Registro existente: sumamos los puntos
    $row = $result->fetch_assoc();
    $nuevo_elo = $row['elo'] + $elo_a_sumar;
    $stmt->close();

    $stmt = $conn->prepare("UPDATE ranking SET elo = ? WHERE id_ranking = ?");
    if (!$stmt) {
        echo json_encode([
            "status"  => "error",
            "message" => "Error en prepare (ranking UPDATE): " . $conn->error
        ]);
        exit();
    }
    $stmt->bind_param("ii", $nuevo_elo, $row['id_ranking']);
} else {
    // No existe registro: insertamos uno nuevo
    $stmt->close();
    $stmt = $conn->prepare("INSERT INTO ranking (id_juego, usuario, elo) VALUES (?, ?, ?)");
    if (!$stmt) {
        echo json_encode([
            "status"  => "error",
            "message" => "Error en prepare (ranking INSERT): " . $conn->error
        ]);
        exit();
    }
    $nuevo_elo = $elo_a_sumar;
    $stmt->bind_param("isi", $id_juego, $usuario, $nuevo_elo);
}
$stmt->execute();
$stmt->close();

// ----------------------------------------------------
// 2. Registrar la partida en historial_juegos
// ----------------------------------------------------
$resultadoPartidaDB = $_POST['resultado'] ?? 'jugado';  // Valor predeterminado
$stmtHist = $conn->prepare("INSERT INTO historial_juegos (usuario, id_juego, resultado) VALUES (?, ?, ?)");
if (!$stmtHist) {
    echo json_encode([
        "status"  => "error",
        "message" => "Error en prepare (historial INSERT): " . $conn->error
    ]);
    exit();
}
$stmtHist->bind_param("sis", $usuario, $id_juego, $resultadoPartidaDB);
$stmtHist->execute();
$stmtHist->close();

// ----------------------------------------------------
// 3. Asignar Logro Global: ELO >= 1000 ("Milenario")
// ----------------------------------------------------
if ($nuevo_elo >= 1000) {
    asignarLogro($usuario, 6); // Suponiendo id_logro=6 para "Milenario"
    $logros_otorgados[] = [
        "id"          => 6,
        "nombre"      => "Milenario",
        "descripcion" => "Alcanza 1000 puntos de ELO en cualquier juego",
        "imagen"      => "images/milenario.png"
    ];
}

// ----------------------------------------------------
// 4. Obtener el nombre del juego para logros específicos
// ----------------------------------------------------
$stmt = $conn->prepare("SELECT nombre FROM juegos WHERE id_juego = ?");
$stmt->bind_param("i", $id_juego);
$stmt->execute();
$resultGame = $stmt->get_result();
$nombreJuego = "";
if ($rowGame = $resultGame->fetch_assoc()) {
    $nombreJuego = $rowGame['nombre'];
}
$stmt->close();

// ----------------------------------------------------
// 5. Lógica para logros específicos según el juego
// ----------------------------------------------------
if (strcasecmp($nombreJuego, "Risk") == 0) {
    // Contar partidas jugadas en Risk
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM historial_juegos WHERE usuario = ? AND id_juego = ?");
    $stmt->bind_param("si", $usuario, $id_juego);
    $stmt->execute();
    $resultCount = $stmt->get_result();
    $totalPartidas = $resultCount->fetch_assoc()['total'];
    $stmt->close();

    if ($totalPartidas == 1) {
        asignarLogro($usuario, 2); // "Novato en Risk"
        $logros_otorgados[] = [
            "id"          => 2,
            "nombre"      => "Novato en Risk",
            "descripcion" => "Juega tu primera partida en Risk",
            "imagen"      => "images/novato_risk.png"
        ];
    }

    if (strcasecmp($resultadoPartidaDB, "victoria") == 0) {
        // Contar las victorias en Risk
        $stmt = $conn->prepare("SELECT COUNT(*) as victorias FROM historial_juegos WHERE usuario = ? AND id_juego = ? AND resultado = 'victoria'");
        $stmt->bind_param("si", $usuario, $id_juego);
        $stmt->execute();
        $resultWins = $stmt->get_result();
        $victorias = $resultWins->fetch_assoc()['victorias'];
        $stmt->close();

        if ($victorias == 1) {
            asignarLogro($usuario, 4); // "Primera Victoria en Risk"
            $logros_otorgados[] = [
                "id"          => 4,
                "nombre"      => "Primera Victoria en Risk",
                "descripcion" => "Gana tu primera partida en Risk",
                "imagen"      => "images/victoria_risk.png"
            ];
        }
    }
} elseif (strcasecmp($nombreJuego, "Hundir la Flota") == 0) {
    // Contar partidas jugadas en Hundir la Flota
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM historial_juegos WHERE usuario = ? AND id_juego = ?");
    $stmt->bind_param("si", $usuario, $id_juego);
    $stmt->execute();
    $resultCount = $stmt->get_result();
    $totalPartidas = $resultCount->fetch_assoc()['total'];
    $stmt->close();

    if ($totalPartidas == 1) {
        asignarLogro($usuario, 3); // "Novato Naval"
        $logros_otorgados[] = [
            "id"          => 3,
            "nombre"      => "Novato Naval",
            "descripcion" => "Juega tu primera partida en Hundir la Flota",
            "imagen"      => "images/novato_flot.png"
        ];
    }

    if (strcasecmp($resultadoPartidaDB, "victoria") == 0) {
        // Contar las victorias en Hundir la Flota
        $stmt = $conn->prepare("SELECT COUNT(*) as victorias FROM historial_juegos WHERE usuario = ? AND id_juego = ? AND resultado = 'victoria'");
        $stmt->bind_param("si", $usuario, $id_juego);
        $stmt->execute();
        $resultWins = $stmt->get_result();
        $victorias = $resultWins->fetch_assoc()['victorias'];
        $stmt->close();

        if ($victorias == 1) {
            asignarLogro($usuario, 5); // "Primera Victoria Naval"
            $logros_otorgados[] = [
                "id"          => 5,
                "nombre"      => "Primera Victoria Naval",
                "descripcion" => "Gana tu primera partida en Hundir la Flota",
                "imagen"      => "images/victoria_flot.png"
            ];
        }
    }
}

$conn->close();

// Devolver la respuesta en JSON
echo json_encode([
    "status"      => "success",
    "message"     => "Ranking y logros actualizados correctamente.",
    "achievements"=> $logros_otorgados
]);
exit();
?>
