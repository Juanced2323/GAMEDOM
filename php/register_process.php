<?php
// php/register_process.php

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    require_once "db_connect.php";

    $email     = trim($_POST['email'] ?? '');
    $username  = trim($_POST['username'] ?? '');
    $nombre    = trim($_POST['nombre'] ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $edad      = intval($_POST['edad'] ?? 0);
    $telefono  = trim($_POST['telefono'] ?? '');
    $password  = trim($_POST['password'] ?? '');
    $confirm   = trim($_POST['confirmPassword'] ?? '');

    // 1. Verificar contraseñas
    if ($password !== $confirm) {
        header("Location: ../html/registro.html?error=password");
        exit();
    }

    // 2. Verificar que no exista ya el correo o el usuario
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE correo = ? OR usuario = ?");
    if (!$stmt) {
        die("Error en prepare(): " . $conn->error);
    }
    $stmt->bind_param("ss", $email, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        // El correo o el usuario ya existe
        header("Location: ../html/registro.html?error=exists");
        exit();
    }
    $stmt->close();

    // (Opcional) Hashear la contraseña
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // 3. Insertar en la tabla usuarios
    $stmt = $conn->prepare("INSERT INTO usuarios (correo, usuario, nombre, apellidos, edad, telefono, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        die("Error en prepare(): " . $conn->error);
    }
    // "sssisss" → s=correo, s=usuario, s=nombre, i=edad, s=telefono, s=password
    $stmt->bind_param("sssisss", $email, $username, $nombre, $apellidos, $edad, $telefono, $hashed_password);

    if ($stmt->execute()) {
        // Registro exitoso: redirige a index.html
        header("Location: ../html/index.html");
        exit();
    } else {
        // Error al insertar, redirige con un error genérico
        header("Location: ../html/registro.html?error=insert");
        exit();
    }

    $stmt->close();
    $conn->close();
} else {
    // Si no se accede por POST, redirigimos a registro
    header("Location: ../html/registro.html");
    exit();
}

