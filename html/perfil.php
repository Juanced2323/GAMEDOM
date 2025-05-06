<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.html");
    exit();
}

require_once "php/db_connect.php";
$username = $_SESSION['usuario'];

// 1) Comprobar si ya es desarrollador
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

// 2) Procesar creación de perfil de desarrollador
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

// 3) Cargar datos básicos del usuario
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE usuario = ?");
if (!$stmt) {
    die("Error en prepare: " . $conn->error);
}
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();
$stmt->close();

// Formatear fecha e imagen
$fechaRegistro = date("Y-m-d", strtotime($userData['fecha_registro']));
if (!empty($userData['imagen'])) {
    $img_src = "data:image/jpeg;base64," . base64_encode($userData['imagen']);
} else {
    $img_src = 'images/default-profile.png';
}

// 4) Cargar logros obtenidos y pendientes
$stmtAch = $conn->prepare("
    SELECT l.id_logro, l.nombre, l.descripcion, l.imagen, ul.fecha_obtenido 
      FROM logros l 
      INNER JOIN usuarios_logros ul ON l.id_logro = ul.id_logro 
     WHERE ul.usuario = ?
");
$stmtAch->bind_param("s", $username);
$stmtAch->execute();
$resultAch = $stmtAch->get_result();
$logros_obtenidos = $resultAch->fetch_all(MYSQLI_ASSOC);
$stmtAch->close();

$stmtPen = $conn->prepare("
    SELECT id_logro, nombre, descripcion, imagen 
      FROM logros 
     WHERE id_logro NOT IN (
         SELECT id_logro 
           FROM usuarios_logros 
          WHERE usuario = ?
     )
");
$stmtPen->bind_param("s", $username);
$stmtPen->execute();
$resultPen = $stmtPen->get_result();
$logros_pendientes = $resultPen->fetch_all(MYSQLI_ASSOC);
$stmtPen->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Perfil – GAMEDOM</title>
  <link rel="stylesheet" href="css/perfil.css">
  <link rel="stylesheet" href="css/achievement.css">
  <style>
    /* Campos de perfil (oculto por defecto) */
    #profileFieldsContainer { display: none; }
    /* Campos de desarrollador */
    #developerFields { display: none; margin-top: 15px; }
    .profile-header { text-align: center; margin-bottom: 20px; }
    #toggleProfileBtn { margin-top:10px; padding:8px 16px; cursor:pointer; }
    .save-btn { margin-top:10px; }
  </style>
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
        <a href="perfil.php" class="nav-item">Perfil</a>
      </div>
    </nav>
  </header>

  <main>
    <!-- Cabecera siempre visible -->
    <div class="profile-header">
      <div class="image-box">
        <img src="<?php echo $img_src; ?>" alt="Foto de Perfil" id="profilePic">
      </div>
      <h2 id="profileUsername"><?php echo htmlspecialchars($userData['usuario']); ?></h2>
      <button id="toggleProfileBtn" class="btn btn-secundario">
        Mostrar campos de perfil
      </button>
      <?php if ($isDeveloper): ?>
        <a href="mis_juegos.php" class="btn">Mis Juegos</a>
      <?php endif; ?>
    </div>

    <!-- Contenedor de edición de perfil -->
    <div id="profileFieldsContainer">
      <div class="profile-container">
        <!-- Sección de imagen -->
        <div class="profile-image-section">
          <button id="editImageBtn" onclick="toggleImageUpload()">Cambiar Imagen</button>
          <div id="uploadForm">
            <form action="php/upload_profile_image.php" method="POST" enctype="multipart/form-data">
              <input type="file" name="imagen" accept=".jpg,.jpeg,.png" required>
              <button type="submit">Subir Imagen</button>
            </form>
          </div>
        </div>

        <!-- Sección de información -->
        <div class="profile-info-section">
          <div class="info-group">
            <label for="correo">Correo</label>
            <input type="email" id="correo" name="correo"
                   value="<?php echo htmlspecialchars($userData['correo']); ?>" readonly>
          </div>
          <div class="info-group">
            <label for="usuario">Usuario</label>
            <input type="text" id="usuario" name="usuario"
                   value="<?php echo htmlspecialchars($userData['usuario']); ?>" readonly>
          </div>
          <div class="info-group">
            <label for="nombre">Nombre</label>
            <input type="text" id="nombre" name="nombre"
                   value="<?php echo htmlspecialchars($userData['nombre']); ?>" readonly>
            <button class="edit-btn pencil-btn"
                    onclick="openEditModal('nombre', document.getElementById('nombre').value)">✏️</button>
          </div>
          <div class="info-group">
            <label for="apellidos">Apellidos</label>
            <input type="text" id="apellidos" name="apellidos"
                   value="<?php echo htmlspecialchars($userData['apellidos']); ?>" readonly>
            <button class="edit-btn pencil-btn"
                    onclick="openEditModal('apellidos', document.getElementById('apellidos').value)">✏️</button>
          </div>
          <div class="info-group">
            <label for="edad">Edad</label>
            <input type="number" id="edad" name="edad"
                   value="<?php echo $userData['edad']; ?>" readonly>
            <button class="edit-btn pencil-btn"
                    onclick="openEditModal('edad', document.getElementById('edad').value)">✏️</button>
          </div>
          <div class="info-group">
            <label for="telefono">Teléfono</label>
            <input type="tel" id="telefono" name="telefono"
                   value="<?php echo htmlspecialchars($userData['telefono']); ?>" readonly>
            <button class="edit-btn pencil-btn"
                    onclick="openEditModal('telefono', document.getElementById('telefono').value)">✏️</button>
          </div>
          <div class="info-group">
            <label for="nacionalidad">Nacionalidad</label>
            <input type="text" id="nacionalidad" name="nacionalidad"
                   value="<?php echo htmlspecialchars($userData['nacionalidad']); ?>" readonly>
          </div>
          <div class="info-group">
            <label for="fecha_registro">Fecha de Registro</label>
            <input type="text" id="fecha_registro" name="fecha_registro"
                   value="<?php echo $fechaRegistro; ?>" readonly>
          </div>
          <button id="globalEditBtn" class="save-btn" onclick="togglePencilIcons()">Editar</button>
          <button id="saveChangesBtn" class="save-btn" style="display: none;"
                  onclick="saveProfileChanges()">Guardar Cambios</button>
        </div>
      </div>

      <?php if (!$isDeveloper): ?>
      <!-- Formulario perfil desarrollador -->
      <form method="POST">
        <div class="info-group">
          <label>
            <input type="checkbox" id="isDeveloper">
            ¿Eres desarrollador?
          </label>
        </div>
        <div id="developerFields">
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
          <button type="submit" name="createDeveloper" class="save-btn">
            Crear perfil desarrollador
          </button>
        </div>
      </form>
      <?php endif; ?>
    </div>

    <!-- Amigos siempre visible -->
    <div class="friends-section logros-section">
      <h2>Amigos</h2>
      <div class="amigos-lista" id="amigos-lista">
        <p>Cargando amigos...</p>
      </div>
      <a href="amigos.php" class="btn btn-secundario"
         style="margin-top: 15px; display: inline-block;">Gestionar amigos</a>
    </div>

    <!-- Logros y partidas siempre visibles -->
    <div class="logros-section">
      <h2>Tus Logros</h2>
      <?php if (count($logros_obtenidos) > 0): ?>
        <div class="logros-conseguidos">
          <?php foreach ($logros_obtenidos as $logro): ?>
            <div class="logro-item obtenido">
              <img src="data:image/jpeg;base64,<?php echo base64_encode($logro['imagen']); ?>"
                   alt="<?php echo htmlspecialchars($logro['nombre']); ?>">
              <h4><?php echo htmlspecialchars($logro['nombre']); ?></h4>
              <p><?php echo htmlspecialchars($logro['descripcion']); ?></p>
              <small>Conseguido el: <?php echo date("Y-m-d", strtotime($logro['fecha_obtenido'])); ?></small>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="logros-section" id="partidas-section">
          <h2>Partidas en Curso</h2>
          <div id="partidas-lista"><p>Cargando tus partidas...</p></div>
        </div>
      <?php else: ?>
        <p>Aún no has conseguido ningún logro.</p>
      <?php endif; ?>

      <h3>Logros Disponibles</h3>
      <div class="logros-pendientes">
        <?php if (count($logros_pendientes) > 0): ?>
          <?php foreach ($logros_pendientes as $logro): ?>
            <div class="logro-item pendiente">
              <img src="data:image/jpeg;base64,<?php echo base64_encode($logro['imagen']); ?>"
                   alt="<?php echo htmlspecialchars($logro['nombre']); ?>">
              <h4><?php echo htmlspecialchars($logro['nombre']); ?></h4>
              <p><?php echo htmlspecialchars($logro['descripcion']); ?></p>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p>¡Has conseguido todos los logros!</p>
        <?php endif; ?>
      </div>
    </div>
  </main>

  <!-- Modal de edición -->
  <div id="editModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeModal()">&times;</span>
      <h3>Editar <span id="modalFieldName"></span></h3>
      <input type="text" id="modalInput">
      <button class="save-btn" onclick="saveFieldChange()">Cambiar</button>
    </div>
  </div>

  <script src="js/perfil.js"></script>
  <script src="js/achievements.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      // Toggle de campos de perfil
      const toggleBtn = document.getElementById('toggleProfileBtn');
      const fieldsContainer = document.getElementById('profileFieldsContainer');
      toggleBtn.addEventListener('click', () => {
        const hidden = fieldsContainer.style.display === 'none';
        fieldsContainer.style.display = hidden ? 'block' : 'none';
        toggleBtn.textContent = hidden
          ? 'Ocultar campos de perfil'
          : 'Mostrar campos de perfil';
      });

      // Mostrar/ocultar formulario desarrollador
      const devCheckbox = document.getElementById('isDeveloper');
      if (devCheckbox) {
        devCheckbox.addEventListener('change', () => {
          document.getElementById('developerFields').style.display =
            devCheckbox.checked ? 'block' : 'none';
        });
      }
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
