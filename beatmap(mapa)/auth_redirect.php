<?php
// Permitir sessão em subpastas no mesmo host
$cookieDomain = $_SERVER['HTTP_HOST'] ?? '';
$cookieDomain = preg_replace('/:\\d+$/', '', $cookieDomain);
if ($cookieDomain === 'localhost' || $cookieDomain === '' || filter_var($cookieDomain, FILTER_VALIDATE_IP)) {
    session_set_cookie_params(0, '/');
} else {
    session_set_cookie_params(0, '/', '.' . $cookieDomain);
}
session_start();

// Verificar se há token na URL
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    // Conectar ao banco de dados
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "beatmap";
    
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        die("Erro de conexão");
    }
    
    // Verificar token
    $stmt = $conn->prepare("SELECT user_id, username, expires_at, used FROM auth_tokens WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->bind_result($user_id, $user_name, $expires_at, $used);
    
    if ($stmt->fetch()) {
        $stmt->close();
        
        // Verificar se token não foi usado e não expirou
        if ($used == 0 && strtotime($expires_at) > time()) {
            // Marcar token como usado
            $updateStmt = $conn->prepare("UPDATE auth_tokens SET used = 1 WHERE token = ?");
            $updateStmt->bind_param("s", $token);
            $updateStmt->execute();
            $updateStmt->close();
            
            // Criar sessão no beatmap.map
            unset(
                $_SESSION['artist_id'],
                $_SESSION['artist_logged_in'],
                $_SESSION['artist_name'],
                $_SESSION['artist_email'],
                $_SESSION['artist_city'],
                $_SESSION['artist_moderation_status']
            );
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $user_name;
            $_SESSION['user_type'] = 'user';
            $emailStmt = $conn->prepare("SELECT email FROM users WHERE id = ? LIMIT 1");
            if ($emailStmt) {
                $emailStmt->bind_param("i", $user_id);
                $emailStmt->execute();
                $emailStmt->bind_result($user_email);
                if ($emailStmt->fetch() && is_string($user_email)) {
                    $_SESSION['user_email'] = trim($user_email);
                }
                $emailStmt->close();
            }
            
            $conn->close();
            
            // Redirecionar para a página principal sem o token na URL
            header("Location: ./index.html");
            exit;
        } else {
            $conn->close();
            die("Token inválido ou expirado");
        }
    } else {
        $stmt->close();
        $conn->close();
        die("Token não encontrado");
    }
} else {
    // Se não há token, redirecionar para login
    header("Location: ../beatmap(index)/login.php");
    exit;
}
?>
