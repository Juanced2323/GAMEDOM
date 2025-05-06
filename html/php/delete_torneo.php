<?php
require_once "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["id_torneo"])) {
    $id = intval($_POST["id_torneo"]);

    $stmt = $conn->prepare("DELETE FROM torneos WHERE id_torneo = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: ../admin_torneos.php?deleted=1");
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>
