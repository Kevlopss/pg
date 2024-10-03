<?php
// Importar la conexión a la base de datos
require __DIR__ . '/../../../includes/config/DbConection.php';

$db = $conectarDB;

// Procesar el formulario de creación si se ha enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = htmlspecialchars($_POST['nombre']);
    $estado = htmlspecialchars($_POST['estado']);

    $sql = "INSERT INTO rol (nombre, estado, fecha_crea) VALUES (?, ?, NOW())";
    $stmt = $db->prepare($sql);
    $stmt->bind_param('si', $nombre, $estado);

    if ($stmt->execute()) {
        $id_rol = $stmt->insert_id;
        $estado_label = ($estado == 1) ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>';
        echo "<tr data-id='" . htmlspecialchars($id_rol) . "'>";
        echo "<td>" . htmlspecialchars($id_rol) . ".</td>";
        echo "<td class='nombre'>" . htmlspecialchars($nombre) . "</td>";
        echo "<td class='estado'>" . $estado_label . "</td>";
        echo "<td class='fecha_crea'>" . date('Y-m-d H:i:s') . "</td>";
        echo "<td>";
        echo "<button class='btn btn-warning btn-sm edit-role-btn' data-id='" . htmlspecialchars($id_rol) . "'>Editar</button> ";
        echo "<button class='btn btn-danger btn-sm delete-role-btn' data-id='" . htmlspecialchars($id_rol) . "'>Eliminar</button>";
        echo "</td>";
        echo "</tr>";
    } else {
        echo "Error al crear el rol: " . $stmt->error;
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
    <title>Crear Rol</title>
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
    <form action="/TINES/vistas/modulos/roles/crear_rol.php" method="POST"> <!-- Asegurarse de que la ruta es correcta -->
        <h1>Crear Rol</h1>
        <legend>
            <p>Completar los datos del nuevo rol</p>
        </legend>
        <div class="form-group">
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" required class="form-control">
        </div>
        <div class="form-group">
            <label for="estado">Estado:</label>
            <select id="estado" name="estado" required class="form-control">
                <option value="1">Activo</option>
                <option value="0">Inactivo</option>
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