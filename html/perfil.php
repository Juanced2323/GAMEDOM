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

// No cerramos la conexión aquí para poder usarla en las consultas de logros
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Perfil - GAMEDOM</title>
  <link rel="stylesheet" href="css/perfil.css">
  <link rel="stylesheet" href="css/achievement.css">
  <style>
    /* Estilos para imagen y botones */
    .image-box img {
      width: 150px;
      height: 150px;
      object-fit: cover;
      border-radius: 50%;
    }
    #uploadForm {
      display: none;
      margin-top: 10px;
    }
    .pencil-btn {
      display: none;
    }
    #editImageBtn {
      margin-top: 5px;
      font-size: 0.9em;
      padding: 5px 10px;
    }
    /* Estilos para la sección de logros */
    .logros-section {
      margin-top: 30px;
      padding: 20px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
    .logros-section h2,
    .logros-section h3 {
      margin-bottom: 15px;
      font-weight: 600;
    }
    .logros-conseguidos, .logros-pendientes {
      display: flex;
      flex-wrap: wrap;
      gap: 15px;
    }
    .logro-item {
      width: 150px;
      text-align: center;
      background: rgba(0,0,0,0.2);
      padding: 10px;
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    .logro-item img {
      width: 80px;
      height: 80px;
      object-fit: cover;
      border-radius: 5px;
      margin-bottom: 5px;
    }
    .logro-item.pendiente img {
      filter: grayscale(100%);
      opacity: 0.5;
    }
    .logro-item h4 {
      font-size: 1em;
      margin: 5px 0;
    }
    .logro-item p {
      font-size: 0.8em;
      margin: 0;
    }
    .logro-item small {
      font-size: 0.7em;
    }

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
  <header>
    <nav class="navbar">
      <div class="nav-left">
        <a href="index.php" class="nav-item">Inicio</a>
        <a href="biblioteca.php" class="nav-item">Biblioteca</a>
        <a href="comunidad.php" class="nav-item">Comunidad</a>
        <a href="premios.php" class="nav-item">Premios</a>
      </div>
      <div class="nav-right">
        <?php if (isset($_SESSION['usuario'])): ?>
          <a href="perfil.php" class="nav-item">Perfil</a>
        <?php else: ?>
          <a href="login.html" class="nav-item">Iniciar Sesión</a>
        <?php endif; ?>
      </div>
    </nav>
  </header>
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

    <!-- Sección de Amigos -->
    <div class="friends-section logros-section">
          <h2> Amigos </h2>
          <div class="amigos-lista" id="amigos-lista">
            <!-- Aqui se muestran hasta 5 amigos via JavaScript -->
            <p>Cargando amigos...</p>
          </div>
          <a href="amigos.php" class="btn btn-secundario" style="margin-top: 15px; display: inline-block;">Gestionar amigos</a>
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

  <!-- Script para gestionar carga de amigos -->
  <script>
    document.addEventListener("DOMContentLoaded", function () {
      fetch("php/obtener_amigos.php")
        .then(response => response.json())
        .then(data => {
          const contenedor = document.getElementById("amigos-lista");
          contenedor.innerHTML = ""; // Limpiar mensaje de carga

          if (!Array.isArray(data) || data.length === 0) {
            contenedor.innerHTML = "<p>Aún no tienes amigos.</p>";
            return;
          }

          data.forEach(amigo => {
            const item = document.createElement("div");
            item.className = "amigo-item";
            item.innerHTML = `
              <img src="${amigo.imagen}" alt="Avatar de ${amigo.usuario}">
              <span>${amigo.usuario}</span>
            `;
            contenedor.appendChild(item);
          });
        })
        .catch(error => {
          console.error("Error al cargar amigos:", error);
          document.getElementById("amigos-lista").innerHTML = "<p>Error al cargar los amigos.</p>";
        });
    });
  </script>


</body>
</html>
