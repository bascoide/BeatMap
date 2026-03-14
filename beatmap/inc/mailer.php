<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Download manual do PHPMailer (sem Composer)
require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

function sendConfirmationEmail($to, $name, $confirm_url) {
    $mail = new PHPMailer(true);
    
    try {
        // Configurações Mailtrap (Mantidas as originais)
        $mail->isSMTP();
        $mail->Host = 'sandbox.smtp.mailtrap.io';
        $mail->SMTPAuth = true;
        $mail->Username = '754da2f3dd3988';
        $mail->Password = '1924fa81f2eab0';
        $mail->Port = 2525;
        $mail->SMTPSecure = false;
        $mail->SMTPAutoTLS = false;
        
        // ⭐⭐ CONFIGURAÇÃO UTF-8 ⭐⭐
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';
        
        $mail->setFrom('no-reply@beatmap.com', 'BeatMap');
        $mail->addAddress($to, $name);
        
        $mail->isHTML(true);
        $mail->Subject = '🎵 Confirme o seu email - BeatMap';
        
        $mail->Body = "
        <!DOCTYPE html>
        <html lang='pt'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Confirmação BeatMap</title>
            <style>
                /* Import da fonte Inter (fallback para sans-serif) */
                @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;900&display=swap');

                body {
                    font-family: 'Inter', Arial, sans-serif;
                    background-color: #0a0a0a; /* --bg-dark */
                    margin: 0;
                    padding: 0;
                    color: #ffffff; /* --text-light */
                    line-height: 1.6;
                }
                .email-wrapper {
                    width: 100%;
                    background-color: #0a0a0a;
                    padding: 40px 0;
                }
                .email-container {
                    max-width: 600px;
                    margin: 0 auto;
                    background: #1a1a1a; /* --bg-card */
                    border-radius: 10px;
                    overflow: hidden;
                    border: 1px solid #2a2a2a;
                    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
                }
                /* Topo decorativo simulando o visualizador de música */
                .visualizer-strip {
                    height: 6px;
                    width: 100%;
                    background: repeating-linear-gradient(
                        90deg,
                        #ff6b35,
                        #ff6b35 10px,
                        #1a1a1a 10px,
                        #1a1a1a 20px
                    );
                }
                .email-header {
                    padding: 40px 30px 20px 30px;
                    text-align: center;
                    border-bottom: 1px solid #2a2a2a;
                }
                .logo {
                    font-size: 28px;
                    font-weight: 900;
                    letter-spacing: -1px;
                    color: #ffffff;
                    text-decoration: none;
                    margin: 0;
                }
                .logo span {
                    color: #ff6b35; /* --primary */
                }
                .email-content {
                    padding: 40px 30px;
                    text-align: center;
                }
                .welcome-title {
                    font-size: 24px;
                    font-weight: 700;
                    margin-bottom: 10px;
                    color: #ffffff;
                }
                .text-muted {
                    color: #a0a0a0; /* --text-muted */
                    font-size: 16px;
                    margin-bottom: 30px;
                }
                /* Botão estilo .btn-primary do CSS */
                .confirm-button {
                    display: inline-block;
                    padding: 18px 35px;
                    background-color: #ff6b35; /* --primary */
                    color: #ffffff;
                    text-decoration: none;
                    border-radius: 4px; /* Matches style.css btn radius */
                    font-weight: 700;
                    font-size: 16px;
                    margin: 20px 0;
                    transition: all 0.3s;
                }
                /* Estilo do input field para o link raw */
                .link-box {
                    background: #0a0a0a;
                    padding: 15px;
                    border: 1px solid #333;
                    border-radius: 4px;
                    margin-top: 30px;
                    word-break: break-all;
                    color: #a0a0a0;
                    font-size: 13px;
                    text-align: left;
                }
                .email-footer {
                    background: #0a0a0a;
                    padding: 30px;
                    text-align: center;
                    color: #555;
                    font-size: 12px;
                    border-top: 1px solid #2a2a2a;
                }
                .tagline {
                    color: #ff6b35;
                    font-weight: 700;
                    font-size: 12px;
                    letter-spacing: 2px;
                    text-transform: uppercase;
                    margin-bottom: 10px;
                    display: block;
                }
            </style>
        </head>
        <body>
            <div class='email-wrapper'>
                <div class='email-container'>
                    <div class='visualizer-strip'></div>
                    
                    <div class='email-header'>
                        <div class='logo'>Beat<span>Map</span></div>
                    </div>
                    
                    <div class='email-content'>
                        <h1 class='welcome-title'>Olá, " . htmlspecialchars($name) . "!</h1>
                        
                        <p class='text-muted'>
                            Onde o som ganha visibilidade. Obrigado por se juntar à comunidade BeatMap. Estamos prontos para amplificar a sua voz.
                        </p>
                        
                        <a href='" . htmlspecialchars($confirm_url) . "' class='confirm-button'>
                            Confirmar Conta
                        </a>
                        
                        <div class='text-muted' style='font-size: 14px; margin-top: 20px;'>
                            Ou copie este link para o navegador:
                        </div>
                        
                        <div class='link-box'>
                            " . htmlspecialchars($confirm_url) . "
                        </div>
                    </div>
                    
                    <div class='email-footer'>
                        <span class='tagline'>BeatMap Artists</span>
                        <p>Se não criou uma conta, pode ignorar este email com segurança.</p>
                        <p>&copy; " . date('Y') . " BeatMap. Todos os direitos reservados.</p>
                    </div>
                </div>
            </div>
        </body>
        </html>
        ";
        
        // Versão Texto Simples
        $mail->AltBody = "BEATMAP\n\n" .
                        "Olá " . $name . "!\n\n" .
                        "Onde o som ganha visibilidade. Obrigado por se registar.\n\n" .
                        "Confirme a sua conta clicando no link:\n" .
                        $confirm_url . "\n\n" .
                        "Se não se registou, ignore este email.\n\n" .
                        "BeatMap Artists";
        
        $sent = $mail->send();
        
        // Log se foi enviado com sucesso (inclui o link para debugging)
        if ($sent) {
            $logPath = __DIR__ . '/confirmation_log.txt';
            $logLine = date('Y-m-d H:i:s') . " - Email enviado com SUCESSO para: $to ($name) - Link: $confirm_url\n";
            @file_put_contents($logPath, $logLine, FILE_APPEND | LOCK_EX);
        }
        
        return $sent;
        
    } catch (Exception $e) {
        // Log do erro
        $logPath = __DIR__ . '/confirmation_log.txt';
        $logLine = date('Y-m-d H:i:s') . " - ERRO Email: " . $e->getMessage() . "\n";
        @file_put_contents($logPath, $logLine, FILE_APPEND | LOCK_EX);
        
        error_log("Erro ao enviar email: " . $e->getMessage());
        return false;
    }
}

