<?php
session_start();
$activePage = basename($_SERVER['PHP_SELF'], ".php");

require_once "php/db_connect.php";
require_once "php/recommendations.php";

// Si el usuario no está logueado, mostramos la página de acceso restringido
if (!isset($_SESSION['usuario'])) {
    $conn->close();
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
      <meta charset="UTF-8">
      <title>Comunidad - GAMEDOM</title>
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <!-- CSS principal y para comunidad y chat -->
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
          <a href="index.php" class="nav-item <?php echo ($activePage === 'index') ? 'active' : ''; ?>">Inicio</a>
          <a href="biblioteca.php" class="nav-item <?php echo ($activePage === 'biblioteca') ? 'active' : ''; ?>">Biblioteca</a>
          <a href="comunidad.php" class="nav-item <?php echo ($activePage === 'comunidad') ? 'active' : ''; ?>">Comunidad</a>
          <a href="premios.php" class="nav-item <?php echo ($activePage === 'premios') ? 'active' : ''; ?>">Premios</a>
          <a href="login.html" class="nav-item">Iniciar Sesión</a>
        </div>
      </div>

      <main>
        <div class="restricted-access">
          <h2>Acceso Restringido</h2>
          <p>Esta sección está disponible solo para usuarios registrados.</p>
          <a href="login.html" class="btn-acceso">Iniciar Sesión</a>
        </div>
      </main>

      <!-- Chat en Vivo (accesible incluso para usuarios no logueados, si lo prefieres) -->
      <div id="chat-container" class="chat-container">
        <h2>Chat en Vivo</h2>
        <div id="chat-box" class="chat-box"></div>
        <div id="chat-input-container">
          <input type="text" id="chat-input" placeholder="Escribe un mensaje..." autofocus>
          <button>Enviar</button>
        </div> 
      </div>

      <!-- Scripts -->
      <script src="js/socket.io.js"></script>
      <script src="js/chat.js"></script>
    </body>
    </html>
    <?php
    exit;
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Comunidad - GAMEDOM</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <!-- CSS principal, del chat y de comunidad -->
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
      <a href="index.php" class="nav-item <?php echo ($activePage === 'index') ? 'active' : ''; ?>">Inicio</a>
      <a href="biblioteca.php" class="nav-item <?php echo ($activePage === 'biblioteca') ? 'active' : ''; ?>">Biblioteca</a>
      <a href="comunidad.php" class="nav-item <?php echo ($activePage === 'comunidad') ? 'active' : ''; ?>">Comunidad</a>
      <a href="premios.php" class="nav-item <?php echo ($activePage === 'premios') ? 'active' : ''; ?>">Premios</a>
      <a href="perfil.php" class="nav-item <?php echo ($activePage === 'perfil') ? 'active' : ''; ?>">Perfil</a>
    </div>
  </div>

  <main>
    <section class="community-section">
      <h2>Comunidad</h2>
      <p>Bienvenido a la sección Comunidad. Aquí puedes interactuar con otros usuarios a través del chat en vivo.</p>

      <!-- Sección del Chat en Vivo integrada en la página -->
      <div id="chat-container" class="chat-container">
        <h2>Chat en Vivo</h2>
        <div id="chat-box" class="chat-box"></div>
        <div id="chat-input-container">
          <input type="text" id="chat-input" placeholder="Escribe un mensaje..." autofocus>
          <button>Enviar</button>
        </div>
      </div>

      <!-- Puedes agregar aquí otros contenidos propios de la comunidad -->
    </section>
  </main>

  <footer class="footer">
    <p>
      © 2025 CodeCrafters. Todos los derechos reservados.  
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

  <!-- Scripts -->
  <script src="js/socket.io.js"></script>
  <script src="js/chat.js"></script>
</body>
</html>
