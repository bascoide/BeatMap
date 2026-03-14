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
header('Content-Type: application/json');

// Simula um pequeno atraso para feedback visual
usleep(500000);

// 1. Obter dados do POST
$input = json_decode(file_get_contents('php://input'), true);
$username = $input['username'] ?? '';
$password = $input['password'] ?? '';

if (empty($username) || empty($password)) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Utilizador e palavra-passe são obrigatórios.']);
    exit;
}

// 2. Conectar à Base de Dados
try {
    $host = 'localhost';
    $db   = 'beatmap';
    $user = 'root';
    $pass = '';
    $charset = 'utf8mb4';
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro de conexão com a base de dados.']);
    exit;
}

// 3. Procurar o utilizador
// Compatível com esquemas antigos (password) e atuais (password_hash)
try {
    $stmt = $pdo->prepare("SELECT id, username, email, password_hash, password FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $stmt = $pdo->prepare("SELECT id, username, email, password_hash FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

$storedHash = null;
if ($user) {
    $storedHash = $user['password_hash'] ?? null;
    if (!$storedHash) {
        $storedHash = $user['password'] ?? null;
    }
}

// 4. Verificar a password com password_verify()
if ($user && $storedHash && password_verify($password, $storedHash)) {
    // Sucesso! Password corresponde ao hash na BD.
    session_regenerate_id(true); // Segurança extra

    // Definir as variáveis da sessão
    unset(
        $_SESSION['artist_id'],
        $_SESSION['artist_logged_in'],
        $_SESSION['artist_name'],
        $_SESSION['artist_email'],
        $_SESSION['artist_city'],
        $_SESSION['artist_moderation_status']
    );
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['user_email'] = trim((string)($user['email'] ?? ''));
    $_SESSION['user_type'] = 'user';

    echo json_encode(['success' => true, 'message' => 'Login bem-sucedido!']);
} else {
    // Falha no login
    http_response_code(401); // Unauthorized
    echo json_encode(['success' => false, 'message' => 'Utilizador ou palavra-passe incorretos.']);
}