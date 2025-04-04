<?php
session_start();
// Opcional: Verificar permisos de administrador

require_once "php/db_connect.php";

// Obtener todas las categorías disponibles (si las usas para logros, opcional)
$categorias = [];
$result = $conn->query("SELECT * FROM categorias ORDER BY nombre ASC");
while ($row = $result->fetch_assoc()) {
    $categorias[] = $row;
}

// Obtener la lista de juegos para los logros específicos
$games = [];
$resultGames = $conn->query("SELECT id_juego, nombre FROM juegos ORDER BY nombre ASC");
while ($row = $resultGames->fetch_assoc()) {
    $games[] = $row;
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Añadir Logro - GAMEDOM</title>
  <link rel="stylesheet" href="css/admin_juegos.css">
  <style>
    /* Estilos para el selector de tipo de logro */
    .tipo-logro {
      margin: 10px 0;
    }
    .tipo-logro label {
      margin-right: 15px;
      font-weight: 600;
    }
    #gameSelector {
      display: none;
      margin-top: 5px;
    }
  </style>
  <script>
    // Función para mostrar/ocultar el selector de juego según el tipo de logro seleccionado
    function toggleGameSelector() {
      const tipoGlobal = document.getElementById("tipoGlobal");
      const gameSelector = document.getElementById("gameSelector");
      if (!tipoGlobal.checked) {
        gameSelector.style.display = "block";
      } else {
        gameSelector.style.display = "none";
      }
    }
  </script>
</head>
<body>
  <header>
    <h1>Añadir Nuevo Logro</h1>
  </header>
  <main>
    <form action="php/add_logro.php" method="POST" enctype="multipart/form-data">
      <div>
        <label for="nombre">Nombre del Logro:</label>
        <input type="text" id="nombre" name="nombre" required>
      </div>
      <div>
        <label for="descripcion">Descripción:</label>
        <textarea id="descripcion" name="descripcion" required></textarea>
      </div>
      <div>
        <label for="imagen">Icono del Logro (JPG, PNG):</label>
        <input type="file" id="imagen" name="imagen" accept=".jpg,.jpeg,.png" required>
      </div>
      <!-- Selección de tipo de logro -->
      <div class="tipo-logro">
        <span>Tipo de Logro:</span>
        <label>
          <input type="radio" name="tipo" id="tipoGlobal" value="global" checked onclick="toggleGameSelector()"> Global
        </label>
        <label>
          <input type="radio" name="tipo" value="juego" onclick="toggleGameSelector()"> Juego
        </label>
      </div>
      <!-- Dropdown para seleccionar juego (visible solo si se selecciona "juego") -->
      <div id="gameSelector">
        <label for="id_juego">Selecciona el Juego:</label>
        <select name="id_juego" id="id_juego">
          <?php foreach ($games as $game): ?>
            <option value="<?php echo $game['id_juego']; ?>">
              <?php echo htmlspecialchars($game['nombre']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <button type="submit">Agregar Logro</button>
    </form>
  </main>
</body>
</html>
