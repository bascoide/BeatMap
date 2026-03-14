<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$cookieDomain = $_SERVER['HTTP_HOST'] ?? '';
$cookieDomain = preg_replace('/:\\d+$/', '', $cookieDomain);
if ($cookieDomain === 'localhost' || $cookieDomain === '') {
    session_set_cookie_params(0, '/');
} else {
    session_set_cookie_params(0, '/', '.' . $cookieDomain);
}
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
    exit;
}

if (!isset($_SESSION['user_id']) && !isset($_SESSION['artist_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'É necessário iniciar sessão.']);
    exit;
}

$artistId = (int)($_GET['id'] ?? 0);
if ($artistId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'id inválido.']);
    exit;
}

function ensureArtistModerationStatusColumn(PDO $pdo): void {
    $stmt = $pdo->query("SHOW COLUMNS FROM artists LIKE 'moderation_status'");
    $column = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;
    $exists = (bool)$column;
    $columnType = strtolower((string)($column['Type'] ?? ''));
    if (!$exists) {
        $pdo->exec("ALTER TABLE artists ADD COLUMN moderation_status ENUM('pending','approved','rejected','banned') NOT NULL DEFAULT 'approved' AFTER is_confirmed");
    } elseif ($columnType !== '' && strpos($columnType, "'banned'") === false) {
        $pdo->exec("ALTER TABLE artists MODIFY COLUMN moderation_status ENUM('pending','approved','rejected','banned') NOT NULL DEFAULT 'approved'");
    }
}

try {
    $host = 'localhost';
    $db = 'beatmap';
    $user = 'root';
    $pass = '';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    ensureArtistModerationStatusColumn($pdo);

    $stmt = $pdo->prepare('SELECT * FROM artists WHERE id = ? AND is_confirmed = 1 AND moderation_status = "approved" LIMIT 1');
    $stmt->execute([$artistId]);
    $artist = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$artist) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Artista não encontrado.']);
        exit;
    }

    $profilePicture = (string)($artist['profile_picture'] ?? '');
    if ($profilePicture !== '') {
        if (preg_match('/^https?:\\/\\//i', $profilePicture) || str_starts_with($profilePicture, '/')) {
            $artist['image'] = $profilePicture;
        } else {
            $artist['image'] = '/beatmap/' . ltrim($profilePicture, '/');
        }
    } else {
        $artist['image'] = null;
    }

    echo json_encode([
        'success' => true,
        'artist' => $artist,
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao carregar perfil do artista.',
    ]);
}
