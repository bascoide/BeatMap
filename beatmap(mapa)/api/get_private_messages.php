<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
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

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
    exit;
}

$artistId = (int)($_SESSION['artist_id'] ?? 0);
if ($artistId <= 0) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Apenas artistas podem ver mensagens privadas.',
    ]);
    exit;
}

$limit = (int)($_GET['limit'] ?? 40);
if ($limit <= 0) {
    $limit = 40;
}
$limit = min(100, $limit);
$markRead = (int)($_GET['mark_read'] ?? 0) === 1;
$includeMessages = (int)($_GET['include_messages'] ?? 1) !== 0;
$groupByArtist = (int)($_GET['group_by_artist'] ?? 0) === 1;
$conversationWith = (int)($_GET['conversation_with'] ?? 0);

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

function ensurePrivateMessagesIndexes(PDO $pdo): void {
    $indexStmt = $pdo->query('SHOW INDEX FROM private_messages');
    $existingIndexes = [];

    if ($indexStmt) {
        foreach ($indexStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $indexName = (string)($row['Key_name'] ?? '');
            if ($indexName !== '') {
                $existingIndexes[$indexName] = true;
            }
        }
    }

    $safeCreateIndex = function (string $indexName, string $sql) use ($pdo, $existingIndexes): void {
        if (!empty($existingIndexes[$indexName])) {
            return;
        }

        try {
            $pdo->exec($sql);
        } catch (Throwable $e) {
            // Ignora falhas por corrida (índice criado em paralelo) ou falta de permissões,
            // para não bloquear a API de mensagens privadas.
        }
    };

    $safeCreateIndex(
        'idx_pm_pair_sr_id',
        'CREATE INDEX idx_pm_pair_sr_id ON private_messages (sender_artist_id, recipient_artist_id, id)'
    );

    $safeCreateIndex(
        'idx_pm_pair_rs_id',
        'CREATE INDEX idx_pm_pair_rs_id ON private_messages (recipient_artist_id, sender_artist_id, id)'
    );

    $safeCreateIndex(
        'idx_pm_unread_recipient',
        'CREATE INDEX idx_pm_unread_recipient ON private_messages (recipient_artist_id, read_at, sender_artist_id, id)'
    );
}

function resolveArtistImageColumn(PDO $pdo): string {
    $stmt = $pdo->query('SHOW COLUMNS FROM artists');
    if (!$stmt) {
        return '';
    }

    $columns = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $field = (string)($row['Field'] ?? '');
        if ($field !== '') {
            $columns[$field] = true;
        }
    }

    if (!empty($columns['image'])) {
        return 'image';
    }

    if (!empty($columns['profile_picture'])) {
        return 'profile_picture';
    }

    return '';
}

