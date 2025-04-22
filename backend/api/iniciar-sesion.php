<?php
// Mostrar errores para depuración
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Leer y validar JSON antes de iniciar sesión
$data = json_decode(file_get_contents("php://input"), true);
$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';
$recordarme = $data['recordarme'] ?? false;

// Si el usuario eligió "recordarme", extender duración de la sesión (¡debe ir antes del session_start!)
if ($recordarme) {
    ini_set('session.gc_maxlifetime', 60 * 60 * 24 * 7); // 7 días
    session_set_cookie_params(60 * 60 * 24 * 7);
}

// Iniciar sesión después de configurar duración
session_start();

// Cabeceras para JSON
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

// Incluir conexión a la base de datos
require_once '../db.php';

try {
    if (empty($email) || empty($password)) {
        echo json_encode(["success" => false, "error" => "Campos incompletos"]);
        exit;
    }

    // Buscar el usuario
    $stmt = $conexion->prepare("SELECT id, contrasena FROM usuarios WHERE email = ?");
    if (!$stmt) {
        throw new Exception("Error en prepare(): " . $conexion->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($fila = $resultado->fetch_assoc()) {
        if (password_verify($password, $fila['contrasena'])) {
            $_SESSION['user_id'] = $fila['id'];
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "error" => "Contraseña incorrecta"]);
        }
    } else {
        echo json_encode(["success" => false, "error" => "Usuario no encontrado"]);
    }

    $stmt->close();
    $conexion->close();

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Error del servidor: " . $e->getMessage()]);
}