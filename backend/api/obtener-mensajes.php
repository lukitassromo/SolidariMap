<?php
session_start();
header("Content-Type: application/json");
require_once '../db.php';

// Validar parámetro
$id_persona = $_GET['id_persona'] ?? null;

if (!$id_persona) {
    echo json_encode(["success" => false, "error" => "ID de persona no especificado."]);
    exit;
}

// Consulta SQL con nombre y foto de perfil del usuario
$sql = "SELECT m.texto, m.enviado_en, u.nombre, u.foto
        FROM mensajes m
        JOIN usuarios u ON m.id_usuario = u.id
        WHERE m.id_persona = ?
        ORDER BY m.enviado_en ASC";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_persona);
$stmt->execute();
$res = $stmt->get_result();

$mensajes = [];
while ($fila = $res->fetch_assoc()) {
    $mensajes[] = [
        "texto" => $fila["texto"],
        "enviado_en" => $fila["enviado_en"],
        "nombre" => $fila["nombre"],
        "foto" => $fila["foto"] ?: "../public/img/perfil-defecto.png"
    ];
}

echo json_encode(["success" => true, "mensajes" => $mensajes]);

$stmt->close();
$conexion->close();