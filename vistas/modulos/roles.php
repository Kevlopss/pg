<?php
// Importar la conexión a la base de datos
require __DIR__ . '/../../includes/config/DbConection.php';

$db = $conectarDB;

// Configuración de la paginación
$registrosPorPagina = 25;
$paginaActual = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($paginaActual - 1) * $registrosPorPagina;

// Obtener el número total de registros
$totalRegistrosQuery = $db->query("SELECT COUNT(*) AS total FROM rol");
$totalRegistros = $totalRegistrosQuery->fetch_assoc()['total'];
$totalPaginas = ceil($totalRegistros / $registrosPorPagina);

// Obtener los registros para la página actual
$sql = "SELECT id_rol, nombre, estado, fecha_crea FROM rol LIMIT $offset, $registrosPorPagina";
$result = $db->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>CRUD Roles</title>
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
                    <h1>Roles</h1>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <section class="content">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Lista de Roles</h3>
                <button type="button" class="btn btn-success float-right" id="createRoleBtn">+ CREAR ROL</button>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Tipo rol</th>
                            <th>Estado</th>
                            <th>Fecha creado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="rolesTableBody">
                        <?php
                        if ($result && $result->num_rows > 0) {
                            $contador = $offset + 1;
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr data-id='" . htmlspecialchars($row['id_rol']) . "'>";
                                echo "<td>" . $contador . ".</td>";
                                echo "<td class='nombre'>" . htmlspecialchars($row['nombre']) . "</td>";
                                echo "<td class='estado'>" . (($row['estado'] == 1) ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>') . "</td>";
                                echo "<td class='fecha_crea'>" . htmlspecialchars($row['fecha_crea']) . "</td>";
                                echo "<td>";
                                echo "<button class='btn btn-warning btn-sm edit-role-btn' data-id='" . htmlspecialchars($row['id_rol']) . "'>Editar</button> ";
                                echo "<button class='btn btn-danger btn-sm delete-role-btn' data-id='" . htmlspecialchars($row['id_rol']) . "'>Eliminar</button>";
                                echo "</td>";
                                echo "</tr>";
                                $contador++;
                            }
                        } else {
                            echo "<tr><td colspan='5'>No se encontraron roles.</td></tr>";
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
<div class="modal fade" id="editRoleModal" tabindex="-1" role="dialog" aria-labelledby="editRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editRoleModalLabel">Editar Rol</h5>
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
<div class="modal fade" id="createRoleModal" tabindex="-1" role="dialog" aria-labelledby="createRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createRoleModalLabel">Crear Rol</h5>
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
                <button type="button" class="btn btn-success" id="createRoleButton">Crear Rol</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Editar rol
    $('.edit-role-btn').on('click', function() {
        var roleId = $(this).data('id');

        // Hacer una petición AJAX para obtener el contenido de editar_rol.php
        $.ajax({
            url: '/TINES/vistas/modulos/roles/editar_rol.php',
            type: 'GET',
            data: { id: roleId },
            success: function(response) {
                // Cargar el contenido en el modal
                $('#editRoleModal .modal-body').html(response);

                // Mostrar el modal
                $('#editRoleModal').modal('show');
            },
            error: function() {
                showAlert('Error al cargar el contenido del modal.', 'danger');
            }
        });
    });

    $('#saveChangesButton').on('click', function() {
        var form = $('#editRoleModal form');
        $.ajax({
            url: form.attr('action'),
            type: form.attr('method'),
            data: form.serialize(),
            success: function(response) {
                if (response.includes('alerta incorrecto')) {
                    $('#modal-alert').html(response).removeClass('d-none');
                } else {
                    // Actualizar la fila correspondiente en la tabla
                    var roleId = form.find('input[name="id_rol"]').val();
                    var row = $('tr[data-id="' + roleId + '"]');
                    row.find('.nombre').text(form.find('input[name="nombre"]').val());
                    row.find('.estado').html((form.find('select[name="estado"]').val() == 1) ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>');
                    // Cerrar el modal
                    $('#editRoleModal').modal('hide');
                    // Mostrar mensaje de éxito
                    showAlert('Rol actualizado correctamente.', 'success');
                }
            },
            error: function() {
                $('#modal-alert').html('Error al guardar los cambios.').removeClass('d-none');
            }
        });
    });

    // Crear rol
    $('#createRoleBtn').on('click', function() {
        // Hacer una petición AJAX para obtener el contenido de crear_rol.php
        $.ajax({
            url: '/TINES/vistas/modulos/roles/crear_rol.php',
            type: 'GET',
            success: function(response) {
                // Cargar el contenido en el modal
                $('#createRoleModal .modal-body').html(response);

                // Mostrar el modal
                $('#createRoleModal').modal('show');
            },
            error: function() {
                showAlert('Error al cargar el contenido del modal.', 'danger');
            }
        });
    });

    $('#createRoleButton').on('click', function() {
        var form = $('#createRoleModal form');
        $.ajax({
            url: form.attr('action'),
            type: form.attr('method'),
            data: form.serialize(),
            success: function(response) {
                if (response.includes('alerta incorrecto')) {
                    $('#create-modal-alert').html(response).removeClass('d-none');
                } else {
                    // Actualizar la tabla con el nuevo rol
                    var newRole = $(response).find('tr');
                    $('#rolesTableBody').append(newRole);
                    // Cerrar el modal
                    $('#createRoleModal').modal('hide');
                    // Mostrar mensaje de éxito
                    showAlert('Rol creado correctamente.', 'success'); 
                    setTimeout(function() {
                           
                        location.reload(); // Opcional: recargar la página para ver los cambios
                            
                        }, 2000);
                    
                    // Actualizar la numeración
                    updateTableIndexes();
                }
            },
            error: function() {
                $('#create-modal-alert').html('Error al crear el rol.').removeClass('d-none');
            }
        });
    });

    // Eliminar rol
    $('.delete-role-btn').on('click', function() {
        if (confirm('¿Estás seguro de que deseas eliminar este rol?')) {
            var roleId = $(this).data('id');

            $.ajax({
                url: '/TINES/vistas/modulos/roles/eliminar_rol.php',
                type: 'POST',
                data: { id_rol: roleId },
                success: function(response) {
                    var jsonResponse = JSON.parse(response);
                    if (jsonResponse.status === 'success') {
                        showAlert('Rol eliminado correctamente.', 'success');
                        $('tr[data-id="' + roleId + '"]').remove();
                        // Actualizar la numeración
                        updateTableIndexes();
                    } else {
                        showAlert(jsonResponse.message, 'danger');
                    }
                },
                error: function() {
                    showAlert('Error al eliminar el rol.', 'danger');
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
        $('#rolesTableBody tr').each(function(index) {
            $(this).find('td:first').text((index + 1) + '.');
        });
    }
});
</script>

</body>
</html>
