<?php
// Importar la conexión a la base de datos
require __DIR__ . '/../../../includes/config/DbConection.php';

// Verificar si el ID del cliente se ha pasado como parámetro POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id_cliente'])) {
        $id = htmlspecialchars($_POST['id_cliente']);
        
        $db = $conectarDB;
        
        $sql = "DELETE FROM cliente WHERE id_cliente = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i', $id);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Cliente eliminado correctamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al eliminar el cliente: ' . $stmt->error]);
        }

        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No se ha proporcionado un ID de cliente.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
}