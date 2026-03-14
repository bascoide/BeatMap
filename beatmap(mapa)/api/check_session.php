<?php
// Desativar exibição de erros no output para não quebrar o JSON
error_reporting(0);
ini_set('display_errors', 0);

// Permitir que a sessão seja partilhada entre pastas (index e mapa)
$cookieDomain = $_SERVER['HTTP_HOST'] ?? '';
$cookieDomain = preg_replace('/:\\d+$/', '', $cookieDomain);
if ($cookieDomain === 'localhost' || $cookieDomain === '' || filter_var($cookieDomain, FILTER_VALIDATE_IP)) {
    session_set_cookie_params(0, '/');
} else {
    session_set_cookie_params(0, '/', '.' . $cookieDomain);
}

session_start();
header('Content-Type: application/json');

function ensureArtistModerationStatusColumn(PDO $pdo): void
{
    $stmt = $pdo->query("SHOW COLUMNS FROM artists LIKE 'moderation_status'");
    $column = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;
    $exists = (bool)$column;
    $columnType = strtolower((string)($column['Type'] ?? ''));
    if (!$exists) {
        $pdo->exec("ALTER TABLE artists ADD COLUMN moderation_status ENUM('pending','approved','rejected','banned') NOT NULL DEFAULT 'approved' AFTER is_confirmed");
    } elseif ($columnType !== '' && strpos($columnType, "'banned'") === false) {
        $pdo->exec("ALTER TABLE artists MODIFY COLUMN moderation_status ENUM('pending','approved','rejected','banned') NOT NULL DEFAULT 'approved'");
    }
}

if (isset($_SESSION['user_id']) || isset($_SESSION['artist_id'])) {
    $response = ['loggedIn' => true];
    $accountType = null;
    $accountId = null;
    $sessionUserType = (string)($_SESSION['user_type'] ?? '');
    
    // Tenta obter o tipo de utilizador
    if ($sessionUserType === 'artist' && isset($_SESSION['artist_id'])) {
        $accountType = 'artist';
        $accountId = (int) $_SESSION['artist_id'];
        $response['userType'] = $accountType;
    } elseif ($sessionUserType === 'user' && isset($_SESSION['user_id'])) {
        $accountType = 'user';
        $accountId = (int) $_SESSION['user_id'];
        $response['userType'] = $accountType;
    } elseif (isset($_SESSION['artist_id']) && !isset($_SESSION['user_id'])) {
        $accountType = 'artist';
        $accountId = (int) $_SESSION['artist_id'];
        $response['userType'] = $accountType;
    } elseif (isset($_SESSION['user_id'])) {
        $accountType = 'user';
        $accountId = (int) $_SESSION['user_id'];
        $response['userType'] = $accountType;
    }

    if ($accountType !== null && $accountId !== null) {
        $response['accountId'] = $accountId;
        $response['accountKey'] = $accountType . '_' . $accountId;
    }
    
    // Tenta obter o nome da sessão se já estiver definido
    $sessionName = $_SESSION['username'] ?? $_SESSION['artist_name'] ?? null;
    if ($sessionName) {
        $response['username'] = $sessionName;
    }

    $sessionEmail = trim((string)($_SESSION['artist_email'] ?? $_SESSION['user_email'] ?? $_SESSION['email'] ?? ''));
    if ($sessionEmail !== '') {
        $response['email'] = $sessionEmail;
    }

    // Para artistas, sincroniza sempre o estado de moderação com a BD em cada refresh.
    // Assim, quando o admin aprovar a conta, o estado "pendente" desaparece imediatamente.
    $shouldFetchFromDb = !isset($sessionName) || isset($_SESSION['artist_id']);
    if ($shouldFetchFromDb) {
        // Se não, busca na base de dados
        try {
            $host = 'localhost';
            $db   = 'beatmap';
            $user = 'root';
            $pass = '';
            $charset = 'utf8mb4';
            $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
            $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            ensureArtistModerationStatusColumn($pdo);
            
            $foundName = null;
            
            if (isset($_SESSION['artist_id'])) {
                $stmt = $pdo->prepare("SELECT name, email, moderation_status FROM artists WHERE id = ?");
                $stmt->execute([$_SESSION['artist_id']]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row) {
                    $foundName = $row['name'];
                    if (!empty($row['email']) && empty($response['email'])) {
                        $response['email'] = $row['email'];
                    }
                    $moderationStatus = (string)($row['moderation_status'] ?? 'approved');
                    $_SESSION['artist_moderation_status'] = $moderationStatus;
                    $response['artistModerationStatus'] = $moderationStatus;
                    $response['artistIsPending'] = $moderationStatus === 'pending';
                }
            } elseif (isset($_SESSION['user_id'])) {
                $stmt = $pdo->prepare("SELECT username, email FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row) {
                    $foundName = $row['username'];
                    if (!empty($row['email']) && empty($response['email'])) {
                        $response['email'] = $row['email'];
                    }
                }
            }

            if (isset($_SESSION['artist_id']) && empty($response['artistModerationStatus'])) {
                $sessionStatus = (string)($_SESSION['artist_moderation_status'] ?? 'approved');
                $response['artistModerationStatus'] = $sessionStatus;
                $response['artistIsPending'] = $sessionStatus === 'pending';
            }
            
            if ($foundName) {
                // Guarda o nome na sessão para não ter de ir à BD da próxima vez
                if (isset($_SESSION['user_id'])) $_SESSION['username'] = $foundName;
                if (isset($_SESSION['artist_id'])) $_SESSION['artist_name'] = $foundName;
                $response['username'] = $foundName;
            } elseif (empty($response['username'])) {
                // O ID existe na sessão, mas não foi encontrado na BD (pode ter sido apagado)
                $response['username'] = 'Utilizador';
            }
        } catch (Exception $e) {
            // Em caso de erro na BD, não quebra a aplicação e devolve um nome genérico
            if (empty($response['username'])) {
                $response['username'] = 'Utilizador';
            }
            if (isset($_SESSION['artist_id'])) {
                $sessionStatus = (string)($_SESSION['artist_moderation_status'] ?? 'approved');
                $response['artistModerationStatus'] = $sessionStatus;
                $response['artistIsPending'] = $sessionStatus === 'pending';
            }
        }
    } elseif (isset($_SESSION['artist_id'])) {
        $sessionStatus = (string)($_SESSION['artist_moderation_status'] ?? 'approved');
        $response['artistModerationStatus'] = $sessionStatus;
        $response['artistIsPending'] = $sessionStatus === 'pending';
    }
    echo json_encode($response);
} else {
    echo json_encode(['loggedIn' => false]);
}
exit;
?>