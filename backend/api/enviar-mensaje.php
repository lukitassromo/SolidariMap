<?php
session_start();
header("Content-Type: application/json");
require_once '../db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "error" => "No has iniciado sesión."]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$id_persona = $data['id_persona'] ?? null;
$texto = trim($data['texto'] ?? '');

if (!$id_persona || $texto === '') {
    echo json_encode(["success" => false, "error" => "Datos incompletos."]);
    exit;
}

$id_usuario = $_SESSION['user_id'];
$fecha = date("Y-m-d H:i:s");

$stmt = $conexion->prepare("INSERT INTO mensajes (id_usuario, id_persona, texto, enviado_en) VALUES (?, ?, ?, ?)");
$stmt->bind_param("iiss", $id_usuario, $id_persona, $texto, $fecha);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => "No se pudo guardar el mensaje."]);
}

$stmt->close();
$conexion->close();
