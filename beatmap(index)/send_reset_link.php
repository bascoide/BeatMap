<?php
require_once __DIR__ . '/../beatmap/inc/mailer.php';

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "beatmap";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

function redirect_to_forgot($status, $is_artist = false) {
    $url = "forgot_password.html?status=" . urlencode($status);
    header("Location: " . $url);
    exit;
}

function ensure_artist_reset_columns($conn) {
    $required = [
        'reset_token' => "ALTER TABLE artists ADD COLUMN reset_token VARCHAR(255) DEFAULT NULL",
        'reset_expires' => "ALTER TABLE artists ADD COLUMN reset_expires DATETIME DEFAULT NULL",
    ];

    foreach ($required as $column => $alterSql) {
        $check = $conn->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'artists' AND COLUMN_NAME = ?");
        if (!$check) {
            return false;
        }

        $check->bind_param("s", $column);
        if (!$check->execute()) {
            $check->close();
            return false;
        }

        $check->bind_result($exists);
        $check->fetch();
        $check->close();

        if ((int)$exists === 0) {
            try {
                if (!$conn->query($alterSql)) {
                    return false;
                }
            } catch (mysqli_sql_exception $e) {
                if ((int)$e->getCode() !== 1060) {
                    return false;
                }
            }
        }
    }

    return true;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        redirect_to_forgot('not_found');
    }

    if (!ensure_artist_reset_columns($conn)) {
        redirect_to_forgot('mail_error');
    }

    $table = null;

    $stmtUser = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmtUser->bind_param("s", $email);
    $stmtUser->execute();
    $stmtUser->store_result();

    if ($stmtUser->num_rows > 0) {
        $table = 'users';
    }
    $stmtUser->close();

    if ($table === null) {
        $stmtArtist = $conn->prepare("SELECT id FROM artists WHERE email = ?");
        $stmtArtist->bind_param("s", $email);
        $stmtArtist->execute();
        $stmtArtist->store_result();

        if ($stmtArtist->num_rows > 0) {
            $table = 'artists';
        }
        $stmtArtist->close();
    }

    if ($table !== null) {
        // Gerar token único e data de expiração (1 hora)
        $token = bin2hex(random_bytes(50));
        $expires = date("Y-m-d H:i:s", strtotime('+1 hour'));

        // Guardar na base de dados
        $update = $conn->prepare("UPDATE {$table} SET reset_token = ?, reset_expires = ? WHERE email = ?");
        $update->bind_param("sss", $token, $expires, $email);
        
        if ($update->execute()) {
            // Configurar email
            $to = $email;
            $subject = "Recuperar Password - BeatMap";
            
            // Gerar link dinâmico (funciona em localhost e hospedagem)
            $host = $_SERVER['HTTP_HOST'];
            $path = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
            $link = "http://" . $host . $path . "/reset_password.php?token=" . $token;
            
            // Tentar enviar com PHPMailer (SMTP)
            if (sendPasswordResetEmail($to, $link)) {
                redirect_to_forgot('success');
            } else {
                redirect_to_forgot('mail_error');
            }
        }
        $update->close();
    } else {
        redirect_to_forgot('not_found');
    }
}
$conn->close();
?>