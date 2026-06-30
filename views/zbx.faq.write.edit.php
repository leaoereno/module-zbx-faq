<?php
/**
 * View: zbx.faq.write.edit.php — Editor de artigo (autor) v2.1.0
 */
$article     = $data['article']      ?? null;
$tags        = $data['tags']         ?? [];
$groups      = $data['groups']       ?? [];
$media       = $data['media']        ?? [];
$availGroups = $data['avail_groups'] ?? [];
$allTags     = $data['all_tags']     ?? [];
$categories  = $data['categories']   ?? [];
$isSA        = (bool)($data['is_sa']    ?? false);
$isAdmin     = (bool)($data['is_admin'] ?? false);

$isEdit      = $article !== null;
$articleId   = $isEdit ? (int)$article['articleid']    : 0;
$title       = $isEdit ? htmlspecialchars($article['title'], ENT_QUOTES) : '';
$contentType = $isEdit ? (int)$article['content_type'] : 1;
$content     = $isEdit ? $article['content']           : '';
$categoryId  = $isEdit ? (int)$article['categoryid']   : 0;
$status      = $isEdit ? (int)$article['status']       : 0;
$pageTitle   = $isEdit ? 'Editar Artigo' : 'Novo Artigo FAQ';

$rejComment = $isEdit && $status === 4 && !empty($article['review_comment'])
    ? htmlspecialchars($article['review_comment'])
    : '';
?>

<div class="faq-wrap">
<div class="faq-edit-header">
    <h1 class="faq-edit-title"><?= $pageTitle ?></h1>
    <a href="?action=zbx.faq.write" class="faq-btn faq-btn-outline">← Meus Artigos</a>
</div>

<?php if ($rejComment): ?>
<div class="faq-reject-comment">
    <strong>Artigo devolvido pelo revisor:</strong> <?= $rejComment ?>
    <br><small>Faça as correções e reenvie para revisão.</small>
</div>
<?php endif; ?>

<div id="faq-messages"></div>

