<?php
// Permitir sessão em subpastas no mesmo host
$cookieDomain = $_SERVER['HTTP_HOST'] ?? '';
$cookieDomain = preg_replace('/:\\d+$/', '', $cookieDomain);
if ($cookieDomain === 'localhost' || $cookieDomain === '' || filter_var($cookieDomain, FILTER_VALIDATE_IP)) {
  session_set_cookie_params(0, '/');
} else {
  session_set_cookie_params(0, '/', '.' . $cookieDomain);
}
session_start();

// Configurações da conexão
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "beatmap";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

$moderationColCheck = $conn->query("SHOW COLUMNS FROM artists LIKE 'moderation_status'");
$hasModerationStatus = $moderationColCheck && $moderationColCheck->num_rows > 0;
$moderationColumnType = '';
if ($moderationColCheck instanceof mysqli_result) {
  $moderationColumn = $moderationColCheck->fetch_assoc();
  $moderationColumnType = strtolower((string)($moderationColumn['Type'] ?? ''));
}
if ($moderationColCheck instanceof mysqli_result) {
  $moderationColCheck->close();
}
if (!$hasModerationStatus) {
  $conn->query("ALTER TABLE artists ADD COLUMN moderation_status ENUM('pending','approved','rejected','banned') NOT NULL DEFAULT 'approved' AFTER is_confirmed");
} elseif ($moderationColumnType !== '' && strpos($moderationColumnType, "'banned'") === false) {
  $conn->query("ALTER TABLE artists MODIFY COLUMN moderation_status ENUM('pending','approved','rejected','banned') NOT NULL DEFAULT 'approved'");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $pass = $_POST['password'];

    if (empty($email) || empty($pass)) {
        header("Location: login.php?status=empty");
        exit;
    }

    // Tentar login como utilizador normal
    $stmt = $conn->prepare("SELECT id, username, password_hash FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $user_name, $hash);
        $stmt->fetch();
        $stmt->close();

        if (password_verify($pass, $hash)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $user_name;
            $_SESSION['user_email'] = $email;
          $_SESSION['user_type'] = 'user';
          unset($_SESSION['artist_id'], $_SESSION['artist_logged_in'], $_SESSION['artist_name'], $_SESSION['artist_email'], $_SESSION['artist_city']);
            
            // Criar token de autenticação para transferir para beatmap.map
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', time() + 300); // 5 minutos
            
            // Verificar se tabela auth_tokens existe
            $tableCheck = $conn->query("SHOW TABLES LIKE 'auth_tokens'");
            if ($tableCheck && $tableCheck->num_rows > 0) {
                $tokenStmt = $conn->prepare("INSERT INTO auth_tokens (token, user_id, username, expires_at) VALUES (?, ?, ?, ?)");
                $tokenStmt->bind_param("siss", $token, $id, $user_name, $expires);
                $tokenStmt->execute();
                $tokenStmt->close();
                
                header("Location: ../beatmap(mapa)/auth_redirect.php?token=" . $token);
            } else {
                // Se tabela não existe, redirecionar direto
                header("Location: ../beatmap(mapa)/index.html");
            }
            exit;
        } else {
            // Se a password do utilizador falhar, tentar conta de artista com o mesmo email
            $artistStmt = $conn->prepare("SELECT id, name, password_hash, is_confirmed, moderation_status FROM artists WHERE email = ?");
            $artistStmt->bind_param("s", $email);
            $artistStmt->execute();
            $artistStmt->store_result();

            if ($artistStmt->num_rows > 0) {
              $artistStmt->bind_result($artistId, $artistName, $artistHash, $artistConfirmed, $artistModerationStatus);
              $artistStmt->fetch();
              $artistStmt->close();

              if (!password_verify($pass, $artistHash)) {
                header("Location: login.php?status=wrong_password");
                exit;
              }

              if ((int)$artistConfirmed !== 1) {
                header("Location: login.php?status=artist_not_confirmed");
                exit;
              }

              $artistModerationStatus = strtolower(trim((string)($artistModerationStatus ?: 'approved')));
              if ($artistModerationStatus === 'rejected') {
                header("Location: login.php?status=artist_rejected");
                exit;
              }
              if ($artistModerationStatus === 'banned') {
                header("Location: login.php?status=artist_banned");
                exit;
              }

              unset($_SESSION['user_id'], $_SESSION['username']);
              unset($_SESSION['user_email']);
              $_SESSION['artist_id'] = $artistId;
              $_SESSION['artist_logged_in'] = true;
              $_SESSION['user_type'] = 'artist';
              $_SESSION['artist_name'] = $artistName;
              $_SESSION['artist_email'] = $email;
              $_SESSION['artist_moderation_status'] = (string)($artistModerationStatus ?: 'approved');

              header("Location: ../beatmap(mapa)/index.html");
              exit;
            }

            $artistStmt->close();
            header("Location: login.php?status=wrong_password");
            exit;
        }
    } else {
        $stmt->close();

        // Se não existir no users, tentar login como artista
        $artistStmt = $conn->prepare("SELECT id, name, password_hash, is_confirmed, moderation_status FROM artists WHERE email = ?");
        $artistStmt->bind_param("s", $email);
        $artistStmt->execute();
        $artistStmt->store_result();

        if ($artistStmt->num_rows > 0) {
          $artistStmt->bind_result($artistId, $artistName, $artistHash, $artistConfirmed, $artistModerationStatus);
          $artistStmt->fetch();
          $artistStmt->close();

          if (!password_verify($pass, $artistHash)) {
            header("Location: login.php?status=wrong_password");
            exit;
          }

          if ((int)$artistConfirmed !== 1) {
            header("Location: login.php?status=artist_not_confirmed");
            exit;
          }

          $artistModerationStatus = strtolower(trim((string)($artistModerationStatus ?: 'approved')));
          if ($artistModerationStatus === 'rejected') {
            header("Location: login.php?status=artist_rejected");
            exit;
          }
          if ($artistModerationStatus === 'banned') {
            header("Location: login.php?status=artist_banned");
            exit;
          }

          unset($_SESSION['user_id'], $_SESSION['username']);
          unset($_SESSION['user_email']);
          $_SESSION['artist_id'] = $artistId;
          $_SESSION['artist_logged_in'] = true;
          $_SESSION['user_type'] = 'artist';
          $_SESSION['artist_name'] = $artistName;
          $_SESSION['artist_email'] = $email;
          $_SESSION['artist_moderation_status'] = (string)($artistModerationStatus ?: 'approved');

          header("Location: ../beatmap(mapa)/index.html");
          exit;
        }

        $artistStmt->close();
        header("Location: login.php?status=not_found");
        exit;
    }
}
$conn->close();

