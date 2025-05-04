<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../login.html");
    exit;
}
require_once __DIR__ . "/db_connect.php";
$username = $_SESSION['usuario'];

// 1) Validación básica
if (!isset($_POST['game_id'], $_POST['contenido'])) {
    die("Solicitud inválida: faltan parámetros.");
}
$gameId = intval($_POST['game_id']);
$contenido = trim($_POST['contenido']);
if ($contenido === '') {
    die("El comentario no puede estar vacío.");
}

// 2) Procesar imagen (opcional)
$imagenData = null;
if (
    isset($_FILES['imagen']) &&
    $_FILES['imagen']['error'] === UPLOAD_ERR_OK &&
    is_uploaded_file($_FILES['imagen']['tmp_name'])
) {
    $imagenData = file_get_contents($_FILES['imagen']['tmp_name']);
}

// 3) Determinar o crear el hilo (topic) correcto
if ($gameId > 0) {
    // Hilo "General" para el juego concreto
    $sqlTopic = "
      SELECT id_topic
        FROM forum_topics
       WHERE id_juego = ?
         AND titulo    = 'General'
       LIMIT 1
    ";
    $stmt = $conn->prepare($sqlTopic)
        or die("Error en prepare(topic select): " . $conn->error);
    $stmt->bind_param("i", $gameId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $topicId = (int)$row['id_topic'];
    } else {
        // Creamos el hilo si no existía
        $stmt->close();
        $sqlNewTopic = "
          INSERT INTO forum_topics (id_juego, usuario, titulo, contenido)
          VALUES (?, ?, 'General', '')
        ";
        $stmt2 = $conn->prepare($sqlNewTopic)
            or die("Error en prepare(topic insert): " . $conn->error);
        $stmt2->bind_param("is", $gameId, $username);
        $stmt2->execute()
            or die("Error al crear hilo: " . $stmt2->error);
        $topicId = $stmt2->insert_id;
        $stmt2->close();
    }
    $stmt->close();

} else {
    // Hilo "General" global (id_juego = NULL)
    $sqlTopic = "
      SELECT id_topic
        FROM forum_topics
       WHERE id_juego IS NULL
         AND titulo    = 'General'
       LIMIT 1
    ";
    $stmt = $conn->prepare($sqlTopic)
        or die("Error en prepare(topic select global): " . $conn->error);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $topicId = (int)$row['id_topic'];
    } else {
        $stmt->close();
        $sqlNewTopic = "
          INSERT INTO forum_topics (id_juego, usuario, titulo, contenido)
          VALUES (NULL, ?, 'General', '')
        ";
        $stmt2 = $conn->prepare($sqlNewTopic)
            or die("Error en prepare(topic insert global): " . $conn->error);
        $stmt2->bind_param("s", $username);
        $stmt2->execute()
            or die("Error al crear hilo global: " . $stmt2->error);
        $topicId = $stmt2->insert_id;
        $stmt2->close();
    }
    $stmt->close();
}

// 4) Insertar el post en forum_posts
if ($imagenData !== null) {
    $sqlPost = "
      INSERT INTO forum_posts (id_topic, usuario, contenido, imagen)
      VALUES (?, ?, ?, ?)
    ";
    $stmt3 = $conn->prepare($sqlPost)
        or die("Error en prepare(post con imagen): " . $conn->error);
    // Usar send_long_data para BLOB grandes
    $stmt3->bind_param("issb", $topicId, $username, $contenido, $null)
        or die("Error en bind_param(post con imagen): " . $stmt3->error);
    $stmt3->send_long_data(3, $imagenData);
} else {
    $sqlPost = "
      INSERT INTO forum_posts (id_topic, usuario, contenido)
      VALUES (?, ?, ?)
    ";
    $stmt3 = $conn->prepare($sqlPost)
        or die("Error en prepare(post): " . $conn->error);
    $stmt3->bind_param("iss", $topicId, $username, $contenido)
        or die("Error en bind_param(post): " . $stmt3->error);
}

$stmt3->execute()
    or die("Error al insertar post: " . $stmt3->error);
$stmt3->close();

// 5) Redirigir a comunidad para que se recargue el feed
header("Location: ../comunidad.php");
exit;
