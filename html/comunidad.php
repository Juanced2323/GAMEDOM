<?php
session_start();
$activePage = basename($_SERVER['PHP_SELF'], ".php");
require_once "php/db_connect.php";
require_once "php/recommendations.php";

// 1) Validar sesión
if (!isset($_SESSION['usuario'])) {
    header("Location: login.html");
    exit;
}
$username = $_SESSION['usuario'];

// 2) Tus juegos favoritos
$favorites = [];
$stmtFav = $conn->prepare("SELECT id_juego FROM favoritos WHERE usuario = ?");
$stmtFav->bind_param("s", $username);
$stmtFav->execute();
$resFav = $stmtFav->get_result();
while ($r = $resFav->fetch_assoc()) {
    $favorites[] = (int)$r['id_juego'];
}
$stmtFav->close();

// 3) Recomendaciones content-based
$recomGames = getContentBasedRecommendations($username, $conn, 5);
$recomIds   = array_map(fn($g) => (int)$g['id_juego'], $recomGames);

// 4) Cargar todos los juegos
$allGames = [];
if ($resG = $conn->query("SELECT id_juego, nombre FROM juegos ORDER BY nombre ASC")) {
    $allGames = $resG->fetch_all(MYSQLI_ASSOC);
    $resG->close();
}

// 5) Leer filtros de GET
$filterGameIdParam   = $_GET['filter_game_id']   ?? '';
$filterGameNameParam = trim($_GET['filter_game_search'] ?? '');
$timeFilter          = $_GET['time_filter'] ?? 'newest';

// Determinar filtro activo
$filterGame = null;
if ($filterGameIdParam !== '') {
    $filterGame = (int)$filterGameIdParam;
} elseif ($filterGameNameParam !== '') {
    foreach ($allGames as $g) {
        if (mb_strtolower($g['nombre']) === mb_strtolower($filterGameNameParam)) {
            $filterGame = (int)$g['id_juego'];
            break;
        }
    }
}

// 6) Construir cláusulas WHERE
$where = [];
if ($filterGame !== null) {
    $where[] = $filterGame === 0
        ? "t.titulo = 'General'"
        : "(t.titulo = 'General' OR t.id_juego = {$filterGame})";
}
switch ($timeFilter) {
    case 'last_hour':
        $where[] = "p.fecha_creado >= DATE_SUB(NOW(), INTERVAL 1 HOUR)";
        break;
    case 'last_day':
        $where[] = "p.fecha_creado >= DATE_SUB(NOW(), INTERVAL 1 DAY)";
        break;
    case 'last_week':
        $where[] = "p.fecha_creado >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        break;
    case 'last_month':
        $where[] = "p.fecha_creado >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
        break;
}
$whereSQL = $where ? 'WHERE '.implode(' AND ', $where) : '';

// 7) ORDER BY según tiempo
$orderDir = $timeFilter === 'oldest' ? 'ASC' : 'DESC';

// 8) Recuperar posts
$sql = "
  SELECT 
    p.id_post, p.usuario, p.contenido, p.imagen, p.fecha_creado,
    t.id_juego, t.titulo AS topic, j.nombre AS juego
  FROM forum_posts p
  JOIN forum_topics t ON p.id_topic = t.id_topic
  JOIN juegos j       ON t.id_juego = j.id_juego
  {$whereSQL}
  ORDER BY p.fecha_creado {$orderDir}
  LIMIT 200
";
$posts = [];
if ($res = $conn->query($sql)) {
    $posts = $res->fetch_all(MYSQLI_ASSOC);
    $res->close();
}
$conn->close();

// 9) Reordenar: General + favoritos + recomendados
$priorityIds   = array_unique(array_merge($favorites, $recomIds));
$priorityPosts = $otherPosts = [];
foreach ($posts as $post) {
    if ($post['topic'] === 'General' || in_array((int)$post['id_juego'], $priorityIds, true)) {
        $priorityPosts[] = $post;
    } else {
        $otherPosts[]    = $post;
    }
}
$posts = array_merge($priorityPosts, $otherPosts);

// 10) Preparar array JS de juegos
$orderedGames = [];
foreach ($allGames as $g) {
    if (in_array((int)$g['id_juego'], $priorityIds, true)) {
        $orderedGames[] = ['id'=>(int)$g['id_juego'],'nombre'=>$g['nombre']];
    }
}
foreach ($allGames as $g) {
    if (!in_array((int)$g['id_juego'], $priorityIds, true)) {
        $orderedGames[] = ['id'=>(int)$g['id_juego'],'nombre'=>$g['nombre']];
    }
}
$jsGames = array_merge([['id'=>0,'nombre'=>'General']], $orderedGames);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Comunidad – GAMEDOM</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="css/community.css">
  <link rel="stylesheet" href="css/Index.css">
  <link rel="stylesheet" href="css/chat.css">
