<?php
$host = "127.0.0.1";
$usuario = "root";
$contrasena = ""; // sin contraseña en XAMPP
$base_de_datos = "solidarimap";
$puerto = 3307; // asegurate de usar el puerto correcto en tu XAMPP

$conexion = new mysqli($host, $usuario, $contrasena, $base_de_datos, $puerto);

if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}
?>