<?php
session_start();
$activePage = basename($_SERVER['PHP_SELF'], ".php");

if (!isset($_SESSION['usuario'])) {
    // Si no está logueado, se mostrará un mensaje de acceso restringido.
    // No se necesita conexión a la BBDD.
    $favGames = [];
} else {
    require_once "php/db_connect.php";
    $usuario = $_SESSION['usuario'];

    // Consultar los juegos favoritos del usuario, uniendo la tabla favoritos y juegos.
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
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Biblioteca - GAMEDOM</title>
  <link rel="stylesheet" href="css/main.css">
</head>
<body>
  <header>
    <nav class="navbar">
      <div class="nav-left">
        <!-- Nuevo enlace a Inicio -->
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
    <?php if (!isset($_SESSION['usuario'])): ?>
      <!-- Mensaje de restricción -->
      <div class="restricted-access">
        <h2>Acceso Restringido</h2>
        <p>Esta sección está disponible solo para usuarios registrados.</p>
        <a href="login.html" class="btn-acceso">Iniciar Sesión</a>
      </div>
    <?php else: ?>
      <!-- Contenido de la biblioteca: se muestran los juegos favoritos -->
      <section class="game-catalog">
        <h2>Biblioteca de Juegos</h2>
        <div class="game-list">
          <?php if (!empty($favGames)): ?>
            <?php foreach ($favGames as $game): ?>
              <div class="game-card">
                <a href="pantalla_juego.php?id=<?php echo $game['id_juego']; ?>">
                  <?php
                    // Si el juego tiene icono (almacenado como BLOB), se convierte a base64
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
            <p>No tienes juegos en tu biblioteca.</p>
          <?php endif; ?>
        </div>
      </section>
    <?php endif; ?>
  </main>
</body>
</html>
