<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "beatmap";

$conn = new mysqli($servername, $username, $password, $dbname);

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

// Verificar token na URL
$token = $_GET['token'] ?? '';
$msg = '';
$valid_token = false;

if (!ensure_artist_reset_columns($conn)) {
    $msg = "Ocorreu um erro ao preparar a recuperação para artistas.";
}

if ($token) {
    $current_time = date("Y-m-d H:i:s");
    $stmtUser = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expires > ?");
    $stmtUser->bind_param("ss", $token, $current_time);
    $stmtUser->execute();
    $stmtUser->store_result();
    
    if ($stmtUser->num_rows > 0) {
        $valid_token = true;
    } else {
        $stmtArtist = $conn->prepare("SELECT id FROM artists WHERE reset_token = ? AND reset_expires > ?");
        $stmtArtist->bind_param("ss", $token, $current_time);
        $stmtArtist->execute();
        $stmtArtist->store_result();

        if ($stmtArtist->num_rows > 0) {
            $valid_token = true;
        } else {
            $msg = "Este link é inválido ou já expirou.";
        }
        $stmtArtist->close();
    }
    $stmtUser->close();
}

// Processar nova password
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_password'])) {
    $pass = $_POST['new_password'];
    $token_post = $_POST['token'];

    if (strlen($pass) < 6) {
        header("Location: reset_password.php?token=" . urlencode($token_post) . "&status=password_length");
        exit;
    }

    if (!ensure_artist_reset_columns($conn)) {
        header("Location: reset_password.php?token=" . urlencode($token_post) . "&status=error");
        exit;
    }

    $password_hash = password_hash($pass, PASSWORD_DEFAULT);
    $current_time = date("Y-m-d H:i:s");

    $updateUser = $conn->prepare("UPDATE users SET password_hash = ?, reset_token = NULL, reset_expires = NULL WHERE reset_token = ? AND reset_expires > ?");
    $updateUser->bind_param("sss", $password_hash, $token_post, $current_time);
    if ($updateUser->execute() && $updateUser->affected_rows > 0) {
        $updateUser->close();
        header("Location: login.php?status=reset_success");
        exit;
    }
    $updateUser->close();

    $updateArtist = $conn->prepare("UPDATE artists SET password_hash = ?, reset_token = NULL, reset_expires = NULL WHERE reset_token = ? AND reset_expires > ?");
    $updateArtist->bind_param("sss", $password_hash, $token_post, $current_time);
    if ($updateArtist->execute() && $updateArtist->affected_rows > 0) {
        $updateArtist->close();
        header("Location: ../beatmap(index)/login.php?status=reset_success");
        exit;
    }
    $updateArtist->close();

    header("Location: reset_password.php?token=" . urlencode($token_post) . "&status=invalid_token");
    exit;
}
?>

<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Password | BeatMap</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: linear-gradient(rgba(10, 10, 10, 0.7), rgba(10, 10, 10, 0.7)), url("fundo-do-site.gif");
            background-size: cover;
            height: 100vh; display: flex; align-items: center; justify-content: center;
        }
        .auth-card {
            background: #1a1a1a; padding: 40px; border-radius: 20px; border: 1px solid #2a2a2a;
            max-width: 400px; width: 90%; box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5); text-align: center;
        }
        h2 { margin-bottom: 20px; }
        h2 span { color: var(--primary); }
        input {
            width: 100%; padding: 12px; margin-bottom: 20px; background: #0a0a0a;
            border: 1px solid #333; border-radius: 8px; color: white;
        }
        .btn-submit {
            width: 100%; padding: 15px; background: var(--primary); color: white;
            border: none; border-radius: 8px; font-weight: 700; cursor: pointer;
        }
        .error-msg { color: #ffcccc; margin-bottom: 20px; }

        /* Estilos para as mensagens de alerta */
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
            display: none;
        }
        .alert-error {
            background: rgba(255, 50, 50, 0.2);
            border: 1px solid #ff3232;
            color: #ffcccc;
        }
    </style>
</head>
<body>
    <div class="auth-card">
        <div class="logo" style="margin-bottom: 20px">BEAT<span>MAP</span></div>
        
        <div id="message-box"></div>

        <?php if ($valid_token): ?>
            <h2>Nova <span>Password.</span></h2>
            <form action="reset_password.php" method="POST">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                <input type="password" name="new_password" placeholder="Nova palavra-passe (min. 6 chars)" required>
                <button type="submit" class="btn-submit">Alterar Password</button>
            </form>
        <?php else: ?>
            <div class="error-msg"><?php echo $msg ?: "Token não fornecido."; ?></div>
            <a href="forgot_password.html" style="color: var(--primary);">Tentar novamente</a>
        <?php endif; ?>
    </div>

    <script src="script.js"></script>
    <script>
        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get("status");
        const msgBox = document.getElementById("message-box");

        if (status === "password_length") {
            msgBox.innerHTML = '<div class="alert alert-error">A password deve ter pelo menos 6 caracteres.</div>';
            msgBox.querySelector(".alert").style.display = "block";
        } else if (status === "invalid_token") {
            msgBox.innerHTML = '<div class="alert alert-error">Este link é inválido ou já expirou.</div>';
            msgBox.querySelector(".alert").style.display = "block";
        } else if (status === "error") {
            msgBox.innerHTML = '<div class="alert alert-error">Ocorreu um erro ao alterar a password. Tenta novamente.</div>';
            msgBox.querySelector(".alert").style.display = "block";
        }
    </script>
</body>
</html>