<?php /* Shared JS — incluído nas views que precisam de editor/tags/upload */ ?>
<script>
(function(window){
'use strict';

/* ---- Markdown renderer leve ---- */
window.faqMd = function(md) {
    var h = md.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    h = h.replace(/```([\s\S]*?)```/g,function(_,c){return '<pre><code>'+c+'</code></pre>';});
    h = h.replace(/`([^`]+)`/g,'<code>$1</code>');
    h = h.replace(/^###### (.+)$/gm,'<h6>$1</h6>');
    h = h.replace(/^##### (.+)$/gm,'<h5>$1</h5>');
    h = h.replace(/^#### (.+)$/gm,'<h4>$1</h4>');
    h = h.replace(/^### (.+)$/gm,'<h3>$1</h3>');
    h = h.replace(/^## (.+)$/gm,'<h2>$1</h2>');
    h = h.replace(/^# (.+)$/gm,'<h1>$1</h1>');
    h = h.replace(/\*\*\*(.+?)\*\*\*/g,'<strong><em>$1</em></strong>');
    h = h.replace(/\*\*(.+?)\*\*/g,'<strong>$1</strong>');
    h = h.replace(/\*(.+?)\*/g,'<em>$1</em>');
    h = h.replace(/!\[([^\]]*)\]\(([^)]+)\)/g,'<img src="$2" alt="$1" style="max-width:100%">');
    h = h.replace(/\[([^\]]+)\]\(([^)]+)\)/g,'<a href="$2">$1</a>');
    h = h.replace(/^---+$/gm,'<hr>');
    h = h.replace(/^&gt; (.+)$/gm,'<blockquote>$1</blockquote>');
    h = h.replace(/^[\*\-] (.+)$/gm,'<li>$1</li>');
    h = h.replace(/\n{2,}/g,'</p><p>');
    h = '<p>'+h+'</p>';
    h = h.replace(/<p>(<h[1-6]>|<pre>|<blockquote>|<hr>)/g,'$1');
    h = h.replace(/(<\/h[1-6]>|<\/pre>|<\/blockquote>|<hr>)<\/p>/g,'$1');
    h = h.replace(/<p>\s*<\/p>/g,'');
    return h;
};

window.faqEscHtml = function(s){ return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); };
window.faqEscAttr = function(s){ return faqEscHtml(s).replace(/"/g,'&quot;'); };

/* ---- Toolbar Markdown ---- */
window.faqInitToolbar = function(toolbarId, textareaId) {
    var toolbar = document.getElementById(toolbarId);
    var ta      = document.getElementById(textareaId);
    if (!toolbar || !ta) return;
    toolbar.querySelectorAll('.faq-md-tool').forEach(function(btn){
        btn.addEventListener('click', function(){
            var start = ta.selectionStart, end = ta.selectionEnd;
            var sel   = ta.value.substring(start, end);
            var pre   = ta.value.substring(0, start);
            var post  = ta.value.substring(end);
            var snips = {
                bold:['**',sel||'texto','**'], italic:['*',sel||'texto','*'],
                heading:['# ',sel||'Título',''], link:['[',sel||'texto','](url)'],
                code:['`',sel||'código','`'], codeblock:['```\n',sel||'código\n','```'],
                ul:['- ',sel||'item',''], blockquote:['> ',sel||'citação',''],
                hr:['\n---\n','',''], table:['| Col1 | Col2 |\n|------|------|\n| A    | B    |','','']
            };
            var s = snips[btn.dataset.action]; if(!s) return;
            var ins = s[0]+s[1]+s[2];
            ta.value = pre+ins+post;
            ta.focus();
            ta.selectionStart = ta.selectionEnd = start+ins.length;
        });
    });
};

/* ---- Tags input ---- */
window.faqInitTags = function(containerId, hiddenId, allTags) {
    var container  = document.getElementById(containerId);
    var hidden     = document.getElementById(hiddenId);
    var tagInput   = container ? container.querySelector('.faq-tag-text-input') : null;
    var suggBox    = document.getElementById(containerId + '-suggestions');
    if (!container || !tagInput) return;

    var currentTags = hidden.value ? hidden.value.split(',').filter(Boolean) : [];

    function refresh() { hidden.value = currentTags.join(','); }

    function addTag(name) {
        name = name.trim();
        if (!name || currentTags.indexOf(name) !== -1) return;
        currentTags.push(name);
        var chip = document.createElement('span');
        chip.className = 'faq-tag-chip';
        chip.innerHTML = faqEscHtml(name) + '<button type="button" class="faq-tag-chip-del" data-tag="'+faqEscAttr(name)+'">✕</button>';
        chip.querySelector('.faq-tag-chip-del').addEventListener('click', removeTag);
        container.insertBefore(chip, tagInput);
        refresh();
    }

    function removeTag() {
        var t = this.getAttribute('data-tag');
        currentTags = currentTags.filter(function(x){return x!==t;});
        this.closest('.faq-tag-chip').remove();
        refresh();
    }

    container.querySelectorAll('.faq-tag-chip-del').forEach(function(b){ b.addEventListener('click', removeTag); });

    tagInput.addEventListener('keydown', function(e){
        if (e.key==='Enter'||e.key===',') { e.preventDefault(); addTag(this.value); this.value=''; if(suggBox) suggBox.style.display='none'; }
        else if (e.key==='Backspace'&&this.value===''&&currentTags.length) {
            container.querySelectorAll('.faq-tag-chip').forEach(function(c,i,a){ if(i===a.length-1) c.querySelector('.faq-tag-chip-del').click(); });
        }
    });

    if (suggBox && allTags) {
        tagInput.addEventListener('input', function(){
            var q = this.value.toLowerCase().trim();
            if (!q) { suggBox.style.display='none'; return; }
            var matches = allTags.filter(function(t){ return t.toLowerCase().indexOf(q)===0 && currentTags.indexOf(t)===-1; });
            if (!matches.length) { suggBox.style.display='none'; return; }
            suggBox.innerHTML = matches.slice(0,8).map(function(t){ return '<div class="faq-tag-suggestion">'+faqEscHtml(t)+'</div>'; }).join('');
            suggBox.style.display='block';
            suggBox.querySelectorAll('.faq-tag-suggestion').forEach(function(d){
                d.addEventListener('click',function(){ addTag(this.textContent); tagInput.value=''; suggBox.style.display='none'; });
            });
        });
        document.addEventListener('click', function(e){ if(!container.contains(e.target)&&!suggBox.contains(e.target)) suggBox.style.display='none'; });
    }
};

/* ---- Upload de mídias ---- */
window.faqInitUploadAutoSave = function(areaId, inputId, listId, articleId, getIdCallback) {
    var area  = document.getElementById(areaId);
    var input = document.getElementById(inputId);
    var list  = document.getElementById(listId);
    if (!area || !input || !list) return;

    area.addEventListener('dragover',function(e){e.preventDefault();this.classList.add('drag-over');});
    area.addEventListener('dragleave',function(){this.classList.remove('drag-over');});
    area.addEventListener('drop',function(e){e.preventDefault();this.classList.remove('drag-over');uploadFiles(e.dataTransfer.files);});
    input.addEventListener('change',function(){uploadFiles(this.files);this.value='';});

    function uploadFiles(files) {
        getIdCallback(function(resolvedId) {
            Array.from(files).forEach(function(file){
                var form = new FormData();
                form.append('articleid', resolvedId);
                form.append('media_file', file);
                fetch('zabbix.php?action=zbx.faq.media.upload', {method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'}, body:form})
            .then(function(r){return r.json();})
            .then(function(d){
                if (!d.success) { if(window.showMsg) showMsg('Erro no upload: '+(d.error||'Falha.'),false); else console.error('Upload erro:',d.error); return; }
                var isImg = d.mime_type && d.mime_type.indexOf('image/')===0;
                var item  = document.createElement('div');
                item.className = 'faq-media-item';
                item.id = 'faq-media-'+d.mediaid;
                item.innerHTML = (isImg ? '<img src="?action=zbx.faq.media.serve&mediaid='+d.mediaid+'" class="faq-media-thumb" alt="'+faqEscAttr(d.original_name)+'">' : '<div class="faq-media-icon">📎</div>') +
                    '<span class="faq-media-name">'+faqEscHtml(d.original_name)+'</span>' +
                    '<button type="button" class="faq-btn faq-btn-sm faq-btn-danger" onclick="faqDeleteMedia('+d.mediaid+',this)">✕</button>';
                list.appendChild(item);
            })
            .catch(function(e){ if(window.showMsg) showMsg('Erro de comunicação no upload.',false); else console.error('Upload erro:',e); });
            }); // forEach
        }); // getIdCallback
    }
};

window.faqDeleteMedia = function(mediaId, btn) {
    if (!confirm('Remover este anexo?')) return;
    btn.disabled = true;
    fetch('zabbix.php?action=zbx.faq.media.delete',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},'body':'mediaid='+mediaId})
    .then(function(r){return r.json();})
    .then(function(d){ if(d.success) document.getElementById('faq-media-'+mediaId).remove(); else{ btn.disabled=false; if(window.showMsg) showMsg('Erro ao remover arquivo.',false); else console.error('Delete erro:',d); } });
};

/* ---- Lightbox ---- */
window.faqLightbox = function(src, caption) {
    var lb = document.getElementById('faq-lightbox');
    if (!lb) return;
    document.getElementById('faq-lb-img').src = src;
    document.getElementById('faq-lb-caption').textContent = caption;
    lb.style.display = 'flex';
};
document.addEventListener('DOMContentLoaded', function(){
    var lbClose = document.getElementById('faq-lb-close');
    var lbBack  = document.getElementById('faq-lb-backdrop');
    if(lbClose) lbClose.addEventListener('click',function(){document.getElementById('faq-lightbox').style.display='none';});
    if(lbBack)  lbBack.addEventListener('click',function(){document.getElementById('faq-lightbox').style.display='none';});
});

})(window);
window.faqInitUpload = function(a,b,c,id,s){ window.faqInitUploadAutoSave(a,b,c,id,function(cb){cb(id);}); };
</script>
