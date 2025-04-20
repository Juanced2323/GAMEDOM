<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.html");
    exit();
}
$usuario_actual = $_SESSION['usuario'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gestión de Amigos</title>
  <link rel="stylesheet" href="css/amigos.css"> <!-- si tus compas lo manejan -->

  <style>
    /* Estilos para amigos */
    .amigo-item {
    display: flex;
    align-items: center;
    gap: 10px;
    background-color: rgba(255, 255, 255, 0.05);
    padding: 10px;
    margin-bottom: 10px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .amigo-item img {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
    }

    .amigo-item span {
    font-weight: bold;
    color: white;
    }
  </style>
</head>
<body>
  <h1>Gestión de Amigos</h1>

  <!-- Sección 1: Ver y buscar amigos -->
  <section>
    <h2>Mis Amigos</h2>
    <input type="text" id="buscador-amigos" placeholder="Buscar amigos...">
    <div id="lista-amigos">
      <p>Cargando amigos...</p>
    </div>
  </section>

  <!-- Sección 2: Enviar solicitud de amistad -->
  <section>
    <h2>Enviar Solicitud</h2>
    <form id="form-solicitud">
      <input type="text" name="destinatario" id="destinatario" placeholder="Nombre de usuario" required>
      <button type="submit">Enviar</button>
    </form>
    <div id="mensaje-solicitud"></div>
  </section>

  <!-- Sección 3: Solicitudes recibidas -->
  <section>
    <h2>Solicitudes Recibidas</h2>
    <div id="lista-solicitudes">
      <p>Cargando solicitudes...</p>
    </div>
  </section>

  <script src="js/amigos.js"></script>
</body>
</html>
