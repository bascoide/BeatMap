<?php
error_reporting(0);
ini_set('display_errors', 0);

$cookieDomain = $_SERVER['HTTP_HOST'] ?? '';
$cookieDomain = preg_replace('/:\\d+$/', '', $cookieDomain);
if ($cookieDomain === 'localhost' || $cookieDomain === '') {
    session_set_cookie_params(0, '/');
} else {
    session_set_cookie_params(0, '/', '.' . $cookieDomain);
}

session_start();
header('Content-Type: application/json');

$accountType = null;
$accountId = null;

if (isset($_SESSION['artist_id'])) {
    $accountType = 'artist';
    $accountId = (int) $_SESSION['artist_id'];
} elseif (isset($_SESSION['user_id'])) {
    $accountType = 'user';
    $accountId = (int) $_SESSION['user_id'];
}

if (!$accountType || !$accountId) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Sessão inválida.',
    ]);
    exit;
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
    ]);

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS map_first_access (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            account_type ENUM('user', 'artist') NOT NULL,
            account_id INT NOT NULL,
            first_seen_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_seen_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            visit_count INT NOT NULL DEFAULT 1,
            UNIQUE KEY uniq_map_account (account_type, account_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );

    $pdo->beginTransaction();

    $selectStmt = $pdo->prepare(
        'SELECT id, visit_count FROM map_first_access WHERE account_type = ? AND account_id = ? LIMIT 1'
    );
    $selectStmt->execute([$accountType, $accountId]);
    $existing = $selectStmt->fetch();

    $showTutorial = false;
    $visitCount = 1;

    if ($existing) {
        $visitCount = max(1, (int) $existing['visit_count'] + 1);

        $updateStmt = $pdo->prepare(
            'UPDATE map_first_access SET visit_count = ?, last_seen_at = CURRENT_TIMESTAMP WHERE id = ?'
        );
        $updateStmt->execute([$visitCount, (int) $existing['id']]);
    } else {
        $insertStmt = $pdo->prepare(
            'INSERT INTO map_first_access (account_type, account_id, visit_count) VALUES (?, ?, 1)'
        );
        $insertStmt->execute([$accountType, $accountId]);
        $showTutorial = true;
        $visitCount = 1;
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'showTutorial' => $showTutorial,
        'accountType' => $accountType,
        'accountId' => $accountId,
        'visitCount' => $visitCount,
    ]);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Não foi possível validar o tutorial.',
    ]);
}
