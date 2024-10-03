<?php
// Importar la conexión a la base de datos
require __DIR__ . '/../../../includes/config/DbConection.php';
$db = $conectarDB;

date_default_timezone_set('America/Mexico_City');  // Establecer la zona horaria

// Consulta para traer los productos con su respectivo precio normal y precio descuento
$query = "SELECT producto.Cod_producto, producto.Nombre, precio_producto.Precio_normal, precio_producto.Precio_descuento_2
          FROM precio_producto
          INNER JOIN producto ON precio_producto.Id_producto = producto.Id_producto
          WHERE precio_producto.Precio_descuento_2 > 0";  // Solo traer productos con un descuento activo
$resultado = $db->query($query);
?>

<div class="container mt-3" style="margin-bottom:calc(30%)">
    <h2 style="width:calc(100%); text-align:center; text-transform:uppercase; margin-bottom:2rem;">Reporte de Promociones</h2>

    <!-- Tabla para mostrar los resultados -->
    <div id="tablaResultados">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th style="background-color:#003366; color:white; text-align:center;">Cod. Producto</th>
                    <th style="background-color:#003366; color:white; text-align:center;">Nombre Producto</th>
                    <th style="background-color:#003366; color:white; text-align:center;">Precio Normal</th>
                    <th style="background-color:#003366; color:white; text-align:center;">Precio Descuento</th>
                    <th style="background-color:#003366; color:white; text-align:center;">Porcentaje Descuento</th>
                    <th style="background-color:#003366; color:white; text-align:center;">Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($resultado && $resultado->num_rows > 0): ?>
                    <?php while($fila = $resultado->fetch_assoc()): ?>
                        <?php
                            // Calcular el porcentaje de descuento
                            $precioNormal = $fila['Precio_normal'];
                            $precioDescuento = $fila['Precio_descuento_2'];
                            $porcentajeDescuento = ($precioNormal > 0) ? (($precioNormal - $precioDescuento) / $precioNormal) * 100 : 0;

                            // Determinar el estado (activo/inactivo)
                            $estado = ($precioDescuento > 0) ? 'Activo' : 'Inactivo';
                        ?>
                        <tr>
                            <td style="text-align:center;"><?php echo $fila['Cod_producto']; ?></td>
                            <td style="text-align:center;"><?php echo $fila['Nombre']; ?></td>
                            <td style="text-align:center;">Q <?php echo number_format($precioNormal, 2); ?></td>
                            <td style="text-align:center;">Q <?php echo number_format($precioDescuento, 2); ?></td>
                            <td style="text-align:center;"><?php echo number_format($porcentajeDescuento, 2); ?>%</td>
                            <td style="text-align:center;"><?php echo $estado; ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align:center;">No se encontraron productos en promoción</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$db->close();
?>
