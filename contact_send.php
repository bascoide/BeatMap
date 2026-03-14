<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');
ob_start();

header('Content-Type: application/json; charset=UTF-8');

set_exception_handler(static function (Throwable $e): void {
    if (ob_get_length()) {
        ob_clean();
    }
    error_log('Erro no formulario de contacto (exception): ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Nao foi possivel enviar a mensagem. Tenta novamente.']);
    exit;
});

register_shutdown_function(static function (): void {
    $lastError = error_get_last();
    if ($lastError !== null && in_array($lastError['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        if (ob_get_length()) {
            ob_clean();
        }
        error_log('Erro fatal no formulario de contacto: ' . ($lastError['message'] ?? 'desconhecido'));
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=UTF-8');
            http_response_code(500);
        }
        echo json_encode(['success' => false, 'message' => 'Nao foi possivel enviar a mensagem. Tenta novamente.']);
    }
});

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metodo nao permitido.']);
    exit;
}

$payload = $_POST;
if (empty($payload)) {
    $rawBody = file_get_contents('php://input');
    if (is_string($rawBody) && $rawBody !== '') {
        $decoded = json_decode($rawBody, true);
        if (is_array($decoded)) {
            $payload = $decoded;
        }
    }
}

$name = trim((string)($payload['name'] ?? ''));
$email = trim((string)($payload['email'] ?? ''));
$phone = trim((string)($payload['phone'] ?? ''));
$subject = trim((string)($payload['subject'] ?? ''));
$message = trim((string)($payload['message'] ?? ''));

if ($name === '' || $email === '' || $subject === '' || $message === '') {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Preenche todos os campos obrigatorios.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Email invalido.']);
    exit;
}

$maxMessageLength = 4000;
$messageLength = function_exists('mb_strlen') ? mb_strlen($message) : strlen($message);
if ($messageLength > $maxMessageLength) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Mensagem demasiado longa.']);
    exit;
}

$phpMailerCandidates = [
    __DIR__ . '/beatmap/inc/PHPMailer/src',
    __DIR__ . '/../beatmap/inc/PHPMailer/src',
    dirname(__DIR__) . '/beatmap/inc/PHPMailer/src',
];

$phpMailerDir = null;
foreach ($phpMailerCandidates as $candidate) {
    if (is_dir($candidate)) {
        $phpMailerDir = $candidate;
        break;
    }
}

if ($phpMailerDir === null) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Servico de email indisponivel.']);
    exit;
}

require_once $phpMailerDir . '/Exception.php';
require_once $phpMailerDir . '/PHPMailer.php';
require_once $phpMailerDir . '/SMTP.php';

