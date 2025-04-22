<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
header("Content-Type: application/json");

require_once dirname(__DIR__) . '/db.php';

try {
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

    // Verificar si ya está unido
    $stmt = $conexion->prepare("SELECT 1 FROM comunidades WHERE id_usuario = ? AND id_persona = ?");
    $stmt->bind_param("ii", $id_usuario, $id_persona);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo json_encode(["success" => false, "error" => "Ya estás unido a esta comunidad."]);
        $stmt->close();
        exit;
    }
    $stmt->close();

    // Insertar relación
    $stmt = $conexion->prepare("INSERT INTO comunidades (id_usuario, id_persona) VALUES (?, ?)");
    $stmt->bind_param("ii", $id_usuario, $id_persona);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "Error al unirse a la comunidad."]);
    }

    $stmt->close();
    $conexion->close();

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Error interno: " . $e->getMessage()]);
}
