<?php
/**
 * View: zbx.faq.write.php — Meus artigos (autor)
 */
$articles    = $data['articles']    ?? [];
$total       = (int)($data['total']  ?? 0);
$page        = (int)($data['page']   ?? 1);
$limit       = (int)($data['limit']  ?? 20);
$isSA        = (bool)($data['is_sa'] ?? false);
$totalPages  = $total > 0 ? ceil($total / $limit) : 1;

$statusBadge = [
    0 => ['Rascunho',    'faq-badge-draft'],
    1 => ['Em Revisão',  'faq-badge-review'],
    2 => ['Publicado',   'faq-badge-pub'],
    3 => ['Arquivado',   'faq-badge-archive'],
    4 => ['Rejeitado',   'faq-badge-reject'],
];
?>

<div class="faq-wrap">
<div class="faq-edit-header">
    <h1 class="faq-edit-title">Escrever Artigo</h1>
    <a href="?action=zbx.faq.write.edit" class="faq-btn faq-btn-primary">+ Novo Artigo</a>
</div>

<div id="faq-messages"></div>

<?php if ($articles): ?>
<table class="faq-table">
    <thead>
        <tr>
            <th>Título</th>
            <th>Categoria</th>
            <th>Status</th>
            <th>Atualizado</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($articles as $art): ?>
        <?php
            $st    = (int)$art['status'];
            $badge = $statusBadge[$st] ?? ['?', ''];
        ?>
        <tr>
            <td>
                <a href="?action=zbx.faq.write.edit&articleid=<?= (int)$art['articleid'] ?>"
                   class="faq-card-title" style="font-size:13px">
                    <?= htmlspecialchars($art['title']) ?>
                </a>
                <?php if ($st === 4 && $art['review_comment']): ?>
                <div class="faq-reject-comment" style="margin-top:4px;padding:4px 8px;font-size:11px">
                    <strong>Devolvido:</strong> <?= htmlspecialchars($art['review_comment']) ?>
                </div>
                <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($art['category_name'] ?? '—') ?></td>
            <td><span class="faq-badge <?= $badge[1] ?>"><?= $badge[0] ?></span></td>
            <td><?= date('d/m/Y H:i', (int)$art['updated_at']) ?></td>
            <td>
                <div class="faq-card-actions" style="border:none;padding:0;margin:0">
                    <?php if (in_array($st, [0, 4])): ?>
                    <a href="?action=zbx.faq.write.edit&articleid=<?= (int)$art['articleid'] ?>"
                       class="faq-btn faq-btn-sm faq-btn-outline">Editar</a>
                    <?php elseif ($st === 2): ?>
                    <a href="?action=zbx.faq.view&articleid=<?= (int)$art['articleid'] ?>"
                       class="faq-btn faq-btn-sm faq-btn-outline">Ver</a>
                    <?php else: ?>
                    <span class="faq-btn faq-btn-sm faq-btn-outline" style="opacity:.5;cursor:default">Em análise</span>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php if ($totalPages > 1): ?>
<div class="faq-pagination">
    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
    <a href="?action=zbx.faq.write&page=<?= $p ?>"
       class="faq-page-btn<?= $p === $page ? ' active' : '' ?>"><?= $p ?></a>
    <?php endfor; ?>
    <span class="faq-page-info"><?= $total ?> artigo(s)</span>
</div>
<?php endif; ?>

<?php else: ?>
<div class="faq-empty">
    <div class="faq-empty-icon">✍️</div>
    <p>Você ainda não escreveu nenhum artigo.</p>
    <a href="?action=zbx.faq.write.edit" class="faq-btn faq-btn-primary">Criar meu primeiro artigo</a>
</div>
<?php endif; ?>

</div><!-- .faq-wrap -->
<?php include __DIR__ . '/faq.common.css.php'; ?>
