<?php
// Importar la conexión a la base de datos
require __DIR__ . '/../../../includes/config/DbConection.php';

// Verificar si el ID del rol se ha pasado como parámetro POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id_rol'])) {
        $id = htmlspecialchars($_POST['id_rol']);
        
        $db = $conectarDB;
        
        $sql = "DELETE FROM rol WHERE id_rol = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i', $id);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Rol eliminado correctamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al eliminar el rol: ' . $stmt->error]);
        }

        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No se ha proporcionado un ID de rol.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
}