</head>
<body>
  <!-- MENÚ SUPERIOR -->
  <div class="menu-superior">
    <div class="nav-left">
      <img src="images/imagenes/Logo.png" alt="Logo" class="logo">
    </div>
    <div class="nav-right">
      <a href="index.php"      class="nav-item <?= $activePage==='index'?'active':'' ?>">Inicio</a>
      <a href="biblioteca.php" class="nav-item <?= $activePage==='biblioteca'?'active':'' ?>">Biblioteca</a>
      <a href="comunidad.php"  class="nav-item <?= $activePage==='comunidad'?'active':'' ?>">Comunidad</a>
      <a href="premios.php"    class="nav-item <?= $activePage==='premios'?'active':'' ?>">Premios</a>
      <a href="perfil.php"     class="nav-item <?= $activePage==='perfil'?'active':'' ?>">Perfil</a>
    </div>
  </div>

  <main class="community-page">
    <!-- IZQUIERDA: FEED -->
    <section class="feed-section">
      <?php if (empty($posts)): ?>
        <p class="no-posts">No hay comentarios que mostrar.</p>
      <?php else: ?>
        <ul class="posts-list">
          <?php foreach ($posts as $post): ?>
            <li class="post-item">
              <div class="post-header">
                <div class="user-info">
                  <strong><?= htmlspecialchars($post['usuario']) ?></strong>
                  &nbsp;en&nbsp;
                  <em><?= $post['topic']==='General' ? 'General' : htmlspecialchars($post['juego']) ?></em>
                </div>
                <time class="timestamp"><?= date("Y-m-d H:i", strtotime($post['fecha_creado'])) ?></time>
              </div>
              <div class="post-body">
                <p><?= nl2br(htmlspecialchars($post['contenido'])) ?></p>
                <?php if ($post['imagen']): ?>
                  <img src="data:image/jpeg;base64,<?= base64_encode($post['imagen']) ?>"
                       alt="Imagen publicada" class="post-image">
                <?php endif; ?>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </section>

    <!-- DERECHA: SIDEBAR (Filtros + Publicar) -->
    <aside class="sidebar">
      <!-- PANEL FILTROS -->
      <div class="filter-panel">
        <h3>Filtrar Publicaciones</h3>
        <form method="get" class="filter-form">
          <div class="form-group">
            <label for="filter_game_search">Juego:</label>
            <input type="hidden" name="filter_game_id" id="filter_game_id"
                   value="<?= htmlspecialchars($filterGame ?? '') ?>">
            <input type="text" id="filter_game_search"
                   placeholder="Escribe para filtrar…" autocomplete="off"
                   value="<?= htmlspecialchars($filterGameNameParam ?? '') ?>">
            <ul class="suggestions" id="filter_suggestions"></ul>
          </div>
          <div class="form-group">
            <label for="time_filter">Tiempo:</label>
            <select name="time_filter" id="time_filter">
              <option value="newest"    <?= $timeFilter==='newest'?   'selected':'' ?>>Más nuevos</option>
              <option value="oldest"    <?= $timeFilter==='oldest'?   'selected':'' ?>>Más antiguos</option>
              <option value="last_hour" <?= $timeFilter==='last_hour'?'selected':'' ?>>Última hora</option>
              <option value="last_day"  <?= $timeFilter==='last_day'? 'selected':'' ?>>Último día</option>
              <option value="last_week" <?= $timeFilter==='last_week'?'selected':'' ?>>Última semana</option>
              <option value="last_month"<?= $timeFilter==='last_month'?'selected':'' ?>>Último mes</option>
            </select>
          </div>
          <button type="submit" class="btn btn-block">Aplicar filtros</button>
        </form>
      </div>

      <!-- PANEL PUBLICAR -->
      <div class="publish-panel">
        <h3>Publicar Comentario</h3>
        <form action="php/create_post.php" method="post" enctype="multipart/form-data">
          <input type="hidden" name="game_id" id="game_id">
          <div class="form-group">
            <label for="game_search">Juego o 'General':</label>
            <input type="text" id="game_search" placeholder="Escribe para buscar…" autocomplete="off">
            <ul class="suggestions" id="suggestions"></ul>
          </div>
          <div class="form-group">
            <label for="contenido">Comentario:</label>
            <textarea name="contenido" id="contenido" rows="4" required></textarea>
          </div>
          <div class="form-group">
            <label for="imagen">Adjuntar imagen (opcional):</label>
            <input type="file" name="imagen" id="imagen" accept="image/*">
          </div>
          <button type="submit" class="btn btn-block">Publicar</button>
        </form>
      </div>
    </aside>
  </main>

  <!-- FOOTER -->
  <footer>
    <nav>
      <a href="index.php">Inicio</a>
      <a href="biblioteca.php">Biblioteca</a>
      <a href="comunidad.php">Comunidad</a>
      <a href="premios.php">Premios</a>
      <a href="perfil.php">Perfil</a>
    </nav>
    <p>&copy; 2025 GAMEDOM. Todos los derechos reservados.</p>
  </footer>

  <script src="js/chat.js"></script>
  <script>
    const games = <?= json_encode($jsGames, JSON_UNESCAPED_UNICODE) ?>;

    // Autocomplete filtros
    document.getElementById('filter_game_search').addEventListener('input', e => {
      const term = e.target.value.trim().toLowerCase();
      const box  = document.getElementById('filter_suggestions');
      box.innerHTML = '';
      if (!term) return;
      games.filter(g => g.nombre.toLowerCase().includes(term))
           .forEach(g => {
             const li = document.createElement('li');
             li.textContent = g.nombre;
             li.onclick = () => {
               e.target.value = g.nombre;
               document.getElementById('filter_game_id').value = g.id;
               box.innerHTML = '';
             };
             box.appendChild(li);
           });
    });

    // Autocomplete publicación
    document.getElementById('game_search').addEventListener('input', e => {
      const term = e.target.value.trim().toLowerCase();
      const box  = document.getElementById('suggestions');
      box.innerHTML = '';
      if (!term) return;
      games.filter(g => g.nombre.toLowerCase().includes(term))
           .forEach(g => {
             const li = document.createElement('li');
             li.textContent = g.nombre;
             li.onclick = () => {
               e.target.value = g.nombre;
               document.getElementById('game_id').value = g.id;
               box.innerHTML = '';
             };
             box.appendChild(li);
           });
    });

    // Validación antes de enviar
    document.querySelector('.publish-panel form').addEventListener('submit', e => {
      if (!document.getElementById('game_id').value) {
        alert('Selecciona un juego de la lista o "General".');
        e.preventDefault();
      }
    });
  </script>
</body>
</html>
