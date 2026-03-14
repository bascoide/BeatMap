<?php
session_start();
header('Content-Type: application/json');
require __DIR__ . '/../../inc/db.php';
require_once __DIR__ . '/../../inc/config.php';

$resp = ['ok' => false, 'message' => ''];
if (!isset($_SESSION['artist_id'])) {
    $resp['message'] = 'Sessão expirou. Por favor inicie novamente o registo.';
    echo json_encode($resp);
    exit;
}

$id = (int)$_SESSION['artist_id'];
$stmt = $mysqli->prepare("SELECT name, email FROM artists WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$artist = $res->fetch_assoc();
$stmt->close();

if (!$artist) {
    $resp['message'] = 'Artista não encontrado.';
    echo json_encode($resp);
    exit;
}

// generate new token
$token = bin2hex(random_bytes(16));
$token_expires = date('Y-m-d H:i:s', time() + 60*60*24);
$u = $mysqli->prepare("UPDATE artists SET confirmation_token = ?, token_expires = ? WHERE id = ?");
$u->bind_param('ssi', $token, $token_expires, $id);
$u->execute();
$u->close();

$scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$confirm_url = $scheme . '://' . $_SERVER['HTTP_HOST'] . BASE_URL . 'email/confirm_email.php?token=' . urlencode($token);
$subject = 'Confirme o seu email - Beatmap';
$message = "Olá {$artist['name']},\n\nFoi solicitado um novo email de confirmação. Por favor confirme clicando aqui:\n\n$confirm_url\n\nSe não pediu, ignore.\n\nCumprimentos,\nBeatmap";
$headers = 'From: no-reply@' . $_SERVER['HTTP_HOST'] . "\r\n" . 'Reply-To: no-reply@' . $_SERVER['HTTP_HOST'] . "\r\n" . 'X-Mailer: PHP/' . phpversion();

@mail($artist['email'], $subject, $message, $headers);
// Also write confirmation link to local log for development
$logPath = __DIR__ . '/../../inc/confirmation_log.txt';
$logLine = date('Y-m-d H:i:s') . " - Resend To: {$artist['email']} - Link: $confirm_url\n";
@file_put_contents($logPath, $logLine, FILE_APPEND | LOCK_EX);


$resp['ok'] = true;
$resp['message'] = 'Email de confirmação reenviado para ' . $artist['email'];
$resp['link'] = $confirm_url; // include link for development/testing

echo json_encode($resp);
exit;

?>
