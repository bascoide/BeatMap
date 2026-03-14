<?php
session_start();
$pageTitle = 'Login Artista';
require __DIR__ . '/../inc/header.php';
require __DIR__ . '/../inc/db.php';

$moderationColCheck = $mysqli->query("SHOW COLUMNS FROM artists LIKE 'moderation_status'");
$hasModerationStatus = $moderationColCheck && $moderationColCheck->num_rows > 0;
$moderationColumnType = '';
if ($moderationColCheck instanceof mysqli_result) {
  $moderationColumn = $moderationColCheck->fetch_assoc();
  $moderationColumnType = strtolower((string)($moderationColumn['Type'] ?? ''));
}
if ($moderationColCheck instanceof mysqli_result) {
  $moderationColCheck->close();
}
if (!$hasModerationStatus) {
  $mysqli->query("ALTER TABLE artists ADD COLUMN moderation_status ENUM('pending','approved','rejected','banned') NOT NULL DEFAULT 'approved' AFTER is_confirmed");
} elseif ($moderationColumnType !== '' && strpos($moderationColumnType, "'banned'") === false) {
  $mysqli->query("ALTER TABLE artists MODIFY COLUMN moderation_status ENUM('pending','approved','rejected','banned') NOT NULL DEFAULT 'approved'");
}

// Se já está logado, redireciona
if (isset($_SESSION['artist_logged_in']) && $_SESSION['artist_logged_in']) {
    header('Location: ' . VIEWS_URL . 'artist_dashboard.php');
    exit;
}

$errors = [];
$success_message = '';

if (($_GET['status'] ?? '') === 'reset_success') {
  $success_message = 'Password alterada com sucesso. Faça login com a nova password.';
}

if (($_GET['status'] ?? '') === 'artist_rejected') {
  $errors[] = 'A conta foi recusada. Atualiza o perfil e envia novamente para revisão.';
}

if (($_GET['status'] ?? '') === 'artist_banned') {
  $errors[] = 'A conta foi banida. Contacta o administrador para mais informações.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email válido é obrigatório.';
    }
    
    if ($password === '') {
        $errors[] = 'Senha é obrigatória.';
    }
    
    if (!$errors) {
        // Buscar artista pelo email
        $stmt = $mysqli->prepare("SELECT * FROM artists WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $artist = $result->fetch_assoc();
        $stmt->close();
        
    if ($artist && password_verify($password, $artist['password_hash'])) {
      $artistStatus = strtolower(trim((string)($artist['moderation_status'] ?? 'approved')));

      if (!$artist['is_confirmed']) {
        $errors[] = 'Conta não confirmada. Verifique o seu email para confirmar. <a href="email/resend_confirmation.php">Reenviar email de confirmação</a>';
      } elseif ($artistStatus === 'rejected') {
        $errors[] = 'A conta foi recusada. Atualiza o perfil e envia novamente para revisão.';
      } elseif ($artistStatus === 'banned') {
        $errors[] = 'A conta foi banida. Contacta o administrador para mais informações.';
      } else {
        // Login bem-sucedido
        $_SESSION['artist_logged_in'] = true;
        $_SESSION['artist_id'] = $artist['id'];
        $_SESSION['artist_name'] = $artist['name'];
        $_SESSION['artist_email'] = $artist['email'];
        $_SESSION['artist_city'] = $artist['council'];
        $_SESSION['artist_moderation_status'] = $artistStatus;
                
        header('Location: artist_dashboard.php');
        exit;
      }
    } else {
      $errors[] = 'Email ou senha incorretos.';
    }
    }
}
?>

<h2>Login Artista</h2>

<?php if ($success_message): ?>
  <div class="alert success"><?php echo htmlspecialchars($success_message); ?></div>
<?php endif; ?>

<?php if ($errors): ?>
  <div class="alert error">
    <ul>
      <?php foreach ($errors as $e) echo '<li>'.htmlspecialchars($e).'</li>'; ?>
    </ul>
  </div>
<?php endif; ?>

<form method="post" action="" class="form-artist">
  <div class="form-group">
    <label for="email">Email</label>
    <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
  </div>
  
  <div class="form-group">
    <label for="password">Senha</label>
    <input type="password" name="password" id="password" required>
  </div>

  <div style="text-align: right; margin-top: -10px; margin-bottom: 20px;">
    <a href="/beatmap(index)/forgot_password.html" style="color: var(--accent-light); text-decoration: none;">Esqueceu-se da palavra-passe?</a>
  </div>
  
  <button type="submit" class="btn">Entrar</button>
  
  <div style="text-align: center; margin-top: 20px;">
    <p>Não tem conta? <a href="<?php echo VIEWS_URL; ?>add_artist.php" style="color: var(--accent-light);">Registe-se aqui</a></p>
  </div>
</form>

<?php require __DIR__ . '/../inc/footer.php'; ?>