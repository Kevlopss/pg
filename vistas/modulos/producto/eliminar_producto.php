<?php
// Importar la conexión a la base de datos
require __DIR__ . '/../../../includes/config/DbConection.php';

// Verificar si el ID del usuario se ha pasado como parámetro POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id'])) {
        $id = htmlspecialchars($_POST['id']);
        
        $db = $conectarDB;
        
        $sql = "DELETE FROM precio_producto WHERE Id_producto = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i', $id);
        $result1= $stmt->execute();
        $sql2 = "DELETE FROM producto WHERE Id_producto = ?";
        $stmt2 = $db->prepare($sql2);
        $stmt2->bind_param('i', $id);
        $resutl2 = $stmt2->execute();
        if ($result1 && $resutl2) {
            echo json_encode(['status' => 'success', 'message' => 'Producto eliminado correctamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al eliminar el usuario: ' . $stmt->error]);
        }

        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No se ha proporcionado un ID de producto.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
}
?>