<?php
require __DIR__ . '/../../../includes/config/DbConection.php';
$db = $conectarDB;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    header('Content-Type: application/json');
    try {
        $nombre = trim($_POST['nombre_producto']);
        $codigo = trim($_POST['Cod_producto']);
        $descripcion = trim($_POST['descripcion']);
        $imagen = $_FILES['imagen'];
        $presentacion = trim($_POST['presentacion_id']);
        $tipo = trim($_POST['tipo_id']);
        $categoria = trim($_POST['categoria_id']);
        $estado = trim($_POST['estado']);
        $fecha_creacion = trim($_POST['fecha_creacion']);

        // Crear carpeta de imágenes si no existe
        $carpetaImagenes = __DIR__ . '/imagenes';
        if (!is_dir($carpetaImagenes)) {
            if (!mkdir($carpetaImagenes, 0777, true)) {
                throw new Exception('No se pudo crear la carpeta de imágenes.');
            }
        } else {
            chmod($carpetaImagenes, 0777);
        }

        // Verificar si se subió la imagen correctamente
        if ($imagen['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Error al subir la imagen: ' . $imagen['error']);
        }

        // Guardar imagen
        $nombreImagen = uniqid() . '.' . pathinfo($imagen['name'], PATHINFO_EXTENSION);
        $rutaImagen = $carpetaImagenes . '/' . $nombreImagen;
        if (!move_uploaded_file($imagen['tmp_name'], $rutaImagen)) {
            throw new Exception('Error al mover la imagen a la carpeta de destino.');
        }

        $sql = "INSERT INTO producto (Nombre, Cod_producto, Descripcion, Imagen, Id_presentacion_producto, Id_tipo_producto, Id_categoria_producto, Estado, Fecha_creacion)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->bind_param('sssssssss', $nombre, $codigo, $descripcion, $nombreImagen, $presentacion, $tipo, $categoria, $estado, $fecha_creacion);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Producto creado correctamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al crear el producto: ' . $stmt->error]);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if (!$isAjax) {
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear producto</title>
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
<style>
#createP{
    margin-left: 10px;
    background-color: #1C379C;
    border-color: #1C379C;
    font-size: 17px;
}
#createP:hover{
    background-color: #267FBB;
    border-color: #267FBB;
}
</style>    
    <form id="formCreateProduct" action="/TINES/vistas/modulos/producto/crear_producto.php" method="POST" enctype="multipart/form-data">
        <legend>
            <p>Completar los datos del nuevo producto</p>
        </legend>
        <div class="form-group">
            <label for="nombre_producto">Nombre:</label>
            <input type="text" id="nombre_producto" name="nombre_producto" required class="form-control">
        </div>
        <div class="form-group">
            <label for="Cod_producto">Código:</label>
            <input type="text" id="Cod_producto" name="Cod_producto" required class="form-control">
        </div>
        <div class="form-group">
            <label for="descripcion">Descripción:</label>
            <input type="text" id="descripcion" name="descripcion" required class="form-control">
        </div>
        <div class="form-group">
            <label for="imagen">Imagen:</label>
            <input type="file" id="imagen" name="imagen" accept="image/*" required class="form-control">
        </div>
        <div class="form-group">
            <label for="presentacion_id">Presentación:</label>
            <select id="presentacion_id" name="presentacion_id" required class="form-control">
                <?php
                $sqlSelect = "SELECT Id_presentacion_producto, Nombre FROM presentacion_producto";
                $resultado = $db->query($sqlSelect);
                if ($resultado->num_rows > 0) {
                    while ($row = $resultado->fetch_assoc()) {
                        echo '<option value="' . htmlspecialchars($row['Id_presentacion_producto']) . '">';
                        echo htmlspecialchars($row['Nombre']);
                        echo "</option>";
                    }
                }
                ?>
            </select>
        </div>
        <div class="form-group">
            <label for="tipo_id">Tipo de producto:</label>
            <select id="tipo_id" name="tipo_id" required class="form-control">
                <?php
                $sqlSelect = "SELECT Id_tipo_producto, Nombre FROM tipo_producto";
                $resultado = $db->query($sqlSelect);
                if ($resultado->num_rows > 0) {
                    while ($row = $resultado->fetch_assoc()) {
                        echo '<option value="' . htmlspecialchars($row['Id_tipo_producto']) . '">';
                        echo htmlspecialchars($row['Nombre']);
                        echo "</option>";
                    }
                }
                ?>
            </select>
        </div>
        <div class="form-group">
            <label for="categoria_id">Categoría:</label>
            <select id="categoria_id" name="categoria_id" required class="form-control">
                <?php
                $sqlSelect = "SELECT Id_categoria_producto, Nombre FROM categoria_producto";
                $resultado = $db->query($sqlSelect);
                if ($resultado->num_rows > 0) {
                    while ($row = $resultado->fetch_assoc()) {
                        echo '<option value="' . htmlspecialchars($row['Id_categoria_producto']) . '">';
                        echo htmlspecialchars($row['Nombre']);
                        echo "</option>";
                    }
                }
                ?>
            </select>
        </div>
        <div class="form-group">
            <label for="estado">Estado:</label>
            <select id="estado" name="estado" required class="form-control">
                <option value="1">Activo</option>
                <option value="0">Inactivo</option>
            </select>
        </div>
        <div class="form-group">
            <label for="fecha_creacion">Fecha Creación:</label>
            <input type="datetime-local" id="fecha_creacion" name="fecha_creacion" required class="form-control">
        </div>
        <button type="submit" class="btn btn-primary float-right" id="createP">Crear Producto</button>
        <button type="button" class="btn btn-secondary float-right" data-dismiss="modal" id="cerrar">Cerrar</button>
    </form>
    <script>
        document.getElementById('formCreateProduct').addEventListener('submit', function(event) {
            event.preventDefault();
            var formData = new FormData(this);
            fetch(this.action, {
                method: this.method,
                body: formData
            }).then(response => response.json()).then(data => {
                if (data.status === 'success') {
                    $('#createProductModal').modal('hide');
                    setTimeout(() => {
                        const alertContainer = document.getElementById('alert-container');
                        alertContainer.innerHTML = '<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                                                    data.message +
                                                    '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                                                    '<span aria-hidden="true">&times;</span>' +
                                                    '</button>' +
                                                    '</div>';
                        setTimeout(() => {
                            location.reload();
                        }, 2000);
                    }, 50); // Espera medio segundo para cerrar el modal antes de mostrar la alerta
                } else {
                    alert(data.message);
                }
            }).catch(error => {
                console.error('Error:', error);
                alert('Hubo un problema al crear el producto.');
            });
        });
    </script>
<?php
if (!$isAjax) {
?>
</body>
</html>
<?php
}
?>
