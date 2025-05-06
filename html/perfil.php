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
$stmt = $conn->prepare("SELECT id_desarrollador FROM desarrolladores WHERE usuario = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
if ($row = $stmt->get_result()->fetch_assoc()) {
    $isDeveloper = true;
    $id_desarrollador = $row['id_desarrollador'];
}
$stmt->close();

// 2) ¿Es premium?
$isPremium = false;
$stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM premium_users WHERE usuario = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
if (($row = $stmt->get_result()->fetch_assoc()) && $row['cnt'] > 0) {
    $isPremium = true;
}
$stmt->close();

// 3) Crear perfil de desarrollador
if (!$isDeveloper && isset($_POST['createDeveloper'])) {
    $nombre_empresa  = trim($_POST['devName'] ?? '');
    $sitio_web       = trim($_POST['devWebsite'] ?? '');
    $descripcion_dev = trim($_POST['devDescription'] ?? '');
    $stmt = $conn->prepare("
      INSERT INTO desarrolladores
        (usuario, nombre_empresa, sitio_web, descripcion)
      VALUES (?,?,?,?)
    ");
    $stmt->bind_param("ssss", $username, $nombre_empresa, $sitio_web, $descripcion_dev);
    $stmt->execute();
    $stmt->close();
    header("Location: perfil.php");
    exit();
}

// 4) Hacerse premium
if (!$isPremium && isset($_POST['becomePremium'])) {
  $stmtP = $conn->prepare("
      INSERT INTO premium_users (usuario)
      VALUES (?)
  ");
  if (!$stmtP) {
      die("Error al preparar Premium: " . $conn->error);
  }
  $stmtP->bind_param("s", $username);
  $stmtP->execute();
  $stmtP->close();
  header("Location: perfil.php");
  exit();
}


// 5) Datos básicos de usuario
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE usuario = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$userData = $stmt->get_result()->fetch_assoc();
$stmt->close();

// formateo
$fechaRegistro = date("Y-m-d", strtotime($userData['fecha_registro']));
$img_src = $userData['imagen']
    ? "data:image/jpeg;base64,".base64_encode($userData['imagen'])
    : "images/default-profile.png";

// 6) Biblioteca
$stmt = $conn->prepare("
  SELECT g.id_juego, g.nombre
    FROM favoritos f
    JOIN juegos g ON f.id_juego = g.id_juego
   WHERE f.usuario = ?
   ORDER BY g.nombre
");
$stmt->bind_param("s", $username);
$stmt->execute();
$libraryGames = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// 7) Logros por juego
$achievementsByGame = [];
if ($libraryGames) {
    $stmt = $conn->prepare("
      SELECT l.id_logro, l.nombre, l.descripcion, l.imagen, ul.fecha_obtenido
        FROM logros l
   LEFT JOIN usuarios_logros ul
     ON ul.id_logro = l.id_logro AND ul.usuario = ?
       WHERE l.id_juego = ?
       ORDER BY l.nombre
    ");
    foreach ($libraryGames as $g) {
        $stmt->bind_param("si", $username, $g['id_juego']);
        $stmt->execute();
        $achievementsByGame[$g['id_juego']] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    $stmt->close();
}

// 8) Logros globales
$stmt = $conn->prepare("
  SELECT l.id_logro, l.nombre, l.descripcion, l.imagen, ul.fecha_obtenido
    FROM logros l
LEFT JOIN usuarios_logros ul
  ON ul.id_logro = l.id_logro AND ul.usuario = ?
   WHERE l.tipo = 'global'
   ORDER BY l.nombre
");
$stmt->bind_param("s", $username);
$stmt->execute();
$globalAchievements = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Perfil – GAMEDOM</title>
  <link rel="stylesheet" href="css/Index.css">
  <link rel="stylesheet" href="css/perfil.css">
  <link rel="stylesheet" href="css/achievement.css">
  <style>
    /* -- Tu CSS original -- */
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

    .image-box { text-align:center; position:relative; }
    .image-box img { width:140px; height:140px; border-radius:50%;
                     border:3px solid #4e73df; object-fit:cover; }
    .upload-form {
      margin-top:10px;
    }
    .upload-form input[type=file] {
      width:100%;
      padding:5px;
    }
    .upload-form button {
      margin-top:5px;
      width:100%;
      padding:8px;
      background:#6c5ce7;
      color:#fff;
      border:none;
      border-radius:4px;
      cursor:pointer;
    }
    .upload-form button:hover {
      background:#341f97;
    }

    .sidebar {
      text-align: center;
    }

    .avatar-box {
      position: relative;
      margin-bottom: 1rem;
    }

    .avatar {
      width: 140px;
      height: 140px;
      border-radius: 50%;
      border: 3px solid #4e73df;
      object-fit: cover;
    }

    .upload-form {
      margin-top: 0.75rem;
    }

    .upload-form input[type="file"] {
      display: block;
      margin: 0 auto 0.5rem;
    }

    .upload-form button {
      background: #6c5ce7;
      color: #fff;
      border: none;
      padding: 6px 12px;
      border-radius: 4px;
      cursor: pointer;
      font-size: 0.9rem;
    }

    .upload-form button:hover {
      background: #341f97;
    }

    .username {
      margin: 0.5rem 0 1rem;
      font-size: 1.4rem;
      color: #4e73df;
    }

    .action-buttons {
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
      margin-bottom: 1rem;
    }

    .action-buttons button,
    .action-buttons .btn {
      padding: 10px;
      border: none;
      border-radius: 4px;
      font-weight: bold;
      cursor: pointer;
      font-size: 1rem;
    }

    #toggleProfileBtn {
      background: #f6c23e;
      color: #fff;
    }

    #toggleProfileBtn:hover {
      background: #dda20a;
    }

    .btn-premium {
      background: #f0932b;
      color: #fff;
    }

    .btn-premium:hover {
      background: #ea7b13;
    }

    .btn-premium.active {
      display: inline-block;
      background: #e84393;
      cursor: default;
    }

    .btn-logout {
      background: #e74a3b;
      color: #fff;
    }

    .btn-logout:hover {
      background: #c0392b;
    }

    /* Opcional: un poco de separación antes de desarrollador */
    .developer-prompt {
      margin-top: 1.5rem;
      text-align: left;
      padding: 0 1rem;
    }

    .developer-prompt label {
      font-weight: bold;
      cursor: pointer;
    }

    .developer-fields {
      display: none;
      margin-top: 0.75rem;
    }

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
  </style>
</head>
<body>
  <header class="menu-superior">
    <div class="nav-left">
      <img src="images/imagenes/Logo.png" alt="Logo Gamedom" class="logo">
    </div>
    <div class="nav-right">
      <a href="index.php" class="nav-item">Inicio</a>
      <a href="biblioteca.php" class="nav-item">Biblioteca</a>
      <a href="comunidad.php" class="nav-item">Comunidad</a>
      <a href="premios.php" class="nav-item">Premios</a>
      <a href="perfil.php" class="nav-item">Perfil</a>
    </div>
  </header>

  <div class="profile-container">
  <aside class="profile-image-section">
      <!-- Avatar + Subida -->
      <div class="avatar-box">
        <img src="<?= $img_src ?>" alt="Foto de Perfil" class="avatar">
        <form class="upload-form" action="php/upload_profile_image.php" method="POST" enctype="multipart/form-data">
          <input type="file" name="imagen" accept=".jpg,.jpeg,.png" required>
          <button type="submit">Subir Imagen</button>
        </form>
      </div>

      <!-- Usuario -->
      <h2 class="username"><?= htmlspecialchars($userData['usuario']) ?></h2>

      <!-- BOTONES DE ACCIÓN -->
      <div class="action-buttons">
        <button id="toggleProfileBtn">Mostrar datos</button>
        <?php if ($isDeveloper): ?>
          <a href="mis_juegos.php" class="btn">Mis Juegos</a>
        <?php endif; ?>

        <form method="POST" style="display:inline;">
          <?php if (!$isPremium): ?>
            <button type="submit" name="becomePremium" class="btn btn-premium">
              Hazte Premium<br><small>3.99 $</small>
            </button>
          <?php else: ?>
            <span class="btn btn-premium active">¡Eres Premium!</span>
          <?php endif; ?>
        </form>

        <a href="php/logout.php" class="btn btn-logout">Cerrar Sesión</a>
      </div>

      <!-- DATOS OCULTOS -->
      <div id="profileFieldsContainer" style="display:none; margin-top:1rem;">
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

      <!-- PERFIL DESARROLLADOR -->
      <?php if (!$isDeveloper): ?>
      <div class="developer-prompt" style="margin-top:1.5rem;">
        <label>
          <input type="checkbox" id="isDeveloperCheckbox">
          ¿Eres desarrollador?
        </label>
        <div id="developerFields" class="developer-fields" style="display:none; margin-top:0.5rem;">
          <form method="POST">
            <div class="info-group">
              <label for="devName">Empresa / alias</label>
              <input type="text" id="devName" name="devName" required>
            </div>
            <div class="info-group">
              <label for="devWebsite">Web / repositorio</label>
              <input type="url" id="devWebsite" name="devWebsite">
            </div>
            <div class="info-group">
              <label for="devDescription">Descripción</label>
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

    <!-- MAIN -->
    <section class="profile-info-section">
      <!-- Partidas -->
      <div class="card">
        <h2>Partidas en Curso</h2>
        <div id="partidas-lista" class="partidas-lista">
          <p style="color:#fff;">Cargando partidas…</p>
        </div>
      </div>

      <!-- Amigos -->
      <div class="friends-section">
        <h2>Amigos</h2>
        <div id="amigos-lista"><p>Cargando amigos…</p></div>
        <a href="amigos.php" class="btn" style="background:#36b9cc; margin-top:0.5rem;">
          Gestionar amigos
        </a>
      </div>

      <!-- Logros globales -->
      <div class="section">
        <h2>Logros Globales</h2>
        <div class="logros-grid">
          <?php $got=false; ?>
          <?php foreach ($globalAchievements as $a): ?>
            <?php if (!empty($a['fecha_obtenido'])): $got=true; ?>
              <div class="logro-item">
                <img src="data:image/jpeg;base64,<?= base64_encode($a['imagen']) ?>" alt="">
                <h4><?= htmlspecialchars($a['nombre']) ?></h4>
                <small>Obtenido <?= date("Y-m-d",strtotime($a['fecha_obtenido'])) ?></small>
              </div>
            <?php endif; ?>
          <?php endforeach; ?>
          <?php if (!$got): ?>
            <p>No tienes logros globales aún.</p>
          <?php endif; ?>
        </div>
      </div>

      <!-- Logros por juego -->
      <?php foreach ($libraryGames as $g): ?>
        <div class="section">
          <h2><?= htmlspecialchars($g['nombre']) ?></h2>
          <div class="logros-grid">
            <?php
              $rows = $achievementsByGame[$g['id_juego']] ?? [];
              if (empty($rows)) {
                echo "<p>No hay logros para este juego.</p>";
              } else {
                foreach ($rows as $a) {
                  $cl = empty($a['fecha_obtenido']) ? 'pendiente' : '';
            ?>
              <div class="logro-item <?= $cl ?>">
                <img src="data:image/jpeg;base64,<?= base64_encode($a['imagen']) ?>" alt="">
                <h4><?= htmlspecialchars($a['nombre']) ?></h4>
                <?php if (!empty($a['fecha_obtenido'])): ?>
                  <small>Obtenido <?= date("Y-m-d",strtotime($a['fecha_obtenido'])) ?></small>
                <?php endif; ?>
              </div>
            <?php
                }
              }
            ?>
          </div>
        </div>
      <?php endforeach; ?>
    </section>
  </div>

  <script>
    // Toggle datos de perfil
    document.getElementById('toggleProfileBtn').onclick = () => {
      const box = document.getElementById('profileFieldsContainer');
      const btn = document.getElementById('toggleProfileBtn');
      if (box.style.display === 'block') {
        box.style.display = 'none';
        btn.textContent = 'Mostrar datos';
      } else {
        box.style.display = 'block';
        btn.textContent = 'Ocultar datos';
      }
    };
    // Toggle campos desarrollador
    document.getElementById('isDeveloperCheckbox')?.addEventListener('change', function(){
      document.getElementById('developerFields').style.display =
        this.checked ? 'block' : 'none';
    });
    // Cargar partidas y amigos
    document.addEventListener('DOMContentLoaded', () => {
      fetch('php/obtener_partidas.php')
        .then(r=>r.json())
        .then(arr=>{
          const c = document.getElementById('partidas-lista');
          c.innerHTML = arr.length
            ? arr.map(p=>`
                <div class="partida-item">
                  <h4>${p.nombre_juego}</h4>
                  <p>ID: ${p.partida_id}</p>
                  <p class="turno">${p.es_tu_turno==='Sí'?'🎯 Tu turno':'⏳ Esperando'}</p>
                </div>
              `).join('')
            : '<p style="color:#fff;">No hay partidas activas.</p>';
        });
      fetch('php/obtener_amigos.php')
        .then(r=>r.json())
        .then(arr=>{
          const c = document.getElementById('amigos-lista');
          c.innerHTML = arr.length
            ? arr.map(a=>`<div class="friend-item">
                <img src="${a.imagen}" alt=""><span>${a.usuario}</span>
              </div>`).join('')
            : '<p>No tienes amigos.</p>';
        });
    });
  </script>
  <script src="js/partidas.js"></script>
  <script src="js/amigosperfil.js"></script>

  <footer class="footer">
    <nav>
      <a href="index.php">Inicio</a> |
      <a href="biblioteca.php">Biblioteca</a> |
      <a href="comunidad.php">Comunidad</a> |
      <a href="premios.php">Premios</a> |
      <a href="perfil.php">Perfil</a>
    </nav>
    <p>&copy; 2025 GAMEDOM. Todos los derechos reservados.</p>
  </footer>

</body>


</html>
