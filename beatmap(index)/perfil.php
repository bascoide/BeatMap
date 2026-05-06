<?php
// Permitir que a sessão seja partilhada entre pastas
$cookieDomain = $_SERVER['HTTP_HOST'] ?? '';
$cookieDomain = preg_replace('/:\\d+$/', '', $cookieDomain);
if ($cookieDomain === 'localhost' || $cookieDomain === '') {
    session_set_cookie_params(0, '/');
} else {
    $parts = explode('.', $cookieDomain);
    if (count($parts) > 1) {
        $cookieDomain = '.' . $parts[count($parts)-2] . '.' . $parts[count($parts)-1];
    }
    session_set_cookie_params(0, '/', $cookieDomain);
}
session_start();

// Assumindo que a sua conexão PDO está em ../config/database.php
// require_once '../config/database.php';
// Por agora, vamos criar uma conexão real para buscar os dados do utilizador
$pdo = null;
try {
    $host = 'localhost';
    $db   = 'beatmap';
    $user_db = 'root';
    $pass_db = '';
    $charset = 'utf8mb4';
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $pdo = new PDO($dsn, $user_db, $pass_db, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    die("Erro de conexão com a base de dados: " . $e->getMessage());
}

// 1. Verifique se o utilizador está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit();
}

// 2. Redirecionar artistas para a página de edição de artista
if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'artist') {
    header('Location: ../beatmap/views/edit_artist.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Buscar dados atuais do utilizador na base de dados
try {
    $stmt = $pdo->prepare("SELECT username, email, password_hash FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        session_destroy();
        header('Location: auth.php?status=error');
        exit();
    }
} catch (PDOException $e) {
    $error = "Erro ao carregar os dados do utilizador.";
    $user = ['username' => 'Erro', 'email' => 'erro@servidor.com', 'password_hash' => ''];
}

// 3. Lógica de atualização
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        if (empty($username) || empty($email)) {
            $error = "Nome de utilizador e email são obrigatórios.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "O formato do email é inválido.";
        } else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
            $stmt->execute([$username, $email, $user_id]);
            if ($stmt->fetch()) {
                $error = "O nome de utilizador ou email já está em uso por outra conta.";
            } else {
                $updateStmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
                if ($updateStmt->execute([$username, $email, $user_id])) {
                    $_SESSION['username'] = $username; // Atualiza a sessão
                    $user['username'] = $username;
                    $user['email'] = $email;
                    $message = "Perfil atualizado com sucesso!";
                } else {
                    $error = "Ocorreu um erro ao atualizar o perfil.";
                }
            }
        }
    }

    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error = "Todos os campos de senha são obrigatórios.";
        } elseif (!password_verify($current_password, $user['password_hash'])) {
            $error = "A senha atual está incorreta.";
        } elseif (strlen($new_password) < 6) {
            $error = "A nova senha deve ter pelo menos 6 caracteres.";
        } elseif ($new_password !== $confirm_password) {
            $error = "A nova senha e a confirmação não correspondem.";
        } else {
            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $updateStmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            if ($updateStmt->execute([$new_password_hash, $user_id])) {
                $message = "Senha alterada com sucesso!";
            } else {
                $error = "Ocorreu um erro ao alterar a senha.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - Beatmap</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    <style>
        .profile-page {
            min-height: 100vh;
            padding: 120px 20px 50px;
            background:
                linear-gradient(rgba(10, 10, 10, 0.75), rgba(10, 10, 10, 0.75)),
                url("fundo-do-site.gif");
            background-size: cover;
            background-position: center;
        }

        .profile-navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            width: 100%;
            padding: 16px 26px;
            background: rgba(10, 10, 10, 0.92) !important;
            backdrop-filter: blur(14px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
            z-index: 1000;
        }

        .profile-navbar .nav-links {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .profile-wrapper {
            width: 100%;
            max-width: 840px;
            margin: 0 auto;
        }

        .profile-container {
            background: #1a1a1a;
            border-radius: 20px;
            border: 1px solid #2a2a2a;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
            overflow: hidden;
        }

        .profile-header {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 28px 34px;
            background-color: #151515;
            border-bottom: 1px solid #2a2a2a;
        }

        .avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--primary);
            box-shadow: 0 0 0 4px rgba(115, 49, 223, 0.2);
        }

        .user-details h2 {
            margin: 0;
            font-size: 22px;
            font-weight: 700;
        }
        .user-details p {
            margin: 4px 0 0;
            color: var(--text-muted);
            font-size: 14px;
        }

        .profile-tabs {
            display: flex;
            border-bottom: 1px solid #2a2a2a;
            padding: 0 18px;
        }

        .tab-btn {
            padding: 15px 20px;
            cursor: pointer;
            background: none;
            border: none;
            color: var(--text-muted);
            font-weight: 600;
            font-size: 15px;
            position: relative;
            transition: color 0.3s ease;
        }
        .tab-btn:hover {
            color: var(--text-light);
        }
        .tab-btn.active {
            color: var(--primary);
        }
        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 3px;
            background-color: var(--primary);
            border-radius: 3px 3px 0 0;
        }

        .tab-content {
            display: none;
            padding: 28px 34px;
        }
        .tab-content.active {
            display: block;
            animation: fadeIn 0.3s ease-in-out;
        }

        .form-section h3 {
            font-size: 18px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 25px;
            padding-bottom: 10px;
            border-bottom: 1px solid #2a2a2a;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            color: var(--text-muted);
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            background: #0a0a0a;
            border: 1px solid #333;
            border-radius: 8px;
            color: #fff;
            outline: none;
            transition: var(--transition);
        }

        .form-group input:focus {
            border-color: var(--primary);
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 5px;
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            filter: brightness(1.15);
        }

        /* Messages */
        .message {
            padding: 12px 15px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 34px 0;
            font-size: 14px;
            font-weight: 500;
        }
        .message.success {
            background-color: rgba(40, 167, 69, 0.2);
            color: #52c41a;
            border: 1px solid rgba(40, 167, 69, 0.5);
        }
        .message.error {
            background-color: rgba(220, 53, 69, 0.2);
            color: #ff4d4f;
            border: 1px solid rgba(220, 53, 69, 0.5);
        }

        /* Danger Zone */
        .danger-zone {
            margin-top: 30px;
            padding: 20px;
            border: 1px solid rgba(255, 77, 79, 0.55);
            background-color: rgba(220, 53, 69, 0.1);
            border-radius: 8px;
        }
        .danger-zone h4 {
            color: #ff4d4f;
            margin-top: 0;
        }
        .danger-zone p {
            font-size: 14px;
            color: var(--text-muted);
            margin-bottom: 15px;
        }
        .btn-danger {
            background-color: #c82333;
        }
        .btn-danger:hover {
            background-color: #a71d2a;
        }

        @media (max-width: 768px) {
            .profile-page {
                padding-top: 95px;
            }

            .profile-navbar {
                padding: 14px 16px;
            }

            .profile-navbar .nav-links {
                display: flex;
                gap: 8px;
            }

            .profile-navbar .btn-nav {
                font-size: 12px;
                padding: 8px 12px;
            }

            .profile-header {
                flex-direction: column;
                text-align: center;
                padding: 24px 22px;
            }

            .tab-content {
                padding: 24px 22px;
            }

            .message {
                margin: 18px 22px 0;
            }
        }
    </style>
