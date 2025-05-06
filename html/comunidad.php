<?php
session_start();
$activePage = basename($_SERVER['PHP_SELF'], ".php");

require_once "php/db_connect.php";
require_once "php/recommendations.php";

// ========== Obtener la lista de juegos para el selector ==========
$gamesList = [];
$sqlGames = "SELECT id_juego, nombre FROM juegos ORDER BY nombre ASC";
$resultGames = $conn->query($sqlGames);
while ($rowGame = $resultGames->fetch_assoc()) {
    $gamesList[] = $rowGame;
}
$resultGames->close();

// ========== PAGINA PARA USUARIOS NO LOGUEADOS ==========
if (!isset($_SESSION['usuario'])) {
    $conn->close();
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
      <meta charset="UTF-8">
      <title>Comunidad - GAMEDOM</title>
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <!-- CSS principal (igual que index) -->
      <link rel="stylesheet" href="css/Index.css">
    </head>
    <body>
      <!-- MENÚ SUPERIOR (idéntico a index.php) -->
      <div class="menu-superior">
        <div class="nav-left">
          <img src="images/imagenes/Logo.png" alt="Logo Gamedom" class="logo">
        </div>
        <div class="nav-right">
          <a href="index.php" class="nav-item <?php echo ($activePage === 'index') ? 'active' : ''; ?>">Inicio</a>
          <a href="biblioteca.php" class="nav-item <?php echo ($activePage === 'biblioteca') ? 'active' : ''; ?>">Biblioteca</a>
          <a href="comunidad.php" class="nav-item <?php echo ($activePage === 'comunidad') ? 'active' : ''; ?>">Comunidad</a>
          <a href="torneos.php" class="nav-item <?php echo ($activePage === 'torneos') ? 'active' : ''; ?>">Torneos</a>
          <a href="login.html" class="nav-item">Iniciar Sesión</a>
        </div>
      </div>

      <main>
        <div class="restricted-access">
          <h2>Acceso Restringido</h2>
          <p>Esta sección está disponible solo para usuarios registrados.</p>
          <a href="login.html" class="btn-acceso">Iniciar Sesión</a>
        </div>
      </main>

      <!-- FOOTER (igual que en index.php) -->
      <footer class="footer">
        <p>
          © 2025 CodeCrafters. Todos los derechos reservados.  
          Todas las marcas registradas pertenecen a sus respectivos dueños en EE. UU. y otros países.<br>
          Todos los precios incluyen IVA (donde sea aplicable).
        </p>
        <nav>
          <a href="Política de privacidad.html">Política de Privacidad</a> |
          <a href="Información legal.html">Información legal</a> |
          <a href="Cookies.html">Cookies</a> |
          <a href="A cerca de.html">A cerca de CodeCrafters</a>
        </nav>
      </footer>
    </body>
    </html>
    <?php
    exit;
}

// ========== Lógica de Rankings si el usuario está logueado ==========

$selectedGame = isset($_GET['game']) ? intval($_GET['game']) : 0;
$generalRanking = [];
$userRankPosition = null;
$rankingLess50 = [];

// Ranking de cada juego (top 5)
$rankingGames = [];
$sqlRankGames = "SELECT DISTINCT id_juego FROM ranking";
$result = $conn->query($sqlRankGames);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $rankingGames[] = $row['id_juego'];
    }
}
$gameRankings = [];
foreach ($rankingGames as $gId) {
    // top 5 ELO descendente
    $stmt = $conn->prepare("SELECT usuario, elo FROM ranking WHERE id_juego = ? ORDER BY elo DESC LIMIT 5");
    $stmt->bind_param("i", $gId);
    $stmt->execute();
    $resRank = $stmt->get_result();
    $topPlayers = [];
    while ($r = $resRank->fetch_assoc()) {
        $topPlayers[] = $r;
    }
    $stmt->close();
    
    // Obtener nombre del juego
    $nombreJuego = "";
    $stmt2 = $conn->prepare("SELECT nombre FROM juegos WHERE id_juego = ?");
    $stmt2->bind_param("i", $gId);
    $stmt2->execute();
    $resNombre = $stmt2->get_result();
    if ($n = $resNombre->fetch_assoc()) {
        $nombreJuego = $n['nombre'];
    }
    $stmt2->close();
    
    $gameRankings[] = [
        'id_juego' => $gId,
        'nombre'   => $nombreJuego,
        'players'  => $topPlayers
    ];
}

