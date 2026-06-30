<?php
/**
 * View: zbx.faq.review.edit.php — Editor de revisão (Admin)
 */
$article    = $data['article']    ?? [];
$tags       = $data['tags']       ?? [];
$groups     = $data['groups']     ?? [];
$media      = $data['media']      ?? [];
$revisions  = $data['revisions']  ?? [];
$allGroups  = $data['all_groups'] ?? [];
$allTags    = $data['all_tags']   ?? [];
$categories = $data['categories'] ?? [];
$isSA       = (bool)($data['is_sa'] ?? false);

$articleId   = (int)$article['articleid'];
$title       = htmlspecialchars($article['title'], ENT_QUOTES);
$content     = $article['content'];
$contentType = (int)$article['content_type'];
$categoryId  = (int)$article['categoryid'];

$statusLabels = [0=>'Rascunho',1=>'Em Revisão',2=>'Publicado',3=>'Arquivado',4=>'Rejeitado'];
?>

<div class="faq-wrap">
<div class="faq-edit-header">
    <h1 class="faq-edit-title">Revisão de Artigo</h1>
    <div style="display:flex;gap:8px">
        <span class="faq-badge faq-badge-review" style="padding:5px 12px;font-size:12px">Em Revisão</span>
        <a href="?action=zbx.faq.review" class="faq-btn faq-btn-outline">← Fila de Revisão</a>
    </div>
</div>

<div id="faq-messages"></div>

