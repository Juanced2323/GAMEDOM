<?php
session_start();
// Opcional: Verificar permisos de administrador

require_once "db_connect.php";

// Recoger y limpiar los datos del formulario
$nombre      = trim($_POST['nombre'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$ruta_index  = trim($_POST['ruta_index'] ?? '');

// Procesar el archivo del icono
if (isset($_FILES['icono']) && $_FILES['icono']['error'] === UPLOAD_ERR_OK) {
    $iconoContent = file_get_contents($_FILES['icono']['tmp_name']);
} else {
    die("Error al subir el icono.");
}

// Procesar el archivo de capturas (opcional)
$capturasContent = null;
if (isset($_FILES['capturas']) && $_FILES['capturas']['error'] === UPLOAD_ERR_OK) {
    $capturasContent = file_get_contents($_FILES['capturas']['tmp_name']);
}

// Procesar el modo de juego: unir los valores seleccionados en una cadena separada por comas
$modo_juego_array = $_POST['modo_juego'] ?? [];
$modo_juego = '';
if (!empty($modo_juego_array) && is_array($modo_juego_array)) {
    $modo_juego = implode(",", $modo_juego_array);
}

// Insertar el nuevo juego en la tabla juegos (incluyendo modo_juego y capturas)
$stmt = $conn->prepare("INSERT INTO juegos (nombre, icono, descripcion, ruta_index, modo_juego, capturas) VALUES (?, ?, ?, ?, ?, ?)");
if (!$stmt) {
    die("Error en prepare: " . $conn->error);
}
$stmt->bind_param("ssssss", $nombre, $iconoContent, $descripcion, $ruta_index, $modo_juego, $capturasContent);
if (!$stmt->execute()) {
    die("Error al insertar el juego: " . $stmt->error);
}

// Obtener el id insertado para usarlo en la tabla pivot
$id_juego = $conn->insert_id;
$stmt->close();

// Procesar las categorías seleccionadas (si existen)
if (isset($_POST['categorias']) && is_array($_POST['categorias'])) {
    $stmt = $conn->prepare("INSERT INTO juegos_categorias (id_juego, id_categoria) VALUES (?, ?)");
    if (!$stmt) {
        die("Error en prepare (categorías): " . $conn->error);
    }
    foreach ($_POST['categorias'] as $id_categoria) {
        $id_categoria = intval($id_categoria);
        $stmt->bind_param("ii", $id_juego, $id_categoria);
        if (!$stmt->execute()) {
            die("Error al insertar categoría: " . $stmt->error);
        }
    }
    $stmt->close();
}

$conn->close();

// Redirigir al panel de administración de juegos con un mensaje de éxito
header("Location: ../admin_juegos.php?success=1");
exit();
?>
