<!doctype html>
<html lang="pt-BR">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sobre Nós | BeatMap</title>
    <link rel="icon" href="icone.png" />
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="style.css" />
    <style>
      /* Estilos específicos para a página Sobre */
      .about-hero {
        height: 60vh;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        background:
          linear-gradient(rgba(10, 10, 10, 0.4), rgba(10, 10, 10, 0.9)),
          url("fundo-do-site.gif");
        background-size: cover;
        background-position: center;
      }

      .content-section {
        padding: 100px 10%;
        line-height: 1.8;
      }

      .mission-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 40px;
        margin-top: 50px;
      }

      .card {
        background: #1a1a1a;
        padding: 40px;
        border-radius: 15px;
        border: 1px solid #2a2a2a;
        transition: var(--transition);
      }

      .card:hover {
        border-color: var(--primary);
        transform: translateY(-10px);
      }

      .card h3 {
        color: var(--primary);
        margin-bottom: 15px;
        font-size: 24px;
      }

      .interview-section {
        margin-top: 70px;
        background: #121212;
        border: 1px solid #2a2a2a;
        border-radius: 18px;
        padding: 35px;
      }

      .interview-layout {
        display: grid;
        grid-template-columns: 1.4fr 0.8fr;
        gap: 28px;
        align-items: center;
      }

      .interview-section h3 {
        color: var(--primary);
        font-size: 28px;
        margin-bottom: 12px;
      }

      .interview-section p {
        color: var(--text-muted);
        max-width: 760px;
        margin-bottom: 24px;
      }

      .interview-video {
        width: 100%;
        max-width: 380px;
        display: block;
        border-radius: 14px;
        border: 1px solid #343434;
        background: #000;
        margin-left: auto;
      }

      .interview-media {
        width: 100%;
      }

      @media (max-width: 768px) {
        .interview-section {
          padding: 24px;
          margin-top: 50px;
        }

        .interview-layout {
          grid-template-columns: 1fr;
          gap: 20px;
        }

        .interview-section h3 {
          font-size: 22px;
        }

        .interview-video {
          margin-left: 0;
          max-width: 100%;
        }
      }
    </style>
  </head>
  <body>
    <header class="navbar">
      <div class="logo">
        <a href="index.html" style="text-decoration: none; color: inherit"
          >BEAT<span>MAP</span></a
        >
      </div>
      <nav class="nav-links">
        <a href="index.html">Início</a>
        <a href="auth.php" class="btn-nav">Começar Agora</a>
      </nav>
    </header>

    <section class="about-hero">
      <div class="hero-content">
        <span class="tagline">CONHEÇA O NOSSO MAPA</span>
        <h1>Objetivo<span> do mapa</span></h1>
      </div>
    </section>

    <main class="content-section">
      <div
        class="text-block"
        style="max-width: 800px; margin: 0 auto; text-align: center"
      >
        <h2>Conectar batidas, <br />criar <span>oportunidades.</span></h2>
        <p style="font-size: 20px; margin-top: 20px; color: var(--text-light)">
          O BeatMap nasceu da necessidade de dar voz aos menos conhecidos. Somos
          uma plataforma feita para músicos, focada em conectar os artistas com
          outros ou com a comunidade deles.
        </p>
      </div>

      <div class="mission-grid">
        <div class="card">
          <h3>A Visão</h3>
          <p>
            Ser a maior plataforma de colaboração musical de Portugal, onde
            descobrir, pela localização geográfica, não é mais uma barreira para
            a criação de hits.
          </p>
        </div>
        <div class="card">
          <h3>O Compromisso</h3>
          <p>
            Oferecer ferramentas técnicas e visibilidade justa para músicos
            independentes que procuram mais conhecimento ou mais colaborações.
          </p>
        </div>
        <div class="card">
          <h3>A Tecnologia</h3>
          <p>
            Utilizamos um mapa inteligente, fácil e focado para que cada pessoa
            gaste menos tempo a procurar cada músico na área desejada.
          </p>
        </div>
      </div>

      <section class="interview-section" aria-labelledby="entrevista-titulo">
        <div class="interview-layout">
          <div class="interview-copy">
            <h3 id="entrevista-titulo">Entrevista de Referência</h3>
            <p>
              Esta entrevista com um artista foi uma das principais fontes de
              orientação para o projeto. Muitas ideias do BeatMap nasceram
              deste momento de conversa e escuta ativa sobre os desafios reais
              de quem vive da música.
            </p>
          </div>
          <div class="interview-media">
            <video class="interview-video" controls preload="metadata">
              <source src="../beatmap/assets/entrevista%20editada.mp4" type="video/mp4" />
              O teu navegador não suporta reprodução de vídeo.
            </video>
          </div>
        </div>
      </section>
    </main>

    <section class="section-dark">
      <div class="container">
        <div class="section-grid">
          <div class="text-block">
            <h2>A nossa Comunidade em <span>Números.</span></h2>
            <p>
              Cada número representa uma nova conexão musical feita através da
              nossa plataforma.
            </p>
          </div>
          <div class="stats-grid">
            <div class="stat-item"><strong>24/7</strong> Suporte</div>
            <div class="stat-item"><strong>100%</strong> Independente</div>
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

    <script src="script.js"></script>
  </body>
</html>
