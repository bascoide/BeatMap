<?php
session_set_cookie_params(0, '/', '.beatmap');
session_start();

// Configurações da conexão
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "beatmap";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Receber e limpar dados
    $user = trim($_POST['username']);
    $email = trim($_POST['email']);
    $pass = $_POST['password'];

    if (empty($user) || empty($email) || empty($pass)) {
        header("Location: registo.php?status=empty");
        exit;
    }

    if (strlen($pass) < 6) {
        header("Location: registo.php?status=password_length");
        exit;
    }

    // Verificar duplicados
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    if (!$stmt) {
        die("Erro na preparação da consulta (Verifica se a tabela 'users' existe): " . $conn->error);
    }
    $stmt->bind_param("ss", $email, $user);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        header("Location: registo.php?status=exists");
    } else {
        // Encriptar password e inserir
        $password_hash = password_hash($pass, PASSWORD_DEFAULT);
        $insert = $conn->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
        if (!$insert) {
            die("Erro na preparação da inserção: " . $conn->error);
        }
        $insert->bind_param("sss", $user, $email, $password_hash);

        if ($insert->execute()) {
            // Sucesso: Redireciona de volta com status=success
            header("Location: registo.php?status=success");
        } else {
            // Erro genérico
            header("Location: registo.php?status=error");
        }
        $insert->close();
    }
    $stmt->close();

}
$conn->close();
?>