</head>
<body class="profile-page">

    <nav class="navbar scrolled profile-navbar">
        <a href="/beatmap(mapa)/" class="logo" style="text-decoration: none; color: inherit;">BEAT<span>MAP</span></a>
        <nav class="nav-links">
            <a href="../beatmap(mapa)/index.html" class="btn-nav">Voltar ao Mapa</a>
        </nav>
    </nav>

    <main class="profile-wrapper">
    <div class="profile-container">
        <header class="profile-header">
            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['username']); ?>&background=7331df&color=fff&size=128" alt="Avatar" class="avatar">
            <div class="user-details">
                <h2><?php echo htmlspecialchars($user['username']); ?></h2>
                <p><?php echo htmlspecialchars($user['email']); ?></p>
            </div>
        </header>

        <?php if ($message || $error): ?>
            <div class="message <?php echo $error ? 'error' : 'success'; ?>">
                <?php echo htmlspecialchars($error ?: $message); ?>
            </div>
        <?php endif; ?>

        <div class="profile-tabs">
            <button class="tab-btn active" data-tab="conta">Conta</button>
            <button class="tab-btn" data-tab="seguranca">Segurança</button>
        </div>

        <!-- Tab: Conta -->
        <div id="tab-conta" class="tab-content active">
            <form action="perfil.php" method="POST">
                <h3>Dados da Conta</h3>
                <div class="form-group">
                    <label for="username">Nome de Utilizador</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <button type="submit" name="update_profile" class="btn-submit">Guardar Alterações</button>
            </form>
        </div>

        <!-- Tab: Segurança -->
        <div id="tab-seguranca" class="tab-content">
            <form action="perfil.php" method="POST">
                <h3>Alterar Senha</h3>
                <div class="form-group">
                    <label for="current_password">Senha Atual</label>
                    <input type="password" id="current_password" name="current_password" required placeholder="••••••••">
                </div>
                <div class="form-group">
                    <label for="new_password">Nova Senha</label>
                    <input type="password" id="new_password" name="new_password" required placeholder="Pelo menos 6 caracteres">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirmar Nova Senha</label>
                    <input type="password" id="confirm_password" name="confirm_password" required placeholder="Repita a nova senha">
                </div>
                <button type="submit" name="change_password" class="btn-submit">Alterar Senha</button>
            </form>
        </div>
    </div>
    </main>

    <script src="script.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            // Theme handler
            function applyTheme(theme) {
                if (theme === "light") {
                    document.body.classList.add("light-theme");
                } else {
                    document.body.classList.remove("light-theme");
                }
                localStorage.setItem("theme", theme);
            }
            applyTheme(localStorage.getItem("theme") || "dark");

            // Tab handler
            const tabButtons = document.querySelectorAll('.tab-btn');
            const tabContents = document.querySelectorAll('.tab-content');

            tabButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const tabId = button.getAttribute('data-tab');

                    // Update buttons
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    button.classList.add('active');

                    // Update content
                    tabContents.forEach(content => {
                        if (content.id === `tab-${tabId}`) {
                            content.classList.add('active');
                        } else {
                            content.classList.remove('active');
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>
