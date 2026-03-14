<?php
// Forçar a exibição de todos os erros para depuração.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Garantir que a resposta seja sempre JSON, mesmo em caso de erro fatal.
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$cookieDomain = $_SERVER['HTTP_HOST'] ?? '';
$cookieDomain = preg_replace('/:\\d+$/', '', $cookieDomain);
if ($cookieDomain === 'localhost' || $cookieDomain === '') {
    session_set_cookie_params(0, '/');
} else {
    session_set_cookie_params(0, '/', '.' . $cookieDomain);
}
session_start();

require_once __DIR__ . '/../../beatmap/inc/available_genres.php';

function normalizeArtistsForMap(array $artists): array {
    return array_map(function ($artist) {
        $profilePicture = $artist['profile_picture'] ?? '';

        if (!empty($profilePicture)) {
            if (preg_match('/^https?:\/\//i', $profilePicture)) {
                $artist['image'] = $profilePicture;
            } elseif (str_starts_with($profilePicture, '/')) {
                $artist['image'] = $profilePicture;
            } else {
                $artist['image'] = '/beatmap/' . ltrim($profilePicture, '/');
            }
        } else {
            $artist['image'] = null;
        }

        return $artist;
    }, $artists);
}

function getCurrentVoterToken(): ?string {
    if (isset($_SESSION['artist_id'])) {
        return 'artist_' . (int)$_SESSION['artist_id'];
    }

    if (isset($_SESSION['user_id'])) {
        return 'user_' . (int)$_SESSION['user_id'];
    }

    return null;
}

function getCurrentLoggedArtistId(): ?int {
    if (!isset($_SESSION['artist_id'])) {
        return null;
    }

    $artistId = (int)$_SESSION['artist_id'];
    return $artistId > 0 ? $artistId : null;
}

function excludeLoggedArtistFromList(array $artists, ?int $loggedArtistId): array {
    // Keep backward compatibility with existing call sites: the logged artist
    // must now remain visible in the map lists.
    return $artists;
}

