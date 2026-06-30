<?php
/**
 * View: zbx.faq.admin.php — Painel de Administração
 */
$tab          = $data['tab']           ?? 'categories';
$tags         = $data['tags']          ?? [];
$categories   = $data['categories']    ?? [];
$flatCats     = $data['flat_cats']     ?? [];
$articles     = $data['articles']      ?? [];
$total        = (int)($data['total']   ?? 0);
$page         = (int)($data['page']    ?? 1);
$limit        = (int)($data['limit']   ?? 30);
$mediaTypes   = $data['media_types']   ?? [];
$faqMediatype = $data['faq_mediatype'] ?? null;
$isSA         = (bool)($data['is_sa'] ?? false);
$totalPages   = $total > 0 ? ceil($total / $limit) : 1;

$statusBadge = [
    0 => ['Rascunho',   'faq-badge-draft'],
    1 => ['Em Revisão', 'faq-badge-review'],
    2 => ['Publicado',  'faq-badge-pub'],
    3 => ['Arquivado',  'faq-badge-archive'],
    4 => ['Rejeitado',  'faq-badge-reject'],
];

function renderAdminTree(array $nodes, int $depth = 0): void {
    foreach ($nodes as $node) {
        $indent = str_repeat('&nbsp;&nbsp;&nbsp;', $depth);
        $catId  = (int)$node['categoryid'];
        echo '<tr>';
        echo '<td>' . $indent . ($depth > 0 ? '↳ ' : '') . htmlspecialchars($node['name']) . '</td>';
        echo '<td>' . ($node['parent_id'] ? '' : '<em>Raiz</em>') . '</td>';
        echo '<td>';
        echo '<button type="button" class="faq-btn faq-btn-sm faq-btn-outline" onclick="catEdit(' . $catId . ')">Editar</button> ';
        echo '<button type="button" class="faq-btn faq-btn-sm faq-btn-danger" onclick="catDelete(' . $catId . ')">Excluir</button>';
        echo '</td>';
        echo '</tr>';
        if (!empty($node['children'])) {
            renderAdminTree($node['children'], $depth + 1);
        }
    }
}
?>

<div class="faq-wrap">
<div class="faq-edit-header">
    <h1 class="faq-edit-title">Administração FAQ</h1>
</div>

<!-- Tabs -->
<div class="faq-tabs">
    <a href="?action=zbx.faq.admin&tab=categories" class="faq-tab<?= $tab==='categories'?' active':'' ?>">📁 Categorias</a>
    <a href="?action=zbx.faq.admin&tab=articles"   class="faq-tab<?= $tab==='articles'?' active':'' ?>">📄 Artigos</a>
    <a href="?action=zbx.faq.admin&tab=tags"          class="faq-tab<?= $tab==='tags'?' active':'' ?>">🏷️ Tags</a>
    <a href="?action=zbx.faq.admin&tab=notifications" class="faq-tab<?= $tab==='notifications'?' active':'' ?>">🔔 Notificações</a>
</div>

<div id="faq-messages"></div>

