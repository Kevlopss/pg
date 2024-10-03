<?php
require __DIR__ . '/../../../includes/config/DbConection.php';

$db = $conectarDB;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = htmlspecialchars($_POST['id']);
    $id_abogado = htmlspecialchars($_POST['id_abogado']);
    $id_asistente = htmlspecialchars($_POST['id_asistente']);

    $sql = "UPDATE abogado_asistente SET id_abogado = ?, id_asistente = ? WHERE id_Abte = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param('iii', $id_abogado, $id_asistente, $id);

    if ($stmt->execute()) {
        echo "<div class='alerta correcto'>";
        echo "<p>Configuración actualizada correctamente.</p>";
        echo "</div>";
    } else {
        echo "<div class='alerta incorrecto'>";
        echo "Error al actualizar la configuración: " . $stmt->error;
        echo "</div>";
    }

    $stmt->close();
    exit;
}

$id = htmlspecialchars($_GET['id']);
$sql = "SELECT * FROM abogado_asistente WHERE id_Abte = ?";
$stmt = $db->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$config = $result->fetch_assoc();

$stmt->close();

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if (!$isAjax) {
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Configuración</title>
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
    <form action="/DES/vistas/modulos/configuracion/editar_configuracion.php" method="POST">
        <h1>Editar Configuración</h1>

        <legend>
            <p>Modificar los datos de la configuración</p>
        </legend>
        <div class="form-group">
            <label for="id_abogado">ID Abogado:</label>
            <input type="text" id="id_abogado" name="id_abogado" required class="form-control" value="<?php echo htmlspecialchars($config['id_abogado']); ?>">
        </div>
        <div class="form-group">
            <label for="id_asistente">ID Asistente:</label>
            <input type="text" id="id_asistente" name="id_asistente" required class="form-control" value="<?php echo htmlspecialchars($config['id_asistente']); ?>">
        </div>
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($config['id_Abte']); ?>">
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-success" id="saveChangesButton">Guardar cambios</button>
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