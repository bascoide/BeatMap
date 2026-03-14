<?php
session_start();
require __DIR__ . '/../../inc/db.php';

if (!isset($_SESSION['artist_id'])) {
    header('Location: /beatmap(index)/login.php');
    exit;
}

$id = (int)$_SESSION['artist_id'];
$stmt = $mysqli->prepare("SELECT is_confirmed FROM artists WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$artist = $res->fetch_assoc();
$stmt->close();

if ($artist && $artist['is_confirmed']) {
    // Fazer login automático
    $_SESSION['artist_logged_in'] = true;
    unset($_SESSION['artist_pending']);
    header('Location: /beatmap(index)/login.php');
    exit;
} else {
    // Voltar para confirmar
    header('Location: /beatmap(index)/login.php');
    exit;
}
?>
