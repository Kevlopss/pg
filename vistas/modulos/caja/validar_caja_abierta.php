<?php
// Importar la conexión a la base de datos
require __DIR__ . '/../../../includes/config/DbConection.php';
$db = $conectarDB;

// Verificar el estado de la caja
$estadoCajaQuery = $db->query("SELECT * FROM caja WHERE estado = 'abierta' ORDER BY fecha_apertura DESC LIMIT 1");
$estadoCaja = $estadoCajaQuery->fetch_assoc();

if ($estadoCaja) {
    $response = [
        'status' => 'success',
        'message' => 'La caja está abierta.',
        'data' => [
            'id_caja' => $estadoCaja['id_caja'],
            'fecha_apertura' => $estadoCaja['fecha_apertura'],
            'monto_inicial' => $estadoCaja['monto_inicial']
        ]
    ];
} else {
    $response = [
        'status' => 'error',
        'message' => 'No hay ninguna caja abierta.'
    ];
}

// Devolver la respuesta en formato JSON
header('Content-Type: application/json');
echo json_encode($response);