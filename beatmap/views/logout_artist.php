<?php
session_start();
require __DIR__ . '/../inc/config.php';
session_destroy();
header('Location: ' . BASE_URL);
exit;