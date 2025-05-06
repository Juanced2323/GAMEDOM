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
  <title>GAMEDOM – Gestión de Amigos</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">

  <!--  estilos globales del sitio  -->
  <link rel="stylesheet" href="css/Index.css">
  <!--  si tienes estilos específicos para amigos  -->
  <link rel="stylesheet" href="css/amigos.css">
  <!--  iconos (campana, etc.)  -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"/>

  <style>
    /* ——— ajustes rápidos sólo para esta página ——— */
    body            { background:#F9DFBC; }
    .wrapper        { max-width:900px; margin:30px auto; background:#fff;
                      padding:25px; border-radius:8px;
                      box-shadow:0 4px 10px rgba(0,0,0,.1); }

    h1              { text-align:center; color:#7d110d; margin-bottom:25px; }

    section         { margin-top:35px; }
    section h2      { color:#7d110d; margin-bottom:12px; }

    #buscador-amigos,
    #destinatario   { width:100%; padding:10px; border:1px solid #ccc;
                      border-radius:6px; margin-bottom:12px; }

    button, input[type="submit"]{
      background:#f0932b; color:#fff; border:none; border-radius:6px;
      padding:10px 22px; font-weight:bold; cursor:pointer; transition:.25s;
    }
    button:hover    { background:#d77e16; }

    /* Tarjeta de amigo (mantiene ids/clases originales) */
    .amigo-item{
      display:flex; align-items:center; gap:10px;
      background:#F9DFBC; padding:10px; border-radius:8px;
      box-shadow:0 2px 4px rgba(0,0,0,.1); margin-bottom:10px; }
    .amigo-item img{ width:48px;height:48px;border-radius:50%;object-fit:cover; }
    .amigo-item span{ font-weight:bold; color:#7d110d; }
  </style>
</head>
<body>

<!--─────────  MENÚ SUPERIOR  ─────────-->
<header class="menu-superior">
  <div class="nav-left">
    <img src="images/imagenes/Logo.png" alt="Logo Gamedom" class="logo">
  </div>
  <div class="nav-right">
    <a href="index.php"      class="nav-item">Inicio</a>
    <a href="biblioteca.php" class="nav-item">Biblioteca</a>
    <a href="comunidad.php"  class="nav-item">Comunidad</a>
    <a href="premios.php"    class="nav-item">Premios</a>
    <a href="perfil.php"     class="nav-item">Perfil</a>
  </div>
</header>

<!--─────────  CONTENIDO  ─────────-->
<main>
  <div class="wrapper">
    <h1>Gestión&nbsp;de&nbsp;Amigos</h1>

    <!-- Sección 1: Ver / Buscar / Eliminar -->
    <section>
      <h2>Mis Amigos</h2>
      <input type="text" id="buscador-amigos" placeholder="Buscar amigos…">
      <form id="form-eliminar-amigos">
        <div id="lista-amigos">
          <p>Cargando amigos…</p>
        </div>
        <button type="submit" style="margin-top:12px;">Eliminar seleccionados</button>
      </form>
    </section>

    <!-- Sección 2: Enviar solicitud -->
    <section>
      <h2>Enviar Solicitud</h2>
      <form id="form-solicitud">
        <input type="text" name="destinatario" id="destinatario"
               placeholder="Nombre de usuario" required>
        <button type="submit">Enviar</button>
      </form>
      <div id="mensaje-solicitud" style="margin-top:10px;"></div>
    </section>

    <!-- Sección 3: Solicitudes recibidas -->
    <section>
      <h2>Solicitudes Recibidas</h2>
      <div id="lista-solicitudes">
        <p>Cargando solicitudes…</p>
      </div>
    </section>
  </div>
</main>

<!--─────────  FOOTER  ─────────-->
<footer class="footer">
  <p>© 2025 GAMEDOM. Todos los derechos reservados.</p>
  <nav>
    <a href="index.php">Inicio</a> |
    <a href="biblioteca.php">Biblioteca</a> |
    <a href="comunidad.php">Comunidad</a> |
    <a href="premios.php">Premios</a> |
    <a href="perfil.php">Perfil</a>
  </nav>
</footer>

<!-- tu lógica JS sigue intacta -->
<script src="js/amigos.js"></script>
</body>
</html>
