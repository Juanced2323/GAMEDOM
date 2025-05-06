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

// 4) Lógica de recomendación o consulta según filtros
if (!$filtersApplied && isset($_SESSION['usuario'])) {
    $usuario = $_SESSION['usuario'];
    $recommended = getContentBasedRecommendations($usuario, $conn, 10);

    $sqlAll = "SELECT * FROM juegos";
    $resAll = $conn->query($sqlAll);
    $allGames = [];
    while ($row = $resAll->fetch_assoc()) {
        $allGames[] = $row;
    }
    $resAll->close();

    $recommendedIDs = array_column($recommended, 'id_juego');
    $orderedGames = $recommended;
    foreach ($allGames as $g) {
        if (!in_array($g['id_juego'], $recommendedIDs)) {
            $orderedGames[] = $g;
        }
    }
} else {
    $sql = "SELECT DISTINCT j.* FROM juegos j";
    $wheres = [];
    $params = [];
    $types = '';

    if (!empty($selectedCategories)) {
        $sql .= " JOIN juegos_categorias jc ON j.id_juego = jc.id_juego
                  JOIN categorias c ON c.id_categoria = jc.id_categoria";
        $placeholders = implode(',', array_fill(0, count($selectedCategories), '?'));
        $wheres[] = "c.nombre IN ($placeholders)";
        $types .= str_repeat('s', count($selectedCategories));
        foreach ($selectedCategories as $cat) {
            $params[] = $cat;
        }
    }

    if ($searchTerm !== '') {
        $wheres[] = "j.nombre LIKE ?";
        $types .= 's';
        $params[] = "%$searchTerm%";
    }

    if (!empty($wheres)) {
        $sql .= " WHERE " . implode(" AND ", $wheres);
    }
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
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>GAMEDOM - Inicio</title>
  <!-- Enlaza tu CSS principal -->
  <link rel="stylesheet" href="css/Index.css">
  <!-- Font Awesome para íconos -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />

</head>
<body>
  <!-- MENÚ SUPERIOR -->
  <div class="menu-superior">
    <!-- Bloque Izquierdo: Logo -->
    <a href="http://localhost:8080/">
          <img src="images/imagenes/Logo.png" alt="Logo de Gamedom" class="logo">
    </a>

    <!-- Bloque Derecho: Enlaces y dropdown -->
    <div class="nav-right">
      <a href="biblioteca.php" class="nav-item">Biblioteca</a>
      <a href="comunidad.php" class="nav-item">Comunidad</a>
      <a href="premios.php" class="nav-item">Premios</a>
      <?php if (isset($_SESSION['usuario'])): ?>
        <a href="perfil.php" class="nav-item">Perfil</a>
      <?php else: ?>
        <a href="login.html" class="nav-item">Iniciar Sesión</a>
      <?php endif; ?>

      <div class="dropdown">
        <span class="dropdown-toggle" data-text="idiomas">Idiomas ▼</span>
        <ul class="dropdown-menu">
          <li><a href="#" onclick="changeLanguage('es')">
            <img src="images/Banderas/España.png" alt="Español">Español
          </a></li>
          <li><a href="#" onclick="changeLanguage('en')">
            <img src="images/Banderas/Inglés.png" alt="Inglés">English
          </a></li>
          <!-- Más idiomas si quieres -->
        </ul>
      </div>
    </div>
  </div>

  <!-- CONTENIDO PRINCIPAL: Filtros y Slider -->
  <main class="main-content">
    <!-- Barra lateral de filtros -->
    <aside class="filter-sidebar">
      <h3>Filtrar Juegos</h3>
      <form method="GET" action="index.php">
        <div class="filter-group">
          <label for="search">Buscar:</label>
          <input type="text" id="search" name="search" placeholder="Buscar juegos..." value="<?php echo htmlspecialchars($searchTerm); ?>">
        </div>
        <div class="filter-group">
          <h4 data-text="categoria">Categoría</h4>
          <?php
          foreach ($allCategories as $catName) {
              $checked = in_array($catName, $selectedCategories) ? 'checked' : '';
              echo "<label>
                      <input type='checkbox' name='category[]' value='" . htmlspecialchars($catName) . "' $checked>
                      " . htmlspecialchars($catName) . "
                    </label>";
          }
          ?>
        </div>
        <button type="submit">Filtrar</button>
      </form>
    </aside>

    <!-- Slider de juegos -->
    <div class="container">
      <div class="slide1">
        <!-- Ítems fijos (puedes conservar o eliminar estos si lo deseas) -->
        <div class="item" style="background-image: url('images/Juegos/Hundir la flota.jpg');">
          <div class="content">
            <div class="name" data-text="Hundirlaflota">Hundir la flota</div>
            <div class="des" data-text="txthundirlaflota">
              ¡Zarpa hacia la estrategia definitiva!
            </div>
            <button data-text="Jugar">Jugar</button>
          </div>
        </div>
        <div class="item" style="background-image: url('images/Juegos/risk.jpg');">
          <div class="content">
            <div class="name" data-text="Risk">Risk</div>
            <div class="des" data-text="txtRisk">
              ¡Prepárate para una batalla legendaria! 
            </div>
            <button data-text="Jugar">Jugar</button>
          </div>
        </div>

        <!-- Ítems dinámicos -->
        <?php if (!empty($orderedGames)): ?>
          <?php foreach ($orderedGames as $game): ?>
            <?php
              if (!empty($game['icono'])) {
                  $iconoBase64 = "data:image/jpeg;base64," . base64_encode($game['icono']);
              } else {
                  $iconoBase64 = "images/default-game.png";
              }
            ?>
            <div class="item" style="background-image: url('<?php echo $iconoBase64; ?>');">
              <div class="content">
                <div class="name"><?php echo htmlspecialchars($game['nombre']); ?></div>
                <div class="des">
                  <?php echo isset($game['descripcion']) ? htmlspecialchars($game['descripcion']) : "Descripción no disponible"; ?>
                </div>
                <button onclick="window.location.href='pantalla_juego.php?id=<?php echo $game['id_juego']; ?>'">
                  Jugar
                </button>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="item" style="background: #ccc;">
            <div class="content">
              <div class="name">Sin resultados</div>
              <div class="des">No hay juegos que coincidan con tu búsqueda.</div>
            </div>
          </div>
        <?php endif; ?>
      </div>
      <!-- Controles del slider -->
      <div class="button">
        <button class="prev"><i class="fa-solid fa-arrow-left"></i></button>
        <button class="next"><i class="fa-solid fa-arrow-right"></i></button>
      </div>
    </div>
  </main>
  
<!--SLIDER AUTOMÁTICO-->
<div class="container-carousel">
        <div class="carruseles" id="slider">
            <section class="slider-section">
                <img class="img1" src="images/Juegos/Burden of command.jpg">
            </section>
            <section class="slider-section">
                <img src="images/Juegos/Jurassic world.jpg">
            </section>
            <section class="slider-section">
                <img class= "img2" src="images/Juegos/iRacing.jpg">
            </section>
            <section class="slider-section">
                <img class="img1" src="images/Juegos/Ajedrez.jpg">
            </section>
            <section class="slider-section">
                <img src="images/Juegos/Nexus prime guerra de campeones.jpg">
            </section>
            <section class="slider-section">
                <img src="images/Juegos/3en raya.jpg">
            </section>
            <section class="slider-section">
                <img src="images/Juegos/Red dead redemption.jpg">
            </section>
            <section class="slider-section">
                <img class= "img2" src="images/Juegos/solitario.jpg">
            </section>
            <section class="slider-section">
                <img class= "img2" src="images/Juegos/tetris.jpg">
            </section>
            <section class="slider-section">
                <img src="images/Juegos/Drive beyons horizon.jpg">
            </section>
        </div>
        <div class="btn-left"><i class='bx bx-chevron-left'><</i></div>
        <div class="btn-right"><i class='bx bx-chevron-right'>></i></div>
    </div>
  

  <!-- FOOTER (único) -->
  <footer class="footer">
    <p data-text="cc">
      © 2025 CodeCrafters. Todos los derechos reservados. Todas las marcas registradas pertenecen a sus respectivos dueños en EE. UU. y otros países.
      Todos los precios incluyen IVA (donde sea aplicable).
    </p>
    <nav>
      <a href="Política de privacidad.html" data-text="privacy">Política de Privacidad</a> |
      <a href="Información legal.html" data-text="legal">Información legal</a> |
      <a href="Cookies.html" data-text="cookies">Cookies</a> |
      <a href="A cerca de.html" data-text="about">A cerca de CodeCrafters</a>
    </nav>
  </footer>

  <!-- Script para slider e idiomas -->
  <script src="js/Index.js"></script>


</body>
</html>
