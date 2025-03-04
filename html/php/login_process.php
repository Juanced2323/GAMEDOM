<?php
// php/login_process.php

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    require_once "db_connect.php";

    $user = $_POST["username"] ?? '';
    $pass = $_POST["password"] ?? '';

    // Consulta: obtenemos el registro basándonos en usuario o correo
    $sql = "SELECT * FROM usuarios
            WHERE (usuario = ? OR correo = ?)
            LIMIT 1";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error en prepare(): " . $conn->error);
    }

    $stmt->bind_param("ss", $user, $user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        // Obtenemos la fila
        $row = $result->fetch_assoc();
        // Verificamos la contraseña usando password_verify()
        if (password_verify($pass, $row['password'])) {
            // Login correcto, redirige a index.html
            header("Location: ../index.html");
            exit();
        } else {
            // La contraseña no coincide
            header("Location: ../login.html?error=1");
            exit();
        }
    } else {
        // No se encontró usuario
        header("Location: ../login.html?error=1");
        exit();
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: ../login.html");
    exit();
}
