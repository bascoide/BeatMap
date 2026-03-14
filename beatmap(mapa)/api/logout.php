<?php
// Desativar exibição de erros no output para não quebrar o JSON
error_reporting(0);
ini_set('display_errors', 0);

// Permitir que a sessão seja partilhada entre pastas
$cookieDomain = $_SERVER['HTTP_HOST'] ?? '';
$cookieDomain = preg_replace('/:\\d+$/', '', $cookieDomain);
if ($cookieDomain === 'localhost' || $cookieDomain === '' || filter_var($cookieDomain, FILTER_VALIDATE_IP)) {
	session_set_cookie_params(0, '/');
} else {
	session_set_cookie_params(0, '/', '.' . $cookieDomain);
}

session_start();

// Destruir todas as variáveis de sessão
$_SESSION = [];

// Destruir a sessão
session_destroy();

header('Content-Type: application/json');
echo json_encode(['success' => true, 'message' => 'Sessão terminada com sucesso.']);
exit;
?>