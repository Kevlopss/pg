<?php
require __DIR__ . '/../../includes/config/DbConection.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$db = $conectarDB;

// Configuración de la paginación
$registrosPorPagina = 25;
$paginaActual = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($paginaActual - 1) * $registrosPorPagina;

$filters = [];
$params = [];
$sql = "
    SELECT 
        i.id, 
        p.Cod_producto, 
        p.Nombre AS nombre, 
        c.Nombre AS categoria, 
        b.nombre AS bodega, 
        i.stock
    FROM inventario i
    JOIN producto p ON i.Id_producto = p.Id_producto
    JOIN categoria_producto c ON p.Id_categoria_producto = c.Id_categoria_producto
    JOIN bodegas b ON i.Id_bodega = b.Id_bodega";

// Agregar filtros basados en la solicitud POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name_product = isset($_POST['nameProducto']) ? trim($_POST['nameProducto']) : '';
    $categoria_product = isset($_POST['cat']) ? trim($_POST['cat']) : '';
    $bodega_product = isset($_POST['bodega']) ? trim($_POST['bodega']) : '';

    if (!empty($name_product)) {
        $filters[] = "(UPPER(p.Nombre) LIKE ? OR p.Cod_producto LIKE ?)";
        $params[] = "%" . strtoupper($name_product) . "%";
        $params[] = "%" . $name_product . "%";
    }
    if (!empty($categoria_product)) {
        $filters[] = "UPPER(c.Nombre) LIKE ?";
        $params[] = "%" . strtoupper($categoria_product) . "%";
    }
    if (!empty($bodega_product)) {
        $filters[] = "UPPER(b.nombre) LIKE ?";
        $params[] = "%" . strtoupper($bodega_product) . "%";
    }

    if (count($filters) > 0) {
        $sql .= " WHERE " . implode(" AND ", $filters);
    }
}

// Obtener el número total de registros
$totalRegistrosQuery = $db->prepare("SELECT COUNT(*) AS total 
    FROM inventario i
    JOIN producto p ON i.Id_producto = p.Id_producto
    JOIN categoria_producto c ON p.Id_categoria_producto = c.Id_categoria_producto
    JOIN bodegas b ON i.Id_bodega = b.Id_bodega" 
    . (count($filters) > 0 ? " WHERE " . implode(" AND ", $filters) : ""));
if (count($params) > 0) {
    $totalRegistrosQuery->bind_param(str_repeat('s', count($params)), ...$params);
}
$totalRegistrosQuery->execute();
$totalRegistros = $totalRegistrosQuery->get_result()->fetch_assoc()['total'];
$totalPaginas = ceil($totalRegistros / $registrosPorPagina);

$sql .= " LIMIT $offset, $registrosPorPagina";

$stmt = $db->prepare($sql);
if (count($params) > 0) {
    $stmt->bind_param(str_repeat('s', count($params)), ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Función para recorrer los resultados y generar las filas de la tabla
function ShowTables($result, $offset) {
    $contador = $offset + 1;
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td class='id-col'>" . $contador . ".</td>";
        echo "<td>" . htmlspecialchars($row['Cod_producto']) . "</td>";
        echo "<td>" . htmlspecialchars($row['nombre']) . "</td>";
        echo "<td>" . htmlspecialchars($row['categoria']) . "</td>";
        echo "<td>" . htmlspecialchars($row['bodega']) . "</td>";
        echo "<td>" . htmlspecialchars($row['stock']) . "</td>";
        echo "</tr>";
        $contador++;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <style>
        #h4 {
            color: gray;
        }
        #ver {
            background-color: #1C379C;
            border-color: #1C379C;
        }
        #reporte {
            margin-left: 7px;
            background-color: #1C379C;
            border-color: #1C379C;
            font-size: 16px;
        }
        #reporte:hover {
            background-color: #267FBB;
            border-color: #267FBB;
        }
        #colorInventario {
            background-color: #1C379C;
            color: white;
        }
        thead {
            background-color: #1C379C;
            color: white;
        }
        tr td:first-child {
            background-color: #1C379C;
            color: white;
        }
        input {
            padding: 6px;
            display: inline-block;
            margin-right: 1px;
            color: black;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }
        input[type="text"]::placeholder {
            font-size: 16px;
            color: #555;
        }
        #filters {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        #filters select,
        #filters input,
        #filters button {
            flex: 1;
        }
        #filters button {
            flex: 0 0 auto;
        }
    </style>
</head>
<body>

<!-- Contenido -->
<div class="content-wrapper">
    <section class="content-header" id="colorInventario">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Inventario</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="card">
            <form method='POST' action=''>
                <div class="card-header" id="cardHeader">
                    <div id="filters">
                        <input type="text" id="user-select" class="form-control" name="nameProducto" placeholder="Nombre del producto" value="">
                        <select id="user-select" class="form-control" name="cat">
                            <option value="" selected disabled>Categoría</option>
                            <?php
                            $sqlSelect = "SELECT * FROM categoria_producto";
                            $resultado = $db->query($sqlSelect);
                            if ($resultado->num_rows > 0) {
                                while ($row = $resultado->fetch_assoc()) {
                                    echo '<option value="' . htmlspecialchars($row['Nombre']) . '">';
                                    echo htmlspecialchars($row['Nombre']);
                                    echo "</option>";
                                }
                            }
                            ?>
                        </select>
                        <select id="user-select" class="form-control" name="bodega">
                            <option value="" selected disabled>Bodega</option>
                            <?php
                            $sqlSelect = "SELECT * FROM bodegas";
                            $resultado = $db->query($sqlSelect);
                            if ($resultado->num_rows > 0) {
                                while ($row = $resultado->fetch_assoc()) {
                                    echo '<option value="' . htmlspecialchars($row['nombre']) . '">';
                                    echo htmlspecialchars($row['nombre']);
                                    echo "</option>";
                                }
                            }
                            ?>
                        </select>
                        <button type="submit" class="btn btn-success" id="createUserBtn">Buscar</button>
                        <button type="button" class="btn btn-success float-right" id="reporte">Reporte</button>
                    </div>
                </div>
            </form>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Código</th>
                            <th>Nombre</th>
                            <th>Categoría</th>
                            <th>Bodega</th>
                            <th>Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Verificar si hay resultados
                        if ($result && $result->num_rows > 0) {
                            ShowTables($result, $offset);
                        } else {
                            echo "<tr><td colspan='6'>No se encontraron registros.</td></tr>";
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

<script>
    $(document).ready(function() {
        $('#reporte').on('click', function() {
            // Implementar lógica para generar el reporte
            alert('Generar reporte');
        });
    });
</script>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</body>
</html>
