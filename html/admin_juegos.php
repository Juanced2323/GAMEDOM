<?php
session_start();
// Opcional: Verifica que el usuario tenga permisos de administrador

require_once "php/db_connect.php";

$result = $conn->query("SELECT * FROM juegos ORDER BY nombre ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Administración de Juegos - GAMEDOM</title>
  <link rel="stylesheet" href="css/admin_juegos.css">
</head>
<body>
  <header>
    <h1>Administración de Juegos</h1>
  </header>
  <main>
    <a href="add_game.php" class="btn">Añadir Nuevo Juego</a>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <th>Ruta Index</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php while($juego = $result->fetch_assoc()): ?>
          <tr>
            <td><?php echo $juego['id_juego']; ?></td>
            <td><?php echo $juego['nombre']; ?></td>
            <td><?php echo $juego['ruta_index']; ?></td>
            <td>
              <a href="edit_game.php?id=<?php echo $juego['id_juego']; ?>" class="btn">Editar</a>
              <a href="delete_game.php?id=<?php echo $juego['id_juego']; ?>" class="btn btn-danger" onclick="return confirm('¿Seguro que deseas eliminar este juego?')">Eliminar</a>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </main>
</body>
</html>
