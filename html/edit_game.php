<?php
session_start();
require_once "php/db_connect.php";

if (!isset($_GET['id'])) {
    header("Location: admin_juegos.php");
    exit();
}

$id_juego = intval($_GET['id']);

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
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Editar Juego - <?php echo $juego['nombre']; ?></title>
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
        <input type="text" id="nombre" name="nombre" value="<?php echo $juego['nombre']; ?>" required>
      </div>
      <div>
        <label for="descripcion">Descripción:</label>
        <textarea id="descripcion" name="descripcion" required><?php echo $juego['descripcion']; ?></textarea>
      </div>
      <div>
        <label for="ruta_index">Ruta del Index:</label>
        <input type="text" id="ruta_index" name="ruta_index" value="<?php echo $juego['ruta_index']; ?>" required>
      </div>
      <div>
        <label for="icono">Icono del Juego:</label>
        <input type="file" id="icono" name="icono" accept=".jpg,.jpeg,.png">
        <small>Dejar vacío si no se desea cambiar el icono.</small>
      </div>
      <button type="submit">Actualizar Juego</button>
    </form>
  </main>
</body>
</html>