<div class="faq-edit-layout">
    <div class="faq-edit-main">

        <div class="faq-field">
            <label class="faq-label" for="faq-title">Título <span class="faq-required">*</span></label>
            <input type="text" id="faq-title" class="faq-input faq-input-full" value="<?= $title ?>" maxlength="500">
        </div>

        <div class="faq-field">
            <label class="faq-label">Formato</label>
            <div class="faq-radio-group">
                <?php foreach ([0=>'Texto Simples',1=>'Markdown',2=>'HTML'] as $v=>$l): ?>
                <label class="faq-radio<?= $contentType===$v?' selected':'' ?>">
                    <input type="radio" name="content_type" value="<?= $v ?>"<?= $contentType===$v?' checked':'' ?>>
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
                <textarea id="faq-content" class="faq-code-editor" rows="16"><?= htmlspecialchars($content, ENT_QUOTES) ?></textarea>
                <div id="faq-preview-pane" class="faq-preview-pane" style="display:none"></div>
            </div>
        </div>

        <div class="faq-field" style="position:relative">
            <label class="faq-label">Tags</label>
            <div class="faq-tags-input" id="faq-tags-container">
                <?php foreach ($tags as $tag): ?>
                <span class="faq-tag-chip">
                    <?= htmlspecialchars($tag) ?>
                    <button type="button" class="faq-tag-chip-del" data-tag="<?= htmlspecialchars($tag,ENT_QUOTES) ?>">✕</button>
                </span>
                <?php endforeach; ?>
                <input type="text" class="faq-tag-text-input" placeholder="Adicionar tag...">
            </div>
            <input type="hidden" id="faq-tags-hidden" value="<?= htmlspecialchars(implode(',', $tags), ENT_QUOTES) ?>">
            <div id="faq-tags-container-suggestions" class="faq-tag-suggestions" style="display:none"></div>
        </div>

        <!-- Anexos -->
        <div class="faq-field">
            <label class="faq-label">Mídias de Apoio</label>
            <div class="faq-upload-area" id="faq-upload-area">
                <input type="file" id="faq-file-input" multiple accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt,.csv,.mp4,.webm">
                <label for="faq-file-input" class="faq-upload-label"><span class="faq-upload-label-icon">📎</span>📎 Clique ou arraste arquivos</label>
            </div>
            <div id="faq-media-list" class="faq-media-list">
                <?php foreach ($media as $m): ?>
                <?php $isImg = str_starts_with($m['mime_type'], 'image/'); ?>
                <div class="faq-media-item" id="faq-media-<?= (int)$m['mediaid'] ?>">
                    <?php if ($isImg): ?>
                    <img src="?action=zbx.faq.media.serve&mediaid=<?= (int)$m['mediaid'] ?>" class="faq-media-thumb" alt="<?= htmlspecialchars($m['original_name']) ?>">
                    <?php else: ?><div class="faq-media-icon">📎</div><?php endif; ?>
                    <span class="faq-media-name"><?= htmlspecialchars($m['original_name']) ?></span>
                    <button type="button" class="faq-btn faq-btn-sm faq-btn-danger" onclick="faqDeleteMedia(<?= (int)$m['mediaid'] ?>, this)">✕</button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Comentário de rejeição -->
        <div class="faq-field" id="faq-reject-section" style="display:none">
            <label class="faq-label" for="faq-review-comment">
                Comentário para o Autor <span class="faq-required">*</span>
            </label>
            <textarea id="faq-review-comment" class="faq-textarea" rows="3"
                      placeholder="Explique o que precisa ser corrigido..."></textarea>
        </div>

    </div>

    <aside class="faq-edit-sidebar">
        <div class="faq-sb-section">
            <div class="faq-sb-title">Categoria</div>
            <select id="faq-category" class="faq-select faq-select-full">
                <?php foreach ($categories as $cat): ?>
                <option value="<?= (int)$cat['categoryid'] ?>"<?= (int)$cat['categoryid']===$categoryId?' selected':'' ?>>
                    <?= htmlspecialchars($cat['label']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="faq-sb-section">
            <div class="faq-sb-title">Grupos com Acesso</div>
            <div class="faq-groups-list">
                <?php foreach ($allGroups as $g): ?>
                <label class="faq-group-check">
                    <input type="checkbox" value="<?= (int)$g['usrgrpid'] ?>"
                           <?= in_array((int)$g['usrgrpid'], $groups) ? 'checked' : '' ?>>
                    <?= htmlspecialchars($g['name']) ?>
                </label>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Ações de revisão -->
        <div class="faq-sb-section">
            <button type="button" id="faq-btn-save" class="faq-btn faq-btn-outline faq-btn-full" style="margin-bottom:6px">
                💾 Salvar Edições
            </button>
            <button type="button" id="faq-btn-publish" class="faq-btn faq-btn-success faq-btn-full" style="margin-bottom:6px">
                ✅ Publicar Artigo
            </button>
            <button type="button" id="faq-btn-reject" class="faq-btn faq-btn-danger faq-btn-full">
                ↩️ Devolver para Revisão
            </button>
        </div>

        <!-- Histórico -->
        <?php if ($revisions): ?>
        <div class="faq-sb-section">
            <div class="faq-sb-title">Histórico</div>
            <?php foreach ($revisions as $rev): ?>
            <div class="faq-rev-item">
                <span class="faq-rev-who"><?= htmlspecialchars($rev['name'].' '.$rev['surname']) ?></span>
                <span class="faq-rev-date"><?= date('d/m/y H:i', (int)$rev['changed_at']) ?></span>
                <?php if ($rev['note']): ?>
                <span class="faq-rev-note"><?= htmlspecialchars($rev['note']) ?></span>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </aside>
</div>

</div><!-- .faq-wrap -->
<?php include __DIR__ . '/faq.common.css.php'; ?>
<?php include __DIR__ . '/faq.common.js.php'; ?>

<script>
(function(){
    var articleId = <?= $articleId ?>;
    var allTags   = <?= json_encode($allTags) ?>;

    document.querySelectorAll('.faq-radio').forEach(function(lbl){
        lbl.querySelector('input').addEventListener('change',function(){
            document.querySelectorAll('.faq-radio').forEach(function(l){l.classList.remove('selected');});
            lbl.classList.add('selected');
            var toolbar = document.getElementById('faq-md-toolbar');
            if (toolbar) toolbar.style.display = parseInt(this.value)===1?'flex':'none';
        });
    });

    var previewOn = false;
    document.getElementById('faq-preview-toggle').addEventListener('click', function(){
        previewOn = !previewOn;
        var ta = document.getElementById('faq-content');
        var pane = document.getElementById('faq-preview-pane');
        if (previewOn) {
            ta.style.display='none'; pane.style.display='block';
            var ct = parseInt(document.querySelector('input[name=content_type]:checked').value||1);
            pane.innerHTML = ct===0?'<pre style="white-space:pre-wrap">'+faqEscHtml(ta.value)+'</pre>':ct===2?ta.value:faqMd(ta.value);
            this.textContent='✏️ Editar';
        } else { ta.style.display='block'; pane.style.display='none'; this.textContent='👁 Preview'; }
    });

    faqInitToolbar('faq-md-toolbar', 'faq-content');
    faqInitTags('faq-tags-container', 'faq-tags-hidden', allTags);
    faqInitUpload('faq-upload-area', 'faq-file-input', 'faq-media-list', articleId);

    function getBody(action) {
        var title   = document.getElementById('faq-title').value.trim();
        var content = document.getElementById('faq-content').value;
        var ct      = document.querySelector('input[name=content_type]:checked').value;
        var catId   = document.getElementById('faq-category').value;
        var tags    = document.getElementById('faq-tags-hidden').value;
        var groups  = [];
        document.querySelectorAll('.faq-groups-list input:checked').forEach(function(cb){ groups.push(cb.value); });

        var body = 'articleid='+encodeURIComponent(articleId)+
                   '&submit_action='+encodeURIComponent(action)+
                   '&title='+encodeURIComponent(title)+
                   '&content='+encodeURIComponent(content)+
                   '&content_type='+encodeURIComponent(ct)+
                   '&categoryid='+encodeURIComponent(catId);
        if (tags) tags.split(',').forEach(function(t){ if(t.trim()) body+='&tags[]='+encodeURIComponent(t.trim()); });
        groups.forEach(function(g){ body+='&groups[]='+encodeURIComponent(g); });
        return body;
    }

    function doAction(action, btn, extraBody) {
        btn.disabled = true;
        fetch('zabbix.php?action=zbx.faq.review.save', {
            method:'POST',
            headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},
            body: getBody(action) + (extraBody||'')
        }).then(function(r){return r.json();}).then(function(d){
            btn.disabled = false;
            if (d.success) {
                showMsg(action==='publish'?'Artigo publicado!':action==='reject'?'Artigo devolvido!':'Edições salvas!', true);
                if (action!=='save') setTimeout(function(){ window.location.href='?action=zbx.faq.review'; }, 1200);
            } else {
                showMsg('Erro: '+(d.error||'Falha.'), false);
            }
        }).catch(function(e){ btn.disabled=false; showMsg('Erro: '+e, false); });
    }

    document.getElementById('faq-btn-save').addEventListener('click', function(){ doAction('save', this); });

    document.getElementById('faq-btn-publish').addEventListener('click', function(){
        if (confirm('Publicar este artigo agora?')) doAction('publish', this);
    });

    var rejectMode = false;
    document.getElementById('faq-btn-reject').addEventListener('click', function(){
        if (!rejectMode) {
            rejectMode = true;
            document.getElementById('faq-reject-section').style.display = 'block';
            this.textContent = '✅ Confirmar Devolução';
            this.classList.remove('faq-btn-danger');
            this.classList.add('faq-btn-warning');
        } else {
            var comment = document.getElementById('faq-review-comment').value.trim();
            if (!comment) { alert('Informe o motivo da devolução.'); return; }
            doAction('reject', this, '&review_comment='+encodeURIComponent(comment));
        }
    });

    function showMsg(text, ok) {
        var div = document.getElementById('faq-messages');
        div.innerHTML = '<div class="faq-msg '+(ok?'faq-msg-ok':'faq-msg-err')+'">'+faqEscHtml(text)+'</div>';
        setTimeout(function(){ div.innerHTML=''; }, 5000);
    }
})();
</script>
