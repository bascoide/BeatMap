<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');

$cookieDomain = $_SERVER['HTTP_HOST'] ?? '';
$cookieDomain = preg_replace('/:\\d+$/', '', $cookieDomain);
if ($cookieDomain === 'localhost' || $cookieDomain === '' || filter_var($cookieDomain, FILTER_VALIDATE_IP)) {
    session_set_cookie_params(0, '/');
} else {
    session_set_cookie_params(0, '/', '.' . $cookieDomain);
}

session_start();

if (!isset($_SESSION['user_id']) && !isset($_SESSION['artist_id'])) {
    echo json_encode(['loggedIn' => false]);
    exit;
}

$email = trim((string)($_SESSION['artist_email'] ?? $_SESSION['user_email'] ?? $_SESSION['email'] ?? ''));

if ($email === '') {
    $mysqli = @new mysqli('127.0.0.1', 'root', '', 'beatmap');
    if (!$mysqli->connect_errno) {
        $mysqli->set_charset('utf8mb4');

        if (isset($_SESSION['artist_id'])) {
            $artistId = (int)$_SESSION['artist_id'];
            $stmt = $mysqli->prepare('SELECT email FROM artists WHERE id = ? LIMIT 1');
            if ($stmt) {
                $stmt->bind_param('i', $artistId);
                $stmt->execute();
                $stmt->bind_result($dbEmail);
                if ($stmt->fetch() && is_string($dbEmail)) {
                    $email = trim($dbEmail);
                }
                $stmt->close();
            }
        } elseif (isset($_SESSION['user_id'])) {
            $userId = (int)$_SESSION['user_id'];
            $stmt = $mysqli->prepare('SELECT email FROM users WHERE id = ? LIMIT 1');
            if ($stmt) {
                $stmt->bind_param('i', $userId);
                $stmt->execute();
                $stmt->bind_result($dbEmail);
                if ($stmt->fetch() && is_string($dbEmail)) {
                    $email = trim($dbEmail);
                }
                $stmt->close();
            }
        }

        $mysqli->close();
    }
}

echo json_encode([
    'loggedIn' => true,
    'email' => $email,
]);
