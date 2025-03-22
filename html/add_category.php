<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Añadir Nueva Categoría - Admin</title>
  <link rel="stylesheet" href="css/admin_categorias.css">
</head>
<body>
  <header>
    <h1>Añadir Nueva Categoría</h1>
  </header>
  <main>
    <form action="php/add_category.php" method="POST">
      <div>
        <label for="nombre">Nombre de la Categoría:</label>
        <input type="text" id="nombre" name="nombre" required>
      </div>
      <button type="submit">Agregar Categoría</button>
    </form>
  </main>
</body>
</html>
