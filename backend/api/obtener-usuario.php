<?php
session_start();
header("Content-Type: application/json");
require_once '../db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "error" => "No has iniciado sesión."]);
    exit;
}

$id_usuario = $_SESSION['user_id'];

$stmt = $conexion->prepare("SELECT nombre, email, foto_perfil FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$resultado = $stmt->get_result();

if ($fila = $resultado->fetch_assoc()) {
    $nombre = $fila['nombre'];
    $email = $fila['email'];
    $foto = $fila['foto_perfil'] ?? '';

    // Usar ruta por defecto si no hay imagen
    $ruta = $foto
        ? "../public/uploads/" . $foto
        : "../public/img/perfil-defecto.png";

    echo json_encode([
        "success" => true,
        "nombre" => $nombre,
        "email" => $email,
        "foto" => $ruta
    ]);
} else {
    echo json_encode(["success" => false, "error" => "Usuario no encontrado."]);
}

$stmt->close();
$conexion->close();