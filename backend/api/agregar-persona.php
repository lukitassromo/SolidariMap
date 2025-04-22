<?php
session_start();
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

require_once '../db.php';

// ✅ Verificar que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "error" => "No estás registrado. Inicie sesión para agregar personas."]);
    exit;
}

// Recibe datos JSON del frontend
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["success" => false, "error" => "No se recibieron datos."]);
    exit;
}

// Extrae los datos del JSON
$nombre = $data['nombre'] ?? '';
$edad = $data['edad'] ?? '';
$detalles = $data['detalles'] ?? '';
$horario = $data['horario'] ?? '';
$latitud = $data['latitud'] ?? '';
$longitud = $data['longitud'] ?? '';

// Valida que no estén vacíos los campos obligatorios
if (empty($nombre) || empty($edad) || empty($horario) || empty($latitud) || empty($longitud)) {
    echo json_encode(["success" => false, "error" => "Faltan datos obligatorios."]);
    exit;
}

// Inserta en la base de datos
$stmt = $conexion->prepare("INSERT INTO personas (nombre, edad, detalles, horario, latitud, longitud) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sissdd", $nombre, $edad, $detalles, $horario, $latitud, $longitud);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => "Error al insertar en la base de datos."]);
}

$stmt->close();
$conexion->close();