// Se o utilizador já estiver logado, redireciona para o mapa
    if (isset($_SESSION['user_id']) || isset($_SESSION['artist_id'])) {
  header('Location: ../beatmap(mapa)/');
    exit;
}
?>
<!doctype html>
<html lang="pt-BR">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login | BeatMap</title>
    <link rel="icon" href="icone.png" />
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="style.css" />
    <style>
      .auth-container {
        height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background:
          linear-gradient(rgba(10, 10, 10, 0.7), rgba(10, 10, 10, 0.7)),
          url("fundo-do-site.gif");
        background-size: cover;
      }

      .auth-card {
        background: #1a1a1a;
        padding: 40px;
        border-radius: 20px;
        border: 1px solid #2a2a2a;
        max-width: 400px;
        width: 90%;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
      }

      .auth-card h2 {
        font-size: 28px;
        margin-bottom: 30px;
        text-align: center;
      }

      .auth-card h2 span {
        color: var(--primary);
      }

      .form-group {
        margin-bottom: 20px;
      }

      .form-group label {
        display: block;
        margin-bottom: 8px;
        font-size: 14px;
        color: var(--text-muted);
      }

      .form-group input {
        width: 100%;
        padding: 12px 15px;
        background: #0a0a0a;
        border: 1px solid #333;
        border-radius: 8px;
        color: white;
        outline: none;
        transition: var(--transition);
      }

      .form-group input:focus {
        border-color: var(--primary);
      }

      .btn-submit {
        width: 100%;
        padding: 15px;
        background: var(--primary);
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 700;
        cursor: pointer;
        transition: var(--transition);
      }

      .btn-submit:hover {
        transform: translateY(-3px);
        filter: brightness(1.2);
      }

      .auth-footer {
        margin-top: 25px;
        text-align: center;
        font-size: 14px;
        color: var(--text-muted);
      }

      .auth-footer a {
        color: var(--primary);
        text-decoration: none;
        font-weight: 600;
      }

      .forgot-password {
        display: block;
        text-align: right;
        font-size: 12px;
        margin-top: 5px;
        color: var(--text-muted);
        text-decoration: none;
      }

      /* Estilos para as mensagens de alerta */
      .alert {
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 14px;
        text-align: center;
        display: none;
      }
      .alert-success {
        background: rgba(115, 49, 223, 0.2);
        border: 1px solid var(--primary);
        color: #fff;
      }
      .alert-error {
        background: rgba(255, 50, 50, 0.2);
        border: 1px solid #ff3232;
        color: #ffcccc;
      }
      .alert-warning {
        background: rgba(255, 165, 0, 0.2);
        border: 1px solid orange;
        color: #fff;
      }

      .back-link {
        display: block;
        text-align: center;
        margin-top: 20px;
        color: var(--text-muted);
        text-decoration: none;
        font-size: 14px;
      }
      .back-link:hover {
        color: white;
      }
    </style>
  </head>
  <body>
    <main class="auth-container">
      <div class="auth-card">
        <div class="logo" style="text-align: center; margin-bottom: 20px">
          <a href="index.html" style="text-decoration: none; color: inherit"
            >BEAT<span>MAP</span></a
          >
        </div>
        <h2>Entrar no <span>Palco.</span></h2>

        <!-- Local onde a mensagem vai aparecer -->
        <div id="message-box"></div>

        <form action="login.php" method="POST">
          <div class="form-group">
            <label>E-mail</label>
            <input
              type="email"
              name="email"
              placeholder="exemplo@email.com"
              required
            />
          </div>
          <div class="form-group">
            <label>Palavra-passe</label>
            <input
              type="password"
              name="password"
              placeholder="••••••••"
              required
            />
            <a href="forgot_password.html" class="forgot-password"
              >Esqueceste-te da palavra-passe?</a
            >
          </div>
          <button type="submit" class="btn-submit">Entrar</button>
        </form>

        <div class="auth-footer">
          Ainda não és membro? <a href="registo.php">Cria conta</a>
        </div>

        <a href="index.html" class="back-link">← Voltar ao Início</a>
      </div>
    </main>

    <script src="script.js"></script>
    <script>
      const urlParams = new URLSearchParams(window.location.search);
      const status = urlParams.get("status");
      const msgBox = document.getElementById("message-box");

      if (status === "wrong_password") {
        msgBox.innerHTML =
          '<div class="alert alert-error">Palavra-passe incorreta.</div>';
        msgBox.querySelector(".alert").style.display = "block";
      } else if (status === "not_found") {
        msgBox.innerHTML =
          '<div class="alert alert-error">Conta não encontrada.</div>';
        msgBox.querySelector(".alert").style.display = "block";
      } else if (status === "empty") {
        msgBox.innerHTML =
          '<div class="alert alert-warning">Preencha todos os campos.</div>';
        msgBox.querySelector(".alert").style.display = "block";
      } else if (status === "reset_success") {
        msgBox.innerHTML =
          '<div class="alert alert-success">Password alterada com sucesso! Pode fazer login.</div>';
        msgBox.querySelector(".alert").style.display = "block";
      } else if (status === "artist_not_confirmed") {
        msgBox.innerHTML =
          '<div class="alert alert-warning">Conta de artista não confirmada. Confirma o email e tenta novamente.</div>';
        msgBox.querySelector(".alert").style.display = "block";
      } else if (status === "artist_rejected") {
        msgBox.innerHTML =
          '<div class="alert alert-warning">A conta de artista foi recusada e precisa de nova revisão do admin.</div>';
        msgBox.querySelector(".alert").style.display = "block";
      } else if (status === "artist_banned") {
        msgBox.innerHTML =
          '<div class="alert alert-error">A conta de artista foi banida. Contacta o administrador.</div>';
        msgBox.querySelector(".alert").style.display = "block";
      }
    </script>
  </body>
</html>