$mail = new PHPMailer\PHPMailer\PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'sandbox.smtp.mailtrap.io';
    $mail->SMTPAuth = true;
    $mail->Username = '754da2f3dd3988';
    $mail->Password = '1924fa81f2eab0';
    $mail->Port = 2525;
    $mail->SMTPSecure = false;
    $mail->SMTPAutoTLS = false;

    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';

    $mail->setFrom('no-reply@beatmap.com', 'BeatMap Contacto');
    $mail->addAddress('no-reply@beatmap.com', 'BeatMap');
    $mail->addReplyTo($email, $name);

    $cleanSubject = preg_replace('/[\r\n]+/', ' ', $subject);
    $mail->isHTML(true);
    $mail->Subject = 'Formulario de Contacto: ' . $cleanSubject;

    $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $safeEmail = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
    $safePhone = htmlspecialchars($phone !== '' ? $phone : 'Nao informado', ENT_QUOTES, 'UTF-8');
    $safeSubject = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');
    $safeMessage = nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));
    $submittedAt = date('d/m/Y H:i');

    $mail->Body = "
        <!DOCTYPE html>
        <html lang='pt'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Novo contacto - BeatMap</title>
            <style>
                body {
                    margin: 0;
                    padding: 0;
                    background: #0b0b0f;
                    color: #f3f4f6;
                    font-family: Inter, Segoe UI, Arial, sans-serif;
                }
                .email-shell {
                    width: 100%;
                    padding: 32px 16px;
                    background: radial-gradient(circle at top right, rgba(115, 49, 223, 0.25), transparent 45%), #0b0b0f;
                }
                .email-card {
                    max-width: 640px;
                    margin: 0 auto;
                    background: #14141a;
                    border: 1px solid #2a2b33;
                    border-radius: 16px;
                    overflow: hidden;
                }
                .top-line {
                    height: 6px;
                    background: linear-gradient(90deg, #7331df, #ff8a2b);
                }
                .header {
                    padding: 26px 24px 18px;
                    border-bottom: 1px solid #23242b;
                }
                .logo {
                    margin: 0;
                    font-size: 27px;
                    font-weight: 900;
                    letter-spacing: -0.8px;
                }
                .logo span {
                    color: #7331df;
                }
                .title {
                    margin: 12px 0 4px;
                    font-size: 22px;
                    line-height: 1.2;
                }
                .subtitle {
                    margin: 0;
                    color: #a4a7b5;
                    font-size: 14px;
                }
                .content {
                    padding: 20px 24px 24px;
                }
                .meta-grid {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 14px;
                }
                .meta-grid td {
                    width: 50%;
                    vertical-align: top;
                    padding: 8px 8px 8px 0;
                }
                .meta-label {
                    display: block;
                    color: #8f93a3;
                    font-size: 12px;
                    margin-bottom: 4px;
                    text-transform: uppercase;
                    letter-spacing: 0.6px;
                }
                .meta-value {
                    display: block;
                    color: #ffffff;
                    font-size: 15px;
                    line-height: 1.4;
                }
                .message-box {
                    margin-top: 10px;
                    background: #0f1015;
                    border: 1px solid #2a2b33;
                    border-radius: 12px;
                    padding: 14px;
                }
                .message-title {
                    margin: 0 0 8px;
                    color: #cfd2de;
                    font-size: 13px;
                    font-weight: 700;
                    text-transform: uppercase;
                    letter-spacing: 0.6px;
                }
                .message-body {
                    margin: 0;
                    color: #f0f1f5;
                    font-size: 15px;
                    line-height: 1.65;
                    word-break: break-word;
                }
                .footer {
                    padding: 14px 24px 22px;
                    border-top: 1px solid #23242b;
                    color: #8f93a3;
                    font-size: 12px;
                    line-height: 1.5;
                }
            </style>
        </head>
        <body>
            <div class='email-shell'>
                <div class='email-card'>
                    <div class='top-line'></div>
                    <div class='header'>
                        <p class='logo'>Beat<span>Map</span></p>
                        <h1 class='title'>Novo contacto recebido</h1>
                        <p class='subtitle'>Formulario enviado em {$submittedAt}</p>
                    </div>
                    <div class='content'>
                        <table class='meta-grid' role='presentation'>
                            <tr>
                                <td>
                                    <span class='meta-label'>Nome</span>
                                    <span class='meta-value'>{$safeName}</span>
                                </td>
                                <td>
                                    <span class='meta-label'>Email</span>
                                    <span class='meta-value'>{$safeEmail}</span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <span class='meta-label'>Telefone</span>
                                    <span class='meta-value'>{$safePhone}</span>
                                </td>
                                <td>
                                    <span class='meta-label'>Assunto</span>
                                    <span class='meta-value'>{$safeSubject}</span>
                                </td>
                            </tr>
                        </table>

                        <div class='message-box'>
                            <p class='message-title'>Mensagem</p>
                            <p class='message-body'>{$safeMessage}</p>
                        </div>
                    </div>
                    <div class='footer'>
                        Este email foi enviado automaticamente pelo formulario de contacto do BeatMap.<br>
                        Usa o botao &quot;Responder&quot; para responder diretamente ao remetente.
                    </div>
                </div>
            </div>
        </body>
        </html>
    ";

    $mail->AltBody = "BEATMAP - NOVO CONTACTO\n"
        . "Enviado em: {$submittedAt}\n\n"
        . "Nome: {$name}\n"
        . "Email: {$email}\n"
        . "Telefone: " . ($phone !== '' ? $phone : 'Nao informado') . "\n"
        . "Assunto: {$subject}\n\n"
        . "Mensagem:\n{$message}\n";

    $mail->send();

    echo json_encode(['success' => true, 'message' => 'Mensagem enviada com sucesso.']);
} catch (Throwable $e) {
    if (ob_get_length()) {
        ob_clean();
    }
    error_log('Erro no formulario de contacto: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Nao foi possivel enviar a mensagem. Tenta novamente.']);
}

if (ob_get_length()) {
    ob_end_flush();
}
