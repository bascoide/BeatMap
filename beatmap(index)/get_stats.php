<?php
// Configurações da conexão à base de dados
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "beatmap"; // Verifica se o nome da tua base de dados é este

// Criar conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexão
if ($conn->connect_error) {
    die(json_encode(["error" => "Falha na conexão: " . $conn->connect_error]));
}

// Query para contar o número de artistas na tabela fornecida
$sql = "SELECT COUNT(*) as total FROM artists";
$result = $conn->query($sql);

$response = ["total" => 0];

if ($result && $row = $result->fetch_assoc()) {
    $response["total"] = $row["total"];
}

// Retornar resposta em JSON para o JavaScript
header('Content-Type: application/json');
echo json_encode($response);

$conn->close();
?>