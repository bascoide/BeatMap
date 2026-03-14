<?php
$DB_HOST = '127.0.0.1';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'beatmap';

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) {
    error_log('BD Error: (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
    die('Desculpe, o servidor está indisponível. Por favor tente novamente mais tarde.');
}
$mysqli->set_charset('utf8mb4');

function ensureArtistModerationInfrastructure(mysqli $mysqli): void
{
    try {
        $check = $mysqli->query("SHOW COLUMNS FROM artists LIKE 'moderation_status'");
        $column = $check ? $check->fetch_assoc() : null;
        if ($check instanceof mysqli_result) {
            $check->close();
        }

        if (!$column) {
            $mysqli->query("ALTER TABLE artists ADD COLUMN moderation_status ENUM('pending','approved','rejected','banned') NOT NULL DEFAULT 'approved' AFTER is_confirmed");
        } else {
            $columnType = strtolower((string)($column['Type'] ?? ''));
            if ($columnType !== '' && strpos($columnType, "'banned'") === false) {
                $mysqli->query("ALTER TABLE artists MODIFY COLUMN moderation_status ENUM('pending','approved','rejected','banned') NOT NULL DEFAULT 'approved'");
            }
        }

        $mysqli->query(
            'CREATE TABLE IF NOT EXISTS artist_moderation_history (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                artist_id INT NOT NULL,
                previous_status VARCHAR(20) NOT NULL,
                new_status VARCHAR(20) NOT NULL,
                changed_by ENUM("system", "artist", "admin") NOT NULL DEFAULT "system",
                changed_by_id INT DEFAULT NULL,
                change_note VARCHAR(255) DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                KEY idx_artist_mod_history_artist (artist_id),
                KEY idx_artist_mod_history_new_status (new_status),
                KEY idx_artist_mod_history_created_at (created_at),
                CONSTRAINT fk_artist_mod_history_artist
                    FOREIGN KEY (artist_id)
                    REFERENCES artists(id)
                    ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );

        $triggerCheck = $mysqli->query(
            "SELECT TRIGGER_NAME FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA = DATABASE() AND TRIGGER_NAME = 'trg_artists_moderation_history_after_update' LIMIT 1"
        );
        $hasTrigger = $triggerCheck && $triggerCheck->num_rows > 0;
        if ($triggerCheck instanceof mysqli_result) {
            $triggerCheck->close();
        }

        if (!$hasTrigger) {
            $mysqli->query(
                "CREATE TRIGGER trg_artists_moderation_history_after_update AFTER UPDATE ON artists FOR EACH ROW INSERT INTO artist_moderation_history (artist_id, previous_status, new_status, changed_by, change_note) SELECT NEW.id, OLD.moderation_status, NEW.moderation_status, 'system', 'Alteração de estado da conta' WHERE COALESCE(OLD.moderation_status, 'approved') <> COALESCE(NEW.moderation_status, 'approved')"
            );
        }
    } catch (Throwable $e) {
        error_log('Erro ao garantir infraestrutura de moderação: ' . $e->getMessage());
    }
}

ensureArtistModerationInfrastructure($mysqli);

function ensureDefaultArtistAvatar(mysqli $mysqli): void
{
    try {
        $defaultAvatarPath = 'assets/default-avatar.png';
        $oldDefaultAvatarPath = 'assets/default-avatar.svg';
        $stmt = $mysqli->prepare("UPDATE artists SET profile_picture = ? WHERE profile_picture IS NULL OR TRIM(profile_picture) = '' OR profile_picture = ?");
        if ($stmt) {
            $stmt->bind_param('ss', $defaultAvatarPath, $oldDefaultAvatarPath);
            $stmt->execute();
            $stmt->close();
        }

        // Define default in schema for future inserts made outside the registration flow.
        $mysqli->query("ALTER TABLE artists ALTER profile_picture SET DEFAULT 'assets/default-avatar.png'");
    } catch (Throwable $e) {
        error_log('Erro ao garantir avatar padrão: ' . $e->getMessage());
    }
}

ensureDefaultArtistAvatar($mysqli);
?>