<?php
session_start();

// Datos de ejemplo
$gameName = "Hundir la Flota";
$gameIcon = "images/juego-1.jpeg";
$gameDescription = "Disfruta de una reinvención del clásico juego de estrategia naval...";
$gameScreenshots = [
  "images/screenshot1.jpg",
  "images/screenshot2.jpg",
];
$rankingPlayers = "1. JugadorA<br>2. JugadorB<br>3. JugadorC";
$tournamentInfo = "Torneo activo: Torneo de Verano - Final el 15/07/2025";
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo $gameName; ?> - Información del Juego</title>
  <link rel="stylesheet" href="css/pantalla_juego.css">
</head>
<body>
  <header>
    <nav class="navbar">
      <div class="nav-left">
        <a href="index.php" class="nav-item">Inicio</a>
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
    <div class="game-info-container">
      <div class="game-header">
        <img src="<?php echo $gameIcon; ?>" alt="<?php echo $gameName; ?>" class="game-icon">
        <div class="game-name"><?php echo $gameName; ?></div>
      </div>
      <div class="game-description">
        <?php echo $gameDescription; ?>
      </div>
      <div class="screenshots">
        <?php foreach($gameScreenshots as $screenshot): ?>
          <img src="<?php echo $screenshot; ?>" alt="Captura de pantalla">
        <?php endforeach; ?>
      </div>
      <div class="ranking">
        <strong>Ranking de Jugadores:</strong><br>
        <?php echo $rankingPlayers; ?>
      </div>
      <div class="tournaments">
        <strong>Torneos Activos:</strong><br>
        <?php echo $tournamentInfo; ?>
      </div>
      <!-- Botón que dirige al juego -->
      <button class="play-button" onclick="location.href='games/HundirLaFlota/index.html'">
        Jugar Ahora
      </button>
    </div>
  </main>
</body>
</html>
