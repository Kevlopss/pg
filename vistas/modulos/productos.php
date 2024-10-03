<?php
require __DIR__ . '/../../includes/config/DbConection.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$db = $conectarDB;

// Configuración de la paginación
$registrosPorPagina = 25;
$paginaActual = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($paginaActual - 1) * $registrosPorPagina;

$filters = [];
$params = [];
$sql = "
    SELECT 
        e.*, 
        c.Id_presentacion_producto, c.Nombre AS Pres_producto, c.Medida,
        t.Id_tipo_producto, t.Nombre AS Nombre_tipo,
        tt.Id_categoria_producto, tt.Nombre AS Nombre_cat
    FROM producto AS e
    LEFT JOIN presentacion_producto AS c ON e.Id_presentacion_producto = c.Id_presentacion_producto
    LEFT JOIN tipo_producto AS t ON e.Id_tipo_producto = t.Id_tipo_producto
    LEFT JOIN categoria_producto AS tt ON e.Id_categoria_producto = tt.Id_categoria_producto";

// Agregar filtros basados en la solicitud POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name_product = isset($_POST['nameProducto']) ? trim($_POST['nameProducto']) : '';
    $categoria_product = isset($_POST['cat']) ? trim($_POST['cat']) : '';
    $tipo_product = isset($_POST['tipo']) ? trim($_POST['tipo']) : '';
    $presentacion_product = isset($_POST['pres']) ? trim($_POST['pres']) : '';

    if (!empty($name_product)) {
        $filters[] = "(UPPER(e.Nombre) LIKE ? OR e.Cod_producto LIKE ?)";
        $params[] = "%" . strtoupper($name_product) . "%";
        $params[] = "%" . $name_product . "%";
    }
    if (!empty($categoria_product)) {
        $filters[] = "UPPER(tt.Nombre) LIKE ?";
        $params[] = "%" . strtoupper($categoria_product) . "%";
    }
    if (!empty($tipo_product)) {
        $filters[] = "UPPER(t.Nombre) LIKE ?";
        $params[] = "%" . strtoupper($tipo_product) . "%";
    }
    if (!empty($presentacion_product)) {
        $filters[] = "UPPER(c.Nombre) LIKE ?";
        $params[] = "%" . strtoupper($presentacion_product) . "%";
    }

    if (count($filters) > 0) {
        $sql .= " WHERE " . implode(" AND ", $filters);
    }
}

// Obtener el número total de registros
$totalRegistrosQuery = $db->prepare("SELECT COUNT(*) AS total FROM producto AS e
    LEFT JOIN presentacion_producto AS c ON e.Id_presentacion_producto = c.Id_presentacion_producto
    LEFT JOIN tipo_producto AS t ON e.Id_tipo_producto = t.Id_tipo_producto
    LEFT JOIN categoria_producto AS tt ON e.Id_categoria_producto = tt.Id_categoria_producto" 
    . (count($filters) > 0 ? " WHERE " . implode(" AND ", $filters) : ""));
if (count($params) > 0) {
    $totalRegistrosQuery->bind_param(str_repeat('s', count($params)), ...$params);
}
$totalRegistrosQuery->execute();
$totalRegistros = $totalRegistrosQuery->get_result()->fetch_assoc()['total'];
$totalPaginas = ceil($totalRegistros / $registrosPorPagina);

$sql .= " LIMIT $offset, $registrosPorPagina";

