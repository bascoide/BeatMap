<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "beatmap";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Adicionar colunas para reset de password se não existirem
$sql = "ALTER TABLE users 
        ADD COLUMN IF NOT EXISTS reset_token VARCHAR(255) DEFAULT NULL,
        ADD COLUMN IF NOT EXISTS reset_expires DATETIME DEFAULT NULL";

if ($conn->query($sql) === TRUE) {
    echo "Tabela 'users' atualizada com sucesso! Colunas de reset adicionadas.";
} else {
    echo "Erro ao atualizar tabela: " . $conn->error;
}

$conn->close();
?>