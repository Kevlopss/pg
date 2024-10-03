<?php
require __DIR__ . '/../../../includes/config/DbConection.php';

$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
if ($contentType === "application/json") {
    $content = trim(file_get_contents("php://input"));
    $datos = json_decode($content, true);

    if (is_array($datos) && isset($datos['usuario'])) {
        $user = $datos['usuario'];

        if (isset($datos['detalles']) && is_array($datos['detalles'])) {
            $db = $conectarDB;

            try {
                $db->begin_transaction(); // Iniciar una transacción

                $fecha = $datos['fecha'];
                $total = $datos['total'];
                $detallesVenta = $datos['detalles'];

                // Insertar venta
                $insertVenta = "INSERT INTO ventas (fecha, total) VALUES (?, ?)";
                $stmtVenta = $db->prepare($insertVenta);
                if (!$stmtVenta) {
                    throw new Exception("Error en la preparación de la consulta de ventas: " . $db->error);
                }
                $stmtVenta->bind_param('sd', $fecha, $total);
                $stmtVenta->execute();
                $ventaId = $db->insert_id;
                $stmtVenta->close();

                // Preparar las consultas de detalles de venta y actualización de inventario
                $insertDetalleVenta = "INSERT INTO detalles_de_ventas (venta_id, producto_id, cantidad, precio_unitario, total) VALUES (?, ?, ?, ?, ?)";
                $stmtDetalleVenta = $db->prepare($insertDetalleVenta);
                if (!$stmtDetalleVenta) {
                    throw new Exception("Error en la preparación de la consulta de detalles de ventas: " . $db->error);
                }

                $updateInventario = "UPDATE inventario SET stock = stock - ? WHERE Id_producto = ?";
                $stmtUpdateInventario = $db->prepare($updateInventario);
                if (!$stmtUpdateInventario) {
                    throw new Exception("Error en la preparación de la consulta de inventario: " . $db->error);
                }

                foreach ($detallesVenta as $detalle) {
                    $Cod = $detalle['producto_id'];
                    $sql = "SELECT * FROM producto WHERE Cod_producto = ?";
                    $stmt = $db->prepare($sql);
                    if (!$stmt) {
                        throw new Exception("Error en la preparación de la consulta del producto: " . $db->error);
                    }
                    $stmt->bind_param('s', $Cod);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $producto = $result->fetch_assoc();
                    $stmt->close();

                    if ($producto) {
                        $productoId = $producto['Id_producto'];
                        $cantidad = $detalle['cantidad'];
                        $precioUnitario = $detalle['precio_unitario'];
                        $totalProducto = $detalle['total'];

                        $stmtDetalleVenta->bind_param('iiidd', $ventaId, $productoId, $cantidad, $precioUnitario, $totalProducto);
                        $stmtDetalleVenta->execute();

                        $stmtUpdateInventario->bind_param('ii', $cantidad, $productoId);
                        $stmtUpdateInventario->execute();

                    } else {
                        throw new Exception("Producto con código $Cod no encontrado.");
                    }
                }

                $stmtDetalleVenta->close();
                $stmtUpdateInventario->close();
                $db->commit(); // Confirmar la transacción
                $db->close();

                echo json_encode(['status' => 'success', 'message' => 'Venta registrada exitosamente.']);

            } catch (Exception $e) {
                $db->rollback(); // Revertir la transacción en caso de error
                echo json_encode(['status' => 'error', 'message' => 'Error al registrar la venta: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(["error" => "Invalid data format"]);
        }
    } else {
        echo json_encode(["error" => "Invalid data format"]);
    }
} else {
    echo json_encode(["error" => "Content-Type must be application/json"]);
}
?>
