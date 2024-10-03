<?php
// Importar la conexión a la base de datos
require __DIR__ . '/../../../includes/config/DbConection.php';
$db = $conectarDB;

date_default_timezone_set('America/Mexico_City');  // Establecer la zona horaria
$fechaActual = date('Y-m-d');  // Obtener la fecha actual

// Inicializamos la variable para la sumatoria de "Monto Final" a nivel global
$totalCobrado = 0;

// Función para calcular la suma del monto final
function calcularTotalCobrado($db, $fechaDesde, $fechaHasta) {
    global $totalCobrado;  // Hacer la variable accesible a nivel global

    // Reiniciar la variable $totalCobrado a 0
    $totalCobrado = 0;

    // Consulta SQL para sumar la columna "monto_final"
    $querySum = "SELECT SUM(monto_final) as total FROM caja WHERE DATE(fecha_apertura) BETWEEN '$fechaDesde' AND '$fechaHasta'";
    $resultadoSum = $db->query($querySum);

    // Actualizar el valor de $totalCobrado con el resultado de la consulta
    if ($resultadoSum && $resultadoSum->num_rows > 0) {
        $fila = $resultadoSum->fetch_assoc();
        $totalCobrado = isset($fila['total']) ? $fila['total'] : 0;
    }
}

// Si se han enviado fechas por POST, usamos las fechas seleccionadas
if (isset($_POST['fechaDesde']) && isset($_POST['fechaHasta'])) {
    $fechaDesde = $_POST['fechaDesde'];
    $fechaHasta = $_POST['fechaHasta'];

    // Calcular el total cobrado para las fechas seleccionadas
    calcularTotalCobrado($db, $fechaDesde, $fechaHasta);

    // Consulta para mostrar los datos en la tabla
    $query = "SELECT caja.*, usuario.nombre, usuario.apellido 
              FROM caja 
              INNER JOIN usuario ON caja.id_usuario = usuario.id_usuario 
              WHERE DATE(caja.fecha_apertura) BETWEEN '$fechaDesde' AND '$fechaHasta'";
    $resultado = $db->query($query);

} else {
    // Si no se seleccionan fechas, usamos la fecha actual
    $fechaDesde = $fechaActual;
    $fechaHasta = $fechaActual;

    // Calcular el total cobrado para la fecha actual
    calcularTotalCobrado($db, $fechaDesde, $fechaHasta);

    // Consulta para mostrar los datos en la tabla con la fecha actual
    $query = "SELECT caja.*, usuario.nombre, usuario.apellido 
              FROM caja 
              INNER JOIN usuario ON caja.id_usuario = usuario.id_usuario 
              WHERE DATE(caja.fecha_apertura) = '$fechaActual'";
    $resultado = $db->query($query);
}
?>

<div class="container mt-3" style="margin-bottom:calc(30%)">

    <h2 style="width:calc(100%); text-align:center; text-transform:uppercase; margin-bottom:2rem;">Reporte de Caja</h2>

    <!-- Inputs de selección de fecha con Bootstrap -->
    <div class="row mb-3" >
        <div class="col-md-3">
            <label for="fechaDesde">Desde:</label>
            <input type="date" id="fechaDesde" class="form-control" value="<?php echo $fechaActual; ?>">
        </div>
        <div class="col-md-3">
            <label for="fechaHasta">Hasta:</label>
            <input type="date" id="fechaHasta" class="form-control" value="<?php echo $fechaActual; ?>">
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button id="btnGenerar" class="btn" style="background-color:#003366; color:white;">Generar</button>
        </div>
    </div>

    <!-- Tabla para mostrar los resultados -->
    <div id="tablaResultados">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th style="background-color:#003366; color:white; text-align:center;">Cod. Referencia</th>
                    <th style="background-color:#003366; color:white; text-align:center;">Fecha Apertura</th>
                    <th style="background-color:#003366; color:white; text-align:center;">Fecha Cierre</th>
                    <th style="background-color:#003366; color:white; text-align:center;">Monto Inicial</th>
                    <th style="background-color:#003366; color:white; text-align:center;">Monto Final</th>
                    <th style="background-color:#003366; color:white; text-align:center;">Estado</th>
                    <th style="background-color:#003366; color:white; text-align:center;">Encargado</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($resultado && $resultado->num_rows > 0): ?>
                    <?php while($fila = $resultado->fetch_assoc()): ?>
                        <tr>
                            <td style="background-color:#003366; color:white; text-align:center;"><?php echo $fila['id_caja']; ?></td>
                            <td style="text-align:center;"><?php echo $fila['fecha_apertura']; ?></td>
                            <td style="text-align:center;"><?php echo $fila['fecha_cierre']; ?></td>
                            <td style="text-align:center;"><?php echo $fila['monto_inicial']; ?></td>
                            <td style="text-align:center;"><?php echo $fila['monto_final']; ?></td>
                            <td style="text-align:center;"><?php echo $fila['estado']; ?></td>
                            <td style="text-align:center;"><?php echo $fila['nombre'] . " " . $fila['apellido']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align:center;">No se encontraron registros</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Tabla de resumen -->
    <div class="container mt-3">
        <table class="table table-bordered" style="width: 100%; margin: 0 auto; background-color:#003366; color:white;">
            <thead>
                <tr style="text-align:center;">
                    <th colspan="2" style="background-color:#003366; color:white; text-align:center;">RESUMEN</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="background-color:#003366; color:white; text-align:center;">Total cobrado</td>
                    <td style="text-align:center;">Q <?php echo number_format($totalCobrado, 2); ?></td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

<script>
$(document).ready(function() {
    $('#btnGenerar').click(function(e) {
        e.preventDefault();

        // Obtener las fechas desde los inputs
        var fechaDesde = $('#fechaDesde').val();
        var fechaHasta = $('#fechaHasta').val();

        // Validar que se hayan ingresado las fechas
        if (fechaDesde === '' || fechaHasta === '') {
            alert('Por favor ingrese ambas fechas');
            return;
        }

        // Enviar las fechas mediante AJAX
        $.ajax({
            url: '/TINES/vistas/modulos/reporte/reporte_caja.php', // El archivo actual se procesa en sí mismo
            type: 'POST',
            data: {
                fechaDesde: fechaDesde,
                fechaHasta: fechaHasta
            },
            success: function(response) {
                // Reemplazar la tabla con los nuevos resultados
                $('#tablaResultados').html($(response).find('#tablaResultados').html());
            }
        });
    });
});
</script>

<?php
$db->close();
?>
