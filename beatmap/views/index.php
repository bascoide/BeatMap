<?php
session_start();
require_once __DIR__ . '/../inc/config.php';

if (isset($_SESSION['artist_logged_in']) && $_SESSION['artist_logged_in']) {
  $mapUrl = str_replace('/beatmap/', '/beatmap(mapa)/', BASE_URL);
  header('Location: ' . $mapUrl);
  exit;
}

$pageTitle = 'Artists';
require __DIR__ . '/../inc/header.php';
require __DIR__ . '/../inc/db.php';
?>

<!-- HERO SECTION DO NOVO TEMPLATE -->
<main class="hero">
  <div class="hero-video-container">
    <div class="gradient-overlay"></div>
  </div>

  <!-- Visualizador de Música -->
  <div class="music-visualizer-container">
    <div class="music-bar"></div><div class="music-bar"></div><div class="music-bar"></div>
    <div class="music-bar"></div><div class="music-bar"></div><div class="music-bar"></div>
    <div class="music-bar"></div><div class="music-bar"></div><div class="music-bar"></div>
    <div class="music-bar"></div><div class="music-bar"></div><div class="music-bar"></div>
    <div class="music-bar"></div><div class="music-bar"></div><div class="music-bar"></div>
    <div class="music-bar"></div><div class="music-bar"></div><div class="music-bar"></div>
    <div class="music-bar"></div><div class="music-bar"></div><div class="music-bar"></div>
    <div class="music-bar"></div><div class="music-bar"></div><div class="music-bar"></div>
    <div class="music-bar"></div><div class="music-bar"></div><div class="music-bar"></div>
    <div class="music-bar"></div><div class="music-bar"></div><div class="music-bar"></div>
  </div>

  <div class="hero-content">
    <span class="tagline">BeatMap Artists</span>
    <h1>
      O lugar dos <br /><span class="highlight">Artistas.</span>
    </h1>
    <p>
      Aqui, todos os artistas têm um espaço para brilhar. Descubra talentos, conecte-se com outros músicos e faça parte de uma comunidade que valoriza a criatividade e a autenticidade.
    </p>
    <div class="hero-btns">
      <?php if (isset($_SESSION['artist_logged_in']) && $_SESSION['artist_logged_in']): ?>
          <a href="<?php echo VIEWS_URL; ?>artist_dashboard.php" class="btn-primary">Meu Painel</a>
      <?php else: ?>
          <a href="<?php echo VIEWS_URL; ?>add_artist.php" class="btn-primary">Começar Agora</a>
      <?php endif; ?>
    </div>
  </div>
</main>

<!-- SECÇÃO SOBRE -->
<section id="sobre" class="section-dark">
  <div class="container">
    <div class="section-grid">
      <div class="text-block">
        <h2>Não é apenas um mapa. <br />É o teu <span>palco.</span></h2>
        <p style="color: var(--text-muted); font-size: 1.1rem;">
          Nós damos voz a todos. No BeatMap, o foco são os tesouros
          desconhecidos da música e o crescimento artístico real.
          Instale a nossa aplicação para levar a música consigo.
        </p>
      </div>
      <div class="stats-grid">
        <?php
        // Contagem rápida para as estatísticas (opcional, se quiseres dinâmico)
        $count_res = $mysqli->query("SELECT COUNT(*) as total FROM artists");
        $total_artists = $count_res ? $count_res->fetch_assoc()['total'] : '10k+';
        ?>
        <div class="stat-item"><strong><?php echo $total_artists; ?></strong> Artistas</div>
        <div class="stat-item"><strong>300+</strong> Concelhos</div>
      </div>
    </div>
  </div>
</section>

<!-- SECÇÃO ÚLTIMOS ARTISTAS -->
<section id="artistas" class="section-dark" style="background: #050505; border-top: 1px solid #1a1a1a;">
  <div class="container">
    <h2>Últimos <span>Artistas</span><p style="color: var(--text-muted); font-size: 1.05rem; margin-bottom: 20px;">Clique no nome do artista para entrar no perfil.</p></h2>
    <?php
    $res = $mysqli->query("SELECT id, name, genre, council, district, created_at, profile_picture FROM artists WHERE is_confirmed = 1 ORDER BY created_at DESC LIMIT 6");
    if ($res && $res->num_rows) {
        echo '<ul class="artist-list">';
        while ($row = $res->fetch_assoc()) {
        $genre = trim((string)($row['genre'] ?? ''));
        $council = trim((string)($row['council'] ?? ''));
        $district = trim((string)($row['district'] ?? ''));
            
            echo '<li style="display: flex; align-items: flex-start; gap: 20px;">';
            echo '<div style="display: flex; flex-direction: column; align-items: center; min-width: 76px;">';

            // Foto de perfil pequena
            $avatarSrc = !empty($row['profile_picture'])
              ? (BASE_URL . ltrim($row['profile_picture'], '/'))
              : DEFAULT_AVATAR_URL;
            echo '<img src="'.htmlspecialchars($avatarSrc).'" alt="'.htmlspecialchars($row['name']).'" style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover; border: 2px solid #333; flex-shrink: 0;">';

            if ($council !== '' || $district !== '') {
              echo '<div style="margin-top: 8px; text-align: center; font-size: 0.78rem; line-height: 1.25; color: var(--text-muted);">';
              if ($council !== '' && $district !== '') {
                echo htmlspecialchars($council).',<br>'.htmlspecialchars($district);
              } elseif ($council !== '') {
                echo htmlspecialchars($council);
              } else {
                echo htmlspecialchars($district);
              }
              echo '</div>';
            }

            echo '</div>';

            echo '<div>';
            echo '<h4 style="margin-top: 0;"><a href="'.VIEWS_URL.'artist_profile.php?id='.$row['id'].'">'.htmlspecialchars($row['name']).'</a></h4>';
            echo '<div class="artist-meta-pills">';
            if ($genre !== '') {
              echo '<span class="meta-pill genre">'.htmlspecialchars($genre).'</span>';
            }
            echo '</div>';
            echo '</div>';
            echo '</li>';
        }
        echo '</ul>';
        echo '<div style="text-align:center; margin-top:40px;"><p style="color: var(--text-muted); font-size: 1.1rem; font-style: italic;">e muito mais...</p></div>';
    } else {
        echo '<p style="color: var(--text-muted);">Nenhum artista registado ainda.</p>';
    }
    ?>
  </div>
</section>

<script>
  // Script original de instalação PWA mantido
  let deferredPrompt;
  window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
  });
</script>

<?php require __DIR__ . '/../inc/footer.php'; ?>
