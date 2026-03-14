<?php
if (!isset($pageTitle)) $pageTitle = 'BeatMap';
require_once __DIR__ . '/config.php';
?>
<!doctype html>
<html lang="pt">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?php echo htmlspecialchars($pageTitle); ?> | BeatMap</title>
  <link rel="icon" href="<?php echo ASSETS_URL; ?>icon-laranja.png" />
  <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>style.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css">
  <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify" defer></script>
  <base href="<?php echo BASE_URL; ?>">
</head>
<body>
  <header class="navbar">
    <a href="<?php echo VIEWS_URL; ?>" class="logo">BEAT<span>MAP</span> <small>artists</small></a>
    <nav class="nav-links">
      <?php if (isset($_SESSION['artist_logged_in']) && $_SESSION['artist_logged_in']): ?>
        <a href="/beatmap(mapa)/index.html" class="btn-nav">Voltar ao mapa</a>
      <?php else: ?>
        <a href="<?php echo VIEWS_URL; ?>index.php#artistas">Explorar</a>
        <a href="<?php echo VIEWS_URL; ?>add_artist.php" class="btn-nav">Começar</a>
      <?php endif; ?>
    </nav>
  </header>