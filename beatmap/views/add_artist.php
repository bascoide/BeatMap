<?php
session_start();
$pageTitle = 'Registar artista';
require __DIR__ . '/../inc/header.php';
require __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../inc/available_genres.php';

// Redireciona se já estiver logado
if (isset($_SESSION['artist_logged_in']) && $_SESSION['artist_logged_in']) {
    header('Location: /beatmap/artist_dashboard.php');
    exit;
}

if (!isset($_SESSION['registration_data'])) {
    $_SESSION['registration_data'] = [];
}

$current_step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
if ($current_step < 1 || $current_step > 6) $current_step = 1;

$errors = [];
$fieldErrors = [];
$success = false;

$availableGenres = getAvailableArtistGenres();

if (!function_exists('normalizeCouncilName')) {
    function normalizeCouncilName(string $name): string
    {
        $normalized = trim(preg_replace('/\s+/u', ' ', $name));
        $normalized = preg_replace('/\s+Municipality$/iu', '', $normalized);
        return trim($normalized);
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

if (!function_exists('geonamesExactMatch')) {
    function utf8Lower(string $value): string
    {
        return function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value);
    }

    function geonamesExactMatch(string $name, string $featureCode, ?string $district = null): ?array
    {
        $searchName = trim($name);
        if ($searchName === '') {
            return null;
        }

        $params = [
            'username' => 'vascovale',
            'country' => 'PT',
            'featureCode' => $featureCode,
            'name_equals' => $searchName,
            'maxRows' => 10,
        ];
        $url = 'https://secure.geonames.org/searchJSON?' . http_build_query($params);

        $rawResponse = false;
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 3,
                CURLOPT_TIMEOUT => 5,
            ]);
            $rawResponse = curl_exec($ch);
            curl_close($ch);
        } else {
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'timeout' => 5,
                ]
            ]);
            $rawResponse = @file_get_contents($url, false, $context);
        }

        if (!$rawResponse) {
            return null;
        }

        $data = json_decode($rawResponse, true);
        if (!is_array($data) || empty($data['geonames']) || !is_array($data['geonames'])) {
            return null;
        }

        $targetName = utf8Lower(
            $featureCode === 'ADM1'
                ? normalizeDistrictNamePt($searchName)
                : normalizeCouncilName($searchName)
        );
        $targetDistrict = $district ? utf8Lower(normalizeDistrictNamePt($district)) : null;

        foreach ($data['geonames'] as $item) {
            $itemName = $featureCode === 'ADM1'
                ? normalizeDistrictNamePt((string)($item['name'] ?? ''))
                : normalizeCouncilName((string)($item['name'] ?? ''));
            $itemDistrict = normalizeDistrictNamePt((string)($item['adminName1'] ?? ''));

            if ($itemName === '') {
                continue;
            }

            if (utf8Lower($itemName) !== $targetName) {
                continue;
            }

            if ($featureCode === 'ADM2' && $targetDistrict !== null && $targetDistrict !== '' && utf8Lower($itemDistrict) !== $targetDistrict) {
                continue;
            }

            return [
                'name' => $itemName,
                'district' => $itemDistrict,
            ];
        }

        return null;
    }
}

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
    function sanitizePreviewUrl(?string $raw): string
    {
        $value = trim((string)$raw);
        if ($value === '') {
            return '';
        }

        if (!preg_match('#^https?://#i', $value)) {
            $value = 'https://' . ltrim($value, '/');
        }

        return filter_var($value, FILTER_VALIDATE_URL) ? $value : '';
    }
}

