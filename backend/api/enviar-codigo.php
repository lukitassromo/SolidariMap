<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

require_once '../db.php';

// Asegúrate de que estas rutas sean correctas
require_once '../../PHPMailer/src/PHPMailer.php';
require_once '../../PHPMailer/src/SMTP.php';
require_once '../../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Obtener datos del frontend
$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';
$confirmPassword = $data['confirmPassword'] ?? '';

// Validaciones básicas
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || empty($password) || $password !== $confirmPassword) {
    echo json_encode(["success" => false, "error" => "Datos inválidos."]);
    exit;
}

// Generar código aleatorio de 6 dígitos
$codigo = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$creadoEn = date("Y-m-d H:i:s");
$expiraEn = date("Y-m-d H:i:s", strtotime("+5 minutes"));

// Guardar código en tabla temporal
$stmt = $conexion->prepare("INSERT INTO codigos_verificacion (email, codigo, creado_en, expiracion) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $email, $codigo, $creadoEn, $expiraEn);
$stmt->execute();
$stmt->close();

// Configurar PHPMailer
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.mailtrap.io';
    $mail->SMTPAuth = true;
    $mail->Username = '4fe94e90937fb2';  // Tu usuario de Mailtrap
    $mail->Password = '856c94212c3e4d';  // Tu contraseña de Mailtrap
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 2525;

    $mail->setFrom('no-reply@solidarimap.org', 'SolidariMap');
    $mail->addAddress($email);

    $mail->Subject = 'Código de verificación - SolidariMap';
    $mail->Body = "Tu código de verificación es: $codigo\nEste código expirará en 5 minutos.";

    $mail->send();
    echo json_encode(["success" => true]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => "No se pudo enviar el correo. Error: " . $mail->ErrorInfo]);
}
