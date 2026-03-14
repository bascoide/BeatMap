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
    <title>Criar Conta | BeatMap</title>
    <link rel="icon" href="icone.png" />
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="style.css" />
    <style>
      .auth-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
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
        max-width: 500px;
        width: 100%;
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
        margin-top: 10px;
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

      .artist-link {
        margin-top: 12px;
      }

      .artist-link .artist-cta {
        color: #ff6b35;
      }

      /* Estilos para as mensagens de alerta */
      .alert {
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 14px;
        text-align: center;
        display: none; /* Escondido por defeito */
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
          <a href="index.php" style="text-decoration: none; color: inherit"
            >BEAT<span>MAP</span></a
          >
        </div>
        <h2>Cria a tua <span>Conta.</span></h2>

        <!-- Local onde a mensagem vai aparecer -->
        <div id="message-box"></div>

        <form action="register.php" method="POST">
          <div class="form-group">
            <label>nome de utilizador</label>
            <input
              type="text"
              name="username"
              placeholder="nome_de_utilizador"
              required
            />
          </div>
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
          </div>
          <button type="submit" class="btn-submit">Registar Agora</button>
        </form>

        <div class="auth-footer">
          Já tens uma conta? <a href="login.php">Faz Login</a>
          <div class="artist-link">
            És um artista? <a href="../beatmap/views/index.php" class="artist-cta">Cria conta aqui!</a>
          </div>
        </div>

        <a href="index.php" class="back-link">← Voltar ao Início</a>
      </div>
    </main>

    <script src="script.js"></script>
    <script>
      // Verificar se existem parâmetros na URL (ex: ?status=success)
      const urlParams = new URLSearchParams(window.location.search);
      const status = urlParams.get("status");
      const msgBox = document.getElementById("message-box");

      if (status === "success") {
        msgBox.innerHTML =
          '<div class="alert alert-success">Conta criada com sucesso! <br> A redirecionar para o login...</div>';
        msgBox.querySelector(".alert").style.display = "block";
        // Redirecionar para login após 3 segundos
        setTimeout(() => {
          window.location.href = "login.php";
        }, 3000);
      } else if (status === "exists") {
        msgBox.innerHTML =
          '<div class="alert alert-error">Esse nome de utilizador ou email já existe.</div>';
        msgBox.querySelector(".alert").style.display = "block";
      } else if (status === "empty") {
        msgBox.innerHTML =
          '<div class="alert alert-warning">Por favor preenche todos os campos.</div>';
      } else if (status === "password_length") {
        msgBox.innerHTML =
          '<div class="alert alert-error">A password deve ter pelo menos 6 caracteres.</div>';
        msgBox.querySelector(".alert").style.display = "block";
      }
    </script>
  </body>
</html>