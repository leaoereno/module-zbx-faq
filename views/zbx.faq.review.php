<?php
/**
 * View: zbx.faq.review.php — Fila de revisão (Admin)
 */
$articles   = $data['articles'] ?? [];
$total      = (int)($data['total'] ?? 0);
$page       = (int)($data['page']  ?? 1);
$limit      = (int)($data['limit'] ?? 20);
$isSA       = (bool)($data['is_sa'] ?? false);
$totalPages = $total > 0 ? ceil($total / $limit) : 1;
?>

<div class="faq-wrap">
<div class="faq-edit-header">
    <h1 class="faq-edit-title">Publicação — Artigos em Revisão</h1>
    <span class="faq-badge faq-badge-review" style="font-size:13px;padding:4px 12px"><?= $total ?> pendente(s)</span>
</div>

<div id="faq-messages"></div>

<?php if ($articles): ?>
<table class="faq-table">
    <thead>
        <tr>
            <th>Título</th>
            <th>Categoria</th>
            <th>Autor</th>
            <th>Enviado em</th>
            <th>Tags</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($articles as $art): ?>
        <tr>
            <td>
                <a href="?action=zbx.faq.review.edit&articleid=<?= (int)$art['articleid'] ?>"
                   class="faq-card-title" style="font-size:13px">
                    <?= htmlspecialchars($art['title']) ?>
                </a>
            </td>
            <td><?= htmlspecialchars($art['category_name'] ?? '—') ?></td>
            <td><?= htmlspecialchars($art['author_name'] . ' ' . $art['author_surname']) ?></td>
            <td><?= date('d/m/Y H:i', (int)$art['updated_at']) ?></td>
            <td>
                <?php foreach ($art['tags'] as $tag): ?>
                <span class="faq-tag" style="cursor:default"><?= htmlspecialchars($tag) ?></span>
                <?php endforeach; ?>
            </td>
            <td>
                <div class="faq-card-actions" style="border:none;padding:0;margin:0">
                    <a href="?action=zbx.faq.review.edit&articleid=<?= (int)$art['articleid'] ?>"
                       class="faq-btn faq-btn-sm faq-btn-outline">Revisar</a>
                    <button class="faq-btn faq-btn-sm faq-btn-success"
                            onclick="reviewAction(<?= (int)$art['articleid'] ?>, 'publish')">
                        ✅ Publicar
                    </button>
                    <button class="faq-btn faq-btn-sm faq-btn-danger"
                            onclick="reviewReject(<?= (int)$art['articleid'] ?>)">
                        ↩️ Devolver
                    </button>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php if ($totalPages > 1): ?>
<div class="faq-pagination">
    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
    <a href="?action=zbx.faq.review&page=<?= $p ?>"
       class="faq-page-btn<?= $p === $page ? ' active' : '' ?>"><?= $p ?></a>
    <?php endfor; ?>
</div>
<?php endif; ?>

<?php else: ?>
<div class="faq-empty">
    <div class="faq-empty-icon">✅</div>
    <p>Nenhum artigo aguardando revisão.</p>
</div>
<?php endif; ?>

<!-- Modal de rejeição -->
<div id="faq-reject-modal" style="display:none" class="faq-modal-backdrop">
    <div class="faq-modal">
        <h3 class="faq-modal-title">↩️ Devolver para Revisão</h3>
        <label class="faq-label" for="reject-comment">Comentário para o autor <span class="faq-required">*</span></label>
        <textarea id="reject-comment" class="faq-textarea" rows="4"
                  placeholder="Explique o que precisa ser corrigido..."></textarea>
        <div class="faq-modal-actions">
            <button class="faq-btn faq-btn-outline" onclick="closeRejectModal()">Cancelar</button>
            <button class="faq-btn faq-btn-danger" onclick="confirmReject()">↩️ Devolver</button>
        </div>
    </div>
</div>

</div><!-- .faq-wrap -->
<?php include __DIR__ . '/faq.common.css.php'; ?>
<script>
(function(){
    var pendingRejectId = 0;

    window.reviewAction = function(articleId, action, comment) {
        var body = 'articleid='+encodeURIComponent(articleId)+'&submit_action='+encodeURIComponent(action);
        if (comment) body += '&review_comment='+encodeURIComponent(comment);

        fetch('zabbix.php?action=zbx.faq.review.save',{
            method:'POST',
            headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},
            body: body
        }).then(function(r){return r.json();}).then(function(d){
            if (d.success) { window.location.reload(); }
            else { alert('Erro: '+(d.error||'Falha.')); }
        });
    };

    window.reviewReject = function(articleId) {
        pendingRejectId = articleId;
        document.getElementById('reject-comment').value = '';
        document.getElementById('faq-reject-modal').style.display = 'flex';
    };

    window.closeRejectModal = function() {
        document.getElementById('faq-reject-modal').style.display = 'none';
    };

    window.confirmReject = function() {
        var comment = document.getElementById('reject-comment').value.trim();
        if (!comment) { alert('Por favor, informe o motivo da devolução.'); return; }
        closeRejectModal();
        reviewAction(pendingRejectId, 'reject', comment);
    };
})();
</script>
