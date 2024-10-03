<?php
// Importar la conexión a la base de datos
require __DIR__ . '/../../../includes/config/DbConection.php';

// Verificar si el ID del usuario se ha pasado como parámetro en POST
if (isset($_POST['id_usuario'])) {
    $id = htmlspecialchars($_POST['id_usuario']);
} elseif (isset($_GET['id'])) {
    // Verificar si el ID del usuario se ha pasado como parámetro en GET
    $id = htmlspecialchars($_GET['id']);
} else {
    echo "No se ha proporcionado un ID de usuario.";
    exit;
}

$db = $conectarDB;

// Procesar el formulario de actualización si se ha enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = htmlspecialchars($_POST['nombre']);
    $apellido = htmlspecialchars($_POST['apellido']);
    $correo = htmlspecialchars($_POST['correo']);
    $colegiado = htmlspecialchars($_POST['colegiado']);
    $usuario = htmlspecialchars($_POST['usuario']);
    $clave = htmlspecialchars($_POST['clave']);
    $estado = htmlspecialchars($_POST['estado']);
    $id_rol = htmlspecialchars($_POST['id_rol']);

    $sql = "UPDATE usuario SET nombre=?, apellido=?, correo=?, colegiado=?, usuario=?, clave=?, estado=?, id_rol=?, fecha_modifica=NOW() WHERE id_usuario=?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param('ssssssiii', $nombre, $apellido, $correo, $colegiado, $usuario, $clave, $estado, $id_rol, $id);

    if ($stmt->execute()) {
        echo "Usuario actualizado correctamente.";
    } else {
        echo "Error al actualizar el usuario: " . $stmt->error;
    }

    $stmt->close();
    exit;
}

// Obtener los datos del usuario actual
$sql = "SELECT * FROM usuario WHERE id_usuario = ?";
$stmt = $db->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "No se encontró el usuario con el ID proporcionado.";
    exit;
}

$usuario = $result->fetch_assoc();
$stmt->close();

// Comprobar si es una petición AJAX
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if (!$isAjax) {
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuario</title>
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
    <form action="/TINES/vistas/modulos/usuarios/editar_usuario.php?id=<?php echo $id; ?>" method="POST"> <!-- Asegurarse de que la ruta es correcta -->
        <h1>Editar Usuario</h1>

        <legend>
            <p>Hacer click sobre el dato para modificarlo</p>
        </legend>
        <div class="form-group">
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required class="form-control">
        </div>
        <div class="form-group">
            <label for="apellido">Apellido:</label>
            <input type="text" id="apellido" name="apellido" value="<?php echo htmlspecialchars($usuario['apellido']); ?>" required class="form-control">
        </div>
        <div class="form-group">
            <label for="correo">Correo:</label>
            <input type="email" id="correo" name="correo" value="<?php echo htmlspecialchars($usuario['correo']); ?>" required class="form-control">
        </div>
        <div class="form-group">
            <label for="colegiado">Telefono:</label>
            <input type="text" id="colegiado" name="colegiado" value="<?php echo htmlspecialchars($usuario['colegiado']); ?>" required class="form-control">
        </div>
        <div class="form-group">
            <label for="usuario">Usuario:</label>
            <input type="text" id="usuario" name="usuario" value="<?php echo htmlspecialchars($usuario['usuario']); ?>" required class="form-control">
        </div>
        <div class="form-group">
            <label for="clave">Clave:</label>
            <input type="text" id="clave" name="clave" value="<?php echo htmlspecialchars($usuario['clave']); ?>" required class="form-control">
        </div>
        <div class="form-group">
            <label for="estado">Estado:</label>
            <select id="estado" name="estado" required class="form-control">
                <option value="1" <?php echo ($usuario['estado'] == 1) ? 'selected' : ''; ?>>Activo</option>
                <option value="0" <?php echo ($usuario['estado'] == 0) ? 'selected' : ''; ?>>Inactivo</option>
            </select>
        </div>
        <div class="form-group">
            <label for="id_rol">Rol:</label>

            <select id="id_rol" name="id_rol" required class="form-control">
            <?php
            $sqlSelect = "SELECT id_rol, nombre FROM rol";
            $resultado = $db->query($sqlSelect);
            if ($resultado->num_rows > 0) {
                while ($row = $resultado->fetch_assoc()) {
                    $selected = $row['id_rol'] == $usuario['id_rol'] ? 'selected' : '';
                    echo '<option value="' . htmlspecialchars($row['id_rol']) . '" ' . $selected . '>';
                    echo htmlspecialchars($row['nombre']);
                    echo "</option>";
                }
            }
            ?>
            </select>

        </div>
        <input type="hidden" id="id_usuario" name="id_usuario" value="<?php echo htmlspecialchars($usuario['id_usuario']); ?>">
    </form>
<?php
if (!$isAjax) {
?>
</body>
</html>
<?php
}
?>
