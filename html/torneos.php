<?php
session_start();
require_once "php/db_connect.php";

$activePage = basename($_SERVER['PHP_SELF'], ".php");

$hoy = date("Y-m-d");

$queryActivos = "
    SELECT t.*, j.nombre AS nombre_juego
    FROM torneos t
    JOIN juegos j ON t.id_juego = j.id_juego
    WHERE t.fecha_inicio <= '$hoy' AND t.fecha_fin >= '$hoy'
    ORDER BY t.fecha_fin ASC
";
$resultActivos = $conn->query($queryActivos);

$queryProximos = "
    SELECT t.*, j.nombre AS nombre_juego
    FROM torneos t
    JOIN juegos j ON t.id_juego = j.id_juego
    WHERE t.fecha_inicio > '$hoy'
    ORDER BY t.fecha_inicio ASC
";
$resultProximos = $conn->query($queryProximos);

// Torneos finalizados hace 5 días exactos
$queryFinalizados = "
    SELECT t.*, j.nombre AS nombre_juego
    FROM torneos t
    JOIN juegos j ON t.id_juego = j.id_juego
    WHERE t.fecha_fin = DATE_SUB(CURDATE(), INTERVAL 5 DAY)
    ORDER BY t.fecha_fin DESC
";
$resultFinalizados = $conn->query($queryFinalizados);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Torneos - GAMEDOM</title>
  <link rel="stylesheet" href="css/torneos.css">
</head>
<body>
  <header>
    <nav class="navbar">
      <div class="nav-left">
        <a href="index.php" class="nav-item <?php echo ($activePage === 'index') ? 'active' : ''; ?>">Inicio</a>
        <a href="biblioteca.php" class="nav-item <?php echo ($activePage === 'biblioteca') ? 'active' : ''; ?>">Biblioteca</a>
        <a href="comunidad.php" class="nav-item <?php echo ($activePage === 'comunidad') ? 'active' : ''; ?>">Comunidad</a>
        <a href="torneos.php" class="nav-item <?php echo ($activePage === 'torneos') ? 'active' : ''; ?>">Torneos</a>
      </div>
      <div class="nav-right">
        <?php if (isset($_SESSION['usuario'])): ?>
          <a href="perfil.php" class="nav-item">Perfil</a>
        <?php else: ?>
          <a href="login.html" class="nav-item">Iniciar sesión</a>
        <?php endif; ?>
      </div>
    </nav>
  </header>

  <main class="main-content">
    <!-- Torneos Activos -->
    <section class="torneos-section">
      <h2>Torneos Activos</h2>
      <?php if ($resultActivos->num_rows > 0): ?>
        <?php while($torneo = $resultActivos->fetch_assoc()): ?>
          <div class="torneo-card">
            <h3><?php echo htmlspecialchars($torneo['nombre_torneo']); ?></h3>
            <p><strong>Fecha:</strong> Del <?php echo $torneo['fecha_inicio']; ?> al <?php echo $torneo['fecha_fin']; ?></p>
            <p><strong>Juego:</strong> <?php echo htmlspecialchars($torneo['nombre_juego']); ?></p>
            <a class="participar-btn" href="torneo_detalle.php?id=<?php echo $torneo['id_torneo']; ?>">Participar</a>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p>No hay torneos activos en este momento.</p>
      <?php endif; ?>
    </section>

    <!-- Próximos Torneos -->
    <section class="torneos-section">
      <h2>Próximos Torneos</h2>
      <?php if ($resultProximos->num_rows > 0): ?>
        <?php while($torneo = $resultProximos->fetch_assoc()): ?>
          <div class="torneo-card">
            <h3><?php echo htmlspecialchars($torneo['nombre_torneo']); ?></h3>
            <p><strong>Fecha:</strong> Del <?php echo $torneo['fecha_inicio']; ?> al <?php echo $torneo['fecha_fin']; ?></p>
            <p><strong>Juego:</strong> <?php echo htmlspecialchars($torneo['nombre_juego']); ?></p>
            <button class="btn-disabled" disabled>Próximamente</button>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p>No hay torneos próximos por ahora.</p>
      <?php endif; ?>
    </section>

    <!-- Torneos Finalizados -->
    <section class="torneos-section">
      <h2>Torneos Finalizados</h2>
      <?php if ($resultFinalizados->num_rows > 0): ?>
        <?php while($torneo = $resultFinalizados->fetch_assoc()): ?>
          <div class="torneo-card finalizado">
            <h3><?php echo htmlspecialchars($torneo['nombre_torneo']); ?></h3>
            <p><strong>Juego:</strong> <?php echo htmlspecialchars($torneo['nombre_juego']); ?></p>
            <p><strong>Finalizado el:</strong> <?php echo $torneo['fecha_fin']; ?></p>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p>No hay torneos finalizados recientes.</p>
      <?php endif; ?>
    </section>
  </main>

  <footer class="footer">
    <p data-text="footer">
      © 2025 CodeCrafters. Todos los derechos reservados. Todas las marcas registradas pertenecen a sus respectivos dueños en EE. UU. y otros países.<br>
      Todos los precios incluyen IVA (donde sea aplicable).
    </p>
    <nav>
      <a href="Política de privacidad.html" data-text="privacy">Política de Privacidad</a> |
      <a href="Información legal.html" data-text="legal">Información legal</a> |
      <a href="Cookies.html" data-text="cookies">Cookies</a> |
      <a href="A cerca de.html" data-text="about">A cerca de CodeCrafters</a>
    </nav>
  </footer>
</body>
</html>
