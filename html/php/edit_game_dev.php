<?php
session_start();
require_once __DIR__ . '/db_connect.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: ../login.html");
    exit();
}

$usuario = $_SESSION['usuario'];

// 1) Verificar que el usuario es desarrollador y propietario del juego
if (!isset($_GET['id'])) {
    die("❌ Falta el ID del juego.");
}
$id_juego = intval($_GET['id']);

$stmtDev = $conn->prepare("
    SELECT j.*
      FROM juegos j
      JOIN desarrolladores_juegos dj
        ON dj.id_juego = j.id_juego
     WHERE dj.id_desarrollador = (
         SELECT id_desarrollador
           FROM desarrolladores
          WHERE usuario = ?
     )
       AND j.id_juego = ?
    LIMIT 1
");
$stmtDev->bind_param("si", $usuario, $id_juego);
$stmtDev->execute();
$resDev = $stmtDev->get_result();
if (!$game = $resDev->fetch_assoc()) {
    die("❌ No puedes editar este juego o no existe.");
}
$stmtDev->close();

// 2) Procesar POST para actualizar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoger y sanear
    $nombre      = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $ruta_index  = trim($_POST['ruta_index'] ?? '');
    $modo_array  = $_POST['modo_juego'] ?? [];
    $modo_juego  = implode(",", $modo_array);

    // Procesar icono (opcional)
    $iconoData = null;
    if (!empty($_FILES['icono']['tmp_name']) && $_FILES['icono']['error'] === UPLOAD_ERR_OK) {
        $iconoData = file_get_contents($_FILES['icono']['tmp_name']);
    }

    // Procesar capturas (opcional)
    $capturasData = null;
    if (!empty($_FILES['capturas']['tmp_name']) && $_FILES['capturas']['error'] === UPLOAD_ERR_OK) {
        $capturasData = file_get_contents($_FILES['capturas']['tmp_name']);
    }

    // 3) Construir UPDATE dinámico
    $fields = "nombre = ?, descripcion = ?, ruta_index = ?, modo_juego = ?";
    $types  = "ssss";
    $params = [ $nombre, $descripcion, $ruta_index, $modo_juego ];

    if ($iconoData !== null) {
        $fields .= ", icono = ?";
        $types  .= "s";
        $params[] = $iconoData;
    }
    if ($capturasData !== null) {
        $fields .= ", capturas = ?";
        $types  .= "s";
        $params[] = $capturasData;
    }

    $types .= "i";
    $params[] = $id_juego;

    $sql = "UPDATE juegos SET $fields WHERE id_juego = ?";
    $stmtU = $conn->prepare($sql);
    if (!$stmtU) {
        die("Error en prepare update: " . $conn->error);
    }
    $stmtU->bind_param($types, ...$params);
    if (!$stmtU->execute()) {
        die("Error al actualizar juego: " . $stmtU->error);
    }
    $stmtU->close();

    // 4) Actualizar categorías
    $stmtDel = $conn->prepare("DELETE FROM juegos_categorias WHERE id_juego = ?");
    $stmtDel->bind_param("i", $id_juego);
    $stmtDel->execute();
    $stmtDel->close();

    if (!empty($_POST['categorias']) && is_array($_POST['categorias'])) {
        $stmtCat = $conn->prepare("
            INSERT INTO juegos_categorias (id_juego, id_categoria)
            VALUES (?, ?)
        ");
        foreach ($_POST['categorias'] as $cat) {
            $cat_id = intval($cat);
            $stmtCat->bind_param("ii", $id_juego, $cat_id);
            if (!$stmtCat->execute()) {
                die("Error al actualizar categoría: " . $stmtCat->error);
            }
        }
        $stmtCat->close();
    }

    // 5) Redirigir con éxito
    header("Location: ../mis_juegos.php?success=edited");
    exit();
}

// 6) GET: cargar todas las categorías y las asignadas
$result = $conn->query("SELECT id_categoria, nombre FROM categorias ORDER BY nombre ASC");
$categorias = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

$stmtAsig = $conn->prepare("
    SELECT id_categoria 
      FROM juegos_categorias 
     WHERE id_juego = ?
");
$stmtAsig->bind_param("i", $id_juego);
$stmtAsig->execute();
$resAsig = $stmtAsig->get_result();
$asignadas = array_column($resAsig->fetch_all(MYSQLI_ASSOC), 'id_categoria');
$stmtAsig->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Editar Juego – GAMEDOM</title>
  <style>
    body { font-family: Arial,sans-serif; background:#f0f2f5; margin:0 }
    .container { max-width:800px; margin:40px auto; background:#fff;
                 padding:20px; border-radius:8px;
                 box-shadow:0 2px 8px rgba(0,0,0,0.1) }
    h1 { color:#333; margin-bottom:20px }
    .form-group { margin-bottom:15px }
    .form-group label { display:block; margin-bottom:5px; color:#555 }
    .form-group input[type="text"],
    .form-group textarea { width:100%; padding:8px; border:1px solid #ccc;
                          border-radius:4px; box-sizing:border-box }
    fieldset { margin-bottom:15px; border:1px solid #ccc;
               border-radius:4px; padding:10px }
    legend { padding:0 5px; font-weight:bold }
    .btn { display:inline-block; padding:10px 16px; background:#007bff;
           color:#fff; text-decoration:none; border:none; border-radius:4px;
           cursor:pointer }
    .btn:hover { background:#0056b3 }
  </style>
</head>
<body>
  <div class="container">
    <h1>Editar Juego</h1>
    <form action="edit_game_dev.php?id=<?= $id_juego ?>" method="POST" enctype="multipart/form-data">
      <div class="form-group">
        <label for="nombre">Nombre del Juego:</label>
        <input type="text" id="nombre" name="nombre"
               value="<?= htmlspecialchars($game['nombre']) ?>" required>
      </div>

      <div class="form-group">
        <label for="descripcion">Descripción:</label>
        <textarea id="descripcion" name="descripcion" rows="4" required><?=
          htmlspecialchars($game['descripcion'])
        ?></textarea>
      </div>

      <div class="form-group">
        <label for="ruta_index">Ruta del Index:</label>
        <input type="text" id="ruta_index" name="ruta_index"
               value="<?= htmlspecialchars($game['ruta_index']) ?>" required>
      </div>

      <div class="form-group">
        <label for="icono">Nuevo Icono (opcional):</label>
        <input type="file" id="icono" name="icono" accept=".jpg,.jpeg,.png">
      </div>

      <div class="form-group">
        <label for="capturas">Nuevas Capturas (opcional):</label>
        <input type="file" id="capturas" name="capturas" accept=".jpg,.jpeg,.png">
      </div>

      <fieldset>
        <legend>Modo de Juego</legend>
        <?php foreach (['Multijugador','Individual','IA'] as $modo): ?>
          <label>
            <input type="checkbox" name="modo_juego[]"
              value="<?= $modo ?>"
              <?= in_array($modo, explode(',', $game['modo_juego'])) ? 'checked' : '' ?>>
            <?= $modo ?>
          </label><br>
        <?php endforeach; ?>
      </fieldset>

      <fieldset>
        <legend>Categorías</legend>
        <?php if ($categorias): ?>
          <?php foreach ($categorias as $cat): ?>
            <label>
              <input type="checkbox" name="categorias[]"
                     value="<?= $cat['id_categoria'] ?>"
                     <?= in_array($cat['id_categoria'], $asignadas) ? 'checked' : '' ?>>
              <?= htmlspecialchars($cat['nombre']) ?>
            </label><br>
          <?php endforeach; ?>
        <?php else: ?>
          <p>No hay categorías disponibles.</p>
        <?php endif; ?>
      </fieldset>

      <button type="submit" class="btn">💾 Guardar Cambios</button>
    </form>
  </div>
</body>
</html>
