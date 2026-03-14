<?php
session_start();
 $pageTitle = 'Editar artista';
require __DIR__ . '/../inc/header.php';
require __DIR__ . '/../inc/db.php';

if (!function_exists('sanitizeSocialLinks')) {
  function sanitizeSocialLinks(?string $raw): array
  {
    $allowed = [
      'instagram', 'x', 'youtube', 'tiktok', 'linkedin',
      'tidal', 'spotify', 'soundcloud', 'apple_music',
      'youtube_music', 'bandcamp'
    ];

    if (!$raw) {
      return [];
    }

    $data = json_decode($raw, true);
    if (!is_array($data)) {
      return [];
    }

    $clean = [];
    foreach ($allowed as $key) {
      if (empty($data[$key])) {
        continue;
      }

      $url = trim((string)$data[$key]);
      if ($url === '') {
        continue;
      }

      if (!preg_match('#^https?://#i', $url)) {
        $url = 'https://' . ltrim($url, '/');
      }

      if (filter_var($url, FILTER_VALIDATE_URL)) {
        $clean[$key] = $url;
      }
    }

    return $clean;
  }
}

if (!function_exists('validateSocialLinksPayload')) {
  function validateSocialLinksPayload(?string $raw, array &$errors): bool
  {
    $trimmed = trim((string)$raw);
    if ($trimmed === '') {
      return true;
    }

    $allowed = [
      'instagram', 'x', 'youtube', 'tiktok', 'linkedin',
      'tidal', 'spotify', 'soundcloud', 'apple_music',
      'youtube_music', 'bandcamp'
    ];

    $data = json_decode($trimmed, true);
    if (!is_array($data)) {
      $errors[] = 'Os links sociais enviados sao invalidos.';
      return false;
    }

    foreach ($data as $key => $value) {
      if (!is_string($key) || !in_array($key, $allowed, true)) {
        $errors[] = 'Existe uma rede social nao suportada.';
        return false;
      }

      $url = trim((string)$value);
      if ($url === '') {
        continue;
      }

      if (!preg_match('#^https?://#i', $url)) {
        $url = 'https://' . ltrim($url, '/');
      }

      if (!filter_var($url, FILTER_VALIDATE_URL)) {
        $errors[] = 'Existe um link social invalido.';
        return false;
      }
    }

    return true;
  }
}

if (!function_exists('sanitizePreviewUrl')) {
  function sanitizePreviewUrl($value): ?string
  {
    $url = trim((string)$value);
    if ($url === '') {
      return null;
    }

    if (!preg_match('#^https?://#i', $url)) {
      $url = 'https://' . ltrim($url, '/');
    }

    return filter_var($url, FILTER_VALIDATE_URL) ? $url : null;
  }
}

if (!function_exists('normalizeCouncilName')) {
  function normalizeCouncilName(string $name): string
  {
    return trim((string)preg_replace('/\s+Municipality$/iu', '', trim($name)));
  }
}

if (!function_exists('normalizeDistrictNamePt')) {
  function normalizeDistrictKey(string $name): string
  {
    $value = trim((string)preg_replace('/\s+/u', ' ', $name));
    if ($value === '') {
      return '';
    }

    $value = function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value);
    return strtr($value, [
      'á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a',
      'é' => 'e', 'è' => 'e', 'ê' => 'e',
      'í' => 'i', 'ì' => 'i', 'î' => 'i',
      'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'õ' => 'o',
      'ú' => 'u', 'ù' => 'u', 'û' => 'u',
      'ç' => 'c',
    ]);
  }

  function normalizeDistrictNamePt(string $name): string
  {
    $clean = trim((string)preg_replace('/\s+/u', ' ', $name));
    if ($clean === '') {
      return '';
    }

    $map = [
      'aveiro' => 'Aveiro',
      'beja' => 'Beja',
      'braga' => 'Braga',
      'braganca' => 'Bragança',
      'castelo branco' => 'Castelo Branco',
      'coimbra' => 'Coimbra',
      'evora' => 'Évora',
      'faro' => 'Faro',
      'guarda' => 'Guarda',
      'leiria' => 'Leiria',
      'lisbon' => 'Lisboa',
      'lisboa' => 'Lisboa',
      'portalegre' => 'Portalegre',
      'porto' => 'Porto',
      'santarem' => 'Santarém',
      'setubal' => 'Setúbal',
      'viana do castelo' => 'Viana do Castelo',
      'vila real' => 'Vila Real',
      'viseu' => 'Viseu',
      'azores' => 'Açores',
      'acores' => 'Açores',
      'a cores' => 'Açores',
      'madeira' => 'Madeira',
    ];

    $key = normalizeDistrictKey($clean);
    return $map[$key] ?? $clean;
  }
}

if (!function_exists('parseGenresInput')) {
  function parseGenresInput($raw): array
  {
    if (is_array($raw)) {
      $values = $raw;
    } elseif (is_string($raw)) {
      $trimmed = trim($raw);
      if ($trimmed === '') {
        return [];
      }

      $decoded = json_decode($trimmed, true);
      if (is_array($decoded)) {
        $values = [];
        foreach ($decoded as $item) {
          if (is_array($item) && isset($item['value'])) {
            $values[] = $item['value'];
          } elseif (is_string($item)) {
            $values[] = $item;
          }
        }
      } else {
        $values = explode(',', $trimmed);
      }
    } else {
      return [];
    }

    $clean = array_map(static fn($v) => trim((string)$v), $values);
    $clean = array_filter($clean, static fn($v) => $v !== '');

    return array_values(array_unique($clean));
  }
}

// Verificar se o artista está logado
if ((!isset($_SESSION['artist_logged_in']) || !$_SESSION['artist_logged_in']) && !isset($_SESSION['artist_id'])) {
    header('Location: ' . VIEWS_URL . 'add_artist.php');
    exit;
}

if (isset($_SESSION['artist_id']) && (!isset($_SESSION['artist_logged_in']) || !$_SESSION['artist_logged_in'])) {
  $_SESSION['artist_logged_in'] = true;
}

// Buscar dados do artista
$stmt = $mysqli->prepare("SELECT * FROM artists WHERE id = ?");
$stmt->bind_param('i', $_SESSION['artist_id']);
$stmt->execute();
$artist = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($artist) {
  $artist['district'] = normalizeDistrictNamePt((string)($artist['district'] ?? ''));
}

// Verificar se a conta está confirmada
if (!$artist || !$artist['is_confirmed']) {
    $_SESSION['artist_logged_in'] = false;
    header('Location: add_artist.php');
    exit;
}

$currentProfileImage = !empty($artist['profile_picture'])
  ? BASE_URL . ltrim($artist['profile_picture'], '/')
  : '';

