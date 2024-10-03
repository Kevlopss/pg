<?php
// Importar la conexión a la base de datos
require __DIR__ . '/../../../includes/config/DbConection.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$db = $conectarDB;

// Verificar si el ID del producto se ha pasado como parámetro
if (isset($_GET['id'])) {
    $id = htmlspecialchars($_GET['id']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'No se ha proporcionado un ID del producto.']);
    exit;
}

// Procesar el formulario de actualización si se ha enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = htmlspecialchars($_POST['Nombre']);
    $codigo = htmlspecialchars($_POST['Cod_producto']);
    $descripcion = htmlspecialchars($_POST['Descripcion']);
    $imagen = $_FILES['Imagen'];
    $presentacion = htmlspecialchars($_POST['Pres_producto']);
    $tipo = htmlspecialchars($_POST['Nombre_tipo']);
    $categoria = htmlspecialchars($_POST['Nombre_cat']);
    $estado = htmlspecialchars($_POST['Estado']);
    
    $precio_normal = htmlspecialchars($_POST['precio_normal']);
    $precio_descuento = htmlspecialchars($_POST['precio_descuento']);
    $precio_descuento_2 = htmlspecialchars($_POST['precio_descuento_2']);
    $precio_mayorista = htmlspecialchars($_POST['precio_mayorista']);
    $precio_oferta = htmlspecialchars($_POST['precio_oferta']);

    if ($imagen['error'] === UPLOAD_ERR_OK) {
        // Crear carpeta de imágenes si no existe
        $carpetaImagenes = __DIR__ . '/imagenes';
        if (!is_dir($carpetaImagenes)) {
            if (!mkdir($carpetaImagenes, 0777, true)) {
                throw new Exception('No se pudo crear la carpeta de imágenes.');
            }
        } else {
            chmod($carpetaImagenes, 0777);
        }

        // Obtener la imagen actual desde la base de datos
        $sql = "SELECT Imagen FROM producto WHERE Id_producto = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->bind_result($imagenActual);
        $stmt->fetch();
        $stmt->close();

        // Eliminar la imagen actual si existe
        if ($imagenActual) {
            $rutaImagenActual = $carpetaImagenes . '/' . $imagenActual;
            if (file_exists($rutaImagenActual)) {
                unlink($rutaImagenActual);
            }
        }

        // Guardar la nueva imagen
        $nombreImagen = uniqid() . '.' . pathinfo($imagen['name'], PATHINFO_EXTENSION);
        $rutaImagen = $carpetaImagenes . '/' . $nombreImagen;
        if (!move_uploaded_file($imagen['tmp_name'], $rutaImagen)) {
            throw new Exception('Error al mover la imagen a la carpeta de destino.');
        }

        // Actualizar producto con la nueva imagen
        $sql = "UPDATE producto SET Cod_producto=?, Nombre=?, Descripcion=?, Imagen=?, Id_presentacion_producto=?, Id_tipo_producto=?, Id_categoria_producto=?, Estado=? WHERE Id_producto=?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param('sssssiiii', $codigo, $nombre, $descripcion, $nombreImagen, $presentacion, $tipo, $categoria, $estado, $id);
    } else {
        // Actualizar producto sin cambiar la imagen
        $sql = "UPDATE producto SET Cod_producto=?, Nombre=?, Descripcion=?, Id_presentacion_producto=?, Id_tipo_producto=?, Id_categoria_producto=?, Estado=? WHERE Id_producto=?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ssssiiii', $codigo, $nombre, $descripcion, $presentacion, $tipo, $categoria, $estado, $id);
    }

    try {
        if ($stmt->execute()) {
            // Actualizar precios
            $sql_check = "SELECT * FROM precio_producto WHERE Id_producto = ?";
            $stmt_check = $db->prepare($sql_check);
            $stmt_check->bind_param('i', $id);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();

            if ($result_check->num_rows > 0) {
                $sql_update = "UPDATE precio_producto SET 
                                Precio_normal = ?, 
                                Precio_descuento = ?, 
                                Precio_descuento_2 = ?, 
                                Precio_mayorista = ?, 
                                Precio_oferta = ? 
                               WHERE Id_producto = ?";
                $stmt_update = $db->prepare($sql_update);
                $stmt_update->bind_param('sssssi', $precio_normal, $precio_descuento, $precio_descuento_2, $precio_mayorista, $precio_oferta, $id);
                $stmt_update->execute();
            } else {
                $sql_insert = "INSERT INTO precio_producto (Id_producto, Precio_normal, Precio_descuento, Precio_descuento_2, Precio_mayorista, Precio_oferta) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt_insert = $db->prepare($sql_insert);
                $stmt_insert->bind_param('isssss', $id, $precio_normal, $precio_descuento, $precio_descuento_2, $precio_mayorista, $precio_oferta);
                $stmt_insert->execute();
            }

            echo json_encode(['status' => 'success', 'message' => 'Producto y precios editados correctamente.']);
        } else {
            throw new Exception('Error al editar el producto: ' . $stmt->error);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }

    $stmt->close();
    exit;
}