<!-- ===== TAB CATEGORIAS ===== -->
<?php if ($tab === 'categories'): ?>
<div style="display:flex;gap:20px;align-items:flex-start">

    <!-- Formulário de categoria -->
    <div style="width:280px;flex-shrink:0">
        <div class="faq-sb-section">
            <div class="faq-sb-title" id="cat-form-title">Nova Categoria</div>
            <input type="hidden" id="cat-id" value="0">
            <div class="faq-field">
                <label class="faq-label" for="cat-name">Nome <span class="faq-required">*</span></label>
                <input type="text" id="cat-name" class="faq-input faq-input-full" placeholder="Nome da categoria" maxlength="255">
            </div>
            <div class="faq-field">
                <label class="faq-label" for="cat-desc">Descrição</label>
                <textarea id="cat-desc" class="faq-textarea" rows="2" placeholder="Opcional"></textarea>
            </div>
            <div class="faq-field">
                <label class="faq-label" for="cat-parent">Categoria Pai</label>
                <select id="cat-parent" class="faq-select faq-select-full">
                    <option value="">— Raiz —</option>
                    <?php foreach ($flatCats as $c): ?>
                    <option value="<?= (int)$c['categoryid'] ?>"><?= htmlspecialchars($c['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="faq-field">
                <label class="faq-label" for="cat-sort">Ordem</label>
                <input type="number" id="cat-sort" class="faq-input" value="0" min="0" style="width:80px">
            </div>
            <div style="display:flex;gap:6px">
                <button type="button" id="cat-save-btn" class="faq-btn faq-btn-primary">💾 Salvar</button>
                <button type="button" id="cat-cancel-btn" class="faq-btn faq-btn-outline" style="display:none">Cancelar</button>
            </div>
        </div>
    </div>

    <!-- Árvore de categorias -->
    <div style="flex:1">
        <?php if ($categories): ?>
        <table class="faq-table">
            <thead><tr><th>Nome</th><th>Pai</th><th>Ações</th></tr></thead>
            <tbody>
                <?php renderAdminTree($categories); ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="faq-empty"><div class="faq-empty-icon">📁</div><p>Nenhuma categoria criada.</p></div>
        <?php endif; ?>
    </div>
</div>

<!-- ===== TAB ARTIGOS ===== -->
<?php elseif ($tab === 'articles'): ?>
<?php if ($articles): ?>
<table class="faq-table">
    <thead>
        <tr>
            <th>Título</th><th>Categoria</th><th>Autor</th><th>Status</th><th>Atualizado</th><th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($articles as $art): ?>
        <?php $st = (int)$art['status']; $badge = $statusBadge[$st] ?? ['?','']; ?>
        <tr>
            <td><a href="?action=zbx.faq.view&articleid=<?= (int)$art['articleid'] ?>" class="faq-card-title" style="font-size:13px"><?= htmlspecialchars($art['title']) ?></a></td>
            <td><?= htmlspecialchars($art['category_name']??'—') ?></td>
            <td><?= htmlspecialchars($art['author_name'].' '.$art['author_surname']) ?></td>
            <td><span class="faq-badge <?= $badge[1] ?>"><?= $badge[0] ?></span></td>
            <td><?= date('d/m/Y H:i', (int)$art['updated_at']) ?></td>
            <td>
                <div class="faq-card-actions" style="border:none;padding:0;margin:0">
                    <?php if ($st === 2): ?>
                    <button type="button" class="faq-btn faq-btn-sm faq-btn-warning" onclick="articleArchive(<?= (int)$art['articleid'] ?>)">Arquivar</button>
                    <?php endif; ?>
                    <button type="button" class="faq-btn faq-btn-sm faq-btn-danger" onclick="articleDelete(<?= (int)$art['articleid'] ?>)">Excluir</button>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php if ($totalPages > 1): ?>
<div class="faq-pagination">
    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
    <a href="?action=zbx.faq.admin&tab=articles&page=<?= $p ?>"
       class="faq-page-btn<?= $p===$page?' active':'' ?>"><?= $p ?></a>
    <?php endfor; ?>
    <span class="faq-page-info"><?= $total ?> artigo(s)</span>
</div>
<?php endif; ?>
<?php else: ?>
<div class="faq-empty"><div class="faq-empty-icon">📄</div><p>Nenhum artigo encontrado.</p></div>
<?php endif; ?>

<!-- ===== TAB NOTIFICAÇÕES ===== -->
<?php elseif ($tab === 'tags'): ?>
<div class="faq-admin-section">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
        <h3 style="margin:0">🏷️ Gerenciar Tags</h3>
        <span style="color:#888;font-size:13px">Tags sem uso podem ser removidas com segurança.</span>
    </div>
    <?php if ($tags): ?>
    <table class="faq-table">
        <thead><tr><th>Tag</th><th style="text-align:center;width:120px">Artigos</th><th style="width:100px">Ação</th></tr></thead>
        <tbody>
        <?php foreach ($tags as $tag): ?>
        <tr id="faq-tag-row-<?= (int)$tag['tagid'] ?>">
            <td><span class="faq-tag-chip" style="cursor:default"><?= htmlspecialchars($tag['name']) ?></span></td>
            <td style="text-align:center">
                <span style="background:<?= (int)$tag['usage_count']===0?'#fef2f2;color:#dc2626':'#f0fdf4;color:#16a34a' ?>;padding:2px 10px;border-radius:12px;font-size:12px;font-weight:600">
                    <?= (int)$tag['usage_count'] ?> artigo<?= (int)$tag['usage_count']!==1?'s':'' ?>
                </span>
            </td>
            <td>
                <button type="button" class="faq-btn faq-btn-sm faq-btn-danger"
                        onclick="faqDeleteTag(<?= (int)$tag['tagid'] ?>, '<?= htmlspecialchars($tag['name'],ENT_QUOTES) ?>', this)">
                    🗑 Excluir
                </button>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p class="faq-empty">Nenhuma tag cadastrada.</p>
    <?php endif; ?>
</div>

<?php elseif ($tab === 'notifications'): ?>
<div style="max-width:560px">
    <div class="faq-sb-section">
        <div class="faq-sb-title">Media Type para Notificações FAQ</div>
        <p style="font-size:13px;color:var(--color-text-secondary,#666);margin-bottom:12px">
            Crie um <strong>Media Type</strong> no Zabbix com o nome exato <code>FAQ Notifications</code> e configure o email/webhook desejado.
            O módulo FAQ usará automaticamente esse media type para notificar autores e revisores.
        </p>
        <?php if ($faqMediatype): ?>
        <div class="faq-msg faq-msg-ok">
            ✅ Media Type configurado: <strong><?= htmlspecialchars($faqMediatype['name']) ?></strong>
            (ID: <?= (int)$faqMediatype['mediatypeid'] ?>)
        </div>
        <?php else: ?>
        <div class="faq-msg faq-msg-err">
            ⚠️ Media Type "FAQ Notifications" não encontrado ou inativo.
        </div>
        <?php endif; ?>

        <div style="margin-top:14px">
            <div class="faq-sb-title">Media Types ativos disponíveis</div>
            <table class="faq-table">
                <thead><tr><th>ID</th><th>Nome</th></tr></thead>
                <tbody>
                    <?php foreach ($mediaTypes as $mt): ?>
                    <tr>
                        <td><?= (int)$mt['mediatypeid'] ?></td>
                        <td><?= htmlspecialchars($mt['name']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div style="margin-top:14px;padding:12px;background:var(--color-bg-alt,#f5f5f5);border-radius:4px;font-size:12px">
            <strong>Fluxo de notificações:</strong><br>
            📤 Artigo enviado para revisão → notifica <em>admins dos grupos</em><br>
            ✅ Artigo publicado → notifica <em>autor</em><br>
            ↩️ Artigo devolvido → notifica <em>autor</em> com comentário<br>
            ✏️ Artigo editado pelo revisor → notifica <em>autor</em>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Dados para JS (categorias para edição) -->
<script>
var faqCatData = <?= json_encode(array_map(function($c){return ['categoryid'=>(int)$c['categoryid'],'name'=>$c['name'],'label'=>$c['label']];}, $flatCats)) ?>;
</script>

</div><!-- .faq-wrap -->
<?php include __DIR__ . '/faq.common.css.php'; ?>
<script>
// Funções globais (acessíveis via onclick no HTML)
var _faqAdminCatId = 0;

function catEdit(id) {
    _faqAdminCatId = id;
    var cat = faqCatData.find(function(c){ return c.categoryid === id; });
    if (!cat) return;
    document.getElementById('cat-id').value = id;
    document.getElementById('cat-name').value = cat.name;
    document.getElementById('cat-form-title').textContent = 'Editar Categoria';
    document.getElementById('cat-cancel-btn').style.display = 'inline-flex';
    document.getElementById('cat-name').focus();
}

function catDelete(id) {

    fetch('zabbix.php?action=zbx.faq.admin.category.delete', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
        body: 'categoryid=' + encodeURIComponent(id)
    }).then(function(r){ return r.json(); }).then(function(d){
        if (d.success) window.location.reload();
        else faqAdminMsg('Erro: ' + (d.error || 'Falha.'), false);
    }).catch(function(e){ faqAdminMsg('Erro de comunicação.', false); });
}

function articleDelete(id) {

    fetch('zabbix.php?action=zbx.faq.admin.article.delete', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
        body: 'articleid=' + encodeURIComponent(id)
    }).then(function(r){ return r.json(); }).then(function(d){
        if (d.success) window.location.reload();
        else faqAdminMsg('Erro: ' + (d.error || 'Falha.'), false);
    }).catch(function(e){ faqAdminMsg('Erro de comunicação.', false); });
}

function articleArchive(id) {

    // Chama o save de revisão com action=archive (status=3)
    fetch('zabbix.php?action=zbx.faq.review.save', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
        body: 'articleid=' + encodeURIComponent(id) + '&submit_action=archive'
    }).then(function(r){ return r.json(); }).then(function(d){
        if (d.success) window.location.reload();
        else faqAdminMsg('Erro: ' + (d.error || 'Falha.'), false);
    }).catch(function(e){ faqAdminMsg('Erro de comunicação.', false); });
}

