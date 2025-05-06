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
require_once "logros.php"; // Archivo con función asignarLogro()

$usuario = $_SESSION['usuario'];
// Se cambia de 'id_juego' a 'juego_id'
$id_juego = intval($_POST['juego_id'] ?? 0);
$elo_a_sumar = 50; // Asegúrate de que este valor sea el adecuado para sumar

// Unificar y normalizar el resultado de la partida (se espera: "victoria", "derrota" o "jugado")
$resultadoPartida = strtolower(trim($_POST['resultado'] ?? 'derrota'));

// Para depuración (activa estas líneas si lo necesitas)
// error_log("Usuario: " . $usuario);
// error_log("ID Juego: " . $id_juego);
// error_log("Resultado de la partida: " . $resultadoPartida);

$logros_otorgados = []; // Array para logros otorgados (para notificaciones)

// ============================================================
// 1. ACTUALIZACIÓN DEL RANKING
// ============================================================
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
    // Registro existente: se suma el ELO
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
    // No existe registro: se inserta uno nuevo
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

// ============================================================
// 2. REGISTRO DE LA PARTIDA EN HISTORIAL_JUEGOS
// ============================================================
$stmtHist = $conn->prepare("INSERT INTO historial_juegos (usuario, id_juego, resultado) VALUES (?, ?, ?)");
if (!$stmtHist) {
    echo json_encode([
        "status"  => "error",
        "message" => "Error en prepare (historial INSERT): " . $conn->error
    ]);
    exit();
}
$stmtHist->bind_param("sis", $usuario, $id_juego, $resultadoPartida);
$stmtHist->execute();
$stmtHist->close();

// ============================================================
// 2.1. LOGRO: PRIMERA PARTIDA GENERAL
// ============================================================
$stmtGeneral = $conn->prepare("SELECT COUNT(*) as total FROM historial_juegos WHERE usuario = ?");
$stmtGeneral->bind_param("s", $usuario);
$stmtGeneral->execute();
$resultGeneral = $stmtGeneral->get_result();
$totalGeneral = $resultGeneral->fetch_assoc()['total'];
$stmtGeneral->close();

if ($totalGeneral == 1) {
    asignarLogro($usuario, 1); // Se asume id_logro=1 para "Primer Juego"
    $logros_otorgados[] = [
        "id"          => 1,
        "nombre"      => "Primer Juego",
        "descripcion" => "Juega tu primera partida",
        "imagen"      => "images/primer_juego.png"
    ];
}

// ============================================================
// 3. LOGRO GLOBAL: ELO >= 1000 ("Milenario")
// ============================================================
if ($nuevo_elo >= 1000) {
    asignarLogro($usuario, 6); // Se asume id_logro=6 para "Milenario"
    $logros_otorgados[] = [
        "id"          => 6,
        "nombre"      => "Milenario",
        "descripcion" => "Alcanza 1000 puntos de ELO en cualquier juego",
        "imagen"      => "images/milenario.png"
    ];
}

// ============================================================
// 4. OBTENCIÓN DEL NOMBRE DEL JUEGO
// ============================================================
$stmt = $conn->prepare("SELECT nombre FROM juegos WHERE id_juego = ?");
$stmt->bind_param("i", $id_juego);
$stmt->execute();
$resultGame = $stmt->get_result();
$nombreJuego = "";
if ($rowGame = $resultGame->fetch_assoc()) {
    $nombreJuego = trim($rowGame['nombre']); // Se aplica trim() para evitar problemas de espacios adicionales
    // error_log("Nombre del juego: [" . $nombreJuego . "]");
}
$stmt->close();

// ============================================================
// 5. ASIGNACIÓN DE LOGROS ESPECÍFICOS POR JUEGO
// ============================================================
if (strcasecmp($nombreJuego, "Risk") == 0) {
    // --- Logro: Novato en Risk (Primera partida en Risk)
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM historial_juegos WHERE usuario = ? AND id_juego = ?");
    $stmt->bind_param("si", $usuario, $id_juego);
    $stmt->execute();
    $resultCount = $stmt->get_result();
    $totalPartidas = $resultCount->fetch_assoc()['total'];
    $stmt->close();

    if ($totalPartidas == 1) {
        asignarLogro($usuario, 2); // id_logro=2: "Novato en Risk"
        $logros_otorgados[] = [
            "id"          => 2,
            "nombre"      => "Novato en Risk",
            "descripcion" => "Juega tu primera partida en Risk",
            "imagen"      => "images/novato_risk.png"
        ];
    }

    // --- Logro: Primera Victoria en Risk
    if ($resultadoPartida === "victoria") {
        $stmt = $conn->prepare("SELECT COUNT(*) as victorias FROM historial_juegos WHERE usuario = ? AND id_juego = ? AND resultado = 'victoria'");
        $stmt->bind_param("si", $usuario, $id_juego);
        $stmt->execute();
        $resultWins = $stmt->get_result();
        $victorias = $resultWins->fetch_assoc()['victorias'];
        $stmt->close();

        if ($victorias == 1) {
            asignarLogro($usuario, 4); // id_logro=4: "Primera Victoria en Risk"
            $logros_otorgados[] = [
                "id"          => 4,
                "nombre"      => "Primera Victoria en Risk",
                "descripcion" => "Gana tu primera partida en Risk",
                "imagen"      => "images/victoria_risk.png"
            ];
        }
    }
} elseif (strcasecmp($nombreJuego, "Hundir la Flota") == 0) {
    // --- Logro: Novato Naval (Primera partida en Hundir la Flota)
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM historial_juegos WHERE usuario = ? AND id_juego = ?");
    $stmt->bind_param("si", $usuario, $id_juego);
    $stmt->execute();
    $resultCount = $stmt->get_result();
    $totalPartidas = $resultCount->fetch_assoc()['total'];
    $stmt->close();

    if ($totalPartidas == 1) {
        asignarLogro($usuario, 3); // id_logro=3: "Novato Naval"
        $logros_otorgados[] = [
            "id"          => 3,
            "nombre"      => "Novato Naval",
            "descripcion" => "Juega tu primera partida en Hundir la Flota",
            "imagen"      => "images/novato_flot.png"
        ];
    }

    // --- Logro: Primera Victoria Naval
    if ($resultadoPartida === "victoria") {
        $stmt = $conn->prepare("SELECT COUNT(*) as victorias FROM historial_juegos WHERE usuario = ? AND id_juego = ? AND resultado = 'victoria'");
        $stmt->bind_param("si", $usuario, $id_juego);
        $stmt->execute();
        $resultWins = $stmt->get_result();
        $victorias = $resultWins->fetch_assoc()['victorias'];
        $stmt->close();

        if ($victorias == 1) {
            asignarLogro($usuario, 5); // id_logro=5: "Primera Victoria Naval"
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

// ============================================================
// 6. DEVOLVER RESPUESTA EN FORMATO JSON
// ============================================================
echo json_encode([
    "status"       => "success",
    "message"      => "Ranking y logros actualizados correctamente.",
    "achievements" => $logros_otorgados
]);
exit();
?>
