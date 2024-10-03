<?php
// Importar la conexión a la base de datos
require __DIR__ . '/../../../includes/config/DbConection.php';

$db = $conectarDB;

// Procesar el formulario de creación si se ha enviado
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

    $sql = "INSERT INTO cliente (nombre, apellido, correo, telefono, direccion, departamento, municipio, nit, negocio, genero, tipo_cliente, estado_cliente, fecha_creacion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $db->prepare($sql);
    $stmt->bind_param('ssssssssssss', $nombre, $apellido, $correo, $telefono, $direccion, $departamento, $municipio, $nit, $negocio, $genero, $tipo_cliente, $estado_cliente);

    if ($stmt->execute()) {
        echo "<tr data-id='" . $stmt->insert_id . "'>";
        echo "<td></td>";
        echo "<td class='nombre'>" . htmlspecialchars($nombre) . "</td>";
        echo "<td class='apellido'>" . htmlspecialchars($apellido) . "</td>";
        echo "<td class='correo'>" . htmlspecialchars($correo) . "</td>";
        echo "<td class='telefono'>" . htmlspecialchars($telefono) . "</td>";
        echo "<td class='direccion'>" . htmlspecialchars($direccion) . "</td>";
        echo "<td class='departamento'>" . htmlspecialchars($departamento) . "</td>";
        echo "<td class='municipio'>" . htmlspecialchars($municipio) . "</td>";
        echo "<td class='nit'>" . htmlspecialchars($nit) . "</td>";
        echo "<td class='negocio'>" . htmlspecialchars($negocio) . "</td>";
        echo "<td class='genero'>" . htmlspecialchars($genero) . "</td>";
        echo "<td class='tipo_cliente'>" . htmlspecialchars($tipo_cliente) . "</td>";
        echo "<td class='estado_cliente'>" . (($estado_cliente == 'activo') ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>') . "</td>";
        echo "<td>";
        echo "<button class='btn btn-warning btn-sm edit-client-btn' data-id='" . $stmt->insert_id . "'>Editar</button> ";
        echo "<button class='btn btn-danger btn-sm delete-client-btn' data-id='" . $stmt->insert_id . "'>Eliminar</button>";
        echo "</td>";
        echo "</tr>";
    } else {
        echo "Error al crear el cliente: " . $stmt->error;
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
    <title>Crear Cliente</title>
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
    <form id="formularioCrearCliente" action="/TINES/vistas/modulos/clientes/crear_cliente.php" method="POST"> <!-- Asegurarse de que la ruta es correcta -->
        <h1>Crear Cliente</h1>
        <legend>
            <p>Completar los datos del nuevo cliente</p>
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
            <label for="telefono">Teléfono:</label>
            <input type="text" id="telefono" name="telefono" required class="form-control">
        </div>
        <div class="form-group">
            <label for="direccion">Dirección:</label>
            <input type="text" id="direccion" name="direccion" required class="form-control">
        </div>
        <div class="form-group">
            <label for="departamento">Departamento:</label>
            <input type="text" id="departamento" name="departamento" required class="form-control">
        </div>
        <div class="form-group">
            <label for="municipio">Municipio:</label>
            <input type="text" id="municipio" name="municipio" required class="form-control">
        </div>
        <div class="form-group">
            <label for="nit">NIT:</label>
            <input type="text" id="nit" name="nit" required class="form-control">
        </div>
        <div class="form-group">
            <label for="negocio">Negocio:</label>
            <input type="text" id="negocio" name="negocio" required class="form-control">
        </div>
        <div class="form-group">
            <label for="genero">Género:</label>
            <select id="genero" name="genero" required class="form-control">
                <option value="masculino">Masculino</option>
                <option value="femenino">Femenino</option>
                <option value="otro">Otro</option>
            </select>
        </div>
        <div class="form-group">
            <label for="tipo_cliente">Tipo de Cliente:</label>
            <select id="tipo_cliente" name="tipo_cliente" required class="form-control">
                <option value="regular">Regular</option>
                <option value="premium">Premium</option>
                <option value="vip">VIP</option>
            </select>
        </div>
        <div class="form-group">
            <label for="estado_cliente">Estado:</label>
            <select id="estado_cliente" name="estado_cliente" required class="form-control">
                <option value="activo">Activo</option>
                <option value="inactivo">Inactivo</option>
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
