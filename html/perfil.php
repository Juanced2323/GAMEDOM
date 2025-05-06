<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.html");
    exit();
}

require_once "php/db_connect.php";
$username = $_SESSION['usuario'];

// 1) ¿Es desarrollador?
$isDeveloper = false;
$id_desarrollador = null;
$stmtDev = $conn->prepare("SELECT id_desarrollador FROM desarrolladores WHERE usuario = ?");
$stmtDev->bind_param("s", $username);
$stmtDev->execute();
$resDev = $stmtDev->get_result();
if ($rowDev = $resDev->fetch_assoc()) {
    $isDeveloper = true;
    $id_desarrollador = $rowDev['id_desarrollador'];
}
$stmtDev->close();

// 2) Crear perfil de desarrollador
if (!$isDeveloper && isset($_POST['createDeveloper'])) {
    $nombre_empresa  = trim($_POST['devName'] ?? '');
    $sitio_web       = trim($_POST['devWebsite'] ?? '');
    $descripcion_dev = trim($_POST['devDescription'] ?? '');
    $stmtIns = $conn->prepare("
        INSERT INTO desarrolladores
          (usuario, nombre_empresa, sitio_web, descripcion)
        VALUES (?,?,?,?)
    ");
    $stmtIns->bind_param("ssss",
        $username,
        $nombre_empresa,
        $sitio_web,
        $descripcion_dev
    );
    $stmtIns->execute();
    $id_desarrollador = $conn->insert_id;
    $stmtIns->close();
    header("Location: perfil.php");
    exit();
}

// 3) Datos básicos de usuario
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE usuario = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$userData = $stmt->get_result()->fetch_assoc();
$stmt->close();

$fechaRegistro = date("Y-m-d", strtotime($userData['fecha_registro']));
$img_src = !empty($userData['imagen'])
    ? "data:image/jpeg;base64," . base64_encode($userData['imagen'])
    : 'images/default-profile.png';

// 4) Juegos en biblioteca (favoritos)
$stmtLib = $conn->prepare("
    SELECT g.id_juego, g.nombre
      FROM favoritos f
      JOIN juegos g ON f.id_juego = g.id_juego
     WHERE f.usuario = ?
     ORDER BY g.nombre
");
$stmtLib->bind_param("s", $username);
$stmtLib->execute();
$libraryGames = $stmtLib->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtLib->close();

// 5) Logros por juego
$achievementsByGame = [];
if (!empty($libraryGames)) {
    $stmtG = $conn->prepare("
        SELECT l.id_logro, l.nombre, l.descripcion, l.imagen, ul.fecha_obtenido
          FROM logros l
          LEFT JOIN usuarios_logros ul
            ON l.id_logro = ul.id_logro AND ul.usuario = ?
         WHERE l.id_juego = ?
         ORDER BY l.nombre
    ");
    foreach ($libraryGames as $game) {
        $stmtG->bind_param("si", $username, $game['id_juego']);
        $stmtG->execute();
        $achievementsByGame[$game['id_juego']] = $stmtG->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    $stmtG->close();
}

// 6) Logros globales de la plataforma
$stmtGlob = $conn->prepare("
    SELECT l.id_logro, l.nombre, l.descripcion, l.imagen, ul.fecha_obtenido
      FROM logros l
      LEFT JOIN usuarios_logros ul
        ON l.id_logro = ul.id_logro AND ul.usuario = ?
     WHERE l.tipo = 'global'
     ORDER BY l.nombre
");
$stmtGlob->bind_param("s", $username);
$stmtGlob->execute();
$globalAchievements = $stmtGlob->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtGlob->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Perfil – GAMEDOM</title>
  <link rel="stylesheet" href="css/perfil.css">
  <link rel="stylesheet" href="css/achievement.css">
  <style>
    * { box-sizing:border-box; margin:0; padding:0; }
    body { font-family:Arial,sans-serif; background:#f0f2f5; color:#333; }
    a { color:#4e73df; text-decoration:none; }
    .navbar { background:#fff; padding:10px 20px; box-shadow:0 2px 4px rgba(0,0,0,0.1);
               display:flex; justify-content:space-between; }
    .navbar a { font-weight:bold; margin-right:15px; }
    .container { display:flex; max-width:1200px; margin:20px auto; gap:20px; padding:0 20px; }
    .sidebar { flex:1; background:#fff; border-radius:8px; padding:20px;
               box-shadow:0 2px 8px rgba(0,0,0,0.1); }
    .main { flex:2; display:flex; flex-direction:column; gap:20px; }

    .image-box { text-align:center; }
    .image-box img { width:140px; height:140px; border-radius:50%;
                     border:3px solid #4e73df; object-fit:cover; }
    .sidebar h2 { text-align:center; margin:15px 0; color:#4e73df; }
    .sidebar button, .sidebar .btn { width:100%; margin:10px 0; padding:10px;
                                     border:none; border-radius:4px; cursor:pointer;
                                     font-weight:bold; }
    .sidebar button { background:#f6c23e; color:#fff; }
    .sidebar button:hover { background:#dda20a; }
    .sidebar .btn { background:#1cc88a; color:#fff; }
    .sidebar .btn:hover { background:#17a673; }

    #profileFieldsContainer { display:none; margin-top:20px; }
    .info-group { margin-bottom:12px; }
    .info-group label { display:block; margin-bottom:4px; font-weight:bold; }
    .info-group input { width:100%; padding:8px; border:1px solid #ccc;
                        border-radius:4px; background:#f8f9fc; }

    .sidebar .btn-logout { background:#e74a3b; }
    .sidebar .btn-logout:hover { background:#c0392b; }

    .card { background:#4e73df; color:#fff; border-radius:8px; padding:20px;
            box-shadow:0 4px 8px rgba(0,0,0,0.1); position:relative; }
    .card h2 { margin-bottom:15px; }
    .card::before { content:"🎮"; position:absolute; top:-15px; left:-15px;
                     background:#2e59d9; width:40px; height:40px; border-radius:50%;
                     display:flex; align-items:center; justify-content:center;
                     font-size:1.2em; }
    .partidas-lista { display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:15px; }
    .partida-item { background:#fff; color:#333; border-radius:6px; padding:15px;
                    box-shadow:0 2px 4px rgba(0,0,0,0.1); display:flex;
                    flex-direction:column; }
    .partida-item h4 { margin-bottom:8px; color:#4e73df; }
    .partida-item .turno { margin-top:auto; font-weight:bold; }

    .friends-section { background:#fff; border-radius:8px; padding:20px;
                       box-shadow:0 2px 8px rgba(0,0,0,0.1); }
    .friends-section h2 { color:#4e73df; margin-bottom:10px; }
    .friend-item { display:flex; align-items:center; gap:10px;
                   margin-bottom:10px; }
    .friend-item img { width:48px; height:48px; border-radius:50%; object-fit:cover; }
    .friend-item span { font-weight:bold; }

    .section { background:#fff; border-radius:8px; padding:20px;
               box-shadow:0 2px 8px rgba(0,0,0,0.1); }
    .section h2 { color:#1cc88a; margin-bottom:15px; }
    .logros-grid { display:flex; flex-wrap:wrap; gap:15px; }
    .logro-item { width:150px; text-align:center; padding:10px;
                  border-radius:6px; box-shadow:0 2px 4px rgba(0,0,0,0.05);
                  background:#f8f9fc; }
    .logro-item img { width:70px; height:70px; object-fit:cover;
                      border-radius:4px; margin-bottom:6px; }
    .logro-item.pendiente img { filter:grayscale(100%); opacity:0.5; }
    .logro-item h4 { font-size:0.95em; margin-bottom:4px; }
    .logro-item small { font-size:0.75em; color:#666; }
    .developer-prompt {
      background: #fff;
      border-radius: 8px;
      padding: 15px 20px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      margin-bottom: 20px;
    }

    .developer-prompt label {
      font-weight: bold;
      cursor: pointer;
    }

    .developer-fields {
      display: none;
      margin-top: 15px;
    }

    .developer-fields .info-group {
      margin-bottom: 10px;
    }

    .developer-fields .info-group label {
      display: block;
      margin-bottom: 4px;
    }

    .developer-fields .info-group input,
    .developer-fields .info-group textarea {
      width: 100%;
      padding: 8px;
      border: 1px solid #ccc;
      border-radius: 4px;
    }

    .btn-dev-create {
      background: #1cc88a;
      color: #fff;
      border: none;
      padding: 10px 16px;
      border-radius: 4px;
      font-weight: bold;
      cursor: pointer;
      transition: background 0.3s;
    }

    .btn-dev-create:hover {
      background: #17a673;
    }

 </style>
</head>
<body>
  <header class="navbar">
    <div class="nav-left">
      <a href="index.php">Inicio</a>
      <a href="biblioteca.php">Biblioteca</a>
      <a href="comunidad.php">Comunidad</a>
      <a href="premios.php">Premios</a>
    </div>
    <div class="nav-right">
      <a href="perfil.php">Perfil</a>
    </div>
  </header>

  <div class="container">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="image-box">
        <img src="<?= $img_src ?>" alt="Foto de Perfil">
      </div>
      <h2><?= htmlspecialchars($userData['usuario']) ?></h2>
      <button id="toggleProfileBtn">Mostrar datos</button>
      <?php if ($isDeveloper): ?>
        <a href="mis_juegos.php" class="btn">Mis Juegos</a>
      <?php endif; ?>
      <a href="php/logout.php" class="btn btn-logout">Cerrar Sesión</a>

      <div id="profileFieldsContainer">
        <div class="info-group">
          <label>Correo</label>
          <input type="email" value="<?= htmlspecialchars($userData['correo']) ?>" readonly>
        </div>
        <div class="info-group">
          <label>Nombre</label>
          <input type="text" value="<?= htmlspecialchars($userData['nombre']) ?>" readonly>
        </div>
        <div class="info-group">
          <label>Apellidos</label>
          <input type="text" value="<?= htmlspecialchars($userData['apellidos']) ?>" readonly>
        </div>
        <div class="info-group">
          <label>Edad</label>
          <input type="number" value="<?= $userData['edad'] ?>" readonly>
        </div>
        <div class="info-group">
          <label>Teléfono</label>
          <input type="tel" value="<?= htmlspecialchars($userData['telefono']) ?>" readonly>
        </div>
        <div class="info-group">
          <label>Registro</label>
          <input type="text" value="<?= $fechaRegistro ?>" readonly>
        </div>
      </div>
      <?php if (!$isDeveloper): ?>
      <!-- Sección Crear Perfil Desarrollador -->
      <div class="developer-prompt">
        <label>
          <input type="checkbox" id="isDeveloperCheckbox">
          ¿Eres desarrollador?
        </label>
        <div id="developerFields" class="developer-fields">
          <form method="POST">
            <div class="info-group">
              <label for="devName">Nombre empresa / alias</label>
              <input type="text" id="devName" name="devName" required>
            </div>
            <div class="info-group">
              <label for="devWebsite">Sitio web o repositorio</label>
              <input type="url" id="devWebsite" name="devWebsite">
            </div>
            <div class="info-group">
              <label for="devDescription">Descripción breve</label>
              <textarea id="devDescription" name="devDescription"></textarea>
            </div>
            <button type="submit" name="createDeveloper" class="btn btn-dev-create">
              Crear perfil desarrollador
            </button>
          </form>
        </div>
      </div>
    <?php endif; ?>

    </aside>

    <!-- Main Content -->
    <section class="main">
      <!-- Partidas en curso -->
      <div class="card">
        <h2>Partidas en Curso</h2>
        <div id="partidas-lista" class="partidas-lista">
          <p style="color:#fff;">Cargando partidas...</p>
        </div>
      </div>

      <!-- Amigos encima de logros -->
      <div class="friends-section">
        <h2>Amigos</h2>
        <div id="amigos-lista"><p>Cargando amigos...</p></div>
        <a href="amigos.php" class="btn" style="background:#36b9cc; margin-top:10px;">Gestionar amigos</a>
      </div>

      <!-- Logros Globales -->
      <div class="section">
        <h2>Logros Globales</h2>
        <div class="logros-grid">
          <?php $found=false; foreach ($globalAchievements as $a): if (!empty($a['fecha_obtenido'])): $found=true; ?>
            <div class="logro-item">
              <img src="data:image/jpeg;base64,<?= base64_encode($a['imagen']) ?>" alt="">
              <h4><?= htmlspecialchars($a['nombre']) ?></h4>
              <small>Obtenido <?= date("Y-m-d", strtotime($a['fecha_obtenido'])) ?></small>
            </div>
          <?php endif; endforeach;
          if (!$found): ?>
            <p>No tienes logros globales aún.</p>
          <?php endif; ?>
        </div>
      </div>

      <!-- Logros por juego -->
      <?php if (!empty($libraryGames)): foreach ($libraryGames as $game):
        $rows = $achievementsByGame[$game['id_juego']] ?? [];
      ?>
        <div class="section">
          <h2><?= htmlspecialchars($game['nombre']) ?></h2>
          <div class="logros-grid">
            <?php if (empty($rows)): ?>
              <p>No hay logros para este juego.</p>
            <?php else: foreach ($rows as $a): ?>
              <div class="logro-item <?= empty($a['fecha_obtenido'])?'pendiente':'' ?>">
                <img src="data:image/jpeg;base64,<?= base64_encode($a['imagen']) ?>" alt="">
                <h4><?= htmlspecialchars($a['nombre']) ?></h4>
                <?php if (!empty($a['fecha_obtenido'])): ?>
                  <small>Obtenido <?= date("Y-m-d", strtotime($a['fecha_obtenido'])) ?></small>
                <?php endif; ?>
              </div>
            <?php endforeach; endif; ?>
          </div>
        </div>
      <?php endforeach; endif; ?>
    </section>
  </div>

  <script>
    document.getElementById('toggleProfileBtn').onclick = () => {
      const box = document.getElementById('profileFieldsContainer');
      box.style.display = box.style.display === 'block' ? 'none' : 'block';
      document.getElementById('toggleProfileBtn').textContent =
        box.style.display === 'block' ? 'Ocultar datos' : 'Mostrar datos';
    };

    document.addEventListener('DOMContentLoaded', () => {
      fetch('php/obtener_partidas.php')
        .then(r=>r.json())
        .then(data=>{
          const c = document.getElementById('partidas-lista');
          c.innerHTML = '';
          if (!data.length) {
            c.innerHTML = '<p style="color:#fff;">No hay partidas activas.</p>';
            return;
          }
          data.forEach(p=>{
            const d = document.createElement('div');
            d.className = 'partida-item';
            d.innerHTML = `
              <h4>${p.nombre_juego}</h4>
              <p>ID: ${p.partida_id}</p>
              <p class="turno">${p.es_tu_turno==='Sí'?'🎯 Tu turno':'⏳ Esperando'}</p>
            `;
            c.appendChild(d);
          });
        })
        .catch(_=>{
          document.getElementById('partidas-lista').innerHTML = '<p style="color:#fff;">Error al cargar.</p>';
        });

      fetch('php/obtener_amigos.php')
        .then(r=>r.json())
        .then(data=>{
          const c = document.getElementById('amigos-lista');
          c.innerHTML = '';
          if (!data.length) return c.innerHTML = '<p>No tienes amigos.</p>';
          data.forEach(a=>{
            const d = document.createElement('div');
            d.className = 'friend-item';
            d.innerHTML = `<img src="${a.imagen}" alt=""><span>${a.usuario}</span>`;
            c.appendChild(d);
          });
        })
        .catch(_=>{
          document.getElementById('amigos-lista').innerHTML = '<p>Error al cargar amigos.</p>';
        });
    });
  </script>
  <script>
    document.getElementById('isDeveloperCheckbox')?.addEventListener('change', function(){
      document.getElementById('developerFields').style.display =
        this.checked ? 'block' : 'none';
    });
  </script>

  <script>
    // Pasar el nombre de usuario desde PHP a JS
    window.USUARIO_ID = "<?php echo htmlspecialchars($username); ?>";
  </script>
  <script src="js/partidas.js"></script>
  <script src="js/amigosperfil.js"></script>


</body>
</html>
