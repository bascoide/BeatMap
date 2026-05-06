<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$cookieDomain = $_SERVER['HTTP_HOST'] ?? '';
$cookieDomain = preg_replace('/:\\d+$/', '', $cookieDomain);
if ($cookieDomain === 'localhost' || $cookieDomain === '') {
    session_set_cookie_params(0, '/');
} else {
    session_set_cookie_params(0, '/', '.' . $cookieDomain);
}
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
    exit;
}

const PRIVATE_MESSAGE_MAX_LENGTH = 255;
const PRIVATE_AUDIO_MAX_BYTES = 10485760;
const PRIVATE_VOICE_MAX_DURATION_SECONDS = 60;

$senderArtistId = (int)($_SESSION['artist_id'] ?? 0);
if ($senderArtistId <= 0) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Apenas artistas podem enviar mensagens privadas.',
    ]);
    exit;
}

$contentType = strtolower((string)($_SERVER['CONTENT_TYPE'] ?? ''));
$input = null;

if (strpos($contentType, 'application/json') !== false) {
    $input = json_decode(file_get_contents('php://input'), true);
}

if (!is_array($input)) {
    $input = $_POST;
}

$recipientArtistId = (int)($input['recipient_artist_id'] ?? 0);
$message = trim((string)($input['message'] ?? ''));
$audioSource = trim((string)($input['audio_source'] ?? ''));
$audioDurationSeconds = isset($input['audio_duration_seconds'])
    ? (float)$input['audio_duration_seconds']
    : null;
$audioUpload = $_FILES['audio'] ?? null;

if ($recipientArtistId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'recipient_artist_id inválido.']);
    exit;
}

if ($recipientArtistId === $senderArtistId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Não podes enviar mensagem para a tua própria conta.']);
    exit;
}

if ($message === '' && empty($audioUpload['tmp_name'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Envia uma mensagem ou um ficheiro de áudio.']);
    exit;
}

if (function_exists('mb_strlen')) {
    if (mb_strlen($message, 'UTF-8') > PRIVATE_MESSAGE_MAX_LENGTH) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'A mensagem deve ter no máximo 255 caracteres.']);
        exit;
    }
} elseif (strlen($message) > PRIVATE_MESSAGE_MAX_LENGTH) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'A mensagem deve ter no máximo 255 caracteres.']);
    exit;
}

function ensureArtistModerationStatusColumn(PDO $pdo): void {
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

function ensurePrivateMessagesTable(PDO $pdo): void {
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS private_messages (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            sender_artist_id INT NOT NULL,
            recipient_artist_id INT NOT NULL,
            message VARCHAR(255) NULL DEFAULT NULL,
            audio_path VARCHAR(255) NULL DEFAULT NULL,
            audio_original_name VARCHAR(255) NULL DEFAULT NULL,
            audio_mime_type VARCHAR(100) NULL DEFAULT NULL,
            audio_size_bytes INT UNSIGNED NULL DEFAULT NULL,
            audio_duration_seconds DECIMAL(6,2) NULL DEFAULT NULL,
            audio_source VARCHAR(32) NULL DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            read_at TIMESTAMP NULL DEFAULT NULL,
            KEY idx_private_messages_recipient (recipient_artist_id, created_at),
            KEY idx_private_messages_sender (sender_artist_id, created_at),
            CONSTRAINT fk_private_messages_sender
                FOREIGN KEY (sender_artist_id)
                REFERENCES artists(id)
                ON DELETE CASCADE,
            CONSTRAINT fk_private_messages_recipient
                FOREIGN KEY (recipient_artist_id)
                REFERENCES artists(id)
                ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
    );
}

function ensurePrivateMessagesAudioColumns(PDO $pdo): void {
    $stmt = $pdo->query('SHOW COLUMNS FROM private_messages');
    if (!$stmt) {
        return;
    }

    $columns = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $field = (string)($row['Field'] ?? '');
        if ($field !== '') {
            $columns[$field] = $row;
        }
    }

    if (!isset($columns['message'])) {
        $pdo->exec('ALTER TABLE private_messages ADD COLUMN message VARCHAR(255) NULL DEFAULT NULL AFTER recipient_artist_id');
    } else {
        $messageType = strtolower((string)($columns['message']['Type'] ?? ''));
        $messageNullable = strtoupper((string)($columns['message']['Null'] ?? 'NO')) === 'YES';
        if ($messageType !== 'varchar(255)' || !$messageNullable) {
            $pdo->exec('ALTER TABLE private_messages MODIFY COLUMN message VARCHAR(255) NULL DEFAULT NULL');
        }
    }

    $definitions = [
        'audio_path' => 'ALTER TABLE private_messages ADD COLUMN audio_path VARCHAR(255) NULL DEFAULT NULL AFTER message',
        'audio_original_name' => 'ALTER TABLE private_messages ADD COLUMN audio_original_name VARCHAR(255) NULL DEFAULT NULL AFTER audio_path',
        'audio_mime_type' => 'ALTER TABLE private_messages ADD COLUMN audio_mime_type VARCHAR(100) NULL DEFAULT NULL AFTER audio_original_name',
        'audio_size_bytes' => 'ALTER TABLE private_messages ADD COLUMN audio_size_bytes INT UNSIGNED NULL DEFAULT NULL AFTER audio_mime_type',
        'audio_duration_seconds' => 'ALTER TABLE private_messages ADD COLUMN audio_duration_seconds DECIMAL(6,2) NULL DEFAULT NULL AFTER audio_size_bytes',
        'audio_source' => 'ALTER TABLE private_messages ADD COLUMN audio_source VARCHAR(32) NULL DEFAULT NULL AFTER audio_duration_seconds',
    ];

    foreach ($definitions as $column => $sql) {
        if (!isset($columns[$column])) {
            $pdo->exec($sql);
        }
    }
}

