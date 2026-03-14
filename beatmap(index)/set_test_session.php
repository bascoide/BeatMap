<?php
session_set_cookie_params(0, '/', '.beatmap');
session_start();

$_SESSION['user_id'] = 999;
$_SESSION['username'] = 'TestUser';
$_SESSION['test_time'] = date('Y-m-d H:i:s');

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Teste de Sessão</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #1a1a1a; color: #fff; text-align: center; }
        .success { background: #2e7d32; padding: 20px; border-radius: 10px; display: inline-block; margin: 20px; }
        a { color: #4CAF50; text-decoration: none; font-size: 18px; }
    </style>
</head>
<body>
    <div class="success">
        <h2>✅ Sessão de Teste Criada!</h2>
        <p>user_id: 999</p>
        <p>username: TestUser</p>
        <p>Hora: <?php echo date('Y-m-d H:i:s'); ?></p>
    </div>
    <br><br>
    <a href="debug_session.php">Ver debug em beatmap.home</a><br><br>
    <a href="http://beatmap.map/beatmap(mapa)/debug_session.php">Ver debug em beatmap.map</a>
</body>
</html>
