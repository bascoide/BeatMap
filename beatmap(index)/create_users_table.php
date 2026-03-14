<?php
// Configurações da conexão à base de dados (iguais ao get_stats.php)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "beatmap";

// Criar conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// SQL para criar a tabela users
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Tabela 'users' criada com sucesso!";
} else {
    echo "Erro ao criar tabela: " . $conn->error;
}

$conn->close();
?>