<?php
// Importar la conexión a la base de datos
require __DIR__ . '/../../includes/config/DbConection.php';

$db = $conectarDB;
$sqlUsuarios = "SELECT u.id_usuario, u.nombre, u.apellido, r.nombre AS rol FROM usuario u JOIN rol r ON u.id_rol = r.id_rol";
$resultUsuarios = $db->query($sqlUsuarios);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Accesos</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</head>
<body>

<!-- Contenido -->
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Gestión de Accesos</h1>
                </div>
               
            </div>
        </div>
    </section>

    <section class="content">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Seleccionar Usuario</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="user-select">Seleccione un usuario:</label>
                    <select id="user-select" class="form-control">
                        <option value="" selected disabled>Seleccionar usuario</option>
                        <?php
                        if ($resultUsuarios->num_rows > 0) {
                            while ($row = $resultUsuarios->fetch_assoc()) {
                                echo "<option value='" . htmlspecialchars($row['id_usuario']) . "' data-rol='" . htmlspecialchars($row['rol']) . "'>";
                                echo htmlspecialchars($row['nombre']) . " " . htmlspecialchars($row['apellido']) . " (" . htmlspecialchars($row['rol']) . ")";
                                echo "</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                <div id="user-details" class="mt-4" style="display: none;">
                    <h5>Detalles del Usuario</h5>
                    <p id="user-name"></p>
                    <p id="user-role"></p>
                </div>
                <div id="access-list" class="mt-4" style="display: none;">
                    <h5>Accesos</h5>
                    <form id="access-form">
                        <input type="hidden" name="id_usuario" id="id_usuario" value="">
                        <div id="accesses">
                            <!-- Los accesos se cargarán aquí -->
                        </div>
                        <button type="submit" class="btn btn-primary mt-2">Guardar Accesos</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
$(document).ready(function() {
    $('#user-select').on('change', function() {
        var userId = $(this).val();
        var userName = $(this).find('option:selected').text();
        var userRole = $(this).find('option:selected').data('rol');
        
        // Mostrar nombre y rol del usuario
        $('#user-name').text("Nombre: " + userName);
        $('#user-role').text("Rol: " + userRole);
        $('#id_usuario').val(userId);
        $('#user-details').show();

        // Obtener accesos del usuario
        $.ajax({
            url: '/TINES/vistas/modulos/acceso/obtener_accesos.php',
            type: 'GET',
            data: { id: userId },
            success: function(response) {
                $('#accesses').html(response);
                $('#access-list').show();
            },
            error: function() {
                alert('Error al obtener los accesos del usuario.');
            }
        });
    });

    $('#access-form').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();

        $.ajax({
            url: '/TINES/vistas/modulos/acceso/guardar_accesos.php',
            type: 'POST',
            data: formData,
            success: function(response) {
                alert(response);
            },
            error: function() {
                alert('Error al guardar los accesos.');
            }
        });
    });
});
</script>

</body>
</html>