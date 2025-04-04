<?php
require_once "db_connect.php";

// Función que asigna un logro al usuario
function asignarLogro($usuario, $nombre_logro){
    global $conn;

    // Comprobar si ya tiene el logro
    $stmt = $conn->prepare("SELECT id_logro FROM logros WHERE nombre = ?");
    $stmt->bind_param("s", $nombre_logro);
    $stmt->execute();
    $result = $stmt->get_result();
    $logro = $result->fetch_assoc();
    $stmt->close();

    if(!$logro) return; // Logro no encontrado

    // Comprobar si el usuario ya tiene ese logro
    $stmt = $conn->prepare("SELECT * FROM usuarios_logros WHERE usuario = ? AND id_logro = ?");
    $stmt->bind_param("si", $usuario, $logro['id_logro']);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows > 0){
        $stmt->close();
        return; // Ya lo tiene
    }
    $stmt->close();

    // Asignar el logro al usuario
    $stmt = $conn->prepare("INSERT INTO usuarios_logros (usuario, id_logro) VALUES (?, ?)");
    $stmt->bind_param("si", $usuario, $logro['id_logro']);
    $stmt->execute();
    $stmt->close();
}
?>