function sendPasswordResetEmail($to, $reset_url) {
    $mail = new PHPMailer(true);

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

        $mail->setFrom('no-reply@beatmap.com', 'BeatMap');
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = 'Redefinição de Palavra-passe - BeatMap';

        $mail->Body = "
        <!DOCTYPE html>
        <html lang='pt'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Redefinição de Password</title>
        </head>
        <body style='margin:0; padding:20px; background:#0a0a0a; color:#ffffff; font-family:Inter,Arial,sans-serif;'>
            <table align='center' cellpadding='0' cellspacing='0' width='100%' style='max-width:600px;'>
                <tr>
                    <td align='center' style='padding:20px 0; font-size:28px; font-weight:900;'>BEAT<span style='color:#7331df;'>MAP</span></td>
                </tr>
                <tr>
                    <td style='background:#1a1a1a; border:1px solid #2a2a2a; border-radius:15px; padding:30px;'>
                        <h1 style='margin:0 0 16px 0; font-size:22px; color:#fff;'>Redefinição de Palavra-passe</h1>
                        <p style='margin:0 0 20px 0; color:#a0a0a0;'>Recebemos um pedido para alterar a sua palavra-passe.</p>
                        <p style='margin:0 0 24px 0; color:#a0a0a0;'>Se não foi você, pode ignorar este email com segurança.</p>
                        <div style='text-align:center;'>
                            <a href='" . htmlspecialchars($reset_url, ENT_QUOTES, 'UTF-8') . "' style='display:inline-block; background:#7331df; color:#fff; text-decoration:none; padding:14px 22px; border-radius:8px; font-weight:700;'>Alterar Password</a>
                        </div>
                        <p style='margin:24px 0 0 0; color:#a0a0a0; font-size:14px; text-align:center;'>Este link expira em 1 hora.</p>
                    </td>
                </tr>
            </table>
        </body>
        </html>";

        $mail->AltBody = "Redefinição de Palavra-passe - BeatMap\n\n" .
                         "Use o link para alterar a sua password:\n" .
                         $reset_url . "\n\n" .
                         "Este link expira em 1 hora.";

        return $mail->send();
    } catch (Exception $e) {
        $logPath = __DIR__ . '/confirmation_log.txt';
        $logLine = date('Y-m-d H:i:s') . " - ERRO Reset Email: " . $e->getMessage() . "\n";
        @file_put_contents($logPath, $logLine, FILE_APPEND | LOCK_EX);
        error_log("Erro ao enviar email de reset: " . $e->getMessage());
        return false;
    }
}
?>