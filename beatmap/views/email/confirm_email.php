<?php
session_start();
// Support being called both directly and through wrapper
$dbPath = file_exists(__DIR__ . '/../../inc/db.php') ? __DIR__ . '/../../inc/db.php' : __DIR__ . '/../inc/db.php';
require $dbPath;

function ensureArtistModerationStatusColumn(mysqli $mysqli): void
{
    $check = $mysqli->query("SHOW COLUMNS FROM artists LIKE 'moderation_status'");
    $column = $check ? $check->fetch_assoc() : null;
    $exists = (bool)$column;
    $columnType = strtolower((string)($column['Type'] ?? ''));
    if ($check instanceof mysqli_result) {
        $check->close();
    }

    if (!$exists) {
        $mysqli->query("ALTER TABLE artists ADD COLUMN moderation_status ENUM('pending','approved','rejected','banned') NOT NULL DEFAULT 'approved' AFTER is_confirmed");
    } elseif ($columnType !== '' && strpos($columnType, "'banned'") === false) {
        $mysqli->query("ALTER TABLE artists MODIFY COLUMN moderation_status ENUM('pending','approved','rejected','banned') NOT NULL DEFAULT 'approved'");
    }
}

ensureArtistModerationStatusColumn($mysqli);

function renderConfirmationPage(
    string $title,
    string $message,
    string $status = 'info',
    array $actions = [],
    ?string $redirectUrl = null,
    int $redirectSeconds = 0
): void
{
        $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $safeMessage = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        $statusClass = in_array($status, ['success', 'warning', 'error', 'info'], true) ? $status : 'info';
        $assetsCss = '../../assets/style.css';
        $safeRedirectUrl = $redirectUrl ? htmlspecialchars($redirectUrl, ENT_QUOTES, 'UTF-8') : null;
        $jsRedirectUrl = $redirectUrl ? json_encode($redirectUrl, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) : null;
        $redirectSeconds = max(0, $redirectSeconds);

        // Seleção de ícone SVG baseado no status
        $iconSvg = '';
        switch ($status) {
            case 'success':
                $iconSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>';
                break;
            case 'error':
                $iconSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>';
                break;
            case 'warning':
                $iconSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>';
                break;
            default:
                $iconSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>';
        }

        echo "<!DOCTYPE html>
<html lang=\"pt\">
<head>
    <meta charset=\"UTF-8\" />
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\" />
    <title>Beatmap • Confirmação de Email</title>
    <link rel=\"stylesheet\" href=\"{$assetsCss}\" />
    <link href=\"https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap\" rel=\"stylesheet\">
    <style>
        :root {
            --primary: #ff6b35;
            --primary-hover: #e55a2b;
            --bg-dark: #121212;
            --text-light: #e0e0e0;
            --text-muted: #a0a0a0;
            --success: #198754;
            --warning: #ffc107;
            --error: #dc3545;
            --info: #0d6efd;
        }

        body {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 20px;
            margin: 0;
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-dark);
            background-image: 
                radial-gradient(circle at 15% 50%, rgba(255, 107, 53, 0.14), transparent 40%),
                radial-gradient(circle at 85% 30%, rgba(255, 107, 53, 0.10), transparent 40%);
            color: var(--text-light);
            overflow-x: hidden;
        }

        .confirm-card {
            width: 100%;
            max-width: 460px;
            background: rgba(30, 30, 30, 0.6);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 24px;
            padding: 48px 32px;
            box-shadow: 0 20px 60px -10px rgba(0, 0, 0, 0.6);
            text-align: center;
            position: relative;
            overflow: hidden;
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .confirm-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .brand-logo {
            font-size: 1.5rem;
            font-weight: 900;
            margin-bottom: 40px;
            letter-spacing: -0.02em;
            color: #fff;
            display: inline-block;
        }

        .brand-logo span {
            color: var(--primary);
        }

        .status-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.3s ease;
        }
        
        .confirm-card:hover .status-icon {
            transform: scale(1.05);
        }

        .status-icon.success { background: rgba(25, 135, 84, 0.15); color: var(--success); box-shadow: 0 0 30px rgba(25, 135, 84, 0.2); }
        .status-icon.warning { background: rgba(255, 193, 7, 0.15); color: var(--warning); box-shadow: 0 0 30px rgba(255, 193, 7, 0.2); }
        .status-icon.error { background: rgba(220, 53, 69, 0.15); color: var(--error); box-shadow: 0 0 30px rgba(220, 53, 69, 0.2); }
        .status-icon.info { background: rgba(13, 110, 253, 0.15); color: var(--info); box-shadow: 0 0 30px rgba(13, 110, 253, 0.2); }

        h1 {
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0 0 12px;
            color: #fff;
            letter-spacing: -0.01em;
        }

        p {
            color: var(--text-muted);
            font-size: 1.05rem;
            line-height: 1.6;
            margin: 0 0 36px;
        }

        .actions {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .redirect-note {
            color: var(--text-muted);
            font-size: 0.95rem;
            margin-top: 6px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 14px 24px;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            font-size: 1rem;
            cursor: pointer;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
            border: 1px solid var(--primary);
            box-shadow: 0 4px 15px rgba(223, 127, 49, 0.4);
        }

        .btn-primary:hover {
            background: var(--primary-hover);
            border-color: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(223, 127, 49, 0.4);
        }

        .btn-ghost {
            background: transparent;
            color: var(--text-muted);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .btn-ghost:hover {
            color: #fff;
            border-color: rgba(255, 255, 255, 0.3);
            background: rgba(255, 255, 255, 0.05);
        }

        @media (min-width: 480px) {
            .actions {
                flex-direction: row;
                justify-content: center;
            }
            .btn {
                min-width: 140px;
            }
        }
    </style>
</head>
<body>
    <main class=\"confirm-card\" role=\"main\">
        <div class=\"brand-logo\">Beat<span>map</span></div>
        
        <div class=\"status-icon {$statusClass}\">
            {$iconSvg}
        </div>

        <h1>{$safeTitle}</h1>
    <p>{$safeMessage}</p>";

    if ($safeRedirectUrl && $redirectSeconds > 0) {
        echo "<p class=\"redirect-note\">A redirecionar para o login em {$redirectSeconds} segundos...</p>";
    }

    if (!empty($actions)) {
        echo "<div class=\"actions\">";

        foreach ($actions as $action) {
            $href = htmlspecialchars((string)($action['href'] ?? '#'), ENT_QUOTES, 'UTF-8');
            $label = htmlspecialchars((string)($action['label'] ?? 'Continuar'), ENT_QUOTES, 'UTF-8');
            $type = ($action['type'] ?? 'ghost') === 'primary' ? 'btn btn-primary' : 'btn btn-ghost';
            echo "<a href=\"{$href}\" class=\"{$type}\">{$label}</a>";
        }

        echo "</div>";
        }

        echo "
    </main>";

    if ($safeRedirectUrl && $redirectSeconds > 0) {
        $redirectMs = $redirectSeconds * 1000;
        echo "
<script>
setTimeout(function () {
    window.location.href = {$jsRedirectUrl};
}, {$redirectMs});
</script>";
    }

    echo "
</body>
</html>";
        exit;
}

$token = $_GET['token'] ?? '';
if (!$token) {
        renderConfirmationPage(
                'Token inválido',
                'O link de confirmação é inválido ou está incompleto.',
                'error',
                [
                        ['href' => '../add_artist.php', 'label' => 'Voltar ao registo', 'type' => 'primary'],
                    ['href' => '/beatmap(index)/login.php', 'label' => 'Iniciar sessão', 'type' => 'ghost'],
                ]
        );
}

$stmt = $mysqli->prepare("SELECT id, email, token_expires, is_confirmed, moderation_status FROM artists WHERE confirmation_token = ? LIMIT 1");
$stmt->bind_param('s', $token);
$stmt->execute();
$res = $stmt->get_result();
$artist = $res->fetch_assoc();
$stmt->close();

if (!$artist) {
    renderConfirmationPage(
        'Não foi possível validar o token',
        'Este link já não é válido. Solicite um novo email de confirmação para continuar.',
        'error',
    );
}

if ($artist['is_confirmed']) {
    $status = (string)($artist['moderation_status'] ?? 'approved');
    $message = $status === 'pending'
        ? 'O seu email já está confirmado. A conta encontra-se em estado pendente até aprovação do administrador.'
        : 'O seu email já estava confirmado. Pode iniciar sessão normalmente.';

    renderConfirmationPage(
        'Conta já confirmada',
        $message,
        'info',
        [
            ['href' => '/beatmap(index)/login.php', 'label' => 'Iniciar sessão', 'type' => 'primary'],
        ]
    );
}

if ($artist['token_expires'] && strtotime($artist['token_expires']) < time()) {
    renderConfirmationPage(
        'Token expirado',
        'Este link expirou. Solicite um novo email de confirmação para ativar a conta.',
        'warning',
        [
            ['href' => '../add_artist.php', 'label' => 'Reenviar confirmação', 'type' => 'primary'],
            ['href' => '/beatmap(index)/login.php', 'label' => 'Iniciar sessão', 'type' => 'ghost'],
        ]
    );
}

$u = $mysqli->prepare("UPDATE artists SET is_confirmed = 1, moderation_status = 'pending', confirmation_token = NULL, token_expires = NULL WHERE id = ?");
$u->bind_param('i', $artist['id']);
$u->execute();
$u->close();

// If the user has a session pending, mark logged in
if (isset($_SESSION['artist_pending']) && $_SESSION['artist_pending'] && isset($_SESSION['artist_id']) && $_SESSION['artist_id'] == $artist['id']) {
    $_SESSION['artist_logged_in'] = true;
    unset($_SESSION['artist_pending']);
    header('Location: /beatmap(index)/login.php');
    exit;
}

renderConfirmationPage(
    'Email confirmado com sucesso',
    'Email confirmado. A sua conta está agora em estado pendente e será analisada por um administrador antes de aparecer no mapa.',
    'success',
    [],
    '/beatmap(index)/login.php',
    3
);

?>
