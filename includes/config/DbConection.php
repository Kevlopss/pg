<?php
$servername = "localhost";  // El nombre del servidor MySQL (puede ser "localhost" para la mayoría de las instalaciones locales)
$username = "root"; // Tu nombre de usuario de MySQL (que has usado en SQL Server)
$password = ""; // Tu contraseña de MySQL (que has usado en SQL Server)
$database = "tines"; // Nombre de la base de datos a la que deseas conectarte

// Crear una conexión a la base de datos
$conectarDB = new mysqli($servername, $username, $password, $database);

// Verificar la conexión
if ($conectarDB->connect_error) {
    die("Conexión fallida: " . $conectarDB->connect_error);
} else {
    // Verificar errores de MySQL
    if ($conectarDB->errno) {
        die("Error de MySQL: " . $conectarDB->error);
    }
}
// $conectarDB->close();

