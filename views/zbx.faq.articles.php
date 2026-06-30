<?php
/**
 * View: zbx.faq.articles.php — Lista de artigos publicados
 */
$articles   = $data['articles']   ?? [];
$categories = $data['categories'] ?? [];
$allTags    = $data['all_tags']   ?? [];
$total      = (int)($data['total']  ?? 0);
$page       = (int)($data['page']   ?? 1);
$limit      = (int)($data['limit']  ?? 20);
$search     = htmlspecialchars($data['search']     ?? '', ENT_QUOTES);
$filterCat  = (int)($data['categoryid'] ?? 0);
$filterTag  = htmlspecialchars($data['tag']        ?? '', ENT_QUOTES);
$isAdmin    = (bool)($data['is_admin'] ?? false);
$isSA       = (bool)($data['is_sa']   ?? false);
$totalPages = $total > 0 ? ceil($total / $limit) : 1;

function renderCatTree(array $nodes, int $selected, string $action): void {
    if (!$nodes) return;
    echo '<ul class="faq-cat-tree">';
    foreach ($nodes as $node) {
        $isActive = (int)$node['categoryid'] === $selected;
        $hasChild = !empty($node['children']);
        $catId    = (int)$node['categoryid'];
        echo '<li class="faq-cat-item' . ($hasChild ? ' has-children' : '') . ($isActive ? ' active' : '') . '">';
        echo '<a href="?action=' . $action . '&categoryid=' . $catId . '">';
        if ($hasChild) echo '<span class="faq-cat-toggle">▸</span>';
        echo htmlspecialchars($node['name']) . '</a>';
        if ($hasChild) renderCatTree($node['children'], $selected, $action);
        echo '</li>';
    }
    echo '</ul>';
}
?>

<div class="faq-wrap">
<div class="faq-layout">
    <aside class="faq-sidebar">
        <div class="faq-sidebar-section">
            <div class="faq-sidebar-title">Categorias</div>
            <a href="?action=zbx.faq.articles" class="faq-cat-all<?= $filterCat === 0 ? ' active' : '' ?>">Todos os artigos</a>
            <?php renderCatTree($categories, $filterCat, 'zbx.faq.articles'); ?>
        </div>
        <?php if ($allTags): ?>
        <div class="faq-sidebar-section">
            <div class="faq-sidebar-title">Tags</div>
            <div class="faq-tagcloud">
                <?php foreach ($allTags as $t): ?>
                <a href="?action=zbx.faq.articles&tag=<?= urlencode($t) ?>"
                   class="faq-tag<?= $filterTag === $t ? ' active' : '' ?>"><?= htmlspecialchars($t) ?></a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </aside>

    <main class="faq-main">
        <div class="faq-topbar">
            <form method="get" action="" class="faq-search-form">
                <input type="hidden" name="action" value="zbx.faq.articles">
                <?php if ($filterCat): ?><input type="hidden" name="categoryid" value="<?= $filterCat ?>"><?php endif; ?>
                <input type="text" name="search" value="<?= $search ?>" placeholder="Pesquisar artigos..." class="faq-input faq-search-input">
                <button type="submit" class="faq-btn faq-btn-primary">Buscar</button>
                <?php if ($search || $filterTag): ?>
                <a href="?action=zbx.faq.articles" class="faq-btn faq-btn-outline">Limpar</a>
                <?php endif; ?>
            </form>
            <?php if ($isAdmin): ?>
            <a href="?action=zbx.faq.write.edit" class="faq-btn faq-btn-primary">+ Novo Artigo</a>
            <?php endif; ?>
        </div>

        <?php if ($filterTag): ?>
        <div class="faq-filter-active">
            Tag: <strong><?= htmlspecialchars($filterTag) ?></strong>
            <a href="?action=zbx.faq.articles" class="faq-tag-remove">✕</a>
        </div>
        <?php endif; ?>

        <?php if ($articles): ?>
        <div class="faq-article-grid">
            <?php foreach ($articles as $art): ?>
            <div class="faq-article-card">
                <div class="faq-card-header">
                    <a href="?action=zbx.faq.view&articleid=<?= (int)$art['articleid'] ?>" class="faq-card-title">
                        <?= htmlspecialchars($art['title']) ?>
                    </a>
                </div>
                <div class="faq-card-meta">
                    <?php if ($art['category_name']): ?>
                    <span class="faq-meta-cat">📁 <?= htmlspecialchars($art['category_name']) ?></span>
                    <?php endif; ?>
                    <span><?= htmlspecialchars($art['author_name'] . ' ' . $art['author_surname']) ?></span>
                    <?php if ($art['published_at']): ?>
                    <span><?= date('d/m/Y', (int)$art['published_at']) ?></span>
                    <?php endif; ?>
                </div>
                <?php if ($art['tags']): ?>
                <div class="faq-card-tags">
                    <?php foreach ($art['tags'] as $tag): ?>
                    <a href="?action=zbx.faq.articles&tag=<?= urlencode($tag) ?>" class="faq-tag"><?= htmlspecialchars($tag) ?></a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php if ($totalPages > 1): ?>
        <div class="faq-pagination">
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <a href="?action=zbx.faq.articles&page=<?= $p ?>&search=<?= urlencode($search) ?>&categoryid=<?= $filterCat ?>&tag=<?= urlencode($filterTag) ?>"
               class="faq-page-btn<?= $p === $page ? ' active' : '' ?>"><?= $p ?></a>
            <?php endfor; ?>
            <span class="faq-page-info"><?= $total ?> artigo(s)</span>
        </div>
        <?php endif; ?>
        <?php else: ?>
        <div class="faq-empty">
            <div class="faq-empty-icon">📄</div>
            <p>Nenhum artigo publicado encontrado.</p>
            <?php if ($isAdmin): ?>
            <a href="?action=zbx.faq.write.edit" class="faq-btn faq-btn-primary">Criar o primeiro artigo</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </main>
</div>

</div><!-- .faq-wrap -->
<?php include __DIR__ . '/faq.common.css.php'; ?>
<script>
(function(){
    document.querySelectorAll('.faq-cat-item.has-children > a').forEach(function(a){
        a.addEventListener('click', function(e){
            if (e.target.classList.contains('faq-cat-toggle')) {
                e.preventDefault();
                a.closest('.faq-cat-item').classList.toggle('open');
            } else {
                a.closest('.faq-cat-item').classList.toggle('open');
            }
        });
    });
    document.querySelectorAll('.faq-cat-item.active').forEach(function(li){
        var p = li.parentElement;
        while(p){ if(p.classList && p.classList.contains('faq-cat-item')) p.classList.add('open'); p=p.parentElement; }
    });
})();
</script>
