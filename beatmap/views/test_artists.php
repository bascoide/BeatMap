<?php
session_start();
$pageTitle = 'Teste - Todos os Artistas';
require_once __DIR__ . '/../inc/config.php';
require __DIR__ . '/../inc/db.php';

// Buscar todos os artistas
$sql = "SELECT id, name, genre, council, district, bio, created_at, profile_picture FROM artists ORDER BY name ASC";
$res = $mysqli->query($sql);

require __DIR__ . '/../inc/header.php';
?>

<div style="padding: 40px 20px;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <h1 style="text-align: center; margin-bottom: 10px;">🎵 Listagem de Artistas (Teste)</h1>
        <p style="text-align: center; color: #666; margin-bottom: 40px;">
            Total: <?php echo $res->num_rows; ?> artistas
        </p>

        <?php if ($res && $res->num_rows > 0): ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;">
                <?php while ($artist = $res->fetch_assoc()): ?>
                    <div style="
                        border: 1px solid #e0e0e0;
                        border-radius: 12px;
                        padding: 20px;
                        background: #fff;
                        transition: transform 0.2s, box-shadow 0.2s;
                        cursor: pointer;
                    " onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 16px rgba(0,0,0,0.1)';" 
                       onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                        
                        <!-- Avatar -->
                        <div style="
                            width: 100%;
                            height: 200px;
                            border-radius: 8px;
                            margin-bottom: 15px;
                            overflow: hidden;
                            background: #f0f0f0;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            font-size: 3rem;
                            font-weight: bold;
                            color: #999;
                        ">
                            <?php $avatarSrc = !empty($artist['profile_picture']) ? (BASE_URL . ltrim($artist['profile_picture'], '/')) : DEFAULT_AVATAR_URL; ?>
                            <img src="<?php echo htmlspecialchars($avatarSrc); ?>" 
                                 alt="<?php echo htmlspecialchars($artist['name']); ?>"
                                 style="width:100%; height:100%; object-fit:cover;">
                        </div>

                        <!-- Nome -->
                        <h3 style="margin: 0 0 10px 0; color: #333; font-size: 1.2rem;">
                            <?php echo htmlspecialchars($artist['name']); ?>
                        </h3>

                        <!-- Tags de Género e Localização -->
                        <div style="display: flex; gap: 8px; margin-bottom: 15px; flex-wrap: wrap;">
                            <?php if ($artist['genre']): ?>
                                <span style="
                                    background: #e8f5e9;
                                    color: #2e7d32;
                                    padding: 4px 12px;
                                    border-radius: 12px;
                                    font-size: 0.85rem;
                                    font-weight: 500;
                                ">
                                    🎸 <?php echo htmlspecialchars($artist['genre']); ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($artist['council']): ?>
                                <span style="
                                    background: #f3e5f5;
                                    color: #6a1b9a;
                                    padding: 4px 12px;
                                    border-radius: 12px;
                                    font-size: 0.85rem;
                                    font-weight: 500;
                                ">
                                    📍 <?php echo htmlspecialchars($artist['council']); ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Bio -->
                        <p style="
                            color: #666;
                            font-size: 0.95rem;
                            margin: 0 0 15px 0;
                            line-height: 1.4;
                            min-height: 40px;
                        ">
                            <?php 
                            $bio = $artist['bio'] ?? 'Sem descrição';
                            echo htmlspecialchars(strlen($bio) > 100 ? substr($bio, 0, 100) . '...' : $bio); 
                            ?>
                        </p>

                        <!-- Botão Ver Perfil -->
                        <a href="<?php echo VIEWS_URL; ?>artist_profile.php?id=<?php echo $artist['id']; ?>" 
                           style="
                               display: block;
                               text-align: center;
                               background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                               color: white;
                               padding: 10px 16px;
                               border-radius: 6px;
                               text-decoration: none;
                               font-weight: 600;
                               font-size: 0.95rem;
                               transition: opacity 0.2s;
                           "
                           onmouseover="this.style.opacity='0.9';"
                           onmouseout="this.style.opacity='1';">
                            Ver Perfil Completo →
                        </a>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 60px 20px;">
                <p style="font-size: 2rem; margin-bottom: 20px;">😴</p>
                <h2 style="color: #333;">Nenhum artista encontrado</h2>
                <p style="color: #666;">A base de dados não contém artistas ainda.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    @media (max-width: 768px) {
        div[style*="grid-template-columns"] {
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)) !important;
        }
    }
</style>

<?php require __DIR__ . '/../inc/footer.php'; ?>