try {
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

    ensurePrivateMessagesTable($pdo);
    ensurePrivateMessagesAudioColumns($pdo);
    ensurePrivateMessagesIndexes($pdo);
    $artistImageColumn = resolveArtistImageColumn($pdo);
    $otherArtistImageSelect = $artistImageColumn !== ''
        ? ('a.' . $artistImageColumn . ' AS other_artist_image')
        : "'' AS other_artist_image";

    if ($markRead && $conversationWith > 0) {
        $markStmt = $pdo->prepare(
            'UPDATE private_messages
             SET read_at = CURRENT_TIMESTAMP
             WHERE recipient_artist_id = :artist_id
               AND sender_artist_id = :other_artist_id
               AND read_at IS NULL'
        );
        $markStmt->execute([
            ':artist_id' => $artistId,
            ':other_artist_id' => $conversationWith,
        ]);
    } elseif ($markRead) {
        $markStmt = $pdo->prepare(
            'UPDATE private_messages
             SET read_at = CURRENT_TIMESTAMP
             WHERE recipient_artist_id = :artist_id
               AND sender_artist_id <> :artist_id_sender
               AND read_at IS NULL'
        );
        $markStmt->execute([
            ':artist_id' => $artistId,
            ':artist_id_sender' => $artistId,
        ]);
    }

    $unreadStmt = $pdo->prepare(
        'SELECT COUNT(*)
         FROM private_messages
         WHERE recipient_artist_id = :artist_id
           AND sender_artist_id <> :artist_id_sender
           AND read_at IS NULL'
    );
    $unreadStmt->execute([
        ':artist_id' => $artistId,
        ':artist_id_sender' => $artistId,
    ]);
    $unreadCount = (int)$unreadStmt->fetchColumn();

    $messages = [];
    $conversations = [];
    $lastId = 0;

    if ($includeMessages && $conversationWith > 0) {
        $stmt = $pdo->prepare(
                 'SELECT recent.id, recent.sender_artist_id, recent.recipient_artist_id, recent.message,
                      recent.audio_path, recent.audio_original_name, recent.audio_mime_type,
                      recent.audio_size_bytes, recent.audio_duration_seconds, recent.audio_source,
                      recent.created_at,
                    recent.sender_name, recent.recipient_name, recent.other_artist_id, recent.other_artist_name
             FROM (
                  SELECT pm.id, pm.sender_artist_id, pm.recipient_artist_id, pm.message,
                      pm.audio_path, pm.audio_original_name, pm.audio_mime_type,
                      pm.audio_size_bytes, pm.audio_duration_seconds, pm.audio_source,
                      pm.created_at,
                       s.name AS sender_name,
                       r.name AS recipient_name,
                       CASE
                           WHEN pm.sender_artist_id = :artist_id_case THEN pm.recipient_artist_id
                           ELSE pm.sender_artist_id
                       END AS other_artist_id,
                       CASE
                           WHEN pm.sender_artist_id = :artist_id_case2 THEN r.name
                           ELSE s.name
                       END AS other_artist_name
                FROM private_messages pm
                INNER JOIN artists s ON s.id = pm.sender_artist_id
                INNER JOIN artists r ON r.id = pm.recipient_artist_id
                WHERE (
                       pm.sender_artist_id = :artist_id_sender
                       AND pm.recipient_artist_id = :conversation_with_recipient
                      )
                   OR (
                       pm.sender_artist_id = :conversation_with_sender
                       AND pm.recipient_artist_id = :artist_id_recipient
                      )
                ORDER BY pm.id DESC
                LIMIT ' . (int)$limit . '
             ) recent
             ORDER BY recent.id ASC'
        );

        $stmt->execute([
            ':artist_id_case' => $artistId,
            ':artist_id_case2' => $artistId,
            ':artist_id_sender' => $artistId,
            ':conversation_with_recipient' => $conversationWith,
            ':conversation_with_sender' => $conversationWith,
            ':artist_id_recipient' => $artistId,
        ]);

        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($messages as &$row) {
            $messageId = (int)($row['id'] ?? 0);
            if ($messageId > $lastId) {
                $lastId = $messageId;
            }

            $row['id'] = $messageId;
            $row['sender_artist_id'] = (int)($row['sender_artist_id'] ?? 0);
            $row['recipient_artist_id'] = (int)($row['recipient_artist_id'] ?? 0);
            $row['other_artist_id'] = (int)($row['other_artist_id'] ?? 0);
            $row['audio_size_bytes'] = isset($row['audio_size_bytes']) ? (int)$row['audio_size_bytes'] : null;
            $row['audio_duration_seconds'] = isset($row['audio_duration_seconds']) ? (float)$row['audio_duration_seconds'] : null;
        }
        unset($row);
    } elseif ($includeMessages && $groupByArtist) {
        $stmt = $pdo->prepare(
            'SELECT c.other_artist_id,
                    a.name AS other_artist_name,
                    ' . $otherArtistImageSelect . ',
                    pm.id AS last_message_id,
                    pm.sender_artist_id AS last_sender_artist_id,
                    pm.recipient_artist_id AS last_recipient_artist_id,
                    pm.message AS last_message,
                    pm.audio_path AS last_audio_path,
                    pm.audio_source AS last_audio_source,
                    pm.created_at AS last_created_at,
                    CASE
                        WHEN COALESCE(pm.message, \'\') <> \'\' THEN pm.message
                        WHEN pm.audio_path IS NOT NULL AND pm.audio_source = \'voice_recording\' THEN \'Mensagem de voz\'
                        WHEN pm.audio_path IS NOT NULL THEN \'Ficheiro de áudio\'
                        ELSE \'\'
                    END AS last_message_preview,
                    (
                        SELECT COUNT(*)
                        FROM private_messages pu
                        WHERE pu.sender_artist_id = c.other_artist_id
                          AND pu.recipient_artist_id = :artist_id_unread
                          AND pu.read_at IS NULL
                    ) AS unread_count
             FROM (
                 SELECT
                     CASE
                         WHEN sender_artist_id = :artist_id_case_group THEN recipient_artist_id
                         ELSE sender_artist_id
                     END AS other_artist_id,
                     MAX(id) AS last_message_id
                 FROM private_messages
                 WHERE sender_artist_id = :artist_id_sender_group
                    OR recipient_artist_id = :artist_id_recipient_group
                 GROUP BY other_artist_id
             ) c
             INNER JOIN private_messages pm ON pm.id = c.last_message_id
             INNER JOIN artists a ON a.id = c.other_artist_id
             ORDER BY pm.created_at DESC, pm.id DESC
             LIMIT ' . (int)$limit
        );

        $stmt->execute([
            ':artist_id_unread' => $artistId,
            ':artist_id_case_group' => $artistId,
            ':artist_id_sender_group' => $artistId,
            ':artist_id_recipient_group' => $artistId,
        ]);

        $conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($conversations as &$row) {
            $row['other_artist_id'] = (int)($row['other_artist_id'] ?? 0);
            $row['last_message_id'] = (int)($row['last_message_id'] ?? 0);
            $row['last_sender_artist_id'] = (int)($row['last_sender_artist_id'] ?? 0);
            $row['last_recipient_artist_id'] = (int)($row['last_recipient_artist_id'] ?? 0);
            $row['unread_count'] = (int)($row['unread_count'] ?? 0);

            $messageId = (int)($row['last_message_id'] ?? 0);
            if ($messageId > $lastId) {
                $lastId = $messageId;
            }
        }
        unset($row);
    } elseif ($includeMessages) {
        $stmt = $pdo->prepare(
                'SELECT pm.id, pm.sender_artist_id, pm.recipient_artist_id, pm.message,
                    pm.audio_path, pm.audio_original_name, pm.audio_mime_type,
                    pm.audio_size_bytes, pm.audio_duration_seconds, pm.audio_source,
                    pm.created_at,
                    s.name AS sender_name,
                    r.name AS recipient_name,
                    CASE
                        WHEN pm.sender_artist_id = :artist_id_case THEN pm.recipient_artist_id
                        ELSE pm.sender_artist_id
                    END AS other_artist_id,
                    CASE
                        WHEN pm.sender_artist_id = :artist_id_case2 THEN r.name
                        ELSE s.name
                    END AS other_artist_name
             FROM private_messages pm
             INNER JOIN artists s ON s.id = pm.sender_artist_id
             INNER JOIN artists r ON r.id = pm.recipient_artist_id
             WHERE pm.sender_artist_id = :artist_id_sender
                OR pm.recipient_artist_id = :artist_id_recipient
             ORDER BY pm.created_at DESC, pm.id DESC
             LIMIT ' . (int)$limit
        );

        $stmt->execute([
            ':artist_id_case' => $artistId,
            ':artist_id_case2' => $artistId,
            ':artist_id_sender' => $artistId,
            ':artist_id_recipient' => $artistId,
        ]);

        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($messages as &$row) {
            $messageId = (int)($row['id'] ?? 0);
            if ($messageId > $lastId) {
                $lastId = $messageId;
            }

            $row['id'] = $messageId;
            $row['sender_artist_id'] = (int)($row['sender_artist_id'] ?? 0);
            $row['recipient_artist_id'] = (int)($row['recipient_artist_id'] ?? 0);
            $row['other_artist_id'] = (int)($row['other_artist_id'] ?? 0);
            $row['audio_size_bytes'] = isset($row['audio_size_bytes']) ? (int)$row['audio_size_bytes'] : null;
            $row['audio_duration_seconds'] = isset($row['audio_duration_seconds']) ? (float)$row['audio_duration_seconds'] : null;
        }
        unset($row);
    }

    echo json_encode([
        'success' => true,
        'messages' => $messages,
        'conversations' => $conversations,
        'last_id' => $lastId,
        'unread_count' => $unreadCount,
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao carregar mensagens privadas.',
    ]);
}
