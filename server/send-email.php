<?php
file_put_contents(__DIR__ . "/debug.log", "POST DATA: " . print_r($_POST, true) . "\n", FILE_APPEND);
file_put_contents(__DIR__ . "/lang-debug.log", "LANG POST: " . ($_POST['lang'] ?? 'no recibido') . "\n", FILE_APPEND);


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
    echo json_encode(['success' => false, 'message' => $messages[$lang]['missing_recaptcha'] ?? 'Falta el token de reCAPTCHA.']);
    exit;
}

file_put_contents(__DIR__ . "/debug.log", "reCAPTCHA Token: " . $recaptchaToken . "\n", FILE_APPEND);

$recaptchaUrl = 'https://www.google.com/recaptcha/api/siteverify';
$recaptchaResponse = file_get_contents($recaptchaUrl . '?secret=' . $secretKey . '&response=' . $recaptchaToken);
$recaptchaData = json_decode($recaptchaResponse, true);

file_put_contents(__DIR__ . "/debug.log", "reCAPTCHA Response: " . $recaptchaResponse . "\n", FILE_APPEND);
file_put_contents(__DIR__ . "/debug.log", "reCAPTCHA Data: " . print_r($recaptchaData, true) . "\n", FILE_APPEND);

if (!$recaptchaData['success'] || $recaptchaData['score'] < 0.5) {
    echo json_encode(['success' => false, 'message' => $messages[$lang]['invalid_recaptcha'] ?? 'No se pudo verificar el reCAPTCHA.']);
    exit;
}

try {

    // Validar dirección del remitente
    $fromEmail = $_ENV['SMTP_USER'];
    if (!filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
        throw new Exception($msg['from_email_invalid'] . $fromEmail);
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
        throw new Exception($msg['no_recipients']);
    }

    // Separar los correos ingresados
    $selectedRecipients = array_map('trim', explode(',', $_POST['recipients']));
    
    // Filtrar correos válidos
    $validRecipients = array_filter($selectedRecipients, function($email) use ($allowedRecipients) {
        return in_array($email, $allowedRecipients) && filter_var($email, FILTER_VALIDATE_EMAIL);
    });

    // Si no hay destinatarios válidos, detener
    if (empty($validRecipients)) {
        throw new Exception($msg['invalid_recipients']);
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

    $lang = $_POST['lang'] ?? 'es'; // español por defecto

    $messages = [
        'es' => [
            'from_email_invalid' => 'La dirección de correo del remitente no es válida: ',
            'missing_recaptcha' => 'Falta el token de reCAPTCHA.',
            'invalid_recaptcha' => 'No se pudo verificar el reCAPTCHA.',
            'no_recipients' => 'No se seleccionó ningún destinatario.',
            'invalid_recipients' => 'Ninguna dirección válida seleccionada.',
            'success' => 'El mensaje ha sido enviado con éxito.',
            'send_error' => 'Hubo un error al enviar el mensaje.',
        ],
        'en' => [
            'from_email_invalid' => 'The sender email address is not valid: ',
            'missing_recaptcha' => 'Missing reCAPTCHA token.',
            'invalid_recaptcha' => 'reCAPTCHA verification failed.',
            'no_recipients' => 'No recipient selected.',
            'invalid_recipients' => 'No valid recipient address selected.',
            'success' => 'Message sent successfully.',
            'send_error' => 'There was an error sending the message.',
        ],
        'pt' => [
            'from_email_invalid' => 'O endereço de e-mail do remetente não é válido: ',
            'missing_recaptcha' => 'Falta o token do reCAPTCHA.',
            'invalid_recaptcha' => 'Não foi possível verificar o reCAPTCHA.',
            'no_recipients' => 'Nenhum destinatário selecionado.',
            'invalid_recipients' => 'Nenhum endereço de destinatário válido selecionado.',
            'success' => 'A mensagem foi enviada com sucesso.',
            'send_error' => 'Houve um erro ao enviar a mensagem.',
        ]
    ];

    $msg = $messages[$lang] ?? $messages['es']; // fallback al español


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
    $mail->Subject = 'Latcom - Contacto desde newsletters';
    $titles = [
        'es' => 'Título campaña',
        'en' => 'Campaign title',
        'pt' => 'Título campanha'
    ];
    
    $title = $titles[$lang] ?? $titles['es'];
    $mail->Body = '
        <div style="font-family: Arial, sans-serif; background-color: #f7f7f7; padding: 30px;">
            <div style="max-width: 600px; margin: auto; background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
                <div style="text-align: center; margin-bottom: 20px; padding: 20px; background-color: #002646;">
                    <img src="https://latcom.com/landingform/assets/latcom.png" alt="Latcom" style="max-width: 200px;">
                </div>
                <h2 style="color: #333; text-align: center;">' . $title . '</h2>
                <hr style="margin: 20px 0; border: none; border-top: 1px solid #eee;">
                <p><strong>Nombre:</strong> ' . $name . '</p>
                <p><strong>Apellido:</strong> ' . $lastname . '</p>
                <p><strong>Teléfono:</strong> ' . $phone . '</p>
                <p><strong>Correo:</strong> ' . $email . '</p>
                <p><strong>Empresa:</strong> ' . $company . '</p>
                <p><strong>Cargo:</strong> ' . $position . '</p>
                <p><strong>País:</strong> ' . $country . '</p>
                <p><strong>Ciudad:</strong> ' . $city . '</p>
                <p><strong>Mensaje:</strong> ' . $message . '</p>
            </div>
        </div>';

    // Enviar el correo
    if ($mail->send()) {
        $response = ['success' => true, 'message' => $msg['success']];
    } else {
        throw new Exception('Hubo un error al enviar el mensaje.' . $mail->ErrorInfo);
    }
} catch (Exception $e) {
    $response = ['success' => false, 'message' => $msg['send_error'] . ' ' . $e->getMessage()];
}

// Guardar logs
error_log(print_r($_POST, true), 3, __DIR__ . "/errores.log");

// Enviar respuesta JSON
header('Content-Type: application/json');
echo json_encode($response);
exit;
