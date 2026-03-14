<?php
session_start();
$pageTitle = 'Meu Perfil';
require __DIR__ . '/../inc/header.php';
require __DIR__ . '/../inc/db.php';

// Verificar se o artista está logado
if (!isset($_SESSION['artist_logged_in']) || !$_SESSION['artist_logged_in']) {
    header('Location: ' . VIEWS_URL . 'login_artist.php');
    exit;
}

// Buscar dados atualizados do artista
$stmt = $mysqli->prepare("SELECT * FROM artists WHERE id = ?");
$stmt->bind_param('i', $_SESSION['artist_id']);
$stmt->execute();
$artist = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$artist) {
    session_destroy();
    header('Location: login_artist.php');
    exit;
}

// Verificar se a conta está confirmada
if (!$artist['is_confirmed']) {
    $_SESSION['artist_logged_in'] = false;
    $_SESSION['artist_pending'] = true;
    header('Location: add_artist.php');
    exit;
}

$allowedStatuses = ['pending', 'approved', 'rejected', 'banned'];
$moderationStatus = strtolower(trim((string)($artist['moderation_status'] ?? 'approved')));
if (!in_array($moderationStatus, $allowedStatuses, true)) {
    $moderationStatus = 'approved';
}

if ($moderationStatus === 'banned') {
    session_unset();
    session_destroy();
    header('Location: ' . VIEWS_URL . 'login_artist.php?status=artist_banned');
    exit;
}

$statusLabels = [
    'pending' => 'Pendente',
    'rejected' => 'Recusado',
];

$statusClasses = [
    'pending' => 'status-pending',
    'rejected' => 'status-rejected',
];

$dashboardMessage = '';
$dashboardMessageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'resubmit_review') {
    if ($moderationStatus === 'rejected') {
        $update = $mysqli->prepare("UPDATE artists SET moderation_status = 'pending' WHERE id = ? AND moderation_status = 'rejected' LIMIT 1");
        $update->bind_param('i', $_SESSION['artist_id']);
        $update->execute();

        if ($update->affected_rows > 0) {
            $moderationStatus = 'pending';
            $artist['moderation_status'] = 'pending';
            $_SESSION['artist_moderation_status'] = 'pending';
            $dashboardMessage = 'A tua conta foi reenviada para revisão do admin.';
        } else {
            $dashboardMessage = 'Não foi possível reenviar neste momento. Tenta novamente.';
            $dashboardMessageType = 'error';
        }

        $update->close();
    } else {
        $dashboardMessage = 'O reenvio só está disponível para contas recusadas.';
        $dashboardMessageType = 'error';
    }
}

$showStatusBadge = isset($statusLabels[$moderationStatus]);
?>

