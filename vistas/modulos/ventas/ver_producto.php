<?php
// Importar la conexión a la base de datos
require __DIR__ . '/../../../includes/config/DbConection.php';

$db = $conectarDB;

// Verificar si el código del producto se ha pasado como parámetro
if (isset($_GET['codigo'])) {
    $codigo = htmlspecialchars($_GET['codigo']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'No se ha proporcionado un código de producto.']);
    exit;
}

$sql = "
    SELECT 
        e.Cod_producto, e.Nombre, e.Descripcion, e.Imagen,
        c.Nombre AS Pres_producto, c.Medida,
        t.Nombre AS Nombre_tipo,
        tt.Nombre AS Nombre_cat,
        pp.Precio_normal
    FROM producto AS e
    LEFT JOIN presentacion_producto AS c ON e.Id_presentacion_producto = c.Id_presentacion_producto
    LEFT JOIN tipo_producto AS t ON e.Id_tipo_producto = t.Id_tipo_producto
    LEFT JOIN categoria_producto AS tt ON e.Id_categoria_producto = tt.Id_categoria_producto
    LEFT JOIN precio_producto AS pp ON e.Id_producto = pp.Id_producto
    WHERE e.Cod_producto = ?";

$stmt = $db->prepare($sql);
$stmt->bind_param('s', $codigo);
$result = $stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'No se encontró el producto con el código proporcionado.']);
    exit;
}

$row = $result->fetch_assoc();
$stmt->close();

echo json_encode(['status' => 'success', 'producto' => $row]);