$errors = [];
$success = false;
$availableGenres = [
  'Rock', 'Pop', 'Hip-Hop', 'Rap', 'Trap', 'Drill', 'R&B', 'Soul', 'Funk',
  'Jazz', 'Blues', 'Gospel', 'Reggae', 'Dancehall', 'Ska',
  'Fado', 'Pimba', 'Folclore', 'Música Popular Portuguesa',
  'Eletrónica', 'House', 'Techno', 'Trance', 'EDM', 'Dubstep', 'Drum and Bass',
  'Ambient', 'Lo-fi', 'Synthwave',
  'Indie', 'Alternative', 'Grunge', 'Punk', 'Metal', 'Hard Rock', 'Progressive Rock',
  'Kizomba', 'Kuduro', 'Afrobeats', 'Semba', 'Morna', 'Funaná',
  'Samba', 'Bossa Nova', 'Forró', 'MPB', 'Sertanejo', 'Bachata', 'Salsa', 'Tango',
  'Flamenco', 'Classical', 'Instrumental', 'Experimental'
];
$selectedGenres = parseGenresInput($artist['genre'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
  $genreValues = parseGenresInput($_POST['genre'] ?? []);
  $selectedGenres = $genreValues;
  $genre = implode(',', $genreValues);
    $conselho = normalizeCouncilName(trim($_POST['conselho'] ?? ''));
    $district = normalizeDistrictNamePt(trim($_POST['district'] ?? ''));
    $bio = trim($_POST['bio'] ?? '');
    $rawSocialLinks = $_POST['social_links'] ?? '';
    validateSocialLinksPayload($rawSocialLinks, $errors);
    $cleanSocialLinks = sanitizeSocialLinks($rawSocialLinks);
    $socialLinksJson = $cleanSocialLinks ? json_encode($cleanSocialLinks, JSON_UNESCAPED_SLASHES) : null;
    $preview1 = sanitizePreviewUrl($_POST['preview1'] ?? null);
    $preview2 = sanitizePreviewUrl($_POST['preview2'] ?? null);
    $preview3 = sanitizePreviewUrl($_POST['preview3'] ?? null);
  $removeProfilePicture = ($_POST['remove_profile_picture'] ?? '0') === '1';
    $profile_picture_path = $artist['profile_picture']; // Manter a imagem antiga por defeito

  if ($removeProfilePicture && !empty($artist['profile_picture'])) {
    $existingProfilePath = (string)$artist['profile_picture'];
    if (strpos($existingProfilePath, 'uploads/avatars/') === 0) {
      $existingPath = __DIR__ . '/../' . ltrim($existingProfilePath, '/');
      if (is_file($existingPath)) {
        @unlink($existingPath);
      }
    }
    $profile_picture_path = DEFAULT_AVATAR_PATH;
  }

    // Lidar com o upload da foto de perfil
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $target_dir = __DIR__ . '/../uploads/avatars/';
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        $imageFileType = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
        $unique_name = uniqid('avatar_', true) . '.' . $imageFileType;
        $target_file = $target_dir . $unique_name;
        $allowed_types = ['jpg', 'jpeg', 'png'];

        if (in_array($imageFileType, $allowed_types) && $_FILES['profile_picture']['size'] < 5000000) { // Limite de 5MB
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
                // Apagar foto antiga se existir
                if (!empty($artist['profile_picture']) && strpos((string)$artist['profile_picture'], 'uploads/avatars/') === 0 && file_exists(__DIR__ . '/../' . $artist['profile_picture'])) {
                  unlink(__DIR__ . '/../' . $artist['profile_picture']);
                }
                $profile_picture_path = 'uploads/avatars/' . $unique_name;
            } else {
                $errors[] = 'Ocorreu um erro ao carregar a sua nova imagem.';
            }
        } else {
            $errors[] = 'Ficheiro inválido. Apenas JPG, JPEG e PNG são permitidos e o tamanho deve ser inferior a 5MB.';
        }
    }

    // Validações
    if ($name === '') $errors[] = 'O nome é obrigatório.';
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email válido é obrigatório.';
    }

    // Verificar se email já existe (outro artista)
    if (!$errors && $email !== $artist['email']) {
        $check_stmt = $mysqli->prepare("SELECT id FROM artists WHERE email = ? AND id != ?");
        $check_stmt->bind_param('si', $email, $_SESSION['artist_id']);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if ($check_stmt->num_rows > 0) {
            $errors[] = 'Já existe um artista com este email.';
        }
        $check_stmt->close();
    }

    if (!$errors) {
        $stmt = $mysqli->prepare("UPDATE artists SET name = ?, email = ?, genre = ?, council = ?, district = ?, bio = ?, profile_picture = ?, social_links = ?, preview1 = ?, preview2 = ?, preview3 = ? WHERE id = ?");
        $stmt->bind_param('sssssssssssi', $name, $email, $genre, $conselho, $district, $bio, $profile_picture_path, $socialLinksJson, $preview1, $preview2, $preview3, $_SESSION['artist_id']);
        if ($stmt->execute()) {
            // Atualizar sessão
            $_SESSION['artist_name'] = $name;
            $_SESSION['artist_email'] = $email;
            $_SESSION['artist_city'] = $conselho;
            $success = true;
            $artist['name'] = $name;
            $artist['email'] = $email;
            $artist['genre'] = $genre;
            $artist['council'] = $conselho;
            $artist['district'] = $district;
            $artist['bio'] = $bio;
            $artist['social_links'] = $socialLinksJson;
            $artist['preview1'] = $preview1;
            $artist['preview2'] = $preview2;
            $artist['preview3'] = $preview3;
            $artist['profile_picture'] = $profile_picture_path;
            $currentProfileImage = !empty($profile_picture_path)
                ? BASE_URL . ltrim($profile_picture_path, '/')
                : '';
        } else {
            $errors[] = 'Ocorreu um erro ao atualizar o perfil.';
        }
        $stmt->close();
    }
}
?>

<main class="artist-profile-page">
  <section class="artist-profile-wrapper">
    <div class="artist-profile-container">
      <header class="artist-profile-header">
        <div class="artist-avatar-wrap">
          <img src="<?php echo htmlspecialchars($currentProfileImage ?: DEFAULT_AVATAR_URL); ?>" alt="Foto de perfil" class="artist-avatar">
        </div>
        <div class="artist-header-details">
          <h2><?php echo htmlspecialchars($_POST['name'] ?? $artist['name']); ?></h2>
          <p><?php echo htmlspecialchars($_POST['email'] ?? $artist['email']); ?></p>
          <span class="artist-badge">Conta Artista</span>
        </div>
      </header>

      <?php if ($success): ?>
        <div class="message success">Perfil atualizado com sucesso.</div>
      <?php endif; ?>

      <?php if ($errors): ?>
        <div class="message error">
          <ul><?php foreach ($errors as $e) echo '<li>' . htmlspecialchars($e) . '</li>'; ?></ul>
        </div>
      <?php endif; ?>

      <div class="profile-tabs">
        <button type="button" class="tab-btn active" data-tab="conta">Conta</button>
        <button type="button" class="tab-btn" data-tab="artista">Perfil Artista</button>
      </div>

      <form method="post" action="" class="artist-form" enctype="multipart/form-data">
        <div id="tab-conta" class="tab-content active">
          <h3>Dados da Conta</h3>
          <div class="form-group">
            <label for="profile_picture">Foto de Perfil</label>
            <div class="profile-upload-card" id="profileUploadCard">
              <div class="profile-upload-top">
                <div class="profile-preview <?php echo $currentProfileImage ? 'has-image' : ''; ?>" id="profilePreviewBox">
                  <img id="profilePreviewImg" src="<?php echo htmlspecialchars($currentProfileImage); ?>" alt="Preview da foto de perfil">
                  <img src="<?php echo htmlspecialchars(DEFAULT_AVATAR_URL); ?>" alt="Avatar padrão" class="profile-fallback-image">
                </div>
                <div class="profile-meta">
                  <strong>Imagem de Perfil</strong>
                  <small>JPG, JPEG ou PNG • até 5MB</small>
                </div>
              </div>

              <div class="profile-actions">
                <label for="profilePictureInput" class="file-trigger">Selecionar foto</label>
                <button type="button" class="crop-photo-btn" id="openCropBtn" disabled>Recortar foto</button>
                <button type="button" class="remove-photo-btn" id="removeProfilePictureBtn" <?php echo $currentProfileImage ? '' : 'disabled'; ?>>Retirar foto</button>
                <input type="file" class="hidden-file-input" id="profilePictureInput" name="profile_picture" accept="image/png, image/jpeg">
                <input type="hidden" name="remove_profile_picture" id="removeProfilePictureFlag" value="0">
                <span class="file-name" id="profileFileName"></span>
                <span class="drop-hint">Também podes arrastar e largar a imagem aqui.</span>
              </div>
            </div>
            <small class="muted-note">Deixe em branco para manter a foto atual.</small>
          </div>

          <div class="form-grid">
            <div class="form-group">
              <label for="name">Nome Artístico</label>
              <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($_POST['name'] ?? $artist['name']); ?>" required>
            </div>

            <div class="form-group">
              <label for="email">Email de Contacto</label>
              <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($_POST['email'] ?? $artist['email']); ?>" required>
            </div>
          </div>
        </div>

        <div id="tab-artista" class="tab-content">
          <h3>Informação Artística</h3>

          <div class="form-group genre-field">
            <div class="genre-header">
              <label for="genreSearchInput">Géneros Musicais</label>
              <div class="genre-search-wrap">
                <input type="text" id="genreSearchInput" class="genre-search-input" placeholder="Pesquisar género..." autocomplete="off">
                <button type="button" class="genre-search-clear" id="genreSearchClear" aria-label="Limpar pesquisa">×</button>
              </div>
            </div>
            <div class="genre-selected-wrap">
              <span class="genre-selected-label">Selecionados</span>
              <div class="genre-selected-chips" id="genreSelectedChips" aria-live="polite"></div>
            </div>
            <div class="genre-checklist">
              <?php foreach ($availableGenres as $genreOption): ?>
                <label class="genre-option <?php echo in_array($genreOption, $selectedGenres, true) ? 'selected' : ''; ?>">
                  <input type="checkbox" name="genre[]" value="<?php echo htmlspecialchars($genreOption); ?>" <?php echo in_array($genreOption, $selectedGenres, true) ? 'checked' : ''; ?>>
                  <?php echo htmlspecialchars($genreOption); ?>
                </label>
              <?php endforeach; ?>
            </div>
            <p class="genre-search-empty" id="genreSearchEmpty">Nenhum género encontrado.</p>
            <p class="genre-help">Clica nos géneros para selecionar. Podes escolher vários.</p>
          </div>

          <div class="form-grid">
            <div class="form-group">
              <label for="conselho">Concelho</label>
              <div class="input-wrapper">
                <input type="text" name="conselho" id="conselho" autocomplete="off" value="<?php echo htmlspecialchars(normalizeCouncilName($_POST['conselho'] ?? $artist['council'])); ?>" placeholder="Pesquisar...">
                <div id="conselho-suggestions" class="suggestions"></div>
              </div>
            </div>

            <div class="form-group">
              <label for="district">Distrito</label>
              <div class="input-wrapper">
                <input type="text" name="district" id="district" autocomplete="off" value="<?php echo htmlspecialchars($_POST['district'] ?? $artist['district']); ?>" placeholder="Pesquisar...">
                <div id="district-suggestions" class="suggestions"></div>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label for="bio">Biografia</label>
            <textarea name="bio" id="bio" rows="6" placeholder="Conta a tua história..."><?php echo htmlspecialchars($_POST['bio'] ?? $artist['bio']); ?></textarea>
          </div>

          <div class="form-group">
            <label>Previews de músicas</label>
            <div class="form-group">
              <label for="preview1">Preview 1</label>
              <input type="url" name="preview1" id="preview1" placeholder="https://..." value="<?php echo htmlspecialchars($_POST['preview1'] ?? ($artist['preview1'] ?? '')); ?>">
            </div>
            <div class="form-group">
              <label for="preview2">Preview 2</label>
              <input type="url" name="preview2" id="preview2" placeholder="https://..." value="<?php echo htmlspecialchars($_POST['preview2'] ?? ($artist['preview2'] ?? '')); ?>">
            </div>
            <div class="form-group">
              <label for="preview3">Preview 3</label>
              <input type="url" name="preview3" id="preview3" placeholder="https://..." value="<?php echo htmlspecialchars($_POST['preview3'] ?? ($artist['preview3'] ?? '')); ?>">
            </div>
          </div>

          <?php $savedSocialLinksJson = htmlspecialchars($_POST['social_links'] ?? ($artist['social_links'] ?? ''), ENT_QUOTES); ?>
          <div class="form-group">
            <label>Redes Sociais</label>
            <div class="social-links-panel" id="socialLinksPanel" data-initial="<?php echo $savedSocialLinksJson; ?>">
              <div class="social-links-header">
                <div class="social-header-info">
                  <div class="social-icon-wrapper">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>
                  </div>
                  <div>
                    <h4>Adiciona os teus links</h4>
                    <p>Plataformas de música e redes sociais</p>
                  </div>
                </div>
                <button type="button" class="social-add-btn" id="socialAddBtn">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                  Adicionar
                </button>
              </div>
              <div class="social-links-list" id="socialLinksList"></div>
              <input type="hidden" name="social_links" id="socialLinksInput" value="<?php echo $savedSocialLinksJson; ?>">
              <small class="social-help">Cola o link e detetamos a rede automaticamente.</small>
              <small id="socialLinksError" class="field-error" aria-live="polite">Corrija ou remova os links nao suportados antes de guardar.</small>
            </div>
          </div>
        </div>

        <div class="btn-row">
          <a href="/beatmap(mapa)/index.html" class="btn-submit btn-secondary">Cancelar</a>
          <button type="submit" class="btn-submit btn-primary">Guardar Alterações</button>
        </div>
      </form>
    </div>
  </section>
