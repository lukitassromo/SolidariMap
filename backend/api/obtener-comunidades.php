<?php
session_start();
header("Content-Type: application/json");
require_once '../db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "error" => "No has iniciado sesión."]);
    exit;
}

$id_usuario = $_SESSION['user_id'];

$stmt = $conexion->prepare("
    SELECT p.id, p.nombre, p.edad, p.detalles, p.horario
    FROM comunidades c
    JOIN personas p ON c.id_persona = p.id
    WHERE c.id_usuario = ?
");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$resultado = $stmt->get_result();

$comunidades = [];
while ($fila = $resultado->fetch_assoc()) {
    $comunidades[] = $fila;
}

echo json_encode(["success" => true, "comunidades" => $comunidades]);

$stmt->close();
$conexion->close();
