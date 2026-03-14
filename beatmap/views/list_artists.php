<?php
session_start();
$pageTitle = 'Artistas';
require_once __DIR__ . '/../inc/config.php';
require __DIR__ . '/../inc/db.php';

$q = trim($_GET['q'] ?? '');
$sql = "SELECT id, name, genre, council, bio, created_at, profile_picture FROM artists";
$params = [];
if ($q !== '') {
    $sql .= " WHERE name LIKE ? OR genre LIKE ? OR council LIKE ?";
    $like = '%'.$q.'%';
    $params = [$like, $like, $like];
}
$sql .= " ORDER BY created_at DESC";

$stmt = $mysqli->prepare($sql);
if ($params) $stmt->bind_param(str_repeat('s', count($params)), ...$params);
$stmt->execute();
$res = $stmt->get_result();

if (isset($_GET['ajax'])) {
    require __DIR__ . '/artist_grid_partial.php';
    exit;
}

require __DIR__ . '/../inc/header.php';
?>

<div class="list-wrapper">
    <div class="list-header">
        <div class="header-text">
            <h2>Explorar Artistas</h2>
            <p>Descobre novos talentos e liga-te à música local.</p>
        </div>
        
        <form method="get" action="" class="search-bar">
            <input type="search" name="q" id="search-input" placeholder="Pesquisar nome, género ou concelho..." value="<?php echo htmlspecialchars($q); ?>" autocomplete="off">
            <button type="submit">🔍</button>
        </form>
    </div>

    <div id="results-container">
        <?php require __DIR__ . '/artist_grid_partial.php'; ?>
    </div>
</div>

<?php $stmt->close(); ?>

<style>
    .list-wrapper { max-width: 1200px; margin: 80px auto 40px; padding: 0 20px; }
    
    .list-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; flex-wrap: wrap; gap: 20px; border-bottom: 1px solid #222; padding-bottom: 20px; }
    .header-text h2 { font-size: 2rem; margin: 0 0 5px 0; color: #fff; }
    .header-text p { color: #888; margin: 0; }

    .search-bar { display: flex; gap: 10px; flex: 1; max-width: 400px; }
    .search-bar input { flex: 1; padding: 10px 15px; background: #141414; border: 1px solid #333; border-radius: 6px; color: #fff; }
    .search-bar input:focus { border-color: #ff6b35; outline: none; }
    .search-bar button { background: #ff6b35; border: none; border-radius: 6px; width: 40px; cursor: pointer; color: white; font-size: 1.2rem; transition: .2s; }
    .search-bar button:hover { background: #e55a2b; }

    .artist-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px; }
    
    .artist-card { background: #141414; border: 1px solid #2a2a2a; border-radius: 12px; padding: 25px; text-align: center; transition: transform 0.2s, border-color 0.2s; display: flex; flex-direction: column; }
    .artist-card:hover { transform: translateY(-5px); border-color: #ff6b35; }
    
    .card-avatar { width: 80px; height: 80px; background: #222; border-radius: 50%; margin: 0 auto 15px; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: bold; color: #ff6b35; border: 2px solid #333; }
    
    .artist-card h3 { margin: 0 0 15px 0; font-size: 1.3rem; color: #fff; }
    
    .card-tags { display: flex; gap: 8px; justify-content: center; flex-wrap: wrap; margin-bottom: 15px; }
    .tag { font-size: 0.75rem; padding: 4px 10px; border-radius: 15px; background: #222; color: #ccc; border: 1px solid #333; }
    .tag.genre { color: #ff6b35; border-color: rgba(255, 107, 53, 0.3); }
    
    .card-bio { font-size: 0.9rem; color: #666; margin-bottom: 20px; flex-grow: 1; line-height: 1.5; }
    
    .btn-card { display: block; width: 100%; padding: 10px; background: transparent; border: 1px solid #333; color: #fff; text-decoration: none; border-radius: 6px; transition: .2s; font-weight: 600; }
    .btn-card:hover { background: #ff6b35; border-color: #ff6b35; color: #fff; }

    .empty-state { text-align: center; padding: 60px 20px; background: #141414; border-radius: 12px; border: 1px dashed #333; }
    .empty-icon { font-size: 3rem; margin-bottom: 15px; opacity: 0.5; }
    .empty-state h3 { margin: 0 0 10px 0; color: #fff; }
    .empty-state p { color: #888; margin-bottom: 20px; }
    .btn-reset { display: inline-block; padding: 8px 20px; background: #222; color: #fff; text-decoration: none; border-radius: 4px; font-size: 0.9rem; transition: .2s; }
    .btn-reset:hover { background: #333; }

    @media (max-width: 600px) {
        .list-header { flex-direction: column; align-items: stretch; }
        .search-bar { max-width: 100%; }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-input');
    const resultsContainer = document.getElementById('results-container');
    let debounceTimer;

    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const query = this.value;
        
        // Espera 300ms após parar de escrever para fazer a pesquisa
        debounceTimer = setTimeout(() => {
            // Atualiza URL sem recarregar a página
            const url = new URL(window.location);
            url.searchParams.set('q', query);
            window.history.pushState({}, '', url);

            // Faz o pedido AJAX
            fetch('list_artists.php?ajax=1&q=' + encodeURIComponent(query))
                .then(response => response.text())
                .then(html => {
                    resultsContainer.innerHTML = html;
                })
                .catch(err => console.error('Erro na pesquisa:', err));
        }, 300);
    });
});
</script>

<?php require __DIR__ . '/../inc/footer.php'; ?>