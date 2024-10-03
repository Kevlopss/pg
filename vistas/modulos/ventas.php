<?php
require __DIR__ . '/../../includes/config/DbConection.php';
$db = $conectarDB;

$id_usuario = $_SESSION['id_usuario'];
$newQuery = "SELECT * FROM usuario WHERE id_usuario = ?";
$stmt = $db->prepare($newQuery);
$stmt->bind_param('i', $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
// var_dump($user);
// exit;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Punto de Venta</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <style> 
        #mensaje {
            position: fixed;
            top: 20px; /* Ajusta la distancia desde la parte superior */
            left: 50%;
            transform: translateX(-50%); /* Solo traducir horizontalmente */
            padding: 20px; /* Hacer un poco más grande el padding */
            background-color: #dff0d8;
            color: #3c763d;
            border: 1px solid #d6e9c6;
            border-radius: 10px; /* Hacer los bordes un poco más redondeados */
            font-size: 18px; /* Incrementar el tamaño de la fuente */
            z-index: 9999; /* Asegúrate de que el mensaje esté por encima de otros elementos */
            display: none;
        }
        #mensaje.error {
            background-color: #f2dede;
            color: #a94442;
            border-color: #ebccd1;
        }
        #detalle-venta {
            padding-top: 0;
            margin-top: 0;
            width: 100%;
        }
        #vista-previa {
            margin-bottom: 20px;
            text-align: center;
        }
        #imgs{
            max-width: 50%;
            max-height: 550px;
            width: auto;
            height: auto;
            border-radius: 1rem;
        }
        #vista-previa img {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
        }
        #buscarP{
            width: 100%;
        }
        #buscar {
            width: auto;
        }
        .contenedor {
            display: flex;
            justify-content: space-between;
            width: 95%;
            margin-top: 20px;
            margin-left: 20px;
        }
        #colorProducto {
            /* background: rgb(2,0,36);
            background: linear-gradient(90deg, rgba(2,0,36,1) 0%, rgba(9,121,86,1) 76%, rgba(0,212,255,1) 100%); */
            background-color: #239b56;
        }
        #h1 {
            color: white;
        }
        #ventas {
            width: 90%;
            /* margin-right: 20px;
            padding-right: 20px; */
        }
        #botones {
            display: block;
            flex-direction: row;
            justify-content: center;
            align-items: center;
            margin-right: 30px;
        }
        .detalle-venta-container {
            display: flexbox;
            flex-direction: column;
            align-items: center;
            width: 100%;
            margin-top: 30px;
        }
        #table {
            width: 100%;
        }
        .text-total {
            text-align: right;
        }
        #searchP {
            margin-top: 0;
            padding: 0;
            width: 100%;
        }
        #inline {
            display: flex;
            flex-direction: column;
            width:100%;
        }
        #agregar {
            width: auto;
        }
        #lblcodigo { margin-right: 30px; font-size: 18px; }
        #lblnombre {
            font-size: 18px;
            margin-right: 21px;
            display: block;
            background-color: green;
        }
        #div1 {
            width: 115px;
            margin-right: 50px;
        }
        #div2 {
            width: 400px;
            display: flex;
            margin-left: 30px;
        }
        .autocomplete {
            position: relative;
            display: inline-block;
        }
        input[type="text"] {
            font-size: 18px;
            padding: 8px;
            width: 300px;
            box-sizing: border-box;
        }
        .suggestions {
            border: 1px solid #ccc;
            background-color: white;
            position: absolute;
            z-index: 1000;
            width: 100%;
            max-height: 150px;
            overflow-y: auto;
        }
        .suggestion-item {
            padding: 10px;
            cursor: pointer;
            
        }
        .suggestion-item:hover {
            background-color: #f0f0f0;
        }
        #divcodigo {
            display: flex;
            margin-bottom: 20px;
            width:100%;
        }
        #divnombre {
            display: flex;
            width: 100%;
        }
        .modal-dialog {
            max-width: 80%;
        }
        .botsventa{
            display: inline-block;
            margin: 0;
            padding: 0;
            align-items: center;
        }
        
    </style>
