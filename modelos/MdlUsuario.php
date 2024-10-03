<?php

require_once("conexion.php");

class ModeloUsuario
{
    static public function mdlBuscarUsuario($usuario)
    {
        try {
            // Crear una instancia de la clase conexion
            $conexion = new conexion();

            // Llamar al método no estático conectar
            $con = $conexion->conectar();

            $stm = $con->prepare("SELECT * FROM usuario WHERE usuario = :usuario");
            $stm->bindParam(":usuario", $usuario, PDO::PARAM_STR);
            $stm->execute();
            
            return $stm->fetch();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}