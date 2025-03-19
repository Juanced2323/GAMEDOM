<?php
session_start();
require_once "php/db_connect.php";

// Verificar que se envíe un ID de juego
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id_juego = intval($_GET['id']);

// Obtener datos del juego
$stmt = $conn->prepare("SELECT * FROM juegos WHERE id_juego = ?");
$stmt->bind_param("i", $id_juego);
$stmt->execute();
$result = $stmt->get_result();
$game = $result->fetch_assoc();
$stmt->close();

if (!$game) {
    echo "Juego no encontrado.";
    exit();
}

// Convertir el icono del juego (almacenado como BLOB) a base64 (suponemos JPG)
if (!empty($game['icono'])) {
    $iconoBase64 = "data:image/jpeg;base64," . base64_encode($game['icono']);
} else {
    $iconoBase64 = 'images/default-game.png';
}

// Obtener ranking (top 3) para este juego
$ranking = [];
$stmt = $conn->prepare("SELECT usuario, elo FROM ranking WHERE id_juego = ? ORDER BY elo DESC LIMIT 3");
$stmt->bind_param("i", $id_juego);
$stmt->execute();
$resultRanking = $stmt->get_result();
while($row = $resultRanking->fetch_assoc()){
    $ranking[] = $row;
}
$stmt->close();

// Obtener torneos asociados a este juego (opcional)
$torneos = [];
$stmt = $conn->prepare("SELECT nombre_torneo, fecha_inicio, fecha_fin, estado FROM torneos WHERE id_juego = ? ORDER BY fecha_inicio DESC");
$stmt->bind_param("i", $id_juego);
$stmt->execute();
$resultTorneos = $stmt->get_result();
while($row = $resultTorneos->fetch_assoc()){
    $torneos[] = $row;
}
$stmt->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo htmlspecialchars($game['nombre']); ?> - Información del Juego</title>
  <link rel="stylesheet" href="css/pantalla_juego.css">
</head>
<body>
  <header>
    <nav class="navbar">
      <div class="nav-left">
        <a href="index.php" class="nav-item">Inicio</a>
        <a href="biblioteca.php" class="nav-item">Biblioteca</a>
        <a href="comunidad.php" class="nav-item">Comunidad</a>
        <a href="premios.php" class="nav-item">Premios</a>
      </div>
      <div class="nav-right">
        <?php if (isset($_SESSION['usuario'])): ?>
          <a href="perfil.php" class="nav-item">Perfil</a>
        <?php else: ?>
          <a href="login.html" class="nav-item">Iniciar Sesión</a>
        <?php endif; ?>
      </div>
    </nav>
  </header>
  <main>
    <div class="game-info-container">
      <!-- Encabezado: Icono y Nombre del Juego -->
      <div class="game-header">
        <img src="<?php echo $iconoBase64; ?>" alt="<?php echo htmlspecialchars($game['nombre']); ?>" class="game-icon">
        <div class="game-name"><?php echo htmlspecialchars($game['nombre']); ?></div>
      </div>
      
      <!-- Descripción -->
      <div class="game-description">
        <?php echo nl2br(htmlspecialchars($game['descripcion'])); ?>
      </div>
      
      <!-- Ranking -->
      <div class="ranking">
        <strong>Ranking de Jugadores:</strong><br>
        <?php if(count($ranking) > 0): ?>
          <?php foreach($ranking as $pos => $r): ?>
            <?php echo ($pos+1) . ". " . htmlspecialchars($r['usuario']) . " - Elo: " . $r['elo'] . "<br>"; ?>
          <?php endforeach; ?>
        <?php else: ?>
          <p>No hay ranking disponible para este juego.</p>
        <?php endif; ?>
      </div>
      
      <!-- Torneos -->
      <div class="tournaments">
        <strong>Torneos Activos:</strong><br>
        <?php if(count($torneos) > 0): ?>
          <?php foreach($torneos as $t): ?>
            <?php 
              $fi = !empty($t['fecha_inicio']) ? $t['fecha_inicio'] : "N/A";
              $ff = !empty($t['fecha_fin']) ? $t['fecha_fin'] : "N/A";
            ?>
            <?php echo htmlspecialchars($t['nombre_torneo']) . " (" . $fi . " - " . $ff . ") - Estado: " . htmlspecialchars($t['estado']) . "<br>"; ?>
          <?php endforeach; ?>
        <?php else: ?>
          <p>No hay torneos para este juego.</p>
        <?php endif; ?>
      </div>
      
      <!-- Botón Jugar Ahora: Actualiza el ranking y luego redirige al juego -->
      <button class="play-button" onclick="playGame(<?php echo $game['id_juego']; ?>, '<?php echo htmlspecialchars($game['ruta_index']); ?>')">
        Jugar Ahora
      </button>
    </div>
  </main>
  
  <script>
  // Función que envía el id del juego al script de actualización del ranking
  function playGame(id_juego, ruta_index) {
    const formData = new FormData();
    formData.append('id_juego', id_juego);

    fetch('php/update_ranking.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if(data.status === 'success'){
        // Redirigir a la ruta almacenada en la base de datos
        window.location.href = ruta_index;
      } else {
        alert("Error al actualizar ranking: " + data.message);
        window.location.href = ruta_index; // Redirige de todas formas
      }
    })
    .catch(error => {
      console.error('Error:', error);
      window.location.href = ruta_index; // Redirige aun en caso de error
    });
  }
  </script>
</body>
</html>
