<?php
session_start();
// Opcional: Verificar que el usuario tenga permisos de administrador

require_once "php/db_connect.php";

// Obtener todas las categorías disponibles de la tabla "categorias"
$categorias = [];
$result = $conn->query("SELECT * FROM categorias ORDER BY nombre ASC");
while ($row = $result->fetch_assoc()) {
    $categorias[] = $row;
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Añadir Nuevo Juego - Admin</title>
  <link rel="stylesheet" href="css/admin_juegos.css">
</head>
<body>
  <header>
    <h1>Añadir Nuevo Juego</h1>
  </header>
  <main>
    <form action="php/add_game.php" method="POST" enctype="multipart/form-data">
      <div>
        <label for="nombre">Nombre del Juego:</label>
        <input type="text" id="nombre" name="nombre" required>
      </div>
      <div>
        <label for="descripcion">Descripción:</label>
        <textarea id="descripcion" name="descripcion" required></textarea>
      </div>
      <div>
        <label for="ruta_index">Ruta del Index del Juego:</label>
        <input type="text" id="ruta_index" name="ruta_index" placeholder="e.g., games/HundirLaFlota/index.html" required>
      </div>
      <div>
        <label for="icono">Icono del Juego (JPG, PNG):</label>
        <input type="file" id="icono" name="icono" accept=".jpg,.jpeg,.png" required>
      </div>
      <div>
        <label for="capturas">Capturas del Juego (JPG, PNG):</label>
        <input type="file" id="capturas" name="capturas" accept=".jpg,.jpeg,.png">
      </div>
      <fieldset>
        <legend>Selecciona el Modo de Juego</legend>
        <!-- Usamos checkboxes; se unirán en una cadena -->
        <label>
          <input type="checkbox" name="modo_juego[]" value="Multijugador"> Multijugador
        </label>
        <label>
          <input type="checkbox" name="modo_juego[]" value="Individual"> Individual
        </label>
        <label>
          <input type="checkbox" name="modo_juego[]" value="IA"> IA
        </label>
      </fieldset>
      <fieldset>
        <legend>Selecciona las Categorías</legend>
        <?php if (count($categorias) > 0): ?>
          <?php foreach ($categorias as $cat): ?>
            <div>
              <input type="checkbox" id="cat_<?php echo $cat['id_categoria']; ?>" name="categorias[]" value="<?php echo $cat['id_categoria']; ?>">
              <label for="cat_<?php echo $cat['id_categoria']; ?>"><?php echo htmlspecialchars($cat['nombre']); ?></label>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p>No hay categorías disponibles.</p>
        <?php endif; ?>
      </fieldset>
      <button type="submit">Agregar Juego</button>
    </form>
  </main>
</body>
</html>
