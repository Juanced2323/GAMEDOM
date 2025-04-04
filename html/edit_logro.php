<?php
session_start();
require_once "php/db_connect.php";

if (!isset($_GET['id'])) {
    header("Location: admin_logros.php");
    exit();
}

$id_logro = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM logros WHERE id_logro = ?");
$stmt->bind_param("i", $id_logro);
$stmt->execute();
$logro = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Obtener la lista de juegos para el dropdown
$games = [];
$resultGames = $conn->query("SELECT id_juego, nombre FROM juegos ORDER BY nombre ASC");
while ($row = $resultGames->fetch_assoc()) {
    $games[] = $row;
}
$resultGames->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Editar Logro - <?php echo htmlspecialchars($logro['nombre']); ?></title>
  <link rel="stylesheet" href="css/admin_juegos.css">
  <style>
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
    function toggleGameSelectorEdit() {
      const tipoGlobal = document.getElementById("tipoGlobalEdit");
      const gameSelector = document.getElementById("gameSelectorEdit");
      if (!tipoGlobal.checked) {
        gameSelector.style.display = "block";
      } else {
        gameSelector.style.display = "none";
      }
    }
    window.onload = function() {
      // Al cargar la página, si el logro es de tipo "juego", mostrar el dropdown.
      const tipo = document.querySelector('input[name="tipo"]:checked').value;
      if (tipo === 'juego') {
        document.getElementById("gameSelectorEdit").style.display = "block";
      }
    }
  </script>
</head>
<body>
  <header>
    <h1>Editar Logro</h1>
  </header>
  <main>
    <form action="php/edit_logro.php" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="id_logro" value="<?php echo $logro['id_logro']; ?>">
      <div>
        <label for="nombre">Nombre:</label>
        <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($logro['nombre']); ?>" required>
      </div>
      <div>
        <label for="descripcion">Descripción:</label>
        <textarea id="descripcion" name="descripcion" required><?php echo htmlspecialchars($logro['descripcion']); ?></textarea>
      </div>
      <div>
        <label for="imagen">Icono (Opcional):</label>
        <input type="file" id="imagen" name="imagen" accept=".jpg,.jpeg,.png">
        <small>Dejar vacío si no se desea cambiar el icono.</small>
      </div>
      <!-- Selección de tipo de logro -->
      <div class="tipo-logro">
        <span>Tipo de Logro:</span>
        <label>
          <input type="radio" name="tipo" id="tipoGlobalEdit" value="global" <?php echo ($logro['tipo'] === 'global') ? 'checked' : ''; ?> onclick="toggleGameSelectorEdit()"> Global
        </label>
        <label>
          <input type="radio" name="tipo" value="juego" <?php echo ($logro['tipo'] === 'juego') ? 'checked' : ''; ?> onclick="toggleGameSelectorEdit()"> Juego
        </label>
      </div>
      <!-- Dropdown para seleccionar juego (solo si es de tipo 'juego') -->
      <div id="gameSelectorEdit">
        <label for="id_juego">Selecciona el Juego:</label>
        <select name="id_juego" id="id_juego">
          <?php foreach ($games as $game): ?>
            <?php 
              $selected = ($logro['id_juego'] == $game['id_juego']) ? "selected" : "";
            ?>
            <option value="<?php echo $game['id_juego']; ?>" <?php echo $selected; ?>>
              <?php echo htmlspecialchars($game['nombre']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <button type="submit">Actualizar Logro</button>
    </form>
  </main>
</body>
</html>
