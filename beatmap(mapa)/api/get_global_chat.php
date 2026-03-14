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
    echo json_encode(['success' => false, 'message' => 'É necessário iniciar sessão para ver o chat.']);
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

    $afterId = (int)($_GET['after_id'] ?? 0);
    if ($afterId < 0) {
        $afterId = 0;
    }

    $limit = (int)($_GET['limit'] ?? 80);
    if ($limit <= 0) {
        $limit = 80;
    }
    $limit = min($limit, 100);

    if ($afterId > 0) {
        $stmt = $pdo->prepare(
            'SELECT m.id, m.artist_id, m.message, m.created_at, a.name AS artist_name
             FROM global_chat_messages m
             INNER JOIN artists a ON a.id = m.artist_id
             WHERE m.id > :after_id
               AND a.is_confirmed = 1
               AND a.moderation_status = "approved"
             ORDER BY m.id ASC
             LIMIT :limit'
        );
        $stmt->bindValue(':after_id', $afterId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $messages = $stmt->fetchAll();
    } else {
        $stmt = $pdo->prepare(
            'SELECT * FROM (
                SELECT m.id, m.artist_id, m.message, m.created_at, a.name AS artist_name
                FROM global_chat_messages m
                INNER JOIN artists a ON a.id = m.artist_id
                WHERE a.is_confirmed = 1
                  AND a.moderation_status = "approved"
                ORDER BY m.id DESC
                LIMIT :limit
            ) recent_messages
            ORDER BY id ASC'
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $messages = $stmt->fetchAll();
    }

    $maxId = 0;
    foreach ($messages as $row) {
        $rowId = (int)($row['id'] ?? 0);
        if ($rowId > $maxId) {
            $maxId = $rowId;
        }
    }

    echo json_encode([
        'success' => true,
        'messages' => $messages,
        'last_id' => $maxId,
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao carregar chat global.',
    ]);
}
