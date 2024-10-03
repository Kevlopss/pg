<?php
// Importar la conexión a la base de datos
require __DIR__ . '/../../../includes/config/DbConection.php';
$db = $conectarDB;

// Verificar si ya hay una caja abierta
$estadoCajaQuery = $db->query("SELECT * FROM caja WHERE estado = 'abierta' ORDER BY fecha_apertura DESC LIMIT 1");
$estadoCaja = $estadoCajaQuery->fetch_assoc();

if ($estadoCaja) {
    $response = [
        'status' => 'error',
        'message' => 'Ya hay una caja abierta.'
    ];
} else {
    // Obtener el monto inicial desde la solicitud
    $montoInicial = isset($_POST['monto_inicial']) ? floatval($_POST['monto_inicial']) : 0.00;
    $idUsuario = isset($_POST['id_usuario']) ? intval($_POST['id_usuario']) : null;

    // Insertar una nueva caja
    $sql = "INSERT INTO caja (fecha_apertura, monto_inicial, estado, id_usuario) VALUES (NOW(), ?, 'abierta', ?)";
    $stmt = $db->prepare($sql);
    $stmt->bind_param('di', $montoInicial, $idUsuario);

    if ($stmt->execute()) {
        $response = [
            'status' => 'success',
            'message' => 'Caja abierta correctamente.',
            'data' => [
                'id_caja' => $stmt->insert_id,
                'fecha_apertura' => date('Y-m-d H:i:s'),
                'monto_inicial' => $montoInicial
            ]
        ];
    } else {
        $response = [
            'status' => 'error',
            'message' => 'Error al abrir la caja: ' . $stmt->error
        ];
    }

    $stmt->close();
}

// Devolver la respuesta en formato JSON
header('Content-Type: application/json');
echo json_encode($response);