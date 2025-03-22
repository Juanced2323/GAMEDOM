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
$conn->close();

// Formatear la fecha de registro para mostrar solo la fecha
$fechaRegistro = date("Y-m-d", strtotime($userData['fecha_registro']));

// Convertir la imagen de perfil (BLOB) a base64 (suponemos JPG)
if (!empty($userData['imagen'])) {
    $img_src = "data:image/jpeg;base64," . base64_encode($userData['imagen']);
} else {
    $img_src = 'images/default-profile.png';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Perfil - GAMEDOM</title>
  <link rel="stylesheet" href="css/perfil.css">
  <style>
    /* Imagen de perfil grande */
    .image-box img {
      width: 150px;
      height: 150px;
      object-fit: cover;
      border-radius: 50%;
    }
    /* Ocultar formulario de subir imagen por defecto */
    #uploadForm {
      display: none;
      margin-top: 10px;
    }
    /* Ocultar los botones de lápiz por defecto */
    .pencil-btn {
      display: none;
    }
    /* Estilo para el botón de cambiar imagen */
    #editImageBtn {
      margin-top: 5px;
      font-size: 0.9em;
      padding: 5px 10px;
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
        <!-- Puedes agregar más enlaces si es necesario -->
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
        <!-- Botón para cambiar la imagen, visible siempre en esta sección -->
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
        <!-- Botón para guardar cambios en campos de texto, inicialmente oculto -->
        <button id="saveChangesBtn" class="save-btn" style="display: none;" onclick="saveProfileChanges()">Guardar Cambios</button>
        <!-- Botón para cerrar sesión -->
        <button id="logoutBtn" class="save-btn" onclick="logoutUser()">Cerrar Sesión</button>
      </div>
    </div>
  </main>

  <!-- Modal para editar campos de texto (si usas esa lógica) -->
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
    // Función para alternar la visibilidad de los botones lápiz y el formulario de editar campos de texto
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

    // Función para alternar el formulario de subir imagen
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
</body>
</html>
