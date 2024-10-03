<?php
require __DIR__ . '/../../../includes/config/DbConection.php';

$db = $conectarDB;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = htmlspecialchars($_POST['id']);

    $sql = "DELETE FROM abogado_asistente WHERE id_Abte = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param('i', $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Configuración eliminada correctamente.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al eliminar la configuración: ' . $stmt->error]);
    }

    $stmt->close();
    exit;
}
?>