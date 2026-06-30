<?php
/**
 * View: zbx.faq.view.php — Visualização de artigo
 */
$article   = $data['article']   ?? [];
$tags      = $data['tags']      ?? [];
$media     = $data['media']     ?? [];
$groups    = $data['groups']    ?? [];
$revisions = $data['revisions'] ?? [];
$isAdmin   = (bool)($data['is_admin'] ?? false);
$isSA      = (bool)($data['is_sa']   ?? false);

$articleId   = (int)($article['articleid']   ?? 0);
$title       = htmlspecialchars($article['title'] ?? '', ENT_QUOTES);
$content     = $article['content'] ?? '';
$contentType = (int)($article['content_type'] ?? 0);
$status      = (int)($article['status']      ?? 0);

$statusBadge = [
    0=>['Rascunho','faq-badge-draft'], 1=>['Em Revisão','faq-badge-review'],
    2=>['Publicado','faq-badge-pub'],  3=>['Arquivado','faq-badge-archive'],
    4=>['Rejeitado','faq-badge-reject'],
];
$badge = $statusBadge[$status] ?? ['?',''];

function mimeIcon(string $mime): string {
    if (str_starts_with($mime,'image/')) return '🖼️';
    if (str_starts_with($mime,'video/')) return '🎬';
    if (str_starts_with($mime,'text/'))  return '📝';
    if ($mime==='application/pdf')       return '📄';
    return '📎';
}
function fmtBytes(int $b): string {
    if ($b<1024) return $b.' B';
    if ($b<1048576) return round($b/1024,1).' KB';
    return round($b/1048576,1).' MB';
}
?>