</main>

<div class="crop-modal" id="cropModal" aria-hidden="true">
  <div class="crop-modal-content">
    <h3 class="crop-modal-title">Recortar foto de perfil</h3>
    <div class="crop-canvas-wrap">
      <canvas id="cropCanvas" width="360" height="360"></canvas>
    </div>
    <div class="crop-controls">
      <label>
        Zoom
        <input type="range" id="cropZoom" min="1" max="3" step="0.01" value="1">
      </label>
      <span class="crop-help">Arrasta a imagem para posicionar. Para aumentar/reduzir, arrasta o canto inferior direito do quadrado.</span>
    </div>
    <div class="crop-actions">
      <button type="button" class="btn-submit btn-secondary" id="cancelCropBtn">Cancelar</button>
      <button type="button" class="btn-submit btn-primary" id="applyCropBtn">Aplicar recorte</button>
    </div>
  </div>
</div>

<style>
  .crop-modal {
    position: fixed;
    inset: 0;
    display: none;
    align-items: center;
    justify-content: center;
    background: rgba(5, 5, 5, 0.8);
    backdrop-filter: blur(4px);
    z-index: 3000;
    padding: 20px;
  }

  .crop-modal.open {
    display: flex;
  }

  .crop-modal-content {
    width: min(560px, 96vw);
    border: 1px solid #2a2a2a;
    border-radius: 14px;
    background: #141414;
    padding: 18px;
  }

  .crop-modal-title {
    font-size: 1rem;
    margin-bottom: 12px;
    color: #fff;
  }

  .crop-canvas-wrap {
    width: 100%;
    display: flex;
    justify-content: center;
    margin-bottom: 14px;
  }

  #cropCanvas {
    width: min(360px, 100%);
    aspect-ratio: 1 / 1;
    background: #0e0e0e;
    border: 1px solid #2f2f2f;
    border-radius: 10px;
    cursor: grab;
  }

  #cropCanvas.dragging {
    cursor: grabbing;
  }

  #cropCanvas.resizing {
    cursor: nwse-resize;
  }

  .crop-controls {
    display: grid;
    gap: 10px;
    margin-bottom: 14px;
  }

  .crop-controls label {
    display: grid;
    gap: 6px;
    color: #9a9a9a;
    font-size: 0.8rem;
  }

  .crop-controls input[type="range"] {
    width: 100%;
    accent-color: #ff6b35;
  }

  .crop-help {
    color: #9a9a9a;
    font-size: 0.78rem;
  }

  .crop-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
  }

  @media (max-width: 640px) {
    .cropper-container {
      width: 95%;
      max-width: none;
    }

    .cropper-body {
      min-height: 300px;
      max-height: 400px;
    }
  }
</style>

