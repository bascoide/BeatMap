<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "beatmap";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Criar tabela para tokens de autenticação
$sql = "CREATE TABLE IF NOT EXISTS auth_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token VARCHAR(64) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    username VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME NOT NULL,
    used TINYINT(1) DEFAULT 0,
    INDEX idx_token (token),
    INDEX idx_expires (expires_at)
)";

if ($conn->query($sql) === TRUE) {
    echo "Tabela 'auth_tokens' criada com sucesso!<br>";
    echo "Sistema de tokens pronto para compartilhar sessões entre beatmap.home e beatmap.map";
} else {
    echo "Erro ao criar tabela: " . $conn->error;
}

$conn->close();
?>
