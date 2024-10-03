<?php
// Importar la conexión a la base de datos
require __DIR__ . '/../../../includes/config/DbConection.php';

// Verificar si el ID del cliente se ha pasado como parámetro en POST
if (isset($_POST['id_cliente'])) {
    $id = htmlspecialchars($_POST['id_cliente']);
} elseif (isset($_GET['id'])) {
    // Verificar si el ID del cliente se ha pasado como parámetro en GET
    $id = htmlspecialchars($_GET['id']);
} else {
    echo "No se ha proporcionado un ID de cliente.";
    exit;
}

$db = $conectarDB;

// Procesar el formulario de actualización si se ha enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = htmlspecialchars($_POST['nombre']);
    $apellido = htmlspecialchars($_POST['apellido']);
    $correo = htmlspecialchars($_POST['correo']);
    $telefono = htmlspecialchars($_POST['telefono']);
    $direccion = htmlspecialchars($_POST['direccion']);
    $departamento = htmlspecialchars($_POST['departamento']);
    $municipio = htmlspecialchars($_POST['municipio']);
    $nit = htmlspecialchars($_POST['nit']);
    $negocio = htmlspecialchars($_POST['negocio']);
    $genero = htmlspecialchars($_POST['genero']);
    $tipo_cliente = htmlspecialchars($_POST['tipo_cliente']);
    $estado_cliente = htmlspecialchars($_POST['estado_cliente']);

    $sql = "UPDATE cliente SET nombre=?, apellido=?, correo=?, telefono=?, direccion=?, departamento=?, municipio=?, nit=?, negocio=?, genero=?, tipo_cliente=?, estado_cliente=?, fecha_modificacion=NOW() WHERE id_cliente=?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param('ssssssssssssi', $nombre, $apellido, $correo, $telefono, $direccion, $departamento, $municipio, $nit, $negocio, $genero, $tipo_cliente, $estado_cliente, $id);

    if ($stmt->execute()) {
        echo "Cliente actualizado correctamente.";
    } else {
        echo "Error al actualizar el cliente: " . $stmt->error;
    }

    $stmt->close();
    exit;
}

// Obtener los datos del cliente actual
$sql = "SELECT * FROM cliente WHERE id_cliente = ?";
$stmt = $db->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "No se encontró el cliente con el ID proporcionado.";
    exit;
}

$cliente = $result->fetch_assoc();
$stmt->close();

// Comprobar si es una petición AJAX
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if (!$isAjax) {
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Cliente</title>
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
    <form action="/TINES/vistas/modulos/clientes/editar_cliente.php?id=<?php echo $id; ?>" method="POST"> <!-- Asegurarse de que la ruta es correcta -->
        <h1>Editar Cliente</h1>

        <legend>
            <p>Hacer click sobre el dato para modificarlo</p>
        </legend>
        <div class="form-group">
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($cliente['nombre']); ?>" required class="form-control">
        </div>
        <div class="form-group">
            <label for="apellido">Apellido:</label>
            <input type="text" id="apellido" name="apellido" value="<?php echo htmlspecialchars($cliente['apellido']); ?>" required class="form-control">
        </div>
        <div class="form-group">
            <label for="correo">Correo:</label>
            <input type="email" id="correo" name="correo" value="<?php echo htmlspecialchars($cliente['correo']); ?>" required class="form-control">
        </div>
        <div class="form-group">
            <label for="telefono">Teléfono:</label>
            <input type="text" id="telefono" name="telefono" value="<?php echo htmlspecialchars($cliente['telefono']); ?>" required class="form-control">
        </div>
        <div class="form-group">
            <label for="direccion">Dirección:</label>
            <input type="text" id="direccion" name="direccion" value="<?php echo htmlspecialchars($cliente['direccion']); ?>" required class="form-control">
        </div>
        <div class="form-group">
            <label for="departamento">Departamento:</label>
            <input type="text" id="departamento" name="departamento" value="<?php echo htmlspecialchars($cliente['departamento']); ?>" required class="form-control">
        </div>
        <div class="form-group">
            <label for="municipio">Municipio:</label>
            <input type="text" id="municipio" name="municipio" value="<?php echo htmlspecialchars($cliente['municipio']); ?>" required class="form-control">
        </div>
        <div class="form-group">
            <label for="nit">NIT:</label>
            <input type="text" id="nit" name="nit" value="<?php echo htmlspecialchars($cliente['nit']); ?>" required class="form-control">
        </div>
        <div class="form-group">
            <label for="negocio">Negocio:</label>
            <input type="text" id="negocio" name="negocio" value="<?php echo htmlspecialchars($cliente['negocio']); ?>" required class="form-control">
        </div>
        <div class="form-group">
            <label for="genero">Género:</label>
            <select id="genero" name="genero" required class="form-control">
                <option value="masculino" <?php echo ($cliente['genero'] == 'masculino') ? 'selected' : ''; ?>>Masculino</option>
                <option value="femenino" <?php echo ($cliente['genero'] == 'femenino') ? 'selected' : ''; ?>>Femenino</option>
                <option value="otro" <?php echo ($cliente['genero'] == 'otro') ? 'selected' : ''; ?>>Otro</option>
            </select>
        </div>
        <div class="form-group">
            <label for="tipo_cliente">Tipo de Cliente:</label>
            <select id="tipo_cliente" name="tipo_cliente" required class="form-control">
                <option value="regular" <?php echo ($cliente['tipo_cliente'] == 'regular') ? 'selected' : ''; ?>>Regular</option>
                <option value="premium" <?php echo ($cliente['tipo_cliente'] == 'premium') ? 'selected' : ''; ?>>Premium</option>
                <option value="vip" <?php echo ($cliente['tipo_cliente'] == 'vip') ? 'selected' : ''; ?>>VIP</option>
            </select>
        </div>
        <div class="form-group">
            <label for="estado_cliente">Estado:</label>
            <select id="estado_cliente" name="estado_cliente" required class="form-control">
                <option value="activo" <?php echo ($cliente['estado_cliente'] == 'activo') ? 'selected' : ''; ?>>Activo</option>
                <option value="inactivo" <?php echo ($cliente['estado_cliente'] == 'inactivo') ? 'selected' : ''; ?>>Inactivo</option>
            </select>
        </div>
        <input type="hidden" id="id_cliente" name="id_cliente" value="<?php echo htmlspecialchars($cliente['id_cliente']); ?>">
    </form>
<?php
if (!$isAjax) {
?>
</body>
</html>
<?php
}
?>