</head>
<body>
<div class="content-wrapper">
    <section class="content-header" id="colorProducto" style="width: 100%">
        <div class="container-fluid" style="width: 100%">
            <div class="row mb-2" style="width: 100%">
                <div class="d-flex justify-content-between align-items-center" style="width: 100%">
                    <h1 id="h1" style="margin-left: 10px;">Ventas</h1>
                    <button class="btn btn-primary ms-auto mt-2 bg-light border-light" id="VerVentas"><h6>Ver ventas</h6></button>
                </div>
            </div>
        </div>
    </section>
    <div class="contenedor">
        <div class="col-md-5" id="buscarP">
            <h2 class="text-center" id="searchP">Buscar producto</h2>
            <div class="col-md-4">
                <div class="header" id="inline">
                    <div id="divcodigo">
                        <label for="codigo" id="lblcodigo">Código:</label>
                        <div class="autocomplete">
                            <input type="text" id="codigo" class="form-control d-inline w-auto" name="codigo" oninput="fetchSuggestions(this.value, 'codigo')">
                            <div id="suggestions" class="suggestions"></div>
                        </div>
                        <div class="botsventa">
                            <button id="buscar" class="btn btn-warning">Buscar</button>
                            <button id="agregar" class="btn btn-primary" >Agregar</button>
                            <button id="clean" class="btn btn-success" onclick="limpiarVistaPrevia()">Limpiar</button>
                        </div>
                    </div>

                </div>
            </div>
            <div id="vista-previa" class="detalle-venta-container"></div>
        </div>
        <div class="col-md-7" id="ventas">
            <div id="detalle-venta" class="detalle-venta-container">
                <h2 class="text-center">* DETALLE DE VENTA *</h2>
                <table class="table table-bordered" id="table">
                    <thead class="thead-light">
                        <tr>
                            <th>Cnt.</th>
                            <th>Cod.</th>
                            <th>Producto</th>
                            <th>Precio</th>
                            <th>Total</th>
                            <th>Lista</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Los detalles de la venta se añadirán aquí -->
                    </tbody>
                </table>
                <div class="text-total">
                    <p>Sub Total: <span id="subtotal">Q. 0.00</span></p>
                    <p>Total: <span id="total">Q. 0.00</span></p>
                    <p>Artículos: <span id="articulos">0</span></p>
                </div>
                <div class="form-group row">
                    <label for="importe" class="col-sm-10 col-form-label">Importe</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="importe">
                    </div>
                    <label for="cambio" class="col-sm-10 col-form-label">Cambio</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="cambio" readonly>
                    </div>
                </div>
            </div>
            <div class="botones" id="botones">
                <button id="pagar" class="btn btn-success">💵 Pagar</button>
                <!-- <button id="guardar" class="btn btn-secondary mx-2">📝 Guardar PDF</button> -->
                <button id="cancelar" class="btn btn-danger">🗑️ Cancelar</button>
                <div id="mensaje"></div>
            </div>
        </div>
    </div>
