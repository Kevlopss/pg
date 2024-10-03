<?php
// Importar la conexión a la base de datos
require __DIR__ . '/../../includes/config/DbConection.php';
$db = $conectarDB;

// Verificar el estado actual de la caja
$estadoCajaQuery = $db->query("SELECT * FROM caja WHERE estado = 'abierta' ORDER BY fecha_apertura DESC LIMIT 1");
$estadoCaja = $estadoCajaQuery->fetch_assoc();

if ($estadoCaja) {
    $id_caja_abierta = $estadoCaja['id_caja'];
    $saldoApertura = $estadoCaja['monto_inicial'];
    $ventasTotales = $db->query("SELECT SUM(monto) AS total FROM transaccion WHERE id_caja = $id_caja_abierta AND tipo = 'venta'")->fetch_assoc()['total'] ?? 0.00;
    $retirosTotales = $db->query("SELECT SUM(monto) AS total FROM transaccion WHERE id_caja = $id_caja_abierta AND tipo = 'retiro'")->fetch_assoc()['total'] ?? 0.00;
    $depositosTotales = $db->query("SELECT SUM(monto) AS total FROM transaccion WHERE id_caja = $id_caja_abierta AND tipo = 'deposito'")->fetch_assoc()['total'] ?? 0.00;
    $saldoCierre = $saldoApertura + $ventasTotales - $retirosTotales + $depositosTotales;
    
    // Configuración de la paginación
    $registrosPorPagina = 25;
    $paginaActual = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($paginaActual - 1) * $registrosPorPagina;

    // Obtener el número total de transacciones para la caja abierta
    $totalRegistrosQuery = $db->query("SELECT COUNT(*) AS total FROM transaccion WHERE id_caja = $id_caja_abierta");
    $totalRegistros = $totalRegistrosQuery->fetch_assoc()['total'];
    $totalPaginas = ceil($totalRegistros / $registrosPorPagina);

    // Obtener los registros para la página actual
    $sql = "SELECT t.*, u.nombre AS usuario_nombre FROM transaccion t LEFT JOIN usuario u ON t.id_usuario = u.id_usuario WHERE t.id_caja = $id_caja_abierta LIMIT $offset, $registrosPorPagina";
    $result = $db->query($sql);
} else {
    $saldoApertura = 0.00;
    $ventasTotales = 0.00;
    $retirosTotales = 0.00;
    $depositosTotales = 0.00;
    $saldoCierre = 0.00;
    $totalPaginas = 1;
    $result = false;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Modulo de Caja</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</head>
<body>

<!-- Contenido -->
<div class="content-wrapper">
    <section class="content-header bg-success text-white">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Caja</h1>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <section class="content">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Estado de la Caja</h3>
                <button type="button" class="btn btn-primary float-right" id="nuevaTransaccionBtn">+ NUEVA TRANSACCIÓN</button>
                <?php if (!$estadoCaja): ?>
                <button type="button" class="btn btn-success float-right" id="abrirCajaBtn">+ ABRIR CAJA</button>
                <?php endif; ?>
                <button type="button" class="btn btn-danger float-right" id="cerrarCajaBtn">CERRAR CAJA</button>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-header">Saldo de Apertura</div>
                            <div class="card-body">
                                <h5 id="saldoApertura">$<?php echo number_format($saldoApertura, 2); ?></h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-header">Ventas Totales</div>
                            <div class="card-body">
                                <h5 id="ventasTotales">$<?php echo number_format($ventasTotales, 2); ?></h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-header">Retiros Totales</div>
                            <div class="card-body">
                                <h5 id="retirosTotales">$<?php echo number_format($retirosTotales, 2); ?></h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-header">Depósitos Totales</div>
                            <div class="card-body">
                                <h5 id="depositosTotales">$<?php echo number_format($depositosTotales, 2); ?></h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-header">Saldo de Cierre</div>
                            <div class="card-body">
                                <h5 id="saldoCierre">$<?php echo number_format($saldoCierre, 2); ?></h5>
                            </div>
                        </div>
                    </div>
                </div>

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Monto</th>
                            <th>Descripción</th>
                            <th>Usuario</th>
                        </tr>
                    </thead>
                    <tbody id="transaccionesTableBody">
                        <?php
                        if ($result && $result->num_rows > 0) {
                            $contador = $offset + 1;
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . $contador . ".</td>";
                                echo "<td>" . htmlspecialchars($row['fecha']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['tipo']) . "</td>";
                                echo "<td>$" . htmlspecialchars(number_format($row['monto'], 2)) . "</td>";
                                echo "<td>" . htmlspecialchars($row['descripcion']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['usuario_nombre']) . "</td>";
                                echo "</tr>";
                                $contador++;
                            }
                        } else {
                            echo "<tr><td colspan='6'>No se encontraron transacciones para el dia de hoy.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>

                <!-- Paginación -->
                <nav>
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                            <li class="page-item <?php if ($i == $paginaActual) echo 'active'; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </section>
</div>

<!-- Contenedor para mensajes flotantes -->
<div id="alert-container" class="position-fixed top-0 end-0 p-3" style="top: 10px; right: 20px; z-index: 1050;"></div>

<!-- Modal para registrar transacción -->
<div class="modal fade" id="registrarTransaccionModal" tabindex="-1" role="dialog" aria-labelledby="registrarTransaccionModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="registrarTransaccionModalLabel">Registrar Transacción</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- El formulario se cargará aquí -->
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Abrir caja
    $('#abrirCajaBtn').on('click', function() {
        var montoInicial = prompt('Ingrese el monto inicial para abrir la caja:');
        if (montoInicial !== null) {
            $.ajax({
                url: '/TINES/vistas/modulos/caja/abrir_caja.php',
                type: 'POST',
                data: {
                    monto_inicial: montoInicial,
                    id_usuario: 1 // Asegúrate de pasar el ID del usuario actual
                },
                success: function(response) {
                    showAlert(response.message, response.status === 'success' ? 'success' : 'danger');
                    if (response.status === 'success') {
                        location.reload();
                    }
                },
                error: function() {
                    showAlert('Error al abrir la caja.', 'danger');
                }
            });
        }
    });

    // Cerrar caja
    $('#cerrarCajaBtn').on('click', function() {
        $.ajax({
            url: '/TINES/vistas/modulos/caja/cerrar_caja.php',
            type: 'POST',
            success: function(response) {
                showAlert(response.message, response.status === 'success' ? 'success' : 'danger');
                if (response.status === 'success') {
                    location.reload();
                }
            },
            error: function() {
                showAlert('Error al cerrar la caja.', 'danger');
            }
        });
    });

    // Nueva transacción
    $('#nuevaTransaccionBtn').on('click', function() {
        $.ajax({
            url: '/TINES/vistas/modulos/caja/validar_caja_abierta.php',
            type: 'GET',
            success: function(response) {
                if (typeof response === 'string') {
                    response = JSON.parse(response);
                }
                if (response.status === 'success') {
                    $.ajax({
                        url: '/TINES/vistas/modulos/caja/registrar_transaccion.php',
                        type: 'GET',
                        success: function(formulario) {
                            $('#registrarTransaccionModal .modal-body').html(formulario);
                            $('#registrarTransaccionModal').modal('show');

                            $('#formularioRegistrarTransaccion').on('submit', function(e) {
                                e.preventDefault();

                                $.ajax({
                                    url: $(this).attr('action'),
                                    type: $(this).attr('method'),
                                    data: $(this).serialize(),
                                    success: function(response) {
                                        if (typeof response === 'string') {
                                            response = JSON.parse(response);
                                        }
                                        showAlert(response.message, response.status === 'success' ? 'success' : 'danger');
                                        if (response.status === 'success') {
                                            $('#registrarTransaccionModal').modal('hide');
                                            location.reload();
                                        }
                                    },
                                    error: function() {
                                        showAlert('Error al registrar la transacción.', 'danger');
                                    }
                                });
                            });
                        },
                        error: function() {
                            showAlert('Error al cargar el formulario de transacción.', 'danger');
                        }
                    });
                } else {
                    showAlert(response.message, 'danger');
                }
            },
            error: function() {
                showAlert('Error al validar el estado de la caja.', 'danger');
            }
        });
    });

    function showAlert(message, type) {
        var alertHtml = '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
                        message +
                        '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                        '<span aria-hidden="true">&times;</span>' +
                        '</button>' +
                        '</div>';
        $('#alert-container').html(alertHtml);
        setTimeout(function() {
            $('#alert-container .alert').alert('close');
        }, 3000);
    }
});
</script>

</body>
</html>
