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

$artistId = (int)($_SESSION['artist_id'] ?? 0);
if ($artistId <= 0) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Apenas artistas podem enviar mensagens no chat geral.',
    ]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    $input = $_POST;
}

$message = trim((string)($input['message'] ?? ''));
if ($message === '') {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'A mensagem não pode estar vazia.',
    ]);
    exit;
}

if (function_exists('mb_strlen')) {
    if (mb_strlen($message, 'UTF-8') > 255) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'A mensagem deve ter no máximo 255 caracteres.',
        ]);
        exit;
    }
} elseif (strlen($message) > 255) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'A mensagem deve ter no máximo 255 caracteres.',
    ]);
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

function ensureGlobalChatTable(PDO $pdo): void {
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS global_chat_messages (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            artist_id INT NOT NULL,
            message VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            KEY idx_global_chat_created (created_at),
            KEY idx_global_chat_artist (artist_id),
            CONSTRAINT fk_global_chat_artist
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

    ensureArtistModerationStatusColumn($pdo);
    ensureGlobalChatTable($pdo);

    $artistStmt = $pdo->prepare('SELECT id, name, moderation_status, is_confirmed FROM artists WHERE id = ? LIMIT 1');
    $artistStmt->execute([$artistId]);
    $artist = $artistStmt->fetch(PDO::FETCH_ASSOC);

    if (!$artist || (int)$artist['is_confirmed'] !== 1 || (string)($artist['moderation_status'] ?? 'approved') !== 'approved') {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Só artistas aprovados podem enviar mensagens no chat geral.',
        ]);
        exit;
    }

    $_SESSION['artist_moderation_status'] = (string)($artist['moderation_status'] ?? 'approved');

    $insertStmt = $pdo->prepare('INSERT INTO global_chat_messages (artist_id, message) VALUES (:artist_id, :message)');
    $insertStmt->execute([
        ':artist_id' => $artistId,
        ':message' => $message,
    ]);

    $messageId = (int)$pdo->lastInsertId();

    $fetchStmt = $pdo->prepare(
        'SELECT m.id, m.artist_id, m.message, m.created_at, a.name AS artist_name
         FROM global_chat_messages m
         INNER JOIN artists a ON a.id = m.artist_id
         WHERE m.id = ? LIMIT 1'
    );
    $fetchStmt->execute([$messageId]);
    $insertedMessage = $fetchStmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'message' => 'Mensagem enviada.',
        'chat_message' => $insertedMessage,
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao enviar mensagem para o chat geral.',
    ]);
}
