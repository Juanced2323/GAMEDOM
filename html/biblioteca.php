<?php
session_start();
$activePage = basename($_SERVER['PHP_SELF'], ".php");

if (!isset($_SESSION['usuario'])) {
    $favGames = [];
} else {
    require_once "php/db_connect.php";
    $usuario = $_SESSION['usuario'];

    // --- Biblioteca de juegos favoritos ---
    $stmt = $conn->prepare("
        SELECT j.id_juego, j.nombre
        FROM favoritos f
        JOIN juegos j ON j.id_juego = f.id_juego
        WHERE f.usuario = ?
        ORDER BY j.nombre ASC
    ");
    if (!$stmt) {
        die("Error en prepare (favGames): " . $conn->error);
    }
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $favGames = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // --- Lista para el selector de juegos ---
    $gamesList = [];
    foreach ($favGames as $g) {
        $gamesList[] = ['id_juego' => $g['id_juego'], 'nombre' => $g['nombre']];
    }

    // --- Top 5 de cada juego ---
    $gameRankings = [];
    $ids = array_column($favGames, 'id_juego');
    if (!empty($ids)) {
        $in = implode(',', array_map('intval', $ids));
        $res = $conn->query("SELECT DISTINCT id_juego FROM ranking WHERE id_juego IN ($in)");
        while ($r = $res->fetch_assoc()) {
            $gId = (int)$r['id_juego'];

            // Top 5 por ELO
            $s1 = $conn->prepare("
                SELECT usuario, elo
                FROM ranking
                WHERE id_juego = ?
                ORDER BY elo DESC
                LIMIT 5
            ");
            if (!$s1) {
                die("Error en prepare (top5): " . $conn->error);
            }
            $s1->bind_param("i", $gId);
            $s1->execute();
            $top5 = $s1->get_result()->fetch_all(MYSQLI_ASSOC);
            $s1->close();

            // Nombre del juego
            $s2 = $conn->prepare("
                SELECT nombre
                FROM juegos
                WHERE id_juego = ?
            ");
            if (!$s2) {
                die("Error en prepare (nombre): " . $conn->error);
            }
            $s2->bind_param("i", $gId);
            $s2->execute();
            $nombre = $s2->get_result()->fetch_assoc()['nombre'];
            $s2->close();

            $gameRankings[] = [
                'id_juego' => $gId,
                'nombre'   => $nombre,
                'players'  => $top5
            ];
        }
    }

    // --- Parámetros GET ---
    $selectedGame = isset($_GET['game'])   ? intval($_GET['game'])   : 0;
    $period       = $_GET['period']        ?? 'all';    // all|day|week|month|year

    $generalRanking = [];
    $userRankPos    = null;
    $rankingLess50  = [];

    if ($selectedGame > 0) {
        // Generar cláusula para periodo
        $periodClause = '';
        if ($period !== 'all') {
            switch ($period) {
                case 'day':   $int = '1 DAY';   break;
                case 'week':  $int = '7 DAY';   break;
                case 'month': $int = '1 MONTH'; break;
                case 'year':  $int = '1 YEAR';  break;
                default:      $int = null;
            }
            if ($int) {
                $periodClause = "AND h.fecha >= DATE_SUB(NOW(), INTERVAL $int)";
            }
        }

        // --- Ranking general filtrado por periodo ---
        $sql = "
          SELECT r.usuario,
                 r.elo,
                 COUNT(h.id_historial) AS total_matches
          FROM ranking r
          LEFT JOIN historial_juegos h
            ON r.usuario = h.usuario
           AND h.id_juego = ?
           $periodClause
          WHERE r.id_juego = ?
          GROUP BY r.usuario, r.elo
          ORDER BY r.elo DESC
          LIMIT 100
        ";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("Error en prepare (generalRanking): " . $conn->error);
        }
        $stmt->bind_param("ii", $selectedGame, $selectedGame);
        $stmt->execute();
        $generalRanking = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Posición del usuario
        foreach ($generalRanking as $i => $row) {
            if ($row['usuario'] === $usuario) {
                $userRankPos = $i + 1;
                break;
            }
        }

        // --- Usuarios con menos de 50 partidas ---
        $s3 = $conn->prepare("
          SELECT r.usuario,
                 r.elo,
                 COUNT(h.id_historial) AS total_matches
          FROM ranking r
          LEFT JOIN historial_juegos h
            ON r.usuario = h.usuario
           AND h.id_juego = ?
          WHERE r.id_juego = ?
          GROUP BY r.usuario, r.elo
          HAVING total_matches < 50
          ORDER BY r.elo DESC
        ");
        if (!$s3) {
            die("Error en prepare (less50): " . $conn->error);
        }
        $s3->bind_param("ii", $selectedGame, $selectedGame);
        $s3->execute();
        $rankingLess50 = $s3->get_result()->fetch_all(MYSQLI_ASSOC);
        $s3->close();
    }

    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Biblioteca – GAMEDOM</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="css/Index.css">
  <link rel="stylesheet" href="css/library.css">
  <link rel="stylesheet" href="css/community.css">
</head>
<body>
  <!-- MENÚ SUPERIOR -->
  <div class="menu-superior">
    <div class="nav-left">
      <img src="images/imagenes/Logo.png" alt="Logo" class="logo">
    </div>
    <div class="nav-right">
      <a href="index.php"      class="nav-item <?= $activePage==='index'      ?'active':'' ?>">Inicio</a>
      <a href="biblioteca.php" class="nav-item <?= $activePage==='biblioteca' ?'active':'' ?>">Biblioteca</a>
      <a href="comunidad.php"  class="nav-item <?= $activePage==='comunidad'  ?'active':'' ?>">Comunidad</a>
      <a href="premios.php"    class="nav-item <?= $activePage==='premios'    ?'active':'' ?>">Premios</a>
      <a href="perfil.php"     class="nav-item <?= $activePage==='perfil'     ?'active':'' ?>">Perfil</a>
    </div>
  </div>

  <main>
    <?php if (empty($favGames)): ?>
      <div class="restricted-access">
        <h2>Acceso Restringido</h2>
        <p>Inicia sesión para acceder a tu biblioteca y rankings.</p>
        <a href="login.html" class="btn-acceso">Iniciar Sesión</a>
      </div>
    <?php else: ?>

      <!-- Biblioteca de Juegos -->
      <section class="game-catalog">
        <h2>Biblioteca de Juegos</h2>
        <div class="game-list">
          <?php foreach ($favGames as $g): ?>
            <div class="game-card">
              <a href="pantalla_juego.php?id=<?= $g['id_juego'] ?>">
                <img src="images/default-game.png" alt="<?= htmlspecialchars($g['nombre']) ?>">
                <h4><?= htmlspecialchars($g['nombre']) ?></h4>
              </a>
            </div>
          <?php endforeach; ?>
        </div>
      </section>

      <!-- Sistema de Rankings -->
      <section class="ranking-section">
        <h2>Sistema de Rankings</h2>

        <!-- Top 5 de cada juego -->
        <div class="top-games-ranking">
          <h3>Top 5 de cada juego</h3>
          <?php foreach ($gameRankings as $gr): ?>
            <div class="game-ranking-item">
              <h4><?= htmlspecialchars($gr['nombre']) ?></h4>
              <?php if ($gr['players']): ?>
                <table class="ranking-table">
                  <thead>
                    <tr><th>Pos.</th><th>Usuario</th><th>ELO</th></tr>
                  </thead>
                  <tbody>
                    <?php $p = 1; foreach ($gr['players'] as $pl): ?>
                      <tr>
                        <td><?= $p ?></td>
                        <td><?= htmlspecialchars($pl['usuario']) ?></td>
                        <td><?= $pl['elo'] ?></td>
                      </tr>
                    <?php $p++; endforeach; ?>
                  </tbody>
                </table>
              <?php else: ?>
                <p class="no-posts">Sin datos en este ranking.</p>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Selector de juego -->
        <div class="game-selector">
          <form method="GET" action="biblioteca.php">
            <label for="game-select">Juego:</label>
            <select name="game" id="game-select" required>
              <option value="">-- Selecciona --</option>
              <?php foreach ($gamesList as $g): ?>
                <option value="<?= $g['id_juego'] ?>"
                  <?= $selectedGame === $g['id_juego'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($g['nombre']) ?>
                </option>
              <?php endforeach; ?>
            </select>
            <button type="submit" class="btn">Ver Ranking</button>
          </form>
        </div>

        <?php if ($selectedGame > 0): ?>
          <!-- Selector de periodo -->
          <div class="period-selector">
            <form method="GET" action="biblioteca.php">
              <input type="hidden" name="game" value="<?= $selectedGame ?>">
              <label for="period-select">Periodo:</label>
              <select name="period" id="period-select">
                <option value="all"   <?= $period==='all'   ?'selected':'' ?>>Todos</option>
                <option value="day"   <?= $period==='day'   ?'selected':'' ?>>Último día</option>
                <option value="week"  <?= $period==='week'  ?'selected':'' ?>>Última semana</option>
                <option value="month" <?= $period==='month'?'selected':'' ?>>Último mes</option>
                <option value="year"  <?= $period==='year'  ?'selected':'' ?>>Último año</option>
              </select>
              <button type="submit" class="btn">Filtrar Periodo</button>
            </form>
          </div>

          <!-- Tu posición -->
          <p>
            <?= $userRankPos
                ? "Tu posición en este ranking: <strong>{$userRankPos}</strong>"
                : "No estás en el top 100 de este ranking."
            ?>
          </p>

          <!-- Tabla de ranking detallado -->
          <div class="detailed-ranking">
            <table class="ranking-table">
              <thead>
                <tr><th>Pos.</th><th>Usuario</th><th>ELO</th><th>Partidas</th></tr>
              </thead>
              <tbody>
                <?php if (empty($generalRanking)): ?>
                  <tr><td colspan="4">No hay datos para estos filtros.</td></tr>
                <?php else: foreach ($generalRanking as $i => $r): ?>
                  <tr <?= $r['usuario'] === $usuario ? 'class="highlight"' : '' ?>>
                    <td><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars($r['usuario']) ?></td>
                    <td><?= $r['elo'] ?></td>
                    <td><?= $r['total_matches'] ?></td>
                  </tr>
                <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>

          <!-- Usuarios con menos de 50 partidas -->
          <div class="less50-ranking">
            <h3>Usuarios con Menos de 50 Partidas</h3>
            <table class="ranking-table">
              <thead>
                <tr><th>Pos.</th><th>Usuario</th><th>ELO</th><th>Partidas</th></tr>
              </thead>
              <tbody>
                <?php if (empty($rankingLess50)): ?>
                  <tr><td colspan="4">No hay usuarios con menos de 50 partidas.</td></tr>
                <?php else: foreach ($rankingLess50 as $i => $l): ?>
                  <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars($l['usuario']) ?></td>
                    <td><?= $l['elo'] ?></td>
                    <td><?= $l['total_matches'] ?></td>
                  </tr>
                <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>

      </section>
    <?php endif; ?>
  </main>

  <!-- FOOTER -->
  <footer>
    <p>&copy; 2025 GAMEDOM. Todos los derechos reservados.</p>
  </footer>
</body>
</html>