// Si se selecciona un juego, ranking detallado
if ($selectedGame > 0) {
    // Ranking general (ELO, total partidas)
    $stmtG = $conn->prepare("
        SELECT r.usuario, r.elo, COUNT(h.id_historial) AS total_matches
        FROM ranking r
        LEFT JOIN historial_juegos h ON r.usuario = h.usuario AND h.id_juego = ?
        WHERE r.id_juego = ?
        GROUP BY r.usuario, r.elo
        ORDER BY r.elo DESC
    ");
    $stmtG->bind_param("ii", $selectedGame, $selectedGame);
    $stmtG->execute();
    $resG = $stmtG->get_result();
    while ($row = $resG->fetch_assoc()) {
        $generalRanking[] = $row;
    }
    $stmtG->close();
    
    // Posición del usuario actual
    $currentUser = $_SESSION['usuario'];
    $pos = 1;
    foreach ($generalRanking as $rankRow) {
        if ($rankRow['usuario'] === $currentUser) {
            $userRankPosition = $pos;
            break;
        }
        $pos++;
    }
    
    // Ranking con menos de 50 partidas
    $stmtL = $conn->prepare("
        SELECT r.usuario, r.elo, COUNT(h.id_historial) AS total_matches
        FROM ranking r
        LEFT JOIN historial_juegos h ON r.usuario = h.usuario AND h.id_juego = ?
        WHERE r.id_juego = ?
        GROUP BY r.usuario, r.elo
        HAVING total_matches < 50
        ORDER BY r.elo DESC
    ");
    $stmtL->bind_param("ii", $selectedGame, $selectedGame);
    $stmtL->execute();
    $resL = $stmtL->get_result();
    while ($row = $resL->fetch_assoc()) {
        $rankingLess50[] = $row;
    }
    $stmtL->close();
}

$conn->close();
?>

<!-- PAGINA PARA USUARIOS LOGUEADOS -->
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Comunidad - GAMEDOM</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <!-- CSS principal (mismo que index) -->
  <link rel="stylesheet" href="css/Index.css">
  <!-- CSS para Comunidad (rankings, etc.) -->
  <link rel="stylesheet" href="css/community.css">
</head>
<body>

  <!-- MENÚ SUPERIOR -->
  <div class="menu-superior">
    <div class="nav-left">
      <img src="images/imagenes/Logo.png" alt="Logo Gamedom" class="logo">
    </div>
    <div class="nav-right">
      <a href="index.php" class="nav-item <?php echo ($activePage === 'index') ? 'active' : ''; ?>">Inicio</a>
      <a href="biblioteca.php" class="nav-item <?php echo ($activePage === 'biblioteca') ? 'active' : ''; ?>">Biblioteca</a>
      <a href="comunidad.php" class="nav-item <?php echo ($activePage === 'comunidad') ? 'active' : ''; ?>">Comunidad</a>
      <a href="torneos.php" class="nav-item <?php echo ($activePage === 'torneos') ? 'active' : ''; ?>">Torneos</a>
      <?php if (isset($_SESSION['usuario'])): ?>
        <a href="perfil.php" class="nav-item <?php echo ($activePage === 'perfil') ? 'active' : ''; ?>">Perfil</a>
      <?php else: ?>
        <a href="login.html" class="nav-item">Iniciar Sesión</a>
      <?php endif; ?>
    </div>
  </div>

  <main>
    <section class="community-section">
      <h2>Comunidad</h2>
      <p>Aquí encontrarás rankings, foros, chats y contenido exclusivo para usuarios.</p>

      <!-- Ranking por cada juego (Top 5) -->
      <div class="ranking-section">
        <h3>Top 5 de cada juego</h3>
        <?php if (!empty($gameRankings)): ?>
          <?php foreach ($gameRankings as $gRank): ?>
            <div class="game-ranking-item" style="margin-bottom: 30px;">
              <h4><?php echo htmlspecialchars($gRank['nombre']); ?></h4>
              <?php if (!empty($gRank['players'])): ?>
                <table class="ranking-table">
                  <thead>
                    <tr>
                      <th>Pos.</th>
                      <th>Usuario</th>
                      <th>ELO</th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php
                  $pos = 1;
                  foreach ($gRank['players'] as $p) {
                    echo "<tr>
                            <td>{$pos}</td>
                            <td>".htmlspecialchars($p['usuario'])."</td>
                            <td>{$p['elo']}</td>
                          </tr>";
                    $pos++;
                  }
                  ?>
                  </tbody>
                </table>
              <?php else: ?>
                <p>No hay jugadores en este ranking.</p>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p>No hay juegos en la tabla de ranking.</p>
        <?php endif; ?>
      </div>

      <!-- Selector de juego para Ranking detallado -->
      <div class="game-selector">
        <form method="GET" action="comunidad.php">
          <label for="game-select">Elige un juego:</label>
          <select name="game" id="game-select">
            <option value="0">-- Selecciona un juego --</option>
            <?php foreach ($gamesList as $game): ?>
              <option value="<?php echo $game['id_juego']; ?>" <?php echo ($selectedGame === intval($game['id_juego'])) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($game['nombre']); ?>
              </option>
            <?php endforeach; ?>
          </select>
          <button type="submit">Ver Ranking</button>
        </form>
      </div>

      <?php if ($selectedGame > 0): ?>
      <!-- Información del juego seleccionado -->
      <div class="selected-game-info">
        <?php
          $selectedName = "";
          foreach($gamesList as $gm) {
            if ($gm['id_juego'] == $selectedGame) {
              $selectedName = $gm['nombre'];
              break;
            }
          }
        ?>
        <h3>Ranking para: <?php echo htmlspecialchars($selectedName); ?></h3>
        <?php if ($userRankPosition !== null): ?>
          <p>Tu posición en el ranking general: <strong><?php echo $userRankPosition; ?></strong></p>
        <?php else: ?>
          <p>No tienes registro en este ranking.</p>
        <?php endif; ?>
      </div>

      <!-- Ranking General (con ELO y partidas totales) -->
      <div class="ranking-section">
        <h3>Ranking General</h3>
        <?php if (!empty($generalRanking)): ?>
          <table class="ranking-table">
            <thead>
              <tr>
                <th>Pos.</th>
                <th>Usuario</th>
                <th>ELO</th>
                <th>Total Partidas</th>
              </tr>
            </thead>
            <tbody>
              <?php 
              $pos = 1;
              foreach ($generalRanking as $r) : ?>
                <tr <?php if($r['usuario'] === $_SESSION['usuario']) echo 'class="highlight"'; ?>>
                  <td><?php echo $pos; ?></td>
                  <td><?php echo htmlspecialchars($r['usuario']); ?></td>
                  <td><?php echo $r['elo']; ?></td>
                  <td><?php echo $r['total_matches']; ?></td>
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

      <!-- Ranking: Menos de 50 partidas -->
      <div class="ranking-section less50-ranking">
        <h3>Ranking: Usuarios con Menos de 50 Partidas</h3>
        <?php if (!empty($rankingLess50)): ?>
          <table class="ranking-table">
            <thead>
              <tr>
                <th>Pos.</th>
                <th>Usuario</th>
                <th>ELO</th>
                <th>Total Partidas</th>
              </tr>
            </thead>
            <tbody>
              <?php 
              $pos = 1;
              foreach ($rankingLess50 as $rk) : ?>
                <tr>
                  <td><?php echo $pos; ?></td>
                  <td><?php echo htmlspecialchars($rk['usuario']); ?></td>
                  <td><?php echo $rk['elo']; ?></td>
                  <td><?php echo $rk['total_matches']; ?></td>
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

  <!-- Boton flotante del chat -->
  <div id="chat-toggle-btn" class="chat-toggle-btn" title="Abrir chat">
  💬
  </div>

  <div id="chat-container" class="chat-container hidden">
    <!-- Cabecera del chat con botón del menú -->
    <div class="chat-header">
      <h2>Chat en Vivo</h2>
      <button class="openbtn" onclick="toggleSidebar()">☰</button>
    </div>

    <!-- Sidebar de opciones dentro del chat -->
    <div id="sidebar" class="chat-sidebar hidden">
      <a href="#" onclick="mostrarFormularioAmigo()">➕ Añadir amigo</a>
      <a href="#">📩 Chat privado</a>
      <a href="#">🎮🔒 Partida privada</a>
    </div>

    <!-- Añadir amigos -->
    <div id="form-solicitud-amigo" class="chat-popup hidden">
      <h3>Enviar solicitud de amistad</h3>
      <input type="email" id="correo-amigo" placeholder="Correo del usuario" required>
      <button onclick="enviarSolicitudAmistad()">Añadir</button>
      <button onclick="cerrarFormularioAmigo()">Cancelar</button>
    </div>

    <!-- Mensajes -->
    <div id="chat-box" class="chat-box"></div>

    <!-- Entrada de mensaje -->
    <div id="chat-input-container">
      <input type="text" id="chat-input" placeholder="Escribe un mensaje..." autofocus>
      <button id="chat-send-btn">Enviar</button>
    </div>
  </div>

  <!-- script del chat --> 
  <script src="https://cdn.socket.io/4.5.4/socket.io.min.js"></script>
  <script src="js/chat.js"></script>

  <!-- Boton flotante del chat -->
  <div id="chat-toggle-btn" class="chat-toggle-btn" title="Abrir chat">
  💬
  </div>

  <div id="chat-container" class="chat-container hidden">
    <!-- Cabecera del chat con botón del menú -->
    <div class="chat-header">
      <h2>Chat en Vivo</h2>
      <button class="openbtn" onclick="toggleSidebar()">☰</button>
    </div>

    <!-- Sidebar de opciones dentro del chat -->
    <div id="sidebar" class="chat-sidebar hidden">
      <a href="#" onclick="mostrarFormularioAmigo()">➕ Añadir amigo</a>
      <a href="#">📩 Chat privado</a>
      <a href="#">🎮🔒 Partida privada</a>
    </div>

    <!-- Añadir amigos -->
    <div id="form-solicitud-amigo" class="chat-popup hidden">
      <h3>Enviar solicitud de amistad</h3>
      <input type="email" id="correo-amigo" placeholder="Correo del usuario" required>
      <button onclick="enviarSolicitudAmistad()">Añadir</button>
      <button onclick="cerrarFormularioAmigo()">Cancelar</button>
    </div>

    <!-- Mensajes -->
    <div id="chat-box" class="chat-box"></div>

    <!-- Entrada de mensaje -->
    <div id="chat-input-container">
      <input type="text" id="chat-input" placeholder="Escribe un mensaje..." autofocus>
      <button id="chat-send-btn">Enviar</button>
    </div>
  </div>

  <!-- FOOTER igual que index -->
  <footer class="footer">
    <p>
      © 2025 CodeCrafters. Todos los derechos reservados.  
      Todas las marcas registradas pertenecen a sus respectivos dueños en EE. UU. y otros países.<br>
      Todos los precios incluyen IVA (donde sea aplicable).
    </p>
    <nav>
      <a href="Política de privacidad.html">Política de Privacidad</a> |
      <a href="Información legal.html">Información legal</a> |
      <a href="Cookies.html">Cookies</a> |
      <a href="A cerca de.html">A cerca de CodeCrafters</a>
    </nav>
  </footer>

  <!-- Scripts -->
  <script>
    document.body.dataset.userCorreo = "<?php echo htmlspecialchars($_SESSION['correo'], ENT_QUOTES); ?>";
    document.body.dataset.userNombre = "<?php echo htmlspecialchars($_SESSION['usuario'], ENT_QUOTES); ?>";
  </script>
  <script src="https://cdn.socket.io/4.5.4/socket.io.min.js"></script>
  <script src="js/chat.js"></script>
  
</body>
</html>
