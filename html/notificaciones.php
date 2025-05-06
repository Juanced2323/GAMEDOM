<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: login.html");
  exit();
}
require_once "php/db_connect.php";
$user = $_SESSION['usuario'];

// 1) Partidas pendientes
$stmtP = $conn->prepare("
  SELECT 
    p.id AS partida_id,
    j.nombre AS nombre_juego,
    CASE WHEN p.turno_actual_usuario_id = ? THEN 'Sí' ELSE 'No' END AS es_tu_turno,
    p.fecha_creacion
  FROM partidas_usuarios pu
  JOIN partidas p ON pu.partida_id = p.id
  JOIN juegos j    ON p.juego_id   = j.id_juego
  WHERE pu.usuario_id = ?
    AND p.estado = 'en_progreso'
  ORDER BY p.fecha_creacion DESC
");
$stmtP->bind_param("ss", $user, $user);
$stmtP->execute();
$partidas = $stmtP->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtP->close();

// 2) Solicitudes de amistad
$stmtS = $conn->prepare("
  SELECT 
    u.usuario,
    CONCAT(u.nombre,' ',u.apellidos) AS nombre_completo,
    a.fecha_solicitud
  FROM amistades a
  JOIN usuarios u ON u.usuario = a.solicitante
  WHERE a.receptor = ?
    AND a.estado = 'pendiente'
  ORDER BY a.fecha_solicitud DESC
");
$stmtS->bind_param("s", $user);
$stmtS->execute();
$solicitudes = $stmtS->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtS->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Notificaciones – GAMEDOM</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="css/Index.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"/>
  <style>
    .section {
      background: #fff;
      border-radius: 8px;
      padding: 20px;
      margin: 20px auto;
      max-width: 900px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .section h2 {
      color: #7d110d;
      margin-bottom: 15px;
      font-size: 1.6rem;
    }
    .notifications-list {
      list-style: none;
      padding: 0;
      margin: 0;
    }
    .notification-item {
      padding: 12px 0;
      border-bottom: 1px solid #eee;
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
    }
    .notification-item:last-child { border-bottom: none; }
    .notification-item h4 { margin: 0; color: #4e73df; }
    .notification-item small { color: #666; font-size: 0.8rem; }
    .empty-message { color: #555; font-style: italic; }
    .btn-action {
      padding: 6px 10px;
      font-size: 0.9rem;
      border: none;
      border-radius: 4px;
      color: #fff;
      cursor: pointer;
      margin-left: 5px;
    }
    .btn-accept { background: #1cc88a; }
    .btn-reject { background: #e74a3b; }
  </style>
</head>
<body>
  <!-- MENÚ -->
  <header class="menu-superior">
    <div class="nav-left">
      <img src="images/imagenes/Logo.png" alt="Logo Gamedom" class="logo">
    </div>
    <div class="nav-right">
      <a href="biblioteca.php" class="nav-item">Biblioteca</a>
      <a href="comunidad.php" class="nav-item">Comunidad</a>
      <a href="torneos.php" class="nav-item">Torneos</a>
      <a href="perfil.php" class="nav-item">Perfil</a>
      <div class="dropdown">
        <span class="dropdown-toggle">Idiomas ▼</span>
        <ul class="dropdown-menu">
          <li><a href="#" onclick="changeLanguage('es')">
            <img src="images/Banderas/España.png" alt="Español"> Español
          </a></li>
          <li><a href="#" onclick="changeLanguage('en')">
            <img src="images/Banderas/Inglés.png" alt="Inglés"> English
          </a></li>
        </ul>
      </div>
    </div>
  </header>

  <main>
    <!-- PARTIDAS -->
    <div class="section">
      <h2><i class="fa fa-gamepad"></i> Partidas Pendientes</h2>
      <?php if (empty($partidas)): ?>
        <p class="empty-message">No tienes partidas pendientes.</p>
      <?php else: ?>
        <ul class="notifications-list">
          <?php foreach ($partidas as $p): ?>
            <li class="notification-item">
              <div>
                <h4><?= htmlspecialchars($p['nombre_juego']) ?></h4>
                <small>ID: <?= $p['partida_id'] ?> &bull; Creada: <?= date('Y-m-d', strtotime($p['fecha_creacion'])) ?></small>
              </div>
              <div>
                <small>
                  Turno tuyo: 
                  <?php if ($p['es_tu_turno'] === 'Sí'): ?>
                    <span style="color:#1cc88a;font-weight:bold;">Sí</span>
                  <?php else: ?>
                    <span style="color:#e74a3b;">No</span>
                  <?php endif; ?>
                </small>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>

    <!-- SOLICITUDES -->
    <div class="section">
      <h2><i class="fa fa-user-friends"></i> Solicitudes de Amistad</h2>
      <?php if (empty($solicitudes)): ?>
        <p class="empty-message">No tienes solicitudes de amistad pendientes.</p>
      <?php else: ?>
        <ul id="solicitudes-lista" class="notifications-list">
          <?php foreach ($solicitudes as $s): ?>
            <li class="notification-item">
              <div>
                <h4><?= htmlspecialchars($s['nombre_completo']) ?> (<?= htmlspecialchars($s['usuario']) ?>)</h4>
                <small>Enviada: <?= date('Y-m-d H:i', strtotime($s['fecha_solicitud'])) ?></small>
              </div>
              <div>
                <button class="btn-action btn-accept" data-user="<?= htmlspecialchars($s['usuario']) ?>">
                  Aceptar
                </button>
                <button class="btn-action btn-reject" data-user="<?= htmlspecialchars($s['usuario']) ?>">
                  Rechazar
                </button>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
  </main>

  <!-- FOOTER -->
  <footer>
    <p>© 2025 CodeCrafters. Todos los derechos reservados.</p>
  </footer>

  <script>
    // dropdown idiomas (igual que antes)
    document.querySelector('.dropdown-toggle').onclick = () => {
      document.querySelector('.dropdown-menu').classList.toggle('show');
    };
    function changeLanguage(lang){ console.log("Idioma:", lang); }

    // Función para aceptar/rechazar
    function gestionarSolicitud(usuario, accion) {
      const fd = new FormData();
      fd.append('solicitante', usuario);
      fd.append('accion', accion);

      fetch('php/gestionar_solicitud.php', {
        method: 'POST',
        body: fd
      })
      .then(r=>r.text())
      .then(msg=>{
        alert(msg);
        // Refrescar solo la lista de solicitudes:
        cargarSolicitudes();
      })
      .catch(err=>{
        console.error("Error:", err);
        alert("Error al procesar la solicitud.");
      });
    }

    // Vincula botones tras carga
    function cargarSolicitudes() {
      // ya están en HTML inicial, simplemente re-asocia events:
      document.querySelectorAll('.btn-accept').forEach(btn=>{
        btn.onclick = ()=> gestionarSolicitud(btn.dataset.user, 'aceptada');
      });
      document.querySelectorAll('.btn-reject').forEach(btn=>{
        btn.onclick = ()=> gestionarSolicitud(btn.dataset.user, 'rechazada');
      });
    }

    // Al cargar, asocia eventos
    document.addEventListener('DOMContentLoaded', cargarSolicitudes);
  </script>
</body>
</html>
