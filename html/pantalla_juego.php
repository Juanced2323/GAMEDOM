<?php 
session_start();
require_once "php/db_connect.php";

// Verificar que se envíe un ID de juego por GET
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id_juego = intval($_GET['id']);

// Obtener datos del juego
$stmt = $conn->prepare("SELECT * FROM juegos WHERE id_juego = ?");
if (!$stmt) {
    die("Error en prepare (juegos): " . $conn->error);
}
$stmt->bind_param("i", $id_juego);
$stmt->execute();
$result = $stmt->get_result();
$game = $result->fetch_assoc();
$stmt->close();

if (!$game) {
    echo "Juego no encontrado.";
    exit();
}

// Convertir el icono del juego (almacenado como BLOB) a base64
if (!empty($game['icono'])) {
    $iconoBase64 = "data:image/jpeg;base64," . base64_encode($game['icono']);
} else {
    $iconoBase64 = 'images/default-game.png';
}

// Preparar la captura (si existe en la columna capturas)
$capturesBase64 = null;
if (!empty($game['capturas'])) {
    $capturesBase64 = "data:image/jpeg;base64," . base64_encode($game['capturas']);
}

// Obtener las categorías asociadas al juego
$categories = [];
$stmt = $conn->prepare("
    SELECT c.nombre
    FROM juegos_categorias jc
    JOIN categorias c ON c.id_categoria = jc.id_categoria
    WHERE jc.id_juego = ?
");
if (!$stmt) {
    die("Error en prepare (categorías): " . $conn->error);
}
$stmt->bind_param("i", $id_juego);
$stmt->execute();
$resCats = $stmt->get_result();
while ($row = $resCats->fetch_assoc()) {
    $categories[] = $row['nombre'];
}
$stmt->close();

// Obtener ranking (top 3) para este juego
$ranking = [];
$stmt = $conn->prepare("SELECT usuario, elo FROM ranking WHERE id_juego = ? ORDER BY elo DESC LIMIT 3");
if (!$stmt) {
    die("Error en prepare (ranking): " . $conn->error);
}
$stmt->bind_param("i", $id_juego);
$stmt->execute();
$resultRanking = $stmt->get_result();
while($row = $resultRanking->fetch_assoc()){
    $ranking[] = $row;
}
$stmt->close();

// Obtener torneos asociados a este juego
$torneos = [];
$stmt = $conn->prepare("SELECT nombre_torneo, fecha_inicio, fecha_fin, estado
                       FROM torneos
                       WHERE id_juego = ?
                       ORDER BY fecha_inicio DESC");
if (!$stmt) {
    die("Error en prepare (torneos): " . $conn->error);
}
$stmt->bind_param("i", $id_juego);
$stmt->execute();
$resultTorneos = $stmt->get_result();
while($row = $resultTorneos->fetch_assoc()){
    $torneos[] = $row;
}
$stmt->close();

// Verificar si el juego está en la lista de favoritos del usuario
$isFavorite = false;
if(isset($_SESSION['usuario'])){
    $usuario = $_SESSION['usuario'];
    $stmtFav = $conn->prepare("SELECT * FROM favoritos WHERE usuario = ? AND id_juego = ?");
    if($stmtFav){
        $stmtFav->bind_param("si", $usuario, $id_juego);
        $stmtFav->execute();
        $resFav = $stmtFav->get_result();
        if($resFav->num_rows > 0){
            $isFavorite = true;
        }
        $stmtFav->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title><?php echo htmlspecialchars($game['nombre']); ?> - Información del Juego</title>
  <link rel="stylesheet" href="css/pantalla_juego.css">
  <link rel="stylesheet" href="css/achievement.css"> <!-- Tus estilos de logros -->
  <style>
    /* Estilos básicos para el contenedor de favoritos */
    .favorite-container {
      margin: 15px 0;
      display: flex;
      align-items: center;
      cursor: pointer;
    }
    .favorite-container img {
      width: 30px;
      height: 30px;
      margin-right: 10px;
    }
    /* Botón Manual (opcional) */
    .manual-button {
      margin: 15px 0;
      padding: 10px 20px;
      cursor: pointer;
    }
  </style>
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
      
      <!-- Sección de Favoritos -->
      <?php if(isset($_SESSION['usuario'])): ?>
      <div class="favorite-container" onclick="toggleFavorite(<?php echo $game['id_juego']; ?>)">
        <img id="favoriteIcon" src="<?php echo $isFavorite ? 'images/star-filled.png' : 'images/star-outline.png'; ?>" alt="Favorito">
        <span id="favoriteText"><?php echo $isFavorite ? 'Quitar de Biblioteca' : 'Añadir a Biblioteca'; ?></span>
      </div>
      <?php endif; ?>
      
      <!-- Descripción -->
      <div class="game-description">
        <?php echo nl2br(htmlspecialchars($game['descripcion'])); ?>
      </div>
      
      <!-- Categorías -->
      <div class="categories">
        <strong>Categorías:</strong><br>
        <?php
          if (count($categories) > 0) {
              echo implode(", ", array_map('htmlspecialchars', $categories));
          } else {
              echo "Sin categorías asignadas.";
          }
        ?>
      </div>

      <!-- Capturas -->
      <div class="screenshots">
        <strong>Capturas:</strong><br>
        <?php if ($capturesBase64): ?>
          <img src="<?php echo $capturesBase64; ?>" alt="Captura del juego" style="width:300px;">
        <?php else: ?>
          <p>No hay capturas disponibles.</p>
        <?php endif; ?>
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
              echo htmlspecialchars($t['nombre_torneo']) . " ($fi - $ff) - Estado: " . htmlspecialchars($t['estado']) . "<br>";
            ?>
          <?php endforeach; ?>
        <?php else: ?>
          <p>No hay torneos para este juego.</p>
        <?php endif; ?>
      </div>
      
      <!-- Botón para ver el manual del juego -->
      <button class="manual-button" onclick="window.open('Manuales/Hunde La Flota.pdf', '_blank')">
        Ver Manual de Hunde La Flota
      </button>

      <!-- Botón Jugar Ahora: Llama a la función playGame -->
      <button class="play-button" onclick="playGame(<?php echo $game['id_juego']; ?>, '<?php echo htmlspecialchars($game['ruta_index']); ?>')">
        Jugar Ahora
      </button>
    </div>
  </main>
  
  <!-- Script con la lógica de achievements (notificaciones) -->
  <script>
  // Esta función muestra la notificación de logro con una animación de fade in/out
  function showAchievementNotification(achievement) {
      // Contenedor principal de notificaciones
      let container = document.getElementById('achievementContainer');
      if (!container) {
        container = document.createElement('div');
        container.id = 'achievementContainer';
        // Estilos básicos
        container.style.position = 'fixed';
        container.style.bottom = '20px';
        container.style.right = '20px';
        container.style.zIndex = '10000';
        document.body.appendChild(container);
      }
      
      // Crear el elemento de notificación
      const notif = document.createElement('div');
      notif.className = 'achievement-notification';
      // Estilos inline o puedes usar clases de CSS
      notif.style.display = 'flex';
      notif.style.alignItems = 'center';
      notif.style.background = 'rgba(0, 0, 0, 0.85)';
      notif.style.color = '#fff';
      notif.style.padding = '10px 15px';
      notif.style.borderRadius = '8px';
      notif.style.marginTop = '10px';
      notif.style.opacity = '0';
      notif.style.transition = 'opacity 1s ease';

      notif.innerHTML = `
        <img src="${achievement.imagen}" alt="${achievement.nombre}" style="width:50px;height:50px;margin-right:10px;border-radius:5px;object-fit:cover;">
        <div>
          <strong>${achievement.nombre}</strong>
          <p style="margin:0;font-size:0.8em;">${achievement.descripcion}</p>
        </div>
      `;
      container.appendChild(notif);
      
      // Fade in
      requestAnimationFrame(() => {
        notif.style.opacity = '1';
      });
      
      // Después de 5 segundos, fade out y eliminar
      setTimeout(() => {
        notif.style.opacity = '0';
        setTimeout(() => {
          if (notif.parentNode) {
            notif.parentNode.removeChild(notif);
          }
        }, 1000);
      }, 5000);
  }

    function playGame(id_juego, ruta_index) {
    const formData = new FormData();
    formData.append('juego_id', id_juego);

    // Paso 1: Actualizar ranking y logros
    fetch('php/update_ranking.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.status === 'success') {
        const handleRedirect = () => {
          // Paso 2: Crear o unirse a una partida
          fetch('php/gestor_partidas.php', {
            method: 'POST',
            body: formData
          })
          .then(response => response.json())
          .then(partidaData => {
            if (partidaData.status === 'success') {
              window.location.href = partidaData.redirect;
            } else {
              alert("Error al unirse o crear partida.");
            }
          })
          .catch(error => {
            console.error("Error al crear/unirse a partida:", error);
          });
        };

        // Si hay logros, mostrarlos y luego continuar
        if (data.achievements && data.achievements.length > 0) {
          data.achievements.forEach(achievement => {
            showAchievementNotification(achievement);
          });
          setTimeout(handleRedirect, 6000);
        } else {
          handleRedirect();
        }

      } else {
        alert("Error al actualizar ranking: " + data.message);
        window.location.href = ruta_index;
      }
    })
    .catch(error => {
      console.error('Error:', error);
      window.location.href = ruta_index;
    });
  }

  // Función para alternar el estado de favorito (añadir/quitar de biblioteca)
  function toggleFavorite(id_juego) {
    const formData = new FormData();
    formData.append('id_juego', id_juego);

    fetch('php/toggle_favorite.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if(data.status === 'added'){
         document.getElementById('favoriteIcon').src = 'images/star-filled.png';
         document.getElementById('favoriteText').innerText = 'Quitar de Biblioteca';
      } else if(data.status === 'removed'){
         document.getElementById('favoriteIcon').src = 'images/star-outline.png';
         document.getElementById('favoriteText').innerText = 'Añadir a Biblioteca';
      } else {
         alert("Error al actualizar favoritos: " + data.message);
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert("Error al actualizar favoritos.");
    });
  }
  </script>
</body>
</html>
