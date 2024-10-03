<?php
// Importar la conexión a la base de datos
require __DIR__ . '/../../../includes/config/DbConection.php';
$db = $conectarDB;

// Verificar si la caja está abierta
$estadoCajaQuery = $db->query("SELECT * FROM caja WHERE estado = 'abierta' ORDER BY fecha_apertura DESC LIMIT 1");
$estadoCaja = $estadoCajaQuery->fetch_assoc();

if ($estadoCaja) {
    $idCaja = $estadoCaja['id_caja'];

    // Calcular el monto final de la caja
    $saldoApertura = $estadoCaja['monto_inicial'];
    $ventasTotales = $db->query("SELECT SUM(monto) AS total FROM transaccion WHERE id_caja = {$idCaja} AND tipo = 'venta'")->fetch_assoc()['total'];
    $retirosTotales = $db->query("SELECT SUM(monto) AS total FROM transaccion WHERE id_caja = {$idCaja} AND tipo = 'retiro'")->fetch_assoc()['total'];
    $depositosTotales = $db->query("SELECT SUM(monto) AS total FROM transaccion WHERE id_caja = {$idCaja} AND tipo = 'deposito'")->fetch_assoc()['total'];
    $saldoCierre = $saldoApertura + $ventasTotales - $retirosTotales + $depositosTotales;

    // Actualizar la caja para cerrarla
    $sql = "UPDATE caja SET fecha_cierre = NOW(), monto_final = ?, estado = 'cerrada' WHERE id_caja = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param('di', $saldoCierre, $idCaja);

    if ($stmt->execute()) {
        $response = [
            'status' => 'success',
            'message' => 'Caja cerrada correctamente.',
            'data' => [
                'id_caja' => $idCaja,
                'fecha_cierre' => date('Y-m-d H:i:s'),
                'monto_final' => $saldoCierre
            ]
        ];
    } else {
        $response = [
            'status' => 'error',
            'message' => 'Error al cerrar la caja: ' . $stmt->error
        ];
    }

    $stmt->close();
} else {
    $response = [
        'status' => 'error',
        'message' => 'No hay ninguna caja abierta.'
    ];
}

// Devolver la respuesta en formato JSON
header('Content-Type: application/json');
echo json_encode($response);
