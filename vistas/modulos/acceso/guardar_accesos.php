<?php
// Importar la conexión a la base de datos
require __DIR__ . '/../../../includes/config/DbConection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario = htmlspecialchars($_POST['id_usuario']);

    // Accesos posibles
    $accesos = [
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

    $db = $conectarDB;

    // Iniciar la transacción
    $db->begin_transaction();

    try {
        // Verificar si el usuario ya tiene un registro en la tabla acceso
        $sqlCheck = "SELECT * FROM acceso WHERE id_usuario = ?";
        $stmtCheck = $db->prepare($sqlCheck);
        $stmtCheck->bind_param('i', $id_usuario);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->get_result();
        
        if ($resultCheck->num_rows > 0) {
            // Actualizar los accesos existentes
            foreach ($accesos as $acceso) {
                $valor = isset($_POST[$acceso]) ? 1 : 0;
                $sql = "UPDATE acceso SET $acceso = ? WHERE id_usuario = ?";
                $stmt = $db->prepare($sql);
                $stmt->bind_param('ii', $valor, $id_usuario);
                $stmt->execute();
                $stmt->close();
            }
        } else {
            // Insertar un nuevo registro con los accesos
            $campos = implode(", ", $accesos);
            $valores = implode(", ", array_fill(0, count($accesos), '?'));
            $sql = "INSERT INTO acceso (id_usuario, $campos) VALUES (?, $valores)";
            $stmt = $db->prepare($sql);

            $params = [$id_usuario];
            foreach ($accesos as $acceso) {
                $params[] = isset($_POST[$acceso]) ? 1 : 0;
            }

            $stmt->bind_param(str_repeat('i', count($params)), ...$params);
            $stmt->execute();
            $stmt->close();
        }
        
        // Confirmar la transacción
        $db->commit();
        echo 'Accesos actualizados correctamente.';
    } catch (Exception $e) {
        // Revertir la transacción
        $db->rollback();
        echo 'Error al actualizar los accesos: ' . $e->getMessage();
    }
}