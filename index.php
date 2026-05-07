<?php
$cookieDomain = $_SERVER['HTTP_HOST'] ?? '';
$cookieDomain = preg_replace('/:\\d+$/', '', $cookieDomain);
if ($cookieDomain === 'localhost' || $cookieDomain === '') {
  session_set_cookie_params(0, '/');
} else {
  session_set_cookie_params(0, '/', '.' . $cookieDomain);
}
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
            
            header("Location: /beatmap(mapa)/auth_redirect.php?token=" . $token);
        } else {
            $conn->close();
            header('Location: /beatmap(mapa)/');
        }
    } else {
          header('Location: /beatmap(mapa)/');
    }
    exit;
}
?>
<!doctype html>
<html lang="pt-BR">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>BeatMap | Studio</title>
    <link rel="icon" href="beatmap(index)\icone.png" />
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="/beatmap(index)/style.css" />
  </head>
  <body>
    <header class="navbar">
      <div class="logo">BEAT<span>MAP</span></div>
      <nav class="nav-links">
        <a href="/beatmap(index)/sobre.php">O Projeto</a>
        <a href="/beatmap(index)/auth.php" class="btn-nav">Começar Agora</a>
      </nav>
    </header>

    <main class="hero">
      <div class="hero-video-container">
        <div class="gradient-overlay"></div>
      </div>

      <div class="music-visualizer-container">
        <div class="music-bar"></div>
        <div class="music-bar"></div>
        <div class="music-bar"></div>
        <div class="music-bar"></div>
        <div class="music-bar"></div>
        <div class="music-bar"></div>
        <div class="music-bar"></div>
        <div class="music-bar"></div>
        <div class="music-bar"></div>
        <div class="music-bar"></div>
        <div class="music-bar"></div>
        <div class="music-bar"></div>
        <div class="music-bar"></div>
        <div class="music-bar"></div>
        <div class="music-bar"></div>
        <div class="music-bar"></div>
        <div class="music-bar"></div>
        <div class="music-bar"></div>
        <div class="music-bar"></div>
        <div class="music-bar"></div>
        <div class="music-bar"></div>
        <div class="music-bar"></div>
        <div class="music-bar"></div>
        <div class="music-bar"></div>
        <div class="music-bar"></div>
        <div class="music-bar"></div>
        <div class="music-bar"></div>
        <div class="music-bar"></div>
        <div class="music-bar"></div>
        <div class="music-bar"></div>
      </div>

      <div class="hero-content">
        <span class="tagline"></span>
        <h1>
          Onde o som ganha <br /><span class="highlight">visibilidade.</span>
        </h1>
        <p>
          A plataforma definitiva para músicos independentes conectarem, criarem
          e dominarem o palco digital.
        </p>
        <div class="hero-btns">
          <a href="/beatmap(index)/auth.php" class="btn-primary">Explorar Mapa</a>
          <a href="/beatmap(index)/sobre.php" class="btn-secondary">Saber Mais</a>
        </div>
      </div>
    </main>

    <section id="sobre" class="section-dark">
      <div class="container">
        <div class="section-grid">
          <div class="text-block">
            <h2>Não é apenas um mapa. <br />É o teu <span>palco.</span></h2>
            <p>
              Nós damos voz a todos. No BeatMap, o foco é os tesouros
              desconhecidos da música, o crescimento artístico real.
            </p>
          </div>
          <div class="stats-grid">
            <div class="stat-item">
              <strong id="artist-count">A carregar...</strong> Músicos
            </div>
            <div class="stat-item"><strong>300+</strong> Concelhos</div>
          </div>
        </div>
      </div>
    </section>

    <footer class="footer">
      <div class="footer-content">
        <div class="logo">BEAT<span>MAP</span></div>
        <p>© 2026 BeatMap Studio. Todos os direitos reservados.</p>
      </div>
    </footer>

    <script src="/beatmap(index)/script.js"></script>
  </body>
</html>