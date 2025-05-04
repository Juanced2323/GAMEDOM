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

// 2) Cargar lista de juegos favoritos
$favorites = [];
$stmtFav = $conn->prepare("SELECT id_juego FROM favoritos WHERE usuario = ?");
$stmtFav->bind_param("s", $username);
$stmtFav->execute();
$resFav = $stmtFav->get_result();
while ($row = $resFav->fetch_assoc()) {
    $favorites[] = $row['id_juego'];
}
$stmtFav->close();

// 3) Cargar detalles de esos juegos
$favGames = [];
if ($favorites) {
    // Construimos tantos placeholders como juegos favoritos
    $placeholders = implode(',', array_fill(0, count($favorites), '?'));
    $sql = "SELECT id_juego, nombre FROM juegos WHERE id_juego IN ($placeholders) ORDER BY nombre";
    $stmt = $conn->prepare($sql);
    // Bind dinámico
    $types = str_repeat('i', count($favorites));
    $stmt->bind_param($types, ...$favorites);
    $stmt->execute();
    $favGames = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// 4) Cargar comentarios (posts) de esos juegos
$posts = [];
if ($favorites) {
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
    $stmt->bind_param($types, ...$favorites);
    $stmt->execute();
    $posts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$conn->close();
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
    <!-- 1. Feed de comentarios de tus favoritos -->
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

    <!-- 2. Formulario para publicar un nuevo comentario -->
    <section class="new-post-section">
      <h2>Publicar Comentario</h2>
      <form action="php/create_post.php" method="post" enctype="multipart/form-data">
        <div class="form-group">
          <label for="game_select">Selecciona un juego:</label>
          <select name="game_id" id="game_select" required>
            <option value="">-- Elige uno de tus favoritos --</option>
            <?php foreach ($favGames as $g): ?>
              <option value="<?= $g['id_juego'] ?>"><?= htmlspecialchars($g['nombre']) ?></option>
            <?php endforeach; ?>
            <option value="other">Otro juego…</option>
          </select>
        </div>

        <div class="form-group" id="other_game_group" style="display:none;">
          <label for="other_game">¿Qué juego quieres?</label>
          <input type="text" name="other_game" id="other_game" placeholder="Escribe el nombre del juego">
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

    <!-- 3. Solicitar un juego nuevo -->
    <section class="request-section">
      <h2>Solicitar un juego</h2>
      <form action="php/request_game.php" method="post">
        <div class="form-group">
          <label for="request_game">¿Qué juego te gustaría ver en GAMEDOM?</label>
          <input type="text" name="game_request" id="request_game" required placeholder="Nombre del juego">
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
      <a href="A cerca de.html">A cerca de CodeCrafters</a>
    </nav>
  </footer>

  <script src="js/chat.js"></script>
  <script>
    // Mostrar/ocultar campo de "otro juego" según selección
    document.getElementById('game_select')
      .addEventListener('change', function() {
        const otherGroup = document.getElementById('other_game_group');
        otherGroup.style.display = this.value === 'other' ? 'block' : 'none';
      });
  </script>
</body>
</html>
