<?php
session_start();
$activePage = basename($_SERVER['PHP_SELF'], ".php");

if (!isset($_SESSION['usuario'])) {
    $favGames = [];
} else {
    require_once "php/db_connect.php";
    $usuario = $_SESSION['usuario'];

    // --- Biblioteca de juegos favoritos ---
    $stmt = $conn->prepare("SELECT j.* FROM favoritos f JOIN juegos j ON j.id_juego = f.id_juego WHERE f.usuario = ? ORDER BY j.nombre ASC");
    if (!$stmt) {
        die("Error en prepare: " . $conn->error);
    }
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $favGames = [];
    while ($row = $result->fetch_assoc()) {
        $favGames[] = $row;
    }
    $stmt->close();

    // --- Sistema de Rankings ---
    // Utilizar la biblioteca del usuario para construir la lista de juegos del selector
    $gamesList = [];
    if (!empty($favGames)) {
        foreach ($favGames as $game) {
            $gamesList[] = ['id_juego' => $game['id_juego'], 'nombre' => $game['nombre']];
        }
    }

    // Filtrar los rankings solo para los juegos que el usuario tiene en su biblioteca
    $userFavIDs = [];
    if (!empty($favGames)) {
        $userFavIDs = array_column($favGames, 'id_juego');
    }
    $rankingGames = [];
    if (!empty($userFavIDs)) {
        $ids_string = implode(',', array_map('intval', $userFavIDs));
        $sqlRankGames = "SELECT DISTINCT id_juego FROM ranking WHERE id_juego IN ($ids_string)";
        $resultRank = $conn->query($sqlRankGames);
        if ($resultRank) {
            while ($row = $resultRank->fetch_assoc()) {
                $rankingGames[] = $row['id_juego'];
            }
        }
    }
    $gameRankings = [];
    foreach ($rankingGames as $gId) {
        // Top 5 (ordenado de mayor a menor ELO)
        $stmtRank = $conn->prepare("SELECT usuario, elo FROM ranking WHERE id_juego = ? ORDER BY elo DESC LIMIT 5");
        $stmtRank->bind_param("i", $gId);
        $stmtRank->execute();
        $resRank = $stmtRank->get_result();
        $topPlayers = [];
        while ($r = $resRank->fetch_assoc()) {
            $topPlayers[] = $r;
        }
        $stmtRank->close();
        
        // Obtener el nombre del juego
        $nombreJuego = "";
        $stmtName = $conn->prepare("SELECT nombre FROM juegos WHERE id_juego = ?");
        $stmtName->bind_param("i", $gId);
        $stmtName->execute();
        $resName = $stmtName->get_result();
        if ($n = $resName->fetch_assoc()) {
            $nombreJuego = $n['nombre'];
        }
        $stmtName->close();
        
        $gameRankings[] = [
            'id_juego' => $gId,
            'nombre'   => $nombreJuego,
            'players'  => $topPlayers
        ];
    }

    // Obtener juego seleccionado (si se indica en la URL)
    $selectedGame = isset($_GET['game']) ? intval($_GET['game']) : 0;
    $generalRanking = [];
    $userRankPosition = null;
    $rankingLess50 = [];

    // Si se ha seleccionado un juego, se muestra el ranking detallado
    if ($selectedGame > 0) {
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

        // Calcular la posición del usuario actual en el ranking general
        $currentUser = $_SESSION['usuario'];
        $pos = 1;
        foreach ($generalRanking as $rankRow) {
            if ($rankRow['usuario'] === $currentUser) {
                $userRankPosition = $pos;
                break;
            }
            $pos++;
        }

        // Ranking de usuarios con menos de 50 partidas
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
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Biblioteca - GAMEDOM</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <!-- CSS principal, de la biblioteca y rankings (chat eliminado) -->
  <link rel="stylesheet" href="css/Index.css">
  <link rel="stylesheet" href="css/library.css">
  <!-- Se incluye también CSS para rankings (se puede reutilizar community.css) -->
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
      <a href="premios.php" class="nav-item <?php echo ($activePage === 'premios') ? 'active' : ''; ?>">Premios</a>
      <a href="perfil.php" class="nav-item <?php echo ($activePage === 'perfil') ? 'active' : ''; ?>">Perfil</a>
    </div>
  </div>

  <main>
    <?php if (!isset($_SESSION['usuario'])): ?>
      <div class="restricted-access">
        <h2>Acceso Restringido</h2>
        <p>Esta sección está disponible solo para usuarios registrados.</p>
        <a href="login.html" class="btn-acceso">Iniciar Sesión</a>
      </div>
    <?php else: ?>
      <!-- Sección Biblioteca de Juegos -->
      <section class="game-catalog">
        <h2>Biblioteca de Juegos</h2>
        <div class="game-list">
          <?php if (!empty($favGames)): ?>
            <?php foreach ($favGames as $game): ?>
              <div class="game-card">
                <a href="pantalla_juego.php?id=<?php echo $game['id_juego']; ?>">
                  <?php
                  // Mostrar el icono si existe; sino, mostrar imagen por defecto.
                  if (!empty($game['icono'])) {
                      $iconoBase64 = "data:image/jpeg;base64," . base64_encode($game['icono']);
                      echo '<img src="' . $iconoBase64 . '" alt="' . htmlspecialchars($game['nombre']) . '">';
                  } else {
                      echo '<img src="images/default-game.png" alt="Juego sin icono">';
                  }
                  ?>
                  <h4><?php echo htmlspecialchars($game['nombre']); ?></h4>
                </a>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p class="no-games">No tienes juegos en tu biblioteca.</p>
          <?php endif; ?>
        </div>
      </section>

      <!-- Sección de Rankings (filtrada por juegos en la biblioteca) -->
      <section class="ranking-section">
        <h2>Sistema de Rankings</h2>

        <!-- Top 5 de cada juego -->
        <div class="top-games-ranking">
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
                              <td>" . htmlspecialchars($p['usuario']) . "</td>
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
          <form method="GET" action="biblioteca.php">
            <label for="game-select">Elige un juego:</label>
            <select name="game" id="game-select">
              <option value="0">-- Selecciona un juego --</option>
              <?php foreach ($gamesList as $gameItem): ?>
                <option value="<?php echo $gameItem['id_juego']; ?>" <?php echo ($selectedGame === intval($gameItem['id_juego'])) ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($gameItem['nombre']); ?>
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

        <!-- Ranking General detallado -->
        <div class="detailed-ranking">
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

        <!-- Ranking: Usuarios con menos de 50 partidas -->
        <div class="detailed-ranking less50-ranking">
          <h3>Usuarios con Menos de 50 Partidas</h3>
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
    <?php endif; ?>
  </main>

  <footer class="footer">
    <p>
      © 2025 CodeCrafters. Todos los derechos reservados. Todas las marcas registradas pertenecen a sus respectivos dueños en EE. UU. y otros países.<br>
      Todos los precios incluyen IVA (donde sea aplicable).
    </p>
    <nav>
      <a href="Política de privacidad.html">Política de Privacidad</a> |
      <a href="Información legal.html">Información legal</a> |
      <a href="Cookies.html">Cookies</a> |
      <a href="A cerca de.html">A cerca de CodeCrafters</a>
    </nav>
  </footer>
  
  <!-- Se ha eliminado la sección de Chat en Vivo -->
  
  <!-- Scripts -->
  <script src="js/socket.io.js"></script>
  <script src="js/main.js"></script>
</body>
</html>
