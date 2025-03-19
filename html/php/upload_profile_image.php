<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../login.html");
    exit();
}

require_once "db_connect.php";

// Verificamos que se haya subido un archivo correctamente
if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
    header("Location: ../perfil.php?error=upload");
    exit();
}

// Validar extensión (por ejemplo, permitir solo jpg/jpeg y png)
$allowed_extensions = ['jpg', 'jpeg', 'png'];
$file_name = $_FILES['imagen']['name'];
$file_tmp = $_FILES['imagen']['tmp_name'];
$extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

if (!in_array($extension, $allowed_extensions)) {
    header("Location: ../perfil.php?error=extension");
    exit();
}

// Validar tamaño máximo (por ejemplo, 2MB)
$max_size = 2 * 1024 * 1024; // 2 MB
if ($_FILES['imagen']['size'] > $max_size) {
    header("Location: ../perfil.php?error=size");
    exit();
}

// Leer el contenido del archivo
$imageData = file_get_contents($file_tmp);

// Actualizar la columna 'imagen' en la base de datos
$username = $_SESSION['usuario'];
$stmt = $conn->prepare("UPDATE usuarios SET imagen = ? WHERE usuario = ?");
if (!$stmt) {
    die("Error en prepare(): " . $conn->error);
}
// Usamos "s" para pasar los datos binarios como cadena
$stmt->bind_param("ss", $imageData, $username);

if ($stmt->execute()) {
    header("Location: ../perfil.php?success=img");
    exit();
} else {
    header("Location: ../perfil.php?error=update");
    exit();
}
?>
