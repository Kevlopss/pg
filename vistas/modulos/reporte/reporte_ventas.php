<?php 
require __DIR__ . '/../../../includes/config/DbConection.php';
$db = $conectarDB;

date_default_timezone_set('America/Mexico_City');

// Inicializamos la variable para la sumatoria de "total" de las ventas
$totalVentas = 0;
$resultado = null; // Inicializar la variable

// Función para calcular la suma del total de las ventas
function calcularTotalVentas($db, $startDate, $endDate) {
    global $totalVentas;

    $totalVentas = 0;

    $querySum = "SELECT SUM(dt.total) as total FROM ventas AS v 
                 RIGHT JOIN detalles_de_ventas AS dt ON v.id_venta = dt.venta_id 
                 WHERE DATE(v.fecha) BETWEEN '$startDate' AND '$endDate'";
    $resultadoSum = $db->query($querySum);

    if ($resultadoSum && $resultadoSum->num_rows > 0) {
        $fila = $resultadoSum->fetch_assoc();
        $totalVentas = isset($fila['total']) ? $fila['total'] : 0;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajax'])) {
    // Inicializar las variables de fecha
    $startDate = '';
    $endDate = '';

    // Depurar: Verifica si los datos POST están recibiéndose
    error_log("POST Data: " . print_r($_POST, true));

    // Obtener el rango de fechas seleccionado
    $dateRange = $_POST['date_range'];

    // Depurar: Verifica el valor del rango de fechas seleccionado
    error_log("Date Range: " . $dateRange);

    // Ajustar las fechas según la selección
    switch ($dateRange) {
        case 'today':
            $startDate = date('Y-m-d');
            $endDate = date('Y-m-d');
            break;
        case 'week':
            $startDate = date('Y-m-d', strtotime('monday this week'));
            $endDate = date('Y-m-d', strtotime('sunday this week'));
            break;
        case 'month':
            $startDate = date('Y-m-01');
            $endDate = date('Y-m-t');
            break;
        case 'custom':
            // Validar si los valores de start_date y end_date están definidos
            if (isset($_POST['start_date']) && isset($_POST['end_date'])) {
                $startDate = DateTime::createFromFormat('Y-m-d', $_POST['start_date'])->format('Y-m-d');
                $endDate = DateTime::createFromFormat('Y-m-d', $_POST['end_date'])->format('Y-m-d');
            } else {
                echo json_encode(['error' => 'Fechas no válidas']);
                error_log("Error: Fechas no válidas");
                exit;
            }
            break;
    }

    // Verificar si las fechas están vacías o no válidas
    if (empty($startDate) || empty($endDate)) {
        echo json_encode(['error' => 'Fechas no válidas']);
        error_log("Error: Fechas vacías o no válidas.");
        exit;
    }

    // Depurar: Verificar las fechas después del proceso de selección
    error_log("Start Date: " . $startDate . " End Date: " . $endDate);

    // Calcular el total de ventas
    calcularTotalVentas($db, $startDate, $endDate);

    // Consulta de ventas
    // $query = "SELECT v.id_venta, v.fecha, p.Nombre, dt.cantidad, dt.precio_unitario, dt.total 
    //           FROM ventas AS v 
    //           RIGHT JOIN detalles_de_ventas AS dt ON v.id_venta = dt.venta_id 
    //           LEFT JOIN producto AS p ON p.Id_producto = dt.producto_id 
    //           WHERE DATE(v.fecha) BETWEEN '$startDate' AND '$endDate' 
    //           ORDER BY v.id_venta DESC";
    $query = "SELECT v.id_venta, v.fecha, p.Nombre, dt.cantidad, dt.precio_unitario, 
                 (dt.cantidad * dt.precio_unitario) AS total 
          FROM ventas AS v 
          RIGHT JOIN detalles_de_ventas AS dt ON v.id_venta = dt.venta_id 
          LEFT JOIN producto AS p ON p.Id_producto = dt.producto_id 
          WHERE DATE(v.fecha) BETWEEN '$startDate' AND '$endDate' 
          ORDER BY v.id_venta DESC";
    // Depurar: Verificar la consulta SQL antes de ejecutarla
    error_log("SQL Query: " . $query);

    $resultado = $db->query($query);

    if (!$resultado) {
        echo json_encode(['error' => 'Error en la consulta']);
        error_log("Error en la consulta: " . $db->error);
        exit;
    }

    // Preparar la respuesta en JSON
    $ventas = [];
    if ($resultado && $resultado->num_rows > 0) {
        while ($fila = $resultado->fetch_assoc()) {
            $ventas[] = $fila;
        }
    }

    // Enviar respuesta en formato JSON
    echo json_encode([
        'ventas' => $ventas,
        'totalVentas' => number_format($totalVentas, 2)
    ]);
    exit;
}


$db->close();
?>

<div class="container mt-3">
    <h2 style="text-align:center;">Reporte de Ventas</h2>

    <!-- Formulario para seleccionar rango de fechas -->
    <div class="card">
        <form method='POST' action='' id="venta">
            <div class="card-header">
                <h3>Ventas</h3>
            </div>
            <div class="card-body">
                <div class="form-inline">
                    <div class="form-group mr-2">
                        <label class="mr-2" for="categoria">Seleccionar rango de ventas:</label>
                        <select id="date-select" class="form-control date-select" name="date_range">
                            <option value="" selected disabled>Seleccionar Rango de Fechas</option>
                            <option value="today">Hoy</option>
                            <option value="week">Esta Semana</option>
                            <option value="month">Este Mes</option>
                            <option value="custom">Fecha Personalizada</option>
                        </select>
                    </div>
                    <!-- Custom Date Range -->
                    <div id="custom-date-range" class="form-group mr-2" style="display:none;">
                        <label for="start-date">Fecha Inicio:</label>
                        <input type="date" id="start-date" class="form-control" name="start_date">
                        <label for="end-date" class="ml-2">Fecha Fin:</label>
                        <input type="date" id="end-date" class="form-control" name="end_date">
                    </div>
                    <div class="btn-container float-right">
                        <button type="submit" class="btn btn-primary">Buscar</button>
                        <button type="button" id="refresh" class="btn btn-secondary ml-2">Refresh</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Tabla para mostrar los resultados -->
    <div id="tablaResultados" class="card">
        <table class="table table-bordered table-striped" style="margin-bottom: 0;">
            <thead>
                <tr>
                    <th>ID Venta</th>
                    <th>Fecha</th>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio Unitario</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($resultado && $resultado->num_rows > 0): ?>
                    <?php while($fila = $resultado->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $fila['id_venta']; ?></td>
                            <td><?php echo $fila['fecha']; ?></td>
                            <td><?php echo $fila['Nombre']; ?></td>
                            <td><?php echo $fila['cantidad']; ?></td>
                            <td><?php echo $fila['precio_unitario']; ?></td>
                            <td><?php echo number_format($fila['total'], 2); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align:center;">No se encontraron registros</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Resumen de ventas -->
    <div class="card">
        <table class="table table-bordered" style="width: 100%; margin: 0 auto;">
            <thead>
                <tr style="text-align:center;">
                    <th colspan="2"><h5>Resumen</h5></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Total ventas</td>
                    <td id="total-ventas">Q <?php echo number_format($totalVentas, 2); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
$(document).ready(function() {
    // Mostrar y ocultar el rango de fechas personalizado
    $('#date-select').on('change', function() {
        if ($(this).val() === 'custom') {
            $('#custom-date-range').show();
        } else {
            $('#custom-date-range').hide();
        }
    });

    // Enviar el formulario usando AJAX
    $('#venta').on('submit', function(e) {
        e.preventDefault(); // Evitar el comportamiento por defecto del formulario

        // Obtener los datos del formulario
        var formData = $(this).serialize() + '&ajax=1';

        // Enviar los datos por AJAX
        $.ajax({
            type: 'POST',
            url: '/TINES/vistas/modulos/reporte/reporte_ventas.php', // Mismo archivo PHP
            data: formData,
            dataType: 'json',
            success: function(response) {
                // Actualizar la tabla de resultados
                var tbody = '';
                if (response.ventas.length > 0) {
                    $.each(response.ventas, function(index, venta) {
                        tbody += `<tr>
                            <td>${venta.id_venta}</td>
                            <td>${venta.fecha}</td>
                            <td>${venta.Nombre}</td>
                            <td>${venta.cantidad}</td>
                            <td>${venta.precio_unitario}</td>
                            <td>${venta.total}</td>
                        </tr>`;
                    });
                } else {
                    tbody = `<tr><td colspan="6" style="text-align:center;">No se encontraron registros</td></tr>`;
                }
                $('#tablaResultados tbody').html(tbody);

                // Actualizar el resumen de ventas
                $('#total-ventas').text('Q ' + response.totalVentas);

            },
            error: function() {
                alert('Hubo un error al procesar la solicitud.');
            }
        });
    });
    // Funcionalidad del botón Refresh
    $('#refresh').on('click', function() {
        // Restablecer el formulario
        $('#venta')[0].reset();
        
        // Ocultar el rango de fechas personalizado
        $('#custom-date-range').hide();

        // Limpiar los resultados de la tabla
        $('#tablaResultados tbody').html(`<tr><td colspan="6" style="text-align:center;">No se encontraron registros</td></tr>`);

        // Reiniciar el resumen de ventas
        $('table tbody tr td:last-child').text('No se encontraron registros');
    });
});
</script>

<?php
// $db->close();
?>
