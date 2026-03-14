<?php
session_start();
$pageTitle = 'Download - BeatMap';
require __DIR__ . '/../inc/header.php';
?>
<link rel="stylesheet" href="<?php echo ASSETS_URL; ?>download.css">

<main class="download-page container">
  <div class="download-hero">
    <h1>Descarregue o BeatMap</h1>
    <p>Leve a melhor música independente consigo. Instale a aplicação no seu dispositivo para uma experiência personalizada e sem interrupções.</p>
    <p class="subtitle">Grátis • Sem anúncios • Sem assinaturas obrigatórias</p>
  </div>

  <!-- Plataformas Principais -->
  <section class="platforms-main">
    <div class="platform-card">
      <div class="platform-icon">🤖</div>
      <h3>Android</h3>
      <div class="platform-info">Android 8.0 ou superior</div>
      <p>App móvel com modo offline e notificações</p>
      <a href="#" class="download-btn-main">
        <span>⬇️ Descarregar APK</span>
        <small>(65 MB)</small>
      </a>
    </div>
  </section>

    <!-- Informações e requisitos -->
  <section class="download-info">
    <h2>⚙️ Requisitos do Sistema</h2>
    
    <h3>📱 Android</h3>
    <div class="requirements-grid">
      <div class="requirement-item">
        <strong>Sistema Operativo</strong>
        <span>Android 8.0 (Oreo) ou superior</span>
      </div>
      <div class="requirement-item">
        <strong>Armazenamento</strong>
        <span>100 MB livre + cache para músicas</span>
      </div>
      <div class="requirement-item">
        <strong>RAM</strong>
        <span>2 GB mínimo (4 GB recomendado)</span>
      </div>
      <div class="requirement-item">
        <strong>Conexão</strong>
        <span>Wi-Fi ou dados móveis para streaming</span>
      </div>
    </div>
  </section>
</main>

</main>

<?php require __DIR__ . '/../inc/footer.php'; ?>