$stmt = $db->prepare($sql);
if (count($params) > 0) {
    $stmt->bind_param(str_repeat('s', count($params)), ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Función para recorrer los resultados y generar las filas de la tabla
function ShowTables($result, $offset) {
    $contador = $offset + 1;
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td class='id-col'>" . $contador . ".</td>";
        echo "<td>" . htmlspecialchars($row['Cod_producto']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Nombre']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Descripcion']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Pres_producto'] ." (". $row['Medida']) .")". "</td>";
        echo "<td>" . htmlspecialchars($row['Nombre_tipo']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Nombre_cat']) . "</td>";
        echo '<td>' . ($row["Estado"] == 1 ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>') . '</td>';
        echo "<td>";
        echo "<button type='button' class='btn btn-primary ver-btn' id='ver' data-id='" . htmlspecialchars($row['Id_producto']) . "'>Ver</button> ";
        echo "<button type='button' class='btn btn-success' id='editar' data-id='".htmlspecialchars($row['Id_producto'])."' onclick='loadEditModal(".htmlspecialchars($row['Id_producto']).")'>Editar</button>";
        echo "<button type='button' class='btn btn-danger btn-sm delete-user-btn' id='eliminar' data-id='" . htmlspecialchars($row['Id_producto']) . "'>Eliminar</button>";
        echo "</td>";
        echo "</tr>";
        $contador++;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Productos</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <style>
        #h4{
            color:gray;
        }
        #ver{
            background-color: #1C379C;
            border-color: #1C379C;
        }
        #eliminar{
            height: 38.5px;
            margin-left: 10px;
            font-size: 16px;
        }
        tr:nth-child(even) {
            background-color: #ddd; 
        }
        td:last-child{
            /* Ancho de la última columna(donde estas las acciones R-U-D) */
            width:240px;
        }
        tbody tr:hover{
            background-color: #D6EAF8;
        }
        #user-select {
            display: inline-block;
            width: 12%;
        }
        #createUserBtn {
            margin-left: 10px;
            background-color: #1C379C;
            border-color: #1C379C;
            position: absolute;
        }
        #createUserBtn:hover {
            background-color: #267FBB;
            border-color: #267FBB;
        }
        #createProductBtn, #reporte, #editar {
            margin-left: 7px;
            background-color: #1C379C;
            border-color: #1C379C;
            font-size: 16px;
        }
        #createProductBtn:hover, #reporte:hover, #editar:hover, #ver:hover {
            background-color: #267FBB;
            border-color: #267FBB;
        }
        #colorProducto {
            background-color: #1C379C;
            color: white;
        }
        thead {
            background-color: #1C379C;
            color: white;
        }
        tr td:first-child {
            background-color: #1C379C;
            color: white;
        }
        input {
            padding: 6px;
            display: inline-block;
            margin-right: 1px;
            color: black;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }
        input[type="text"]::placeholder {
            font-size: 16px;
            color: #555;
        }
        #modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            padding: 20px;
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        }
        #modal.active {
            display: block;
        }
        .modal-header{
            background-color: #1C379C;
            color: white;
            border-top-right-radius: 2px;
            border-top-left-radius: 2px;
        }
        .modal-header .close {
            color: white;
        }
        .modal-footer{
            background-color: #1C379C;
            border-bottom-left-radius: 2px;
            border-bottom-right-radius: 2px;
        }
        #cerrar{
            background-color: #C2281E;
            border: none;
            font-size: 18px;
        }
        #cerrar:hover{
            background-color: #F34545;
        }
        #saveChangesButton{
            background-color: green ;
            border-color: green;
            border: 2px solid green;
            font-size: 17px;
        }
        #saveChangesButton:hover{
            background-color: #278C21;
            border-color: #278C21;
        }
    </style>
</head>
<body>

