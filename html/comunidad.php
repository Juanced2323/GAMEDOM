<?php
session_start();
$activePage = basename($_SERVER['PHP_SELF'], ".php");
require_once "php/db_connect.php";
require_once "php/recommendations.php";

// CONSULTA PARA LLENAR EL DROP-DOWN DE JUEGOS
$gamesList = [];
$sqlGames = "SELECT id_juego, nombre FROM juegos ORDER BY nombre ASC";
$resultGames = $conn->query($sqlGames);
while ($rowGame = $resultGames->fetch_assoc()) {
    $gamesList[] = $rowGame;
}
$resultGames->close();

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario'])) {
    // Si no está logueado, cerramos la conexión y mostramos el acceso restringido.
    $conn->close();
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <title>Comunidad - GAMEDOM</title>
      <link rel="stylesheet" href="css/main.css">
      <link rel="stylesheet" href="css/chat.css">
    </head>
    <body>
      <header>
        <nav class="navbar">
          <div class="nav-left">
            <a href="index.php" class="nav-item <?php echo ($activePage === 'index') ? 'active' : ''; ?>">Inicio</a>
            <a href="biblioteca.php" class="nav-item <?php echo ($activePage === 'biblioteca') ? 'active' : ''; ?>">Biblioteca</a>
            <a href="comunidad.php" class="nav-item <?php echo ($activePage === 'comunidad') ? 'active' : ''; ?>">Comunidad</a>
            <a href="premios.php" class="nav-item <?php echo ($activePage === 'premios') ? 'active' : ''; ?>">Premios</a>
          </div>
          <div class="nav-right">
            <a href="login.html" class="nav-item">Iniciar Sesión</a>
          </div>
        </nav>
      </header>
      <main>
        <div class="restricted-access">
          <h2>Acceso Restringido</h2>
          <p>Esta sección está disponible solo para usuarios registrados.</p>
          <a href="login.html" class="btn-acceso">Iniciar Sesión</a>
        </div>
      </main>
      <!-- Contenedor del Chat -->
      <div id="chat-container" class="chat-container">
        <h2>Chat en Vivo</h2>
        <div id="chat-box" class="chat-box"></div>
        <div id="chat-input-container">
          <input type="text" id="chat-input" placeholder="Escribe un mensaje..." autofocus>
          <button>Enviar</button>
        </div> 
      </div>
      <!-- Scripts -->
      <script src="js/socket.io.js"></script>
      <script src="js/chat.js"></script>
      <script src="js/main.js"></script>
    </body>
    </html>
    <?php
    exit; // Detener la ejecución si no hay usuario logueado.
} else {
    // Si el usuario está logueado, procesamos los rankings.
    $gameRankings = [];    // Rankings por cada juego
    $rankingLess50 = [];   // Ranking global: usuarios con menos de 50 partidas

    // 1. Ranking por cada juego:
    $rankingGames = [];
    $sql = "SELECT DISTINCT id_juego FROM ranking";
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $rankingGames[] = $row['id_juego'];
        }
    }
    
    foreach ($rankingGames as $gameId) {
        // Top 5 jugadores para el juego según ELO (descendente)
        $stmt = $conn->prepare("SELECT r.usuario, r.elo 
                                 FROM ranking r 
                                 WHERE r.id_juego = ? 
                                 ORDER BY r.elo DESC LIMIT 5");
        $stmt->bind_param("i", $gameId);
        $stmt->execute();
        $resultRank = $stmt->get_result();
        $rankings = [];
        while ($r = $resultRank->fetch_assoc()) {
            $rankings[] = $r;
        }
        $stmt->close();
        
        // Obtener el nombre del juego
        $gameName = "";
        $stmt = $conn->prepare("SELECT nombre FROM juegos WHERE id_juego = ?");
        $stmt->bind_param("i", $gameId);
        $stmt->execute();
        $resGame = $stmt->get_result();
        if ($gameRow = $resGame->fetch_assoc()) {
            $gameName = $gameRow['nombre'];
        }
        $stmt->close();
        
        $gameRankings[] = [
            'gameId'   => $gameId,
            'gameName' => $gameName,
            'rankings' => $rankings
        ];
    }
    
    // 2. Ranking global para el juego seleccionado:
    // Se habilitará si se elige un juego
    $selectedGame = isset($_GET['game']) ? intval($_GET['game']) : 0;
    $generalRanking = [];  // Ranking general para el juego seleccionado
    $userRankPosition = null; // Posición del usuario actual en el ranking general
    $gameRankingForSelected = []; // Guardaremos el ranking de este juego para mostrar su nombre, etc.
    
    if ($selectedGame > 0) {
        // Ranking General para el juego seleccionado: obtenemos ELO y total de partidas
        $stmt = $conn->prepare("
            SELECT r.usuario, r.elo, COUNT(h.id_historial) AS total_matches
            FROM ranking r
            LEFT JOIN historial_juegos h ON r.usuario = h.usuario AND h.id_juego = ?
            WHERE r.id_juego = ?
            GROUP BY r.usuario, r.elo
            ORDER BY r.elo DESC
        ");
        $stmt->bind_param("ii", $selectedGame, $selectedGame);
        $stmt->execute();
        $resultGeneral = $stmt->get_result();
        while ($row = $resultGeneral->fetch_assoc()) {
            $generalRanking[] = $row;
        }
        $stmt->close();
        
        // Determinar la posición del usuario actual en el ranking general
        $currentUser = $_SESSION['usuario'];
        $pos = 1;
        foreach ($generalRanking as $r) {
            if ($r['usuario'] === $currentUser) {
                $userRankPosition = $pos;
                break;
            }
            $pos++;
        }
        
        // Ranking global: usuarios con menos de 50 partidas para el juego seleccionado
        $stmt2 = $conn->prepare("
            SELECT r.usuario, r.elo, COUNT(h.id_historial) AS total_matches
            FROM ranking r
            LEFT JOIN historial_juegos h ON r.usuario = h.usuario AND h.id_juego = ?
            WHERE r.id_juego = ?
            GROUP BY r.usuario, r.elo
            HAVING total_matches < 50
            ORDER BY r.elo DESC
        ");
        $stmt2->bind_param("ii", $selectedGame, $selectedGame);
        $stmt2->execute();
        $resultLess50 = $stmt2->get_result();
        while ($row = $resultLess50->fetch_assoc()) {
            $rankingLess50[] = $row;
        }
        $stmt2->close();
    }
    
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Comunidad - GAMEDOM</title>
  <!-- CSS principal -->
  <link rel="stylesheet" href="css/main.css">
  <!-- CSS del Chat -->
  <link rel="stylesheet" href="css/chat.css">
  <!-- CSS específico para Comunidad -->
  <link rel="stylesheet" href="css/community.css">
</head>
<body>
  <!-- MENÚ SUPERIOR -->
  <header>
    <nav class="navbar">
      <div class="nav-left">
        <a href="index.php" class="nav-item <?php echo ($activePage === 'index') ? 'active' : ''; ?>">Inicio</a>
        <a href="biblioteca.php" class="nav-item <?php echo ($activePage === 'biblioteca') ? 'active' : ''; ?>">Biblioteca</a>
        <a href="comunidad.php" class="nav-item <?php echo ($activePage === 'comunidad') ? 'active' : ''; ?>">Comunidad</a>
        <a href="premios.php" class="nav-item <?php echo ($activePage === 'premios') ? 'active' : ''; ?>">Premios</a>
      </div>
      <div class="nav-right">
        <?php if (isset($_SESSION['usuario'])): ?>
          <a href="perfil.php" class="nav-item <?php echo ($activePage === 'perfil') ? 'active' : ''; ?>">Perfil</a>
        <?php else: ?>
          <a href="login.html" class="nav-item">Iniciar Sesión</a>
        <?php endif; ?>
      </div>
    </nav>
  </header>

  <main>
    <section class="community-section">
      <h2>Comunidad</h2>
      <p>Aquí encontrarás rankings, foros, chats y contenido exclusivo para usuarios.</p>
      
      <!-- Selector de juego para ver rankings -->
      <div class="game-selector">
        <form method="GET" action="comunidad.php">
          <label for="game-select">Elige un juego:</label>
          <select name="game" id="game-select">
            <option value="0">-- Selecciona un juego --</option>
            <?php foreach ($gamesList as $game): ?>
              <option value="<?php echo $game['id_juego']; ?>" <?php echo (isset($_GET['game']) && intval($_GET['game']) === intval($game['id_juego'])) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($game['nombre']); ?>
              </option>
            <?php endforeach; ?>
          </select>
          <button type="submit">Ver Ranking</button>
        </form>
      </div>
      
      <?php if ($selectedGame > 0): ?>
      <!-- Información y rankings del juego seleccionado -->
      <div class="selected-game-info">
        <?php
          $selectedGameName = "";
          foreach($gamesList as $g) {
            if ($g['id_juego'] == $selectedGame) {
              $selectedGameName = $g['nombre'];
              break;
            }
          }
        ?>
        <h3>Ranking para: <?php echo htmlspecialchars($selectedGameName); ?></h3>
        <?php if ($userRankPosition !== null): ?>
          <p>Tu posición en el ranking general: <?php echo $userRankPosition; ?></p>
        <?php else: ?>
          <p>No tienes registro en este ranking.</p>
        <?php endif; ?>
      </div>
      
      <!-- Ranking General -->
      <div class="ranking-section">
        <h3>Ranking General</h3>
        <?php if (!empty($generalRanking)): ?>
          <table class="ranking-table">
            <thead>
              <tr>
                <th>Posición</th>
                <th>Usuario</th>
                <th>ELO</th>
                <th>Total Partidas</th>
              </tr>
            </thead>
            <tbody>
              <?php 
              $pos = 1;
              foreach ($generalRanking as $ranking) : ?>
                <tr <?php if($ranking['usuario'] === $_SESSION['usuario']) echo 'class="highlight"'; ?>>
                  <td><?php echo $pos; ?></td>
                  <td><?php echo htmlspecialchars($ranking['usuario']); ?></td>
                  <td><?php echo $ranking['elo']; ?></td>
                  <td><?php echo $ranking['total_matches']; ?></td>
                </tr>
              <?php 
                $pos++;
              endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <p>No hay datos para este juego.</p>
        <?php endif; ?>
      </div>
      
      <!-- Ranking global: Usuarios con menos de 50 partidas -->
      <div class="ranking-section less50-ranking">
        <h3>Ranking: Usuarios con Menos de 50 Partidas</h3>
        <?php if (!empty($rankingLess50)): ?>
          <table class="ranking-table">
            <thead>
              <tr>
                <th>Posición</th>
                <th>Usuario</th>
                <th>ELO</th>
                <th>Total Partidas</th>
              </tr>
            </thead>
            <tbody>
              <?php 
              $pos = 1;
              foreach ($rankingLess50 as $ranking) : ?>
                <tr>
                  <td><?php echo $pos; ?></td>
                  <td><?php echo htmlspecialchars($ranking['usuario']); ?></td>
                  <td><?php echo $ranking['elo']; ?></td>
                  <td><?php echo $ranking['total_matches']; ?></td>
                </tr>
              <?php 
                $pos++;
              endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <p>No hay usuarios con menos de 50 partidas para este juego.</p>
        <?php endif; ?>
      </div>
      
      <?php endif; ?>
    </section>
  </main>

  <!-- Contenedor del Chat -->
  <div id="chat-container" class="chat-container">
    <h2>Chat en Vivo</h2>
    <div id="chat-box" class="chat-box"></div>
    <div id="chat-input-container">
      <input type="text" id="chat-input" placeholder="Escribe un mensaje..." autofocus>
      <button>Enviar</button>
    </div> 
  </div>

  <!-- Scripts (se cargan desde archivos propios) -->
  <script src="js/socket.io.js"></script>
  <script src="js/chat.js"></script>
  <script src="js/main.js"></script>
</body>
</html>
