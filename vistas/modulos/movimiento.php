<?php

// Importar la conexión a la base de datos
require __DIR__ . '/../../includes/config/DbConection.php';
$db = $conectarDB;

// Manejo del formulario de movimiento
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo_producto = $_POST['codigo_producto'];
    $tipo_movimiento = $_POST['tipo_movimiento'];
    $cantidad = $_POST['cantidad'];

    // Obtener el id_producto basado en el código de producto
    $sql_obtener_id = "SELECT Id_producto FROM producto WHERE Cod_producto = ?";
    $stmt_obtener_id = $db->prepare($sql_obtener_id);
    $stmt_obtener_id->bind_param('s', $codigo_producto);
    $stmt_obtener_id->execute();
    $result_obtener_id = $stmt_obtener_id->get_result();

    if ($result_obtener_id->num_rows > 0) {
        $row = $result_obtener_id->fetch_assoc();
        $id_producto = $row['Id_producto'];

        // Inserción del movimiento en la base de datos
        $sql = "INSERT INTO movimientos (id_producto, tipo_movimiento, cantidad) VALUES (?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->bind_param('isi', $id_producto, $tipo_movimiento, $cantidad);

        if ($stmt->execute()) {
            // Actualización del stock en la tabla de inventario
            if ($tipo_movimiento == 'carga') {
                $update_stock = "UPDATE inventario SET stock = stock + ? WHERE Id_producto = ?";
            } else {
                $update_stock = "UPDATE inventario SET stock = stock - ? WHERE Id_producto = ?";
            }
            $stmt_update = $db->prepare($update_stock);
            $stmt_update->bind_param('ii', $cantidad, $id_producto);
            $stmt_update->execute();
        } else {
            echo "Error al insertar el movimiento: " . $stmt->error;
        }
    } else {
        echo "Error: El producto con código $codigo_producto no existe.";
    }
}

// Obtener movimientos registrados
$sql_movimientos = "SELECT m.*, p.Nombre FROM movimientos m JOIN producto p ON m.id_producto = p.Id_producto ORDER BY m.fecha_movimiento DESC";
$result_movimientos = $db->query($sql_movimientos);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Movimientos de Inventario</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .center-content {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }
        .form-row {
            width: 100%;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
</head>

<body>

<div class="container mt-5 center-content">
    <h1 class="mb-4">Movimientos de Inventario</h1>

    <form action="movimiento" method="post" class="mb-4 w-75">
        <div class="form-row">
            <div class="form-group col-md-4">
                <label for="buscar_codigo">Buscar Producto por Código:</label>
                <input type="text" id="buscar_codigo" name="codigo_producto" class="form-control" placeholder="Ingrese el código del producto" required>
                <div id="resultados_busqueda" class="list-group mt-2"></div>
            </div>
            <div class="form-group col-md-4">
                <label for="tipo_movimiento">Tipo de Movimiento:</label>
                <select name="tipo_movimiento" id="tipo_movimiento" class="form-control" required>
                    <option value="carga">Carga</option>
                    <option value="descarga">Descarga</option>
                </select>
            </div>
            <div class="form-group col-md-4">
                <label for="cantidad">Cantidad:</label>
                <input type="number" name="cantidad" id="cantidad" class="form-control" min="1" required>
            </div>
        </div>
        <div id="producto_detalles" style="display: none;" class="mb-4">
            <div class="form-group">
                <label>Código:</label>
                <p id="producto_codigo"></p>
            </div>
            <div class="form-group">
                <label>Nombre:</label>
                <p id="producto_nombre"></p>
            </div>
            <div class="form-group">
                <label>Descripción:</label>
                <p id="producto_descripcion"></p>
            </div>
            <div class="form-group">
                <label>Imagen:</label>
                <img id="producto_imagen" src="" alt="Imagen del Producto" class="img-fluid">
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Registrar Movimiento</button>
    </form>

    <h2>Movimientos Registrados</h2>
    <table class="table table-bordered w-75">
        <thead class="thead-dark">
            <tr>
                <th>ID</th>
                <th>Producto</th>
                <th>Tipo de Movimiento</th>
                <th>Cantidad</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result_movimientos->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= $row['Nombre'] ?></td>
                    <td><?= $row['tipo_movimiento'] ?></td>
                    <td><?= $row['cantidad'] ?></td>
                    <td><?= $row['fecha_movimiento'] ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<script>
    $(document).ready(function() {
        $('#buscar_codigo').on('input', function() {
            var codigo = $(this).val();
            if (codigo.length > 0) {
                $.ajax({
                    url: 'movimiento',
                    type: 'GET',
                    data: {buscar_codigo: codigo},
                    success: function(response) {
                        var data = JSON.parse(response);
                        $('#resultados_busqueda').empty();
                        if (data.status === 'success') {
                            data.productos.forEach(function(producto) {
                                $('#resultados_busqueda').append(
                                    '<a href="#" class="list-group-item list-group-item-action" data-id="' + producto.Id_producto + '">' +
                                    producto.Cod_producto + ' - ' + producto.Nombre + ' - ' + producto.Descripcion +
                                    '</a>'
                                );
                            });
                        } else {
                            $('#resultados_busqueda').append('<a href="#" class="list-group-item list-group-item-action disabled">Producto no encontrado</a>');
                        }
                    }
                });
            } else {
                $('#resultados_busqueda').empty();
                $('#producto_detalles').hide();
            }
        });

        $(document).on('click', '.list-group-item', function(e) {
            e.preventDefault();
            var id_producto = $(this).data('id');
            $('#id_producto').val(id_producto);
            $.ajax({
                url: 'movimiento',
                type: 'GET',
                data: {id_producto: id_producto},
                success: function(response) {
                    var data = JSON.parse(response);
                    if (data.status === 'success') {
                        $('#producto_codigo').text(data.producto.Cod_producto);
                        $('#producto_nombre').text(data.producto.Nombre);
                        $('#producto_descripcion').text(data.producto.Descripcion);
                        $('#producto_imagen').attr('src', 'path/to/images/' + data.producto.Imagen);
                        $('#producto_detalles').show();
                        $('#resultados_busqueda').empty();
                        $('#buscar_codigo').val('');
                    }
                }
            });
        });
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
if (isset($_GET['buscar_codigo'])) {
    $codigo = $_GET['buscar_codigo'];
    $sql = "SELECT Id_producto, Cod_producto, Nombre, Descripcion, Imagen FROM producto WHERE Cod_producto LIKE ?";
    $stmt = $db->prepare($sql);
    $codigo = "%$codigo%";
    $stmt->bind_param('s', $codigo);
    $stmt->execute();
    $result = $stmt->get_result();
    $productos = [];
    while ($producto = $result->fetch_assoc()) {
        $productos[] = $producto;
    }
    if (count($productos) > 0) {
        echo json_encode(['status' => 'success', 'productos' => $productos]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Producto no encontrado']);
    }
    exit();
} elseif (isset($_GET['id_producto'])) {
    $id = $_GET['id_producto'];
    $sql = "SELECT Id_producto, Cod_producto, Nombre, Descripcion, Imagen FROM producto WHERE Id_producto = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $producto = $result->fetch_assoc();
        echo json_encode(['status' => 'success', 'producto' => $producto]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Producto no encontrado']);
    }
    exit();
}
?>
