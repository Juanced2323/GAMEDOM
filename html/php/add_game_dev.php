<?php
session_start();

// db_connect.php vive en la misma carpeta
require_once __DIR__ . '/db_connect.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: ../login.html");
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
    die("❌ No tienes perfil de desarrollador configurado.");
}
$id_desarrollador = (int)$rowDev['id_desarrollador'];
$stmtDev->close();

// 2) Procesar formulario en POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Consulta segura de campos
    $nombre      = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $ruta_index  = trim($_POST['ruta_index'] ?? '');

    // Modo de juego
    $modo_array = $_POST['modo_juego'] ?? [];
    $modo_juego = implode(",", $modo_array);

    // Icono (opcional)
    $iconoData = null;
    if (!empty($_FILES['icono']['tmp_name']) && $_FILES['icono']['error'] === UPLOAD_ERR_OK) {
        $iconoData = file_get_contents($_FILES['icono']['tmp_name']);
    }
    // Capturas (opcional)
    $capturasData = null;
    if (!empty($_FILES['capturas']['tmp_name']) && $_FILES['capturas']['error'] === UPLOAD_ERR_OK) {
        $capturasData = file_get_contents($_FILES['capturas']['tmp_name']);
    }

    // 3) Insertar en 'juegos'
    $stmtJ = $conn->prepare("
        INSERT INTO juegos
          (nombre, descripcion, ruta_index, modo_juego, icono, capturas)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    if (!$stmtJ) {
        die("Error en prepare juegos: " . $conn->error);
    }
    $stmtJ->bind_param(
        "ssssss",
        $nombre,
        $descripcion,
        $ruta_index,
        $modo_juego,
        $iconoData,
        $capturasData
    );
    if (!$stmtJ->execute()) {
        die("Error al insertar juego: " . $stmtJ->error);
    }
    $id_juego = $conn->insert_id;
    $stmtJ->close();

    // 4) Relacionar desarrollador ↔ juego
    $stmtRel = $conn->prepare("
        INSERT INTO desarrolladores_juegos
          (id_desarrollador, id_juego)
        VALUES (?, ?)
    ");
    if (!$stmtRel) {
        die("Error en prepare relación: " . $conn->error);
    }
    $stmtRel->bind_param("ii", $id_desarrollador, $id_juego);
    if (!$stmtRel->execute()) {
        die("Error al relacionar juego con desarrollador: " . $stmtRel->error);
    }
    $stmtRel->close();

    // 5) Asignar categorías si hay
    if (!empty($_POST['categorias']) && is_array($_POST['categorias'])) {
        $stmtCat = $conn->prepare("
            INSERT INTO juegos_categorias
              (id_juego, id_categoria)
            VALUES (?, ?)
        ");
        if (!$stmtCat) {
            die("Error en prepare categorías: " . $conn->error);
        }
        foreach ($_POST['categorias'] as $cat) {
            $cat_id = intval($cat);
            $stmtCat->bind_param("ii", $id_juego, $cat_id);
            if (!$stmtCat->execute()) {
                die("Error al insertar categoría: " . $stmtCat->error);
            }
        }
        $stmtCat->close();
    }

    // 6) Volver a la lista con éxito
    header("Location: ../mis_juegos.php?success=1");
    exit();
}

// 7) GET: cargar categorías para el formulario
$result = $conn->query("SELECT id_categoria, nombre FROM categorias ORDER BY nombre ASC");
$categorias = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Publicar Nuevo Juego – GAMEDOM</title>
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
    .btn { display:inline-block; padding:10px 16px;
           background:#007bff; color:#fff; text-decoration:none;
           border:none; border-radius:4px; cursor:pointer }
    .btn:hover { background:#0056b3 }
  </style>
</head>
<body>
  <div class="container">
    <h1>Publicar Nuevo Juego</h1>
    <form action="add_game_dev.php" method="POST" enctype="multipart/form-data">
      <div class="form-group">
        <label for="nombre">Nombre del Juego:</label>
        <input type="text" id="nombre" name="nombre" required>
      </div>

      <div class="form-group">
        <label for="descripcion">Descripción:</label>
        <textarea id="descripcion" name="descripcion" rows="4" required></textarea>
      </div>

      <div class="form-group">
        <label for="ruta_index">Ruta del Index:</label>
        <input type="text" id="ruta_index" name="ruta_index"
               placeholder="e.g., games/MiJuego/index.html" required>
      </div>

      <div class="form-group">
        <label for="icono">Icono (JPG, PNG):</label>
        <input type="file" id="icono" name="icono" accept=".jpg,.jpeg,.png">
      </div>

      <div class="form-group">
        <label for="capturas">Capturas (JPG, PNG):</label>
        <input type="file" id="capturas" name="capturas" accept=".jpg,.jpeg,.png">
      </div>

      <fieldset>
        <legend>Modo de Juego</legend>
        <label><input type="checkbox" name="modo_juego[]" value="Multijugador"> Multijugador</label><br>
        <label><input type="checkbox" name="modo_juego[]" value="Individual"> Individual</label><br>
        <label><input type="checkbox" name="modo_juego[]" value="IA"> IA</label>
      </fieldset>

      <fieldset>
        <legend>Categorías</legend>
        <?php if (!empty($categorias)): ?>
          <?php foreach ($categorias as $cat): ?>
            <label><input type="checkbox" name="categorias[]" value="<?= $cat['id_categoria'] ?>">
              <?= htmlspecialchars($cat['nombre']) ?>
            </label><br>
          <?php endforeach; ?>
        <?php else: ?>
          <p>No hay categorías disponibles.</p>
        <?php endif; ?>
      </fieldset>

      <button type="submit" class="btn">✅ Publicar Juego</button>
    </form>
  </div>
</body>
</html>