if (!function_exists('detectMusicPreviewEmbed')) {
    function detectMusicPreviewEmbed(string $rawUrl): ?array
    {
        $url = trim($rawUrl);
        if ($url === '') {
            return null;
        }

        if (!preg_match('#^https?://#i', $url)) {
            $url = 'https://' . ltrim($url, '/');
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $parts = parse_url($url);
        $host = strtolower((string)($parts['host'] ?? ''));
        $path = (string)($parts['path'] ?? '');
        $query = (string)($parts['query'] ?? '');

        if (strpos($host, 'www.') === 0) {
            $host = substr($host, 4);
        }

        if (strpos($host, 'spotify.com') !== false) {
            if (preg_match('#/(?:intl-[a-z]{2}/)?(track|album|playlist|episode|show)/([a-zA-Z0-9]+)#i', $path, $match)) {
                $type = strtolower($match[1]);
                $id = $match[2];
                return [
                    'provider' => 'Spotify',
                    'icon' => BASE_URL . 'assets/social/spotify.png',
                    'embed_src' => 'https://open.spotify.com/embed/' . $type . '/' . rawurlencode($id) . '?utm_source=generator',
                    'embed_height' => in_array($type, ['track', 'episode'], true) ? 80 : 120,
                    'url' => $url,
                ];
            }
        }

        if (strpos($host, 'youtube.com') !== false || strpos($host, 'youtu.be') !== false || strpos($host, 'music.youtube.com') !== false) {
            $videoId = '';

            if (strpos($host, 'youtu.be') !== false) {
                if (preg_match('#^/([a-zA-Z0-9_-]{11})#', $path, $match)) {
                    $videoId = $match[1];
                }
            } else {
                parse_str($query, $queryParams);
                if (!empty($queryParams['v']) && preg_match('#^[a-zA-Z0-9_-]{11}$#', (string)$queryParams['v'])) {
                    $videoId = (string)$queryParams['v'];
                } elseif (preg_match('#/(?:shorts|embed)/([a-zA-Z0-9_-]{11})#', $path, $match)) {
                    $videoId = $match[1];
                }
            }

            if ($videoId !== '') {
                $isMusic = strpos($host, 'music.youtube.com') !== false;
                return [
                    'provider' => $isMusic ? 'YouTube Music' : 'YouTube',
                    'icon' => BASE_URL . 'assets/social/' . ($isMusic ? 'youtubemusic-com.png' : 'youtube.png'),
                    'embed_src' => 'https://www.youtube.com/embed/' . rawurlencode($videoId) . '?rel=0&modestbranding=1&playsinline=1',
                    'embed_height' => 190,
                    'url' => $url,
                ];
            }
        }

        if (strpos($host, 'soundcloud.com') !== false) {
            return [
                'provider' => 'SoundCloud',
                'icon' => BASE_URL . 'assets/social/soundcloud.png',
                'embed_src' => 'https://w.soundcloud.com/player/?url=' . rawurlencode($url) . '&color=%237331df&auto_play=false&hide_related=false&show_comments=false&show_user=true&show_reposts=false&visual=true',
                'embed_height' => 120,
                'url' => $url,
            ];
        }

        if (strpos($host, 'music.apple.com') !== false) {
            $embedUrl = 'https://embed.music.apple.com' . $path . ($query !== '' ? ('?' . $query) : '');
            $isAppleTrack = preg_match('/(?:^|&)i=\d+/', $query) === 1;
            return [
                'provider' => 'Apple Music',
                'icon' => BASE_URL . 'assets/social/apple.png',
                'embed_src' => $embedUrl,
                'embed_height' => $isAppleTrack ? 140 : 180,
                'url' => $url,
            ];
        }

        return [
            'provider' => (string)(parse_url($url, PHP_URL_HOST) ?: 'Link'),
            'icon' => '',
            'embed_src' => '',
            'embed_height' => 0,
            'url' => $url,
        ];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'continue';
    $addFieldError = function (string $field, string $message) use (&$errors, &$fieldErrors): void {
        $errors[] = $message;
        $fieldErrors[$field][] = $message;
    };

    if ($current_step == 1) {
        $_SESSION['registration_data']['name'] = trim($_POST['name'] ?? '');
        $_SESSION['registration_data']['email'] = trim($_POST['email'] ?? '');
        $_SESSION['registration_data']['password'] = $_POST['password'] ?? '';
        $_SESSION['registration_data']['confirm_password'] = $_POST['confirm_password'] ?? '';
        $password = $_SESSION['registration_data']['password'];

        if ($_SESSION['registration_data']['name'] === '') $addFieldError('name', 'O nome é obrigatório.');
        if (!filter_var($_SESSION['registration_data']['email'], FILTER_VALIDATE_EMAIL)) $addFieldError('email', 'Email inválido.');
        if (strlen($password) < 6) $addFieldError('password', 'A senha deve ter 6+ caracteres.');
        if (!preg_match('/[A-Z]/', $password)) $addFieldError('password', 'A senha deve ter pelo menos uma letra maiúscula (A-Z).');
        if (!preg_match('/[a-z]/', $password)) $addFieldError('password', 'A senha deve ter pelo menos uma letra minúscula (a-z).');
        if (!preg_match('/[0-9]/', $password)) $addFieldError('password', 'A senha deve ter pelo menos um número (0-9).');
        if ($password !== $_SESSION['registration_data']['confirm_password']) $addFieldError('confirm_password', 'As senhas não coincidem.');

        if (!$errors) {
            $check = $mysqli->prepare("SELECT id FROM artists WHERE email = ?");
            $check->bind_param('s', $_SESSION['registration_data']['email']);
            $check->execute();
            if ($check->get_result()->num_rows > 0) $addFieldError('email', 'Este email já está registado.');
            $check->close();
        }

        if (!$errors && $action === 'continue') { header('Location: add_artist.php?step=2'); exit; }
    } 
    elseif ($current_step == 2) {
        $removeProfilePicture = ($_POST['remove_profile_picture'] ?? '0') === '1';

        if ($removeProfilePicture && !empty($_SESSION['registration_data']['profile_picture'])) {
            $existingProfilePath = (string)$_SESSION['registration_data']['profile_picture'];
            if (strpos($existingProfilePath, 'uploads/avatars/') === 0) {
                $existingPath = __DIR__ . '/../' . ltrim($existingProfilePath, '/');
                if (is_file($existingPath)) {
                    @unlink($existingPath);
                }
            }
            unset($_SESSION['registration_data']['profile_picture']);
        }

        // Handle profile picture upload
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
            $target_dir = __DIR__ . '/../uploads/avatars/';
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }
            $imageFileType = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
            $unique_name = uniqid('avatar_', true) . '.' . $imageFileType;
            $target_file = $target_dir . $unique_name;
            $allowed_types = ['jpg', 'jpeg', 'png'];

            if (in_array($imageFileType, $allowed_types) && $_FILES['profile_picture']['size'] < 5000000) { // 5MB limit
                if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
                    if (!empty($_SESSION['registration_data']['profile_picture'])) {
                        $existingProfilePath = (string)$_SESSION['registration_data']['profile_picture'];
                        if (strpos($existingProfilePath, 'uploads/avatars/') === 0) {
                            $oldPath = __DIR__ . '/../' . ltrim($existingProfilePath, '/');
                            if (is_file($oldPath)) {
                                @unlink($oldPath);
                            }
                        }
                    }
                    $_SESSION['registration_data']['profile_picture'] = 'uploads/avatars/' . $unique_name;
                } else {
                    $errors[] = 'Ocorreu um erro ao carregar a sua imagem.';
                }
            } else {
                $errors[] = 'Ficheiro inválido. Apenas JPG, JPEG ou PNG são permitidos e o tamanho deve ser inferior a 5MB.';
            }
        }

        $selectedGenres = $_POST['genre'] ?? [];
        $selectedGenresList = [];
        if (is_array($selectedGenres)) {
            $selectedGenresList = array_values(array_unique(array_filter(array_map('trim', $selectedGenres))));
            $_SESSION['registration_data']['genre'] = implode(',', $selectedGenresList);
        } elseif (is_string($selectedGenres)) {
            // Compatibilidade com dados antigos em string
            $selectedGenresList = array_values(array_unique(array_filter(array_map('trim', explode(',', $selectedGenres)))));
            $_SESSION['registration_data']['genre'] = implode(',', $selectedGenresList);
        }
        $_SESSION['registration_data']['bio'] = trim($_POST['bio'] ?? '');

        if ($action === 'back') { header('Location: add_artist.php?step=1'); exit; }
        if ($action === 'continue') {
            if (count($selectedGenresList) === 0) {
                $errors[] = 'Seleciona pelo menos um género musical para continuar.';
            } else {
                header('Location: add_artist.php?step=3');
                exit;
            }
        }
    } 
    elseif ($current_step == 3) {
        $rawSocialLinks = $_POST['social_links'] ?? '';
        validateSocialLinksPayload($rawSocialLinks, $errors);
        $cleanSocialLinks = sanitizeSocialLinks($rawSocialLinks);
        $_SESSION['registration_data']['social_links'] = $cleanSocialLinks
            ? json_encode($cleanSocialLinks, JSON_UNESCAPED_SLASHES)
            : '';

        if ($action === 'back') { header('Location: add_artist.php?step=2'); exit; }
        if ($action === 'continue') {
            header('Location: add_artist.php?step=4');
            exit;
        }
    }
    elseif ($current_step == 4) {
        // Passo de previews das músicas
        $preview1 = sanitizePreviewUrl($_POST['preview1'] ?? '');
        $preview2 = sanitizePreviewUrl($_POST['preview2'] ?? '');
        $preview3 = sanitizePreviewUrl($_POST['preview3'] ?? '');
        $_SESSION['registration_data']['preview1'] = $preview1;
        $_SESSION['registration_data']['preview2'] = $preview2;
        $_SESSION['registration_data']['preview3'] = $preview3;

        if ($action === 'back') { header('Location: add_artist.php?step=3'); exit; }
        if ($action === 'continue') {
            if ($preview1 === '' && $preview2 === '' && $preview3 === '') {
                $errors[] = 'Adicione pelo menos um link válido de preview de música para continuar.';
            } else {
                header('Location: add_artist.php?step=5');
                exit;
            }
        }
    }
    elseif ($current_step == 5) {
    // Passo de localização (agora passo 5)
        $location_mode = $_POST['location_mode'] ?? 'off';
        $rawDistrict = trim($_POST['district'] ?? '');
        $rawCouncil = ($location_mode === 'on') ? trim($_POST['conselho'] ?? '') : '';

        $_SESSION['registration_data']['region'] = normalizeDistrictNamePt($rawDistrict);
        $_SESSION['registration_data']['conselho'] = normalizeCouncilName($rawCouncil);

        if ($action === 'back') { header('Location: add_artist.php?step=4'); exit; }

        if ($action === 'continue') {
            if ($rawDistrict === '') {
                $errors[] = 'O Distrito é obrigatório.';
            } else {
                $districtMatch = geonamesExactMatch($rawDistrict, 'ADM1');
                if (!$districtMatch) {
                    $errors[] = 'O distrito indicado não existe em Portugal.';
                } else {
                    $_SESSION['registration_data']['region'] = $districtMatch['name'];
                }

                if ($location_mode === 'on') {
                    if ($rawCouncil === '') {
                        $errors[] = 'Indique o Concelho ou desligue a localização exata.';
                    } else {
                        $districtForValidation = $districtMatch['name'] ?? normalizeDistrictNamePt($rawDistrict);
                        $councilMatch = geonamesExactMatch($rawCouncil, 'ADM2', $districtForValidation);

                        if (!$councilMatch) {
                            $errors[] = 'O concelho indicado não existe para o distrito selecionado.';
                        } else {
                            $_SESSION['registration_data']['conselho'] = $councilMatch['name'];
                        }
                    }
                } else {
                    $_SESSION['registration_data']['conselho'] = '';
                }

                if (!$errors) {
                    header('Location: add_artist.php?step=6');
                    exit;
                }
            }
        }
    }
    elseif ($current_step == 6) {
        if ($action === 'back') { header('Location: add_artist.php?step=5'); exit; }

        if ($action === 'register') {
            $data = $_SESSION['registration_data'] ?? [];

            if (empty($data['name']) || empty($data['email']) || empty($data['password']) || empty($data['genre']) || empty($data['region'])) {
                $errors[] = 'Faltam dados obrigatórios do registo. Revê os passos anteriores.';
            }

            if (!$errors) {
                $data = $_SESSION['registration_data'];
                $hash = password_hash($data['password'], PASSWORD_DEFAULT);
                $token = bin2hex(random_bytes(16));
                $expires = date('Y-m-d H:i:s', time() + 86400);
                $profile_picture = trim((string)($data['profile_picture'] ?? ''));
                if ($profile_picture === '') {
                    $profile_picture = DEFAULT_AVATAR_PATH;
                }

                $social_links = !empty($data['social_links']) ? $data['social_links'] : null;
                $preview1 = !empty($data['preview1']) ? $data['preview1'] : null;
                $preview2 = !empty($data['preview2']) ? $data['preview2'] : null;
                $preview3 = !empty($data['preview3']) ? $data['preview3'] : null;

                $stmt = $mysqli->prepare("INSERT INTO artists (name, email, password_hash, genre, council, district, bio, confirmation_token, token_expires, profile_picture, social_links, preview1, preview2, preview3) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param('ssssssssssssss', $data['name'], $data['email'], $hash, $data['genre'], $data['conselho'], $data['region'], $data['bio'], $token, $expires, $profile_picture, $social_links, $preview1, $preview2, $preview3);

                if ($stmt->execute()) {
                    $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                    $confirm_url = $scheme . '://' . $_SERVER['HTTP_HOST'] . BASE_URL . 'email/confirm_email.php?token=' . urlencode($token);
                    $_SESSION['confirm_url'] = $confirm_url;
                    $_SESSION['artist_name'] = $data['name'];
                    $_SESSION['artist_email'] = $data['email'];

                    require __DIR__ . '/../inc/mailer.php';
                    sendConfirmationEmail($data['email'], $data['name'], $confirm_url);

                    $success = true;
                    unset($_SESSION['registration_data']);
                } else {
                    $errors[] = 'Erro ao salvar: ' . $mysqli->error;
                }
            }
        }
    }
}
?>

