<?php
// Importar la conexión a la base de datos
require __DIR__ . '/../../../includes/config/DbConection.php';

// Verificar si el ID del rol se ha pasado como parámetro en POST
if (isset($_POST['id_rol'])) {
    $id = htmlspecialchars($_POST['id_rol']);
} elseif (isset($_GET['id'])) {
    // Verificar si el ID del rol se ha pasado como parámetro en GET
    $id = htmlspecialchars($_GET['id']);
} else {
    echo "No se ha proporcionado un ID de rol.";
    exit;
}

$db = $conectarDB;

// Procesar el formulario de actualización si se ha enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = htmlspecialchars($_POST['nombre']);
    $estado = htmlspecialchars($_POST['estado']);

    $sql = "UPDATE rol SET nombre=?, estado=?, fecha_crea=NOW() WHERE id_rol=?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param('sii', $nombre, $estado, $id);

    if ($stmt->execute()) {
        echo "Rol actualizado correctamente.";
    } else {
        echo "Error al actualizar el rol: " . $stmt->error;
    }

    $stmt->close();
    exit;
}

// Obtener los datos del rol actual
$sql = "SELECT * FROM rol WHERE id_rol = ?";
$stmt = $db->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "No se encontró el rol con el ID proporcionado.";
    exit;
}

$rol = $result->fetch_assoc();
$stmt->close();

// Comprobar si es una petición AJAX
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if (!$isAjax) {
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Rol</title>
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
    <form action="/TINES/vistas/modulos/roles/editar_rol.php?id=<?php echo $id; ?>" method="POST"> <!-- Asegurarse de que la ruta es correcta -->
        <h1>Editar Rol</h1>

        <legend>
            <p>Hacer click sobre el dato para modificarlo</p>
        </legend>
        <div class="form-group">
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($rol['nombre']); ?>" required class="form-control">
        </div>
        <div class="form-group">
            <label for="estado">Estado:</label>
            <select id="estado" name="estado" required class="form-control">
                <option value="1" <?php echo ($rol['estado'] == 1) ? 'selected' : ''; ?>>Activo</option>
                <option value="0" <?php echo ($rol['estado'] == 0) ? 'selected' : ''; ?>>Inactivo</option>
            </select>
        </div>
        <input type="hidden" id="id_rol" name="id_rol" value="<?php echo htmlspecialchars($rol['id_rol']); ?>">
    </form>
<?php
if (!$isAjax) {
?>
</body>
</html>
<?php
}
?>
