<?php if ($res && $res->num_rows): ?>
    <div class="artist-grid">
        <?php while ($row = $res->fetch_assoc()): ?>
            <div class="artist-card">
                <div class="card-avatar">
                    <?php $avatarSrc = !empty($row['profile_picture']) ? (BASE_URL . ltrim($row['profile_picture'], '/')) : DEFAULT_AVATAR_URL; ?>
                    <img src="<?php echo htmlspecialchars($avatarSrc); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" style="width:100%; height:100%; object-fit:cover; border-radius:50%; display:block;">
                </div>
                <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                
                <div class="card-tags">
                    <?php if ($row['genre']): ?>
                        <span class="tag genre"><?php echo htmlspecialchars($row['genre']); ?></span>
                    <?php endif; ?>
                    <?php if ($row['council']): ?>
                        <span class="tag location">📍 <?php echo htmlspecialchars($row['council']); ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="card-bio">
                    <?php 
                    $bio = $row['bio'] ?? '';
                    // Truncar bio se for longa
                    echo htmlspecialchars(strlen($bio) > 80 ? substr($bio, 0, 80) . '...' : $bio); 
                    ?>
                </div>
                
                <a href="<?php echo VIEWS_URL; ?>artist_profile.php?id=<?php echo $row['id']; ?>" class="btn-card">Ver Perfil</a>
            </div>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <div class="empty-state">
        <div class="empty-icon">🔍</div>
        <h3>Nenhum artista encontrado</h3>
        <p>Tenta pesquisar por outros termos ou limpa os filtros.</p>
        <?php if ($q !== ''): ?>
            <a href="<?php echo VIEWS_URL; ?>list_artists.php" class="btn-reset">Limpar Pesquisa</a>
        <?php endif; ?>
    </div>
<?php endif; ?>