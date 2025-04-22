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
$id_usuario = $_SESSION['user_id'];

if (!$id_persona) {
    echo json_encode(["success" => false, "error" => "ID de persona no proporcionado."]);
    exit;
}

$stmt = $conexion->prepare("DELETE FROM comunidades WHERE id_usuario = ? AND id_persona = ?");
$stmt->bind_param("ii", $id_usuario, $id_persona);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => "No se pudo abandonar la comunidad."]);
}

$stmt->close();
$conexion->close();
