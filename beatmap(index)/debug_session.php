<?php
session_set_cookie_params(0, '/', '.beatmap');
session_start();

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug Sessão - beatmap.home</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1a1a1a; color: #fff; }
        h2 { color: #4CAF50; }
        pre { background: #2a2a2a; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .info { background: #1e3a5f; padding: 10px; margin: 10px 0; border-radius: 5px; }
    </style>
</head>
<body>
    <h2>🔍 DEBUG SESSÃO - beatmap.home</h2>
    
    <div class="info">
        <strong>Domínio atual:</strong> <?php echo $_SERVER['HTTP_HOST']; ?><br>
        <strong>Session ID:</strong> <?php echo session_id(); ?><br>
        <strong>Cookie Path:</strong> <?php echo session_get_cookie_params()['path']; ?><br>
        <strong>Cookie Domain:</strong> <?php echo session_get_cookie_params()['domain']; ?>
    </div>

    <h3>📦 Variáveis de Sessão ($_SESSION):</h3>
    <pre><?php print_r($_SESSION); ?></pre>

    <h3>🍪 Cookies ($_COOKIE):</h3>
    <pre><?php print_r($_COOKIE); ?></pre>

    <h3>🌐 Informações do Servidor:</h3>
    <pre><?php 
        echo "SERVER_NAME: " . $_SERVER['SERVER_NAME'] . "\n";
        echo "HTTP_HOST: " . $_SERVER['HTTP_HOST'] . "\n";
        echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
    ?></pre>

    <hr>
    <p><a href="http://beatmap.map/beatmap(mapa)/debug_session.php" style="color: #4CAF50;">Ver debug em beatmap.map</a></p>
</body>
</html>
