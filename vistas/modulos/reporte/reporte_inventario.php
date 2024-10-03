<?php
// Importar la conexión a la base de datos
require __DIR__ . '/../../../includes/config/DbConection.php';
$db = $conectarDB;

date_default_timezone_set('America/Mexico_City');  // Establecer la zona horaria

// Consulta para obtener el código de producto, nombre de producto y stock
$query = "SELECT producto.Cod_producto, producto.Nombre, inventario.stock
          FROM inventario
          INNER JOIN producto ON inventario.Id_producto = producto.Id_producto";
$resultado = $db->query($query);
?>

<div class="container mt-3" style="margin-bottom:calc(30%)">
    <h2 style="width:calc(100%); text-align:center; text-transform:uppercase; margin-bottom:2rem;">Reporte de Inventario</h2>

    <!-- Tabla para mostrar los resultados -->
    <div id="tablaResultados">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th style="background-color:#003366; color:white; text-align:center;">Cod. Producto</th>
                    <th style="background-color:#003366; color:white; text-align:center;">Nombre Producto</th>
                    <th style="background-color:#003366; color:white; text-align:center;">Cantidad</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($resultado && $resultado->num_rows > 0): ?>
                    <?php while($fila = $resultado->fetch_assoc()): ?>
                        <tr>
                            <td style="text-align:center;"><?php echo $fila['Cod_producto']; ?></td>
                            <td style="text-align:center;"><?php echo $fila['Nombre']; ?></td>
                            <td style="text-align:center;"><?php echo $fila['stock']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" style="text-align:center;">No se encontraron productos en inventario</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$db->close();
?>