<style>
    .registration-page {
        padding: 120px 0 80px;
        min-height: 100vh;
        background:
            radial-gradient(circle at 20% 15%, rgba(255, 107, 53, 0.12) 0%, transparent 32%),
            radial-gradient(circle at 80% 0%, rgba(255, 107, 53, 0.08) 0%, transparent 28%),
            var(--bg-dark);
    }

    .register-form,
    .register-success {
        max-width: 640px;
        margin: 24px auto;
        padding: 34px;
        border-radius: 16px;
        border: 1px solid #2a2a2a;
        background: linear-gradient(180deg, #1a1a1a 0%, #151515 100%);
        box-shadow: 0 26px 60px -34px rgba(0, 0, 0, 0.9);
    }

    .register-title {
        text-align: center;
        margin-bottom: 22px;
        font-size: 2rem;
        line-height: 1.2;
        letter-spacing: -0.02em;
    }

    .step-indicator {
        color: var(--primary);
        font-size: 0.76rem;
        letter-spacing: 2px;
        text-transform: uppercase;
        display: block;
        margin-bottom: 14px;
        text-align: center;
        font-weight: 700;
    }

    .steps-track {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 8px;
        margin-bottom: 24px;
    }

    .steps-track span {
        display: block;
        height: 6px;
        border-radius: 999px;
        background: #2c2c2c;
        transition: var(--transition);
    }

    .steps-track span.active {
        background: var(--primary);
        box-shadow: 0 0 0 1px rgba(255, 107, 53, 0.35);
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        margin-bottom: 8px;
        font-size: 0.92rem;
        color: #dadada;
    }

    .form-group input,
    .form-group textarea,
    .form-group select {
        border-radius: 10px;
        border: 1px solid #2f2f2f;
        background: #101010;
        transition: var(--transition);
    }

    .form-group input:focus,
    .form-group textarea:focus,
    .form-group select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(255, 107, 53, 0.14);
    }

    .btn-block {
        width: 100%;
    }

    .form-actions {
        display: flex;
        gap: 12px;
        margin-top: 26px;
    }

    .register-form .btn,
    .register-form .btn-secondary {
        min-height: 48px;
        border-radius: 10px;
        font-size: 0.95rem;
        font-weight: 600;
        letter-spacing: 0.02em;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 24px;
        transition: all 0.2s ease;
    }

    .register-form .btn-secondary {
        background: #1a1a1a;
        border: 1px solid #333;
        color: #ccc;
    }

    .register-form .btn-secondary:hover {
        background: #252525;
        border-color: #555;
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }

    .register-form .btn {
        border: none;
        background: var(--primary);
        color: #fff;
        box-shadow: 0 4px 12px rgba(255, 107, 53, 0.25);
    }

    .register-form .btn:hover {
        background: var(--primary-hover);
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(255, 107, 53, 0.35);
    }

    .form-actions .btn {
        flex: 1;
    }

    .muted-note {
        color: var(--text-muted);
        font-size: 0.8rem;
        display: block;
        margin-top: 6px;
    }

    .register-success p {
        color: var(--text-muted);
        margin-top: 10px;
    }

    .register-success .btn {
        margin-top: 20px;
        display: inline-block;
    }

    .registration-map-preview {
        --primary: #7331df;
        --primary-hover: #8a4bf0;
        --panel-bg: rgba(28, 28, 28, 0.9);
        --text-light: #e0e0e0;
        --text-muted: #9e9e9e;
        --border-color: rgba(255, 255, 255, 0.12);
        --transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        margin-top: 14px;
    }

    .registration-map-preview .artist-modal-card {
        position: relative;
        width: 100%;
        border-radius: 14px;
        border: 1px solid var(--border-color);
        background: var(--panel-bg);
        color: var(--text-light);
        padding: 22px;
    }

    .registration-map-preview .artist-modal-header {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        margin-bottom: 16px;
    }

    .registration-map-preview .artist-modal-music-corner {
        margin-left: auto;
        max-width: 190px;
    }

    .registration-map-preview .artist-modal-music-corner .artist-modal-social-links {
        justify-content: flex-end;
    }

    .registration-map-preview .artist-modal-avatar {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--border-color);
    }

    .registration-map-preview .artist-modal-title {
        font-size: 24px;
        font-weight: 800;
        margin: 0 0 6px;
    }

    .registration-map-preview .artist-modal-genre {
        display: inline-block;
        background: var(--primary);
        color: #fff;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        padding: 4px 10px;
    }

    .registration-map-preview .artist-modal-section h3 {
        margin: 0 0 4px;
        color: var(--primary);
        font-size: 14px;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }

    .registration-map-preview .artist-modal-section p {
        margin: 0;
        line-height: 1.5;
        color: var(--text-light);
        white-space: pre-wrap;

        .registration-map-preview .artist-modal-section {
            margin-bottom: 32px;
        }
    }

    .registration-map-preview .artist-modal-social-links {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .registration-map-preview .artist-modal-social-link {
        width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        border-radius: 50%;
        border: 1px solid var(--border-color);
        color: var(--text-light);
        text-decoration: none;
        font-size: 0;
        transition: var(--transition);
        background: rgba(255, 255, 255, 0.03);
    }

    .registration-map-preview .artist-modal-social-link img {
        width: 18px;
        height: 18px;
        object-fit: contain;
    }

    .registration-map-preview .artist-modal-social-link:hover,
    .registration-map-preview .artist-modal-social-link:focus-visible {
        border-color: var(--primary);
        transform: translateY(-1px);
    }

    .registration-map-preview .artist-modal-meta {
        margin-top: 18px;
        padding-top: 14px;
        border-top: 1px solid var(--border-color);
        color: var(--text-muted);
        font-size: 14px;
    }

    .registration-map-preview .artist-modal-meta-layout {
        display: flex;
        justify-content: space-between;
        gap: 14px;
        align-items: flex-start;
    }

    .registration-map-preview .artist-modal-meta-left {
        flex: 1;
    }

    .registration-map-preview .artist-modal-meta-left p,
    .registration-map-preview .artist-modal-meta-right p {
        margin: 0 0 4px;
    }

    .registration-map-preview .artist-modal-meta-social .artist-modal-social-links {
        margin-top: 4px;
    }

    .registration-map-preview .artist-modal-meta-social p {
        margin-bottom: 3px;
    }

    .registration-map-preview .artist-modal-meta-right {
        text-align: right;
    }

    .registration-map-preview .music-preview-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 12px;
    }

    .registration-map-preview .music-preview-grid iframe {
        width: 100%;
        border: 0;
        display: block;
        border-radius: 10px;
        background: #0c0c0c;
    }

    .registration-map-preview .music-preview-link-fallback {
        display: inline-block;
        color: var(--text-muted);
        font-size: 0.85rem;
        text-decoration: none;
        padding: 6px 0;
    }

    .registration-map-preview .music-preview-link-fallback:hover {
        color: var(--primary);
    }

    .profile-upload-card {
        border: 1px solid #2f2f2f;
        background: #111111;
        border-radius: 14px;
        padding: 14px;
        transition: var(--transition);
    }

    .profile-upload-card.drag-over {
        border-color: var(--primary);
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

    .profile-preview.has-image .profile-placeholder {
        display: none;
    }

    .profile-preview.has-image .profile-fallback-image {
        display: none;
    }

    .profile-meta strong {
        display: block;
        font-size: 0.9rem;
    }

    .profile-meta small {
        color: var(--text-muted);
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
        transition: var(--transition);
    }

    .file-trigger:hover,
    .remove-photo-btn:hover,
    .crop-photo-btn:hover {
        border-color: var(--primary);
        color: #fff;
    }

    .remove-photo-btn {
        color: #ffb9b9;
    }

    .remove-photo-btn.active {
        border-color: #d45555;
        background: rgba(212, 85, 85, 0.14);
        color: #ffd0d0;
    }

    .crop-photo-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .remove-photo-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .file-name {
        width: 100%;
        color: var(--text-muted);
        font-size: 0.78rem;
        margin-top: 2px;
    }

    .drop-hint {
        width: 100%;
        color: var(--text-muted);
        font-size: 0.74rem;
        letter-spacing: 0.02em;
    }

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
        color: var(--text-muted);
        font-size: 0.8rem;
    }

    .crop-controls input[type="range"] {
        width: 100%;
        accent-color: var(--primary);
    }

    .crop-help {
        color: var(--text-muted);
        font-size: 0.78rem;
    }

    .crop-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }

    .hidden-file-input {
        display: none;
    }

    .genre-field .tagify {
        background: #101010;
        border: 1px solid #2f2f2f;
        border-radius: 10px;
        min-height: 52px;
        padding: 6px 8px;
        transition: var(--transition);
    }

    .genre-field .tagify:focus-within {
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(255, 107, 53, 0.14);
    }

    .genre-field .tagify__tag {
        margin: 4px;
    }

    .genre-field .tagify__tag > div {
        border-radius: 999px;
        background: rgba(255, 107, 53, 0.16);
        border: 1px solid rgba(255, 107, 53, 0.4);
        color: #fff;
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

    .genre-help {
        margin-top: 6px;
        color: var(--text-muted);
        font-size: 0.78rem;
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

    .genre-search-input {
        width: 220px;
        max-width: 48%;
        height: 38px;
        padding: 0 12px;
        border: 1px solid #2f2f2f;
        border-radius: 10px;
        background: #101010;
        color: #fff;
        font-size: 0.85rem;
        transition: var(--transition);
    }

    .genre-search-wrap {
        position: relative;
        width: 220px;
        max-width: 48%;
    }

    .genre-search-wrap .genre-search-input {
        width: 100%;
        max-width: none;
        padding-right: 34px;
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
        transition: var(--transition);
    }

    .genre-search-clear:hover {
        color: #fff;
        background: rgba(255, 255, 255, 0.08);
    }

    .genre-search-input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.14);
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

    .genre-checklist::-webkit-scrollbar {
        width: 8px;
    }

    .genre-checklist::-webkit-scrollbar-track {
        background: transparent;
        border-radius: 10px;
    }

    .genre-checklist::-webkit-scrollbar-thumb {
        background: rgba(255, 107, 53, 0.3);
        border-radius: 10px;
        transition: background 0.2s;
    }

    .genre-checklist::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 107, 53, 0.5);
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
        transition: var(--transition);
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
        color: var(--text-muted);
        font-size: 0.8rem;
    }

    .genre-required-error {
        display: none;
        margin-top: 8px;
        color: #ff8e8e;
        font-size: 0.8rem;
    }

    .genre-required-error.visible {
        display: block;
    }

  /* --- SWITCH PEQUENO E À DIREITA --- */
  .location-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding: 16px 18px;
    background: #111111;
    border-radius: 14px;
    border: 1px solid #2f2f2f;
    transition: var(--transition);
  }
  .location-header:hover {
    border-color: #3a3a3a;
  }
  .switch-info { display: flex; flex-direction: column; gap: 4px; }
  .switch-label { font-size: 0.92rem; color: #dadada; font-weight: 600; }
  .switch-desc { font-size: 0.78rem; color: var(--text-muted); }

  .switch { position: relative; display: inline-block; width: 46px; height: 26px; flex-shrink: 0; }
  .switch input { opacity: 0; width: 0; height: 0; }
  .slider {
    position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0;
    background-color: #2a2a2a; transition: var(--transition); border-radius: 26px;
    border: 1px solid #3a3a3a;
  }
  .slider:before {
    position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px;
    background-color: #888; transition: var(--transition); border-radius: 50%;
    box-shadow: 0 2px 4px rgba(0,0,0,0.3);
  }
  input:checked + .slider { 
    background-color: rgba(255, 107, 53, 0.16); 
    border-color: var(--primary);
  }
  input:checked + .slider:before { 
    transform: translateX(20px); 
    background-color: var(--primary);
    box-shadow: 0 0 10px rgba(255, 107, 53, 0.4);
  }
  input:focus-visible + .slider {
    box-shadow: 0 0 0 4px rgba(255, 107, 53, 0.14);
  }

  /* --- SUGESTÕES GEONAMES --- */
  .input-wrapper { position: relative; }
  .suggestions {
    position: absolute; top: 100%; left: 0; width: 100%;
        background: #141414; border: 1px solid #2f2f2f; z-index: 1000;
    display: none; max-height: 200px; overflow-y: auto; border-radius: 0 0 4px 4px;
  }
  .suggestion-item { padding: 10px 15px; cursor: pointer; border-bottom: 1px solid #2a2a2a; font-size: 0.85rem; }
  .suggestion-item:hover { background: var(--primary); color: #fff; }

    .field-error {
        display: none;
        margin-top: 6px;
        color: #ff8e8e;
        font-size: 0.78rem;
    }

    .field-error.visible {
        display: block;
    }

    .social-header-info .platform-list {
        font-size: 0.78rem;
        color: var(--text-muted);
        line-height: 1.4;
        display: block;
        margin-top: 2px;
    }

    @keyframes fadeInStep {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    @keyframes fadeOutStep {
        from { opacity: 1; transform: translateY(0); }
        to { opacity: 0; transform: translateY(15px); }
    }

    .register-form {
        animation: fadeInStep 0.5s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
    }

    .register-form.step-exit {
        animation: fadeOutStep 0.35s cubic-bezier(0.55, 0.055, 0.675, 0.19) forwards;
        pointer-events: none;
    }

    input.invalid-location {
        border-color: #d45555 !important;
        box-shadow: 0 0 0 3px rgba(212, 85, 85, 0.18);
    }

    .password-strength-meter {
        margin-top: 12px;
        font-size: 0.8rem;
        display: none;
        animation: fadeInStep 0.3s ease;
    }
    .strength-criteria {
        list-style: none;
        padding: 0;
        margin: 0;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 6px 14px;
    }
    .strength-criteria li {
        color: var(--text-muted);
        transition: color 0.3s ease;
        position: relative;
        padding-left: 20px;
    }
    .strength-criteria li::before {
        content: '○';
        position: absolute;
        left: 0;
        top: 1px;
        font-weight: bold;
        transition: all 0.3s ease;
    }
    .strength-criteria li.valid {
        color: #52c41a;
    }
    .strength-criteria li.valid::before {
        content: '✓';
    }

    @media (max-width: 768px) {
        .registration-page {
            padding: 98px 0 50px;
        }

        .register-form,
        .register-success {
            margin: 18px 14px;
            padding: 22px;
        }

        .register-title {
            font-size: 1.6rem;
            margin-bottom: 18px;
        }

        .genre-header {
            flex-direction: column;
            align-items: stretch;
        }

        .genre-search-input {
            width: 100%;
            max-width: none;
        }

        .genre-search-wrap {
            width: 100%;
            max-width: none;
        }

        .registration-map-preview .artist-modal-header {
            flex-wrap: wrap;
        }

        .registration-map-preview .artist-modal-music-corner {
            margin-left: 0;
            max-width: 100%;
        }

        .registration-map-preview .artist-modal-music-corner .artist-modal-social-links {
            justify-content: flex-start;
        }

        .registration-map-preview .artist-modal-meta-layout {
            flex-direction: column;
            align-items: stretch;
        }

        .registration-map-preview .artist-modal-meta-right {
            text-align: left;
        }
    }
</style>

<?php
$previewData = $_SESSION['registration_data'] ?? [];
$previewName = trim((string)($previewData['name'] ?? 'Artista'));
$previewGenre = trim((string)($previewData['genre'] ?? 'Geral'));
$previewDistrict = trim((string)($previewData['region'] ?? 'N/A'));
$previewCouncil = trim((string)($previewData['conselho'] ?? ''));
$previewBio = trim((string)($previewData['bio'] ?? ''));
$previewImage = trim((string)($previewData['profile_picture'] ?? ''));
$previewTrackLinks = array_values(array_filter([
    trim((string)($previewData['preview1'] ?? '')),
    trim((string)($previewData['preview2'] ?? '')),
    trim((string)($previewData['preview3'] ?? '')),
]));
$previewTrackEmbeds = [];
foreach ($previewTrackLinks as $trackLink) {
    $detectedEmbed = detectMusicPreviewEmbed((string)$trackLink);
    if ($detectedEmbed) {
        $previewTrackEmbeds[] = $detectedEmbed;
    }
}
$previewImageUrl = $previewImage !== ''
    ? (BASE_URL . ltrim($previewImage, '/'))
    : DEFAULT_AVATAR_URL;

$previewLinksRaw = $previewData['social_links'] ?? '';
$previewLinks = [];
if (is_string($previewLinksRaw) && $previewLinksRaw !== '') {
    $decodedPreviewLinks = json_decode($previewLinksRaw, true);
    if (is_array($decodedPreviewLinks)) {
        $previewLinks = $decodedPreviewLinks;
    }
}

$previewPlatformMeta = [
    'instagram' => ['label' => 'Instagram', 'icon' => BASE_URL . 'assets/social/instagram.png'],
    'x' => ['label' => 'X', 'icon' => BASE_URL . 'assets/social/x.png'],
    'youtube' => ['label' => 'YouTube', 'icon' => BASE_URL . 'assets/social/youtube.png'],
    'youtube_music' => ['label' => 'YouTube Music', 'icon' => BASE_URL . 'assets/social/youtubemusic-com.png'],
    'tiktok' => ['label' => 'TikTok', 'icon' => BASE_URL . 'assets/social/tiktok.png'],
    'linkedin' => ['label' => 'LinkedIn', 'icon' => BASE_URL . 'assets/social/linkedin.png'],
    'tidal' => ['label' => 'Tidal', 'icon' => BASE_URL . 'assets/social/tidal.png'],
    'spotify' => ['label' => 'Spotify', 'icon' => BASE_URL . 'assets/social/spotify.png'],
    'soundcloud' => ['label' => 'SoundCloud', 'icon' => BASE_URL . 'assets/social/soundcloud.png'],
    'apple_music' => ['label' => 'Apple Music', 'icon' => BASE_URL . 'assets/social/apple.png'],
    'bandcamp' => ['label' => 'Bandcamp', 'icon' => BASE_URL . 'assets/social/bandcamp.png'],
];

$previewMusicKeys = ['tidal', 'spotify', 'soundcloud', 'apple_music', 'youtube_music', 'bandcamp'];
$previewSocialNetworks = [];
$previewMusicApps = [];

foreach ($previewLinks as $key => $url) {
    if (!is_string($url) || trim($url) === '') {
        continue;
    }

    $normalizedKey = trim((string)$key);
    $platformMeta = $previewPlatformMeta[$normalizedKey] ?? ['label' => ucfirst(str_replace('_', ' ', $normalizedKey)), 'icon' => ''];
    $entry = [
        'label' => $platformMeta['label'],
        'icon' => $platformMeta['icon'],
        'url' => trim($url),
    ];

    if (in_array($normalizedKey, $previewMusicKeys, true)) {
        $previewMusicApps[] = $entry;
    } else {
        $previewSocialNetworks[] = $entry;
    }
}
?>

<div class="registration-page">
    <div class="container">
        <?php if ($success): ?>
            <div class="form-artist register-success" style="text-align: center;">
                <h2>Registo Efetuado!</h2>
                <p>Verifica o email <strong><?= htmlspecialchars($_SESSION['artist_email']) ?></strong> para ativar a conta.</p>
                <a href="/beatmap(index)/login.php" class="btn">Ir para Login</a>
            </div>
        <?php else: ?>
            <form method="post" class="form-artist register-form" enctype="multipart/form-data">
                <span class="step-indicator">Passo <?= $current_step ?> de 6</span>
                <div class="steps-track" aria-hidden="true">
                    <span class="<?= $current_step >= 1 ? 'active' : '' ?>"></span>
                    <span class="<?= $current_step >= 2 ? 'active' : '' ?>"></span>
                    <span class="<?= $current_step >= 3 ? 'active' : '' ?>"></span>
                    <span class="<?= $current_step >= 4 ? 'active' : '' ?>"></span>
                    <span class="<?= $current_step >= 5 ? 'active' : '' ?>"></span>
                    <span class="<?= $current_step >= 6 ? 'active' : '' ?>"></span>
                </div>
                <h2 class="register-title">
                    <?= $current_step==1 ? 'Criar Conta' : ($current_step==2 ? 'O Teu Som' : ($current_step==3 ? 'Redes Sociais' : ($current_step==4 ? 'Previews de Músicas' : ($current_step==5 ? 'Localização' : 'Pré-visualização no Mapa')))) ?>
                </h2>

                <?php if ($errors && !($current_step == 1 && !empty($fieldErrors))): ?>
                    <div class="alert error"><ul><?php foreach($errors as $e) echo "<li>$e</li>"; ?></ul></div>
                <?php endif; ?>

                <?php if ($current_step == 1): ?>
                    <div class="form-group">
                        <label>Nome Artístico</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($_SESSION['registration_data']['name']??'') ?>" required>
                        <?php if (!empty($fieldErrors['name'])): ?>
                            <?php foreach ($fieldErrors['name'] as $fieldError): ?>
                                <p class="field-error visible"><?= htmlspecialchars($fieldError) ?></p>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($_SESSION['registration_data']['email']??'') ?>" required>
                        <?php if (!empty($fieldErrors['email'])): ?>
                            <?php foreach ($fieldErrors['email'] as $fieldError): ?>
                                <p class="field-error visible"><?= htmlspecialchars($fieldError) ?></p>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label>Senha</label>
                        <input type="password" name="password" id="password" required>
                        <?php if (!empty($fieldErrors['password'])): ?>
                            <?php foreach ($fieldErrors['password'] as $fieldError): ?>
                                <p class="field-error visible"><?= htmlspecialchars($fieldError) ?></p>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <div id="password-strength" class="password-strength-meter">
                            <ul class="strength-criteria">
                                <li id="length-check">Pelo menos 6 caracteres</li>
                                <li id="upper-check">Uma letra maiúscula (A-Z)</li>
                                <li id="lower-check">Uma letra minúscula (a-z)</li>
                                <li id="number-check">Um número (0-9)</li>
                            </ul>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Confirmar Senha</label>
                        <input type="password" name="confirm_password" required>
                        <?php if (!empty($fieldErrors['confirm_password'])): ?>
                            <?php foreach ($fieldErrors['confirm_password'] as $fieldError): ?>
                                <p class="field-error visible"><?= htmlspecialchars($fieldError) ?></p>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <button type="submit" name="action" value="continue" class="btn btn-block">Continuar</button>

                <?php elseif ($current_step == 2): ?>
                    <?php
                        $savedProfile = $_SESSION['registration_data']['profile_picture'] ?? '';
                        $savedProfileUrl = $savedProfile ? (BASE_URL . ltrim($savedProfile, '/')) : '';
                        $savedGenres = array_filter(array_map('trim', explode(',', $_SESSION['registration_data']['genre'] ?? '')));
                    ?>
                    <div class="form-group">
                        <label>Foto de Perfil (Opcional)</label>
                        <div class="profile-upload-card" id="profileUploadCard">
                            <div class="profile-upload-top">
                                <div class="profile-preview <?= $savedProfileUrl ? 'has-image' : '' ?>" id="profilePreviewBox">
                                    <img id="profilePreviewImg" src="<?= htmlspecialchars($savedProfileUrl) ?>" alt="Preview da foto de perfil">
                                    <img src="<?= htmlspecialchars(DEFAULT_AVATAR_URL) ?>" alt="Avatar padrão" class="profile-fallback-image">
                                </div>
                                <div class="profile-meta">
                                    <strong>Imagem de Perfil</strong>
                                    <small>JPG, JPEG ou PNG • até 5MB</small>
                                </div>
                            </div>

                            <div class="profile-actions">
                                <label for="profilePictureInput" class="file-trigger">Selecionar foto</label>
                                <button type="button" class="crop-photo-btn" id="openCropBtn" disabled>Recortar foto</button>
                                <button type="button" class="remove-photo-btn" id="removeProfilePictureBtn" disabled>Retirar foto</button>
                                <input type="file" class="hidden-file-input" id="profilePictureInput" name="profile_picture" accept="image/png, image/jpeg">
                                <input type="hidden" name="remove_profile_picture" id="removeProfilePictureFlag" value="0">
                                <span class="file-name" id="profileFileName"></span>
                                <span class="drop-hint">Também podes arrastar e largar a imagem aqui.</span>
                            </div>
                        </div>
                        <small class="muted-note">Tamanho máximo: 5MB.</small>
                    </div>
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
                            <div class="genre-selected-chips" id="genreSelectedChips" aria-live="polite">
                                <?php foreach ($savedGenres as $genre): ?>
                                    <button type="button" class="genre-chip" tabindex="0">
                                        <span><?= htmlspecialchars($genre) ?></span>
                                        <span class="genre-chip-remove" aria-hidden="true">×</span>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="genre-checklist">
                            <?php foreach ($availableGenres as $genre): ?>
                                <label class="genre-option <?= in_array($genre, $savedGenres, true) ? 'selected' : '' ?>">
                                    <input type="checkbox" name="genre[]" value="<?= htmlspecialchars($genre) ?>" <?= in_array($genre, $savedGenres, true) ? 'checked' : '' ?>>
                                    <?= htmlspecialchars($genre) ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <p class="genre-search-empty" id="genreSearchEmpty">Nenhum género encontrado.</p>
                        <p class="genre-required-error" id="genreRequiredError">Seleciona pelo menos um género musical para continuar.</p>
                        <p class="genre-help">Clica nos géneros para selecionar. Podes escolher vários.</p>
                    </div>
                    <div class="form-group">
                        <label>Biografia (Opcional)</label>
                        <textarea name="bio" rows="4"><?= htmlspecialchars($_SESSION['registration_data']['bio']??'') ?></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="submit" name="action" value="back" class="btn-secondary">Voltar</button>
                        <button type="submit" name="action" value="continue" class="btn">Continuar</button>
                    </div>

                <?php elseif ($current_step == 3): ?>
                    <?php
                        $savedSocialLinksJson = htmlspecialchars($_SESSION['registration_data']['social_links'] ?? '', ENT_QUOTES);
                    ?>
                    <div class="form-group">
                        <label>Espaço musical</label>
                        <div class="social-links-panel" id="musicLinksPanel" data-initial="<?= $savedSocialLinksJson ?>">
                            <div class="social-links-header">
                                <div class="social-header-info">
                                    <div class="social-icon-wrapper">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18V5l12-2v13"></path><circle cx="6" cy="18" r="3"></circle><circle cx="18" cy="16" r="3"></circle></svg>
                                    </div>
                                    <div>
                                        <h4>Plataformas de Música</h4>
                                        <small class="platform-list">TIDAL, Spotify, SoundCloud, Apple Music, YouTube Music e Bandcamp</small>
                                    </div>
                                </div>
                                <button type="button" class="social-add-btn" id="musicAddBtn">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                </button>
                            </div>
                            <div class="social-links-list" id="musicLinksList"></div>
                            <small class="social-help">Cole um link de plataforma de música.</small>
                        </div>
                        <small id="musicLinkRequiredError" class="field-error" aria-live="polite">Adicione pelo menos um link no Espaço musical para continuar.</small>
                    </div>
                    <div class="form-group">
                        <label>Redes Sociais</label>
                        <div class="social-links-panel" id="socialLinksPanel" data-initial="<?= $savedSocialLinksJson ?>">
                            <div class="social-links-header">
                                <div class="social-header-info">
                                    <div class="social-icon-wrapper">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>
                                    </div>
                                    <div>
                                        <h4>Redes Sociais</h4>
                                        <small class="platform-list">Instagram, X, YouTube, TikTok e LinkedIn</small>
                                    </div>
                                </div>
                                <button type="button" class="social-add-btn" id="socialAddBtn">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                </button>
                            </div>
                            <div class="social-links-list" id="socialLinksList"></div>
                            <small class="social-help">Cole um link de rede social.</small>
                        </div>
                        <input type="hidden" name="social_links" id="socialLinksInput" value="<?= $savedSocialLinksJson ?>">
                        <small id="socialLinksError" class="field-error" aria-live="polite">Remova ou corrija os links sociais nao suportados para continuar.</small>
                    </div>
                    <div class="form-actions">
                        <button type="submit" name="action" value="back" class="btn-secondary">Voltar</button>
                        <button type="submit" name="action" value="continue" class="btn">Continuar</button>
                    </div>

                <?php elseif ($current_step == 4): ?>
                    <div class="form-group">
                        <label>Preview 1 (obrigatório)</label>
                        <input type="url" name="preview1" placeholder="https://..." value="<?= htmlspecialchars($_SESSION['registration_data']['preview1'] ?? '') ?>" required>
                        <small class="muted-note">Pode ser link YouTube, Spotify, SoundCloud, Apple Music, etc.</small>
                    </div>

                    <div class="form-group">
                        <label>Preview 2 (opcional)</label>
                        <input type="url" name="preview2" placeholder="https://..." value="<?= htmlspecialchars($_SESSION['registration_data']['preview2'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label>Preview 3 (opcional)</label>
                        <input type="url" name="preview3" placeholder="https://..." value="<?= htmlspecialchars($_SESSION['registration_data']['preview3'] ?? '') ?>">
                    </div>

                    <div class="form-actions">
                        <button type="submit" name="action" value="back" class="btn-secondary">Voltar</button>
                        <button type="submit" name="action" value="continue" class="btn">Continuar</button>
                    </div>

                <?php elseif ($current_step == 5): ?>
                    <div class="location-header">
                        <div class="switch-info">
                            <span class="switch-label">Localização Exata</span>
                            <span class="switch-desc">Concelho + Distrito</span>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="location_mode" id="toggleLocation" checked>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <div class="form-group" id="conselho-group">
                        <label>Concelho</label>
                        <div class="input-wrapper">
                            <input type="text" name="conselho" id="conselho" autocomplete="off" placeholder="Pesquisar..." value="<?= htmlspecialchars($_SESSION['registration_data']['conselho']??'') ?>">
                            <div id="conselho-suggestions" class="suggestions"></div>
                        </div>
                        <small id="conselho-error" class="field-error" aria-live="polite"></small>
                    </div>

                    <div class="form-group">
                        <label>Distrito</label>
                        <div class="input-wrapper">
                            <input type="text" name="district" id="district" autocomplete="off" placeholder="Pesquisar..." value="<?= htmlspecialchars($_SESSION['registration_data']['region']??'') ?>">
                            <div id="district-suggestions" class="suggestions"></div>
                        </div>
                        <small id="district-error" class="field-error" aria-live="polite"></small>
                    </div>

                    <div class="form-actions">
                        <button type="submit" name="action" value="back" class="btn-secondary">Voltar</button>
                        <button type="submit" name="action" value="continue" class="btn">Continuar</button>
                    </div>

                <?php elseif ($current_step == 6): ?>
                    <p class="muted-note" style="margin-bottom:10px;">Vê abaixo como o teu perfil vai aparecer no mapa antes de confirmares o teu email.</p>

                    <div class="registration-map-preview">
                        <div class="artist-modal-card">
                            <div class="artist-modal-header">
                                <img src="<?= htmlspecialchars($previewImageUrl) ?>" alt="Foto de <?= htmlspecialchars($previewName !== '' ? $previewName : 'Artista') ?>" class="artist-modal-avatar">
                                <div class="artist-modal-title-wrap">
                                    <h2 class="artist-modal-title"><?= htmlspecialchars($previewName !== '' ? $previewName : 'Artista') ?></h2>
                                    <span class="artist-modal-genre"><?= htmlspecialchars($previewGenre !== '' ? $previewGenre : 'Geral') ?></span>
                                </div>

                                <?php if (!empty($previewMusicApps)): ?>
                                    <div class="artist-modal-music-corner">
                                        <div class="artist-modal-social-links">
                                            <?php foreach ($previewMusicApps as $item): ?>
                                                <a class="artist-modal-social-link" href="<?= htmlspecialchars($item['url']) ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= htmlspecialchars($item['label']) ?>" title="<?= htmlspecialchars($item['label']) ?>">
                                                    <?php if (!empty($item['icon'])): ?>
                                                        <img src="<?= htmlspecialchars($item['icon']) ?>" alt="<?= htmlspecialchars($item['label']) ?>">
                                                    <?php else: ?>
                                                        <span><?= htmlspecialchars($item['label']) ?></span>
                                                    <?php endif; ?>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="artist-modal-section">
                                <h3>Biografia</h3>
                                <p><?= htmlspecialchars($previewBio !== '' ? $previewBio : 'Sem biografia disponível.') ?></p>
                            </div>

                            <?php if (!empty($previewTrackEmbeds)): ?>
                                <div class="artist-modal-section">
                                    <h3>Previews de músicas</h3>
                                    <div class="music-preview-grid">
                                        <?php foreach ($previewTrackEmbeds as $embed): ?>
                                            <?php if (!empty($embed['embed_src'])): ?>
                                                <iframe
                                                    src="<?= htmlspecialchars($embed['embed_src']) ?>"
                                                    width="100%"
                                                    height="<?= (int)($embed['embed_height'] ?? 240) ?>"
                                                    loading="lazy"
                                                    referrerpolicy="strict-origin-when-cross-origin"
                                                    allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"
                                                    allowfullscreen
                                                    title="Preview <?= htmlspecialchars($embed['provider']) ?>"
                                                ></iframe>
                                            <?php else: ?>
                                                <a class="music-preview-link-fallback" href="<?= htmlspecialchars($embed['url']) ?>" target="_blank" rel="noopener noreferrer">Ouvir preview</a>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="artist-modal-meta">
                                <div class="artist-modal-meta-layout">
                                    <div class="artist-modal-meta-left">
                                        <p><strong>Contacto:</strong> <?= htmlspecialchars(trim((string)($previewData['email'] ?? '')) !== '' ? (string)$previewData['email'] : 'Não disponível') ?></p>
                                        <p><strong>Distrito:</strong> <?= htmlspecialchars($previewDistrict !== '' ? $previewDistrict : 'N/A') ?></p>
                                        <p><strong>Concelho:</strong> <?= htmlspecialchars($previewCouncil !== '' ? $previewCouncil : 'N/A') ?></p>
                                    </div>
                                    <div class="artist-modal-meta-right">
                                        <?php if (!empty($previewSocialNetworks)): ?>
                                            <div class="artist-modal-meta-social">
                                                <p><strong>Redes sociais:</strong></p>
                                                <div class="artist-modal-social-links">
                                                    <?php foreach ($previewSocialNetworks as $item): ?>
                                                        <a class="artist-modal-social-link" href="<?= htmlspecialchars($item['url']) ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= htmlspecialchars($item['label']) ?>" title="<?= htmlspecialchars($item['label']) ?>">
                                                            <?php if (!empty($item['icon'])): ?>
                                                                <img src="<?= htmlspecialchars($item['icon']) ?>" alt="<?= htmlspecialchars($item['label']) ?>">
                                                            <?php else: ?>
                                                                <span><?= htmlspecialchars($item['label']) ?></span>
                                                            <?php endif; ?>
                                                        </a>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" name="action" value="back" class="btn-secondary">Voltar</button>
                        <button type="submit" name="action" value="register" class="btn">Confirmar e Receber Email</button>
                    </div>
                <?php endif; ?>
            </form>
        <?php endif; ?>
    </div>
</div>

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
            <button type="button" class="btn-secondary" id="cancelCropBtn">Cancelar</button>
            <button type="button" class="btn" id="applyCropBtn">Aplicar recorte</button>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const form = document.querySelector('.register-form');

    // Password Strength Validation
    const passwordInput = document.getElementById('password');
    const passwordStrengthMeter = document.getElementById('password-strength');
    const lengthCheck = document.getElementById('length-check');
    const upperCheck = document.getElementById('upper-check');
    const lowerCheck = document.getElementById('lower-check');
    const numberCheck = document.getElementById('number-check');

    if (passwordInput && passwordStrengthMeter) {
        passwordInput.addEventListener('input', function() {
            const val = this.value;
            if (val.length > 0) {
                passwordStrengthMeter.style.display = 'block';
            } else {
                passwordStrengthMeter.style.display = 'none';
            }

            // Length check
            if (val.length >= 6) {
                lengthCheck.classList.add('valid');
            } else {
                lengthCheck.classList.remove('valid');
            }

            // Uppercase check
            if (/[A-Z]/.test(val)) {
                upperCheck.classList.add('valid');
            } else {
                upperCheck.classList.remove('valid');
            }

            // Lowercase check
            if (/[a-z]/.test(val)) {
                lowerCheck.classList.add('valid');
            } else {
                lowerCheck.classList.remove('valid');
            }

            // Number check
            if (/[0-9]/.test(val)) {
                numberCheck.classList.add('valid');
            } else {
                numberCheck.classList.remove('valid');
            }
        });
    }

    // 0. Genre Pill Toggle
    const genreOptions = Array.from(document.querySelectorAll('.genre-option'));

        // --- Comportamento igual ao editar artista ---
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
                    .replace(/\u0300-\u036f/g, '')
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

    // (Removido bloco duplicado de filtro e validação de géneros. Agora só o bloco igual ao editar artista controla a UI dos géneros.)

    // 1. Upload + Remoção da Foto de Perfil
    const profileInput = document.getElementById('profilePictureInput');
    const profileUploadCard = document.getElementById('profileUploadCard');
    const profilePreviewBox = document.getElementById('profilePreviewBox');
    const profilePreviewImg = document.getElementById('profilePreviewImg');
    const openCropBtn = document.getElementById('openCropBtn');
    const removeProfilePictureBtn = document.getElementById('removeProfilePictureBtn');
    const removeProfilePictureFlag = document.getElementById('removeProfilePictureFlag');
    const profileFileName = document.getElementById('profileFileName');
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

    function isValidProfileFile(file) {
        if (!file) return false;

        const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        const validByMime = validTypes.includes((file.type || '').toLowerCase());
        const validByExtension = /\.(jpg|jpeg|png)$/i.test(file.name || '');
        const underMaxSize = file.size <= 5000000;

        return (validByMime || validByExtension) && underMaxSize;
    }

    function setCropButtonState(hasImage) {
        if (openCropBtn) {
            openCropBtn.disabled = !hasImage;
        }
        if (removeProfilePictureBtn) {
            removeProfilePictureBtn.disabled = !hasImage;
        }
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
            setCropButtonState(true);
        };
        reader.readAsDataURL(file);
    }

    function clearProfilePreview(markForRemoval = false) {
        if (profileInput) profileInput.value = '';
        if (profilePreviewImg) profilePreviewImg.src = '';
        if (profilePreviewBox) profilePreviewBox.classList.remove('has-image');
        if (profileFileName) profileFileName.textContent = markForRemoval ? 'A foto será removida ao continuar.' : '';
        if (removeProfilePictureBtn) removeProfilePictureBtn.classList.remove('active');
        if (removeProfilePictureFlag) removeProfilePictureFlag.value = markForRemoval ? '1' : '0';
        setCropButtonState(false);
    }

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

    const hasExistingPreview = Boolean(profilePreviewImg?.getAttribute('src'));
    setCropButtonState(hasExistingPreview);

    // 2. Social links
    const socialPanel = document.getElementById('socialLinksPanel');
    const musicPanel = document.getElementById('musicLinksPanel');
    if (socialPanel || musicPanel) {
        const socialList = document.getElementById('socialLinksList');
        const socialAddBtn = document.getElementById('socialAddBtn');
        const musicList = document.getElementById('musicLinksList');
        const musicAddBtn = document.getElementById('musicAddBtn');
        const socialInput = document.getElementById('socialLinksInput');
        const musicLinkRequiredError = document.getElementById('musicLinkRequiredError');
        const socialLinksError = document.getElementById('socialLinksError');

        const ICONS = {
            link: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 13a5 5 0 0 0 7.07 0l2.12-2.12a5 5 0 0 0-7.07-7.07L10 5" fill="none" stroke="#8a8a8a" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 11a5 5 0 0 0-7.07 0L4.8 13.12a5 5 0 1 0 7.07 7.07L14 19" fill="none" stroke="#8a8a8a" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            instagram: '<img src="assets/social/instagram.png" alt="Instagram">',
            x: '<img src="assets/social/x.png" alt="X (Twitter)">',
            youtube: '<img src="assets/social/youtube.png" alt="YouTube">',
            youtube_music: '<img src="assets/social/youtubemusic-com.png" alt="YouTube Music">',
            tiktok: '<img src="assets/social/tiktok.png" alt="TikTok">',
            linkedin: '<img src="assets/social/linkedin.png" alt="LinkedIn">',
            tidal: '<img src="assets/social/tidal.png" alt="Tidal">',
            spotify: '<img src="assets/social/spotify.png" alt="Spotify">',
            soundcloud: '<img src="assets/social/soundcloud.png" alt="SoundCloud">',
            apple_music: '<img src="assets/social/apple.png" alt="Apple Music">',
            bandcamp: '<img src="assets/social/bandcamp.png" alt="Bandcamp">'
        };

        const PROVIDERS = [
            { key: 'instagram', label: 'Instagram', domains: ['instagram.com'], category: 'social' },
            { key: 'x', label: 'X (Twitter)', domains: ['x.com', 'twitter.com'], category: 'social' },
            { key: 'youtube_music', label: 'YouTube Music', domains: ['music.youtube.com'], category: 'music' },
            { key: 'youtube', label: 'YouTube', domains: ['youtube.com', 'youtu.be'], category: 'social' },
            { key: 'tiktok', label: 'TikTok', domains: ['tiktok.com'], category: 'social' },
            { key: 'linkedin', label: 'LinkedIn', domains: ['linkedin.com'], category: 'social' },
            { key: 'tidal', label: 'Tidal', domains: ['tidal.com'], category: 'music' },
            { key: 'spotify', label: 'Spotify', domains: ['spotify.com', 'open.spotify.com'], category: 'music' },
            { key: 'soundcloud', label: 'SoundCloud', domains: ['soundcloud.com'], category: 'music' },
            { key: 'apple_music', label: 'Apple Music', domains: ['music.apple.com'], category: 'music' },
            { key: 'bandcamp', label: 'Bandcamp', domains: ['bandcamp.com'], category: 'music' }
        ];

        const MUSIC_PROVIDER_KEYS = new Set(['tidal', 'spotify', 'soundcloud', 'apple_music', 'youtube_music', 'bandcamp']);
        const SOCIAL_PROVIDER_KEYS = new Set(['instagram', 'x', 'youtube', 'tiktok', 'linkedin']);

        const safeParse = (value) => {
            try {
                return JSON.parse(value || '{}');
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
            if (!socialInput) return;
            const payload = {};
            const rows = [
                ...(socialList ? Array.from(socialList.children) : []),
                ...(musicList ? Array.from(musicList.children) : [])
            ];

            rows.forEach((row) => {
                const input = row.querySelector('input');
                if (!input) return;
                const rowCategory = row.dataset.category || '';
                const provider = detectProvider(input.value);
                if (!provider || provider.category !== rowCategory) return;
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

            if (musicLinkRequiredError) {
                const hasMusicLink = Object.keys(payload).some((key) => MUSIC_PROVIDER_KEYS.has(key));
                musicLinkRequiredError.classList.toggle('visible', !hasMusicLink);
            }

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

            const provider = detectProvider(rawValue);
            if (!provider) return true;
            return provider.category !== (row.dataset.category || '');
        };

        const hasInvalidSocialRows = () => {
            const rows = [
                ...(socialList ? Array.from(socialList.children) : []),
                ...(musicList ? Array.from(musicList.children) : [])
            ];
            return rows.some((row) => isRowInvalid(row));
        };

        const ensureEmptyRowForList = (listEl, category) => {
            if (!listEl) return;
            if (listEl.children.length === 0) {
                listEl.appendChild(createSocialRow(category, ''));
            }
        };

        const fillRowInput = (row, value) => {
            const input = row?.querySelector('input');
            if (!input) return;

            input.value = value;
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('blur'));
        };

        const moveValueToTargetList = (targetList, targetCategory, rowValue) => {
            if (!targetList) return;

            const rows = Array.from(targetList.children);
            const hasAnyFilledRow = rows.some((targetRow) => {
                const targetInput = targetRow.querySelector('input');
                return String(targetInput?.value || '').trim() !== '';
            });

            if (!hasAnyFilledRow) {
                const rowToFill = rows[0] || createSocialRow(targetCategory, '');
                if (!rows[0]) {
                    targetList.appendChild(rowToFill);
                }
                fillRowInput(rowToFill, rowValue);
                return;
            }

            targetList.appendChild(createSocialRow(targetCategory, rowValue));
        };

        const moveRowToCategory = (row, targetCategory) => {
            if (!row || !targetCategory) return;
            const sourceCategory = row.dataset.category || '';
            if (sourceCategory === targetCategory) return;

            const input = row.querySelector('input');
            const rowValue = normalizeUrl(input?.value || '');
            const targetList = targetCategory === 'music' ? musicList : socialList;
            const sourceList = sourceCategory === 'music' ? musicList : socialList;

            if (!targetList || !sourceList) return;

            row.remove();
            moveValueToTargetList(targetList, targetCategory, rowValue);
            ensureEmptyRowForList(sourceList, sourceCategory);
            syncSocialLinks();
        };

        const maybeAutoMoveRow = (row, category) => {
            const input = row?.querySelector('input');
            if (!input) return false;

            const rawValue = String(input.value || '').trim();
            if (rawValue.length < 6 || !rawValue.includes('.')) {
                return false;
            }

            const provider = detectProvider(rawValue);
            if (provider && provider.category !== category) {
                moveRowToCategory(row, provider.category);
                return true;
            }

            return false;
        };

        const createSocialRow = (category, value = '') => {
            const row = document.createElement('div');
            row.className = 'social-link-row';
            row.dataset.category = category;
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
                const provider = detectProvider(rawValue);
                const matchesCategory = provider && provider.category === category;
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
                } else if (matchesCategory) {
                    row.classList.add('active');
                    row.classList.remove('mismatch');
                    if (icon) icon.innerHTML = ICONS[provider.key] || ICONS.link;
                    if (label) {
                        label.classList.remove('warning');
                        label.textContent = provider.label;
                    }
                } else if (provider && !matchesCategory) {
                    row.classList.remove('active');
                    row.classList.add('mismatch');
                    if (icon) icon.innerHTML = ICONS[provider.key] || ICONS.link;
                    const mismatchMessage = category === 'music'
                        ? `Este link e de ${provider.label}. Use a caixa de Redes Sociais.`
                        : `Este link e de ${provider.label}. Use a caixa do Espaco Musical.`;
                    if (label) {
                        label.classList.add('warning');
                        label.textContent = mismatchMessage;
                    }
                    if (input) {
                        input.setCustomValidity(mismatchMessage);
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
                let autoMoveTimer = null;
                input.addEventListener('input', () => {
                    refresh();

                    if (autoMoveTimer) {
                        clearTimeout(autoMoveTimer);
                    }

                    autoMoveTimer = setTimeout(() => {
                        maybeAutoMoveRow(row, category);
                    }, 420);
                });
                input.addEventListener('blur', () => {
                    if (autoMoveTimer) {
                        clearTimeout(autoMoveTimer);
                    }

                    const normalized = normalizeUrl(input.value);
                    if (normalized) {
                        input.value = normalized;
                    }

                    if (maybeAutoMoveRow(row, category)) {
                        return;
                    }

                    refresh();
                });
            }

            if (removeBtn) {
                removeBtn.addEventListener('click', () => {
                    row.remove();
                    const targetList = category === 'music' ? musicList : socialList;
                    ensureEmptyRowForList(targetList, category);
                    syncSocialLinks();
                });
            }

            refresh();
            return row;
        };

        const initial = safeParse((socialPanel?.getAttribute('data-initial') || musicPanel?.getAttribute('data-initial') || ''));
        if (musicList) {
            const musicEntries = Object.entries(initial || {}).filter(([key]) => MUSIC_PROVIDER_KEYS.has(key));
            if (musicEntries.length) {
                musicEntries.forEach(([, url]) => musicList.appendChild(createSocialRow('music', url)));
            } else {
                musicList.appendChild(createSocialRow('music', ''));
            }
        }

        if (socialList) {
            const socialEntries = Object.entries(initial || {}).filter(([key]) => SOCIAL_PROVIDER_KEYS.has(key));
            if (socialEntries.length) {
                socialEntries.forEach(([, url]) => socialList.appendChild(createSocialRow('social', url)));
            } else {
                socialList.appendChild(createSocialRow('social', ''));
            }
        }

        if (socialAddBtn && socialList) {
            socialAddBtn.addEventListener('click', () => {
                socialList.appendChild(createSocialRow('social', ''));
            });
        }

        if (musicAddBtn && musicList) {
            musicAddBtn.addEventListener('click', () => {
                musicList.appendChild(createSocialRow('music', ''));
            });
        }

        if (form && musicPanel && socialInput) {
            form.addEventListener('submit', (event) => {
                const submitAction = event.submitter?.value || '';
                if (submitAction !== 'continue') return;

                const hasInvalid = hasInvalidSocialRows();
                if (hasInvalid) {
                    event.preventDefault();
                    if (socialLinksError) {
                        socialLinksError.classList.add('visible');
                    }
                    return;
                }

                if (socialLinksError) {
                    socialLinksError.classList.remove('visible');
                }

                const payload = safeParse(socialInput.value || '{}');
                const hasMusicLink = Object.keys(payload).some((key) => MUSIC_PROVIDER_KEYS.has(key));
                if (hasMusicLink) {
                    if (musicLinkRequiredError) {
                        musicLinkRequiredError.classList.remove('visible');
                    }
                    return;
                }

                event.preventDefault();
                if (musicLinkRequiredError) {
                    musicLinkRequiredError.classList.add('visible');
                }
            });
        }

        syncSocialLinks();
    }

    // 3. Lógica do Switch
    const toggle = document.getElementById('toggleLocation');
    const mGroup = document.getElementById('conselho-group');
    if (toggle && mGroup) {
        const update = () => mGroup.style.display = toggle.checked ? 'block' : 'none';
        toggle.addEventListener('change', update);
        update();
    }

    // 4. GeoNames + validação em tempo real
    const GN_USER = 'vascovale';
    const debounce = (fn, delay) => { let t; return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), delay); }; };
    const locationForm = form;

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

    function showFieldError(input, errorEl, message) {
        if (!errorEl || !input) return;
        errorEl.textContent = message;
        errorEl.classList.add('visible');
        input.classList.add('invalid-location');
    }

    function clearFieldError(input, errorEl) {
        if (!errorEl || !input) return;
        errorEl.textContent = '';
        errorEl.classList.remove('visible');
        input.classList.remove('invalid-location');
    }

    async function validateLocationExact(query, type, district = '') {
        const value = String(query || '').trim();
        if (!value) {
            return { valid: false };
        }

        const fCode = type === 'district' ? 'ADM1' : 'ADM2';
        const params = new URLSearchParams({
            username: GN_USER,
            country: 'PT',
            featureCode: fCode,
            name_equals: value,
            maxRows: '10',
        });

        try {
            const res = await fetch(`https://secure.geonames.org/searchJSON?${params.toString()}`);
            const data = await res.json();
            const list = Array.isArray(data.geonames) ? data.geonames : [];
            const wantedName = (type === 'district' ? normalizeDistrictName(value) : normalizeCouncilName(value)).toLowerCase();
            const wantedDistrict = normalizeDistrictName(district).toLowerCase();

            for (const item of list) {
                const itemName = type === 'district'
                    ? normalizeDistrictName(item?.name || '')
                    : normalizeCouncilName(item?.name || '');
                const itemDistrict = normalizeDistrictName(item?.adminName1 || '');

                if (!itemName || itemName.toLowerCase() !== wantedName) {
                    continue;
                }

                if (type === 'concelho' && wantedDistrict && itemDistrict.toLowerCase() !== wantedDistrict) {
                    continue;
                }

                return { valid: true, name: itemName, district: itemDistrict };
            }
        } catch (e) {
            console.error(e);
        }

        return { valid: false };
    }

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
                    const itemName = type === 'district'
                        ? normalizeDistrictName(item.name || '')
                        : normalizeCouncilName(item.name || '');
                    const itemDistrict = normalizeDistrictName(item.adminName1 || '');
                    const div = document.createElement('div');
                    div.className = 'suggestion-item';
                    div.textContent = itemName + (type === 'concelho' ? ` (${itemDistrict})` : '');
                    div.onclick = () => {
                        inputRef.value = itemName;
                        if(type === 'concelho' && document.getElementById('district'))
                            document.getElementById('district').value = itemDistrict;

                        if (type === 'district') {
                            clearFieldError(dIn, districtErrorEl);
                        } else {
                            clearFieldError(mIn, councilErrorEl);
                            clearFieldError(dIn, districtErrorEl);
                        }

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
    const dIn = document.getElementById('district');
    const dSug = document.getElementById('district-suggestions');
    const districtErrorEl = document.getElementById('district-error');
    const councilErrorEl = document.getElementById('conselho-error');

    let districtValidationSeq = 0;
    let councilValidationSeq = 0;
    let allowValidatedSubmit = false;

    const validateDistrictLive = debounce(async () => {
        if (!dIn) return true;
        const value = dIn.value.trim();
        if (!value) {
            clearFieldError(dIn, districtErrorEl);
            return false;
        }

        const seq = ++districtValidationSeq;
        const result = await validateLocationExact(value, 'district');
        if (seq !== districtValidationSeq) return false;

        if (!result.valid) {
            showFieldError(dIn, districtErrorEl, 'Este distrito não existe.');
            return false;
        }

        dIn.value = result.name;
        clearFieldError(dIn, districtErrorEl);
        return true;
    }, 450);

    const validateCouncilLive = debounce(async () => {
        if (!mIn || !toggle?.checked) return true;
        const value = mIn.value.trim();
        if (!value) {
            clearFieldError(mIn, councilErrorEl);
            return false;
        }

        const seq = ++councilValidationSeq;
        const result = await validateLocationExact(value, 'concelho', dIn?.value || '');
        if (seq !== councilValidationSeq) return false;

        if (!result.valid) {
            showFieldError(mIn, councilErrorEl, 'Este concelho não existe para o distrito indicado.');
            return false;
        }

        mIn.value = result.name;
        if (dIn && result.district) {
            dIn.value = result.district;
            clearFieldError(dIn, districtErrorEl);
        }
        clearFieldError(mIn, councilErrorEl);
        return true;
    }, 500);

    if (mIn) {
        mIn.oninput = () => {
            searchGeo(mIn.value, 'concelho', mSug, mIn);
            clearFieldError(mIn, councilErrorEl);
            validateCouncilLive();
        };
        mIn.addEventListener('blur', () => {
            if (mIn.value.trim()) validateCouncilLive();
        });
    }

    if (dIn) {
        if (dIn.value.trim()) {
            dIn.value = normalizeDistrictName(dIn.value);
        }
        dIn.oninput = () => {
            searchGeo(dIn.value, 'district', dSug, dIn);
            clearFieldError(dIn, districtErrorEl);
            validateDistrictLive();
        };
        dIn.addEventListener('blur', () => {
            if (dIn.value.trim()) validateDistrictLive();
        });
    }

    if (toggle) {
        toggle.addEventListener('change', () => {
            if (!toggle.checked) {
                clearFieldError(mIn, councilErrorEl);
            } else if (mIn?.value.trim()) {
                validateCouncilLive();
            }
        });
    }

    if (locationForm && dIn) {
        locationForm.addEventListener('submit', async (event) => {
            if (allowValidatedSubmit) return;
            const submitAction = event.submitter?.value || '';
            if (submitAction !== 'continue') return;

            event.preventDefault();

            let hasError = false;
            if (!dIn.value.trim()) {
                showFieldError(dIn, districtErrorEl, 'O distrito é obrigatório.');
                hasError = true;
            }

            const districtResult = dIn.value.trim()
                ? await validateLocationExact(dIn.value.trim(), 'district')
                : { valid: false };

            if (!districtResult.valid) {
                showFieldError(dIn, districtErrorEl, 'Este distrito não existe.');
                hasError = true;
            } else {
                dIn.value = districtResult.name;
                clearFieldError(dIn, districtErrorEl);
            }

            if (toggle?.checked && mIn) {
                if (!mIn.value.trim()) {
                    showFieldError(mIn, councilErrorEl, 'Indique um concelho válido.');
                    hasError = true;
                } else {
                    const councilResult = await validateLocationExact(
                        mIn.value.trim(),
                        'concelho',
                        dIn.value.trim()
                    );

                    if (!councilResult.valid) {
                        showFieldError(mIn, councilErrorEl, 'Este concelho não existe para o distrito indicado.');
                        hasError = true;
                    } else {
                        mIn.value = councilResult.name;
                        if (councilResult.district && dIn.value.trim() === '') {
                            dIn.value = councilResult.district;
                        }
                        clearFieldError(mIn, councilErrorEl);
                    }
                }
            }

            if (hasError) return;

            allowValidatedSubmit = true;
            locationForm.requestSubmit(event.submitter);
        });
    }

    // 5. Animation on Submit
    const regForm = document.querySelector('.register-form');
    if (regForm) {
        regForm.addEventListener('submit', function(e) {
            if (e.defaultPrevented) return;

            e.preventDefault();
            regForm.classList.add('step-exit');

            if (e.submitter && e.submitter.name) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = e.submitter.name;
                input.value = e.submitter.value;
                regForm.appendChild(input);
            }

            setTimeout(() => regForm.submit(), 300);
        });
    }
});
</script>

<?php require __DIR__ . '/../inc/footer.php'; ?>