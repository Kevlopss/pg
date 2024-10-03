<?php
// Importar la conexión a la base de datos
require __DIR__ . '/../../../includes/config/DbConection.php';
$db = $conectarDB;

date_default_timezone_set('America/Mexico_City');  // Establecer la zona horaria

// Verificar si se ha enviado una categoría por filtro
$categoriaSeleccionada = isset($_POST['categoria']) ? $_POST['categoria'] : '';

// Consulta para obtener el código de producto, nombre de producto y categoría, con o sin filtro
$query = "SELECT producto.Cod_producto, producto.Nombre, categoria_producto.Nombre AS NombreCategoria
          FROM producto
          INNER JOIN categoria_producto ON producto.Id_categoria_producto = categoria_producto.Id_categoria_producto";

// Si se selecciona una categoría, agregar la condición a la consulta
if ($categoriaSeleccionada != '') {
    $query .= " WHERE categoria_producto.Nombre = '$categoriaSeleccionada'";
}

$resultado = $db->query($query);

// Consulta para obtener todas las categorías (para el filtro)
$queryCategorias = "SELECT Nombre FROM categoria_producto";
$resultadoCategorias = $db->query($queryCategorias);
?>

<div class="container mt-3" style="margin-bottom:calc(30%)">
    <h2 style="width:calc(100%); text-align:center; text-transform:uppercase; margin-bottom:2rem;">Reporte de Categorías de Productos</h2>

    <!-- Filtro por categoría -->
    <form method="POST" id="filtroCategoria">
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="categoria">Filtrar por categoría:</label>
                <select id="categoria" name="categoria" class="form-control">
                    <option value="">Todas las categorías</option>
                    <?php while($filaCat = $resultadoCategorias->fetch_assoc()): ?>
                        <option value="<?php echo $filaCat['Nombre']; ?>" <?php if ($categoriaSeleccionada == $filaCat['Nombre']) echo 'selected'; ?>>
                            <?php echo $filaCat['Nombre']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">Filtrar</button>
            </div>
        </div>
    </form>

    <!-- Tabla para mostrar los resultados -->
    <div id="tablaResultados">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th style="background-color:#003366; color:white; text-align:center;">Cod. Producto</th>
                    <th style="background-color:#003366; color:white; text-align:center;">Nombre Producto</th>
                    <th style="background-color:#003366; color:white; text-align:center;">Categoría</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($resultado && $resultado->num_rows > 0): ?>
                    <?php while($fila = $resultado->fetch_assoc()): ?>
                        <tr>
                            <td style="text-align:center;"><?php echo $fila['Cod_producto']; ?></td>
                            <td style="text-align:center;"><?php echo $fila['Nombre']; ?></td>
                            <td style="text-align:center;"><?php echo $fila['NombreCategoria']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" style="text-align:center;">No se encontraron productos en esta categoría</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$db->close();
?>
