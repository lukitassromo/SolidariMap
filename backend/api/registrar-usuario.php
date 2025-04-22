<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

require_once '../db.php';

try {
    $data = json_decode(file_get_contents("php://input"), true);
    $nombre = trim($data['nombre'] ?? '');
    $email = trim($data['email'] ?? '');
    $rawContrasena = $data['password'] ?? ''; // <- aquí el cambio
    $codigo = $data['codigo'] ?? '';

    // Validaciones básicas
    if (empty($nombre) || empty($email) || empty($rawContrasena) || empty($codigo)) {
        echo json_encode(["success" => false, "error" => "Todos los campos son obligatorios."]);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["success" => false, "error" => "Correo electrónico inválido."]);
        exit;
    }

    // Verificar si el correo ya está registrado
    $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo json_encode(["success" => false, "error" => "Ya existe una cuenta con ese correo."]);
        $stmt->close();
        exit;
    }
    $stmt->close();

    // Verificar código válido y no vencido
    $ahora = date("Y-m-d H:i:s");
    $stmt = $conexion->prepare("SELECT id FROM codigos_verificacion WHERE email = ? AND codigo = ? AND expiracion > ?");
    $stmt->bind_param("sss", $email, $codigo, $ahora);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if (!$resultado || $resultado->num_rows === 0) {
        echo json_encode(["success" => false, "error" => "Código inválido o expirado."]);
        $stmt->close();
        exit;
    }
    $stmt->close();

    // Hashear y registrar usuario
    $contrasena = password_hash($rawContrasena, PASSWORD_DEFAULT);
    $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, email, contrasena) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $nombre, $email, $contrasena);
    $exito = $stmt->execute();
    $stmt->close();

    if ($exito) {
        // Eliminar código de verificación usado
        $stmt = $conexion->prepare("DELETE FROM codigos_verificacion WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->close();

        echo json_encode(["success" => true, "message" => "Usuario registrado correctamente."]);
    } else {
        echo json_encode(["success" => false, "error" => "Error al registrar el usuario."]);
    }

    $conexion->close();

} catch (Throwable $e) {
    echo json_encode([
        "success" => false,
        "error" => "Error interno: " . $e->getMessage()
    ]);
}
