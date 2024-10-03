<?php
// Importar la conexión a la base de datos
require __DIR__ . '/../../../includes/config/DbConection.php';

if (isset($_GET['id'])) {
    $id_usuario = htmlspecialchars($_GET['id']);
    
    $db = $conectarDB;

    // Definir los accesos posibles
    $accesosPosibles = [
        'administrar_usuarios',
        'administrar_roles',
        'administrar_accesos',
        'administrar_ventas',
        'configurar_perfil',
        'administrar_reportes',
        'administrar_caja',
        'administrar_inventario',
        'administrar_clientes',
        'administrar_configuracion',
        'administrar_movimientos',
        'administrar_permisos',
        'administrar_productos'
    ];

    // Obtener los accesos asignados al usuario
    $sql = "SELECT * FROM acceso WHERE id_usuario = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param('i', $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $accesosUsuario = [];
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        foreach ($accesosPosibles as $acceso) {
            $accesosUsuario[$acceso] = $row[$acceso] ?? 0;
        }
    } else {
        foreach ($accesosPosibles as $acceso) {
            $accesosUsuario[$acceso] = 0;
        }
    }
    
    foreach ($accesosUsuario as $acceso => $valor) {
        echo '<div class="form-check">';
        echo '<input class="form-check-input" type="checkbox" name="' . htmlspecialchars($acceso) . '" id="' . htmlspecialchars($acceso) . '" value="1" ' . ($valor ? 'checked' : '') . '>';
        echo '<label class="form-check-label" for="' . htmlspecialchars($acceso) . '">' . ucwords(str_replace('_', ' ', $acceso)) . '</label>';
        echo '</div>';
    }
    
    $stmt->close();
} else {
    echo 'ID de usuario no proporcionado.';
}
