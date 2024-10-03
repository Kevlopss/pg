// Función para mostrar el formulario correspondiente a la acción seleccionada
function mostrarFormulario(accion) {
    // Obtener el elemento del formulario
    var formulario = document.getElementById('formulario');

    // Limpiar el contenido del formulario
    formulario.innerHTML = '';

    // Determinar qué acción se ha seleccionado y construir el contenido del formulario correspondiente
    switch (accion) {
        case 'crear':
            formulario.innerHTML = `
                <h2>Crear Nuevo Rol</h2>
                <form id="crearFormulario" onsubmit="guardarRegistro(); return false;">
                    <div class="btnss">
                    
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" required>
                    <label for="estado">Estado:</label>
                    <select id="estado" name="estado">
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                    
                    </div>
                    <div class="btnss">
                        <button type="submit">Guardar</button>
                        <button type="button" onclick="ocultarFormulario()">Cancelar</button>
                    </div>
                </form>
            `;
            break;
        case 'actualizar':
               // Solicitar al usuario que ingrese el ID del registro a editar
            var id = prompt("Por favor, ingrese el ID del registro a editar:");
            if (id !== null) { // Verificar si el usuario ingresó un ID o canceló la operación
                // Obtener datos del registro a editar
                fetch('obtener_registro.php?id=' + id)
                    .then(response => response.json())
                    .then(data => {
                        formulario.innerHTML = `
                            <h2>Editar Rol</h2>
                            <form id="editarFormulario" onsubmit="actualizarRegistro(${id}); return false;">
                                <label for="nombre">Nombre:</label>
                                <input type="text" id="nombre" name="nombre" value="${data.nombre}" required>
                                <label for="estado">Estado:</label>
                                <select id="estado" name="estado">
                                    <option value="1" ${data.estado === '1' ? 'selected' : ''}>Activo</option>
                                    <option value="0" ${data.estado === '0' ? 'selected' : ''}>Inactivo</option>
                                </select>
                                <button type="submit">Guardar Cambios</button>
                                <button type="button" onclick="ocultarFormulario()">Cancelar</button>
                            </form>
                        `;
                    })
                    .catch(error => console.error('Error al obtener el registro:', error));
            }
            break;
        case 'borrar':
            // Obtener roles para mostrar en el formulario de borrado
            fetch('obtener_roles.php')
                .then(response => response.json())
                .then(data => {
                    var listaRoles = '<h3>Selecciona los roles a borrar:</h3><div id="listaRegistros">';
                    data.forEach(function (rol) {
                        listaRoles += `
                            <label>
                                <input type="checkbox" name="roles" value="${rol.id_rol}">
                                ${rol.nombre}
                            </label><br>`;
                    });
                    listaRoles += '</div>';
                    formulario.innerHTML = `
                        <h2>Borrar Rol</h2>
                        <form id="borrarFormulario" onsubmit="borrarRegistros(); return false;">
                            ${listaRoles}
                            <button type="submit">Borrar</button>
                            <button type="button" onclick="ocultarFormulario()">Cancelar</button>
                        </form>
                    `;
                })
                .catch(error => console.error('Error al obtener los roles:', error));

            break;
        default:
            break;
    }

    // Mostrar el formulario
    formulario.style.display = 'block';
}

// Función para ocultar el formulario
function ocultarFormulario() {
    var formulario = document.getElementById('formulario');
    formulario.style.display = 'none';
}

// Función para regresar
function regresar() {
    // Aquí puedes agregar la lógica para regresar a la página principal o a otra página
}

// función de crear \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
function guardarRegistro() {
    // Obtener el formulario de creación
    var formulario = document.getElementById('crearFormulario');
    
    // Enviar el formulario a través de una petición AJAX
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'vistas\modulos\rol\crear_rol.php', true);
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status >= 200 && xhr.status < 400) {
            // La solicitud fue exitosa
            console.log(xhr.responseText);
            // Mostrar mensaje de confirmación en el formulario
            var mensajeConfirmacion = document.createElement('div');
            mensajeConfirmacion.textContent = "Registro creado exitosamente";
            formulario.appendChild(mensajeConfirmacion);
            // Limpiar el formulario después de enviarlo
            formulario.reset();
            // Opcional: Ocultar el formulario después de crear el registro
            setTimeout(function() {
                mensajeConfirmacion.style.display = 'none';
            }, 2000);
        } else {
            // Ocurrió un error al procesar la solicitud
            console.error('Error al procesar la solicitud');
        }
    };
    xhr.onerror = function() {
        // Hubo un error de conexión
        console.error('Error de conexión');
    };
    xhr.send(new URLSearchParams(new FormData(formulario)));
}
//este es el fin de crear\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
// Función para borrar registros //////////////////////////////
function borrarRegistros() {
    // Obtener los registros seleccionados para borrar
    var registros = document.querySelectorAll('input[type="checkbox"]:checked');
    var ids = [];
    registros.forEach(function(registro) {
        ids.push(registro.value);
    });

    // Confirmar el borrado de los registros
    var confirmarBorrado = confirm("¿Estás seguro de que quieres borrar los registros seleccionados?");
    if (confirmarBorrado) {
        // Enviar una solicitud AJAX para borrar los registros seleccionados
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'borrar_registro.php', true);
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 400) {
                // La solicitud fue exitosa
                console.log(xhr.responseText);
                // Actualizar la lista de registros después de borrar los registros
                // (puedes recargar la página o actualizar la lista de registros de otra manera)
                // Otra manera de actualizar la lista de registros es volver a cargar la página
                location.reload();
            } else {
                // Ocurrió un error al procesar la solicitud
                console.error('Error al procesar la solicitud');
            }
        };
        xhr.onerror = function() {
            // Hubo un error de conexión
            console.error('Error de conexión');
        };
        xhr.send('ids=' + JSON.stringify(ids));

        // Cerrar el formulario después de confirmar el borrado
        ocultarFormulario();
    }
}
//fin de borrar /////////////////////////////////////////
// Función para actualizar el registro
// Función para actualizar el registro
function actualizarRegistro() {
    // Obtener el formulario de edición
    var formulario = document.getElementById('editarFormulario');
    
    // Obtener los datos del formulario
    var id = formulario.querySelector('input[name="id"]').value;
    var nombre = formulario.querySelector('input[name="nombre"]').value;
    var estado = formulario.querySelector('select[name="estado"]').value;

    // Enviar los datos del formulario a través de una petición AJAX
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'actualizar_registro.php', true);
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status >= 200 && xhr.status < 400) {
            // La solicitud fue exitosa
            console.log(xhr.responseText);
            // Mostrar mensaje de confirmación en el formulario
            var mensajeConfirmacion = document.createElement('div');
            mensajeConfirmacion.textContent = "Registro actualizado exitosamente";
            formulario.appendChild(mensajeConfirmacion);
            // Ocultar el mensaje después de un tiempo
            setTimeout(function() {
                mensajeConfirmacion.style.display = 'none';
            }, 2000);
            // Opcional: Recargar la página para reflejar los cambios en la lista de registros
            // location.reload();
        } else {
            // Ocurrió un error al procesar la solicitud
            console.error('Error al procesar la solicitud');
        }
    };
    xhr.onerror = function() {
        // Hubo un error de conexión
        console.error('Error de conexión');
    };
    xhr.send('id=' + id + '&nombre=' + encodeURIComponent(nombre) + '&estado=' + estado);
}



