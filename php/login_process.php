<?php
// php/login_process.php

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    require_once "db_connect.php";

    $user = $_POST["username"] ?? '';
    $pass = $_POST["password"] ?? '';

    // Sentencia SQL para validar usuario/correo y contraseña
    $sql = "SELECT * FROM usuarios
            WHERE (usuario = ? OR correo = ?)
            AND password = ?
            LIMIT 1";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error en prepare(): " . $conn->error);
    }

    $stmt->bind_param("sss", $user, $user, $pass);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        // Login correcto: redirige a index.html
        header("Location: ../html/index.html");
        exit();
    } else {
        // Credenciales inválidas: redirige al login con error
        header("Location: ../html/login.html?error=1");
        exit();
    }

    $stmt->close();
    $conn->close();
} else {
    // Si no se accede por POST, redirige al login
    header("Location: ../html/login.html");
    exit();
}
