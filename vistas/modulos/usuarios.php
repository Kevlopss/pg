<?php
// Importar la conexión a la base de datos
require __DIR__ . '/../../includes/config/DbConection.php';

$db = $conectarDB; // Línea agregada

// Configuración de la paginación
$registrosPorPagina = 25;
$paginaActual = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($paginaActual - 1) * $registrosPorPagina;

// Obtener el número total de registros
$totalRegistrosQuery = $db->query("SELECT COUNT(*) AS total FROM usuario");
$totalRegistros = $totalRegistrosQuery->fetch_assoc()['total'];
$totalPaginas = ceil($totalRegistros / $registrosPorPagina);

// Obtener los registros para la página actual con JOIN a la tabla de roles
$sql = "SELECT usuario.*, rol.nombre AS nombre_rol 
        FROM usuario 
        LEFT JOIN rol ON usuario.id_rol = rol.id_rol 
        LIMIT $offset, $registrosPorPagina";
$result = $db->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>CRUD Usuarios</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <style>
        th, .id-col {
            background-color: green;
            color: white;
        }
    </style>
</head>
<body>

<!-- Contenido -->
<div class="content-wrapper">
    <section class="content-header text-white" style="background-color: green;">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Usuarios</h1>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <section class="content">
        <div class="card">
            <div class="card-header">
                <button type="button" class="btn btn-success float-right" style="background-color: green;" id="createUserBtn">+ CREAR USUARIO</button>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Apellido</th>
                            <th>Correo</th>
                            <th>Telefono</th>
                            <th>Usuario</th>
                            <th>Clave</th>
                            <th>Estado</th>
                            <th>Fecha Crea</th>
                            <th>Fecha Modifica</th>
                            <th>Rol</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="usuariosTableBody">
                        <?php
                        if ($result && $result->num_rows > 0) {
                            $contador = $offset + 1;
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr data-id='" . htmlspecialchars($row['id_usuario']) . "'>";
                                echo "<td class='id-col'>" . $contador . ".</td>";
                                echo "<td class='nombre'>" . htmlspecialchars($row['nombre']) . "</td>";
                                echo "<td class='apellido'>" . htmlspecialchars($row['apellido']) . "</td>";
                                echo "<td class='correo'>" . htmlspecialchars($row['correo']) . "</td>";
                                echo "<td class='colegiado'>" . htmlspecialchars($row['colegiado']) . "</td>";
                                echo "<td class='usuario'>" . htmlspecialchars($row['usuario']) . "</td>";
                                echo "<td class='clave'>" . htmlspecialchars($row['clave']) . "</td>";
                                echo "<td class='estado'>" . (($row['estado'] == 1) ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>') . "</td>";
                                echo "<td class='fecha_crea'>" . htmlspecialchars($row['fecha_crea']) . "</td>";
                                echo "<td class='fecha_modifica'>" . htmlspecialchars($row['fecha_modifica']) . "</td>";
                                echo "<td class='id_rol'>" . htmlspecialchars($row['nombre_rol']) . "</td>";
                                
                                echo "<td>";
                                echo "<button class='btn btn-warning btn-sm edit-user-btn' data-id='" . htmlspecialchars($row['id_usuario']) . "'>Editar</button> ";
                                echo "<button class='btn btn-danger btn-sm delete-user-btn' data-id='" . htmlspecialchars($row['id_usuario']) . "'>Eliminar</button>";
                                echo "</td>";
                                echo "</tr>";
                                $contador++;
                            }
                        } else {
                            echo "<tr><td colspan='12'>No se encontraron usuarios.</td></tr>";
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
<div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">Editar Usuario</h5>
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
<div class="modal fade" id="createUserModal" tabindex="-1" role="dialog" aria-labelledby="createUserModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createUserModalLabel">Crear Usuario</h5>
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
                <button type="button" class="btn btn-success" id="createUserButton">Crear Usuario</button>
            </div>
        </div>
    </div>
</div>

<script>$(document).ready(function() {
    // Editar usuario
    $('.edit-user-btn').on('click', function() {
        var userId = $(this).data('id');
        
        // Hacer una petición AJAX para obtener el contenido de editar_usuario.php
        $.ajax({
            url: '/TINES/vistas/modulos/usuarios/editar_usuario.php',
            type: 'GET',
            data: { id: userId },
            success: function(response) {
                // Cargar el contenido en el modal
                $('#editUserModal .modal-body').html(response);
                
                // Mostrar el modal
                $('#editUserModal').modal('show');
            },
            error: function() {
                showAlert('Error al cargar el contenido del modal.', 'danger');
            }
        });
    });

    $('#saveChangesButton').on('click', function() {
        var form = $('#editUserModal form');
        $.ajax({
            url: form.attr('action'),
            type: form.attr('method'),
            data: form.serialize(),
            success: function(response) {
                if (response.includes('alerta incorrecto')) {
                    $('#modal-alert').html(response).removeClass('d-none');
                } else {
                    // Actualizar la fila correspondiente en la tabla
                    var userId = form.find('input[name="id_usuario"]').val();
                    var row = $('tr[data-id="' + userId + '"]');
                    row.find('.nombre').text(form.find('input[name="nombre"]').val());
                    row.find('.apellido').text(form.find('input[name="apellido"]').val());
                    row.find('.correo').text(form.find('input[name="correo"]').val());
                    row.find('.colegiado').text(form.find('input[name="colegiado"]').val());
                    row.find('.usuario').text(form.find('input[name="usuario"]').val());
                    row.find('.clave').text(form.find('input[name="clave"]').val());
                    row.find('.estado').html((form.find('select[name="estado"]').val() == 1) ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>');
                    row.find('.fecha_modifica').text(new Date().toLocaleString());
                    row.find('.id_rol').text(form.find('input[name="id_rol"]').val());
                    // Cerrar el modal
                    $('#editUserModal').modal('hide');
                    // Mostrar mensaje de éxito
                    showAlert('Usuario actualizado correctamente.', 'success');
                    updateTableIndexes();
                    location.reload();
                }
            },
            error: function() {
                $('#modal-alert').html('Error al guardar los cambios.').removeClass('d-none');
            }
        });
    });

    // Crear usuario
    $('#createUserBtn').on('click', function() {
        // Hacer una petición AJAX para obtener el contenido de crear_usuario.php
        $.ajax({
            url: '/TINES/vistas/modulos/usuarios/crear_usuario.php',
            type: 'GET',
            success: function(response) {
                // Cargar el contenido en el modal
                $('#createUserModal .modal-body').html(response);

                // Mostrar el modal
                $('#createUserModal').modal('show');
            },
            error: function() {
                showAlert('Error al cargar el contenido del modal.', 'danger');
            }
        });
    });

    $('#createUserButton').on('click', function() {
        var form = $('#createUserModal form');
        $.ajax({
            url: form.attr('action'),
            type: form.attr('method'),
            data: form.serialize(),
            success: function(response) {
                if (response.includes('alerta incorrecto')) {
                    $('#create-modal-alert').html(response).removeClass('d-none');
                } else {
                    // Actualizar la tabla con el nuevo usuario
                    var newUser = $(response).find('tr');
                    $('#usuariosTableBody').append(newUser);
                    // Cerrar el modal
                    $('#createUserModal').modal('hide');
                    // Mostrar mensaje de éxito
                    showAlert('Usuario creado correctamente.', 'success');
                    // Actualizar la numeración
                    updateTableIndexes();
                    location.reload();
                }
            },
            error: function() {
                $('#create-modal-alert').html('Error al crear el usuario.').removeClass('d-none');
            }
        });
    });

    // Eliminar usuario
    $('.delete-user-btn').on('click', function() {
        if (confirm('¿Estás seguro de que deseas eliminar este usuario?')) {
            var userId = $(this).data('id');

            $.ajax({
                url: '/TINES/vistas/modulos/usuarios/eliminar_usuario.php',
                type: 'POST',
                data: { id_usuario: userId },
                success: function(response) {
                    var jsonResponse = JSON.parse(response);
                    if (jsonResponse.status === 'success') {
                        showAlert('Usuario eliminado correctamente.', 'success');
                        $('tr[data-id="' + userId + '"]').remove();
                        // Actualizar la numeración
                        updateTableIndexes();
                    } else {
                        showAlert(jsonResponse.message, 'danger');
                    }
                },
                error: function() {
                    showAlert('Error al eliminar el usuario.', 'danger');
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
        $('#usuariosTableBody tr').each(function(index) {
            $(this).find('td:first').text((index + 1) + '.');
        });
    }
});
</script>

</body>
</html>