function getPrivateMessagesBaseUrl(): string {
    $scriptName = str_replace('\\', '/', (string)($_SERVER['SCRIPT_NAME'] ?? ''));
    $basePath = preg_replace('#/api/[^/]+$#', '', $scriptName);
    $basePath = rtrim((string)$basePath, '/');
    return $basePath !== '' ? $basePath : '';
}

function createPrivateAudioUploadDirectory(): string {
    $directory = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'private-audio';

    if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
        throw new RuntimeException('Não foi possível criar a pasta de áudio privado.');
    }

    return $directory;
}

function detectAudioMimeType(string $filePath): string {
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $mimeType = (string)finfo_file($finfo, $filePath);
            finfo_close($finfo);
            if ($mimeType !== '') {
                return strtolower($mimeType);
            }
        }
    }

    if (function_exists('mime_content_type')) {
        return strtolower((string)(mime_content_type($filePath) ?: ''));
    }

    return '';
}

function getAllowedAudioMimeTypes(): array {
    return [
        'audio/webm' => 'webm',
        'video/webm' => 'webm',
        'application/webm' => 'webm',
        'audio/ogg' => 'ogg',
        'video/ogg' => 'ogg',
        'application/ogg' => 'ogg',
        'audio/wav' => 'wav',
        'audio/x-wav' => 'wav',
        'audio/wave' => 'wav',
        'audio/mpeg' => 'mp3',
        'audio/mp3' => 'mp3',
        'audio/mp4' => 'm4a',
        'audio/x-m4a' => 'm4a',
        'audio/aac' => 'aac',
        'audio/flac' => 'flac',
        'audio/x-flac' => 'flac',
        'application/octet-stream' => null,
    ];
}

function normalizeUploadedMimeType(?string $mimeType): string {
    $normalized = strtolower(trim((string)$mimeType));
    if ($normalized === '') {
        return '';
    }

    $parts = explode(';', $normalized, 2);
    return trim((string)($parts[0] ?? ''));
}

function getAllowedAudioExtensions(): array {
    return [
        'webm' => 'webm',
        'ogg' => 'ogg',
        'wav' => 'wav',
        'mp3' => 'mp3',
        'm4a' => 'm4a',
        'aac' => 'aac',
        'flac' => 'flac',
    ];
}

