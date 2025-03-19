<?php
// php/register_process.php

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    session_start(); // Iniciamos la sesión
    require_once "db_connect.php";

    // Recoger y limpiar los datos del formulario
    $email    = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm  = trim($_POST['confirmPassword'] ?? '');

    // 1. Verificar que ambas contraseñas sean iguales
    if ($password !== $confirm) {
        header("Location: ../registro.html?error=password");
        exit();
    }

    // 2. Verificar que el correo o el usuario no existan ya en la base de datos
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE correo = ? OR usuario = ?");
    if (!$stmt) {
        die("Error en prepare(): " . $conn->error);
    }
    $stmt->bind_param("ss", $email, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        // El correo o el usuario ya existen
        header("Location: ../registro.html?error=exists");
        exit();
    }
    $stmt->close();

    // 3. Hashear la contraseña
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Como los demás datos se completarán en la sección de perfil,
    // insertamos valores vacíos para nombre y apellidos.
    $nombre    = "";
    $apellidos = "";

    // 4. Insertar el nuevo usuario en la base de datos
    $stmt = $conn->prepare("INSERT INTO usuarios (correo, usuario, nombre, apellidos, password) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) {
        die("Error en prepare(): " . $conn->error);
    }
    $stmt->bind_param("sssss", $email, $username, $nombre, $apellidos, $hashed_password);

    if ($stmt->execute()) {
        // Registro exitoso, iniciamos sesión automáticamente
        $_SESSION['usuario'] = $username; // Puedes usar el correo o el usuario como identificador
        header("Location: ../index.php");
        exit();
    } else {
        // Error al insertar el usuario
        header("Location: ../registro.html?error=insert");
        exit();
    }

    $stmt->close();
    $conn->close();
} else {
    // Si se accede al script sin POST, redirigimos al formulario de registro
    header("Location: ../registro.html");
    exit();
}
?>
