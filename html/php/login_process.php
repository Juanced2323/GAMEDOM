<?php
session_start();
require_once "db_connect.php";

$user = $_POST["username"] ?? '';
$pass = $_POST["password"] ?? '';

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
    $row = $result->fetch_assoc();
    // Verifica la contraseña usando password_verify()
    if (password_verify($pass, $row['password'])) {
        // Login correcto: crea la sesión
        $_SESSION['usuario'] = $row['usuario']; // nombre de usuario real desde la BD
        $_SESSION['correo'] = $row['correo'];   // correo real desde la BD
        header("Location: ../index.php");
        exit();
    } else {
        header("Location: ../login.html?error=1");
        exit();
    }
} else {
    header("Location: ../login.html?error=1");
    exit();
}

$stmt->close();
$conn->close();
?>
