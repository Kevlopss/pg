<?php
require __DIR__ . '/../../../includes/config/DbConection.php';

$db = $conectarDB;

header('Content-Type: application/json');

$term = strtolower($_GET['term']);

$sql = "SELECT Cod_producto, Nombre
        FROM producto 
        WHERE LOWER(Nombre) LIKE ?";
$stmt = $db->prepare($sql);
$searchTerm = '%' . $term . '%';
$stmt->bind_param('s', $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

$sugerencias = [];
while ($row = $result->fetch_assoc()) {
    $sugerencias[] = [
        'label' => $row['Nombre'],
        'value' => $row['Cod_producto']
    ];
}

echo json_encode($sugerencias);
?>