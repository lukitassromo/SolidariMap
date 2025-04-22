<?php
session_start();
header("Content-Type: application/json");
require_once '../db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "error" => "No has iniciado sesión."]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$nombre = trim($data['nombre'] ?? '');
$telefono = trim($data['telefono'] ?? '');

if ($nombre === '' && $telefono === '') {
    echo json_encode(["success" => false, "error" => "No se enviaron datos para actualizar."]);
    exit;
}

$userId = $_SESSION['user_id'];
$campos = [];
$valores = [];
$tipos = '';

if ($nombre !== '') {
    $campos[] = "nombre = ?";
    $valores[] = $nombre;
    $tipos .= 's';
}

if ($telefono !== '') {
    $campos[] = "telefono = ?";
    $valores[] = $telefono;
    $tipos .= 's';
}

$valores[] = $userId;
$tipos .= 'i';

$sql = "UPDATE usuarios SET " . implode(", ", $campos) . " WHERE id = ?";
$stmt = $conexion->prepare($sql);

if (!$stmt) {
    echo json_encode(["success" => false, "error" => "Error en la preparación: " . $conexion->error]);
    exit;
}

$stmt->bind_param($tipos, ...$valores);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => "No se pudo actualizar el perfil."]);
}

$stmt->close();
$conexion->close();