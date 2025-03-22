<?php
session_start();
// Opcional: verifica si el usuario tiene permisos de administrador

require_once "php/db_connect.php";

// Obtener todas las categorías ordenadas alfabéticamente
$result = $conn->query("SELECT * FROM categorias ORDER BY nombre ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Administración de Categorías - GAMEDOM</title>
  <link rel="stylesheet" href="css/admin_categorias.css">
</head>
<body>
  <header>
    <h1>Administración de Categorías</h1>
  </header>
  <main>
    <a href="add_category.php" class="btn">Añadir Nueva Categoría</a>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php while($categoria = $result->fetch_assoc()): ?>
          <tr>
            <td><?php echo $categoria['id_categoria']; ?></td>
            <td><?php echo htmlspecialchars($categoria['nombre']); ?></td>
            <td>
              <a href="edit_category.php?id=<?php echo $categoria['id_categoria']; ?>" class="btn">Editar</a>
              <a href="delete_category.php?id=<?php echo $categoria['id_categoria']; ?>" class="btn btn-danger" onclick="return confirm('¿Seguro que deseas eliminar esta categoría?')">Eliminar</a>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </main>
</body>
</html>
<?php $conn->close(); ?>