<div class="faq-edit-layout">
    <div class="faq-edit-main">

        <div class="faq-field">
            <label class="faq-label" for="faq-title">Título <span class="faq-required">*</span></label>
            <input type="text" id="faq-title" class="faq-input faq-input-full"
                   value="<?= $title ?>" placeholder="Título do artigo..." maxlength="500">
        </div>

        <div class="faq-field">
            <label class="faq-label">Formato</label>
            <div class="faq-radio-group">
                <?php foreach ([0 => 'Texto Simples', 1 => 'Markdown', 2 => 'HTML'] as $v => $l): ?>
                <label class="faq-radio<?= $contentType === $v ? ' selected' : '' ?>">
                    <input type="radio" name="content_type" value="<?= $v ?>"<?= $contentType === $v ? ' checked' : '' ?>>
                    <?= $l ?>
                </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="faq-field">
            <label class="faq-label" for="faq-content">
                Conteúdo <span class="faq-required">*</span>
                <button type="button" id="faq-preview-toggle" class="faq-btn faq-btn-sm faq-btn-outline" style="margin-left:8px">👁 Preview</button>
            </label>
            <div class="faq-editor-wrap">
                <div id="faq-md-toolbar" class="faq-md-toolbar" style="display:<?= $contentType===1?'flex':'none' ?>">
                    <button type="button" class="faq-md-tool" data-action="bold"><b>B</b></button>
                    <button type="button" class="faq-md-tool" data-action="italic"><i>I</i></button>
                    <button type="button" class="faq-md-tool" data-action="heading">H1</button>
                    <button type="button" class="faq-md-tool" data-action="link">🔗</button>
                    <button type="button" class="faq-md-tool" data-action="code">&lt;/&gt;</button>
                    <button type="button" class="faq-md-tool" data-action="codeblock">📋</button>
                    <button type="button" class="faq-md-tool" data-action="ul">•</button>
                    <button type="button" class="faq-md-tool" data-action="blockquote">"</button>
                    <button type="button" class="faq-md-tool" data-action="hr">—</button>
                    <button type="button" class="faq-md-tool" data-action="table">⊞</button>
                </div>
                <textarea id="faq-content" class="faq-code-editor" rows="16"
                          placeholder="Escreva o conteúdo aqui..."><?= htmlspecialchars($content, ENT_QUOTES) ?></textarea>
                <div id="faq-preview-pane" class="faq-preview-pane" style="display:none"></div>
            </div>
        </div>

        <div class="faq-field" style="position:relative">
            <label class="faq-label">Tags</label>
            <div class="faq-tags-input" id="faq-tags-container">
                <?php foreach ($tags as $tag): ?>
                <span class="faq-tag-chip">
                    <?= htmlspecialchars($tag) ?>
                    <button type="button" class="faq-tag-chip-del" data-tag="<?= htmlspecialchars($tag, ENT_QUOTES) ?>">✕</button>
                </span>
                <?php endforeach; ?>
                <input type="text" class="faq-tag-text-input" placeholder="Adicionar tag... (Enter)">
            </div>
            <input type="hidden" id="faq-tags-hidden" value="<?= htmlspecialchars(implode(',', $tags), ENT_QUOTES) ?>">
            <div id="faq-tags-container-suggestions" class="faq-tag-suggestions" style="display:none"></div>
        </div>

        <?php /* upload sempre visível — salva rascunho automático se necessário */ ?>
        <div class="faq-field">
            <label class="faq-label">Mídias de Apoio / Anexos</label>
            <div class="faq-upload-area" id="faq-upload-area">
                <input type="file" id="faq-file-input" multiple
                       accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt,.csv,.mp4,.webm">
                <label for="faq-file-input" class="faq-upload-label">
                    <span class="faq-upload-label-icon">📎</span>
                    Clique ou arraste arquivos aqui<br>
                    <small>Imagens, PDF, Documentos — máx. 10 MB</small>
                </label>
            </div>
            <div id="faq-media-list" class="faq-media-list">
                <?php foreach ($media as $m): ?>
                <?php $isImg = str_starts_with($m['mime_type'], 'image/'); ?>
                <div class="faq-media-item" id="faq-media-<?= (int)$m['mediaid'] ?>">
                    <?php if ($isImg): ?>
                    <img src="?action=zbx.faq.media.serve&mediaid=<?= (int)$m['mediaid'] ?>"
                         class="faq-media-thumb" alt="<?= htmlspecialchars($m['original_name']) ?>">
                    <?php else: ?><div class="faq-media-icon">📎</div><?php endif; ?>
                    <span class="faq-media-name"><?= htmlspecialchars($m['original_name']) ?></span>
                    <button type="button" class="faq-btn faq-btn-sm faq-btn-danger"
                            onclick="faqDeleteMedia(<?= (int)$m['mediaid'] ?>, this)">✕</button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>


    </div>

    <aside class="faq-edit-sidebar">
        <div class="faq-sb-section">
            <div class="faq-sb-title">Categoria <span class="faq-required">*</span></div>
            <select id="faq-category" class="faq-select faq-select-full">
                <option value="">— Selecione —</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?= (int)$cat['categoryid'] ?>"<?= (int)$cat['categoryid'] === $categoryId ? ' selected' : '' ?>>
                    <?= htmlspecialchars($cat['label']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="faq-sb-section">
            <div class="faq-sb-title">Grupos com Acesso</div>
            <div class="faq-sb-note">Deixe vazio para acesso de todos.</div>
            <div class="faq-groups-list">
                <?php foreach ($availGroups as $g): ?>
                <label class="faq-group-check">
                    <input type="checkbox" value="<?= (int)$g['usrgrpid'] ?>"
                           <?= in_array((int)$g['usrgrpid'], $groups) ? 'checked' : '' ?>>
                    <?= htmlspecialchars($g['name']) ?>
                </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="faq-sb-section">
            <button type="button" id="faq-save-draft" class="faq-btn faq-btn-outline faq-btn-full" style="margin-bottom:6px">
                💾 Salvar Rascunho
            </button>
            <button type="button" id="faq-submit-review" class="faq-btn faq-btn-primary faq-btn-full">
                📤 Enviar para Revisão
            </button>
        </div>
    </aside>
</div>
</div><!-- .faq-wrap -->

<?php include __DIR__ . '/faq.common.css.php'; ?>
<?php include __DIR__ . '/faq.common.js.php'; ?>

<script>
(function(){
    var articleId = <?= $articleId ?>;
    var allTags   = <?= json_encode($allTags) ?>;

    // Formato
    document.querySelectorAll('.faq-radio').forEach(function(lbl){
        lbl.querySelector('input').addEventListener('change', function(){
            document.querySelectorAll('.faq-radio').forEach(function(l){ l.classList.remove('selected'); });
            lbl.classList.add('selected');
            var toolbar = document.getElementById('faq-md-toolbar');
            if (toolbar) toolbar.style.display = parseInt(this.value) === 1 ? 'flex' : 'none';
        });
    });

    // Preview
    var previewOn = false;
    document.getElementById('faq-preview-toggle').addEventListener('click', function(){
        previewOn = !previewOn;
        var ta   = document.getElementById('faq-content');
        var pane = document.getElementById('faq-preview-pane');
        if (previewOn) {
            ta.style.display = 'none'; pane.style.display = 'block';
            var ct = parseInt(document.querySelector('input[name=content_type]:checked').value || 1);
            pane.innerHTML = ct === 0
                ? '<pre style="white-space:pre-wrap">' + faqEscHtml(ta.value) + '</pre>'
                : ct === 2 ? ta.value : faqMd(ta.value);
            this.textContent = '✏️ Editar';
        } else {
            ta.style.display = 'block'; pane.style.display = 'none';
            this.textContent = '👁 Preview';
        }
    });

    faqInitToolbar('faq-md-toolbar', 'faq-content');
    faqInitTags('faq-tags-container', 'faq-tags-hidden', allTags);

    // Salva rascunho automaticamente antes do upload se articleId ainda não existe
    function autoSaveRascunho(callback) {
        if (articleId > 0) { callback(articleId); return; }
        var title   = document.getElementById('faq-title').value.trim();
        var content = document.getElementById('faq-content').value;
        var ct      = document.querySelector('input[name=content_type]:checked').value;
        var catId   = document.getElementById('faq-category').value;
        if (!title)  { showMsg('Preencha o título antes de anexar um arquivo.', false); return; }
        if (!catId)  { showMsg('Selecione uma categoria antes de anexar um arquivo.', false); return; }
        if (!content) content = '(rascunho)';
        fetch('zabbix.php?action=zbx.faq.write.save', {
            method: 'POST',
            headers: {'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},
            body: 'title='         + encodeURIComponent(title)
                + '&content='      + encodeURIComponent(content)
                + '&content_type=' + encodeURIComponent(ct)
                + '&categoryid='   + encodeURIComponent(catId)
                + '&submit_action=save'
        })
        .then(function(r){ return r.json(); })
        .then(function(d){
            if (d.success && d.articleid) {
                articleId = d.articleid;
                // Atualiza a URL sem recarregar a página
                history.replaceState(null, '', '?action=zbx.faq.write.edit&articleid=' + articleId);
                showMsg('Rascunho salvo automaticamente.', true);
                callback(articleId);
            } else {
                showMsg('Erro ao salvar rascunho: ' + (d.error || 'Falha.'), false);
            }
        })
        .catch(function(e){ showMsg('Erro: ' + e, false); });
    }

    faqInitUploadAutoSave('faq-upload-area', 'faq-file-input', 'faq-media-list', articleId, autoSaveRascunho);

    function getBody(submitAction) {
        var title   = document.getElementById('faq-title').value.trim();
        var content = document.getElementById('faq-content').value;
        var ct      = document.querySelector('input[name=content_type]:checked').value;
        var catId   = document.getElementById('faq-category').value;
        var tags    = document.getElementById('faq-tags-hidden').value;
        var groups  = [];
        document.querySelectorAll('.faq-groups-list input:checked').forEach(function(cb){ groups.push(cb.value); });

        if (!title)   { showMsg('O título é obrigatório.', false);   return null; }
        if (!content) { showMsg('O conteúdo é obrigatório.', false); return null; }
        if (!catId)   { showMsg('Selecione uma categoria.', false);  return null; }

        /* NOTA: usa 'submit_action' para não conflitar com ?action= na URL */
        var body = 'title='          + encodeURIComponent(title)
                 + '&content='       + encodeURIComponent(content)
                 + '&content_type='  + encodeURIComponent(ct)
                 + '&categoryid='    + encodeURIComponent(catId)
                 + '&submit_action=' + encodeURIComponent(submitAction);

        if (tags) tags.split(',').forEach(function(t){ if (t.trim()) body += '&tags[]=' + encodeURIComponent(t.trim()); });
        groups.forEach(function(g){ body += '&groups[]=' + encodeURIComponent(g); });
        if (articleId > 0) body += '&articleid=' + articleId;
        return body;
    }

    function doSave(submitAction, btn) {
        var body = getBody(submitAction);
        if (!body) return;
        btn.disabled = true;
        btn.textContent = 'Salvando...';

        fetch('zabbix.php?action=zbx.faq.write.save', {
            method: 'POST',
            headers: {
                'Content-Type':    'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: body
        })
        .then(function(r){ return r.json(); })
        .then(function(d){
            btn.disabled = false;
            btn.textContent = submitAction === 'save' ? '💾 Salvar Rascunho' : '📤 Enviar para Revisão';
            if (d.success) {
                if (submitAction === 'submit') {
                    showMsg('Artigo enviado para revisão!', true);
                    setTimeout(function(){ window.location.href = '?action=zbx.faq.write'; }, 1200);
                } else {
                    showMsg('Rascunho salvo!', true);
                    if (!articleId && d.articleid) {
                        setTimeout(function(){
                            window.location.href = '?action=zbx.faq.write.edit&articleid=' + d.articleid;
                        }, 800);
                    }
                }
            } else {
                showMsg('Erro: ' + (d.error || 'Falha.'), false);
            }
        })
        .catch(function(e){ btn.disabled = false; showMsg('Erro de comunicação: ' + e, false); });
    }

    document.getElementById('faq-save-draft').addEventListener('click', function(){ doSave('save', this); });
    document.getElementById('faq-submit-review').addEventListener('click', function(){
        doSave('submit', this);
    });

    function showMsg(text, ok) {
        var div = document.getElementById('faq-messages');
        div.innerHTML = '<div class="faq-msg ' + (ok ? 'faq-msg-ok' : 'faq-msg-err') + '">' + faqEscHtml(text) + '</div>';
        setTimeout(function(){ div.innerHTML = ''; }, 5000);
    }
})();
</script>
