<?php
// Mostrar errores en desarrollo (podés quitar esto en producción)
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require_once '../db.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "error" => "No estás autenticado."]);
    exit;
}

$userId = $_SESSION['user_id'];

$uploadDir = "../../public/uploads/";
$maxFileSize = 2 * 1024 * 1024; // 2MB

if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(["success" => false, "error" => "No se recibió ninguna imagen."]);
    exit;
}

$archivo = $_FILES['foto'];
$ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
$permitidos = ['jpg', 'jpeg', 'png'];

if (!in_array($ext, $permitidos)) {
    echo json_encode(["success" => false, "error" => "Formato no permitido. Solo JPG y PNG."]);
    exit;
}

if ($archivo['size'] > $maxFileSize) {
    echo json_encode(["success" => false, "error" => "Máximo permitido: 2MB."]);
    exit;
}

$nombreFinal = "perfil-" . $userId . "." . $ext;
$rutaDestino = $uploadDir . $nombreFinal;

// Crear la carpeta si no existe
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        echo json_encode(["success" => false, "error" => "No se pudo crear el directorio de subida."]);
        exit;
    }
}

// Borrar imagen anterior si existe
if (file_exists($rutaDestino)) {
    unlink($rutaDestino);
}

// Mover archivo subido
if (!move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
    echo json_encode(["success" => false, "error" => "Error al guardar la imagen."]);
    exit;
}

// Guardar en base de datos
$stmt = $conexion->prepare("UPDATE usuarios SET foto_perfil = ? WHERE id = ?");
$stmt->bind_param("si", $nombreFinal, $userId);
$stmt->execute();
$stmt->close();

// URL para mostrar la imagen
$rutaPublica = "../public/uploads/" . $nombreFinal;

echo json_encode([
    "success" => true,
    "url" => $rutaPublica
]);