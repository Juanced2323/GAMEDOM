<?php
session_start();
// Opcional: Verificar permisos de administrador

require_once "php/db_connect.php";

// Verificar que se haya pasado un id de juego por GET
if (!isset($_GET['id'])) {
    header("Location: admin_juegos.php");
    exit();
}

$id_juego = intval($_GET['id']);

// Obtener datos del juego
$stmt = $conn->prepare("SELECT * FROM juegos WHERE id_juego = ?");
$stmt->bind_param("i", $id_juego);
$stmt->execute();
$result = $stmt->get_result();
$juego = $result->fetch_assoc();
$stmt->close();

if (!$juego) {
    echo "Juego no encontrado.";
    exit();
}

// Obtener todas las categorías
$categoriasDisponibles = [];
$resCat = $conn->query("SELECT * FROM categorias ORDER BY nombre ASC");
while ($row = $resCat->fetch_assoc()) {
    $categoriasDisponibles[] = $row;
}
$resCat->close();

// Obtener las categorías ya asignadas a este juego
$categoriasAsignadas = [];
$stmt = $conn->prepare("SELECT id_categoria FROM juegos_categorias WHERE id_juego = ?");
$stmt->bind_param("i", $id_juego);
$stmt->execute();
$resAsig = $stmt->get_result();
while ($row = $resAsig->fetch_assoc()) {
    $categoriasAsignadas[] = $row['id_categoria'];
}
$stmt->close();

// Obtener el modo_juego como array (si existe)
$modo_juego_array = [];
if (!empty($juego['modo_juego'])) {
    $modo_juego_array = explode(",", $juego['modo_juego']);
}

// Cerrar la conexión
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Editar Juego - <?php echo htmlspecialchars($juego['nombre']); ?></title>
  <link rel="stylesheet" href="css/admin_juegos.css">
</head>
<body>
  <header>
    <h1>Editar Juego</h1>
  </header>
  <main>
    <form action="php/edit_game.php" method="POST" enctype="multipart/form-data">
      <!-- Enviamos el ID del juego oculto -->
      <input type="hidden" name="id_juego" value="<?php echo $juego['id_juego']; ?>">
      
      <div>
        <label for="nombre">Nombre del Juego:</label>
        <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($juego['nombre']); ?>" required>
      </div>
      <div>
        <label for="descripcion">Descripción:</label>
        <textarea id="descripcion" name="descripcion" required><?php echo htmlspecialchars($juego['descripcion']); ?></textarea>
      </div>
      <div>
        <label for="ruta_index">Ruta del Index:</label>
        <input type="text" id="ruta_index" name="ruta_index" value="<?php echo htmlspecialchars($juego['ruta_index']); ?>" required>
      </div>

      <!-- Icono (opcional) -->
      <div>
        <label for="icono">Nuevo Icono (JPG, PNG):</label>
        <input type="file" id="icono" name="icono" accept=".jpg,.jpeg,.png">
        <small>Dejar vacío si no se desea cambiar el icono.</small>
      </div>

      <!-- Capturas (opcional) -->
      <div>
        <label for="capturas">Nuevas Capturas (JPG, PNG):</label>
        <input type="file" id="capturas" name="capturas" accept=".jpg,.jpeg,.png">
        <small>Dejar vacío si no se desea cambiar las capturas.</small>
      </div>

      <!-- Modo de Juego (checkbox) -->
      <fieldset>
        <legend>Selecciona el Modo de Juego</legend>
        <?php
        // Lista de modos de juego disponibles
        $modos = ["Multijugador", "Individual", "IA"];
        foreach ($modos as $modo) {
            $checked = in_array($modo, $modo_juego_array) ? "checked" : "";
            echo "<label>
                    <input type='checkbox' name='modo_juego[]' value='".htmlspecialchars($modo)."' $checked> 
                    ".htmlspecialchars($modo)."
                  </label>";
        }
        ?>
      </fieldset>

      <!-- Categorías (checkbox) -->
      <fieldset>
        <legend>Selecciona las Categorías</legend>
        <?php if (!empty($categoriasDisponibles)): ?>
          <?php foreach ($categoriasDisponibles as $cat): ?>
            <?php 
              $checked = in_array($cat['id_categoria'], $categoriasAsignadas) ? "checked" : "";
            ?>
            <div>
              <input type="checkbox" 
                     id="cat_<?php echo $cat['id_categoria']; ?>" 
                     name="categorias[]" 
                     value="<?php echo $cat['id_categoria']; ?>"
                     <?php echo $checked; ?>>
              <label for="cat_<?php echo $cat['id_categoria']; ?>">
                <?php echo htmlspecialchars($cat['nombre']); ?>
              </label>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p>No hay categorías disponibles.</p>
        <?php endif; ?>
      </fieldset>

      <button type="submit">Actualizar Juego</button>
    </form>
  </main>
</body>
</html>
