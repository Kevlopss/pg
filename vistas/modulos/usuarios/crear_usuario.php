<?php
// Importar la conexión a la base de datos
require __DIR__ . '/../../../includes/config/DbConection.php';

$db = $conectarDB;

// Procesar el formulario de creación si se ha enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = htmlspecialchars($_POST['nombre']);
    $apellido = htmlspecialchars($_POST['apellido']);
    $correo = htmlspecialchars($_POST['correo']);
    $colegiado = htmlspecialchars($_POST['colegiado']);
    $usuario = htmlspecialchars($_POST['usuario']);
    $clave = htmlspecialchars($_POST['clave']);
    $estado = htmlspecialchars($_POST['estado']);
    $id_rol = htmlspecialchars($_POST['id_rol']);

    $sql = "INSERT INTO usuario (nombre, apellido, correo, colegiado, usuario, clave, estado, id_rol, fecha_crea) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $db->prepare($sql);
    $stmt->bind_param('ssssssii', $nombre, $apellido, $correo, $colegiado, $usuario, $clave, $estado, $id_rol);

    if ($stmt->execute()) {
        echo "<tr data-id='" . $stmt->insert_id . "'>";
        echo "<td></td>";
        echo "<td class='nombre'>" . htmlspecialchars($nombre) . "</td>";
        echo "<td class='apellido'>" . htmlspecialchars($apellido) . "</td>";
        echo "<td class='correo'>" . htmlspecialchars($correo) . "</td>";
        echo "<td class='colegiado'>" . htmlspecialchars($colegiado) . "</td>";
        echo "<td class='usuario'>" . htmlspecialchars($usuario) . "</td>";
        echo "<td class='clave'>" . htmlspecialchars($clave) . "</td>";
        echo "<td class='estado'>" . (($estado == 1) ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>') . "</td>";
        echo "<td class='fecha_crea'>" . date('Y-m-d H:i:s') . "</td>";
        echo "<td class='fecha_modifica'></td>";
        echo "<td class='id_rol'>" . htmlspecialchars($id_rol) . "</td>";
        echo "<td>";
        echo "<button class='btn btn-warning btn-sm edit-user-btn' data-id='" . $stmt->insert_id . "'>Editar</button> ";
        echo "<button class='btn btn-danger btn-sm delete-user-btn' data-id='" . $stmt->insert_id . "'>Eliminar</button>";
        echo "</td>";
        echo "</tr>";
    } else {
        echo "Error al crear el usuario: " . $stmt->error;
    }

    $stmt->close();
    exit;
}

// Comprobar si es una petición AJAX
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if (!$isAjax) {
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Usuario</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        form {
            width: 80%;
        }
        .form-group {
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
<?php
}
?>
    <form id="formularioCrearUsuario" action="/TINES/vistas/modulos/usuarios/crear_usuario.php" method="POST"> <!-- Asegurarse de que la ruta es correcta -->
        <h1>Crear Usuario</h1>
        <legend>
            <p>Completar los datos del nuevo usuario</p>
        </legend>
        <div class="form-group">
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" required class="form-control">
        </div>
        <div class="form-group">
            <label for="apellido">Apellido:</label>
            <input type="text" id="apellido" name="apellido" required class="form-control">
        </div>
        <div class="form-group">
            <label for="correo">Correo:</label>
            <input type="email" id="correo" name="correo" required class="form-control">
        </div>
        <div class="form-group">
            <label for="colegiado">Telefono:</label>
            <input type="text" id="colegiado" name="colegiado" required class="form-control">
        </div>
        <div class="form-group">
            <label for="usuario">Usuario:</label>
            <input type="text" id="usuario" name="usuario" required class="form-control">
        </div>
        <div class="form-group">
            <label for="clave">Clave:</label>
            <input type="text" id="clave" name="clave" required class="form-control">
        </div>
        <div class="form-group">
            <label for="estado">Estado:</label>
            <select id="estado" name="estado" required class="form-control">
                <option value="1">Activo</option>
                <option value="0">Inactivo</option>
            </select>
        </div>
        <div class="form-group">
            <label for="id_rol">Rol:</label>
            <!-- <input type="text" id="id_rol" name="id_rol" required class="form-control"> -->


            <select id="id_rol" name="id_rol" required class="form-control">
                <?php
                $sqlSelect = "SELECT id_rol , nombre FROM rol";
                $resultado = $db->query($sqlSelect);
                if ($resultado->num_rows > 0) {
                    while ($row = $resultado->fetch_assoc()) {
                        echo '<option value="' . htmlspecialchars($row['id_rol']) . '">';
                        echo htmlspecialchars($row['nombre']);
                        echo "</option>";
                    }
                }
                ?>
            </select>



        </div>
    </form>
<?php
if (!$isAjax) {
?>
</body>
</html>
<?php
}
?>