<!-- Contenido -->
<div class="content-wrapper">
    <section class="content-header" id="colorProducto">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Productos</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="card">
            <form method='POST' action=''>
            <div class="card-header" id="cardHeader">
                <input type="text" id="user-select" class="form-control" name="nameProducto" placeholder="Nombre del producto" value="">
                <select id="user-select" class="form-control" name="cat">
                    <option value="" selected disabled>Categoría</option>
                    <?php
                    $sqlSelect = "SELECT * FROM categoria_producto";
                    $resultado = $db->query($sqlSelect);
                    if ($resultado->num_rows > 0) {
                        while ($row = $resultado->fetch_assoc()) {
                            echo '<option value="' . htmlspecialchars($row['Nombre']) . '">';
                            echo htmlspecialchars($row['Nombre']);
                            echo "</option>";
                        }
                    }
                    ?>
                </select>
                <select id="user-select" class="form-control" name="tipo">
                    <option value="" selected disabled>Tipo</option>
                    <?php
                    $sqlSelect = "SELECT * FROM tipo_producto";
                    $resultado = $db->query($sqlSelect);
                    if ($resultado->num_rows > 0) {
                        while ($row = $resultado->fetch_assoc()) {
                            echo '<option value="' . htmlspecialchars($row['Nombre']) . '">';
                            echo htmlspecialchars($row['Nombre']);
                            echo "</option>";
                        }
                    }
                    ?>
                </select>
                <select id="user-select" class="form-control" name="pres">
                    <option value="" selected disabled>Presentación</option>
                    <?php
                    $sqlSelect = "SELECT * FROM presentacion_producto";
                    $resultado = $db->query($sqlSelect);
                    if ($resultado->num_rows > 0) {
                        while ($row = $resultado->fetch_assoc()) {
                            echo '<option value="' . htmlspecialchars($row['Nombre']) . '">';
                            echo htmlspecialchars($row['Nombre']);
                            echo "</option>";
                        }
                    }
                    ?>
                </select>
                <button type="submit" class="btn btn-success" id="createUserBtn">Buscar</button>
                <button type="button" class="btn btn-success float-right" id="createProductBtn" data-toggle="modal" data-target="#createProductModal"> + Crear producto</button>
                <button type="button" class="btn btn-success float-right" id="reporte">Reporte</button>
            </div>
            </form>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Código</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Presentación</th>
                            <th>Tipo de producto</th>
                            <th>Categoría</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Verificar si hay resultados
                        if ($result && $result->num_rows > 0) {
                            ShowTables($result, $offset);
                        } else {
                            echo "<tr><td colspan='9'>No se encontraron registros.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>

                <!-- Paginación -->
                <nav>
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                            <li class="page-item <?php if ($i == $paginaActual) echo 'active'; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </section>
</div>

<!-- Contenedor para mensajes flotantes -->
<div id="alert-container" class="position-fixed top-0 end-0 p-3" style="top: 10px; right: 20px; z-index: 1050;"></div>

<!-- Modal Editar -->
<style>
  .custom-modal-width {
    max-width: 50%; /* Ajusta este valor según tus necesidades: ancho de la modal */
  }
  .custom-modal-height{
    max-width: 30%;
  }
</style>

<div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-labelledby="editUserModalLabel" aria-hidden="true">
  <div class="modal-dialog custom-modal-width" role="document"> <!-- Aquí agregamos la clase personalizada -->
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title" id="editUserModalLabel">Editar producto</h2>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <!-- Contenido del modal se cargará aquí -->
        <div id="modal-alert" class="alert alert-danger d-none"></div>
        <div id="modalContent"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal" id="cerrar">Cerrar</button>
        <button type="button" class="btn btn-primary" id="saveChangesButton">Guardar cambios</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Crear -->
<div class="modal fade" id="createProductModal" tabindex="-1" role="dialog" aria-labelledby="createProductModalLabel" aria-hidden="true">
  <div class="modal-dialog custom-modal-height" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title" id="createProductModalLabel">Crear Producto</h2>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <!-- Contenido del modal se cargará aquí -->
        <div id="create-product-modal-alert" class="alert alert-danger d-none"></div> <!-- Agregado para mostrar errores -->
        <div id="createProductModalContent"></div> <!-- Contenido del modal de crear producto -->
      </div>
      <div class="modal-footer">
      </div>
    </div>
  </div>
</div>
<!-- Modal Ver -->
<div class="modal fade" id="productModal" tabindex="-1" role="dialog" aria-labelledby="productModalLabel" aria-hidden="true">
  <div class="modal-dialog custom-modal-width" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="productModalLabel">Detalles del Producto</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="productDetails">
        <!-- Aquí se mostrarán los detalles del producto -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal" id="cerrar">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<script>
    function loadEditModal(id) {
        $.ajax({
            url: '/TINES/vistas/modulos/producto/editar_producto.php',
            type: 'GET',
            data: { id: id },
            dataType: 'html',
            success: function(response) {
                $('#modalContent').html(response);
                $('#editUserModal').modal('show');
            },
            error: function() {
                $('#modal-alert').removeClass('d-none').text('Error al cargar el contenido del producto.');
            }
        });
    }

    $(document).ready(function() {
        $(document).on('click', '#saveChangesButton', function() {
            var form = $('#editProductForm')[0];
            var formData = new FormData(form);

            $.ajax({
                url: form.action,
                type: 'POST',
                data: formData,
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.status === 'success') {
                        $('#modal-alert').addClass('d-none');
                        $('#modalContent').prepend('<div class="alert alert-success">' + response.message + '</div>');
                        setTimeout(function() {
                            $('#editUserModal').modal('hide');
                            location.reload();
                        }, 1500);
                    } else {
                        $('#modal-alert').removeClass('d-none').text(response.message);
                    }
                },
                error: function() {
                    $('#modal-alert').removeClass('d-none').text('Error al guardar los cambios.');
                }
            });
        });
    });

    // Mostrar el modal para crear producto
    $('#createProductBtn').on('click', function() {
        // Hacer una petición AJAX para obtener el contenido de crear_producto.php
        $.ajax({
            url: '/TINES/vistas/modulos/producto/crear_producto.php',
            type: 'GET',
            success: function(response) {
                // Cargar el contenido en el modal
                $('#createProductModalContent').html(response);
                
                // Mostrar el modal
                $('#createProductModal').modal('show');
            },
            error: function() {
                alert('Error al cargar el contenido del modal.');
            }
        });
    });

