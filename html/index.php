<?php
session_start();
require_once "php/db_connect.php";
require_once "php/recommendations.php";

// 1) Recoger filtros desde GET
$searchTerm = trim($_GET['search'] ?? '');
$selectedCategories = $_GET['category'] ?? [];  // array de strings

// 2) Verificar si hay filtros aplicados
$filtersApplied = ($searchTerm !== '' || !empty($selectedCategories));

// 3) Obtener la lista de categorías desde la tabla `categorias`
$catQuery = "SELECT nombre FROM categorias ORDER BY nombre ASC";
$catResult = $conn->query($catQuery);
$allCategories = [];
while ($catRow = $catResult->fetch_assoc()) {
    $allCategories[] = $catRow['nombre'];
}
$catResult->close();

$orderedGames = []; // Arreglo final de juegos que se mostrarán

// 4) Si NO hay filtros y el usuario está logueado, usar lógica de recomendación
if (!$filtersApplied && isset($_SESSION['usuario'])) {
    $usuario = $_SESSION['usuario'];
    // Obtener juegos recomendados
    $recommended = getContentBasedRecommendations($usuario, $conn, 10);

    // Obtener todos los juegos
    $sqlAll = "SELECT * FROM juegos";
    $resAll = $conn->query($sqlAll);
    $allGames = [];
    while ($row = $resAll->fetch_assoc()) {
        $allGames[] = $row;
    }
    $resAll->close();

    // Reordenar: primero los recomendados y luego el resto
    $recommendedIDs = array_column($recommended, 'id_juego');
    $orderedGames = $recommended;
    foreach ($allGames as $g) {
        if (!in_array($g['id_juego'], $recommendedIDs)) {
            $orderedGames[] = $g;
        }
    }

} else {
    // 5) Si HAY filtros, construimos una consulta dinámica
    $sql = "SELECT DISTINCT j.* FROM juegos j";
    $wheres = [];
    $params = [];
    $types = '';

    // Si se han elegido categorías, unimos con pivot y tabla categorias
    if (!empty($selectedCategories)) {
        $sql .= " JOIN juegos_categorias jc ON j.id_juego = jc.id_juego
                  JOIN categorias c ON c.id_categoria = jc.id_categoria";
        
        // c.nombre IN (...)
        $placeholders = implode(',', array_fill(0, count($selectedCategories), '?'));
        $wheres[] = "c.nombre IN ($placeholders)";
        
        // bind_param (todas strings)
        $types .= str_repeat('s', count($selectedCategories));
        foreach($selectedCategories as $cat) {
            $params[] = $cat;
        }
    }

    // Si hay término de búsqueda
    if ($searchTerm !== '') {
        $wheres[] = "j.nombre LIKE ?";
        $types .= 's';
        $params[] = "%$searchTerm%";
    }

    if (!empty($wheres)) {
        $sql .= " WHERE " . implode(" AND ", $wheres);
    }

    // Ordenamos por nombre
    $sql .= " ORDER BY j.nombre ASC";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error en prepare (filtros): " . $conn->error);
    }

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $res = $stmt->get_result();
    
    while ($row = $res->fetch_assoc()) {
        $orderedGames[] = $row;
    }
    $stmt->close();
}

$conn->close();
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
      <!-- Formulario GET para filtrar -->
      <form method="GET" action="index.php">
        <div class="filter-group">
          <label for="search">Buscar:</label>
          <input type="text" id="search" name="search"
                 placeholder="Buscar juegos..."
                 value="<?php echo htmlspecialchars($searchTerm); ?>">
        </div>
        
        <div class="filter-group">
          <h4>Categoría</h4>
          <?php
          // Mostrar todas las categorías existentes (desde la tabla `categorias`)
          foreach ($allCategories as $catName) {
              // Comprobamos si la categoría está seleccionada
              $checked = in_array($catName, $selectedCategories) ? 'checked' : '';
              echo "<label>
                      <input type='checkbox' name='category[]' value='".htmlspecialchars($catName)."' $checked>
                      ".htmlspecialchars($catName)."
                    </label>";
          }
          ?>
        </div>
        
        <button type="submit">Filtrar</button>
      </form>
    </aside>
    
    <section class="game-catalog">
      <h2>Catálogo de Juegos</h2>
      <div class="game-list">
        <?php if (!empty($orderedGames)): ?>
          <?php foreach ($orderedGames as $game): ?>
            <div class="game-card">
              <a href="pantalla_juego.php?id=<?php echo $game['id_juego']; ?>">
                <?php
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
          <p>No hay juegos que coincidan con tu búsqueda.</p>
        <?php endif; ?>
      </div>
    </section>
  </main>
</body>
</html>
