<?php 
require __DIR__ . '/../../../includes/config/DbConection.php';
$db = $conectarDB;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajax'])) {
    $categoria = isset($_POST['categoria']) ? $_POST['categoria'] : null;
    $tipo = isset($_POST['tipo']) ? $_POST['tipo'] : null;

    // Iniciar la consulta base
    $sql = "
    SELECT 
        e.*, 
        c.Id_presentacion_producto, c.Nombre AS Pres_producto, c.Medida,
        t.Id_tipo_producto, t.Nombre AS Nombre_tipo,
        tt.Id_categoria_producto, tt.Nombre AS Nombre_cat
    FROM producto AS e
    LEFT JOIN presentacion_producto AS c ON e.Id_presentacion_producto = c.Id_presentacion_producto
    LEFT JOIN tipo_producto AS t ON e.Id_tipo_producto = t.Id_tipo_producto
    LEFT JOIN categoria_producto AS tt ON e.Id_categoria_producto = tt.Id_categoria_producto
    WHERE 1=1"; // Siempre verdadera para agregar condiciones dinámicamente

    // Agregar condiciones según los filtros
    if (!empty($categoria)) {
        $sql .= " AND e.Id_categoria_producto = '$categoria'";
    }

    if (!empty($tipo)) {
        $sql .= " AND e.Id_tipo_producto = '$tipo'";
    }

    // Ejecutar la consulta
    $resultado = $db->query($sql);

    if (!$resultado) {
        echo json_encode(['error' => 'Error en la consulta']);
        error_log("Error en la consulta: " . $db->error);
        exit;
    }

    // Preparar los resultados
    $productos = [];
    if ($resultado->num_rows > 0) {
        while ($fila = $resultado->fetch_assoc()) {
            $productos[] = $fila;
        }
    }

    // Enviar la respuesta en formato JSON
    echo json_encode(['productos' => $productos]);
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Productos</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
</head>
<body>
<div class="container-fluid mt-4">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h2>Reporte de productos</h2>
        </div>
    </div>

    <!-- Filtro de productos -->
    <div class="card">
        <form id="miFormulario" method="POST">
            <div class="card-header">
                <h3>Productos</h3>
            </div>
            <div class="card-body">
                <div class="form-inline">
                    <div class="form-group mr-2">
                        <label for="categoria">Seleccionar categoría:</label>
                        <select class="form-control" id="categoria" name="categoria">
                            <option value="" selected disabled>Seleccione una categoría</option>
                            <?php
                            $sqlSelect = "SELECT * FROM categoria_producto";
                            $resultado = $db->query($sqlSelect);
                            if ($resultado->num_rows > 0) {
                                while ($row = $resultado->fetch_assoc()) {
                                    echo '<option value="' . htmlspecialchars($row['Id_categoria_producto']) . '">';
                                    echo htmlspecialchars($row['Nombre']) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group mr-2">
                        <label for="tipo">Seleccionar tipo:</label>
                        <select class="form-control" id="tipo" name="tipo">
                            <option value="" selected disabled>Seleccione un tipo</option>
                            <?php
                            $sqlSelect = "SELECT * FROM tipo_producto";
                            $resultado = $db->query($sqlSelect);
                            if ($resultado->num_rows > 0) {
                                while ($row = $resultado->fetch_assoc()) {
                                    echo '<option value="' . htmlspecialchars($row['Id_tipo_producto']) . '">';
                                    echo htmlspecialchars($row['Nombre']) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Buscar</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Tabla de productos -->
    <div class="card mt-4">
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Código Producto</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Presentación</th>
                        <th>Tipo</th>
                        <th>Categoría</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody id="tablaResultados">
                    <!-- Los datos se cargarán aquí -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Enviar el formulario usando AJAX
    $('#miFormulario').on('submit', function(e) {
        e.preventDefault(); // Evitar el comportamiento por defecto del formulario

        // Obtener los datos del formulario
        var formData = $(this).serialize() + '&ajax=1';

        // Enviar los datos por AJAX
        $.ajax({
            type: 'POST',
            url: '/TINES/vistas/modulos/reporte/reporte_productos.php', // Mismo archivo PHP
            data: formData,
            dataType: 'json',
            success: function(response) {
                // Actualizar la tabla de resultados
                var tbody = '';
                if (response.productos.length > 0) {
                    $.each(response.productos, function(index, producto) {
                        tbody += `<tr>
                            <td>${index + 1}</td>
                            <td>${producto.Cod_producto}</td>
                            <td>${producto.Nombre}</td>
                            <td>${producto.Descripcion}</td>
                            <td>${producto.Pres_producto} (${producto.Medida})</td>
                            <td>${producto.Nombre_tipo}</td>
                            <td>${producto.Nombre_cat}</td>
                            <td>${producto.Estado == 1 ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>'}</td>
                        </tr>`;
                    });
                } else {
                    tbody = `<tr><td colspan="8" style="text-align:center;">No se encontraron productos</td></tr>`;
                }
                $('#tablaResultados').html(tbody);
            },
            error: function() {
                alert('Hubo un error al procesar la solicitud.');
            }
        });
    });
});
</script>

</body>
</html>
