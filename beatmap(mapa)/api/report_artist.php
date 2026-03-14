<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
    exit;
}

$reporterToken = null;
if (isset($_SESSION['artist_id'])) {
    $reporterToken = 'artist_' . (int)$_SESSION['artist_id'];
} elseif (isset($_SESSION['user_id'])) {
    $reporterToken = 'user_' . (int)$_SESSION['user_id'];
}

if ($reporterToken === null) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'É necessário iniciar sessão para denunciar.',
    ]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    $input = $_POST;
}

$artistId = (int)($input['artist_id'] ?? 0);
if ($artistId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'artist_id inválido.']);
    exit;
}

$reason = trim((string)($input['reason'] ?? ''));
if (function_exists('mb_substr')) {
    $reason = mb_substr($reason, 0, 600);
} else {
    $reason = substr($reason, 0, 600);
}

function ensureArtistReportsTable(PDO $pdo): void {
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS artist_reports (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            artist_id INT NOT NULL,
            reporter_token VARCHAR(64) NOT NULL,
            reason VARCHAR(600) DEFAULT NULL,
            status ENUM("pending", "reviewed", "dismissed") NOT NULL DEFAULT "pending",
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_artist_reporter (artist_id, reporter_token),
            KEY idx_artist_reports_artist (artist_id),
            KEY idx_artist_reports_status (status),
            CONSTRAINT fk_artist_reports_artist
                FOREIGN KEY (artist_id)
                REFERENCES artists(id)
                ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
    );
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

    ensureArtistReportsTable($pdo);

    $artistCheckStmt = $pdo->prepare('SELECT id FROM artists WHERE id = ? AND is_confirmed = 1 LIMIT 1');
    $artistCheckStmt->execute([$artistId]);
    if (!$artistCheckStmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Artista não encontrado.']);
        exit;
    }

    $insertStmt = $pdo->prepare(
        'INSERT INTO artist_reports (artist_id, reporter_token, reason)
         VALUES (:artist_id, :reporter_token, :reason)
         ON DUPLICATE KEY UPDATE
             reason = VALUES(reason),
             status = "pending"'
    );

    $insertStmt->execute([
        ':artist_id' => $artistId,
        ':reporter_token' => $reporterToken,
        ':reason' => $reason !== '' ? $reason : null,
    ]);

    echo json_encode([
        'success' => true,
        'artist_id' => $artistId,
        'message' => 'Denúncia enviada. Obrigado pelo feedback.',
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao enviar denúncia.',
    ]);
}
