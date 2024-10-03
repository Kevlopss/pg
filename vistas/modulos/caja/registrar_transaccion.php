<?php
// Importar la conexión a la base de datos
require __DIR__ . '/../../../includes/config/DbConection.php';
$db = $conectarDB;

// Verificar si la caja está abierta
$estadoCajaQuery = $db->query("SELECT * FROM caja WHERE estado = 'abierta' ORDER BY fecha_apertura DESC LIMIT 1");
$estadoCaja = $estadoCajaQuery->fetch_assoc();

if (!$estadoCaja) {
    echo json_encode(['status' => 'error', 'message' => 'No hay ninguna caja abierta.']);
    exit;
}

// Procesar el formulario de registro de transacción si se ha enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo = htmlspecialchars($_POST['tipo']);
    $monto = htmlspecialchars($_POST['monto']);
    $descripcion = htmlspecialchars($_POST['descripcion']);
    $id_usuario = 1; // Este debería ser el ID del usuario actualmente autenticado
    $id_caja = $estadoCaja['id_caja'];

    $sql = "INSERT INTO transaccion (id_caja, tipo, monto, fecha, descripcion, id_usuario) VALUES (?, ?, ?, NOW(), ?, ?)";
    $stmt = $db->prepare($sql);
    $stmt->bind_param('isdsi', $id_caja, $tipo, $monto, $descripcion, $id_usuario);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Transacción registrada correctamente.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al registrar la transacción: ' . $stmt->error]);
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
    <title>Registrar Transacción</title>
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
    <form id="formularioRegistrarTransaccion" action="/TINES/vistas/modulos/caja/registrar_transaccion.php" method="POST">
        <h1>Registrar Transacción</h1>
        <legend>
            <p>Completar los datos de la nueva transacción</p>
        </legend>
        <div class="form-group">
            <label for="tipo">Tipo de Transacción:</label>
            <select id="tipo" name="tipo" required class="form-control">
                <option value="venta">Venta</option>
                <option value="retiro">Retiro</option>
                <option value="deposito">Depósito</option>
            </select>
        </div>
        <div class="form-group">
            <label for="monto">Monto:</label>
            <input type="number" id="monto" name="monto" step="0.01" required class="form-control">
        </div>
        <div class="form-group">
            <label for="descripcion">Descripción:</label>
            <textarea id="descripcion" name="descripcion" required class="form-control"></textarea>
        </div>
        <button type="submit" class="btn btn-success">Registrar</button>
    </form>
<?php
if (!$isAjax) {
?>
</body>
</html>
<?php
}
?>