function resolveAudioExtension(array $audioUpload, string $detectedMimeType): array {
    $allowedMimeTypes = getAllowedAudioMimeTypes();
    $allowedExtensions = getAllowedAudioExtensions();
    $clientMimeType = normalizeUploadedMimeType((string)($audioUpload['type'] ?? ''));
    $originalName = (string)($audioUpload['name'] ?? '');
    $nameExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    $candidateMimeTypes = array_values(array_unique(array_filter([
        normalizeUploadedMimeType($detectedMimeType),
        $clientMimeType,
    ])));

    foreach ($candidateMimeTypes as $candidateMimeType) {
        if (array_key_exists($candidateMimeType, $allowedMimeTypes) && $allowedMimeTypes[$candidateMimeType] !== null) {
            return [
                'mime_type' => $candidateMimeType,
                'extension' => $allowedMimeTypes[$candidateMimeType],
            ];
        }
    }

    if ($nameExtension !== '' && isset($allowedExtensions[$nameExtension])) {
        $fallbackMimeType = $clientMimeType !== '' && array_key_exists($clientMimeType, $allowedMimeTypes)
            ? $clientMimeType
            : ('audio/' . $allowedExtensions[$nameExtension]);

        return [
            'mime_type' => $fallbackMimeType,
            'extension' => $allowedExtensions[$nameExtension],
        ];
    }

    throw new RuntimeException('Formato de áudio não suportado.');
}

function normalizeAudioSource(?string $audioSource): ?string {
    $normalized = strtolower(trim((string)$audioSource));
    if ($normalized === '') {
        return null;
    }

    if ($normalized === 'voice_recording') {
        return 'voice_recording';
    }

    return 'audio_file';
}

