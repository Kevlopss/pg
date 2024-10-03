<?php
// Importar la conexión a la base de datos
require __DIR__ . '/../../includes/config/DbConection.php';
$db = $conectarDB;

// Configuración de la paginación
$registrosPorPagina = 25;
$paginaActual = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($paginaActual - 1) * $registrosPorPagina;

// Obtener el número total de registros
$totalRegistrosQuery = $db->query("SELECT COUNT(*) AS total FROM cliente");
$totalRegistros = $totalRegistrosQuery->fetch_assoc()['total'];
$totalPaginas = ceil($totalRegistros / $registrosPorPagina);

// Obtener los registros para la página actual
$sql = "SELECT * FROM cliente LIMIT $offset, $registrosPorPagina";
$result = $db->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>CRUD Clientes</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</head>
<body>

<!-- Contenido -->
<div class="content-wrapper">
    <section class="content-header bg-success text-white">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Clientes</h1>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <section class="content">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Lista de Clientes</h3>
                <button type="button" class="btn btn-success float-right" id="createClientBtn">+ CREAR CLIENTE</button>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Apellido</th>
                            <th>Correo</th>
                            <th>Teléfono</th>

                            <th>NIT</th>

                            <th>Tipo Cliente</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="clientesTableBody">
                        <?php
                        if ($result && $result->num_rows > 0) {
                            $contador = $offset + 1;
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr data-id='" . htmlspecialchars($row['id_cliente']) . "'>";
                                echo "<td>" . $contador . ".</td>";
                                echo "<td class='nombre'>" . htmlspecialchars($row['nombre']) . "</td>";
                                echo "<td class='apellido'>" . htmlspecialchars($row['apellido']) . "</td>";
                                echo "<td class='correo'>" . htmlspecialchars($row['correo']) . "</td>";
                                echo "<td class='telefono'>" . htmlspecialchars($row['telefono']) . "</td>";



                                echo "<td class='nit'>" . htmlspecialchars($row['nit']) . "</td>";



                                echo "<td class='tipo_cliente'>" . htmlspecialchars($row['tipo_cliente']) . "</td>";
                                echo "<td class='estado_cliente'>" . (($row['estado_cliente'] == 'activo') ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>') . "</td>";
                                
                                echo "<td>";
                                echo "<button class='btn btn-warning btn-sm edit-client-btn' data-id='" . htmlspecialchars($row['id_cliente']) . "'>Editar</button> ";
                                echo "<button class='btn btn-danger btn-sm delete-client-btn' data-id='" . htmlspecialchars($row['id_cliente']) . "'>Eliminar</button>";
                                echo "</td>";
                                echo "</tr>";
                                $contador++;
                            }
                        } else {
                            echo "<tr><td colspan='14'>No se encontraron clientes.</td></tr>";
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
<div class="modal fade" id="editClientModal" tabindex="-1" role="dialog" aria-labelledby="editClientModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editClientModalLabel">Editar Cliente</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Contenido del modal se cargará aquí -->
                <div id="modal-alert" class="alert alert-danger d-none"></div> <!-- Agregado para mostrar errores -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="saveChangesButton">Guardar cambios</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Crear -->
<div class="modal fade" id="createClientModal" tabindex="-1" role="dialog" aria-labelledby="createClientModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createClientModalLabel">Crear Cliente</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Contenido del modal se cargará aquí -->
                <div id="create-modal-alert" class="alert alert-danger d-none"></div> <!-- Agregado para mostrar errores -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-success" id="createClientButton">Crear Cliente</button>
            </div>
        </div>
    </div>
</div>

<script>$(document).ready(function() {
    // Editar cliente
    $('.edit-client-btn').on('click', function() {
        var clientId = $(this).data('id');
        
        // Hacer una petición AJAX para obtener el contenido de editar_cliente.php
        $.ajax({
            url: '/TINES/vistas/modulos/clientes/editar_cliente.php',
            type: 'GET',
            data: { id: clientId },
            success: function(response) {
                // Cargar el contenido en el modal
                $('#editClientModal .modal-body').html(response);
                
                // Mostrar el modal
                $('#editClientModal').modal('show');
            },
            error: function() {
                showAlert('Error al cargar el contenido del modal.', 'danger');
            }
        });
    });

    $('#saveChangesButton').on('click', function() {
        var form = $('#editClientModal form');
        $.ajax({
            url: form.attr('action'),
            type: form.attr('method'),
            data: form.serialize(),
            success: function(response) {
                if (response.includes('alerta incorrecto')) {
                    $('#modal-alert').html(response).removeClass('d-none');
                } else {
                    // Actualizar la fila correspondiente en la tabla
                    var clientId = form.find('input[name="id_cliente"]').val();
                    var row = $('tr[data-id="' + clientId + '"]');
                    row.find('.nombre').text(form.find('input[name="nombre"]').val());
                    row.find('.apellido').text(form.find('input[name="apellido"]').val());
                    row.find('.correo').text(form.find('input[name="correo"]').val());
                    row.find('.telefono').text(form.find('input[name="telefono"]').val());
                    row.find('.direccion').text(form.find('input[name="direccion"]').val());
                    row.find('.departamento').text(form.find('input[name="departamento"]').val());
                    row.find('.municipio').text(form.find('input[name="municipio"]').val());
                    row.find('.nit').text(form.find('input[name="nit"]').val());
                    row.find('.negocio').text(form.find('input[name="negocio"]').val());
                    row.find('.genero').text(form.find('select[name="genero"]').val());
                    row.find('.tipo_cliente').text(form.find('select[name="tipo_cliente"]').val());
                    row.find('.estado_cliente').html((form.find('select[name="estado_cliente"]').val() == 'activo') ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>');
                    // Cerrar el modal
                    $('#editClientModal').modal('hide');
                    // Mostrar mensaje de éxito
                    showAlert('Cliente actualizado correctamente.', 'success');
                    updateTableIndexes();
                    location.reload();
                }
            },
            error: function() {
                $('#modal-alert').html('Error al guardar los cambios.').removeClass('d-none');
            }
        });
    });

    // Crear cliente
    $('#createClientBtn').on('click', function() {
        // Hacer una petición AJAX para obtener el contenido de crear_cliente.php
        $.ajax({
            url: '/TINES/vistas/modulos/clientes/crear_cliente.php',
            type: 'GET',
            success: function(response) {
                // Cargar el contenido en el modal
                $('#createClientModal .modal-body').html(response);

                // Mostrar el modal
                $('#createClientModal').modal('show');
            },
            error: function() {
                showAlert('Error al cargar el contenido del modal.', 'danger');
            }
        });
    });

    $('#createClientButton').on('click', function() {
        var form = $('#createClientModal form');
        $.ajax({
            url: form.attr('action'),
            type: form.attr('method'),
            data: form.serialize(),
            success: function(response) {
                if (response.includes('alerta incorrecto')) {
                    $('#create-modal-alert').html(response).removeClass('d-none');
                } else {
                    // Actualizar la tabla con el nuevo cliente
                    var newClient = $(response).find('tr');
                    $('#clientesTableBody').append(newClient);
                    // Cerrar el modal
                    $('#createClientModal').modal('hide');
                    // Mostrar mensaje de éxito
                    showAlert('Cliente creado correctamente.', 'success');
                    // Actualizar la numeración
                    updateTableIndexes();
                    location.reload();
                }
            },
            error: function() {
                $('#create-modal-alert').html('Error al crear el cliente.').removeClass('d-none');
            }
        });
    });

    // Eliminar cliente
    $('.delete-client-btn').on('click', function() {
        if (confirm('¿Estás seguro de que deseas eliminar este cliente?')) {
            var clientId = $(this).data('id');

            $.ajax({
                url: '/TINES/vistas/modulos/clientes/eliminar_cliente.php',
                type: 'POST',
                data: { id_cliente: clientId },
                success: function(response) {
                    var jsonResponse = JSON.parse(response);
                    if (jsonResponse.status === 'success') {
                        showAlert('Cliente eliminado correctamente.', 'success');
                        $('tr[data-id="' + clientId + '"]').remove();
                        // Actualizar la numeración
                        updateTableIndexes();
                    } else {
                        showAlert(jsonResponse.message, 'danger');
                    }
                },
                error: function() {
                    showAlert('Error al eliminar el cliente.', 'danger');
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
        setTimeout(function() {
            $('#alert-container .alert').alert('close');
        }, 3000);
    }

    function updateTableIndexes() {
        $('#clientesTableBody tr').each(function(index) {
            $(this).find('td:first').text((index + 1) + '.');
        });
    }
});
</script>

</body>
</html>