<?php
// Importar la conexión a la base de datos
require __DIR__ . '/../../../includes/config/DbConection.php';

$db = $conectarDB;

// Verificar si el ID del producto se ha pasado como parámetro

if (isset($_GET['id'])) {
    $id = htmlspecialchars($_GET['id']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'No se ha proporcionado un ID del producto.']);
    exit;
}
    $sql = "
    SELECT 
        e.*, 
        c.Id_presentacion_producto, c.Nombre AS Pres_producto, c.Medida,
        t.Id_tipo_producto, t.Nombre AS Nombre_tipo,
        tt.Id_categoria_producto, tt.Nombre AS Nombre_cat,
        pp.Precio_normal, pp.Precio_descuento, pp.Precio_descuento_2, pp.Precio_mayorista, pp.Precio_oferta
    FROM producto AS e
    LEFT JOIN presentacion_producto AS c ON e.Id_presentacion_producto = c.Id_presentacion_producto
    LEFT JOIN tipo_producto AS t ON e.Id_tipo_producto = t.Id_tipo_producto
    LEFT JOIN categoria_producto AS tt ON e.Id_categoria_producto = tt.Id_categoria_producto
    LEFT JOIN precio_producto AS pp ON e.Id_producto = pp.Id_producto
    WHERE e.Id_producto = ?";
    // $sql = "SELECT p class="form-group" id="form-groups" id="ps".Cod_producto, p class="form-group" id="form-groups" id="ps".Nombre, p class="form-group" id="form-groups" id="ps".Descripcion, p class="form-group" id="form-groups" id="ps".Imagen, p class="form-group" id="form-groups" id="ps".Id_presentacion_producto, p class="form-group" id="form-groups" id="ps".Id_tipo_producto, 
    // p class="form-group" id="form-groups" id="ps".Id_categoria_producto, p class="form-group" id="form-groups" id="ps".Estado, 
    // pp.Precio_normal, pp.Precio_descuento, pp.Precio_descuento_2, pp.Precio_mayorista, pp.Precio_oferta
    // FROM producto AS p class="form-group" id="form-groups" id="ps"
    // LEFT JOIN precio_producto AS pp ON p class="form-group" id="form-groups" id="ps".Id_producto = pp.Id_producto
    // WHERE p class="form-group" id="form-groups" id="ps".Id_producto = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param('i', $id);
    $result = $stmt -> execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'No se encontró el producto con el ID proporcionado.']);
        exit;
    }
    $row = $result->fetch_assoc();
    // var_dump($row);
    // exit;
    $stmt->close();

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if (!$isAjax) {
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ver producto</title>
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
<!-- <style>
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
</style> -->
<?php

?>
<style>
    #VERProduct {
        display: flex;
        flex-wrap: wrap;
        
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
    #imgcenter{
        text-align: center;
    }
    #section_1{
        width: 49%;
        margin-right: 2%;
        margin-bottom: 20px;
        border: solid 2px black;
        border-radius: 1rem;
        padding: 20px;
        
    }
    #section_2{
        width: 48.5%;
        /* margin-right: 5%; */
        margin-bottom: 20px;
        border: solid 2px black;
        border-radius: 1rem;
        padding: 20px;
    }
    #form-groups {
        margin-bottom: 15px;
        background-color: #E5E8E8 ;
        padding: 15px;
        border-radius: 1rem;
    }
    label {
        display: block;
        font-weight: bold;
    }
    p {
        margin: 0;
        padding: 0;
    }
    .bordered-div {
    position: relative;
    padding: 20px;
    border: 2px solid black;
    /* margin: 50px; */
    }

    .bordered-div::before {
    position: absolute;
    top: -14px; /* Ajusta esta propiedad según sea necesario */
    left: 10px; /* Ajusta esta propiedad según sea necesario */
    background-color: white;
    padding: 0 5px;
    font-size: 16px;
    color: black;
    }

    .bordered-div::before {
    content: "Datos";
    }

    .bordered-div2::before {
    content: "Precios";
    }
</style>
<div id="VERProduct">
    <div id="section_1" class="bordered-div bordered-div1">
        <div class="form-group" id="form-groups">
            <label>Codigo:</label>
            <p><?php echo $row['Cod_producto'] ?></p>
        </div>
        <div class="form-group" id="form-groups">
            <label>Nombre:</label>
            <p><?php echo $row['Nombre'] ?></p>
        </div>
        <div class="form-group" id="form-groups">
            <label>Descripción:</label>
            <p><?php echo $row['Descripcion'] ?></p>
        </div>
        <div class="form-group" id="form-groups">
            <label>Imagen:</label>
            <p id="imgcenter">
                <?php
                    // Obtener el nombre de la imagen en la DB
                    $imageHash = $row['Imagen'];
                    if ($imageHash){
                        // Construir la ruta completa de la imagen
                        $imagePath = '/TINES/vistas/modulos/producto/imagenes/' . $imageHash;
                    }else{
                        echo '<label style="font-weight: normal; color:red; text-decoration: underline;">Imagen no subida</label>';
                    }
                ?>
                <img src="<?php echo $imagePath; ?>" alt="?" id="imgs">
            </p>
        </div>
        <div class="form-group" id="form-groups">
            <label>Presentación:</label>
            <p><?php echo $row['Pres_producto'] ?></p>
        </div>
        <div class="form-group" id="form-groups">
            <label>Tipo:</label>
            <p><?php echo $row['Nombre_tipo'] ?></p>
        </div>
        <div class="form-group" id="form-groups">
            <label>Categoria:</label>
            <p><?php echo $row['Nombre_cat'] ?></p>
        </div>
        <div class="form-group" id="form-groups">
            <label>Estado:</label>
            <p><?php echo ($row["Estado"] == 1 ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>'); ?></p>
        </div>
    </div>
    <div id="section_2" class="bordered-div bordered-div2">
        <div class="form-group" id="form-groups">
            <label>Precio normal:</label>
            <p><?php echo $row['Precio_normal'] ?></p>
        </div>
        <div class="form-group" id="form-groups">
            <label>Precio descuento:</label>
            <p><?php echo $row['Precio_descuento'] ?></p>
        </div>
        <div class="form-group" id="form-groups">
            <label>Precio descuento 2:</label>
            <p><?php echo $row['Precio_descuento_2'] ?></p>
        </div>
        <div class="form-group" id="form-groups">
            <label>Precio mayorista:</label>
            <p><?php echo $row['Precio_mayorista'] ?></p>
        </div>
        <div class="form-group" id="form-groups">
            <label>Precio oferta:</label>
            <p><?php echo $row['Precio_oferta'] ?></p>
        </div>
    </div>
</div>
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
