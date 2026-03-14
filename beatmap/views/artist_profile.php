<?php
session_start();
require_once __DIR__ . '/../inc/config.php';
require __DIR__ . '/../inc/db.php';

// Obter ID do URL
$artist_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Buscar dados do artista (apenas se confirmado)
$stmt = $mysqli->prepare("SELECT * FROM artists WHERE id = ? AND is_confirmed = 1");
$stmt->bind_param('i', $artist_id);
$stmt->execute();
$artist = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Se não encontrar artista, mostra erro
if (!$artist) {
    $pageTitle = 'Artista não encontrado';
    require __DIR__ . '/../inc/header.php';
    echo '
    <div class="container" style="padding: 120px 20px; text-align: center;">
        <h2 style="font-size: 3rem; margin-bottom: 20px;">404</h2>
        <p style="color: #888; margin-bottom: 30px; font-size: 1.2rem;">O artista que procuras não existe ou o perfil não está disponível.</p>
        <a href="'.VIEWS_URL.'list_artists.php" class="btn-secondary">Voltar ao Mapa</a>
    </div>';
    require __DIR__ . '/../inc/footer.php';
    exit;
}

$pageTitle = $artist['name'];
require __DIR__ . '/../inc/header.php';

$socialMeta = [
    'instagram' => [
        'label' => 'Instagram',
        'icon' => '<img src="' . BASE_URL . 'assets/social/instagram.png" alt="Instagram">'
    ],
    'x' => [
        'label' => 'X (Twitter)',
        'icon' => '<img src="' . BASE_URL . 'assets/social/x.png" alt="X (Twitter)">'
    ],
    'youtube' => [
        'label' => 'YouTube',
        'icon' => '<img src="' . BASE_URL . 'assets/social/youtube.png" alt="YouTube">'
    ],
    'youtube_music' => [
        'label' => 'YouTube Music',
        'icon' => '<img src="' . BASE_URL . 'assets/social/youtubemusic-com.png" alt="YouTube Music">'
    ],
    'tiktok' => [
        'label' => 'TikTok',
        'icon' => '<img src="' . BASE_URL . 'assets/social/tiktok.png" alt="TikTok">'
    ],
    'linkedin' => [
        'label' => 'LinkedIn',
        'icon' => '<img src="' . BASE_URL . 'assets/social/linkedin.png" alt="LinkedIn">'
    ],
    'tidal' => [
        'label' => 'Tidal',
        'icon' => '<img src="' . BASE_URL . 'assets/social/tidal.png" alt="Tidal">'
    ],
    'spotify' => [
        'label' => 'Spotify',
        'icon' => '<img src="' . BASE_URL . 'assets/social/spotify.png" alt="Spotify">'
    ],
    'soundcloud' => [
        'label' => 'SoundCloud',
        'icon' => '<img src="' . BASE_URL . 'assets/social/soundcloud.png" alt="SoundCloud">'
    ],
    'apple_music' => [
        'label' => 'Apple Music',
        'icon' => '<img src="' . BASE_URL . 'assets/social/apple.png" alt="Apple Music">'
    ],
    'bandcamp' => [
        'label' => 'Bandcamp',
        'icon' => '<img src="' . BASE_URL . 'assets/social/bandcamp.png" alt="Bandcamp">'
    ]
];

$socialLinks = [];
if (!empty($artist['social_links'])) {
    $decodedLinks = json_decode($artist['social_links'], true);
    if (is_array($decodedLinks)) {
        $socialLinks = $decodedLinks;
    }
}

$musicAppKeys = ['tidal', 'spotify', 'soundcloud', 'apple_music', 'youtube_music', 'bandcamp'];
$musicPlatformLinks = [];
$socialNetworkLinks = [];

foreach ($socialLinks as $key => $url) {
    if (!isset($socialMeta[$key])) {
        continue;
    }

    $entry = [
        'key' => $key,
        'url' => $url,
        'meta' => $socialMeta[$key],
    ];

    if (in_array($key, $musicAppKeys, true)) {
        $musicPlatformLinks[] = $entry;
    } else {
        $socialNetworkLinks[] = $entry;
    }
}

