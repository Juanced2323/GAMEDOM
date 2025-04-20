<?php 
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.html");
    exit();
}

require_once "php/db_connect.php";

$username = $_SESSION['usuario'];
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE usuario = ?");
if(!$stmt) {
    die("Error en prepare: " . $conn->error);
}
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();
$stmt->close();

// Formatear la fecha de registro para mostrar solo la fecha
$fechaRegistro = date("Y-m-d", strtotime($userData['fecha_registro']));

// Convertir la imagen de perfil (BLOB) a base64 (suponemos JPG)
if (!empty($userData['imagen'])) {
    $img_src = "data:image/jpeg;base64," . base64_encode($userData['imagen']);
} else {
    $img_src = 'images/default-profile.png';
}

// Obtener lista de amigos
$correoUsuario = $userData['correo'];
$stmtAmigos = $conn->prepare("SELECT u.usuario, u.correo FROM usuarios u 
                             JOIN amistades a ON (a.solicitante = u.correo OR a.destinatario = u.correo) 
                             WHERE (a.solicitante = ? OR a.destinatario = ?) AND u.correo != ? AND a.estado = 'aceptada'");
$stmtAmigos->bind_param("sss", $correoUsuario, $correoUsuario, $correoUsuario);
$stmtAmigos->execute();
$resAmigos = $stmtAmigos->get_result();
$amigos = $resAmigos->fetch_all(MYSQLI_ASSOC);
$stmtAmigos->close();

// Obtener solicitudes pendientes
$stmtPendientes = $conn->prepare("SELECT u.usuario, u.correo FROM usuarios u 
                                JOIN amistades a ON a.solicitante = u.correo 
                                WHERE a.destinatario = ? AND a.estado = 'pendiente'");
$stmtPendientes->bind_param("s", $correoUsuario);
$stmtPendientes->execute();
$resPendientes = $stmtPendientes->get_result();
$pendientes = $resPendientes->fetch_all(MYSQLI_ASSOC);
$stmtPendientes->close();

// No cerramos la conexión aquí para poder usarla en las consultas de logros
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Perfil - GAMEDOM</title>
  <link rel="stylesheet" href="css/index.css">
  <link rel="stylesheet" href="css/perfil.css">
  <link rel="stylesheet" href="css/logros.css">
  <link rel="stylesheet" href="css/amigos.css">
</head>
<body>
  <!-- MENÚ SUPERIOR -->
  <div class="menu-superior">
    <div class="nav-left">
      <img src="images/imagenes/Logo.png" alt="Logo Gamedom" class="logo">
    </div>
    <div class="nav-right">
      <a href="index.php" class="nav-item <?php echo ($activePage === 'index') ? 'active' : ''; ?>">Inicio</a>
      <a href="biblioteca.php" class="nav-item <?php echo ($activePage === 'biblioteca') ? 'active' : ''; ?>">Biblioteca</a>
      <a href="comunidad.php" class="nav-item <?php echo ($activePage === 'comunidad') ? 'active' : ''; ?>">Comunidad</a>
      <a href="torneos.php" class="nav-item <?php echo ($activePage === 'torneos') ? 'active' : ''; ?>">Torneos</a>
      <?php if (isset($_SESSION['usuario'])): ?>
        <a href="perfil.php" class="nav-item <?php echo ($activePage === 'perfil') ? 'active' : ''; ?>">Perfil</a>
      <?php else: ?>
        <a href="login.html" class="nav-item">Iniciar Sesión</a>
      <?php endif; ?>
    </div>
  </div>

  <main>
    <div class="profile-container">
      <!-- Sección de Imagen de Perfil -->
      <div class="profile-image-section">
        <div class="image-box">
          <img src="<?php echo $img_src; ?>" alt="Foto de Perfil" id="profilePic">
        </div>
        <!-- Botón para cambiar la imagen -->
        <button id="editImageBtn" onclick="toggleImageUpload()">Cambiar Imagen</button>
        <!-- Formulario para subir la imagen, inicialmente oculto -->
        <div id="uploadForm">
          <form action="php/upload_profile_image.php" method="POST" enctype="multipart/form-data">
            <input type="file" name="imagen" accept=".jpg,.jpeg,.png" required>
            <button type="submit">Subir Imagen</button>
          </form>
        </div>
      </div>

      <!-- Sección de Información del Perfil -->
      <div class="profile-info-section">
        <div class="info-group">
          <label for="correo">Correo</label>
          <input type="email" id="correo" name="correo" value="<?php echo htmlspecialchars($userData['correo']); ?>" readonly>
        </div>
        <div class="info-group">
          <label for="usuario">Usuario</label>
          <input type="text" id="usuario" name="usuario" value="<?php echo htmlspecialchars($userData['usuario']); ?>" readonly>
        </div>
        <div class="info-group">
          <label for="nombre">Nombre</label>
          <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($userData['nombre']); ?>" readonly>
          <button class="edit-btn pencil-btn" onclick="openEditModal('nombre', document.getElementById('nombre').value)">✏️</button>
        </div>
        <div class="info-group">
          <label for="apellidos">Apellidos</label>
          <input type="text" id="apellidos" name="apellidos" value="<?php echo htmlspecialchars($userData['apellidos']); ?>" readonly>
          <button class="edit-btn pencil-btn" onclick="openEditModal('apellidos', document.getElementById('apellidos').value)">✏️</button>
        </div>
        <div class="info-group">
          <label for="edad">Edad</label>
          <input type="number" id="edad" name="edad" value="<?php echo $userData['edad']; ?>" readonly>
          <button class="edit-btn pencil-btn" onclick="openEditModal('edad', document.getElementById('edad').value)">✏️</button>
        </div>
        <div class="info-group">
          <label for="telefono">Teléfono</label>
          <input type="tel" id="telefono" name="telefono" value="<?php echo htmlspecialchars($userData['telefono']); ?>" readonly>
          <button class="edit-btn pencil-btn" onclick="openEditModal('telefono', document.getElementById('telefono').value)">✏️</button>
        </div>
        <div class="info-group">
          <label for="nacionalidad">Nacionalidad</label>
          <input type="text" id="nacionalidad" name="nacionalidad" value="<?php echo htmlspecialchars($userData['nacionalidad']); ?>" readonly>
        </div>
        <div class="info-group">
          <label for="fecha_registro">Fecha de Registro</label>
          <input type="text" id="fecha_registro" name="fecha_registro" value="<?php echo $fechaRegistro; ?>" readonly>
        </div>
        <!-- Botón global para activar la edición de campos de texto -->
        <button id="globalEditBtn" class="save-btn" onclick="togglePencilIcons()">Editar</button>
        <!-- Botón para guardar cambios, inicialmente oculto -->
        <button id="saveChangesBtn" class="save-btn" style="display: none;" onclick="saveProfileChanges()">Guardar Cambios</button>
        <!-- Botón para cerrar sesión -->
        <button id="logoutBtn" class="save-btn" onclick="logoutUser()">Cerrar Sesión</button>
      </div>
    </div>

    <!-- Sección de Logros -->
    <?php 
    // Obtener logros conseguidos y pendientes para el usuario
    require_once "php/db_connect.php";
    
    // Logros conseguidos
    $stmtAch = $conn->prepare("SELECT l.id_logro, l.nombre, l.descripcion, l.imagen, ul.fecha_obtenido 
                               FROM logros l 
                               INNER JOIN usuarios_logros ul ON l.id_logro = ul.id_logro 
                               WHERE ul.usuario = ?");
    $stmtAch->bind_param("s", $username);
    $stmtAch->execute();
    $resultAch = $stmtAch->get_result();
    $logros_obtenidos = $resultAch->fetch_all(MYSQLI_ASSOC);
    $stmtAch->close();
    
    // Logros pendientes
    $stmtPen = $conn->prepare("SELECT id_logro, nombre, descripcion, imagen 
                               FROM logros 
                               WHERE id_logro NOT IN (SELECT id_logro FROM usuarios_logros WHERE usuario = ?)");
    $stmtPen->bind_param("s", $username);
    $stmtPen->execute();
    $resultPen = $stmtPen->get_result();
    $logros_pendientes = $resultPen->fetch_all(MYSQLI_ASSOC);
    $stmtPen->close();
    $conn->close();
    ?>
    <div class="logros-section">
      <h2>Tus Logros</h2>
      <?php if (count($logros_obtenidos) > 0): ?>
      <div class="logros-conseguidos">
        <?php foreach ($logros_obtenidos as $logro): ?>
          <div class="logro-item obtenido">
            <img src="data:image/jpeg;base64,<?php echo base64_encode($logro['imagen']); ?>" alt="<?php echo htmlspecialchars($logro['nombre']); ?>">
            <h4><?php echo htmlspecialchars($logro['nombre']); ?></h4>
            <p><?php echo htmlspecialchars($logro['descripcion']); ?></p>
            <small>Conseguido el: <?php echo date("Y-m-d", strtotime($logro['fecha_obtenido'])); ?></small>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Sección de Partidas Pendientes -->
      <div class="logros-section" id="partidas-section">
        <h2>Partidas en Curso</h2>
        <div id="partidas-lista">
          <p>Cargando tus partidas...</p>
        </div>
      </div>

      <?php else: ?>
        <p>Aún no has conseguido ningún logro.</p>
      <?php endif; ?>
      
      <h3>Logros Disponibles</h3>
      <div class="logros-pendientes">
        <?php if (count($logros_pendientes) > 0): ?>
          <?php foreach ($logros_pendientes as $logro): ?>
            <div class="logro-item pendiente">
              <img src="data:image/jpeg;base64,<?php echo base64_encode($logro['imagen']); ?>" alt="<?php echo htmlspecialchars($logro['nombre']); ?>">
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

  <!-- Modal para editar campos de texto (si se utiliza) -->
  <div id="editModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeModal()">&times;</span>
      <h3>Editar <span id="modalFieldName"></span></h3>
      <input type="text" id="modalInput">
      <button class="save-btn" onclick="saveFieldChange()">Cambiar</button>
    </div>
  </div>

  <script src="js/perfil.js"></script>
  <script>
    function togglePencilIcons() {
      const pencilButtons = document.querySelectorAll('.pencil-btn');
      pencilButtons.forEach(button => {
        button.style.display = (button.style.display === 'none' || button.style.display === '') ? 'inline-block' : 'none';
      });

      const globalEditBtn = document.getElementById('globalEditBtn');
      const saveChangesBtn = document.getElementById('saveChangesBtn');
      if (globalEditBtn.style.display === 'none' || globalEditBtn.style.display === '') {
        globalEditBtn.style.display = 'inline-block';
        saveChangesBtn.style.display = 'none';
      } else {
        globalEditBtn.style.display = 'none';
        saveChangesBtn.style.display = 'inline-block';
      }
    }

    function toggleImageUpload() {
      const uploadForm = document.getElementById('uploadForm');
      if (uploadForm.style.display === 'none' || uploadForm.style.display === '') {
        uploadForm.style.display = 'block';
      } else {
        uploadForm.style.display = 'none';
      }
    }

    function logoutUser() {
      window.location.href = "php/logout.php";
    }
  </script>
  <script src="js/achievements.js"></script>

    <script>
  document.addEventListener("DOMContentLoaded", function() {
    fetch('php/obtener_partidas.php')
      .then(response => response.json())
      .then(data => {
        const lista = document.getElementById('partidas-lista');
        lista.innerHTML = ''; // Limpiar mensaje de carga

        if (data.length === 0) {
          lista.innerHTML = '<p>No estás en ninguna partida actualmente.</p>';
          return;
        }

        data.forEach(partida => {
          const partidaDiv = document.createElement('div');
          partidaDiv.classList.add('logro-item');

          partidaDiv.innerHTML = `
            <h4>${partida.nombre_juego}</h4>
            <p>ID Partida: ${partida.partida_id}</p>
            <p>${partida.es_tu_turno === 'Sí' ? '🎯 ¡Es tu turno!' : '⏳ Esperando turno'}</p>
          `;

          lista.appendChild(partidaDiv);
        });
      })
      .catch(error => {
        console.error('Error al cargar partidas:', error);
        document.getElementById('partidas-lista').innerHTML = '<p>Error al cargar las partidas.</p>';
      });
  });
  </script>

  <!-- Seccion Amistades -->
  <div class="amigos-section">
    <h2>Amigos</h2>
    <?php if (count($amigos) > 0): ?>
      <ul>
        <?php foreach ($amigos as $amigo): ?>
          <li><?php echo htmlspecialchars($amigo['usuario']) . " (" . htmlspecialchars($amigo['correo']) . ")"; ?></li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p>No tienes amigos aún.</p>
    <?php endif; ?>
  </div>

  <div class="amigos-section">
    <h2>Solicitudes de Amistad Pendientes</h2>
    <?php if (count($pendientes) > 0): ?>
      <ul>
        <?php foreach ($pendientes as $pendiente): ?>
          <li>
            <?php echo htmlspecialchars($pendiente['usuario']) . " (" . htmlspecialchars($pendiente['correo']) . ")"; ?>
            <form action="php/procesar_amistad.php" method="POST" style="display:inline">
              <input type="hidden" name="correo" value="<?php echo $pendiente['correo']; ?>">
              <button type="submit" name="accion" value="aceptar">Aceptar</button>
              <button type="submit" name="accion" value="eliminar">Eliminar</button>
            </form>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p>No tienes solicitudes pendientes.</p>
    <?php endif; ?>
  </div>

  <!-- FOOTER igual que index -->
  <footer class="footer">
    <p>
      © 2025 CodeCrafters. Todos los derechos reservados.  
      Todas las marcas registradas pertenecen a sus respectivos dueños en EE. UU. y otros países.<br>
      Todos los precios incluyen IVA (donde sea aplicable).
    </p>
    <nav>
      <a href="Política de privacidad.html">Política de Privacidad</a> |
      <a href="Información legal.html">Información legal</a> |
      <a href="Cookies.html">Cookies</a> |
      <a href="A cerca de.html">A cerca de CodeCrafters</a>
    </nav>
  </footer>

</body>
</html>