function faqAdminMsg(text, ok) {
    var div = document.getElementById('faq-messages');
    if (!div) return;
    div.innerHTML = '<div class="faq-msg ' + (ok ? 'faq-msg-ok' : 'faq-msg-err') + '">' + text + '</div>';
    setTimeout(function(){ div.innerHTML = ''; }, 6000);
}

document.addEventListener('DOMContentLoaded', function(){
    var saveBtn   = document.getElementById('cat-save-btn');
    var cancelBtn = document.getElementById('cat-cancel-btn');

    if (saveBtn) saveBtn.addEventListener('click', function(){
        var name   = document.getElementById('cat-name').value.trim();
        var desc   = document.getElementById('cat-desc').value.trim();
        var parent = document.getElementById('cat-parent').value;
        var sort   = document.getElementById('cat-sort').value;
        if (!name) { faqAdminMsg('Nome é obrigatório.', false); return; }

        var body = 'name=' + encodeURIComponent(name) +
                   '&description=' + encodeURIComponent(desc) +
                   '&parent_id=' + encodeURIComponent(parent || '') +
                   '&sort_order=' + encodeURIComponent(sort);
        if (_faqAdminCatId > 0) body += '&categoryid=' + _faqAdminCatId;

        var btn = this; btn.disabled = true; btn.textContent = 'Salvando...';
        fetch('zabbix.php?action=zbx.faq.admin.category.save', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
            body: body
        }).then(function(r){ return r.json(); }).then(function(d){
            btn.disabled = false; btn.textContent = '💾 Salvar';
            if (d.success) window.location.reload();
            else faqAdminMsg('Erro: ' + (d.error || 'Falha.'), false);
        }).catch(function(e){ btn.disabled = false; faqAdminMsg('Erro: ' + e, false); });
    });

    if (cancelBtn) cancelBtn.addEventListener('click', function(){
        _faqAdminCatId = 0;
        document.getElementById('cat-id').value = 0;
        document.getElementById('cat-name').value = '';
        document.getElementById('cat-desc').value = '';
        document.getElementById('cat-sort').value = 0;
        document.getElementById('cat-form-title').textContent = 'Nova Categoria';
        this.style.display = 'none';
    });
});

function faqDeleteTag(tagId, tagName, btn) {
    btn.disabled = true;
    fetch('zabbix.php?action=zbx.faq.admin.tag.delete', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},
        body: 'tagid=' + tagId
    })
    .then(function(r){ return r.json(); })
    .then(function(d){
        if (d.success) {
            var row = document.getElementById('faq-tag-row-' + tagId);
            if (row) row.remove();
        } else {
            btn.disabled = false;
            alert('Erro: ' + (d.error || 'Falha.'));
        }
    })
    .catch(function(){ btn.disabled = false; alert('Erro de comunicação.'); });
}
</script>
