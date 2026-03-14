<?php
session_start();
header('Content-Type: application/json');
require __DIR__ . '/../../inc/db.php';

$response = ['confirmed' => false];
if (isset($_SESSION['artist_id'])) {
    $id = (int)$_SESSION['artist_id'];
    $stmt = $mysqli->prepare("SELECT is_confirmed FROM artists WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();
    if ($row && $row['is_confirmed']) {
        $response['confirmed'] = true;
    }
}

echo json_encode($response);
exit;

?>
