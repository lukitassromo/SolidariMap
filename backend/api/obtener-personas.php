<?php
header("Content-Type: application/json");
require_once "../db.php"; // Asegurate que este archivo exista y se llame así

try {
    $resultado = $conexion->query("SELECT id, nombre, edad, detalles, horario, latitud, longitud FROM personas");

    $personas = [];

    while ($fila = $resultado->fetch_assoc()) {
        $personas[] = $fila;
    }

    echo json_encode(["success" => true, "data" => $personas]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>