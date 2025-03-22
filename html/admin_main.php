<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Panel de Administración - GAMEDOM</title>
  <link rel="stylesheet" href="css/admin_main.css">
</head>
<body>
  <header>
    <h1>Panel de Administración</h1>
  </header>
  <nav>
    <ul>
      <li><a href="admin_juegos.php">Administración de Juegos</a></li>
      <li><a href="admin_categorias.php">Administración de Categorías</a></li>
    </ul>
  </nav>
  <footer>
    <p>&copy; <?php echo date("Y"); ?> GAMEDOM</p>
  </footer>
</body>
</html>