</div>
<!-- Modal Ver Ventas -->
<div class="modal fade" id="ventasModal" tabindex="-1" role="dialog" aria-labelledby="ventasModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ventasModalLabel">Ventas</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="ventasModalBody">
                    <!-- El contenido se cargará aquí mediante AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function(){
        $("#VerVentas").click(function(){
            $.ajax({
                url: '/TINES/vistas/modulos/ventas/seeSale.php',
                method: 'GET',
                success: function(response) {
                    $("#ventasModalBody").html(response);
                    $("#ventasModal").modal('show');
                },
                error: function() {
                    $("#ventasModalBody").html('<p>Error al cargar las ventas.</p>');
                    $("#ventasModal").modal('show');
                }
            });
        });
    });


    let detallesVenta = [];

    document.getElementById('buscar').addEventListener('click', buscarProducto);
    document.getElementById('agregar').addEventListener('click', agregarProducto);
    document.getElementById('cancelar').addEventListener('click', cancelarVenta);
    document.getElementById('importe').addEventListener('blur', calcularCambio);
    document.getElementById('importe').addEventListener('keypress', function(event) {
        if (event.key === 'Enter') {
            calcularCambio();
        }
    });

    function buscarProducto() {
        const codigo = encodeURIComponent($("#codigo").val().trim());

        fetch(`/TINES/vistas/modulos/ventas/buscar_producto.php?codigo=${codigo}`, {
            method: 'GET'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error al buscar el producto');
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 'success' && data.productos.length > 0) {
                const producto = data.productos[0].producto; // Tomamos el primer producto de la lista
                document.getElementById('codigo').dataset.producto = JSON.stringify(producto);
                mostrarVistaPrevia(producto);
            } else {
                alert('Producto no encontrado');
                limpiarVistaPrevia();
               
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function agregarProducto() {
        const productoData = document.getElementById('codigo').dataset.producto;
        if (!productoData) {
            alert('Primero busca un producto');
            return;
        }

        const producto = JSON.parse(productoData);
        const tabla = document.getElementById('detalle-venta').getElementsByTagName('tbody')[0];

        for (let i = 0; i < tabla.rows.length; i++) {
            if (tabla.rows[i].cells[1].textContent === producto.Cod_producto) {
                alert('El producto ya está en la lista de ventas.');
                limpiarVistaPrevia();
                return;
            }
        }

        const nuevaFila = tabla.insertRow();

        const celdaCantidad = nuevaFila.insertCell(0);
        const celdaCodigo = nuevaFila.insertCell(1);
        const celdaProducto = nuevaFila.insertCell(2);
        const celdaPrecio = nuevaFila.insertCell(3);
        const celdaTotal = nuevaFila.insertCell(4);
        const celdaLista = nuevaFila.insertCell(5);

        celdaCantidad.innerHTML = `<input type="number" class="form-control" id="manejarTecla" value="1" min="1" onchange="actualizarTotal(this)" onkeydown="manejarTeclas(event, this)">`;
        celdaCodigo.textContent = producto.Cod_producto;
        celdaProducto.textContent = producto.Nombre;

        let selectPrecio = document.createElement('select');
        selectPrecio.classList.add('form-control');
        selectPrecio.onchange = function() { actualizarTotal(this); };

        let opcionesPrecios = [
            { value: producto.Precio_normal, texto: `Precio Normal: Q. ${producto.Precio_normal}` },
            { value: producto.Precio_descuento, texto: `Precio Descuento: Q. ${producto.Precio_descuento}` },
            { value: producto.Precio_descuento_2, texto: `Precio Descuento 2: Q. ${producto.Precio_descuento_2}` },
            { value: producto.Precio_mayorista, texto: `Precio Mayorista: Q. ${producto.Precio_mayorista}` },
            { value: producto.Precio_oferta, texto: `Precio Oferta: Q. ${producto.Precio_oferta}` }
        ];

        opcionesPrecios.forEach(opcion => {
            let optionElement = document.createElement('option');
            optionElement.value = opcion.value;
            optionElement.textContent = opcion.texto;
            selectPrecio.appendChild(optionElement);
        });

        celdaPrecio.appendChild(selectPrecio);
        celdaTotal.textContent = `Q. ${producto.Precio_normal}`;
        celdaLista.innerHTML = `<button class="btn btn-danger btn-sm" onclick="eliminarProducto(this)">X</button>`;
        actualizarTotalVenta();
        limpiarVistaPrevia();
    }

    function manejarTeclas(event, input) {
        event.preventDefault();
        if (event.key === 'ArrowUp') {
            input.value = parseInt(input.value) + 1;
            actualizarTotal(input);
        } else if (event.key === 'ArrowDown') {
            if (input.value > 1) {
                input.value = parseInt(input.value) - 1;
                actualizarTotal(input);
            }
        }
    }

    function actualizarTotal(elemento) {
        const fila = elemento.parentElement.parentElement;
        const cantidad = fila.cells[0].getElementsByTagName('input')[0].value;
        const selectPrecio = fila.cells[3].getElementsByTagName('select')[0];
        const precio = parseFloat(selectPrecio.value);
        const total = cantidad * precio;
        fila.cells[4].textContent = `Q. ${total.toFixed(2)}`;
        actualizarTotalVenta();
    }

    function actualizarTotalVenta() {
        const tabla = document.getElementById('detalle-venta').getElementsByTagName('tbody')[0];
        let subtotal = 0;
        let articulos = 0;

        for (let i = 0; i < tabla.rows.length; i++) {
            const fila = tabla.rows[i];
            const total = parseFloat(fila.cells[4].textContent.replace('Q. ', ''));
            subtotal += total;
            articulos += parseInt(fila.cells[0].getElementsByTagName('input')[0].value);
        }

        document.getElementById('subtotal').textContent = `Q. ${subtotal.toFixed(2)}`;
        document.getElementById('total').textContent = `Q. ${subtotal.toFixed(2)}`;
        document.getElementById('articulos').textContent = articulos;
    }

    function eliminarProducto(boton) {
        const fila = boton.parentElement.parentElement;
        fila.remove();
        actualizarTotalVenta();
    }

    function cancelarVenta() {
        const tabla = document.getElementById('detalle-venta').getElementsByTagName('tbody')[0];
        tabla.innerHTML = '';
        actualizarTotalVenta();
        document.getElementById('cambio').value = ``;
        document.getElementById('importe').value = ``;
    }

    function calcularCambio() {
        const importe = parseFloat(document.getElementById('importe').value);
        const total = parseFloat(document.getElementById('total').textContent.replace('Q. ', ''));
        const cambio = importe - total;
        document.getElementById('cambio').value = `Q. ${cambio.toFixed(2)}`;
    }

    function mostrarVistaPrevia(producto) {
        if (!producto || !producto.Nombre) {
            console.error('Producto inválido:', producto);
            return;
        }

    const vistaPrevia = document.getElementById('vista-previa');
        vistaPrevia.innerHTML = `
            <div id="div1" class="float-left">        
            <p style="text-decoration: underline;"><strong>${producto.Nombre}</strong></p>
            <p>${producto.Descripcion}</p>
            <p>Presentación: ${producto.Pres_producto}</p>
            <p>Tipo: ${producto.Nombre_tipo}</p>
            <p>Categoría: ${producto.Nombre_cat}</p>
            <p>Precio: Q. ${producto.Precio_normal}</p>
            </div>
            <div id="div2" class="si">
            <img id="imgs" src="/TINES/vistas/modulos/producto/imagenes/${producto.Imagen}" alt="${producto.Nombre}"></div>
        `;
    }

    function limpiarVistaPrevia() {
        document.getElementById('vista-previa').innerHTML = '';
        document.getElementById('codigo').value = ''; // Limpiar el valor del input
        document.getElementById('codigo').dataset.producto = ''; // Limpiar el dataset del input
    }

    function fetchSuggestions(term, field) {
    if (term.length < 2) {
        document.getElementById('suggestions').innerHTML = '';
        return;
    }

    fetch(`/TINES/vistas/modulos/ventas/buscar_producto.php?codigo=${encodeURIComponent(term)}`)
        .then(response => response.json())
        .then(data => {
            const suggestionsContainer = document.getElementById('suggestions');
            suggestionsContainer.innerHTML = '';

            if (data.status === 'success') {
                data.productos.forEach(item => {
                    const suggestionItem = document.createElement('div');
                    suggestionItem.classList.add('suggestion-item');
                    suggestionItem.textContent = item.label;
                    suggestionItem.dataset.value = item.value;
                    suggestionItem.dataset.producto = JSON.stringify(item.producto);
                    suggestionItem.addEventListener('click', () => selectSuggestion(suggestionItem, field));
                    suggestionsContainer.appendChild(suggestionItem);
                });
            } else {
                suggestionsContainer.innerHTML = '<div class="suggestion-item">No se encontraron resultados</div>';
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function selectSuggestion(suggestionItem, field) {
        const input = document.getElementById(field);
        input.value = suggestionItem.textContent;
        document.getElementById('suggestions').innerHTML = '';

        try {
            const producto = JSON.parse(suggestionItem.dataset.producto);
            console.log('Producto seleccionado:', producto); // Verifica los datos del producto en la consola
            mostrarVistaPrevia(producto);
            input.dataset.producto = JSON.stringify(producto);
        } catch (error) {
            console.error('Error al parsear el producto:', error);
        }
    }

    document.addEventListener('click', function (event) {
        const suggestionsContainer = document.getElementById('suggestions');
        if (!suggestionsContainer.contains(event.target) && event.target !== document.getElementById('codigo')) {
            suggestionsContainer.innerHTML = '';
        }
    });

    document.getElementById("pagar").addEventListener("click", function() {
        EnviarDatosRegistrarDB();
        obtenerYEnviarDatos();
        cancelarVenta();
    });

    document.getElementById("guardar").addEventListener("click", function() {
        obtenerYEnviarDatos();
    });

    let data;

    function obtenerYEnviarDatos() {
        let detallesVenta = [];
        const tabla = document.getElementById('detalle-venta').getElementsByTagName('tbody')[0];

        for (let i = 0; i < tabla.rows.length; i++) {
            const fila = tabla.rows[i];
            const detalle = {
                producto_id: fila.cells[1].textContent,
                cantidad: parseInt(fila.cells[0].getElementsByTagName('input')[0].value),
                precio_unitario: parseFloat(fila.cells[3].getElementsByTagName('select')[0].value),
                total: parseFloat(fila.cells[4].textContent.replace('Q. ', ''))
            };
            detallesVenta.push(detalle);
        }

        const fechaActual = new Date();
        const fechaFormateada = fechaActual.getFullYear() + '-' +
            ('0' + (fechaActual.getMonth() + 1)).slice(-2) + '-' +
            ('0' + fechaActual.getDate()).slice(-2) + ' ' +
            ('0' + fechaActual.getHours()).slice(-2) + ':' +
            ('0' + fechaActual.getMinutes()).slice(-2) + ':' +
            ('0' + fechaActual.getSeconds()).slice(-2);

        const data = {
            fecha: fechaFormateada,
            total: parseFloat(document.getElementById('total').textContent.replace('Q. ', '')),
            importe: parseFloat(document.getElementById('importe').value),
            cambio: parseFloat(document.getElementById('cambio').value.replace('Q. ', '')),
            detalles: detallesVenta,
            usuario: <?php echo json_encode($user); ?> // Incluir datos del usuario aquí
        };

        // console.log(data); // mostrar el array en consola del DEV

        // Enviar los datos al servidor para generar el PDF
        fetch('/TINES/vistas/modulos/ventas/reportesV.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.blob(); // Recibir el PDF como un blob
        })
        .then(blob => {
            // Crear un objeto URL para el blob
            const url = window.URL.createObjectURL(blob);
            window.open(url, '_blank'); // Abrir el PDF en una nueva pestaña
            window.URL.revokeObjectURL(url); // Opcionalmente, revocar el objeto URL después de abrirlo
        })
        .catch(error => console.error('Error:', error));
    }

    function EnviarDatosRegistrarDB() {
    let detallesVenta = [];
    const tabla = document.getElementById('detalle-venta').getElementsByTagName('tbody')[0];

    for (let i = 0; i < tabla.rows.length; i++) {
        const fila = tabla.rows[i];
        const detalle = {
            producto_id: fila.cells[1].textContent,
            cantidad: parseInt(fila.cells[0].getElementsByTagName('input')[0].value),
            precio_unitario: parseFloat(fila.cells[3].getElementsByTagName('select')[0].value),
            total: parseFloat(fila.cells[4].textContent.replace('Q. ', ''))
        };
        detallesVenta.push(detalle);
    }

    const fechaActual = new Date();
    const fechaFormateada = fechaActual.getFullYear() + '-' +
        ('0' + (fechaActual.getMonth() + 1)).slice(-2) + '-' +
        ('0' + fechaActual.getDate()).slice(-2) + ' ' +
        ('0' + fechaActual.getHours()).slice(-2) + ':' +
        ('0' + fechaActual.getMinutes()).slice(-2) + ':' +
        ('0' + fechaActual.getSeconds()).slice(-2);

    const data = {
        fecha: fechaFormateada,
        total: parseFloat(document.getElementById('total').textContent.replace('Q. ', '')),
        importe: parseFloat(document.getElementById('importe').value),
        cambio: parseFloat(document.getElementById('cambio').value.replace('Q. ', '')),
        detalles: detallesVenta,
        usuario: <?php echo json_encode($user); ?> // Incluir datos del usuario aquí
    };

    // console.log(data); // mostrar el array en la consola del DEV

    // Enviar los datos al servidor
    fetch('/TINES/vistas/modulos/ventas/registrar_venta.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        const mensajeDiv = document.getElementById("mensaje");
        mensajeDiv.style.display = 'block';
        if (data.status === 'success') {
            mensajeDiv.className = ''; // Remove any existing class
            mensajeDiv.innerHTML = `<p>${data.message}</p>`;
        } else {
            mensajeDiv.className = 'error';
            mensajeDiv.innerHTML = `<p>${data.message}</p>`;
        }
        // Hacer que el mensaje desaparezca después de 2 segundos
        setTimeout(() => {
            mensajeDiv.style.display = 'none';
        }, 2000);
    })
    .catch(error => {
        console.error('Error:', error);
        const mensajeDiv = document.getElementById("mensaje");
        mensajeDiv.className = 'error';
        mensajeDiv.style.display = 'block';
        mensajeDiv.innerHTML = `<p>Error al registrar la venta.</p>`;
        setTimeout(() => {
            mensajeDiv.style.display = 'none';
        }, 2000);
    });
}

</script>
</body>
</html>
