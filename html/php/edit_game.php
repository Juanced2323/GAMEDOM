<?php
session_start();
// Opcional: Verificar permisos de administrador

require_once "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['id_juego'])) {
    header("Location: ../admin_juegos.php");
    exit();
}

$id_juego = intval($_POST['id_juego']);
$nombre = trim($_POST['nombre'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$ruta_index = trim($_POST['ruta_index'] ?? '');

// Procesar modo_juego
$modo_juego_array = $_POST['modo_juego'] ?? [];
$modo_juego = '';
if (!empty($modo_juego_array) && is_array($modo_juego_array)) {
    $modo_juego = implode(",", $modo_juego_array);
}

// Procesar icono (opcional)
$iconoContent = null;
if (isset($_FILES['icono']) && $_FILES['icono']['error'] === UPLOAD_ERR_OK) {
    $iconoContent = file_get_contents($_FILES['icono']['tmp_name']);
}

// Procesar capturas (opcional)
$capturasContent = null;
if (isset($_FILES['capturas']) && $_FILES['capturas']['error'] === UPLOAD_ERR_OK) {
    $capturasContent = file_get_contents($_FILES['capturas']['tmp_name']);
}

// Construir la consulta UPDATE de forma dinámica
$sql = "UPDATE juegos 
        SET nombre = ?, descripcion = ?, ruta_index = ?, modo_juego = ?";
$types = "ssss";
$params = [$nombre, $descripcion, $ruta_index, $modo_juego];

// Si se sube un nuevo icono, actualizamos icono
if ($iconoContent !== null) {
    $sql .= ", icono = ?";
    $types .= "s";
    $params[] = $iconoContent;
}

// Si se suben nuevas capturas, actualizamos capturas
if ($capturasContent !== null) {
    $sql .= ", capturas = ?";
    $types .= "s";
    $params[] = $capturasContent;
}

// Agregamos la condición WHERE
$sql .= " WHERE id_juego = ?";
$types .= "i";
$params[] = $id_juego;

// Preparar la consulta
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error en prepare: " . $conn->error);
}

// Hacer bind_param de forma dinámica
$stmt->bind_param($types, ...$params);

if (!$stmt->execute()) {
    die("Error al actualizar el juego: " . $stmt->error);
}
$stmt->close();

// Actualizar categorías en la tabla pivot
// 1. Borrar las categorías antiguas
$stmt = $conn->prepare("DELETE FROM juegos_categorias WHERE id_juego = ?");
$stmt->bind_param("i", $id_juego);
$stmt->execute();
$stmt->close();

// 2. Insertar las nuevas (si existen)
if (isset($_POST['categorias']) && is_array($_POST['categorias'])) {
    $stmt = $conn->prepare("INSERT INTO juegos_categorias (id_juego, id_categoria) VALUES (?, ?)");
    if (!$stmt) {
        die("Error en prepare (categorías): " . $conn->error);
    }
    foreach ($_POST['categorias'] as $cat_id) {
        $cat_id = intval($cat_id);
        $stmt->bind_param("ii", $id_juego, $cat_id);
        if (!$stmt->execute()) {
            die("Error al insertar categoría: " . $stmt->error);
        }
    }
    $stmt->close();
}

$conn->close();
header("Location: ../admin_juegos.php?success=updated");
exit();
?>
