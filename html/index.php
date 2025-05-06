<?php
session_start();
require_once "php/db_connect.php";
require_once "php/recommendations.php";

// 1. Obtener categorías para los filtros
$allCategories = [];
$sqlAllCat = "SELECT nombre FROM categorias ORDER BY nombre ASC";
$resCat = $conn->query($sqlAllCat);
while ($catRow = $resCat->fetch_assoc()) {
    $allCategories[] = $catRow['nombre'];
}
$resCat->close();

// 2. Lógica de filtros / recomendaciones
$searchTerm = trim($_GET['search'] ?? '');
$selectedCategories = $_GET['category'] ?? [];
$filtersApplied = ($searchTerm !== '' || !empty($selectedCategories));

$orderedGames = [];

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
    // Consulta filtrada
    $sql = "SELECT DISTINCT j.* FROM juegos j";
    $wheres = [];
    $params = [];
    $types = '';

    if (!empty($selectedCategories)) {
        $sql .= "
            JOIN juegos_categorias jc ON j.id_juego = jc.id_juego
            JOIN categorias c ON c.id_categoria = jc.id_categoria
        ";
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
  <title>GAMEDOM - Inicio</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- CSS principal con los estilos originales -->
  <link rel="stylesheet" href="css/Index.css">
  <!-- CSS para el catálogo (tarjetas) -->
  <link rel="stylesheet" href="css/catalogo.css">
  <!-- Font Awesome para íconos -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />

</head>
<body>
  <!-- MENÚ SUPERIOR -->
  <header class="menu-superior">
    <div class="nav-left">
    <a href="http://localhost:8080/" target="_blank">
      <img src="images/imagenes/Logo.png" alt="Logo Gamedom" class="logo">
    </div>
    <div class="nav-right">
      <a href="biblioteca.php" class="nav-item">Biblioteca</a>
      <a href="comunidad.php" class="nav-item">Comunidad</a>
      <a href="torneos.php" class="nav-item">Torneos</a>
      <?php if (isset($_SESSION['usuario'])): ?>
        <a href="perfil.php" class="nav-item">Perfil</a>
      <?php else: ?>
        <a href="login.html" class="nav-item">Iniciar Sesión</a>
      <?php endif; ?>
      <div class="dropdown">
        <span class="dropdown-toggle" data-text="idiomas">Idiomas ▼</span>
        <ul class="dropdown-menu">
                <li><a href="#" onclick="changeLanguage('es')"><img src="images/Banderas/España.png" alt="Español">Español</a></li>
                <li><a href="#" onclick="changeLanguage('en')"><img src="images/Banderas/Inglés.png" alt="Inglés">English</a></li>
                <li><a href="#" onclick="changeLanguage('fr')"><img src="images/Banderas/Francia.png" alt="Francés">Français</a></li>
                <li><a href="#" onclick="changeLanguage('de')"><img src="images/Banderas/Alemania.png" alt="Alemán">Deutsch</a></li>
                <li><a href="#" onclick="changeLanguage('it')"><img src="images/Banderas/Italia.png" alt="Italiano">Italiano</a></li>
                <li><a href="#" onclick="changeLanguage('pt')"><img src="images/Banderas/Portugal.png" alt="Portugués">Português</a></li>
                <li><a href="#" onclick="changeLanguage('ru')"><img src="images/Banderas/Ruso.png" alt="Ruso">Русский </a></li>
                <li><a href="#" onclick="changeLanguage('cn')"><img src="images/Banderas/China.png" alt="Chino">中文 </a></li>
                <li><a href="#" onclick="changeLanguage('jp')"><img src="images/Banderas/Japón.png" alt="Japonés">日本語 </a></li>
                <li><a href="#" onclick="changeLanguage('kr')"><img src="images/Banderas/Corea del sur.png" alt="Coreano">한국어 </a></li>
                <li><a href="#" onclick="changeLanguage('sa')"><img src="images/Banderas/Arabia Saudí.png" alt="Árabe"> العربية</a></li>
                <li><a href="#" onclick="changeLanguage('in')"><img src="images/Banderas/India.png" alt="Hindi">हिन्दी </a></li>
                <li><a href="#" onclick="changeLanguage('tr')"><img src="images/Banderas/Turquía.png" alt="Turco">Türkçe</a></li>
                <li><a href="#" onclick="changeLanguage('nl')"><img src="images/Banderas/Países Bajos.png" alt="Holandés">Nederlands</a></li>
                <li><a href="#" onclick="changeLanguage('se')"><img src="images/Banderas/Suecia.png" alt="Sueco">Svenska</a></li>
                <li><a href="#" onclick="changeLanguage('pl')"><img src="images/Banderas/Polaco.png" alt="Polaco">Polski</a></li>
                <li><a href="#" onclick="changeLanguage('gr')"><img src="images/Banderas/Grecia.png" alt="Griego">Ελληνικά</a></li>
                <li><a href="#" onclick="changeLanguage('il')"><img src="images/Banderas/Hebreo.png" alt="Hebreo">עברית </a></li>
                <li><a href="#" onclick="changeLanguage('fi')"><img src="images/Banderas/Finlandés.png" alt="Finlandés">Suomi</a></li>
                <li><a href="#" onclick="changeLanguage('dk')"><img src="images/Banderas/Danés.png" alt="Danés">Dansk</a></li>
                <li><a href="#" onclick="changeLanguage('hu')"><img src="images/Banderas/hungría.png" alt="Húngaro">Magyar</a></li>
                <li><a href="#" onclick="changeLanguage('cz')"><img src="images/Banderas/Checo.png" alt="Checo">Čeština</a></li>
                <li><a href="#" onclick="changeLanguage('ro')"><img src="images/Banderas/Rumano.png" alt="Rumano">Română </a></li>
                <li><a href="#" onclick="changeLanguage('bg')"><img src="images/Banderas/Bulgaro.png" alt="Búlgaro">Български </a></li>
                <li><a href="#" onclick="changeLanguage('ua')"><img src="images/Banderas/Ucraniano.png" alt="Ucraniano">Українська </a></li>
                <li><a href="#" onclick="changeLanguage('th')"><img src="images/Banderas/Tailandés.png" alt="Tailandés">ไทย </a></li>
                <li><a href="#" onclick="changeLanguage('id')"><img src="images/Banderas/Indonesia.png" alt="Indonesia">Bahasa Indonesia</a></li>
                <li><a href="#" onclick="changeLanguage('vn')"><img src="images/Banderas/Vietnamita.png" alt="Vietnamita">Tiếng Việt</a></li>
                <li><a href="#" onclick="changeLanguage('ir')"><img src="images/Banderas/Persa.png" alt="Persa"> فارسی</a></li>
        </ul>
      </div>
    </div>
  </header>

  <!-- SECCIÓN: JUEGOS DESTACADOS (SLIDER) -->
  <section class="juegos-destacados">
    <h2 class="h2">Juegos Destacados</h2>
    <div class="container">
      <div class="slide">
        <!-- Ítem fijo 1: Hundir la flota -->
        <div class="item" style="background-image: url('images/Juegos/Hundir la flota.jpg');">
          <div class="content">
            <div class="name" data-text="Hundirlaflota">Hundir la flota</div>
            <div class="des" data-text="txthundirlaflota">
              ¡Zarpa hacia la estrategia definitiva!
            </div>
            <button data-text="Jugar" onclick="window.location.href='pantalla_juego.php?id=1'">Jugar</button>
          </div>
        </div>
        <!-- Ítem fijo 2: Risk -->
        <div class="item" style="background-image: url('images/Juegos/risk1.jpg');">
          <div class="content">
            <div class="name" data-text="Risk">Risk</div>
            <div class="des" data-text="txtRisk">
              ¡Prepárate para una batalla legendaria!
            </div>
            <button data-text="Jugar" onclick="window.location.href='pantalla_juego.php?id=2'">Jugar</button>
          </div>
        </div>
      </div>
      <div class="button">
        <button class="prev"><i class="fa-solid fa-arrow-left"></i></button>
        <button class="next"><i class="fa-solid fa-arrow-right"></i></button>
      </div>
    </div>
  </section>

  <!-- SECCIÓN COMPLETA DEL CATÁLOGO DE JUEGOS -->
  <section class="catalog-section">
    <!-- Título de la sección (estilo "perfecto" de bloque) -->
    <div class="catalog-title">
      <h2>Catálogo de Juegos</h2>
    </div>
    <!-- Envoltorio para dos columnas: Filtros a la izq, Tarjetas a la der -->
    <div class="catalog-wrapper">
      <!-- FILTROS A LA IZQUIERDA -->
      <aside class="filter-sidebar">
        <h3>Filtrar Juegos</h3>
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
      
      <!-- TARJETAS DE JUEGOS A LA DERECHA -->
      <div class="catalogo-juegos">
        <div class="cards-container">
          <?php if (!empty($orderedGames)): ?>
            <?php foreach ($orderedGames as $game): ?>
              <?php
                $iconoBase64 = "images/default-game.png";
                if (!empty($game['icono'])) {
                    $iconoBase64 = "data:image/jpeg;base64," . base64_encode($game['icono']);
                }
              ?>
              <div class="card">
                <img src="<?php echo $iconoBase64; ?>" alt="<?php echo htmlspecialchars($game['nombre']); ?>" class="card-img">
                <div class="card-content">
                  <h3><?php echo htmlspecialchars($game['nombre']); ?></h3>
                  <p>
                    <?php echo !empty($game['descripcion']) 
                               ? htmlspecialchars($game['descripcion'])
                               : "Descripción no disponible"; ?>
                  </p>
                  <button onclick="window.location.href='pantalla_juego.php?id=<?php echo $game['id_juego']; ?>'">
                    Jugar
                  </button>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p style="text-align:center;">No hay juegos para mostrar.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>

  <!--CARRUSEL 2 -->
  <h2 class="h22">Recomendaciones semanales</h2>
  <div class="container-carousel">
        <div class="carruseles" id="slider">
            <section class="slider-section">
                <img src="img/1.jpg">
            </section>
            <section class="slider-section">
                <img src="img/2.jpg">
            </section>
            <section class="slider-section">
                <img src="img/3.jpg">
            </section>
            <section class="slider-section">
                <img src="img/4.jpg">
            </section>
            <section class="slider-section">
                <img src="img/5.jpg">
            </section>
            <section class="slider-section">
                <img src="img/6.avif">
            </section>
            <section class="slider-section">
                <img src="img/7.jpg">
            </section>
            <section class="slider-section">
                <img src="img/8.jpg">
            </section>
            <section class="slider-section">
                <img src="img/9.jpg">
            </section>
            <section class="slider-section">
                <img src="img/10.jpg">
            </section>
        </div>
        <div class="btn-left"><i class='bx bx-chevron-left'><</i></div>
        <div class="btn-right"><i class='bx bx-chevron-right'>></i></div>
    </div>

  <!-- FOOTER -->
  <footer class="footer">
    <p data-text="cc">
      © 2025 CodeCrafters. Todos los derechos reservados. Todas las marcas registradas pertenecen a sus respectivos dueños en España y otros países.
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
