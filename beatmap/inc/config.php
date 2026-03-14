<?php
// Configuração de caminhos dinâmicos
// Detecta a raiz do projeto
$script_dir = dirname($_SERVER['SCRIPT_NAME']);
$base_url = rtrim($script_dir, '/') . '/';

// Se estamos em /views/, volta para raiz
if (strpos($base_url, '/views') !== false) {
    $base_url = preg_replace('|/views/?$|', '/', $base_url);
}

// Se o URL contém /beatmap, normaliza
if (preg_match('|/beatmap/?$|', $base_url)) {
    $base_url = preg_replace('|/beatmap/?$|', '/beatmap/', $base_url);
}

define('BASE_URL', $base_url);
define('ASSETS_URL', BASE_URL . 'assets/');
define('VIEWS_URL', BASE_URL . 'views/');
define('DEFAULT_AVATAR_PATH', 'assets/default-avatar.png');
define('DEFAULT_AVATAR_URL', ASSETS_URL . 'default-avatar.png');
define('ROOT_DIR', __DIR__ . '/..');
?>
