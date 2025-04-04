<?php
session_start();
require_once "php/db_connect.php";

// Obtener logros existentes
$logros = [];
$result = $conn->query("SELECT * FROM logros");
while ($row = $result->fetch_assoc()) {
    $logros[] = $row;
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Administración de Logros - GAMEDOM</title>
  <link rel="stylesheet" href="css/admin_juegos.css">
</head>
<body>
  <header>
    <h1>Administración de Logros</h1>
  </header>
  <main>
    <a href="add_logro.html" class="btn">➕ Añadir Nuevo Logro</a>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Icono</th>
          <th>Nombre</th>
          <th>Descripción</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($logros as $logro): ?>
        <tr>
          <td><?php echo $logro['id_logro']; ?></td>
          <td><img src="data:image/png;base64,<?= base64_encode($logro['imagen']); ?>" alt="Icono" style="width:50px;height:50px;"></td>
          <td><?php echo htmlspecialchars($logro['nombre']); ?></td>
          <td><?php echo htmlspecialchars($logro['descripcion']); ?></td>
          <td>
            <a href="edit_logro.php?id=<?php echo $logro['id_logro']; ?>" class="btn-edit">Editar</a>
            <a href="php/delete_logro.php?id=<?php echo $logro['id_logro']; ?>" class="btn-delete" onclick="return confirm('¿Seguro que deseas eliminar este logro?');">Eliminar</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </main>
</body>
</html>
