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

$senderArtistId = (int)($_SESSION['artist_id'] ?? 0);
if ($senderArtistId <= 0) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Apenas artistas podem enviar mensagens privadas.',
    ]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    $input = $_POST;
}

$recipientArtistId = (int)($input['recipient_artist_id'] ?? 0);
$message = trim((string)($input['message'] ?? ''));

if ($recipientArtistId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'recipient_artist_id inválido.']);
    exit;
}

if ($recipientArtistId === $senderArtistId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Não podes enviar mensagem para a tua própria conta.']);
    exit;
}

if ($message === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'A mensagem não pode estar vazia.']);
    exit;
}

if (function_exists('mb_strlen')) {
    if (mb_strlen($message, 'UTF-8') > 255) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'A mensagem deve ter no máximo 255 caracteres.']);
        exit;
    }
} elseif (strlen($message) > 255) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'A mensagem deve ter no máximo 255 caracteres.']);
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

function ensurePrivateMessagesTable(PDO $pdo): void {
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS private_messages (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            sender_artist_id INT NOT NULL,
            recipient_artist_id INT NOT NULL,
            message VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            read_at TIMESTAMP NULL DEFAULT NULL,
            KEY idx_private_messages_recipient (recipient_artist_id, created_at),
            KEY idx_private_messages_sender (sender_artist_id, created_at),
            CONSTRAINT fk_private_messages_sender
                FOREIGN KEY (sender_artist_id)
                REFERENCES artists(id)
                ON DELETE CASCADE,
            CONSTRAINT fk_private_messages_recipient
                FOREIGN KEY (recipient_artist_id)
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
    ensurePrivateMessagesTable($pdo);

    $senderStmt = $pdo->prepare('SELECT id, moderation_status, is_confirmed FROM artists WHERE id = ? LIMIT 1');
    $senderStmt->execute([$senderArtistId]);
    $sender = $senderStmt->fetch(PDO::FETCH_ASSOC);

    if (!$sender || (int)($sender['is_confirmed'] ?? 0) !== 1 || (string)($sender['moderation_status'] ?? 'approved') !== 'approved') {
        $_SESSION['artist_moderation_status'] = (string)($sender['moderation_status'] ?? 'pending');
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'A tua conta ainda está pendente. Não podes enviar mensagens privadas.',
        ]);
        exit;
    }

    $_SESSION['artist_moderation_status'] = (string)($sender['moderation_status'] ?? 'approved');

    $recipientStmt = $pdo->prepare('SELECT id, name, is_confirmed FROM artists WHERE id = ? LIMIT 1');
    $recipientStmt->execute([$recipientArtistId]);
    $recipient = $recipientStmt->fetch(PDO::FETCH_ASSOC);

    if (!$recipient || (int)($recipient['is_confirmed'] ?? 0) !== 1) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Artista de destino não encontrado.']);
        exit;
    }

    $insertStmt = $pdo->prepare(
        'INSERT INTO private_messages (sender_artist_id, recipient_artist_id, message)
         VALUES (:sender_artist_id, :recipient_artist_id, :message)'
    );
    $insertStmt->execute([
        ':sender_artist_id' => $senderArtistId,
        ':recipient_artist_id' => $recipientArtistId,
        ':message' => $message,
    ]);

    $messageId = (int)$pdo->lastInsertId();

    $fetchStmt = $pdo->prepare(
        'SELECT pm.id, pm.sender_artist_id, pm.recipient_artist_id, pm.message, pm.created_at,
                s.name AS sender_name, r.name AS recipient_name
         FROM private_messages pm
         INNER JOIN artists s ON s.id = pm.sender_artist_id
         INNER JOIN artists r ON r.id = pm.recipient_artist_id
         WHERE pm.id = ? LIMIT 1'
    );
    $fetchStmt->execute([$messageId]);
    $inserted = $fetchStmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'message' => 'Mensagem privada enviada.',
        'private_message' => $inserted,
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao enviar mensagem privada.',
    ]);
}
