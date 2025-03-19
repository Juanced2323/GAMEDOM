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
      <button type="submit">Agregar Juego</button>
    </form>
  </main>
</body>
</html>