// Manejar la creación del producto
$('#createProductButton').on('click', function() {
    var form = $('#createProductModal form')[0]; // Obtener el primer formulario dentro del modal
    var formData = new FormData(form);

    $.ajax({
        url: form.action,
        type: form.method,
        data: formData,
        contentType: false, // Evitar que jQuery configure el contentType
        processData: false, // Evitar que jQuery procese el data
        success: function(response) {
            var responseObj = JSON.parse(response);
            if (responseObj.status === 'error') {
                $('#create-product-modal-alert').html(responseObj.message).removeClass('d-none');
                setTimeout(function() {
                    location.reload(); // Recargar la página después de mostrar el mensaje de error
                }, 2000);
            } else {
                $('#createProductModal .modal-body').html('<div class="alert alert-success">' + responseObj.message + '</div>');
                if (responseObj.status === 'success') {
                    setTimeout(function() {
                        $('#createProductModal').modal('hide');
                        location.reload(); // Recargar la página para ver los cambios
                    }, 2000);
                }
            }
        },
        error: function() {
            $('#create-product-modal-alert').html('Error al crear el producto.').removeClass('d-none');
            setTimeout(function() {
                location.reload(); // Recargar la página después de mostrar el mensaje de error
            }, 2000);
        }
    });
});



    $(document).ready(function() {
        // Eliminar producto
        $('.delete-user-btn').on('click', function() {
            if (confirm('¿Estás seguro de que deseas eliminar este producto?')) {
                var userId = $(this).data('id');
                
                $.ajax({
                    url: '/TINES/vistas/modulos/producto/eliminar_producto.php',
                    type: 'POST',
                    data: { id: userId },
                    success: function(response) {
                        var jsonResponse = JSON.parse(response);
                        if (jsonResponse.status === 'success') {
                            // Mostrar mensaje de éxito
                            showAlert('Producto eliminado correctamente.', 'success');
                            // Eliminar la fila de la tabla
                            $('button[data-id="' + userId + '"]').closest('tr').remove();
                        } else {
                            showAlert(jsonResponse.message, 'danger');
                        }
                    },
                    error: function() {
                        showAlert('Error al eliminar el producto.', 'danger');
                    }
                });
            }
        });

        function showAlert(message, type) {
            var alertHtml = '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
                            message +
                            '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                            '<span aria-hidden="true">&times;</span>' +
                            '</button>' +
                            '</div>';
            $('#alert-container').html(alertHtml);
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.ver-btn').forEach(function(button) {
            button.addEventListener('click', function() {
                var id = this.getAttribute('data-id');
                loadVerModal(id);
            });
        });
    });

    function loadVerModal(id) {
        // Aquí va tu código para cargar el modal con AJAX
        $.ajax({
            url: '/TINES/vistas/modulos/producto/ver_producto.php',
            type: 'GET',
            data: { id: id },
            success: function(response) {
                // Código para mostrar el modal con los datos obtenidos
                $('#productDetails').html(response);
                $('#productModal').modal('show');
            },
            error: function() {
                alert('Error al cargar los datos del producto.');
            }
        });
    }
</script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</body>
</html>
