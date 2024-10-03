<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h1>Reportes</h1>

        <!-- Select para elegir el reporte -->
        <div class="form-group">
            <label for="selectReporte">Elija un reporte:</label>
            <select class="form-control" id="selectReporte">
                <option value="">--Seleccione aqui --</option>
                <option value="reporteCaja">Reporte de caja</option>
                <option value="reporteDevoluciones">Reporte de devoluciones</option>
                <option value="reportePromociones">Reporte de promociones</option>
                <option value="reporteInventario">Reporte de inventario</option>
                <option value="reporteCategoria">Reporte de categoría</option>
                <option value="reporteVenta">Reporte de ventas</option>
                <option value="reporteFactura">Reporte de facturas</option>
                <option value="reporteCliente">Reporte de cliente</option>
                <option value="reporteVentas">Reporte de ventas</option>
                <option value="reporteProducto">Reporte de productos</option>
            </select>
        </div>

        <!-- Contenedor donde se mostrará el reporte seleccionado -->
        <div id="reporteContent" class="mt-4"></div>
    </div>

    <script>
        $(document).ready(function() {
            // Al cambiar la opción del select
            $('#selectReporte').change(function(e) {
                e.preventDefault();
                
                // Obtener el valor seleccionado
                var reporteSeleccionado = $(this).val();
                
                // Limpiar el contenido anterior
                $('#reporteContent').html('');

                // Según la opción seleccionada, cargar el archivo correspondiente mediante AJAX
                if (reporteSeleccionado === 'reporteCaja') {
                    $('#reporteContent').load('/TINES/vistas/modulos/reporte/reporte_caja.php');
                } else if (reporteSeleccionado === 'reporteDevoluciones') {
                    $('#reporteContent').load('/TINES/vistas/modulos/reporte/reporte_devoluciones.php');
                } else if (reporteSeleccionado === 'reportePromociones') {
                    $('#reporteContent').load('/TINES/vistas/modulos/reporte/reporte_promociones.php');
                } else if (reporteSeleccionado === 'reporteInventario') {
                    $('#reporteContent').load('/TINES/vistas/modulos/reporte/reporte_inventario.php');
                } else if (reporteSeleccionado === 'reporteCategoria') {
                    $('#reporteContent').load('/TINES/vistas/modulos/reporte/reporte_categoria.php');
                } else if (reporteSeleccionado === 'reporteVenta') {
                    $('#reporteContent').load('/TINES/vistas/modulos/reporte/reporte_venta.php');
                } else if (reporteSeleccionado === 'reporteFactura') {
                    $('#reporteContent').load('/TINES/vistas/modulos/reporte/reporte_factura.php');
                } else if (reporteSeleccionado === 'reporteCliente') {
                    $('#reporteContent').load('/TINES/vistas/modulos/reporte/reporte_cliente.php');
                } else if (reporteSeleccionado === 'reporteVentas') {
                    $('#reporteContent').load('/TINES/vistas/modulos/reporte/reporte_ventas.php');
                } else if (reporteSeleccionado === 'reporteProducto') {
                    $('#reporteContent').load('/TINES/vistas/modulos/reporte/reporte_productos.php');
                }
            });
        });
    </script>
</body>
</html>
