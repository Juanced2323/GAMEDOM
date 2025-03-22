<?php
session_start();
require_once "db_connect.php";

$nombre = trim($_POST['nombre'] ?? '');

if(empty($nombre)){
    echo "El nombre de la categoría es obligatorio.";
    exit();
}

$stmt = $conn->prepare("INSERT INTO categorias (nombre) VALUES (?)");
if(!$stmt){
    die("Error en prepare: " . $conn->error);
}
$stmt->bind_param("s", $nombre);
if($stmt->execute()){
    header("Location: ../admin_categorias.php?success=1");
    exit();
} else {
    echo "Error al agregar la categoría: " . $stmt->error;
}
$stmt->close();
$conn->close();
?>
