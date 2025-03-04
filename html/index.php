<?php
session_start();  // Inicia la sesión

// Verifica si el usuario está autenticado (por ejemplo, si existe una variable de sesión "usuario")
if (!isset($_SESSION['usuario'])) {
    // Si no hay usuario autenticado, redirige a login
    header("Location: login.html");
    exit();
}

// Si llega hasta aquí, el usuario está autenticado
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Sitio - Página Principal</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <h1>Bienvenido a la Página Principal</h1>
    <p>Aquí va el contenido restringido del sitio.</p>
    <!-- Opcional: un botón para cerrar sesión -->
    <a href="php/logout.php">Cerrar Sesión</a>
</body>
</html>
