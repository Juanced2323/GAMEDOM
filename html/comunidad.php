<?php
session_start();
$activePage = basename($_SERVER['PHP_SELF'], ".php");
require_once "php/db_connect.php";

// 1) Comprobación de sesión
if (!isset($_SESSION['usuario'])) {
    $conn->close();
    header("Location: login.html");
    exit;
}
$username = $_SESSION['usuario'];

// 2) Recuperar IDs de tus juegos favoritos
$favorites = [];
$stmtFav = $conn->prepare("SELECT id_juego FROM favoritos WHERE usuario = ?");
$stmtFav->bind_param("s", $username);
$stmtFav->execute();
$resFav = $stmtFav->get_result();
while ($r = $resFav->fetch_assoc()) {
    $favorites[] = $r['id_juego'];
}
$stmtFav->close();

// 3) Recuperar todos los juegos, ordenados alfabéticamente
$allGames = [];
$resAll = $conn->query("SELECT id_juego, nombre FROM juegos ORDER BY nombre ASC");
while ($g = $resAll->fetch_assoc()) {
    $allGames[] = $g;
}
$resAll->close();

// 4) Reordenar: primero tus favoritos (en orden alfabético), luego el resto
$favOrdered   = [];
$othersOrdered = [];
foreach ($allGames as $g) {
    if (in_array($g['id_juego'], $favorites, true)) {
        $favOrdered[] = $g;
    } else {
        $othersOrdered[] = $g;
    }
}
$orderedGames = array_merge($favOrdered, $othersOrdered);

// 5) Cargar posts de tus juegos favoritos (igual que antes)
$posts = [];
if (!empty($favorites)) {
    $placeholders = implode(',', array_fill(0, count($favorites), '?'));
    $sql = "
      SELECT p.id_post, p.usuario, p.contenido, p.imagen, p.fecha_creado,
             j.nombre AS juego
      FROM forum_posts p
      JOIN forum_topics t ON p.id_topic = t.id_topic
      JOIN juegos j ON t.id_juego = j.id_juego
      WHERE t.id_juego IN ($placeholders)
      ORDER BY p.fecha_creado DESC
      LIMIT 50
    ";
    $stmt = $conn->prepare($sql);
    $types = str_repeat('i', count($favorites));
    $stmt->bind_param($types, ...$favorites);
    $stmt->execute();
    $posts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$conn->close();

// 6) Preparar array JS de sugerencias: «General» + $orderedGames
$jsGames = [['id'=>0,'nombre'=>'General']];
foreach ($orderedGames as $g) {
    $jsGames[] = ['id'=>$g['id_juego'], 'nombre'=>$g['nombre']];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Comunidad - GAMEDOM</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="css/Index.css">
  <link rel="stylesheet" href="css/community.css">
  <link rel="stylesheet" href="css/chat.css">
  <style>
    .suggestions {
      border: 1px solid #ccc;
      max-height: 150px;
      overflow-y: auto;
      list-style: none;
      padding: 0;
      margin: 4px 0 0;
    }
    .suggestions li {
      padding: 8px;
      cursor: pointer;
    }
    .suggestions li:hover {
      background: #f0f0f0;
    }
  </style>
</head>
<body>
  <!-- MENÚ SUPERIOR -->
  <div class="menu-superior">
    <div class="nav-left">
      <img src="images/imagenes/Logo.png" alt="Logo Gamedom" class="logo">
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
    <!-- Feed de comentarios de tus favoritos -->
    <section class="feed-section">
      <h2>Comentarios en tus juegos favoritos</h2>
      <?php if (empty($posts)): ?>
        <p>Aún no hay comentarios en tus juegos favoritos.</p>
      <?php else: ?>
        <ul class="posts-list">
          <?php foreach ($posts as $post): ?>
            <li class="post-item">
              <div class="post-header">
                <strong><?= htmlspecialchars($post['usuario']) ?></strong>
                en <em><?= htmlspecialchars($post['juego']) ?></em>
                <span class="timestamp"><?= date("Y-m-d H:i", strtotime($post['fecha_creado'])) ?></span>
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

    <!-- Formulario para publicar un nuevo comentario -->
    <section class="new-post-section">
      <h2>Publicar Comentario</h2>
      <form action="php/create_post.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="game_id" id="game_id">
        <div class="form-group">
          <label for="game_search">Juego (o 'General'):</label>
          <input type="text" id="game_search"
                 placeholder="Empieza a escribir…"
                 autocomplete="off">
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
        <button type="submit" class="btn">Publicar</button>
      </form>
    </section>

    <!-- Solicitar un juego nuevo -->
    <section class="request-section" id="request-section">
      <h2>Solicitar un juego</h2>
      <form action="php/request_game.php" method="post">
        <div class="form-group">
          <label for="request_game">¿Qué juego te gustaría ver?</label>
          <input type="text" name="game_request" id="request_game"
                 placeholder="Nombre del juego" required>
        </div>
        <button type="submit" class="btn">Enviar solicitud</button>
      </form>
    </section>
  </main>

  <!-- FOOTER -->
  <footer class="footer">
    <p>
      © 2025 CodeCrafters. Todos los derechos reservados.<br>
      Todas las marcas registradas pertenecen a sus respectivos dueños en EE. UU. y otros países.<br>
      Todos los precios incluyen IVA (donde sea aplicable).
    </p>
    <nav>
      <a href="Política de privacidad.html">Política de Privacidad</a> |
      <a href="Información legal.html">Información legal</a> |
      <a href="Cookies.html">Cookies</a> |
      <a href="A cerca de.html">Acerca de CodeCrafters</a>
    </nav>
  </footer>

  <!-- Scripts -->
  <script src="js/chat.js"></script>
  <script>
    const games      = <?= json_encode($jsGames, JSON_UNESCAPED_UNICODE) ?>;
    const input       = document.getElementById('game_search');
    const suggBox     = document.getElementById('suggestions');
    const hiddenGameId= document.getElementById('game_id');
    const form        = document.querySelector('.new-post-section form');

    // Filtrar y mostrar sugerencias (favoritos primero)
    input.addEventListener('input', () => {
      const term = input.value.trim().toLowerCase();
      suggBox.innerHTML = '';
      if (!term) return;
      games
        .filter(g => g.nombre.toLowerCase().includes(term))
        .forEach(g => {
          const li = document.createElement('li');
          li.textContent = g.nombre;
          li.dataset.id = g.id;
          li.addEventListener('click', () => {
            input.value = g.nombre;
            hiddenGameId.value = g.id;
            suggBox.innerHTML = '';
          });
          suggBox.appendChild(li);
        });
    });

    // Validar envío: si coincide exactamente, asignamos ID
    form.addEventListener('submit', e => {
      if (!hiddenGameId.value) {
        const term = input.value.trim().toLowerCase();
        const match = games.find(g => g.nombre.toLowerCase() === term);
        if (match) {
          hiddenGameId.value = match.id;
        }
      }
      if (!hiddenGameId.value) {
        alert('Por favor, selecciona un juego de la lista o "General".');
        e.preventDefault();
      }
    });
  </script>
</body>
</html>
