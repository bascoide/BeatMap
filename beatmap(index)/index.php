<?php
$cookieDomain = $_SERVER['HTTP_HOST'] ?? '';
$cookieDomain = preg_replace('/:\\d+$/', '', $cookieDomain);
if ($cookieDomain === 'localhost' || $cookieDomain === '') {
    session_set_cookie_params(0, '/');
} else {
    session_set_cookie_params(0, '/', '.' . $cookieDomain);
}
session_start();

if (isset($_SESSION['user_id']) || isset($_SESSION['artist_id'])) {
    header('Location: auth.php');
    exit;
}

readfile(__DIR__ . '/index.html');
