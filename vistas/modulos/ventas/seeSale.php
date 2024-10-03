<?php
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if (!$isAjax) {
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <title>Ver ventas</title>
</head>
<body>
<?php
}
require_once __DIR__ . "/../../../includes/config/DbConection.php";
$db = $conectarDB;
$sql = "
    SELECT
        v.id_venta,
        p.Nombre,
        dt.cantidad,
        dt.precio_unitario,
        dt.total
    FROM ventas AS v
    RIGHT JOIN detalles_de_ventas AS dt
    ON v.id_venta = dt.venta_id
    LEFT JOIN producto AS p
    ON p.Id_producto = dt.producto_id";
$stmt = $db->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

$salesData = [];
while ($row = $result->fetch_assoc()) {
    $id_venta = $row['id_venta'];
    if (!isset($salesData[$id_venta])) {
        $salesData[$id_venta] = [
            'id_venta' => $id_venta,
            'products' => []
        ];
    }
    $salesData[$id_venta]['products'][] = $row;
}

function showSales($salesData) {
    $no = 1; // Inicializamos el contador para la columna "No"
    $prev_no = null; // Variable para almacenar el valor anterior de "No"

    foreach ($salesData as $sale) {
        echo "<tr>";
        echo "<td rowspan='" . count($sale['products']) . "'>" . htmlspecialchars($no) . "</td>"; // Columna "No"
        echo "<td rowspan='" . count($sale['products']) . "'>" . htmlspecialchars($sale['id_venta']) . "</td>"; // Columna "Código de venta"
        $prev_no = $no; // Actualizamos el valor anterior de "No"
        $no++; // Incrementamos el contador
        $first = true;
        foreach ($sale['products'] as $product) {
            if (!$first) {
                echo "<tr>";
            }
            echo "<td>" . htmlspecialchars($product['Nombre']) . "</td>";
            echo "<td>" . htmlspecialchars($product['cantidad']) . "</td>";
            echo "<td>" . htmlspecialchars("Q. " . $product['precio_unitario']) . "</td>";
            echo "<td>" . htmlspecialchars("Q. " . $product['total']) . "</td>";
            if ($first) {
                echo "<td rowspan='" . count($sale['products']) . "'>";
                echo "<button type='button' class='btn btn-primary ver-btn bg-success border-success' id='ver' data-id='" . htmlspecialchars($sale['id_venta']) . "'>Ver</button> ";
                echo "<button type='button' class='btn btn-primary mx-2' id='ver' data-id='" . htmlspecialchars($sale['id_venta']) . "'>Factura</button> ";
                echo "<button type='button' class='btn btn-danger' id='editar' data-id='" . htmlspecialchars($sale['id_venta']) . "' onclick='loadEditModal(" . htmlspecialchars($sale['id_venta']) . ")'>PDF</button>";
                echo "</td>";
                $first = false;
            }
            echo "</tr>";
        }
    }
}
?>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>No</th>
            <th>Código de venta</th>
            <th>Productos</th>
            <th>Cantidad</th>
            <th>Precio unitario</th>
            <th>Total</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if (!empty($salesData)) {
            showSales($salesData);
        } else {
            echo "<tr><td colspan='7'>No se encontraron registros.</td></tr>";
        }
        ?>
    </tbody>
</table>

<?php
if (!$isAjax) {
?>    
</body>
</html>
<?php
}
?>