function storePrivateAudioUpload(array $audioUpload, ?string $audioSource, ?float $audioDurationSeconds): array {
    if (($audioUpload['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return [];
    }

    if (($audioUpload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Falha no upload do ficheiro de áudio.');
    }

    $tmpName = (string)($audioUpload['tmp_name'] ?? '');
    if ($tmpName === '' || !is_uploaded_file($tmpName)) {
        throw new RuntimeException('Upload de áudio inválido.');
    }

    $sizeBytes = (int)($audioUpload['size'] ?? 0);
    if ($sizeBytes <= 0) {
        throw new RuntimeException('O ficheiro de áudio está vazio.');
    }

    if ($sizeBytes > PRIVATE_AUDIO_MAX_BYTES) {
        throw new RuntimeException('O ficheiro de áudio deve ter no máximo 10 MB.');
    }

    $audioFormat = resolveAudioExtension($audioUpload, detectAudioMimeType($tmpName));
    $mimeType = (string)$audioFormat['mime_type'];
    $extension = (string)$audioFormat['extension'];

    $normalizedAudioSource = normalizeAudioSource($audioSource);
    if ($normalizedAudioSource === 'voice_recording') {
        if ($audioDurationSeconds === null || $audioDurationSeconds <= 0) {
            throw new RuntimeException('A duração da mensagem de voz é inválida.');
        }

        if ($audioDurationSeconds > PRIVATE_VOICE_MAX_DURATION_SECONDS + 0.5) {
            throw new RuntimeException('A mensagem de voz pode ter no máximo 1 minuto.');
        }
    }

    $uploadDirectory = createPrivateAudioUploadDirectory();
    $storedFileName = sprintf('pm-audio-%s-%s.%s', date('YmdHis'), bin2hex(random_bytes(6)), $extension);
    $absolutePath = $uploadDirectory . DIRECTORY_SEPARATOR . $storedFileName;

    if (!move_uploaded_file($tmpName, $absolutePath)) {
        throw new RuntimeException('Não foi possível guardar o ficheiro de áudio.');
    }

    $baseUrl = getPrivateMessagesBaseUrl();
    $publicPath = ($baseUrl !== '' ? $baseUrl : '') . '/uploads/private-audio/' . $storedFileName;

    return [
        'audio_path' => $publicPath,
        'audio_original_name' => trim((string)($audioUpload['name'] ?? '')),
        'audio_mime_type' => $mimeType,
        'audio_size_bytes' => $sizeBytes,
        'audio_duration_seconds' => $audioDurationSeconds,
        'audio_source' => $normalizedAudioSource,
        'absolute_path' => $absolutePath,
    ];
}

try {
    $storedAudio = [];
    $host = 'localhost';
    $db = 'beatmap';
    $user = 'root';
    $pass = '';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    ensureArtistModerationStatusColumn($pdo);
    ensurePrivateMessagesTable($pdo);
    ensurePrivateMessagesAudioColumns($pdo);

    $senderStmt = $pdo->prepare('SELECT id, moderation_status, is_confirmed FROM artists WHERE id = ? LIMIT 1');
    $senderStmt->execute([$senderArtistId]);
    $sender = $senderStmt->fetch(PDO::FETCH_ASSOC);

    if (!$sender || (int)($sender['is_confirmed'] ?? 0) !== 1 || (string)($sender['moderation_status'] ?? 'approved') !== 'approved') {
        $_SESSION['artist_moderation_status'] = (string)($sender['moderation_status'] ?? 'pending');
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'A tua conta ainda está pendente. Não podes enviar mensagens privadas.',
        ]);
        exit;
    }

    $_SESSION['artist_moderation_status'] = (string)($sender['moderation_status'] ?? 'approved');

    $recipientStmt = $pdo->prepare('SELECT id, name, is_confirmed FROM artists WHERE id = ? LIMIT 1');
    $recipientStmt->execute([$recipientArtistId]);
    $recipient = $recipientStmt->fetch(PDO::FETCH_ASSOC);

    if (!$recipient || (int)($recipient['is_confirmed'] ?? 0) !== 1) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Artista de destino não encontrado.']);
        exit;
    }

    $storedAudio = storePrivateAudioUpload($audioUpload ?? [], $audioSource, $audioDurationSeconds);

    $pdo->beginTransaction();

    $insertStmt = $pdo->prepare(
        'INSERT INTO private_messages (
            sender_artist_id,
            recipient_artist_id,
            message,
            audio_path,
            audio_original_name,
            audio_mime_type,
            audio_size_bytes,
            audio_duration_seconds,
            audio_source
        ) VALUES (
            :sender_artist_id,
            :recipient_artist_id,
            :message,
            :audio_path,
            :audio_original_name,
            :audio_mime_type,
            :audio_size_bytes,
            :audio_duration_seconds,
            :audio_source
        )'
    );
    $insertStmt->execute([
        ':sender_artist_id' => $senderArtistId,
        ':recipient_artist_id' => $recipientArtistId,
        ':message' => $message !== '' ? $message : null,
        ':audio_path' => $storedAudio['audio_path'] ?? null,
        ':audio_original_name' => $storedAudio['audio_original_name'] ?? null,
        ':audio_mime_type' => $storedAudio['audio_mime_type'] ?? null,
        ':audio_size_bytes' => $storedAudio['audio_size_bytes'] ?? null,
        ':audio_duration_seconds' => $storedAudio['audio_duration_seconds'] ?? null,
        ':audio_source' => $storedAudio['audio_source'] ?? null,
    ]);

    $messageId = (int)$pdo->lastInsertId();

    $fetchStmt = $pdo->prepare(
        'SELECT pm.id, pm.sender_artist_id, pm.recipient_artist_id, pm.message,
                pm.audio_path, pm.audio_original_name, pm.audio_mime_type,
                pm.audio_size_bytes, pm.audio_duration_seconds, pm.audio_source,
                pm.created_at,
                s.name AS sender_name, r.name AS recipient_name
         FROM private_messages pm
         INNER JOIN artists s ON s.id = pm.sender_artist_id
         INNER JOIN artists r ON r.id = pm.recipient_artist_id
         WHERE pm.id = ? LIMIT 1'
    );
    $fetchStmt->execute([$messageId]);
    $inserted = $fetchStmt->fetch(PDO::FETCH_ASSOC);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Mensagem privada enviada.',
        'private_message' => $inserted,
    ]);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    if (!empty($storedAudio['absolute_path']) && is_file($storedAudio['absolute_path'])) {
        @unlink($storedAudio['absolute_path']);
    }

    http_response_code($e instanceof RuntimeException ? 400 : 500);
    echo json_encode([
        'success' => false,
        'message' => $e instanceof RuntimeException
            ? $e->getMessage()
            : 'Erro ao enviar mensagem privada.',
    ]);
}