<div class="dashboard-wrapper">
    <header class="dashboard-header">
        <div class="header-content">
            <h1>Olá, <span class="name-with-status"><?php if ($showStatusBadge): ?><span class="status-badge <?php echo $statusClasses[$moderationStatus]; ?>"><?php echo $statusLabels[$moderationStatus]; ?></span><?php endif; ?><span class="text-gradient"><?php echo htmlspecialchars($artist['name']); ?></span></span></h1>
            <p>Bem-vindo à tua dashboard.</p>
        </div>
    </header>

    <?php if ($dashboardMessage !== ''): ?>
        <div class="dashboard-alert <?php echo $dashboardMessageType === 'error' ? 'dashboard-alert-error' : 'dashboard-alert-success'; ?>">
            <?php echo htmlspecialchars($dashboardMessage); ?>
        </div>
    <?php endif; ?>

    <div class="dashboard-grid">
        <!-- Coluna Esquerda: Identidade -->
        <div class="dash-card profile-card">
            <div class="avatar-circle">
                <?php $avatarSrc = !empty($artist['profile_picture']) ? (BASE_URL . ltrim($artist['profile_picture'], '/')) : DEFAULT_AVATAR_URL; ?>
                <img src="<?php echo htmlspecialchars($avatarSrc); ?>" alt="<?php echo htmlspecialchars($artist['name']); ?>" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">
            </div>
            <h2 class="name-with-status"><?php if ($showStatusBadge): ?><span class="status-badge <?php echo $statusClasses[$moderationStatus]; ?>"><?php echo $statusLabels[$moderationStatus]; ?></span><?php endif; ?><?php echo htmlspecialchars($artist['name']); ?></h2>
            
            <div class="tags-container">
                <?php if ($artist['genre']): ?>
                    <span class="dash-tag genre-tag"><?php echo htmlspecialchars($artist['genre']); ?></span>
                <?php endif; ?>
                <?php if ($artist['council']): ?>
                    <span class="dash-tag location-tag">📍 <?php echo htmlspecialchars($artist['council']); ?></span>
                <?php endif; ?>
            </div>

            <div class="profile-actions">
                <a href="<?php echo VIEWS_URL; ?>edit_artist.php" class="btn-dash primary">Editar Perfil</a>
                <?php if ($moderationStatus === 'rejected'): ?>
                    <form method="POST" id="resubmitReviewForm" class="resubmit-review-form">
                        <input type="hidden" name="action" value="resubmit_review">
                        <button type="button" class="btn-dash warning" id="resubmitReviewBtn">Reenviar para revisão</button>
                    </form>
                <?php endif; ?>
                <a href="<?php echo VIEWS_URL; ?>logout_artist.php" class="btn-dash secondary">Terminar Sessão</a>
            </div>
            
            <div class="card-footer-info">
                <small>Membro desde <?php echo date('M Y', strtotime($artist['created_at'])); ?></small>
            </div>
        </div>

        <!-- Coluna Direita: Detalhes -->
        <div class="dash-card details-card">
            <div class="info-section">
                <h3>Informação de Contacto</h3>
                <div class="info-row">
                    <span class="label">Email</span>
                    <span class="value"><?php echo htmlspecialchars($artist['email']); ?></span>
                </div>
            </div>

            <div class="info-section">
                <h3>Biografia</h3>
                <div class="bio-box">
                    <?php if ($artist['bio']): ?>
                        <?php echo nl2br(htmlspecialchars($artist['bio'])); ?>
                    <?php else: ?>
                        <p class="empty-state">Ainda não adicionaste uma biografia. Edita o teu perfil para contar a tua história.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .dashboard-wrapper {
        max-width: 1100px;
        margin: 100px auto 40px;
        padding: 0 20px;
    }
    .dashboard-header {
        margin-bottom: 40px;
        border-bottom: 1px solid #222;
        padding-bottom: 20px;
    }
    .dashboard-header h1 { font-size: 2.5rem; margin: 0 0 10px 0; }
    .name-with-status {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .status-badge {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        padding: 4px 10px;
        border-radius: 999px;
        border: 1px solid transparent;
        line-height: 1;
    }
    .status-pending {
        color: #f0b43a;
        background: rgba(240, 180, 58, 0.14);
        border-color: rgba(240, 180, 58, 0.45);
    }
    .status-rejected {
        color: #ff7a7a;
        background: rgba(255, 122, 122, 0.14);
        border-color: rgba(255, 122, 122, 0.45);
    }
    .text-gradient {
        background: linear-gradient(90deg, #ff6b35, #f83600);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .dashboard-header p { color: #888; margin: 0; font-size: 1.1rem; }
    .dashboard-alert {
        margin: 0 0 20px;
        padding: 12px 14px;
        border-radius: 8px;
        border: 1px solid transparent;
        font-size: 0.95rem;
    }
    .dashboard-alert-success {
        color: #92f39f;
        background: rgba(99, 209, 111, 0.12);
        border-color: rgba(99, 209, 111, 0.35);
    }
    .dashboard-alert-error {
        color: #ff9a9a;
        background: rgba(255, 90, 90, 0.12);
        border-color: rgba(255, 90, 90, 0.35);
    }

    .dashboard-grid {
        display: grid;
        grid-template-columns: 350px 1fr;
        gap: 30px;
    }
    @media (max-width: 800px) {
        .dashboard-grid { grid-template-columns: 1fr; }
    }

    .dash-card {
        background: #141414;
        border: 1px solid #2a2a2a;
        border-radius: 12px;
        padding: 30px;
    }

    /* Profile Card */
    .profile-card { text-align: center; }
    .avatar-circle {
        width: 100px; height: 100px;
        background: #222;
        border-radius: 50%;
        margin: 0 auto 20px;
        display: flex; align-items: center; justify-content: center;
        font-size: 2.5rem; font-weight: bold; color: #ff6b35;
        border: 2px solid #333;
    }
    .profile-card h2 { margin: 0 0 15px 0; font-size: 1.8rem; }
    
    .tags-container { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; margin-bottom: 30px; }
    .dash-tag {
        font-size: 0.85rem; padding: 5px 12px; border-radius: 20px;
        background: #222; color: #ccc; border: 1px solid #333;
    }
    .genre-tag { color: #ff6b35; border-color: #ff6b3533; }

    .profile-actions { display: flex; flex-direction: column; gap: 10px; }
    .resubmit-review-form { width: 100%; }
    .btn-dash {
        display: block; width: 100%; padding: 12px; border-radius: 6px;
        text-align: center; text-decoration: none; font-weight: 600; transition: .2s;
        box-sizing: border-box;
    }
    .btn-dash.primary { background: #ff6b35; color: white; border: none; }
    .btn-dash.primary:hover { background: #e55a2b; }
    .btn-dash.warning {
        background: rgba(240, 180, 58, 0.14);
        color: #f0b43a;
        border: 1px solid rgba(240, 180, 58, 0.55);
        cursor: pointer;
    }
    .btn-dash.warning:hover {
        background: rgba(240, 180, 58, 0.22);
    }
    .btn-dash.secondary { background: transparent; color: #888; border: 1px solid #333; }
    .btn-dash.secondary:hover { border-color: #666; color: white; }

    .card-footer-info { margin-top: 20px; color: #555; font-size: 0.8rem; }

    /* Details Card */
    .info-section { margin-bottom: 30px; }
    .info-section h3 {
        font-size: 1.1rem; color: #888; text-transform: uppercase; letter-spacing: 1px;
        margin-bottom: 15px; border-bottom: 1px solid #222; padding-bottom: 10px;
    }
    .info-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #1a1a1a; }
    .info-row .label { color: #666; }
    .info-row .value { color: white; font-weight: 500; }

    .bio-box {
        background: #0a0a0a; padding: 20px; border-radius: 8px;
        line-height: 1.6; color: #ccc; border: 1px solid #222;
    }
    .empty-state { color: #555; font-style: italic; }
</style>

<script>
    (function () {
        const resubmitBtn = document.getElementById('resubmitReviewBtn');
        const resubmitForm = document.getElementById('resubmitReviewForm');

        if (!resubmitBtn || !resubmitForm) return;

        resubmitBtn.addEventListener('click', function () {
            const confirmed = window.confirm('Queres reenviar a tua conta para nova revisão do admin?');
            if (confirmed) {
                resubmitForm.submit();
            }
        });
    })();
</script>

<?php require __DIR__ . '/../inc/footer.php'; ?>