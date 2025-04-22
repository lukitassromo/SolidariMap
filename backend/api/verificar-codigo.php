<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

require_once '../db.php';

// Obtener datos del JSON enviado
$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? '';
$codigoIngresado = $data['codigo'] ?? '';

// Validar datos
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || empty($codigoIngresado)) {
    echo json_encode(["success" => false, "error" => "Datos inválidos."]);
    exit;
}

// Buscar código en la base de datos que no esté vencido
$stmt = $conexion->prepare("SELECT * FROM codigos_verificacion WHERE email = ? AND codigo = ? AND expiracion > NOW() ORDER BY creado_en DESC LIMIT 1");
$stmt->bind_param("ss", $email, $codigoIngresado);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
    echo json_encode(["success" => true, "message" => "Código verificado correctamente."]);
    // Aquí podrías continuar con la creación del usuario si todo es correcto
} else {
    echo json_encode(["success" => false, "error" => "Código incorrecto o expirado."]);
}

$stmt->close();
$conexion->close();
?>