<style>
  .artist-profile-page {
    min-height: 100vh;
    padding: 96px 20px 60px;
    background:
      linear-gradient(rgba(10, 10, 10, 0.82), rgba(10, 10, 10, 0.82)),
      url("assets/fundo-do-site.gif"),
      radial-gradient(circle at 10% 0%, rgba(255, 107, 53, 0.14), transparent 35%),
      radial-gradient(circle at 90% 20%, rgba(255, 107, 53, 0.12), transparent 40%);
    background-size: cover, cover, auto, auto;
    background-position: center, center, center, center;
    background-repeat: no-repeat, no-repeat, no-repeat, no-repeat;
  }

  .artist-profile-wrapper {
    width: 100%;
    max-width: 920px;
    margin: 0 auto;
  }

  .artist-profile-container {
    background: #1a1a1a;
    border-radius: 20px;
    border: 1px solid #2a2a2a;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
    overflow: hidden;
  }

  .artist-profile-header {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 28px 34px;
    background: linear-gradient(130deg, #181818 0%, #1f1a18 100%);
    border-bottom: 1px solid #2a2a2a;
  }

  .artist-avatar {
    width: 88px;
    height: 88px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #ff6b35;
    box-shadow: 0 0 0 4px rgba(255, 107, 53, 0.22);
  }

  .artist-avatar-fallback {
    display: grid;
    place-items: center;
    font-weight: 800;
    font-size: 2rem;
    color: #fff;
    background: linear-gradient(135deg, #ff6b35, #ff865f);
  }

  .artist-header-details h2 {
    margin: 0;
    font-size: 1.5rem;
    color: #fff;
  }

  .artist-header-details p {
    margin: 5px 0 0;
    color: #bdbdbd;
    font-size: 0.95rem;
  }

  .artist-badge {
    display: inline-flex;
    margin-top: 12px;
    padding: 6px 12px;
    border-radius: 999px;
    border: 1px solid rgba(255, 107, 53, 0.4);
    color: #ffad91;
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
  }

  .message {
    margin: 20px 34px 0;
    padding: 12px 15px;
    border-radius: 8px;
    font-size: 14px;
  }

  .message.success {
    background: rgba(40, 167, 69, 0.2);
    border: 1px solid rgba(40, 167, 69, 0.5);
    color: #52c41a;
  }

  .message.error {
    background: rgba(220, 53, 69, 0.2);
    border: 1px solid rgba(220, 53, 69, 0.5);
    color: #ff7678;
  }

  .message ul {
    margin: 0;
    padding-left: 18px;
  }

  .profile-tabs {
    display: flex;
    border-bottom: 1px solid #2a2a2a;
    padding: 0 18px;
    margin-top: 16px;
  }

  .tab-btn {
    padding: 15px 20px;
    cursor: pointer;
    background: none;
    border: none;
    color: #9a9a9a;
    font-weight: 700;
    font-size: 0.95rem;
    position: relative;
    transition: color 0.3s ease;
  }

  .tab-btn:hover {
    color: #f1f1f1;
  }

  .tab-btn.active {
    color: #ff6b35;
  }

  .tab-btn.active::after {
    content: '';
    position: absolute;
    bottom: -1px;
    left: 0;
    width: 100%;
    height: 3px;
    border-radius: 3px 3px 0 0;
    background: #ff6b35;
  }

  .tab-content {
    display: none;
    padding: 28px 34px 8px;
  }

  .tab-content.active {
    display: block;
    animation: fadeIn 0.3s ease-in-out;
  }

  .tab-content h3 {
    margin: 0 0 22px;
    color: #ff845a;
    border-bottom: 1px solid #2a2a2a;
    padding-bottom: 10px;
    font-size: 1.08rem;
  }

  .artist-form {
    padding-bottom: 18px;
  }

  .form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
  }

  .form-group {
    margin-bottom: 22px;
  }

  .form-group label {
    display: block;
    margin-bottom: 8px;
    color: #cfcfcf;
    font-weight: 600;
    font-size: 0.9rem;
  }

  .form-group input[type="text"],
  .form-group input[type="email"],
  .form-group input[type="url"],
  .form-group input[type="file"],
  .form-group textarea {
    width: 100%;
    box-sizing: border-box;
    background: #0f0f0f;
    border: 1px solid #333;
    border-radius: 8px;
    color: #fff;
    font-size: 0.95rem;
    padding: 12px 14px;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
  }

  .form-group input:focus,
  .form-group textarea:focus {
    border-color: #ff6b35;
    box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.16);
    outline: none;
  }

  .form-group textarea {
    resize: vertical;
  }

  .profile-upload-card {
    border: 1px solid #2f2f2f;
    background: #111111;
    border-radius: 14px;
    padding: 14px;
    transition: 0.2s ease;
  }

  .profile-upload-card.drag-over {
    border-color: #ff6b35;
    box-shadow: 0 0 0 4px rgba(255, 107, 53, 0.2);
    background: rgba(255, 107, 53, 0.08);
  }

  .profile-upload-top {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
  }

  .profile-preview {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    border: 2px solid #2f2f2f;
    overflow: hidden;
    background: #1e1e1e;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #777;
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    flex-shrink: 0;
  }

  .profile-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: none;
  }

  .profile-preview .profile-fallback-image {
    display: block;
  }

  .profile-preview.has-image {
    border-color: rgba(255, 107, 53, 0.4);
  }

  .profile-preview.has-image img {
    display: block;
  }

  .profile-preview.has-image .profile-fallback-image {
    display: none;
  }

  .profile-meta strong {
    display: block;
    font-size: 0.9rem;
    color: #fff;
  }

  .profile-meta small {
    color: #9a9a9a;
    font-size: 0.78rem;
  }

  .profile-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
  }

  .file-trigger,
  .remove-photo-btn,
  .crop-photo-btn {
    border: 1px solid #353535;
    border-radius: 10px;
    padding: 10px 14px;
    background: #181818;
    color: #e7e7e7;
    font-size: 0.84rem;
    font-weight: 600;
    cursor: pointer;
    transition: 0.2s ease;
  }

  .file-trigger:hover,
  .remove-photo-btn:hover,
  .crop-photo-btn:hover {
    border-color: #ff6b35;
    color: #fff;
  }

  .remove-photo-btn {
    color: #ffb9b9;
  }

  .crop-photo-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }

  .remove-photo-btn.active {
    border-color: #d45555;
    background: rgba(212, 85, 85, 0.14);
    color: #ffd0d0;
  }

  .remove-photo-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }

  .hidden-file-input {
    display: none;
  }

  .file-name {
    width: 100%;
    color: #9a9a9a;
    font-size: 0.78rem;
    margin-top: 2px;
  }

  .drop-hint {
    width: 100%;
    color: #8e8e8e;
    font-size: 0.74rem;
    letter-spacing: 0.02em;
  }

  .muted-note {
    display: block;
    margin-top: 8px;
    color: #9a9a9a;
    font-size: 0.8rem;
  }

  .input-wrapper {
    position: relative;
  }

  .suggestions {
    position: absolute;
    top: 100%;
    left: 0;
    width: 100%;
    background: #141414;
    border: 1px solid #2f2f2f;
    border-radius: 0 0 4px 4px;
    display: none;
    max-height: 200px;
    overflow-y: auto;
    z-index: 1000;
  }

  .suggestion-item {
    padding: 10px 15px;
    cursor: pointer;
    border-bottom: 1px solid #2a2a2a;
    font-size: 0.85rem;
    color: #d0d0d0;
  }

  .suggestion-item:hover {
    background: #ff6b35;
    color: #fff;
  }

  .genre-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 10px;
  }

  .genre-header label {
    margin: 0;
  }

  .genre-search-wrap {
    position: relative;
    width: 220px;
    max-width: 48%;
  }

  .genre-search-input {
    width: 100%;
    height: 38px;
    padding: 0 34px 0 12px;
    border: 1px solid #2f2f2f;
    border-radius: 10px;
    background: #101010;
    color: #fff;
    font-size: 0.85rem;
    transition: 0.2s ease;
  }

  .genre-search-input:focus {
    outline: none;
    border-color: #ff6b35;
    box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.14);
  }

  .genre-search-clear {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    width: 22px;
    height: 22px;
    border: 0;
    border-radius: 999px;
    background: transparent;
    color: #9c9c9c;
    font-size: 16px;
    line-height: 1;
    cursor: pointer;
    display: none;
    align-items: center;
    justify-content: center;
    transition: 0.2s ease;
  }

  .genre-search-clear:hover {
    color: #fff;
    background: rgba(255, 255, 255, 0.08);
  }

  .genre-selected-wrap {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-bottom: 10px;
  }

  .genre-selected-label {
    color: #b2b2b2;
    font-size: 0.78rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
  }

  .genre-selected-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
  }

  .genre-chip {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    border: 1px solid rgba(255, 107, 53, 0.42);
    border-radius: 999px;
    padding: 4px 10px;
    background: rgba(255, 107, 53, 0.16);
    color: #ffe4d9;
    font-size: 0.77rem;
    font-weight: 600;
    cursor: pointer;
    transition: 0.2s ease;
  }

  .genre-chip:hover {
    border-color: #ff6b35;
    background: rgba(255, 107, 53, 0.24);
    color: #fff;
  }

  .genre-chip-remove {
    width: 16px;
    height: 16px;
    display: inline-grid;
    place-items: center;
    border-radius: 999px;
    background: rgba(0, 0, 0, 0.22);
    font-size: 0.78rem;
    line-height: 1;
  }

  .genre-chip-empty {
    color: #888;
    font-size: 0.78rem;
  }

  .genre-chip:focus-visible {
    outline: none;
    box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.2);
  }

  .genre-checklist {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
    gap: 8px;
    max-height: 280px;
    overflow-y: auto;
    padding: 4px;
    scrollbar-width: thin;
    scrollbar-color: rgba(255, 107, 53, 0.3) transparent;
  }

  .genre-option {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 10px 12px;
    border: 1px solid #2f2f2f;
    border-radius: 999px;
    background: #141414;
    color: #aaa;
    font-size: 0.82rem;
    font-weight: 500;
    cursor: pointer;
    transition: 0.2s ease;
    text-align: center;
    user-select: none;
  }

  .genre-option:hover {
    border-color: rgba(255, 107, 53, 0.5);
    color: #fff;
    transform: translateY(-1px);
  }

  .genre-option.selected {
    border-color: rgba(255, 107, 53, 0.7);
    background: rgba(255, 107, 53, 0.15);
    color: #fff;
    font-weight: 600;
  }

  .genre-option input[type="checkbox"] {
    display: none;
  }

  .genre-search-empty {
    display: none;
    margin-top: 8px;
    color: #9a9a9a;
    font-size: 0.8rem;
  }

  .genre-help {
    margin-top: 6px;
    color: #9a9a9a;
    font-size: 0.78rem;
  }

  .social-links-panel {
    padding: 16px;
    border: 1px solid #2f2f2f;
    border-radius: 12px;
    background: #141414;
  }

  .social-links-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 14px;
  }

  .social-header-info {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .social-icon-wrapper {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: grid;
    place-items: center;
    color: #ff6b35;
    background: rgba(255, 107, 53, 0.14);
  }

  .social-header-info h4 {
    margin: 0;
    color: #fff;
    font-size: 0.95rem;
  }

  .social-header-info p {
    margin: 2px 0 0;
    color: #aaa;
    font-size: 0.8rem;
  }

  .social-add-btn,
  .social-remove-btn {
    border: 1px solid #3a3a3a;
    background: #111;
    color: #ddd;
    border-radius: 8px;
    cursor: pointer;
    transition: 0.2s ease;
  }

  .social-add-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 9px 12px;
    font-weight: 600;
  }

  .social-add-btn:hover,
  .social-remove-btn:hover {
    border-color: #ff6b35;
    color: #ff6b35;
  }

  .social-links-list {
    display: grid;
    gap: 10px;
  }

  .social-link-row {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: nowrap;
    padding: 10px;
    border-radius: 12px;
    border: 1px solid #2a2a2a;
    background: #0f0f0f;
    transition: 0.2s ease;
  }

  .social-input-wrap {
    display: grid;
    grid-template-columns: 30px 1fr;
    align-items: center;
    gap: 10px;
    flex: 1;
    min-width: 0;
  }

  .social-input-wrap input {
    width: 100%;
    height: 44px;
    padding: 0 12px;
    line-height: 44px;
    border-radius: 10px;
    border: 1px solid #2f2f2f;
    background: #101010;
    color: #fff;
    font-size: 0.95rem;
    transition: 0.2s ease;
    min-width: 0;
  }

  .social-input-wrap input:focus {
    border-color: #ff6b35;
    box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.14);
    outline: none;
  }

  .social-input-wrap input::placeholder {
    color: #666;
  }

  .social-input-label {
    display: block;
    grid-column: 2 / 3;
    font-size: 0.75rem;
    color: #7b7b7b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .social-input-label.warning {
    color: #ffb9b9;
  }

  .social-link-row.active {
    border-color: rgba(255, 107, 53, 0.5);
    box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.12);
  }

  .social-link-row.mismatch {
    border-color: rgba(212, 85, 85, 0.65);
    box-shadow: 0 0 0 3px rgba(212, 85, 85, 0.14);
  }

  .social-link-row.mismatch .social-input-wrap input {
    border-color: #d45555;
    box-shadow: 0 0 0 2px rgba(212, 85, 85, 0.2);
  }

  .social-icon {
    width: 30px;
    height: 30px;
    border-radius: 10px;
    background: transparent;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid transparent;
    position: static;
    transform: none;
    pointer-events: none;
    flex-shrink: 0;
  }

  .social-icon img,
  .social-icon svg {
    width: 22px;
    height: 22px;
    object-fit: contain;
  }

  .social-remove-btn {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    border: 1px solid #353535;
    background: #151515;
    color: #ffb9b9;
    font-weight: 700;
    flex-shrink: 0;
  }

  .social-remove-btn:hover {
    border-color: #d45555;
    color: #fff;
  }

  .social-help {
    display: block;
    margin-top: 12px;
    color: #999;
    font-size: 0.8rem;
  }

  .field-error {
    display: none;
    margin-top: 6px;
    color: #ff8e8e;
    font-size: 0.78rem;
  }

  .field-error.visible {
    display: block;
  }

  .btn-row {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin: 18px 34px 10px;
  }

  .btn-submit {
    border: none;
    border-radius: 9px;
    padding: 12px 18px;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
    transition: 0.2s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }

  .btn-primary {
    background: #ff6b35;
    color: #fff;
  }

  .btn-primary:hover {
    background: #e85a26;
    transform: translateY(-2px);
  }

  .btn-secondary {
    background: transparent;
    color: #bbb;
    border: 1px solid #383838;
  }

  .btn-secondary:hover {
    color: #fff;
    border-color: #ff6b35;
  }

  @media (max-width: 768px) {
    .artist-profile-page {
      padding-top: 88px;
    }

    .artist-profile-header {
      padding: 24px 22px;
      flex-direction: column;
      text-align: center;
    }

    .form-grid {
      grid-template-columns: 1fr;
      gap: 0;
    }

    .tab-content {
      padding: 24px 22px 6px;
    }

    .message {
      margin: 18px 22px 0;
    }

    .btn-row {
      margin: 18px 22px 10px;
      flex-direction: column;
    }

    .btn-submit {
      width: 100%;
    }

    .profile-upload-top {
      flex-direction: column;
      align-items: flex-start;
    }

    .social-links-header {
      flex-direction: column;
      align-items: flex-start;
    }

    .genre-selected-wrap {
      align-items: flex-start;
    }
  }
