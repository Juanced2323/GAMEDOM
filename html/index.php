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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>GAMEDOM - Inicio</title>
  <!-- Enlace a la hoja de estilos. Ajusta la ruta si es necesario -->
  <link rel="stylesheet" href="css/Index.css">
  <!-- Font Awesome para los íconos de las flechas del slider -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
</head>
<body>
  <!-- Menú Superior con dropdown de idiomas -->
  <div class="menu-superior">
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
    <a href="http://localhost:8080/login.html" data-text="login">Iniciar sesión</a>
    <span>|</span>
    <div class="dropdown">
      <span class="dropdown-toggle" data-text="idiomas">Idiomas ▼</span>
      <ul class="dropdown-menu">
        <li><a href="#" onclick="changeLanguage('es')"><img src="images/Banderas/España.png" alt="Español">Español</a></li>
        <li><a href="#" onclick="changeLanguage('en')"><img src="images/Banderas/Inglés.png" alt="Inglés">English</a></li>
        <li><a href="#" onclick="changeLanguage('fr')"><img src="images/Banderas/Francia.png" alt="Francés">Français</a></li>
        <li><a href="#" onclick="changeLanguage('de')"><img src="images/Banderas/Alemania.png" alt="Alemán">Deutsch</a></li>
        <li><a href="#" onclick="changeLanguage('it')"><img src="images/Banderas/Italia.png" alt="Italiano">Italiano</a></li>
        <li><a href="#" onclick="changeLanguage('pt')"><img src="images/Banderas/Portugal.png" alt="Portugués">Português</a></li>
        <li><a href="#" onclick="changeLanguage('ru')"><img src="images/Banderas/Ruso.png" alt="Ruso">Русский</a></li>
        <li><a href="#" onclick="changeLanguage('cn')"><img src="images/Banderas/China.png" alt="Chino">中文</a></li>
        <li><a href="#" onclick="changeLanguage('jp')"><img src="images/Banderas/Japón.png" alt="Japonés">日本語</a></li>
        <li><a href="#" onclick="changeLanguage('kr')"><img src="images/Banderas/Corea del sur.png" alt="Coreano">한국어</a></li>
        <li><a href="#" onclick="changeLanguage('sa')"><img src="images/Banderas/Arabia Saudí.png" alt="Árabe"> العربية</a></li>
        <li><a href="#" onclick="changeLanguage('in')"><img src="images/Banderas/India.png" alt="Hindi">हिन्दी</a></li>
        <li><a href="#" onclick="changeLanguage('tr')"><img src="images/Banderas/Turquía.png" alt="Turco">Türkçe</a></li>
        <li><a href="#" onclick="changeLanguage('nl')"><img src="images/Banderas/Países Bajos.png" alt="Holandés">Nederlands</a></li>
        <li><a href="#" onclick="changeLanguage('se')"><img src="images/Banderas/Suecia.png" alt="Sueco">Svenska</a></li>
        <li><a href="#" onclick="changeLanguage('pl')"><img src="images/Banderas/Polaco.png" alt="Polaco">Polski</a></li>
        <li><a href="#" onclick="changeLanguage('gr')"><img src="images/Banderas/Grecia.png" alt="Griego">Ελληνικά</a></li>
        <li><a href="#" onclick="changeLanguage('il')"><img src="images/Banderas/Hebreo.png" alt="Hebreo">עברית</a></li>
        <li><a href="#" onclick="changeLanguage('fi')"><img src="images/Banderas/Finlandés.png" alt="Finlandés">Suomi</a></li>
        <li><a href="#" onclick="changeLanguage('dk')"><img src="images/Banderas/Danés.png" alt="Danés">Dansk</a></li>
        <li><a href="#" onclick="changeLanguage('hu')"><img src="images/Banderas/hungría.png" alt="Húngaro">Magyar</a></li>
        <li><a href="#" onclick="changeLanguage('cz')"><img src="images/Banderas/Checo.png" alt="Checo">Čeština</a></li>
        <li><a href="#" onclick="changeLanguage('ro')"><img src="images/Banderas/Rumano.png" alt="Rumano">Română</a></li>
        <li><a href="#" onclick="changeLanguage('bg')"><img src="images/Banderas/Bulgaro.png" alt="Búlgaro">Български</a></li>
        <li><a href="#" onclick="changeLanguage('ua')"><img src="images/Banderas/Ucraniano.png" alt="Ucraniano">Українська</a></li>
        <li><a href="#" onclick="changeLanguage('th')"><img src="images/Banderas/Tailandés.png" alt="Tailandés">ไทย</a></li>
        <li><a href="#" onclick="changeLanguage('id')"><img src="images/Banderas/Indonesia.png" alt="Indonesia">Bahasa Indonesia</a></li>
        <li><a href="#" onclick="changeLanguage('vn')"><img src="images/Banderas/Vietnamita.png" alt="Vietnamita">Tiếng Việt</a></li>
        <li><a href="#" onclick="changeLanguage('ir')"><img src="images/Banderas/Persa.png" alt="Persa"> فارسی</a></li>
      </ul>
    </div>
  </div>

  <!-- Contenedor principal: Filtros a la izquierda y slider integrado a la derecha -->
  <main style="display: flex;">
    <!-- Filtros -->
    <aside class="filter-sidebar">
      <h3>Filtrar Juegos</h3>
      <form method="GET" action="index.php">
        <div class="filter-group">
          <label for="search">Buscar:</label>
          <input type="text" id="search" name="search" placeholder="Buscar juegos..." value="<?php echo htmlspecialchars($searchTerm); ?>">
        </div>
        <div class="filter-group">
          <h4>Categoría</h4>
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

    <!-- Slider integrado con el catálogo de juegos -->
    <div class="container">
      <div class="slide">
        <!-- Ítems destacados fijos (puedes conservarlos o eliminarlos) -->
        <div class="item" style="background-image: url('images/Juegos/Hundir la flota.jpg');">
          <div class="content">
            <div class="name" data-text="Hundirlaflota">Hundir la flota</div>
            <div class="des" data-text="txthundirlaflota">
              ¡Zarpa hacia la estrategia definitiva! Toma el mando de tu escuadrón naval, despliega tus barcos y desafía a oponentes en duelos épicos.
            </div>
            <button data-text="Jugar">Jugar</button>
          </div>
        </div>

        <div class="item" style="background-image: url('images/Juegos/risk1.jpg');">
          <div class="content">
            <div class="name" data-text="Risk">Risk</div>
            <div class="des" data-text="txtRisk">
              ¡Prepárate para una batalla legendaria! Tu misión es expandir tu imperio, conquistar territorios y enfrentarte a tus oponentes en un juego de estrategia.
            </div>
            <button data-text="Jugar">Jugar</button>
          </div>
        </div>

        <!-- Generación dinámica de juegos del catálogo -->
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
                  <?php 
                    // Puedes usar la descripción del juego si existe, o un mensaje por defecto:
                    echo isset($game['descripcion']) ? htmlspecialchars($game['descripcion']) : "Descripción no disponible";
                  ?>
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

  <!-- Footer -->
  <footer>
    <p data-text="footer">
      © 2025 CodeCrafters. Todos los derechos reservados. Todas las marcas registradas pertenecen a sus respectivos dueños en EE. UU. y otros países. <br>
      Todos los precios incluyen IVA (donde sea aplicable).
    </p>
    <nav>
      <a href="Política de privacidad.html" data-text="privacy">Política de Privacidad</a> |
      <a href="Información legal.html" data-text="legal">Información legal</a> |
      <a href="Cookies.html" data-text="cookies">Cookies</a> |
      <a href="A cerca de.html" data-text="about">A cerca de CodeCrafters</a>
    </nav> 
  </footer>

  <!-- Script para slider y cambio de idioma -->
  <script src="js/Index.js"></script>
</body>
</html>
