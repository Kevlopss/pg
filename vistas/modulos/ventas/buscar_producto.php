<?php
// buscar_producto.php
header('Content-Type: application/json');

require __DIR__ . '/../../../includes/config/DbConection.php';

$db = $conectarDB;

$codigo = isset($_GET['codigo']) ? $_GET['codigo'] : '';

// Actualiza la consulta SQL para buscar por código o nombre
$sql = "
    SELECT
        e.*, 
        c.Id_presentacion_producto, c.Nombre AS Pres_producto, c.Medida,
        t.Id_tipo_producto, t.Nombre AS Nombre_tipo,
        tt.Id_categoria_producto, tt.Nombre AS Nombre_cat,
        pp.Precio_normal, pp.Precio_descuento, pp.Precio_descuento_2, pp.Precio_mayorista, pp.Precio_oferta
    FROM producto AS e
    LEFT JOIN presentacion_producto AS c ON e.Id_presentacion_producto = c.Id_presentacion_producto
    LEFT JOIN tipo_producto AS t ON e.Id_tipo_producto = t.Id_tipo_producto
    LEFT JOIN categoria_producto AS tt ON e.Id_categoria_producto = tt.Id_categoria_producto
    LEFT JOIN precio_producto AS pp ON e.Id_producto = pp.Id_producto
    WHERE e.Cod_producto = ? OR e.Nombre LIKE ?";

$stmt = $db->prepare($sql);
$searchTerm = "%$codigo%";
$stmt->bind_param('ss', $codigo, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

$productos = [];
while ($row = $result->fetch_assoc()) {
    $productos[] = [
        'label' => $row['Nombre'] . ' (' . $row['Descripcion'] . ')',
        'value' => $row['Cod_producto'],
        'producto' => $row
    ];
}

if (!empty($productos)) {
    echo json_encode(['status' => 'success', 'productos' => $productos]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Producto no encontrado']);
}

$stmt->close();
$db->close();
?>
