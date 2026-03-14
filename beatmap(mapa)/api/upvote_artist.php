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

$voterToken = null;
if (isset($_SESSION['artist_id'])) {
    $voterToken = 'artist_' . (int)$_SESSION['artist_id'];
} elseif (isset($_SESSION['user_id'])) {
    $voterToken = 'user_' . (int)$_SESSION['user_id'];
}

if ($voterToken === null) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'É necessário iniciar sessão para votar.']);
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

function ensureArtistUpvotesTable(PDO $pdo): void {
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS artist_upvotes (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            artist_id INT NOT NULL,
            voter_token VARCHAR(64) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_artist_voter (artist_id, voter_token),
            KEY idx_artist_upvotes_artist (artist_id),
            CONSTRAINT fk_artist_upvotes_artist
                FOREIGN KEY (artist_id)
                REFERENCES artists(id)
                ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
    );
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
    ensureArtistUpvotesTable($pdo);

    if (isset($_SESSION['artist_id'])) {
        $artistSessionId = (int)$_SESSION['artist_id'];
        $statusStmt = $pdo->prepare('SELECT moderation_status FROM artists WHERE id = ? LIMIT 1');
        $statusStmt->execute([$artistSessionId]);
        $moderationStatus = (string)($statusStmt->fetchColumn() ?: 'approved');
        $_SESSION['artist_moderation_status'] = $moderationStatus;

        if ($moderationStatus !== 'approved') {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'Só artistas aprovados podem dar upvote.',
            ]);
            exit;
        }
    }

    $artistCheckStmt = $pdo->prepare('SELECT id FROM artists WHERE id = ? AND is_confirmed = 1 LIMIT 1');
    $artistCheckStmt->execute([$artistId]);
    if (!$artistCheckStmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Artista não encontrado.']);
        exit;
    }

    $pdo->beginTransaction();

    $existingStmt = $pdo->prepare('SELECT id FROM artist_upvotes WHERE artist_id = ? AND voter_token = ? LIMIT 1');
    $existingStmt->execute([$artistId, $voterToken]);
    $existingVote = $existingStmt->fetch();

    $hasUpvoted = false;
    $action = 'added';

    if ($existingVote) {
        $deleteStmt = $pdo->prepare('DELETE FROM artist_upvotes WHERE id = ?');
        $deleteStmt->execute([(int)$existingVote['id']]);
        $hasUpvoted = false;
        $action = 'removed';
    } else {
        $insertStmt = $pdo->prepare('INSERT INTO artist_upvotes (artist_id, voter_token) VALUES (?, ?)');
        $insertStmt->execute([$artistId, $voterToken]);
        $hasUpvoted = true;
        $action = 'added';
    }

    $countStmt = $pdo->prepare('SELECT COUNT(*) AS total FROM artist_upvotes WHERE artist_id = ?');
    $countStmt->execute([$artistId]);
    $totalUpvotes = (int)($countStmt->fetch()['total'] ?? 0);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'artist_id' => $artistId,
        'upvotes' => $totalUpvotes,
        'has_upvoted' => $hasUpvoted,
        'action' => $action,
    ]);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao registar upvote.',
    ]);
}
