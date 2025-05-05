<?php
session_start();

// Carga la conexión, db_connect.php está en html/php/
require_once __DIR__ . '/php/db_connect.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: login.html");
    exit();
}

$usuario = $_SESSION['usuario'];

// 1) Verificar perfil de desarrollador
$stmtDev = $conn->prepare("
    SELECT id_desarrollador
      FROM desarrolladores
     WHERE usuario = ?
");
$stmtDev->bind_param("s", $usuario);
$stmtDev->execute();
$resDev = $stmtDev->get_result();
if (!$rowDev = $resDev->fetch_assoc()) {
    die("❌ No tienes perfil de desarrollador.");
}
$id_desarrollador = (int)$rowDev['id_desarrollador'];
$stmtDev->close();

// 2) Listar solo tus juegos
$stmt = $conn->prepare("
    SELECT j.id_juego, j.nombre, j.ruta_index
      FROM juegos j
      JOIN desarrolladores_juegos dj ON dj.id_juego = j.id_juego
     WHERE dj.id_desarrollador = ?
  ORDER BY j.nombre ASC
");
$stmt->bind_param("i", $id_desarrollador);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Mis Juegos Publicados – GAMEDOM</title>
  <style>
    body { font-family: Arial,sans-serif; background: #f0f2f5; margin:0 }
    .container { max-width:900px; margin:40px auto; background:#fff;
                 padding:20px; border-radius:8px;
                 box-shadow:0 2px 8px rgba(0,0,0,0.1) }
    h1 { color:#333; margin-bottom:20px }
    .alert { padding:10px 15px; border-radius:4px; margin-bottom:20px }
    .alert.success { background:#d4edda; color:#155724 }
    .btn { display:inline-block; padding:10px 16px;
           background:#28a745; color:#fff; text-decoration:none;
           border-radius:4px; margin-bottom:20px }
    .btn:hover { background:#218838 }
    .btn-danger { background:#dc3545 }
    .btn-danger:hover { background:#c82333 }
    table { width:100%; border-collapse:collapse; margin-bottom:20px }
    th, td { padding:12px; border:1px solid #ddd; text-align:left }
    th { background:#f7f7f7 }
    tr:nth-child(even) { background:#fafafa }
  </style>
</head>
<body>
  <div class="container">
    <h1>Mis Juegos Publicados</h1>

    <?php if (!empty($_GET['success'])): ?>
      <div class="alert success">✅ Juego publicado correctamente.</div>
    <?php endif; ?>

    <a href="php/add_game_dev.php" class="btn">+ Publicar Nuevo Juego</a>

    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <th>Ruta</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result->num_rows === 0): ?>
          <tr>
            <td colspan="4">Aún no has publicado ningún juego.</td>
          </tr>
        <?php else: ?>
          <?php while($j = $result->fetch_assoc()): ?>
            <tr>
              <td><?= $j['id_juego'] ?></td>
              <td><?= htmlspecialchars($j['nombre']) ?></td>
              <td><?= htmlspecialchars($j['ruta_index']) ?></td>
              <td>
                <a href="php/edit_game_dev.php?id=<?= $j['id_juego'] ?>" class="btn">✏️ Editar</a>
                <a href="php/delete_game_dev.php?id=<?= $j['id_juego'] ?>"
                   class="btn btn-danger"
                   onclick="return confirm('¿Eliminar «<?= addslashes($j['nombre']) ?>»?');">
                  🗑 Eliminar
                </a>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