</style>

<script>
document.addEventListener("DOMContentLoaded", function() {
  const tabButtons = document.querySelectorAll('.tab-btn');
  const tabContents = document.querySelectorAll('.tab-content');
  tabButtons.forEach((button) => {
    button.addEventListener('click', () => {
      const tabId = button.getAttribute('data-tab');
      tabButtons.forEach((btn) => btn.classList.remove('active'));
      button.classList.add('active');
      tabContents.forEach((content) => {
        content.classList.toggle('active', content.id === `tab-${tabId}`);
      });
    });
  });

  // 1. Géneros (igual ao registo)
  const genreOptions = Array.from(document.querySelectorAll('.genre-option'));
  const genreSearchInput = document.getElementById('genreSearchInput');
  const genreSearchClear = document.getElementById('genreSearchClear');
  const genreSearchEmpty = document.getElementById('genreSearchEmpty');
  const genreSelectedChips = document.getElementById('genreSelectedChips');
  const genreChecklist = document.querySelector('.genre-checklist');
  let refreshGenreUI = () => {};

  const reorderGenreOptions = () => {
    if (!genreChecklist) return;

    const sorted = [...genreOptions].sort((a, b) => {
      const aText = (a.textContent || '').trim();
      const bText = (b.textContent || '').trim();
      return aText.localeCompare(bText, 'pt-PT', { sensitivity: 'base' });
    });

    sorted.forEach((option) => genreChecklist.appendChild(option));
  };

  genreOptions.forEach((label) => {
    const checkbox = label.querySelector('input[type="checkbox"]');
    if (!checkbox) return;

    const syncSelectedState = () => {
      label.classList.toggle('selected', checkbox.checked);
    };

    label.addEventListener('click', (event) => {
      if (event.target === checkbox) return;
      event.preventDefault();
      checkbox.checked = !checkbox.checked;
      checkbox.dispatchEvent(new Event('change', { bubbles: true }));
      syncSelectedState();
    });

    checkbox.addEventListener('change', () => {
      syncSelectedState();
      refreshGenreUI();
    });
    syncSelectedState();
  });

  if (genreSearchInput && genreOptions.length) {
    const normalizeText = (value) =>
      String(value || '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase();

    const renderSelectedGenreChips = () => {
      if (!genreSelectedChips) return;
      genreSelectedChips.innerHTML = '';

      const selected = genreOptions
        .map((label) => ({
          label,
          checkbox: label.querySelector('input[type="checkbox"]'),
          text: (label.textContent || '').trim(),
        }))
        .filter((item) => item.checkbox && item.checkbox.checked)
        .sort((a, b) => a.text.localeCompare(b.text, 'pt-PT', { sensitivity: 'base' }));

      if (!selected.length) {
        const empty = document.createElement('span');
        empty.className = 'genre-chip-empty';
        empty.textContent = 'Nenhum género selecionado.';
        genreSelectedChips.appendChild(empty);
        return;
      }

      selected.forEach((item) => {
        const chip = document.createElement('button');
        chip.type = 'button';
        chip.className = 'genre-chip';
        chip.setAttribute('aria-label', `Remover ${item.text}`);
        chip.innerHTML = `<span>${item.text}</span><span class="genre-chip-remove" aria-hidden="true">×</span>`;
        chip.addEventListener('click', () => {
          if (!item.checkbox) return;
          item.checkbox.checked = false;
          item.checkbox.dispatchEvent(new Event('change', { bubbles: true }));
        });
        genreSelectedChips.appendChild(chip);
      });
    };

    const filterGenres = () => {
      const term = normalizeText(genreSearchInput.value.trim());
      let visibleCount = 0;
      let selectedCount = 0;

      genreOptions.forEach((label) => {
        const checkbox = label.querySelector('input[type="checkbox"]');
        const isSelected = Boolean(checkbox && checkbox.checked);
        if (isSelected) selectedCount += 1;

        const genreText = normalizeText(label.textContent || '');
        const passesSearch = term === '' || genreText.includes(term);
        const visible = !isSelected && passesSearch;
        label.style.display = visible ? 'flex' : 'none';
        if (visible) visibleCount += 1;
      });

      if (genreSearchEmpty) {
        if (visibleCount === 0 && selectedCount === genreOptions.length && genreOptions.length > 0) {
          genreSearchEmpty.textContent = 'Todos os géneros já foram selecionados.';
        } else {
          genreSearchEmpty.textContent = 'Nenhum género encontrado.';
        }
        genreSearchEmpty.style.display = visibleCount === 0 ? 'block' : 'none';
      }

      if (genreSearchClear) {
        genreSearchClear.style.display = genreSearchInput.value.trim() ? 'flex' : 'none';
      }
    };

    refreshGenreUI = () => {
      reorderGenreOptions();
      filterGenres();
      renderSelectedGenreChips();
    };

    genreSearchInput.addEventListener('input', filterGenres);

    if (genreSearchClear) {
      genreSearchClear.addEventListener('click', () => {
        genreSearchInput.value = '';
        refreshGenreUI();
        genreSearchInput.focus();
      });
    }

    refreshGenreUI();
  }

  // 2. Upload da foto (igual ao registo)
  const profileInput = document.getElementById('profilePictureInput');
  const profileUploadCard = document.getElementById('profileUploadCard');
  const profilePreviewBox = document.getElementById('profilePreviewBox');
  const profilePreviewImg = document.getElementById('profilePreviewImg');
  const removeProfilePictureBtn = document.getElementById('removeProfilePictureBtn');
  const removeProfilePictureFlag = document.getElementById('removeProfilePictureFlag');
  const profileFileName = document.getElementById('profileFileName');
  const openCropBtn = document.getElementById('openCropBtn');

  function setRemoveButtonState(hasImage) {
    const hasNewSelectedFile = Boolean(profileInput && profileInput.files && profileInput.files.length > 0);
    if (removeProfilePictureBtn) {
      removeProfilePictureBtn.disabled = !hasImage;
    }
    if (openCropBtn) {
      openCropBtn.disabled = !hasNewSelectedFile;
    }
  }

  function isValidProfileFile(file) {
    if (!file) return false;
    const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
    const validByMime = validTypes.includes((file.type || '').toLowerCase());
    const validByExtension = /\.(jpg|jpeg|png)$/i.test(file.name || '');
    const underMaxSize = file.size <= 5000000;
    return (validByMime || validByExtension) && underMaxSize;
  }

  function applyProfileFile(file) {
    if (!isValidProfileFile(file)) {
      if (profileFileName) {
        profileFileName.textContent = 'Ficheiro inválido. Usa JPG, JPEG ou PNG até 5MB.';
      }
      return;
    }

    const dt = new DataTransfer();
    dt.items.add(file);
    if (profileInput) {
      profileInput.files = dt.files;
    }

    const reader = new FileReader();
    reader.onload = (event) => {
      if (profilePreviewImg) profilePreviewImg.src = event.target.result;
      if (profilePreviewBox) profilePreviewBox.classList.add('has-image');
      if (profileFileName) profileFileName.textContent = file.name;
      if (removeProfilePictureBtn) removeProfilePictureBtn.classList.remove('active');
      if (removeProfilePictureFlag) removeProfilePictureFlag.value = '0';
      setRemoveButtonState(true);
    };
    reader.readAsDataURL(file);
  }

  function clearProfilePreview(markForRemoval = false) {
    if (profileInput) profileInput.value = '';
    if (profilePreviewImg) profilePreviewImg.src = '';
    if (profilePreviewBox) profilePreviewBox.classList.remove('has-image');
    if (profileFileName) profileFileName.textContent = markForRemoval ? 'A foto será removida ao guardar.' : '';
    if (removeProfilePictureBtn) removeProfilePictureBtn.classList.add('active');
    if (removeProfilePictureFlag) removeProfilePictureFlag.value = markForRemoval ? '1' : '0';
    setRemoveButtonState(false);
  }

  if (profileInput) {
    profileInput.addEventListener('change', () => {
      const file = profileInput.files && profileInput.files[0];
      if (!file) return;
      applyProfileFile(file);
    });
  }

  if (profileUploadCard) {
    ['dragenter', 'dragover'].forEach((eventName) => {
      profileUploadCard.addEventListener(eventName, (event) => {
        event.preventDefault();
        event.stopPropagation();
        profileUploadCard.classList.add('drag-over');
      });
    });

    ['dragleave', 'dragend'].forEach((eventName) => {
      profileUploadCard.addEventListener(eventName, (event) => {
        event.preventDefault();
        event.stopPropagation();
        profileUploadCard.classList.remove('drag-over');
      });
    });

    profileUploadCard.addEventListener('drop', (event) => {
      event.preventDefault();
      event.stopPropagation();
      profileUploadCard.classList.remove('drag-over');

      const droppedFile = event.dataTransfer?.files?.[0];
      if (!droppedFile) return;
      applyProfileFile(droppedFile);
    });
  }

  if (removeProfilePictureBtn) {
    removeProfilePictureBtn.addEventListener('click', () => {
      clearProfilePreview(true);
    });
  }

  if (openCropBtn) {
    openCropBtn.addEventListener('click', () => {
      openCropModal();
    });
  }

  setRemoveButtonState(Boolean(profilePreviewBox && profilePreviewBox.classList.contains('has-image')));

  // 3. Social links
  const socialPanel = document.getElementById('socialLinksPanel');
  if (socialPanel) {
    const socialList = document.getElementById('socialLinksList');
    const socialAddBtn = document.getElementById('socialAddBtn');
    const socialInput = document.getElementById('socialLinksInput');
    const socialLinksError = document.getElementById('socialLinksError');
    const editForm = document.querySelector('.artist-form');

    const ICONS = {
      link: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 13a5 5 0 0 0 7.07 0l2.12-2.12a5 5 0 0 0-7.07-7.07L10 5" fill="none" stroke="#8a8a8a" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 11a5 5 0 0 0-7.07 0L4.8 13.12a5 5 0 1 0 7.07 7.07L14 19" fill="none" stroke="#8a8a8a" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
      instagram: '<img src="<?php echo BASE_URL; ?>assets/social/instagram.png" alt="Instagram">',
      x: '<img src="<?php echo BASE_URL; ?>assets/social/x.png" alt="X (Twitter)">',
      youtube: '<img src="<?php echo BASE_URL; ?>assets/social/youtube.png" alt="YouTube">',
      youtube_music: '<img src="<?php echo BASE_URL; ?>assets/social/youtubemusic-com.png" alt="YouTube Music">',
      tiktok: '<img src="<?php echo BASE_URL; ?>assets/social/tiktok.png" alt="TikTok">',
      linkedin: '<img src="<?php echo BASE_URL; ?>assets/social/linkedin.png" alt="LinkedIn">',
      tidal: '<img src="<?php echo BASE_URL; ?>assets/social/tidal.png" alt="Tidal">',
      spotify: '<img src="<?php echo BASE_URL; ?>assets/social/spotify.png" alt="Spotify">',
      soundcloud: '<img src="<?php echo BASE_URL; ?>assets/social/soundcloud.png" alt="SoundCloud">',
      apple_music: '<img src="<?php echo BASE_URL; ?>assets/social/apple.png" alt="Apple Music">',
      bandcamp: '<img src="<?php echo BASE_URL; ?>assets/social/bandcamp.png" alt="Bandcamp">'
    };

    const PROVIDERS = [
      { key: 'instagram', label: 'Instagram', domains: ['instagram.com'] },
      { key: 'x', label: 'X (Twitter)', domains: ['x.com', 'twitter.com'] },
      { key: 'youtube_music', label: 'YouTube Music', domains: ['music.youtube.com'] },
      { key: 'youtube', label: 'YouTube', domains: ['youtube.com', 'youtu.be'] },
      { key: 'tiktok', label: 'TikTok', domains: ['tiktok.com'] },
      { key: 'linkedin', label: 'LinkedIn', domains: ['linkedin.com'] },
      { key: 'tidal', label: 'Tidal', domains: ['tidal.com'] },
      { key: 'spotify', label: 'Spotify', domains: ['spotify.com', 'open.spotify.com'] },
      { key: 'soundcloud', label: 'SoundCloud', domains: ['soundcloud.com'] },
      { key: 'apple_music', label: 'Apple Music', domains: ['music.apple.com'] },
      { key: 'bandcamp', label: 'Bandcamp', domains: ['bandcamp.com'] }
    ];
    const PROVIDERS_BY_KEY = Object.fromEntries(PROVIDERS.map((provider) => [provider.key, provider]));

    const decodeHtml = (html) => {
      const txt = document.createElement('textarea');
      txt.innerHTML = html;
      return txt.value;
    };

    const safeParse = (value) => {
      try {
        const decoded = decodeHtml(value || '{}');
        return JSON.parse(decoded);
      } catch (e) {
        return {};
      }
    };

    const normalizeUrl = (value) => {
      const trimmed = String(value || '').trim();
      if (!trimmed) return '';
      if (/^https?:\/\//i.test(trimmed)) return trimmed;
      return `https://${trimmed}`;
    };

    const detectProvider = (value) => {
      const normalized = normalizeUrl(value);
      if (!normalized) return null;

      let host = '';
      try {
        host = new URL(normalized).hostname.toLowerCase();
      } catch (e) {
        return null;
      }

      host = host.replace(/^www\./, '');
      return PROVIDERS.find((provider) =>
        provider.domains.some((domain) => host === domain || host.endsWith(`.${domain}`))
      ) || null;
    };

    const syncSocialLinks = () => {
      if (!socialList || !socialInput) return;
      const payload = {};
      Array.from(socialList.children).forEach((row) => {
        const input = row.querySelector('input');
        if (!input) return;
        const provider = detectProvider(input.value);
        if (!provider) return;
        const url = normalizeUrl(input.value);
        if (!url) return;
        try {
          new URL(url);
          payload[provider.key] = url;
        } catch (e) {
          return;
        }
      });

      socialInput.value = Object.keys(payload).length ? JSON.stringify(payload) : '';

      if (socialLinksError && !hasInvalidSocialRows()) {
        socialLinksError.classList.remove('visible');
      }
    };

    const isRowInvalid = (row) => {
      const input = row?.querySelector('input');
      const rawValue = String(input?.value || '').trim();
      if (!rawValue) return false;

      const normalized = normalizeUrl(rawValue);
      try {
        new URL(normalized);
      } catch (e) {
        return true;
      }

      return !detectProvider(rawValue);
    };

    const hasInvalidSocialRows = () => {
      if (!socialList) return false;
      return Array.from(socialList.children).some((row) => isRowInvalid(row));
    };

    const createSocialRow = (value = '', initialProviderKey = '') => {
      const row = document.createElement('div');
      row.className = 'social-link-row';
      row.dataset.providerKey = initialProviderKey || '';
      row.innerHTML = `
        <div class="social-input-wrap">
          <span class="social-icon">${ICONS.link}</span>
          <input type="url" placeholder="https://" value="${String(value || '').replace(/"/g, '&quot;')}">
          <small class="social-input-label"></small>
        </div>
        <button type="button" class="social-remove-btn">x</button>
      `;

      const input = row.querySelector('input');
      const icon = row.querySelector('.social-icon');
      const label = row.querySelector('.social-input-label');
      const removeBtn = row.querySelector('.social-remove-btn');

      const refresh = () => {
        const rawValue = String(input?.value || '').trim();
        const detectedProvider = detectProvider(rawValue);
        const fallbackProvider = PROVIDERS_BY_KEY[row.dataset.providerKey || ''] || null;
        const provider = detectedProvider || fallbackProvider;
        const normalized = normalizeUrl(rawValue);
        let urlIsValid = false;

        if (rawValue) {
          try {
            new URL(normalized);
            urlIsValid = true;
          } catch (e) {
            urlIsValid = false;
          }
        }

        if (input) {
          input.setCustomValidity('');
        }

        if (detectedProvider) {
          row.dataset.providerKey = detectedProvider.key;
        }

        if (!rawValue) {
          row.classList.remove('active');
          row.classList.remove('mismatch');
          if (icon) icon.innerHTML = ICONS.link;
          if (label) {
            label.classList.remove('warning');
            label.textContent = '';
          }
        } else if (!urlIsValid) {
          row.classList.remove('active');
          row.classList.add('mismatch');
          if (icon) icon.innerHTML = ICONS.link;
          if (label) {
            label.classList.add('warning');
            label.textContent = 'Link invalido. Verifique o URL.';
          }
          if (input) {
            input.setCustomValidity('Link invalido.');
          }
        } else if (detectedProvider) {
          row.classList.add('active');
          row.classList.remove('mismatch');
          if (icon) icon.innerHTML = ICONS[provider.key] || ICONS.link;
          if (label) {
            label.classList.remove('warning');
            label.textContent = provider.label;
          }
        } else {
          row.classList.remove('active');
          row.classList.add('mismatch');
          if (icon) icon.innerHTML = ICONS.link;
          if (label) {
            label.classList.add('warning');
            label.textContent = 'Esta rede/plataforma nao e suportada.';
          }
          if (input) {
            input.setCustomValidity('Rede ou plataforma nao suportada.');
          }
        }
        syncSocialLinks();
      };

      if (input) {
        input.addEventListener('input', refresh);
        input.addEventListener('blur', () => {
          const normalized = normalizeUrl(input.value);
          if (normalized) {
            input.value = normalized;
          }
          refresh();
        });
      }

      if (removeBtn) {
        removeBtn.addEventListener('click', () => {
          row.remove();
          if (socialList && socialList.children.length === 0) {
            socialList.appendChild(createSocialRow(''));
          }
          syncSocialLinks();
        });
      }

      refresh();
      return row;
    };

    const initial = safeParse(socialPanel.getAttribute('data-initial'));
    const initialEntries = Object.entries(initial || {});
    if (socialList) {
      if (initialEntries.length) {
        initialEntries.forEach(([providerKey, url]) => socialList.appendChild(createSocialRow(url, providerKey)));
      } else {
        socialList.appendChild(createSocialRow(''));
      }
    }

    if (socialAddBtn && socialList) {
      socialAddBtn.addEventListener('click', () => {
        socialList.appendChild(createSocialRow(''));
      });
    }

    if (editForm && socialList) {
      editForm.addEventListener('submit', (event) => {
        if (!hasInvalidSocialRows()) {
          if (socialLinksError) {
            socialLinksError.classList.remove('visible');
          }
          return;
        }

        event.preventDefault();
        if (socialLinksError) {
          socialLinksError.classList.add('visible');
        }
      });
    }
  }

  // 4. GeoNames
    const GN_USER = 'vascovale';
    const debounce = (fn, delay) => { let t; return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), delay); }; };
    const normalizeCouncilName = (name) => String(name || '').replace(/\s+Municipality$/i, '').trim();
    const normalizeDistrictKey = (name) => String(name || '')
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .replace(/\s+/g, ' ')
      .trim()
      .toLowerCase();
    const districtNameMapPt = {
      'aveiro': 'Aveiro',
      'beja': 'Beja',
      'braga': 'Braga',
      'braganca': 'Bragança',
      'castelo branco': 'Castelo Branco',
      'coimbra': 'Coimbra',
      'evora': 'Évora',
      'faro': 'Faro',
      'guarda': 'Guarda',
      'leiria': 'Leiria',
      'lisbon': 'Lisboa',
      'lisboa': 'Lisboa',
      'portalegre': 'Portalegre',
      'porto': 'Porto',
      'santarem': 'Santarém',
      'setubal': 'Setúbal',
      'viana do castelo': 'Viana do Castelo',
      'vila real': 'Vila Real',
      'viseu': 'Viseu',
      'azores': 'Açores',
      'acores': 'Açores',
      'a cores': 'Açores',
      'madeira': 'Madeira'
    };
    const normalizeDistrictName = (name) => {
      const cleaned = String(name || '').replace(/\s+/g, ' ').trim();
      if (!cleaned) return '';
      return districtNameMapPt[normalizeDistrictKey(cleaned)] || cleaned;
    };

    async function searchGeo(query, type, container, inputRef) {
        if (query.length < 2) { container.style.display = 'none'; return; }
        const fCode = type === 'district' ? 'ADM1' : 'ADM2';
        const url = `https://secure.geonames.org/searchJSON?username=${GN_USER}&country=PT&featureCode=${fCode}&name_startsWith=${encodeURIComponent(query)}&maxRows=5`;
        
        try {
            const res = await fetch(url);
            const data = await res.json();
            container.innerHTML = '';
            if (data.geonames && data.geonames.length > 0) {
                data.geonames.forEach(item => {
                    const div = document.createElement('div');
                    div.className = 'suggestion-item';
                    const cleanName = type === 'conselho' ? normalizeCouncilName(item.name) : normalizeDistrictName(item.name);
                    const itemDistrict = normalizeDistrictName(item.adminName1 || '');
                    div.textContent = cleanName + (type === 'conselho' ? ` (${itemDistrict})` : '');
                    div.onclick = () => {
                    inputRef.value = cleanName;
                        if(type === 'conselho' && document.getElementById('district')) 
                        document.getElementById('district').value = itemDistrict;
                        container.style.display = 'none';
                    };
                    container.appendChild(div);
                });
                container.style.display = 'block';
            }
        } catch (e) { console.error(e); }
    }

    const mIn = document.getElementById('conselho');
    const mSug = document.getElementById('conselho-suggestions');
    if (mIn) {
        mIn.oninput = debounce(() => searchGeo(mIn.value, 'conselho', mSug, mIn), 300);
        // Hide suggestions on click outside
        document.addEventListener('click', (e) => { if (e.target !== mIn) mSug.style.display = 'none'; });
    }

    const dIn = document.getElementById('district');
    const dSug = document.getElementById('district-suggestions');
    if (dIn) {
      if (dIn.value.trim()) {
        dIn.value = normalizeDistrictName(dIn.value);
      }
        dIn.oninput = debounce(() => searchGeo(dIn.value, 'district', dSug, dIn), 300);
        document.addEventListener('click', (e) => { if (e.target !== dIn) dSug.style.display = 'none'; });
    }

  // 5. Photo Cropper (igual ao criar perfil)
  const cropModal = document.getElementById('cropModal');
  const cropCanvas = document.getElementById('cropCanvas');
  const cropZoom = document.getElementById('cropZoom');
  const applyCropBtn = document.getElementById('applyCropBtn');
  const cancelCropBtn = document.getElementById('cancelCropBtn');

  let cropImage = null;
  let cropSelectorSize = 240;
  let cropScale = 1;
  let cropDrawWidth = 0;
  let cropDrawHeight = 0;
  let cropDrawX = 0;
  let cropDrawY = 0;
  let cropOffsetX = 0;
  let cropOffsetY = 0;
  let isDraggingImage = false;
  let dragStartX = 0;
  let dragStartY = 0;
  let dragOriginOffsetX = 0;
  let dragOriginOffsetY = 0;
  let isResizingCrop = false;
  let resizeStartPointerX = 0;
  let resizeStartPointerY = 0;
  let resizeStartSize = 240;
  let activeResizeHandle = null;
  const CROP_MIN_SIZE = 140;
  const CROP_MAX_SIZE = 340;
  const HANDLE_SIZE = 16;

  function drawCropCanvas() {
    if (!cropCanvas || !cropImage) return;
    const ctx = cropCanvas.getContext('2d');
    const canvasSize = cropCanvas.width;
    const zoom = parseFloat(cropZoom?.value || '1');

    const imgW = cropImage.naturalWidth;
    const imgH = cropImage.naturalHeight;
    const baseScale = Math.max(canvasSize / imgW, canvasSize / imgH);
    cropScale = baseScale * zoom;
    cropDrawWidth = imgW * cropScale;
    cropDrawHeight = imgH * cropScale;
    const baseDrawX = (canvasSize - cropDrawWidth) / 2;
    const baseDrawY = (canvasSize - cropDrawHeight) / 2;

    const maxSelectorByImage = Math.floor(Math.min(cropDrawWidth, cropDrawHeight));
    if (maxSelectorByImage <= 0) return;
    const minAllowedSize = Math.min(CROP_MIN_SIZE, maxSelectorByImage);
    cropSelectorSize = Math.max(minAllowedSize, Math.min(Math.min(CROP_MAX_SIZE, maxSelectorByImage), cropSelectorSize));
    const selectorX = (canvasSize - cropSelectorSize) / 2;
    const selectorY = (canvasSize - cropSelectorSize) / 2;

    let drawX = baseDrawX + cropOffsetX;
    let drawY = baseDrawY + cropOffsetY;

    const minDrawX = selectorX + cropSelectorSize - cropDrawWidth;
    const maxDrawX = selectorX;
    const minDrawY = selectorY + cropSelectorSize - cropDrawHeight;
    const maxDrawY = selectorY;

    drawX = Math.max(minDrawX, Math.min(maxDrawX, drawX));
    drawY = Math.max(minDrawY, Math.min(maxDrawY, drawY));

    cropOffsetX = drawX - baseDrawX;
    cropOffsetY = drawY - baseDrawY;
    cropDrawX = drawX;
    cropDrawY = drawY;

    ctx.clearRect(0, 0, canvasSize, canvasSize);
    ctx.fillStyle = '#0f0f0f';
    ctx.fillRect(0, 0, canvasSize, canvasSize);
    ctx.drawImage(cropImage, cropDrawX, cropDrawY, cropDrawWidth, cropDrawHeight);

    ctx.fillStyle = 'rgba(0, 0, 0, 0.42)';
    ctx.fillRect(0, 0, canvasSize, selectorY);
    ctx.fillRect(0, selectorY + cropSelectorSize, canvasSize, canvasSize - (selectorY + cropSelectorSize));
    ctx.fillRect(0, selectorY, selectorX, cropSelectorSize);
    ctx.fillRect(selectorX + cropSelectorSize, selectorY, canvasSize - (selectorX + cropSelectorSize), cropSelectorSize);

    ctx.strokeStyle = '#ffffff';
    ctx.lineWidth = 2;
    ctx.strokeRect(selectorX, selectorY, cropSelectorSize, cropSelectorSize);

    const corners = [
      { x: selectorX - HANDLE_SIZE / 2, y: selectorY - HANDLE_SIZE / 2 },
      { x: selectorX + cropSelectorSize - HANDLE_SIZE / 2, y: selectorY - HANDLE_SIZE / 2 },
      { x: selectorX - HANDLE_SIZE / 2, y: selectorY + cropSelectorSize - HANDLE_SIZE / 2 },
      { x: selectorX + cropSelectorSize - HANDLE_SIZE / 2, y: selectorY + cropSelectorSize - HANDLE_SIZE / 2 },
    ];

    ctx.fillStyle = '#ffffff';
    corners.forEach((corner) => {
      ctx.fillRect(corner.x, corner.y, HANDLE_SIZE, HANDLE_SIZE);
    });
    ctx.strokeStyle = '#0f0f0f';
    ctx.lineWidth = 1;
    corners.forEach((corner) => {
      ctx.strokeRect(corner.x, corner.y, HANDLE_SIZE, HANDLE_SIZE);
    });
  }

  function closeCropModal() {
    if (cropModal) {
      cropModal.classList.remove('open');
      cropModal.setAttribute('aria-hidden', 'true');
    }
  }

  function openCropModal() {
    const src = profilePreviewImg?.src || '';
    if (!src) return;

    cropImage = new Image();
    cropImage.onload = () => {
      if (cropZoom) cropZoom.value = '1';
      cropSelectorSize = 240;
      cropOffsetX = 0;
      cropOffsetY = 0;
      drawCropCanvas();
    };
    cropImage.src = src;

    if (cropModal) {
      cropModal.classList.add('open');
      cropModal.setAttribute('aria-hidden', 'false');
    }
  }

  [cropZoom].forEach((el) => {
    if (!el) return;
    el.addEventListener('input', drawCropCanvas);
  });

  if (cropCanvas) {
    const getCanvasPoint = (event) => {
      const rect = cropCanvas.getBoundingClientRect();
      const scaleX = cropCanvas.width / rect.width;
      const scaleY = cropCanvas.height / rect.height;
      return {
        x: (event.clientX - rect.left) * scaleX,
        y: (event.clientY - rect.top) * scaleY,
      };
    };

    cropCanvas.addEventListener('mousedown', (event) => {
      if (!cropImage) return;
      const point = getCanvasPoint(event);
      const selectorX = (cropCanvas.width - cropSelectorSize) / 2;
      const selectorY = (cropCanvas.height - cropSelectorSize) / 2;

      const hitTopLeft =
        point.x >= selectorX - HANDLE_SIZE / 2 &&
        point.x <= selectorX + HANDLE_SIZE / 2 &&
        point.y >= selectorY - HANDLE_SIZE / 2 &&
        point.y <= selectorY + HANDLE_SIZE / 2;
      const hitTopRight =
        point.x >= selectorX + cropSelectorSize - HANDLE_SIZE / 2 &&
        point.x <= selectorX + cropSelectorSize + HANDLE_SIZE / 2 &&
        point.y >= selectorY - HANDLE_SIZE / 2 &&
        point.y <= selectorY + HANDLE_SIZE / 2;
      const hitBottomLeft =
        point.x >= selectorX - HANDLE_SIZE / 2 &&
        point.x <= selectorX + HANDLE_SIZE / 2 &&
        point.y >= selectorY + cropSelectorSize - HANDLE_SIZE / 2 &&
        point.y <= selectorY + cropSelectorSize + HANDLE_SIZE / 2;
      const hitBottomRight =
        point.x >= selectorX + cropSelectorSize - HANDLE_SIZE / 2 &&
        point.x <= selectorX + cropSelectorSize + HANDLE_SIZE / 2 &&
        point.y >= selectorY + cropSelectorSize - HANDLE_SIZE / 2 &&
        point.y <= selectorY + cropSelectorSize + HANDLE_SIZE / 2;

      if (hitTopLeft || hitTopRight || hitBottomLeft || hitBottomRight) {
        isResizingCrop = true;
        cropCanvas.classList.add('resizing');
        resizeStartPointerX = point.x;
        resizeStartPointerY = point.y;
        resizeStartSize = cropSelectorSize;
        activeResizeHandle = hitTopLeft
          ? 'nw'
          : hitTopRight
            ? 'ne'
            : hitBottomLeft
              ? 'sw'
              : 'se';
        return;
      }

      const insideImage =
        point.x >= cropDrawX &&
        point.x <= cropDrawX + cropDrawWidth &&
        point.y >= cropDrawY &&
        point.y <= cropDrawY + cropDrawHeight;

      if (insideImage) {
        isDraggingImage = true;
        cropCanvas.classList.add('dragging');
        dragStartX = event.clientX;
        dragStartY = event.clientY;
        dragOriginOffsetX = cropOffsetX;
        dragOriginOffsetY = cropOffsetY;
      }
    });

    window.addEventListener('mousemove', (event) => {
      if (isResizingCrop) {
        const point = getCanvasPoint(event);
        const dx = point.x - resizeStartPointerX;
        const dy = point.y - resizeStartPointerY;
        const maxSizeByImage = Math.min(CROP_MAX_SIZE, Math.min(cropDrawWidth, cropDrawHeight));
        const minSizeByImage = Math.min(CROP_MIN_SIZE, maxSizeByImage);

        if (activeResizeHandle === 'se') {
          cropSelectorSize = Math.max(minSizeByImage, Math.min(maxSizeByImage, resizeStartSize + Math.max(dx, dy)));
        } else if (activeResizeHandle === 'nw') {
          cropSelectorSize = Math.max(minSizeByImage, Math.min(maxSizeByImage, resizeStartSize - Math.max(dx, dy)));
        } else if (activeResizeHandle === 'ne') {
          cropSelectorSize = Math.max(minSizeByImage, Math.min(maxSizeByImage, resizeStartSize + Math.max(dx, -dy)));
        } else if (activeResizeHandle === 'sw') {
          cropSelectorSize = Math.max(minSizeByImage, Math.min(maxSizeByImage, resizeStartSize + Math.max(-dx, dy)));
        }
        drawCropCanvas();
        return;
      }

      if (!isDraggingImage) return;
      cropOffsetX = dragOriginOffsetX + (event.clientX - dragStartX);
      cropOffsetY = dragOriginOffsetY + (event.clientY - dragStartY);
      drawCropCanvas();
    });

    window.addEventListener('mouseup', () => {
      if (isResizingCrop) {
        isResizingCrop = false;
        cropCanvas.classList.remove('resizing');
        activeResizeHandle = null;
      }
      if (isDraggingImage) {
        isDraggingImage = false;
        cropCanvas.classList.remove('dragging');
      }
    });
  }

  if (cancelCropBtn) {
    cancelCropBtn.addEventListener('click', closeCropModal);
  }

  if (applyCropBtn && cropCanvas) {
    applyCropBtn.addEventListener('click', () => {
      const selectorX = (cropCanvas.width - cropSelectorSize) / 2;
      const selectorY = (cropCanvas.height - cropSelectorSize) / 2;
      const sourceX = (selectorX - cropDrawX) / cropScale;
      const sourceY = (selectorY - cropDrawY) / cropScale;
      const sourceSize = cropSelectorSize / cropScale;

      const outputCanvas = document.createElement('canvas');
      outputCanvas.width = 512;
      outputCanvas.height = 512;
      const outputCtx = outputCanvas.getContext('2d');
      outputCtx.drawImage(
        cropImage,
        sourceX,
        sourceY,
        sourceSize,
        sourceSize,
        0,
        0,
        outputCanvas.width,
        outputCanvas.height
      );

      outputCanvas.toBlob((blob) => {
        if (!blob) return;
        const croppedFile = new File([blob], `avatar_cortado_${Date.now()}.png`, { type: 'image/png' });
        applyProfileFile(croppedFile);
        closeCropModal();
      }, 'image/png', 0.92);
    });
  }

  if (cropModal) {
    cropModal.addEventListener('click', (event) => {
      if (event.target === cropModal) {
        closeCropModal();
      }
    });
  }
});
</script>