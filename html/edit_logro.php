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
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <title>Editar Logro</title>
  <link rel="stylesheet" href="css/admin_juegos.css">
</head>
<body>
  <header><h1>Editar Logro</h1></header>
  <main>
    <form action="php/edit_logro.php" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="id_logro" value="<?php echo $logro['id_logro']; ?>">
      <div>
        <label>Nombre:</label>
        <input type="text" name="nombre" value="<?php echo htmlspecialchars($logro['nombre']); ?>" required>
      </div>
      <div>
        <label>Descripción:</label>
        <textarea name="descripcion" required><?php echo htmlspecialchars($logro['descripcion']); ?></textarea>
      </div>
      <div>
        <label>Icono (Opcional):</label>
        <input type="file" name="imagen">
      </div>
      <button type="submit">Actualizar</button>
    </form>
  </main>
</body>
</html>
