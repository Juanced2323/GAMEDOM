<?php
session_start();
require_once "php/db_connect.php";

// Consulta para obtener los juegos ordenados (puedes ajustar el orden o condiciones según necesites)
$query = "SELECT * FROM juegos ORDER BY nombre ASC";
$result = $conn->query($query);

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>GAMEDOM</title>
  <link rel="stylesheet" href="css/main.css">
</head>
<body>
<header>
    <nav class="navbar">
      <div class="nav-left">
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
  <aside class="filter-sidebar">
    <h3>Filtrar Juegos</h3>
    <div class="filter-group">
      <label for="search">Buscar:</label>
      <input type="text" id="search" placeholder="Buscar juegos...">
    </div>
    <div class="filter-group">
      <h4>Categoría</h4>
      <label><input type="checkbox"> Acción</label>
      <label><input type="checkbox"> Aventura</label>
      <label><input type="checkbox"> Estrategia</label>
      <label><input type="checkbox"> Deportes</label>
    </div>
    <div class="filter-group">
      <h4>Género</h4>
      <label><input type="checkbox"> RPG</label>
      <label><input type="checkbox"> Shooter</label>
      <label><input type="checkbox"> Puzzle</label>
      <label><input type="checkbox"> Simulación</label>
    </div>
    <div class="filter-group">
      <h4>Modo de Juego</h4>
      <label><input type="checkbox"> Un jugador</label>
      <label><input type="checkbox"> Multijugador</label>
      <label><input type="checkbox"> Ambos</label>
    </div>
    <div class="filter-group">
      <h4>Precio</h4>
      <select>
        <option value="all">Todos</option>
        <option value="free">Gratis</option>
        <option value="paid">De pago</option>
        <option value="discount">En oferta</option>
      </select>
    </div>
  </aside>
  
  <section class="game-catalog">
    <h2>Catálogo de Juegos</h2>
    <div class="game-list">
      <?php while($game = $result->fetch_assoc()): ?>
        <div class="game-card">
          <!-- El enlace redirige a pantalla_juego.php pasando el id del juego como parámetro -->
          <a href="pantalla_juego.php?id=<?php echo $game['id_juego']; ?>">
            <?php
            // Si el juego tiene un icono (almacenado como BLOB), lo convertimos a base64
            if (!empty($game['icono'])) {
              $iconoBase64 = "data:image/jpeg;base64," . base64_encode($game['icono']);
              echo '<img src="' . $iconoBase64 . '" alt="' . htmlspecialchars($game['nombre']) . '">';
            } else {
              // En caso contrario, mostramos una imagen por defecto (ajusta la ruta según tu proyecto)
              echo '<img src="images/default-game.png" alt="Juego sin icono">';
            }
            ?>
            <h4><?php echo htmlspecialchars($game['nombre']); ?></h4>
          </a>
        </div>
      <?php endwhile; ?>
    </div>
  </section>
</main>
</body>
</html>
<?php $conn->close(); ?>