<div class="faq-wrap">
<div class="faq-view-layout">
    <nav class="faq-breadcrumb">
        <a href="?action=zbx.faq.articles">FAQ</a>
        <?php if ($article['category_name']): ?>
        <span class="faq-bc-sep">›</span>
        <a href="?action=zbx.faq.articles&categoryid=<?= (int)$article['categoryid'] ?>">
            <?= htmlspecialchars($article['category_name']) ?>
        </a>
        <?php endif; ?>
        <span class="faq-bc-sep">›</span>
        <span><?= $title ?></span>
    </nav>

    <div class="faq-view-container">
        <article class="faq-article-body">
            <div class="faq-art-header">
                <h1 class="faq-art-title"><?= $title ?></h1>
                <?php if ($isAdmin): ?>
                <span class="faq-badge <?= $badge[1] ?>"><?= $badge[0] ?></span>
                <?php endif; ?>
            </div>

            <div class="faq-art-meta">
                <span>Por <strong><?= htmlspecialchars($article['author_name'].' '.$article['author_surname']) ?></strong></span>
                <span><?= date('d/m/Y', (int)$article['created_at']) ?></span>
                <?php if ($article['published_at']): ?>
                <span>Publicado em <?= date('d/m/Y', (int)$article['published_at']) ?></span>
                <?php endif; ?>
            </div>

            <div class="faq-content-wrapper">
                <?php if ($contentType === 0): ?>
                <pre class="faq-content-plain"><?= htmlspecialchars($content) ?></pre>
                <?php elseif ($contentType === 2): ?>
                <div class="faq-content-html"><?= strip_tags($content,'<h1><h2><h3><h4><p><strong><em><ul><ol><li><a><br><hr><table><thead><tbody><tr><th><td><code><pre><blockquote><img><span><div>') ?></div>
                <?php else: ?>
                <div class="faq-content-md" id="faq-md-output" data-raw="<?= htmlspecialchars($content, ENT_QUOTES) ?>"></div>
                <?php endif; ?>
            </div>

            <?php if ($tags): ?>
            <div class="faq-art-tags">
                <span class="faq-tags-label">Tags:</span>
                <?php foreach ($tags as $tag): ?>
                <a href="?action=zbx.faq.articles&tag=<?= urlencode($tag['name']) ?>" class="faq-tag">
                    <?= htmlspecialchars($tag['name']) ?>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if ($media): ?>
            <div class="faq-attachments">
                <div class="faq-attach-title">Anexos / Mídias de Apoio</div>
                <div class="faq-attach-grid">
                    <?php foreach ($media as $m): ?>
                    <?php $isImg = str_starts_with($m['mime_type'],'image/'); ?>
                    <div class="faq-attach-item">
                        <?php if ($isImg): ?>
                        <img src="?action=zbx.faq.media.serve&mediaid=<?= (int)$m['mediaid'] ?>"
                             alt="<?= htmlspecialchars($m['original_name']) ?>"
                             class="faq-attach-thumb"
                             onclick="faqLightbox(this.src,'<?= htmlspecialchars($m['original_name'],ENT_QUOTES) ?>')">
                        <?php else: ?>
                        <div class="faq-attach-icon"><?= mimeIcon($m['mime_type']) ?></div>
                        <?php endif; ?>
                        <div class="faq-attach-info">
                            <a href="?action=zbx.faq.media.serve&mediaid=<?= (int)$m['mediaid'] ?>"
                               download="<?= htmlspecialchars($m['original_name']) ?>"
                               class="faq-attach-name"><?= htmlspecialchars($m['original_name']) ?></a>
                            <span class="faq-attach-size"><?= fmtBytes((int)$m['file_size']) ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </article>

        <?php if ($isAdmin): ?>
        <aside class="faq-view-sidebar">
            <div class="faq-vs-section">
                <div class="faq-vs-title">Ações</div>
                <div class="faq-vs-actions">
                    <a href="?action=zbx.faq.write.edit&articleid=<?= $articleId ?>"
                       class="faq-btn faq-btn-outline" style="width:100%;justify-content:center">✏️ Editar</a>
                    <?php if ($status === 1): ?>
                    <a href="?action=zbx.faq.review.edit&articleid=<?= $articleId ?>"
                       class="faq-btn faq-btn-primary" style="width:100%;justify-content:center">📋 Revisar</a>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($groups): ?>
            <div class="faq-vs-section">
                <div class="faq-vs-title">Grupos com Acesso</div>
                <?php foreach ($groups as $g): ?>
                <div class="faq-vs-group"><?= htmlspecialchars($g['name']) ?></div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="faq-vs-section">
                <div class="faq-vs-title">Acesso</div>
                <span class="faq-vs-note">Todos os grupos autenticados</span>
            </div>
            <?php endif; ?>

            <?php if ($revisions): ?>
            <div class="faq-vs-section">
                <div class="faq-vs-title">Histórico</div>
                <?php
                $statusLabels=[0=>'Rascunho',1=>'Revisão',2=>'Publicado',3=>'Arquivado',4=>'Rejeitado'];
                foreach ($revisions as $rev): ?>
                <div class="faq-rev-item">
                    <span class="faq-rev-who"><?= htmlspecialchars($rev['name'].' '.$rev['surname']) ?></span>
                    <span class="faq-rev-date"><?= date('d/m/y H:i',(int)$rev['changed_at']) ?></span>
                    <span class="faq-rev-note">
                        <?= ($statusLabels[$rev['status_from']]??'?') ?> → <?= ($statusLabels[$rev['status_to']]??'?') ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </aside>
        <?php endif; ?>
    </div>
</div>

<div id="faq-lightbox">
    <div id="faq-lb-backdrop"></div>
    <div id="faq-lb-content">
        <button id="faq-lb-close">✕</button>
        <img id="faq-lb-img" src="" alt="">
        <div id="faq-lb-caption"></div>
    </div>
</div>

</div><!-- .faq-wrap -->
<?php include __DIR__ . '/faq.common.css.php'; ?>
<?php include __DIR__ . '/faq.common.js.php'; ?>

<?php if ($contentType === 1): ?>
<script>
document.addEventListener('DOMContentLoaded', function(){
    var el = document.getElementById('faq-md-output');
    if (el) el.innerHTML = faqMd(el.getAttribute('data-raw') || '');
});
</script>
<?php endif; ?>