// Obtener los datos del producto actual
$sql = "SELECT * FROM producto WHERE Id_producto = ?";
$stmt = $db->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'No se encontró el producto con el ID proporcionado.']);
    exit;
}

$producto = $result->fetch_assoc();
$stmt->close();

// Obtener los datos de precios del producto actual
$sql = "SELECT * FROM precio_producto WHERE Id_producto = ?";
$stmt = $db->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $precios = $result->fetch_assoc();
} else {
    $precios = [
        'Precio_normal' => '',
        'Precio_descuento' => '',
        'Precio_descuento_2' => '',
        'Precio_mayorista' => '',
        'Precio_oferta' => ''
    ];
}
$stmt->close();

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if (!$isAjax) {
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar producto</title>
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
        .precio-section {
            
            margin-top: 2rem;
            border-top: 1px solid #ccc;
            padding-top: 1rem;
        }
    </style>
</head>
<body>
<?php
}
?>
<form id="editProductForm" action="/TINES/vistas/modulos/producto/editar_producto.php?id=<?php echo $id; ?>" method="POST" enctype="multipart/form-data">
    <style>
        #editProductForm{
            align-items: center;
            width:100%;
        }
        #form-row-1{
            width:50%;
            display: inline-block;
        }
        #form-row-2{
            margin-top: 8px;
            margin-left: 30px;
            /* margin-right: 8px; */
            width:43.5%;
            display: inline;
            position: absolute;
            font-weight: bold;
        }
        #form-row-2 input{
            margin-bottom: 16px;
        }
        #Images{
            /* width:250px;
            height:250px; */
            max-width: 98%;
            max-height: 950px;
            width: auto;
            height: auto;
            border-radius: 0.5rem;
        }
        #Images{
            text-align: center;
        }
        #imgs{
        /* width:250px;
        height:250px; */
        max-width: 100%;
        max-height: 950px;
        width: auto;
        height: auto;
        border-radius: 1rem;
    }
    </style>
    <legend>
        <p>Hacer click sobre el dato para modificarlo</p>
    </legend>
    <div id="form-row-1">
        <div class="form-group">
            <label for="Cod_producto">Código:</label>
            <input type="text" id="Cod_producto" name="Cod_producto" value="<?php echo htmlspecialchars($producto['Cod_producto']); ?>" required class="form-control">
        </div>
        <div class="form-group">
            <label for="Nombre">Nombre:</label>
            <input type="text" id="Nombre" name="Nombre" value="<?php echo htmlspecialchars($producto['Nombre']); ?>" required class="form-control">
        </div>
    
    <div class="form-group">
        <label for="Descripcion">Descripción:</label>
        <input type="text" id="Descripcion" name="Descripcion" value="<?php echo htmlspecialchars($producto['Descripcion']); ?>" required class="form-control">
    </div>
    <div class="form-group">
        <label for="Imagen">Imagen:</label>
        <p id="imgcenter">
                <?php
                    // Obtener el nombre de la imagen en la DB, para mostrar la imagen
                    $imageHash = $producto['Imagen'];
                    if ($imageHash){
                        // Construir la ruta completa de la imagen
                        $imagePath = '/TINES/vistas/modulos/producto/imagenes/' . $imageHash;
                    }else{
                        echo '<label style="font-weight: normal; color:red; text-decoration: underline;">Imagen no subida</label>';
                    }
                ?>
                <img src="<?php echo $imagePath; ?>" alt="?" id="imgs">
            </p>   
        <!-- Un input por si desea cambiar la imagen  -->
        <input type="file" id="Imagen" name="Imagen" accept="image/*" class="form-control">
    </div>
    <!-- <div class="form-row"> -->
        <div class="form-group">
            <label for="Pres_producto">Presentación:</label>
            <select id="Pres_producto" name="Pres_producto" required class="form-control">
                <?php
                $sqlSelect = "SELECT Id_presentacion_producto, Nombre FROM presentacion_producto";
                $resultado = $db->query($sqlSelect);
                if ($resultado->num_rows > 0) {
                    while ($row = $resultado->fetch_assoc()) {
                        $selected = $row['Id_presentacion_producto'] == $producto['Id_presentacion_producto'] ? 'selected' : '';
                        echo '<option value="' . htmlspecialchars($row['Id_presentacion_producto']) . '" ' . $selected . '>';
                        echo htmlspecialchars($row['Nombre']);
                        echo "</option>";
                    }
                }
                ?>
            </select>
        </div>
        <div class="form-group">
            <label for="Nombre_tipo">Tipo de producto:</label>
            <select id="Nombre_tipo" name="Nombre_tipo" required class="form-control">
                <?php
                $sqlSelect = "SELECT Id_tipo_producto, Nombre FROM tipo_producto";
                $resultado = $db->query($sqlSelect);
                if ($resultado->num_rows > 0) {
                    while ($row = $resultado->fetch_assoc()) {
                        $selected = $row['Id_tipo_producto'] == $producto['Id_tipo_producto'] ? 'selected' : '';
                        echo '<option value="' . htmlspecialchars($row['Id_tipo_producto']) . '" ' . $selected . '>';
                        echo htmlspecialchars($row['Nombre']);
                        echo "</option>";
                    }
                }
                ?>
            </select>
        </div>
        <div class="form-group">
            <label for="Nombre_cat">Categoría:</label>
            <select id="Nombre_cat" name="Nombre_cat" required class="form-control">
                <?php
                $sqlSelect = "SELECT Id_categoria_producto, Nombre FROM categoria_producto";
                $resultado = $db->query($sqlSelect);
                if ($resultado->num_rows > 0) {
                    while ($row = $resultado->fetch_assoc()) {
                        $selected = $row['Id_categoria_producto'] == $producto['Id_categoria_producto'] ? 'selected' : '';
                        echo '<option value="' . htmlspecialchars($row['Id_categoria_producto']) . '" ' . $selected . '>';
                        echo htmlspecialchars($row['Nombre']);
                        echo "</option>";
                    }
                }
                ?>
            </select>
        </div>
    <!-- </div> -->
        <div class="form-group">
            <label for="Estado">Estado:</label>
            <select id="Estado" name="Estado" required class="form-control">
                <option value="1" <?php echo $producto['Estado'] === 'Activo' ? 'selected' : ''; ?>>Activo</option>
                <option value="0" <?php echo $producto['Estado'] === 'Inactivo' ? 'selected' : ''; ?>>Inactivo</option>
            </select>
        </div>
    </div>
    <div id="form-row-2">
        <div class="form-group>
            <label for="precio_normal">Precio normal:</label>
            <input type="text" id="precio_normal" name="precio_normal" value="<?php echo htmlspecialchars($precios['Precio_normal']); ?>" class="form-control">
        </div>
        <div class="form-group">
            <label for="precio_descuento">Precio descuento:</label>
            <input type="text" id="precio_descuento" name="precio_descuento" value="<?php echo htmlspecialchars($precios['Precio_descuento']); ?>" class="form-control">
        </div>
        <div class="form-group">
            <label for="precio_descuento_2">Precio descuento 2:</label>
            <input type="text" id="precio_descuento_2" name="precio_descuento_2" value="<?php echo htmlspecialchars($precios['Precio_descuento_2']); ?>" class="form-control">
        </div>
        <div class="form-group">
            <label for="precio_mayorista">Precio mayorista:</label>
            <input type="text" id="precio_mayorista" name="precio_mayorista" value="<?php echo htmlspecialchars($precios['Precio_mayorista']); ?>" class="form-control">
        </div>
        <div class="form-group">
            <label for="precio_oferta">Precio oferta:</label>
            <input type="text" id="precio_oferta" name="precio_oferta" value="<?php echo htmlspecialchars($precios['Precio_oferta']); ?>" class="form-control">
        </div>
    </div>
    <!-- <div class="form-group">
        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
    </div> -->
</form>
<?php
if (!$isAjax) {
?>
<!-- <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<script>
    $(document).ready(function() {
        $('#editProductForm').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                success: function(response) {
                    var res = JSON.parse(response);
                    if (res.status === 'success') {
                        alert(res.message);
                    } else {
                        alert(res.message);
                    }
                }
            });
        });
    });
</script> -->
</body>
</html>
<?php
}
?>
