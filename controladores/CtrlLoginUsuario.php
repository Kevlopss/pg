<?php

require_once('modelos/MdlUsuario.php');

class ControladorUsuario {
    public function ctrlLoginUsuario() {
        if (isset($_POST['txt_usuario'])) {

            if (preg_match('/^[a-zA-Z0-9]+$/', $_POST["txt_usuario"]) &&
                preg_match('/^[a-zA-Z0-9]+$/', $_POST["txt_clave"])
            ) {

                $usuario = $_POST["txt_usuario"];
                $clave = $_POST["txt_clave"];

                // Utiliza la clase ModeloUsuario
                $result = ModeloUsuario::mdlBuscarUsuario($usuario);

                if (isset($result["usuario"]) && $result["usuario"] == $_POST['txt_usuario'] &&
                    isset($result["clave"]) && $result["clave"] == $_POST['txt_clave']
                ) {
                    // Inicia la sesión si no está activa
                    if (session_status() == PHP_SESSION_NONE) {
                        session_start();
                    }

                    // Almacena el número de di_usuario en la variable de sesión
                    $_SESSION['id_usuario'] = $result["id_usuario"];
                    $_SESSION['login'] = 'activa';

                    // Asegúrate de que no haya salida antes de esta línea
                    header('Location: inicio');
                    exit(); // Asegura que el script se detenga después de la redirección

                } else {
                    echo '<div class="alert alert-danger mt-3"> Accesos incorrectos </div>';
                }
            }
        }
    }
}