if (!function_exists('detectMusicPreviewEmbed')) {
    function detectMusicPreviewEmbed(string $rawUrl): ?array
    {
        $url = trim($rawUrl);
        if ($url === '') {
            return null;
        }

        if (!preg_match('#^https?://#i', $url)) {
            $url = 'https://' . ltrim($url, '/');
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $parts = parse_url($url);
        $host = strtolower($parts['host'] ?? '');
        $path = $parts['path'] ?? '';
        $query = $parts['query'] ?? '';

        if (str_starts_with($host, 'www.')) {
            $host = substr($host, 4);
        }

        // Spotify: track/album/playlist/episode/show
        if (str_contains($host, 'spotify.com')) {
            if (preg_match('#/(?:intl-[a-z]{2}/)?(track|album|playlist|episode|show)/([a-zA-Z0-9]+)#i', $path, $match)) {
                $type = strtolower($match[1]);
                $id = $match[2];
                return [
                    'provider' => 'Spotify',
                    'icon' => BASE_URL . 'assets/social/spotify.png',
                    'embed_src' => 'https://open.spotify.com/embed/' . $type . '/' . rawurlencode($id) . '?utm_source=generator',
                    'embed_kind' => 'spotify',
                    'embed_height' => in_array($type, ['track', 'episode'], true) ? 80 : 120,
                    'url' => $url,
                ];
            }
        }

        // YouTube / YouTube Music
        if (str_contains($host, 'youtube.com') || str_contains($host, 'youtu.be') || str_contains($host, 'music.youtube.com')) {
            $videoId = '';

            if (str_contains($host, 'youtu.be')) {
                if (preg_match('#^/([a-zA-Z0-9_-]{11})#', $path, $match)) {
                    $videoId = $match[1];
                }
            } else {
                parse_str($query, $queryParams);
                if (!empty($queryParams['v']) && preg_match('#^[a-zA-Z0-9_-]{11}$#', $queryParams['v'])) {
                    $videoId = $queryParams['v'];
                } elseif (preg_match('#/(?:shorts|embed)/([a-zA-Z0-9_-]{11})#', $path, $match)) {
                    $videoId = $match[1];
                }
            }

            if ($videoId !== '') {
                $isMusic = str_contains($host, 'music.youtube.com');
                return [
                    'provider' => $isMusic ? 'YouTube Music' : 'YouTube',
                    'icon' => BASE_URL . 'assets/social/' . ($isMusic ? 'youtubemusic-com.png' : 'youtube.png'),
                    'embed_src' => 'https://www.youtube.com/embed/' . rawurlencode($videoId) . '?rel=0&modestbranding=1&playsinline=1',
                    'embed_kind' => 'youtube',
                    'embed_height' => 190,
                    'url' => $url,
                ];
            }
        }

        // SoundCloud
        if (str_contains($host, 'soundcloud.com')) {
            return [
                'provider' => 'SoundCloud',
                'icon' => BASE_URL . 'assets/social/soundcloud.png',
                'embed_src' => 'https://w.soundcloud.com/player/?url=' . rawurlencode($url) . '&color=%237331df&auto_play=false&hide_related=false&show_comments=false&show_user=true&show_reposts=false&visual=true',
                'embed_kind' => 'soundcloud',
                'embed_height' => 120,
                'url' => $url,
            ];
        }

        // Apple Music
        if (str_contains($host, 'music.apple.com')) {
            $embedHost = 'embed.music.apple.com';
            $embedUrl = 'https://' . $embedHost . $path . ($query !== '' ? ('?' . $query) : '');
            $isAppleTrack = preg_match('/(?:^|&)i=\d+/', $query) === 1;
            return [
                'provider' => 'Apple Music',
                'icon' => BASE_URL . 'assets/social/apple.png',
                'embed_src' => $embedUrl,
                'embed_kind' => 'apple_music',
                'embed_height' => $isAppleTrack ? 140 : 180,
                'url' => $url,
            ];
        }

        // Bandcamp
        if (str_contains($host, 'bandcamp.com') && preg_match('#/(track|album)/([^/?#]+)#i', $path, $match)) {
            return [
                'provider' => 'Bandcamp',
                'icon' => BASE_URL . 'assets/social/bandcamp.png',
                'embed_src' => 'https://bandcamp.com/EmbeddedPlayer/size=large/bgcol=ffffff/linkcol=7331df/minimal=true/track=' . rawurlencode($match[2]) . '/transparent=true/',
                'embed_kind' => 'bandcamp',
                'embed_height' => 70,
                'url' => $url,
            ];
        }

        return [
            'provider' => parse_url($url, PHP_URL_HOST) ?: 'Link',
            'icon' => '',
            'embed_src' => '',
            'embed_kind' => 'link',
            'embed_height' => 0,
            'url' => $url,
        ];
    }
}

$musicPreviewItems = [];
foreach (['preview1', 'preview2', 'preview3'] as $previewKey) {
    if (empty($artist[$previewKey])) {
        continue;
    }

    $detected = detectMusicPreviewEmbed((string)$artist[$previewKey]);
    if ($detected) {
        $musicPreviewItems[] = $detected;
    }
}
?>

<div class="profile-wrapper">
    <div class="profile-grid">
        <!-- Coluna Esquerda: Cartão de Identidade -->
        <aside class="profile-sidebar">
            <div class="sidebar-card">
                <?php if (!empty($musicPlatformLinks)): ?>
                    <div class="sidebar-music-corner">
                        <div class="social-buttons social-buttons-music">
                            <?php foreach ($musicPlatformLinks as $item): ?>
                                <a class="social-btn" href="<?php echo htmlspecialchars($item['url']); ?>" target="_blank" rel="noopener" aria-label="<?php echo htmlspecialchars($item['meta']['label']); ?>">
                                    <?php echo $item['meta']['icon']; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="avatar-large">
                    <?php $avatarSrc = !empty($artist['profile_picture']) ? (BASE_URL . ltrim($artist['profile_picture'], '/')) : DEFAULT_AVATAR_URL; ?>
                    <img src="<?php echo htmlspecialchars($avatarSrc); ?>" alt="<?php echo htmlspecialchars($artist['name']); ?>" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">
                </div>
                
                <h1><?php echo htmlspecialchars($artist['name']); ?></h1>
                
                <div class="tags-row">
                    <?php if ($artist['genre']): ?>
                        <span class="tag genre"><?php echo htmlspecialchars($artist['genre']); ?></span>
                    <?php endif; ?>
                    <?php if ($artist['council']): ?>
                        <span class="tag location">📍 <?php echo htmlspecialchars($artist['council']); ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="sidebar-meta">
                    <div class="sidebar-meta-layout">
                        <div class="sidebar-meta-left">
                            <p>Membro desde <?php echo date('Y', strtotime($artist['created_at'])); ?></p>
                            <?php if ($artist['district']): ?>
                                <p class="district-info"><?php echo htmlspecialchars($artist['district']); ?>, Portugal</p>
                            <?php endif; ?>
                        </div>
                        <div class="sidebar-meta-right">
                            <?php if (!empty($socialNetworkLinks)): ?>
                                <div class="sidebar-meta-social">
                                    <p><strong>Redes sociais:</strong></p>
                                    <div class="social-buttons social-buttons-contacts">
                                        <?php foreach ($socialNetworkLinks as $item): ?>
                                            <a class="social-btn" href="<?php echo htmlspecialchars($item['url']); ?>" target="_blank" rel="noopener" aria-label="<?php echo htmlspecialchars($item['meta']['label']); ?>">
                                                <?php echo $item['meta']['icon']; ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <a href="<?php echo VIEWS_URL; ?>add_artist.php" class="btn-contact" aria-label="Criar conta de artista">
                    <span class="btn-contact-icon" aria-hidden="true">✉️</span>
                    <span>Contactar Artista</span>
                    <span class="btn-contact-arrow" aria-hidden="true">↗</span>
                </a>

            </div>
        </aside>

        <!-- Coluna Direita: Conteúdo Principal -->
        <main class="profile-content">
            <div class="content-card">
                <div class="section-header">
                    <h3>Biografia</h3>
                </div>
                <div class="bio-text">
                    <?php if ($artist['bio']): ?>
                        <?php echo nl2br(htmlspecialchars($artist['bio'])); ?>
                    <?php else: ?>
                        <p class="empty-bio">Este artista ainda não adicionou uma biografia.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Previews de Música -->
            <div class="content-card">
                <div class="section-header">
                    <h3>Previews de Música</h3>
                </div>
                <div class="music-previews">
                    <?php if (count($musicPreviewItems) === 0): ?>
                        <p class="empty-bio">Este artista ainda não adicionou previews de música.</p>
                    <?php else: ?>
                        <div class="music-preview-grid">
                            <?php foreach ($musicPreviewItems as $item): ?>
                                <?php if ($item['embed_src'] !== ''): ?>
                                    <iframe
                                        src="<?php echo htmlspecialchars($item['embed_src']); ?>"
                                        width="100%"
                                        height="<?php echo (int)($item['embed_height'] ?? 240); ?>"
                                        loading="lazy"
                                        referrerpolicy="strict-origin-when-cross-origin"
                                        allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"
                                        allowfullscreen
                                        title="Preview <?php echo htmlspecialchars($item['provider']); ?>"
                                    ></iframe>
                                <?php else: ?>
                                    <a class="music-preview-link-fallback" href="<?php echo htmlspecialchars($item['url']); ?>" target="_blank" rel="noopener">Ouvir preview</a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
                </div>
            </div>
        </main>
    </div>
</div>

<style>
    .profile-wrapper { max-width: 1100px; margin: 100px auto 60px; padding: 0 20px; }
    
    .profile-grid { display: grid; grid-template-columns: 320px 1fr; gap: 30px; }
    @media (max-width: 800px) { .profile-grid { grid-template-columns: 1fr; } }

    /* Sidebar Styles */
    .sidebar-card {
        background: #141414; border: 1px solid #2a2a2a; border-radius: 12px; padding: 35px 25px;
        text-align: center; position: sticky; top: 100px;
    }

    .sidebar-music-corner {
        position: absolute;
        top: 12px;
        right: 12px;
        max-width: 170px;
    }

    .social-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .social-buttons-music {
        justify-content: flex-end;
    }

    .social-btn {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        border: 1px solid #333;
        background: rgba(255, 255, 255, 0.04);
        text-decoration: none;
        transition: transform .16s ease, border-color .16s ease;
    }

    .social-btn img {
        width: 16px;
        height: 16px;
        object-fit: contain;
    }

    .social-btn:hover,
    .social-btn:focus-visible {
        transform: translateY(-1px);
        border-color: #ff6b35;
        outline: none;
    }

    .avatar-large {
        width: 130px; height: 130px; background: #222; border-radius: 50%; margin: 0 auto 25px;
        display: flex; align-items: center; justify-content: center;
        font-size: 3.5rem; font-weight: bold; color: #ff6b35; border: 3px solid #333;
    }

    .sidebar-card h1 { font-size: 2rem; margin: 0 0 15px 0; color: #fff; line-height: 1.2; }

    .tags-row { display: flex; gap: 8px; flex-wrap: wrap; justify-content: center; margin-bottom: 25px; }
    .tag { font-size: 0.85rem; padding: 6px 14px; border-radius: 20px; background: #222; color: #ccc; border: 1px solid #333; }
    .tag.genre { color: #ff6b35; border-color: rgba(255, 107, 53, 0.3); }

    .sidebar-meta { border-top: 1px solid #222; padding-top: 20px; margin-bottom: 25px; color: #666; font-size: 0.9rem; }
    .sidebar-meta p { margin: 5px 0; }
    .sidebar-meta-layout {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: flex-start;
    }
    .sidebar-meta-left { flex: 1; text-align: left; }
    .sidebar-meta-right { text-align: right; }
    .sidebar-meta-social .social-buttons-contacts { margin-top: 4px; justify-content: flex-start; }
    .sidebar-meta-social p { margin-bottom: 3px; }
    .district-info { color: #888; }

    .btn-contact {
        display: flex; align-items: center; justify-content: center; gap: 10px;
        width: 100%; padding: 13px 16px;
        background: linear-gradient(135deg, #ff6b35, #ff8b4d);
        color: #fff; text-decoration: none;
        border: 1px solid rgba(255, 255, 255, 0.18);
        border-radius: 999px;
        font-weight: 700; letter-spacing: .2px;
        transition: transform .18s ease, box-shadow .18s ease, filter .18s ease;
        box-shadow: 0 8px 24px rgba(255, 107, 53, 0.28);
    }
    .btn-contact-icon {
        font-size: 1rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: rgba(0, 0, 0, 0.18);
    }
    .btn-contact-arrow {
        font-size: .95rem;
        opacity: .9;
        transform: translateX(0);
        transition: transform .18s ease;
    }
    .btn-contact:hover {
        filter: brightness(1.03);
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(255, 107, 53, 0.34);
    }
    .btn-contact:hover .btn-contact-arrow { transform: translateX(2px); }
    .btn-contact:active {
        transform: translateY(0);
        box-shadow: 0 6px 18px rgba(255, 107, 53, 0.26);
    }
    .btn-contact:focus-visible {
        outline: 2px solid rgba(255, 139, 77, 0.95);
        outline-offset: 2px;
    }

    /* Content Styles */
    .content-card { background: #141414; border: 1px solid #2a2a2a; border-radius: 12px; padding: 40px; min-height: 400px; margin-bottom: 56px; }
    
    .section-header { margin-bottom: 25px; border-bottom: 1px solid #222; padding-bottom: 15px; }
    .section-header h3 { font-size: 1.1rem; color: #888; text-transform: uppercase; letter-spacing: 2px; margin: 0; }

    .bio-text {
        color: #ddd; line-height: 1.8; font-size: 1.1rem; white-space: pre-line;
    }
    .empty-bio { color: #555; font-style: italic; }

    .music-preview-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 14px;
    }

    .music-preview-grid iframe {
        width: 100%;
        border: 0;
        display: block;
        border-radius: 10px;
        background: #0c0c0c;
    }

    .music-preview-link-fallback {
        display: inline-block;
        color: #8a8a8a;
        text-decoration: none;
        padding: 8px 0;
    }

    .music-preview-link-fallback:hover {
        color: #ff6b35;
    }

    @media (max-width: 600px) {
        .content-card { padding: 25px; }
        .avatar-large { width: 100px; height: 100px; font-size: 2.5rem; }
        .sidebar-card { position: static; }
        .sidebar-music-corner {
            position: static;
            max-width: 100%;
            margin: 0 0 12px;
        }
        .social-buttons-music {
            justify-content: flex-start;
        }
        .sidebar-meta-layout {
            flex-direction: column;
            align-items: stretch;
        }
        .sidebar-meta-right {
            text-align: left;
        }
    }
</style>

<?php require __DIR__ . '/../inc/footer.php'; ?>