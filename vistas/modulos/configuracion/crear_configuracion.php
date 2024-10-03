<?php
require __DIR__ . '/../../../includes/config/DbConection.php';

$db = $conectarDB;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_abogado = htmlspecialchars($_POST['id_abogado']);
    $id_asistente = htmlspecialchars($_POST['id_asistente']);

    $sql = "INSERT INTO abogado_asistente (id_abogado, id_asistente) VALUES (?, ?)";
    $stmt = $db->prepare($sql);
    $stmt->bind_param('ii', $id_abogado, $id_asistente);

    if ($stmt->execute()) {
        echo "<div class='alerta correcto'>";
        echo "<p>Configuración creada correctamente.</p>";
        echo "</div>";
    } else {
        echo "<div class='alerta incorrecto'>";
        echo "Error al crear la configuración: " . $stmt->error;
        echo "</div>";
    }

    $stmt->close();
    exit;
}

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if (!$isAjax) {
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Configuración</title>
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
    <form action="/DES/vistas/modulos/configuracion/crear_configuracion.php" method="POST">
        <h1>Crear Configuración</h1>

        <legend>
            <p>Completar los datos de la nueva configuración</p>
        </legend>
        <div class="form-group">
            <label for="id_abogado">ID Abogado:</label>
            <input type="text" id="id_abogado" name="id_abogado" required class="form-control">
        </div>
        <div class="form-group">
            <label for="id_asistente">ID Asistente:</label>
            <input type="text" id="id_asistente" name="id_asistente" required class="form-control">
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