<?php
class conexion
{
    public function conectar(){

        $con = new PDO("mysql:host=localhost;dbname=tines;","root","");
        $con->exec("set names utf8");

        return $con;

    }
}