function ensureArtistUpvotesTable(PDO $pdo): void {
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS artist_upvotes (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            artist_id INT NOT NULL,
            voter_token VARCHAR(64) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_artist_voter (artist_id, voter_token),
            KEY idx_artist_upvotes_artist (artist_id),
            CONSTRAINT fk_artist_upvotes_artist
                FOREIGN KEY (artist_id)
                REFERENCES artists(id)
                ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
    );
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

function attachArtistUpvoteStats(PDO $pdo, array $artists, ?string $voterToken): array {
    if (empty($artists)) {
        return $artists;
    }

    ensureArtistUpvotesTable($pdo);

    $artistIds = [];
    foreach ($artists as $artist) {
        $artistId = (int)($artist['id'] ?? 0);
        if ($artistId > 0) {
            $artistIds[$artistId] = true;
        }
    }

    $artistIds = array_keys($artistIds);
    if (empty($artistIds)) {
        return $artists;
    }

    $placeholders = implode(',', array_fill(0, count($artistIds), '?'));

    $countsStmt = $pdo->prepare(
        "SELECT artist_id, COUNT(*) AS total_upvotes
         FROM artist_upvotes
         WHERE artist_id IN ($placeholders)
         GROUP BY artist_id"
    );
    $countsStmt->execute($artistIds);

    $countsByArtistId = [];
    foreach ($countsStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $countsByArtistId[(int)$row['artist_id']] = (int)$row['total_upvotes'];
    }

    $userUpvotesLookup = [];
    if (!empty($voterToken)) {
        $userStmt = $pdo->prepare(
            "SELECT artist_id
             FROM artist_upvotes
             WHERE voter_token = ? AND artist_id IN ($placeholders)"
        );
        $userStmt->execute(array_merge([$voterToken], $artistIds));

        foreach ($userStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $userUpvotesLookup[(int)$row['artist_id']] = true;
        }
    }

    return array_map(function ($artist) use ($countsByArtistId, $userUpvotesLookup) {
        $artistId = (int)($artist['id'] ?? 0);
        $artist['upvotes'] = (int)($countsByArtistId[$artistId] ?? 0);
        $artist['has_upvoted'] = isset($userUpvotesLookup[$artistId]);
        return $artist;
    }, $artists);
}

function normalizeArtistsWithUpvotes(PDO $pdo, array $artists, ?string $voterToken): array {
    $normalized = normalizeArtistsForMap($artists);
    return attachArtistUpvoteStats($pdo, $normalized, $voterToken);
}

function normalizeLocationValue($value): string {
    $clean = trim((string) ($value ?? ''));
    $clean = preg_replace('/\s+/u', ' ', $clean);
    return $clean ?? '';
}

function pushUniqueLocation(array &$target, array &$lookup, $value): void {
    $normalized = normalizeLocationValue($value);
    if ($normalized === '') {
        return;
    }

    $key = function_exists('mb_strtolower')
        ? mb_strtolower($normalized, 'UTF-8')
        : strtolower($normalized);

    if (!isset($lookup[$key])) {
        $lookup[$key] = true;
        $target[] = $normalized;
    }
}

function getAllowedGenres(): array {
    return getAvailableArtistGenres();
}

function normalizeGenreKey(string $value): string {
    $value = trim($value);
    $value = preg_replace('/\s+/u', ' ', $value);

    if ($value === '') {
        return '';
    }

    if (function_exists('mb_strtolower')) {
        return mb_strtolower($value, 'UTF-8');
    }

    return strtolower($value);
}

function splitArtistGenres(?string $value): array {
    $raw = (string)($value ?? '');
    if (trim($raw) === '') {
        return [];
    }

    $parts = preg_split('/\s*[,;|\/]\s*/u', $raw);
    if (!is_array($parts)) {
        return [];
    }

    $normalized = [];
    foreach ($parts as $part) {
        $clean = trim((string)$part);
        if ($clean !== '') {
            $normalized[] = $clean;
        }
    }

    return array_values(array_unique($normalized));
}

try {
    // Configuração da Base de Dados
    $host = 'localhost';
    $db   = 'beatmap';
    $user = 'root';
    $pass = '';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    $pdo = new PDO($dsn, $user, $pass, $options);
    ensureArtistModerationStatusColumn($pdo);
    $voterToken = getCurrentVoterToken();
    $loggedArtistId = getCurrentLoggedArtistId();

    // Obter parâmetros da query string
    $council = isset($_GET['council']) ? $_GET['council'] : '';
    $district = isset($_GET['district']) ? $_GET['district'] : '';
    $genre = isset($_GET['genre']) ? $_GET['genre'] : '';
    $genres = isset($_GET['genres']) ? $_GET['genres'] : '';
    $summary = isset($_GET['summary']) ? trim((string) $_GET['summary']) : '';

    if ($summary === 'locations') {
        $columnsStmt = $pdo->query('SHOW COLUMNS FROM artists');
        $columns = array_column($columnsStmt->fetchAll(), 'Field');
        $columnSet = array_fill_keys($columns, true);

        $councils = [];
        $councilsLookup = [];
        $districts = [];
        $districtsLookup = [];

        if (isset($columnSet['council'])) {
            $stmt = $pdo->query(
                "SELECT DISTINCT council FROM artists WHERE is_confirmed = 1 AND moderation_status = 'approved' AND council IS NOT NULL AND TRIM(council) <> ''"
            );
            foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $value) {
                pushUniqueLocation($councils, $councilsLookup, $value);
            }
        }

        foreach (['district', 'district_name', 'region'] as $districtColumn) {
            if (!isset($columnSet[$districtColumn])) {
                continue;
            }

            $stmt = $pdo->query(
                "SELECT DISTINCT $districtColumn FROM artists WHERE is_confirmed = 1 AND moderation_status = 'approved' AND $districtColumn IS NOT NULL AND TRIM($districtColumn) <> ''"
            );
            foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $value) {
                pushUniqueLocation($districts, $districtsLookup, $value);
            }
        }

        usort($councils, 'strnatcasecmp');
        usort($districts, 'strnatcasecmp');

        echo json_encode([
            'summary' => 'locations',
            'councils' => $councils,
            'districts' => $districts,
        ]);
        exit;
    }

    if ($summary === 'genres') {
        $allowedGenres = getAllowedGenres();
        $allowedLookup = [];
        $genreCounts = [];

        foreach ($allowedGenres as $genreName) {
            $key = normalizeGenreKey($genreName);
            if ($key === '') {
                continue;
            }

            $allowedLookup[$key] = $genreName;
            $genreCounts[$key] = 0;
        }

        try {
            $stmt = $pdo->query("SELECT genre FROM artists WHERE is_confirmed = 1 AND moderation_status = 'approved' AND genre IS NOT NULL AND TRIM(genre) <> ''");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rows as $row) {
                $rowGenres = splitArtistGenres((string)($row['genre'] ?? ''));

                foreach ($rowGenres as $rowGenre) {
                    $rowKey = normalizeGenreKey($rowGenre);
                    if ($rowKey !== '' && isset($genreCounts[$rowKey])) {
                        $genreCounts[$rowKey]++;
                    }
                }
            }
        } catch (PDOException $e) {
            // Se a coluna genre não existir, devolve os géneros com contagem a 0.
        }

        $genres = [];
        foreach ($allowedGenres as $genreName) {
            $key = normalizeGenreKey($genreName);
            $genres[] = [
                'name' => $genreName,
                'artist_count' => (int)($genreCounts[$key] ?? 0),
            ];
        }

        usort($genres, function ($a, $b) {
            $aCount = (int)($a['artist_count'] ?? 0);
            $bCount = (int)($b['artist_count'] ?? 0);

            if ($aCount !== $bCount) {
                return $bCount <=> $aCount;
            }

            return strnatcasecmp((string)($a['name'] ?? ''), (string)($b['name'] ?? ''));
        });

        echo json_encode([
            'summary' => 'genres',
            'genres' => $genres,
        ]);
        exit;
    }

    // Se vier concelho, procurar por concelho (compatibilidade retroativa)
    if (!empty($council)) {
        $searchTerm = "%" . trim($council) . "%";
        $stmt = $pdo->prepare("SELECT * FROM artists WHERE is_confirmed = 1 AND moderation_status = 'approved' AND council LIKE ?");
        $stmt->execute([$searchTerm]);
        $artists = excludeLoggedArtistFromList($stmt->fetchAll(), $loggedArtistId);
        $artists = normalizeArtistsWithUpvotes($pdo, $artists, $voterToken);
        echo json_encode($artists);
        exit;
    }

    // Se vier distrito, procurar por distrito (coluna 'district' assumida)
    if (!empty($district)) {
        $searchTerm = "%" . trim($district) . "%";
        // Tenta procurar na coluna 'district' — se não existir, tenta coluna 'district_name' ou 'region'
        $possibleCols = ['district', 'district_name', 'region'];
        $artists = [];
        foreach ($possibleCols as $col) {
            try {
                $sql = "SELECT * FROM artists WHERE is_confirmed = 1 AND moderation_status = 'approved' AND $col LIKE ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$searchTerm]);
                $results = $stmt->fetchAll();
                if ($results && count($results) > 0) {
                    $artists = $results;
                    break;
                }
            } catch (PDOException $e) {
                // Coluna não existe ou erro — tentar próxima
                continue;
            }
        }

        // Se ainda não encontrou, tentar buscar por distrito dentro da coluna 'council' como fallback
        if (empty($artists)) {
            $stmt = $pdo->prepare("SELECT * FROM artists WHERE is_confirmed = 1 AND moderation_status = 'approved' AND council LIKE ?");
            $stmt->execute([$searchTerm]);
            $artists = $stmt->fetchAll();
        }

        $artists = excludeLoggedArtistFromList($artists, $loggedArtistId);
        echo json_encode(normalizeArtistsWithUpvotes($pdo, $artists, $voterToken));
        exit;
    }

    // Se vier género, procurar por género em todo o país
    if (!empty($genre)) {
        $selectedGenreKey = normalizeGenreKey((string)$genre);

        if ($selectedGenreKey === '') {
            echo json_encode([]);
            exit;
        }

        try {
            $stmt = $pdo->query("SELECT * FROM artists WHERE is_confirmed = 1 AND moderation_status = 'approved'");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $artists = [];
            foreach ($rows as $row) {
                $rowGenres = splitArtistGenres((string)($row['genre'] ?? ''));
                $rowKeys = array_map('normalizeGenreKey', $rowGenres);

                if (in_array($selectedGenreKey, $rowKeys, true)) {
                    $artists[] = $row;
                }
            }

            $artists = excludeLoggedArtistFromList($artists, $loggedArtistId);
            $artists = normalizeArtistsWithUpvotes($pdo, $artists, $voterToken);
            echo json_encode($artists);
            exit;
        } catch (PDOException $e) {
            echo json_encode([]);
            exit;
        }
    }

    // Se vierem múltiplos géneros, procurar artistas que tenham QUALQUER um deles
    if (!empty($genres)) {
        $rawGenres = array_map('trim', explode(',', (string) $genres));
        $rawGenres = array_values(array_filter($rawGenres, function ($value) {
            return $value !== '';
        }));

        if (!empty($rawGenres)) {
            try {
                $selectedGenreKeys = array_values(array_unique(array_filter(array_map('normalizeGenreKey', $rawGenres), function ($value) {
                    return $value !== '';
                })));

                if (empty($selectedGenreKeys)) {
                    echo json_encode([]);
                    exit;
                }

                $stmt = $pdo->query("SELECT * FROM artists WHERE is_confirmed = 1 AND moderation_status = 'approved'");
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $artists = [];
                foreach ($rows as $row) {
                    $rowGenres = splitArtistGenres((string)($row['genre'] ?? ''));
                    $rowKeys = array_values(array_unique(array_filter(array_map('normalizeGenreKey', $rowGenres), function ($value) {
                        return $value !== '';
                    })));

                    $matchesAny = false;
                    foreach ($rowKeys as $rowKey) {
                        if (in_array($rowKey, $selectedGenreKeys, true)) {
                            $matchesAny = true;
                            break;
                        }
                    }

                    if ($matchesAny) {
                        $artists[] = $row;
                    }
                }

                $artists = excludeLoggedArtistFromList($artists, $loggedArtistId);
                echo json_encode(normalizeArtistsWithUpvotes($pdo, $artists, $voterToken));
                exit;
            } catch (PDOException $e) {
                echo json_encode([]);
                exit;
            }
        }
    }

    // Se não houver parâmetros, retorna status informativo
    echo json_encode(['status' => 'connected', 'message' => 'Conexão à BD bem sucedida, mas nenhum parâmetro (council|district|genre|genres) foi fornecido.']);

} catch (PDOException $e) {
    // Erro específico de PDO (conexão, SQL, etc.)
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Erro de Base de Dados: ' . $e->getMessage()]);

} catch (Throwable $e) {
    // Captura outros erros gerais (ex: erros de sintaxe que não foram pegos antes)
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Erro inesperado no servidor: ' . $e->getMessage()]);
}
?>