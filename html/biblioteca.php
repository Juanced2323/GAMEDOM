<?php
session_start();
$activePage = basename($_SERVER['PHP_SELF'], ".php");
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
        <!-- NUEVO ENLACE A INICIO -->
        <a href="index.php" 
           class="nav-item <?php echo ($activePage === 'index') ? 'active' : ''; ?>">
           Inicio
        </a>

        <a href="biblioteca.php" 
           class="nav-item <?php echo ($activePage === 'biblioteca') ? 'active' : ''; ?>">
           Biblioteca
        </a>
        <a href="comunidad.php" 
           class="nav-item <?php echo ($activePage === 'comunidad') ? 'active' : ''; ?>">
           Comunidad
        </a>
        <a href="premios.php" 
           class="nav-item <?php echo ($activePage === 'premios') ? 'active' : ''; ?>">
           Premios
        </a>
      </div>
      <div class="nav-right">
        <?php if (isset($_SESSION['usuario'])): ?>
          <a href="perfil.php" 
             class="nav-item <?php echo ($activePage === 'perfil') ? 'active' : ''; ?>">
             Perfil
          </a>
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
      <!-- Contenido de la biblioteca -->
      <section class="game-catalog">
        <h2>Biblioteca de Juegos</h2>
        <p>Aquí va el contenido exclusivo para usuarios logueados.</p>
      </section>
    <?php endif; ?>
  </main>
</body>
</html>
