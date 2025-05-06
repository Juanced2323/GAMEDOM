<?php
session_start();
require_once "php/db_connect.php";

if (!isset($_GET["id"])) {
  header("Location: torneos.php");
  exit;
}

$id = intval($_GET["id"]);
$query = "
  SELECT t.*, j.nombre AS nombre_juego
  FROM torneos t
  JOIN juegos j ON t.id_juego = j.id_juego
  WHERE t.id_torneo = $id
";
$result = $conn->query($query);

if ($result->num_rows === 0) {
  echo "<p>Torneo no encontrado</p>";
  exit;
}

$torneo = $result->fetch_assoc();

// Descripción automática solo si no hay descripción en DB
$descripciones = [
  'HundirLaFlota' => 'Compite en una épica batalla naval por turnos. Demuestra tu puntería y estrategia para hundir la flota enemiga antes que te hundan a ti.',
  'Risk' => 'Toma el control del mundo en este torneo de conquista estratégica. Solo los mejores líderes dominarán el mapa global.'
];
$descripcion_auto = $descripciones[$torneo['nombre_juego']] ?? 'Participa en este torneo y demuestra tus habilidades.';

// Premios por defecto si están vacíos
$premio1 = $torneo['premio_1'] ?: '100 puntos Elo + Medalla Dorada + Acceso anticipado a nuevos juegos';
$premio2 = $torneo['premio_2'] ?: '80 puntos Elo + Skin exclusiva';
$premio3 = $torneo['premio_3'] ?: '50 puntos Elo + Perfil destacado por una semana';

// Verificar si el usuario cumple con el Elo mínimo en el juego correspondiente
$puede_participar = false;
$elo_usuario = 0;

if (isset($_SESSION['usuario'])) {
  $usuario = $_SESSION['usuario'];
  $id_juego = $torneo['id_juego'];

  $stmt = $conn->prepare("
    SELECT elo 
    FROM ranking 
    WHERE usuario = ? AND id_juego = ?
  ");
  $stmt->bind_param("si", $usuario, $id_juego);
  $stmt->execute();
  $stmt->bind_result($elo_usuario);
  if ($stmt->fetch()) {
    if ($elo_usuario >= $torneo['elo_minimo']) {
      $puede_participar = true;
    }
  }
  $stmt->close();
}

// Preparar URL de redirección al juego
$nombreJuego = $torneo['nombre_juego'];
$carpetaJuego = str_replace(' ', '', $nombreJuego);
$url_juego = "games/$carpetaJuego/index.html";
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title><?php echo htmlspecialchars($torneo['nombre_torneo']); ?> - Detalles</title>
  <link rel="stylesheet" href="css/index.css">
  <link rel="stylesheet" href="css/detalle_torneos.css">
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

    <div class="detalle-container">
        <h1><?php echo htmlspecialchars($torneo['nombre_torneo']); ?></h1>
        <p><strong>Juego:</strong> <?php echo htmlspecialchars($torneo['nombre_juego']); ?></p>
        <p><strong>Fechas:</strong> <?php echo $torneo['fecha_inicio']; ?> al <?php echo $torneo['fecha_fin']; ?></p>

        <h3>Descripción del torneo</h3>
        <p>
        <?php 
            echo $torneo['descripcion'] 
            ? nl2br(htmlspecialchars($torneo['descripcion'])) 
            : $descripcion_auto; 
        ?>
        </p>

        <h3>Premios</h3>
        <ul>
        <li><strong>1° Puesto:</strong> <?php echo $premio1; ?></li>
        <li><strong>2° Puesto:</strong> <?php echo $premio2; ?></li>
        <li><strong>3° Puesto:</strong> <?php echo $premio3; ?></li>
        </ul>

        <h3>Requisitos de participación</h3>
        <p>Debes tener al menos <strong><?php echo $torneo['elo_minimo']; ?> puntos Elo</strong> en este juego.</p>
        <p>Tu Elo actual: <strong><?php echo $elo_usuario; ?></strong></p>

        <?php if ($puede_participar): ?>
          <form method="POST" action="php/inscribirse_torneo.php">
            <input type="hidden" name="id_torneo" value="<?php echo $torneo['id_torneo']; ?>">
            <button type="submit" class="btn-confirmar">Confirmar Participación</button>
          </form>
        <?php else: ?>
          <button class="btn-confirmar btn-disabled" disabled>No cumples con el puntaje requerido</button>
        <?php endif; ?>
    </div>

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
