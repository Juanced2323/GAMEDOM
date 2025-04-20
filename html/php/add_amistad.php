<?php
session_start();
require_once "db_connect.php";

// Verificar que el usuario esté logueado
if (!isset($_SESSION['correo'])) {
  http_response_code(401);
  echo json_encode(["error" => "No autenticado"]);
  exit();
}

$solicitante = $_SESSION['correo'];
$destinatario = filter_input(INPUT_POST, 'correo', FILTER_VALIDATE_EMAIL);

if (!$destinatario || $solicitante === $destinatario) {
  http_response_code(400);
  echo json_encode(["error" => "Correo inválido o no permitido"]);
  exit();
}

// Verificar que el destinatario exista
$stmt = $conn->prepare("SELECT correo FROM usuarios WHERE correo = ?");
$stmt->bind_param("s", $destinatario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  http_response_code(404);
  echo json_encode(["error" => "Usuario no encontrado"]);
  exit();
}
$stmt->close();

// Verificar si ya hay una solicitud existente
$stmt = $conn->prepare("SELECT id FROM amistades WHERE (solicitante = ? AND destinatario = ?) OR (solicitante = ? AND destinatario = ?)");
$stmt->bind_param("ssss", $solicitante, $destinatario, $destinatario, $solicitante);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
  http_response_code(409);
  echo json_encode(["error" => "Ya existe una solicitud o amistad"]);
  exit();
}
$stmt->close();

// Insertar solicitud
$stmt = $conn->prepare("INSERT INTO amistades (solicitante, destinatario) VALUES (?, ?)");
$stmt->bind_param("ss", $solicitante, $destinatario);
if ($stmt->execute()) {
  echo json_encode(["success" => true]);
} else {
  http_response_code(500);
  echo json_encode(["error" => "Error al enviar solicitud"]);
}
$stmt->close();
$conn->close();
