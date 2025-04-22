<?php
session_start();
header("Content-Type: application/json");
require_once '../db.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(["success" => false, "error" => "ID no especificado"]);
    exit;
}

// Buscar datos de la persona
$stmt = $conexion->prepare("SELECT id, nombre, edad, detalles, horario, latitud, longitud FROM personas WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($persona = $resultado->fetch_assoc()) {
    $stmt->close();

    // Contar cuántos usuarios se unieron a esta persona
    $stmt = $conexion->prepare("SELECT COUNT(*) AS cantidad FROM comunidades WHERE id_persona = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $count = $res->fetch_assoc()['cantidad'] ?? 0;
    $stmt->close();

    // Verificar si el usuario actual ya se unió
    $yaUnido = false;
    if (isset($_SESSION['user_id'])) {
        $id_usuario = $_SESSION['user_id'];
        $stmt = $conexion->prepare("SELECT 1 FROM comunidades WHERE id_usuario = ? AND id_persona = ?");
        $stmt->bind_param("ii", $id_usuario, $id);
        $stmt->execute();
        $verificado = $stmt->get_result();
        $yaUnido = $verificado->num_rows > 0;
        $stmt->close();
    }

    echo json_encode([
        "success" => true,
        "persona" => $persona,
        "miembros" => $count,
        "ya_unido" => $yaUnido
    ]);
} else {
    echo json_encode(["success" => false, "error" => "Persona no encontrada"]);
}

$conexion->close();