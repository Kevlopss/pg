<?php
// Importar la conexión a la base de datos
require __DIR__ . '/../../../includes/config/DbConection.php';

// Verificar si el ID del usuario se ha pasado como parámetro POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id_usuario'])) {
        $id = htmlspecialchars($_POST['id_usuario']);
        
        $db = $conectarDB;
        
        $sql = "DELETE FROM usuario WHERE id_usuario = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i', $id);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Usuario eliminado correctamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al eliminar el usuario: ' . $stmt->error]);
        }

        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No se ha proporcionado un ID de usuario.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
}
