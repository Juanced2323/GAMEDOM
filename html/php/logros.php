<?php
// php/logros.php
require_once "db_connect.php"; // Asegúrate de que $conn esté disponible

/**
 * Asigna un logro al usuario si aún no lo tiene.
 *
 * @param string $usuario El nombre de usuario.
 * @param int $id_logro El ID del logro a asignar.
 */
function asignarLogro($usuario, $id_logro) {
    global $conn;

    // Verificar si el usuario ya tiene el logro
    $stmt = $conn->prepare("SELECT * FROM usuarios_logros WHERE usuario = ? AND id_logro = ?");
    $stmt->bind_param("si", $usuario, $id_logro);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        // Si no lo tiene, lo asignamos
        $stmtInsert = $conn->prepare("INSERT INTO usuarios_logros (usuario, id_logro) VALUES (?, ?)");
        $stmtInsert->bind_param("si", $usuario, $id_logro);
        $stmtInsert->execute();
        $stmtInsert->close();
    }
    $stmt->close();
}
?>
