<?php
session_start();

// 1) Comprobar sesión
if (!isset($_SESSION['usuario'])) {
    header("Location: ../login.html");
    exit;
}

require_once __DIR__ . "/db_connect.php";
$username = $_SESSION['usuario'];

// 2) Validar datos recibidos
if (!isset($_POST['game_id'], $_POST['contenido'])) {
    die("Solicitud inválida.");
}

$gameId  = $_POST['game_id'];
$contenido = trim($_POST['contenido']);

// Si el usuario selecciona “Otro juego…”, redirigimos a la sección de solicitudes
if ($gameId === 'other') {
    header("Location: ../comunidad.php#request-section");
    exit;
}

if ($contenido === '') {
    die("El comentario no puede estar vacío.");
}

// 3) Procesar imagen (opcional)
$imagenData = null;
if (
    isset($_FILES['imagen']) &&
    $_FILES['imagen']['error'] === UPLOAD_ERR_OK &&
    is_uploaded_file($_FILES['imagen']['tmp_name'])
) {
    $imagenData = file_get_contents($_FILES['imagen']['tmp_name']);
}

// Asegurar que $gameId es entero
$gameId = (int) $gameId;

// 4) Buscar (o crear) el tema “General” para este juego
$stmt = $conn->prepare("
    SELECT id_topic
      FROM forum_topics
     WHERE id_juego = ?
       AND titulo    = 'General'
     LIMIT 1
");
if (!$stmt) {
    die("Error en prepare (buscar tema): " . $conn->error);
}
$stmt->bind_param("i", $gameId);
$stmt->execute();
$res = $stmt->get_result();

if ($row = $res->fetch_assoc()) {
    $topicId = (int) $row['id_topic'];
    $stmt->close();
} else {
    $stmt->close();
    // Creamos el tema “General” si no existía
    $stmt2 = $conn->prepare("
      INSERT INTO forum_topics (id_juego, usuario, titulo, contenido)
      VALUES (?, ?, 'General', '')
    ");
    if (!$stmt2) {
        die("Error en prepare (crear tema): " . $conn->error);
    }
    $stmt2->bind_param("is", $gameId, $username);
    $stmt2->execute();
    $topicId = $stmt2->insert_id;
    $stmt2->close();
}

// 5) Insertar el comentario en forum_posts
if ($imagenData !== null) {
    $stmt3 = $conn->prepare("
      INSERT INTO forum_posts (id_topic, usuario, contenido, imagen)
      VALUES (?, ?, ?, ?)
    ");
    if (!$stmt3) {
        die("Error en prepare (insertar post con imagen): " . $conn->error);
    }
    $stmt3->bind_param("isss", $topicId, $username, $contenido, $imagenData);
} else {
    $stmt3 = $conn->prepare("
      INSERT INTO forum_posts (id_topic, usuario, contenido)
      VALUES (?, ?, ?)
    ");
    if (!$stmt3) {
        die("Error en prepare (insertar post): " . $conn->error);
    }
    $stmt3->bind_param("iss", $topicId, $username, $contenido);
}
$stmt3->execute();
$stmt3->close();

// 6) Cerrar conexión y volver a comunidad.php en el juego correspondiente
$conn->close();
header("Location: ../comunidad.php?game_id={$gameId}");
exit;
