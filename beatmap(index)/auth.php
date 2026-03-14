<?php
session_set_cookie_params(0, '/', '.beatmap');
session_start();

// Se o utilizador já estiver logado, redireciona para o mapa
if (isset($_SESSION['user_id'])) {
    $servername = "localhost";
    $username_db = "root";
    $password_db = "";
    $dbname = "beatmap";
    
    $conn = new mysqli($servername, $username_db, $password_db, $dbname);
    
    if (!$conn->connect_error) {
        $tableCheck = $conn->query("SHOW TABLES LIKE 'auth_tokens'");
        if ($tableCheck && $tableCheck->num_rows > 0) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', time() + 300);
            $user_id = $_SESSION['user_id'];
            $user_name = $_SESSION['username'] ?? 'User';
            
            $tokenStmt = $conn->prepare("INSERT INTO auth_tokens (token, user_id, username, expires_at) VALUES (?, ?, ?, ?)");
            $tokenStmt->bind_param("siss", $token, $user_id, $user_name, $expires);
            $tokenStmt->execute();
            $tokenStmt->close();
            $conn->close();
            
            header("Location: http://beatmap.map/beatmap(mapa)/auth_redirect.php?token=" . $token);
        } else {
            $conn->close();
            header('Location: http://beatmap.map/beatmap(mapa)/index.html');
        }
    } else {
        header('Location: http://beatmap.map/beatmap(mapa)/index.html');
    }
    exit;
}
?>
<!doctype html>
<html lang="pt-BR">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Entrar | BeatMap</title>
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
        padding: 50px;
        border-radius: 20px;
        border: 1px solid #2a2a2a;
        max-width: 450px;
        width: 90%;
        text-align: center;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
      }

      .auth-card h2 {
        font-size: 32px;
        margin-bottom: 15px;
      }

      .auth-card h2 span {
        color: var(--primary);
      }

      .auth-card p {
        color: var(--text-muted);
        margin-bottom: 40px;
      }

      .auth-options {
        display: flex;
        flex-direction: column;
        gap: 15px;
      }

      .btn-auth {
        padding: 18px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 700;
        transition: var(--transition);
        display: block;
      }

      .btn-login {
        background: var(--primary);
        color: white;
        border: 1px solid var(--primary);
      }

      .btn-login:hover {
        background: transparent;
        color: var(--primary);
        transform: translateY(-3px);
      }

      .btn-signup {
        background: transparent;
        color: white;
        border: 1px solid #333;
      }

      .btn-signup:hover {
        border-color: var(--primary);
        color: var(--primary);
        transform: translateY(-3px);
      }

      .back-home {
        margin-top: 25px;
        display: inline-block;
        color: var(--text-muted);
        text-decoration: none;
        font-size: 14px;
        transition: 0.3s;
      }

      .back-home:hover {
        color: white;
      }
    </style>
  </head>
  <body>
    <main class="auth-container">
      <div class="auth-card">
        <div class="logo" style="margin-bottom: 30px">BEAT<span>MAP</span></div>
        <h2>Bem-vindo ao <span>Mapa.</span></h2>
        <p>Escolha como deseja continuar a sua jornada musical.</p>

        <div class="auth-options">
          <a href="login.php" class="btn-auth btn-login"
            >Já tenho conta (Login)</a
          >

          <a href="registo.php" class="btn-auth btn-signup">Criar conta</a>
        </div>

        <a href="index.html" class="back-home">← Voltar ao início</a>
      </div>
    </main>
  </body>
</html>
