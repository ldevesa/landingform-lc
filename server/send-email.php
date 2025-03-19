<?php
file_put_contents(__DIR__ . "/debug.log", "POST DATA: " . print_r($_POST, true) . "\n", FILE_APPEND);


// Habilitar reporte de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir PHPMailer y Dotenv
require __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

// Cargar variables de entorno
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// ✅ Validar reCAPTCHA antes de procesar los datos
$secretKey = $_ENV['SECRET_RECAPTCHA_SITE_KEY'];
$recaptchaToken = $_POST['recaptcha-token'] ?? '';

if (!$recaptchaToken) {
    echo json_encode(['success' => false, 'message' => 'Falta el token de reCAPTCHA.']);
    exit;
}

file_put_contents(__DIR__ . "/debug.log", "reCAPTCHA Token: " . $recaptchaToken . "\n", FILE_APPEND);

$recaptchaUrl = 'https://www.google.com/recaptcha/api/siteverify';
$recaptchaResponse = file_get_contents($recaptchaUrl . '?secret=' . $secretKey . '&response=' . $recaptchaToken);
$recaptchaData = json_decode($recaptchaResponse, true);

file_put_contents(__DIR__ . "/debug.log", "reCAPTCHA Response: " . $recaptchaResponse . "\n", FILE_APPEND);
file_put_contents(__DIR__ . "/debug.log", "reCAPTCHA Data: " . print_r($recaptchaData, true) . "\n", FILE_APPEND);

if (!$recaptchaData['success'] || $recaptchaData['score'] < 0.5) {
    echo json_encode(['success' => false, 'message' => 'No se pudo verificar el reCAPTCHA.']);
    exit;
}

try {

    // Validar dirección del remitente
    $fromEmail = $_ENV['SMTP_USER'];
    if (!filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('La dirección de correo del remitente no es válida: ' . $fromEmail);
    }

    // Lista blanca de destinatarios válidos
    $allowedRecipients = [
        "ldevesa@latcom.com",
        "comercial@latcom.com",
        "comercial@worldcomooh.com",
        "esobrino@latcom.com",
        "kvazquez@latcom.com",
        "vmuscatello@latcom.com",
        "vpbueno@latcom.com",
        "marketing@latcom.com",
        "vbueno@latcom.com",
        "rrhh@latcom.com",
        "mbagatini@latcom.com"
    ];

    // Asegurar que el campo POST está presente
    if (!isset($_POST['recipients']) || empty(trim($_POST['recipients']))) {
        throw new Exception('No se seleccionó ningún destinatario.');
    }

    // Separar los correos ingresados
    $selectedRecipients = array_map('trim', explode(',', $_POST['recipients']));
    
    // Filtrar correos válidos
    $validRecipients = array_filter($selectedRecipients, function($email) use ($allowedRecipients) {
        return in_array($email, $allowedRecipients) && filter_var($email, FILTER_VALIDATE_EMAIL);
    });

    // Si no hay destinatarios válidos, detener
    if (empty($validRecipients)) {
        throw new Exception('Ninguna dirección válida seleccionada.');
    }

    // Obtener y sanitizar datos del formulario
    $name = htmlspecialchars($_POST['name'] ?? '');
    $lastname = htmlspecialchars($_POST['lastname'] ?? '');
    $phone = htmlspecialchars($_POST['phone'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $company = htmlspecialchars($_POST['company'] ?? '');
    $position = htmlspecialchars($_POST['position'] ?? '');
    $country = htmlspecialchars($_POST['country'] ?? '');
    $city = htmlspecialchars($_POST['city'] ?? '');
    $message = htmlspecialchars($_POST['message'] ?? '');

    // Crear instancia de PHPMailer
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = $_ENV['SMTP_HOST'];
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['SMTP_USER'];
    $mail->Password = $_ENV['SMTP_PASS'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = $_ENV['SMTP_PORT'];

    // Remitente
    $mail->setFrom($_ENV['SMTP_USER'], 'Latcom');

    // Agregar múltiples destinatarios
    foreach ($validRecipients as $recipient) {
        $mail->addAddress($recipient);
    }

    // Contenido del correo
    $mail->isHTML(true);
    $mail->Subject = 'Latcom - formulario de contacto de newsletters';
    $mail->Body = "Nombre: $name <br> Apellido: $lastname <br> Teléfono: $phone <br> Correo: $email <br> 
                   Empresa: $company <br> Cargo: $position <br> País: $country <br> Ciudad: $city <br> 
                   Mensaje: $message";

    // Enviar el correo
    if ($mail->send()) {
        $response = ['success' => true, 'message' => 'El mensaje ha sido enviado con éxito.'];
    } else {
        throw new Exception('Hubo un error al enviar el mensaje.' . $mail->ErrorInfo);
    }
} catch (Exception $e) {
    $response = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
}

// Guardar logs
error_log(print_r($_POST, true), 3, __DIR__ . "/errores.log");

// Enviar respuesta JSON
header('Content-Type: application/json');
echo json_encode($response);
exit;
