<?php
session_start();
$activePage = basename($_SERVER['PHP_SELF'], ".php");

if (!isset($_SESSION['usuario'])) {
    // Si no está logueado, no se consultan datos
    $favGames = [];
} else {
    require_once "php/db_connect.php";
    $usuario = $_SESSION['usuario'];

    // Consultar los juegos favoritos del usuario
    $stmt = $conn->prepare("
        SELECT j.*
        FROM favoritos f
        JOIN juegos j ON j.id_juego = f.id_juego
        WHERE f.usuario = ?
        ORDER BY j.nombre ASC
    ");
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
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Biblioteca - GAMEDOM</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="css/Index.css">
  <link rel="stylesheet" href="css/library.css">
</head>
<body>
  <!-- MENÚ SUPERIOR (igual que en index.php) -->
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
                  // Mostrar el icono (blob) si existe; de lo contrario, default
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
    <?php endif; ?>
  </main>

  <!-- FOOTER -->
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
</body>
</html>