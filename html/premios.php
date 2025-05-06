<?php
session_start();
/* 1) Solo usuarios logeados */
if (!isset($_SESSION['usuario'])) {
    header("Location: login.html");
    exit();
}

require_once "php/db_connect.php";
$username = $_SESSION['usuario'];

/* 2) ¿El usuario es premium? */
$isPremium = false;
$stmt = $conn->prepare("SELECT 1 FROM premium_users WHERE usuario = ? LIMIT 1");
$stmt->bind_param("s", $username);
$stmt->execute();
if ($stmt->get_result()->num_rows) { $isPremium = true; }
$stmt->close();

/* 3) Si es premium cargamos las skins */
$skins = [];
if ($isPremium) {
    $res = $conn->query("SELECT id_skin,nombre,descripcion,imagen,archivo_skin,rareza
                           FROM premium_skins
                       ORDER BY FIELD(rareza,'legendaria','épica','rara','común'), nombre");
    $skins = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>GAMEDOM – Premios</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Reutilizamos estilos principales -->
  <link rel="stylesheet" href="css/Index.css">
  <link rel="stylesheet" href="css/catalogo.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    /* Candado y mensaje */
    .catalog-title i.fa-lock { margin-left:8px;color:#e74a3b; }
    .locked p               { text-align:center;font-size:1.1rem;color:#555; }

    /* Rarezas con un borde de color opcional */
    .card[data-rareza="legendaria"] { box-shadow:0 0 10px 2px gold;    }
    .card[data-rareza="épica"]      { box-shadow:0 0 8px  2px violet;  }
    .card[data-rareza="rara"]       { box-shadow:0 0 6px  1px deepskyblue; }
  </style>
</head>
<body>
  <!-- MENÚ SUPERIOR (estilo Index) -->
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

      <div id="notificationIcon" class="nav-item">
        <i class="fa fa-bell"></i><span id="notificationBadge"></span>
      </div>
    </div>
  </header>

  <!-- ====================  PREMIOS DE TORNEOS  ==================== -->
  <section class="catalog-section">
    <div class="catalog-title"><h2>Premios de Torneos</h2></div>
    <div class="catalog-wrapper">
      <p style="padding:20px;">Próximamente...</p>
    </div>
  </section>

  <!-- ====================  SKINS PREMIUM  ==================== -->
  <?php if (!$isPremium): ?>
    <section class="catalog-section locked">
      <div class="catalog-title">
        <h2>Skins Premium <i class="fa fa-lock"></i></h2>
      </div>
      <div class="catalog-wrapper">
        <p>Solo disponible para usuarios Premium. <br>Visita tu perfil y pulsa <strong>Hazte Premium</strong> para desbloquearlas.</p>
      </div>
    </section>
  <?php else: ?>
    <section class="catalog-section">
      <div class="catalog-title"><h2>Skins Premium</h2></div>
      <div class="catalog-wrapper">
        <div class="catalogo-juegos" style="flex:1;">
          <div class="cards-container">
            <?php if (!$skins): ?>
              <p style="padding:20px;">Aún no hay skins disponibles.</p>
            <?php else:
              foreach ($skins as $s):
                /* Seleccionamos la imagen: blob o ruta estática */
                $img = $s['imagen']
                       ? 'data:image/jpeg;base64,'.base64_encode($s['imagen'])
                       : $s['archivo_skin'];
            ?>
              <div class="card" data-rareza="<?= htmlspecialchars($s['rareza']) ?>">
                <img src="<?= htmlspecialchars($img) ?>" alt="Skin <?= htmlspecialchars($s['nombre']) ?>" class="card-img">
                <div class="card-content">
                  <h3><?= htmlspecialchars($s['nombre']) ?></h3>
                  <p><?= htmlspecialchars($s['descripcion']) ?></p>
                  <!-- Botón ficticio (a futuro: equipar / comprar) -->
                  <button>Seleccionar</button>
                </div>
              </div>
            <?php endforeach; endif; ?>
          </div>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <!-- FOOTER -->
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

  <!-- ========== Script de notificaciones (mismo que en Index) ========== -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const badge = document.getElementById('notificationBadge');
      const icon  = document.getElementById('notificationIcon');
      if (!icon) return;

      Promise.all([
        fetch('php/obtener_partidas.php').then(r=>r.json()).catch(()=>[]),
        fetch('php/obtener_solicitudes.php').then(r=>r.json()).catch(()=>[])
      ]).then(([partidas, solicitudes])=>{
        const total = partidas.length + (Array.isArray(solicitudes)?solicitudes.length:0);
        if (total){
          badge.textContent = total;
          badge.style.display = 'inline-block';
          badge.classList.add('pulse');
          icon .classList.add('pulse');
          icon.title =
            (partidas.length   ? `${partidas.length} partida(s) pendiente(s)\n` : '') +
            (solicitudes.length? `${solicitudes.length} solicitud(es) de amistad` : '');
        }
      });

      icon.addEventListener('click', ()=>window.location.href='notificaciones.php');
    });
  </script>
</body>
</html>
