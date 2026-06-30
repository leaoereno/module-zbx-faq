<?php /* Shared CSS — FAQ Module v2.0.0 */ ?>
<style>
/* ================================================================
   FAQ Module — Design System
   Compatível com todos os temas Zabbix 7.0 (dark/light/blue/hight contrast)
   ================================================================ */

/* ── Tokens internos ── */
:root {
  --faq-radius:       6px;
  --faq-radius-sm:    4px;
  --faq-radius-pill:  100px;
  --faq-shadow-card:  0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.04);
  --faq-shadow-hover: 0 4px 12px rgba(0,0,0,.10), 0 2px 4px rgba(0,0,0,.06);
  --faq-shadow-modal: 0 20px 60px rgba(0,0,0,.25);
  --faq-trans:        .18s cubic-bezier(.4,0,.2,1);
  --faq-accent:       var(--color-link, #1a73e8);
  --faq-accent-bg:    rgba(26,115,232,.08);
  --faq-accent-hover: rgba(26,115,232,.14);
  --faq-danger:       #e05252;
  --faq-success:      #2dbe4e;
  --faq-warning:      #e89b1a;
  --faq-border:       var(--color-border, #e0e0e0);
  --faq-bg:           var(--color-bg, #fff);
  --faq-bg-alt:       var(--color-bg-alt, #f7f8fa);
  --faq-text:         var(--color-text-primary, #1a1a2e);
  --faq-muted:        var(--color-text-secondary, #6b7280);
  --faq-sidebar-w:    230px;
  --faq-edit-sb-w:    240px;
}

/* ── Reset ── */
.faq-layout *, .faq-edit-layout *, .faq-view-layout *, .faq-admin-wrap * {
  box-sizing: border-box;
}

/* ── Espaçamento do topo — afasta do header/breadcrumb do Zabbix ── */
.faq-wrap { padding: 18px 24px 32px; }

/* ================================================================
   LAYOUT — Articles (sidebar + main)
   ================================================================ */
.faq-layout {
  display: flex;
  min-height: 65vh;
  font-size: 13px;
  color: var(--faq-text);
  gap: 0;
}

/* ── Sidebar ── */
.faq-sidebar {
  width: var(--faq-sidebar-w);
  flex-shrink: 0;
  border-right: 1px solid var(--faq-border);
  padding: 16px 14px 24px;
  background: var(--faq-bg-alt);
  display: flex;
  flex-direction: column;
  gap: 0;
}
.faq-sidebar-section { margin-bottom: 22px; }
.faq-sidebar-title {
  font-weight: 700;
  font-size: 10px;
  text-transform: uppercase;
  letter-spacing: .08em;
  color: var(--faq-muted);
  margin-bottom: 10px;
  padding-bottom: 6px;
  border-bottom: 1px solid var(--faq-border);
}
.faq-cat-all {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 6px 8px;
  border-radius: var(--faq-radius-sm);
  text-decoration: none;
  color: var(--faq-text);
  font-weight: 600;
  font-size: 12px;
  margin-bottom: 2px;
  transition: background var(--faq-trans), color var(--faq-trans);
}
.faq-cat-all::before { content: '📄'; font-size: 12px; }
.faq-cat-all:hover { background: var(--faq-accent-bg); color: var(--faq-accent); }
.faq-cat-all.active { background: var(--faq-accent-bg); color: var(--faq-accent); }

/* Árvore de categorias */
.faq-cat-tree { list-style: none; margin: 0; padding: 0; }
.faq-cat-tree .faq-cat-tree { padding-left: 14px; border-left: 2px solid var(--faq-border); margin-left: 8px; }
.faq-cat-item > a {
  display: flex;
  align-items: center;
  gap: 5px;
  padding: 5px 8px;
  border-radius: var(--faq-radius-sm);
  text-decoration: none;
  color: var(--faq-text);
  font-size: 12px;
  transition: background var(--faq-trans), color var(--faq-trans);
  cursor: pointer;
}
.faq-cat-item > a:hover { background: var(--faq-accent-bg); color: var(--faq-accent); }
.faq-cat-item.active > a { background: var(--faq-accent-bg); color: var(--faq-accent); font-weight: 700; }
.faq-cat-toggle {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 14px;
  height: 14px;
  font-size: 9px;
  color: var(--faq-muted);
  transition: transform var(--faq-trans);
  flex-shrink: 0;
}
.faq-cat-item.open > a .faq-cat-toggle { transform: rotate(90deg); }
.faq-cat-tree .faq-cat-tree { display: none; }
.faq-cat-item.open > .faq-cat-tree { display: block; }

/* Tag cloud sidebar */
.faq-tagcloud { display: flex; flex-wrap: wrap; gap: 5px; }

/* ── Main content ── */
.faq-main { flex: 1; padding: 18px 24px 28px; min-width: 0; }

.faq-topbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 10px;
  margin-bottom: 18px;
  padding-bottom: 14px;
  border-bottom: 1px solid var(--faq-border);
}
.faq-search-form { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }

/* ── Filter chip ── */
.faq-filter-active {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 5px 10px 5px 12px;
  background: var(--faq-accent-bg);
  border: 1px solid rgba(26,115,232,.2);
  border-radius: var(--faq-radius-pill);
  font-size: 12px;
  color: var(--faq-accent);
  font-weight: 600;
  margin-bottom: 12px;
}
.faq-tag-remove {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  background: rgba(26,115,232,.15);
  color: var(--faq-accent);
  text-decoration: none;
  font-weight: 900;
  font-size: 10px;
  transition: background var(--faq-trans);
}
.faq-tag-remove:hover { background: var(--faq-danger); color: #fff; }

/* ================================================================
   INPUTS
   ================================================================ */
.faq-input {
  padding: 6px 10px;
  border: 1px solid var(--faq-border);
  border-radius: var(--faq-radius-sm);
  background: var(--faq-bg);
  color: var(--faq-text);
  font-size: 13px;
  transition: border-color var(--faq-trans), box-shadow var(--faq-trans);
  outline: none;
}
.faq-input:focus {
  border-color: var(--faq-accent);
  box-shadow: 0 0 0 3px var(--faq-accent-bg);
}
.faq-input-full { width: 100%; }
.faq-search-input { width: 260px; }
.faq-select {
  padding: 6px 28px 6px 10px;
  border: 1px solid var(--faq-border);
  border-radius: var(--faq-radius-sm);
  background: var(--faq-bg);
  color: var(--faq-text);
  font-size: 13px;
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%236b7280'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 10px center;
  transition: border-color var(--faq-trans);
  outline: none;
}
.faq-select:focus { border-color: var(--faq-accent); box-shadow: 0 0 0 3px var(--faq-accent-bg); }
.faq-select-full { width: 100%; }
.faq-textarea {
  width: 100%;
  padding: 8px 10px;
  border: 1px solid var(--faq-border);
  border-radius: var(--faq-radius-sm);
  font-size: 13px;
  font-family: inherit;
  resize: vertical;
  background: var(--faq-bg);
  color: var(--faq-text);
  line-height: 1.5;
  transition: border-color var(--faq-trans), box-shadow var(--faq-trans);
  outline: none;
}
.faq-textarea:focus {
  border-color: var(--faq-accent);
  box-shadow: 0 0 0 3px var(--faq-accent-bg);
}

/* ================================================================
   BUTTONS
   ================================================================ */
.faq-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 5px;
  padding: 6px 14px;
  border-radius: var(--faq-radius-sm);
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  text-decoration: none;
  border: 1px solid transparent;
  white-space: nowrap;
  transition: background var(--faq-trans), color var(--faq-trans), border-color var(--faq-trans), box-shadow var(--faq-trans), transform var(--faq-trans);
  line-height: 1;
  letter-spacing: .01em;
  user-select: none;
}
.faq-btn:active { transform: translateY(1px); }
.faq-btn-sm { padding: 4px 10px; font-size: 11px; }
.faq-btn-full { width: 100%; }

.faq-btn-primary {
  background: var(--faq-accent);
  color: #fff !important;
  border-color: var(--faq-accent);
  box-shadow: 0 1px 3px rgba(26,115,232,.3);
}
.faq-btn-primary:hover {
  background: #1557cc;
  border-color: #1557cc;
  box-shadow: 0 2px 6px rgba(26,115,232,.35);
  color: #fff !important;
}
.faq-btn-outline {
  background: transparent;
  color: var(--faq-text);
  border-color: var(--faq-border);
}
.faq-btn-outline:hover {
  background: var(--faq-bg-alt);
  border-color: #bbb;
}
.faq-btn-success {
  background: var(--faq-success);
  color: #fff !important;
  border-color: var(--faq-success);
  box-shadow: 0 1px 3px rgba(45,190,78,.3);
}
.faq-btn-success:hover { background: #229a3d; border-color: #229a3d; color: #fff !important; }
.faq-btn-warning {
  background: var(--faq-warning);
  color: #fff !important;
  border-color: var(--faq-warning);
}
.faq-btn-warning:hover { background: #c07a10; border-color: #c07a10; color: #fff !important; }
.faq-btn-danger {
  background: transparent;
  color: var(--faq-danger);
  border-color: var(--faq-danger);
}
.faq-btn-danger:hover {
  background: var(--faq-danger);
  color: #fff !important;
}
.faq-btn:disabled {
  opacity: .5;
  cursor: not-allowed;
  transform: none;
}

/* ================================================================
   TAGS
   ================================================================ */
.faq-tag {
  display: inline-flex;
  align-items: center;
  gap: 3px;
  padding: 2px 9px;
  border-radius: var(--faq-radius-pill);
  font-size: 11px;
  font-weight: 500;
  background: var(--faq-bg-alt);
  color: var(--faq-muted);
  text-decoration: none;
  border: 1px solid var(--faq-border);
  transition: background var(--faq-trans), color var(--faq-trans), border-color var(--faq-trans);
}
.faq-tag:hover, .faq-tag.active {
  background: var(--faq-accent-bg);
  color: var(--faq-accent);
  border-color: rgba(26,115,232,.3);
}

/* ================================================================
   ARTICLE CARDS
   ================================================================ */
.faq-article-grid { display: flex; flex-direction: column; gap: 10px; }

.faq-article-card {
  border: 1px solid var(--faq-border);
  border-radius: var(--faq-radius);
  padding: 14px 16px;
  background: var(--faq-bg);
  transition: box-shadow var(--faq-trans), border-color var(--faq-trans), transform var(--faq-trans);
  position: relative;
}
.faq-article-card:hover {
  box-shadow: var(--faq-shadow-hover);
  border-color: rgba(26,115,232,.2);
  transform: translateY(-1px);
}
.faq-card-header { display: flex; align-items: center; gap: 10px; margin-bottom: 6px; flex-wrap: wrap; }
.faq-card-title {
  font-weight: 700;
  font-size: 14px;
  color: var(--faq-accent);
  text-decoration: none;
  flex: 1;
  line-height: 1.3;
  transition: color var(--faq-trans);
}
.faq-card-title:hover { color: #1557cc; text-decoration: underline; text-underline-offset: 2px; }
.faq-card-meta {
  display: flex;
  gap: 14px;
  flex-wrap: wrap;
  font-size: 11px;
  color: var(--faq-muted);
  margin-bottom: 8px;
  align-items: center;
}
.faq-meta-cat {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-weight: 600;
  color: var(--faq-text);
}
.faq-meta-cat::before { content: '📁'; font-size: 11px; }
.faq-card-tags { display: flex; gap: 5px; flex-wrap: wrap; }
.faq-card-actions {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
  margin-top: 10px;
  padding-top: 10px;
  border-top: 1px solid var(--faq-border);
}

/* ================================================================
   STATUS BADGES
   ================================================================ */
.faq-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 3px 9px;
  border-radius: var(--faq-radius-pill);
  font-size: 11px;
  font-weight: 700;
  white-space: nowrap;
  letter-spacing: .01em;
}
.faq-badge::before { font-size: 9px; }
.faq-badge-draft   { background: #f3f4f6; color: #6b7280; border: 1px solid #e5e7eb; }
.faq-badge-draft::before { content: '○'; }
.faq-badge-review  { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
.faq-badge-review::before { content: '◑'; }
.faq-badge-pub     { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
.faq-badge-pub::before { content: '●'; }
.faq-badge-archive { background: #f3f4f6; color: #4b5563; border: 1px solid #d1d5db; }
.faq-badge-archive::before { content: '◻'; }
.faq-badge-reject  { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
.faq-badge-reject::before { content: '✕'; font-size: 8px; }

/* ================================================================
   PAGINATION
   ================================================================ */
.faq-pagination { display: flex; align-items: center; gap: 4px; margin-top: 20px; flex-wrap: wrap; }
.faq-page-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 32px;
  height: 32px;
  padding: 0 6px;
  border: 1px solid var(--faq-border);
  border-radius: var(--faq-radius-sm);
  text-decoration: none;
  font-size: 12px;
  font-weight: 600;
  color: var(--faq-text);
  background: var(--faq-bg);
  transition: background var(--faq-trans), border-color var(--faq-trans), color var(--faq-trans);
}
.faq-page-btn:hover { background: var(--faq-accent-bg); border-color: rgba(26,115,232,.3); color: var(--faq-accent); }
.faq-page-btn.active {
  background: var(--faq-accent);
  color: #fff;
  border-color: var(--faq-accent);
  box-shadow: 0 1px 3px rgba(26,115,232,.3);
}
.faq-page-info { font-size: 12px; color: var(--faq-muted); margin-left: 8px; }

/* ================================================================
   EMPTY STATE
   ================================================================ */
.faq-empty {
  text-align: center;
  padding: 64px 24px;
  color: var(--faq-muted);
}
.faq-empty-icon {
  font-size: 48px;
  margin-bottom: 16px;
  opacity: .6;
  display: block;
}
.faq-empty p {
  font-size: 14px;
  margin-bottom: 20px;
  color: var(--faq-muted);
}

/* ================================================================
   MESSAGES / TOASTS
   ================================================================ */
.faq-msg {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 14px;
  border-radius: var(--faq-radius-sm);
  margin-bottom: 12px;
  font-size: 13px;
  font-weight: 600;
  animation: faqFadeIn .2s ease;
}
@keyframes faqFadeIn { from { opacity:0; transform:translateY(-4px); } to { opacity:1; transform:none; } }
.faq-msg::before { font-size: 14px; flex-shrink: 0; }
.faq-msg-ok  { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
.faq-msg-ok::before { content: '✓'; }
.faq-msg-err { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
.faq-msg-err::before { content: '✕'; }

/* ================================================================
   EDITOR LAYOUT
   ================================================================ */
.faq-edit-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20px;
  padding-bottom: 16px;
  border-bottom: 1px solid var(--faq-border);
  flex-wrap: wrap;
  gap: 10px;
}
.faq-edit-title { font-size: 18px; font-weight: 700; color: var(--faq-text); margin: 0; }

.faq-edit-layout { display: flex; gap: 24px; align-items: flex-start; }
.faq-edit-main { flex: 1; min-width: 0; }
.faq-edit-sidebar {
  width: var(--faq-edit-sb-w);
  flex-shrink: 0;
  background: var(--faq-bg-alt);
  border: 1px solid var(--faq-border);
  border-radius: var(--faq-radius);
  padding: 14px;
  position: sticky;
  top: 16px;
}

.faq-field { margin-bottom: 18px; }
.faq-label {
  display: block;
  font-size: 12px;
  font-weight: 700;
  color: var(--faq-text);
  margin-bottom: 6px;
  letter-spacing: .01em;
}
.faq-required { color: var(--faq-danger); margin-left: 2px; }
.faq-field-hint { font-size: 11px; color: var(--faq-muted); margin-top: 4px; line-height: 1.4; }

/* Sidebar sections */
.faq-sb-section {
  margin-bottom: 16px;
  padding-bottom: 16px;
  border-bottom: 1px solid var(--faq-border);
}
.faq-sb-section:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
.faq-sb-title {
  font-weight: 700;
  font-size: 10px;
  text-transform: uppercase;
  letter-spacing: .08em;
  color: var(--faq-muted);
  margin-bottom: 8px;
}
.faq-sb-note { font-size: 11px; color: var(--faq-muted); margin-bottom: 8px; line-height: 1.4; }

.faq-groups-list {
  display: flex;
  flex-direction: column;
  gap: 1px;
  max-height: 220px;
  overflow-y: auto;
  border: 1px solid var(--faq-border);
  border-radius: var(--faq-radius-sm);
  background: var(--faq-bg);
}
.faq-group-check {
  display: flex;
  align-items: center;
  gap: 7px;
  font-size: 12px;
  cursor: pointer;
  padding: 5px 8px;
  border-radius: 0;
  transition: background var(--faq-trans);
}
.faq-group-check:hover { background: var(--faq-accent-bg); }
.faq-group-check input[type=checkbox] { cursor: pointer; accent-color: var(--faq-accent); }

/* ================================================================
   FORMAT RADIO GROUP
   ================================================================ */
.faq-radio-group { display: flex; gap: 6px; flex-wrap: wrap; }
.faq-radio {
  display: flex;
  align-items: center;
  gap: 5px;
  padding: 5px 12px;
  border: 1px solid var(--faq-border);
  border-radius: var(--faq-radius-sm);
  cursor: pointer;
  font-size: 12px;
  font-weight: 600;
  color: var(--faq-muted);
  background: var(--faq-bg);
  transition: border-color var(--faq-trans), color var(--faq-trans), background var(--faq-trans);
  user-select: none;
}
.faq-radio input { display: none; }
.faq-radio:hover { border-color: rgba(26,115,232,.4); color: var(--faq-accent); }
.faq-radio.selected {
  border-color: var(--faq-accent);
  color: var(--faq-accent);
  background: var(--faq-accent-bg);
}

/* ================================================================
   MARKDOWN EDITOR
   ================================================================ */
.faq-editor-wrap {
  border: 1px solid var(--faq-border);
  border-radius: var(--faq-radius-sm);
  overflow: hidden;
  transition: border-color var(--faq-trans), box-shadow var(--faq-trans);
}
.faq-editor-wrap:focus-within {
  border-color: var(--faq-accent);
  box-shadow: 0 0 0 3px var(--faq-accent-bg);
}
.faq-md-toolbar {
  display: flex;
  gap: 2px;
  padding: 6px 8px;
  background: var(--faq-bg-alt);
  border-bottom: 1px solid var(--faq-border);
  flex-wrap: wrap;
  align-items: center;
}
.faq-md-sep { width: 1px; height: 18px; background: var(--faq-border); margin: 0 3px; flex-shrink: 0; }
.faq-md-tool {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 28px;
  height: 26px;
  padding: 0 5px;
  border: 1px solid transparent;
  border-radius: 3px;
  background: none;
  cursor: pointer;
  font-size: 12px;
  color: var(--faq-text);
  transition: background var(--faq-trans), border-color var(--faq-trans);
  font-weight: 600;
}
.faq-md-tool:hover {
  background: var(--faq-accent-hover);
  border-color: rgba(26,115,232,.2);
  color: var(--faq-accent);
}
.faq-code-editor {
  width: 100%;
  min-height: 340px;
  padding: 12px;
  border: none;
  font-family: 'Consolas', 'Monaco', 'Fira Mono', monospace;
  font-size: 13px;
  line-height: 1.65;
  resize: vertical;
  background: var(--faq-bg);
  color: var(--faq-text);
  outline: none;
  display: block;
}
.faq-preview-pane {
  padding: 16px;
  font-size: 14px;
  line-height: 1.7;
  background: var(--faq-bg);
  min-height: 340px;
  border-top: 1px solid var(--faq-border);
}

/* ================================================================
   TAGS INPUT
   ================================================================ */
.faq-tags-input {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 5px;
  padding: 6px 8px;
  border: 1px solid var(--faq-border);
  border-radius: var(--faq-radius-sm);
  background: var(--faq-bg);
  cursor: text;
  min-height: 38px;
  transition: border-color var(--faq-trans), box-shadow var(--faq-trans);
}
.faq-tags-input:focus-within {
  border-color: var(--faq-accent);
  box-shadow: 0 0 0 3px var(--faq-accent-bg);
}
.faq-tag-chip {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 2px 4px 2px 9px;
  background: var(--faq-accent-bg);
  color: var(--faq-accent);
  border: 1px solid rgba(26,115,232,.2);
  border-radius: var(--faq-radius-pill);
  font-size: 12px;
  font-weight: 600;
}
.faq-tag-chip-del {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  background: rgba(26,115,232,.15);
  border: none;
  cursor: pointer;
  font-size: 10px;
  color: var(--faq-accent);
  padding: 0;
  transition: background var(--faq-trans), color var(--faq-trans);
}
.faq-tag-chip-del:hover { background: var(--faq-danger); color: #fff; }
.faq-tag-text-input {
  border: none;
  outline: none;
  font-size: 12px;
  flex: 1;
  min-width: 80px;
  background: transparent;
  color: var(--faq-text);
}
.faq-tag-suggestions {
  position: absolute;
  z-index: 200;
  background: var(--faq-bg);
  border: 1px solid var(--faq-border);
  border-radius: var(--faq-radius-sm);
  padding: 4px 0;
  box-shadow: var(--faq-shadow-hover);
  min-width: 180px;
  max-height: 200px;
  overflow-y: auto;
}
.faq-tag-suggestion {
  padding: 6px 12px;
  font-size: 12px;
  cursor: pointer;
  transition: background var(--faq-trans);
}
.faq-tag-suggestion:hover { background: var(--faq-accent-bg); color: var(--faq-accent); }

/* ================================================================
   FILE UPLOAD
   ================================================================ */
.faq-upload-area {
  border: 2px dashed var(--faq-border);
  border-radius: var(--faq-radius);
  padding: 20px;
  text-align: center;
  margin-bottom: 10px;
  transition: border-color var(--faq-trans), background var(--faq-trans);
  background: var(--faq-bg-alt);
  cursor: pointer;
}
.faq-upload-area:hover { border-color: rgba(26,115,232,.4); background: var(--faq-accent-bg); }
.faq-upload-area.drag-over {
  border-color: var(--faq-accent);
  background: var(--faq-accent-bg);
}
.faq-upload-area input[type=file] { display: none; }
.faq-upload-label {
  cursor: pointer;
  font-size: 13px;
  color: var(--faq-muted);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
}
.faq-upload-label-icon { font-size: 28px; opacity: .5; }
.faq-media-list { display: flex; flex-direction: column; gap: 6px; }
.faq-media-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 10px;
  border: 1px solid var(--faq-border);
  border-radius: var(--faq-radius-sm);
  background: var(--faq-bg);
  transition: box-shadow var(--faq-trans);
}
.faq-media-item:hover { box-shadow: var(--faq-shadow-card); }
.faq-media-thumb { width: 40px; height: 40px; object-fit: cover; border-radius: var(--faq-radius-sm); }
.faq-media-icon { font-size: 24px; width: 40px; text-align: center; }
.faq-media-name { flex: 1; font-size: 12px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

/* ================================================================
   ARTICLE VIEW
   ================================================================ */
.faq-view-layout { padding: 4px 0; }
.faq-breadcrumb {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 2px;
  font-size: 12px;
  color: var(--faq-muted);
  margin-bottom: 18px;
}
.faq-breadcrumb a {
  color: var(--faq-accent);
  text-decoration: none;
  padding: 2px 4px;
  border-radius: 3px;
  transition: background var(--faq-trans);
}
.faq-breadcrumb a:hover { background: var(--faq-accent-bg); }
.faq-bc-sep { color: var(--faq-border); margin: 0 2px; }

.faq-view-container { display: flex; gap: 24px; align-items: flex-start; }
.faq-article-body {
  flex: 1;
  min-width: 0;
  background: var(--faq-bg);
  border: 1px solid var(--faq-border);
  border-radius: var(--faq-radius);
  padding: 28px 32px;
  box-shadow: var(--faq-shadow-card);
}
.faq-view-sidebar {
  width: 210px;
  flex-shrink: 0;
  position: sticky;
  top: 16px;
}

.faq-art-header { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 10px; flex-wrap: wrap; }
.faq-art-title { font-size: 22px; font-weight: 800; margin: 0; flex: 1; line-height: 1.3; color: var(--faq-text); letter-spacing: -.02em; }
.faq-art-meta {
  display: flex;
  gap: 16px;
  flex-wrap: wrap;
  font-size: 12px;
  color: var(--faq-muted);
  margin-bottom: 24px;
  padding-bottom: 16px;
  border-bottom: 2px solid var(--faq-border);
  align-items: center;
}
.faq-art-meta strong { color: var(--faq-text); }

/* Content rendering */
.faq-content-wrapper { line-height: 1.75; font-size: 14px; color: var(--faq-text); }
.faq-content-plain {
  white-space: pre-wrap;
  font-family: 'Consolas', monospace;
  font-size: 13px;
  background: var(--faq-bg-alt);
  padding: 16px;
  border-radius: var(--faq-radius-sm);
  border: 1px solid var(--faq-border);
}
.faq-content-html a, .faq-content-md a { color: var(--faq-accent); }
.faq-content-html code, .faq-content-md code {
  background: var(--faq-bg-alt);
  padding: 2px 6px;
  border-radius: 3px;
  font-size: 12px;
  font-family: 'Consolas', monospace;
  border: 1px solid var(--faq-border);
}
.faq-content-html pre, .faq-content-md pre {
  background: #1e1e2e;
  color: #cdd6f4;
  padding: 16px;
  border-radius: var(--faq-radius-sm);
  overflow-x: auto;
  font-size: 12.5px;
  line-height: 1.6;
  margin: 1em 0;
}
.faq-content-html pre code, .faq-content-md pre code {
  background: none;
  border: none;
  padding: 0;
  color: inherit;
}
.faq-content-html table, .faq-content-md table {
  border-collapse: collapse;
  width: 100%;
  margin: 1.2em 0;
  font-size: 13px;
}
.faq-content-html th, .faq-content-html td,
.faq-content-md th, .faq-content-md td {
  border: 1px solid var(--faq-border);
  padding: 8px 12px;
  text-align: left;
}
.faq-content-html th, .faq-content-md th {
  background: var(--faq-bg-alt);
  font-weight: 700;
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: .04em;
}
.faq-content-html tr:nth-child(even) td, .faq-content-md tr:nth-child(even) td {
  background: var(--faq-bg-alt);
}
.faq-content-md blockquote {
  border-left: 3px solid var(--faq-accent);
  margin: 1em 0;
  padding: 10px 16px;
  background: var(--faq-accent-bg);
  border-radius: 0 var(--faq-radius-sm) var(--faq-radius-sm) 0;
  color: var(--faq-text);
}
.faq-content-md h1 { font-size: 1.6em; margin-top: 1.4em; }
.faq-content-md h2 { font-size: 1.3em; margin-top: 1.3em; border-bottom: 1px solid var(--faq-border); padding-bottom: .3em; }
.faq-content-md h3 { font-size: 1.1em; margin-top: 1.2em; }

.faq-art-tags {
  display: flex;
  align-items: center;
  gap: 6px;
  flex-wrap: wrap;
  margin-top: 24px;
  padding-top: 16px;
  border-top: 1px solid var(--faq-border);
}
.faq-tags-label { font-size: 11px; font-weight: 700; color: var(--faq-muted); text-transform: uppercase; letter-spacing: .06em; }

/* Attachments */
.faq-attachments {
  margin-top: 28px;
  padding: 16px;
  border: 1px solid var(--faq-border);
  border-radius: var(--faq-radius-sm);
  background: var(--faq-bg-alt);
}
.faq-attach-title {
  font-weight: 700;
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: .07em;
  color: var(--faq-muted);
  margin-bottom: 12px;
}
.faq-attach-grid { display: flex; flex-wrap: wrap; gap: 8px; }
.faq-attach-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 12px;
  background: var(--faq-bg);
  border: 1px solid var(--faq-border);
  border-radius: var(--faq-radius-sm);
  max-width: 290px;
  transition: box-shadow var(--faq-trans);
}
.faq-attach-item:hover { box-shadow: var(--faq-shadow-card); }
.faq-attach-thumb { width: 48px; height: 48px; object-fit: cover; border-radius: var(--faq-radius-sm); cursor: zoom-in; }
.faq-attach-icon { font-size: 26px; width: 48px; text-align: center; }
.faq-attach-info { flex: 1; min-width: 0; }
.faq-attach-name {
  font-size: 12px;
  color: var(--faq-accent);
  text-decoration: none;
  display: block;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  font-weight: 600;
}
.faq-attach-name:hover { text-decoration: underline; }
.faq-attach-size { font-size: 11px; color: var(--faq-muted); }

/* View sidebar */
.faq-vs-section {
  background: var(--faq-bg);
  border: 1px solid var(--faq-border);
  border-radius: var(--faq-radius-sm);
  padding: 12px;
  margin-bottom: 10px;
}
.faq-vs-title {
  font-weight: 700;
  font-size: 10px;
  text-transform: uppercase;
  letter-spacing: .08em;
  color: var(--faq-muted);
  margin-bottom: 10px;
  padding-bottom: 6px;
  border-bottom: 1px solid var(--faq-border);
}
.faq-vs-actions { display: flex; flex-direction: column; gap: 6px; }
.faq-vs-group {
  font-size: 12px;
  padding: 4px 0;
  border-bottom: 1px solid var(--faq-border);
  color: var(--faq-text);
  display: flex;
  align-items: center;
  gap: 5px;
}
.faq-vs-group::before { content: '👥'; font-size: 11px; }
.faq-vs-group:last-child { border-bottom: none; }
.faq-vs-note { font-size: 12px; color: var(--faq-muted); }

/* Revision history */
.faq-rev-item {
  padding: 6px 0;
  border-bottom: 1px solid var(--faq-border);
}
.faq-rev-item:last-child { border-bottom: none; }
.faq-rev-who { font-weight: 700; font-size: 11px; color: var(--faq-text); display: block; }
.faq-rev-date { color: var(--faq-muted); font-size: 11px; display: block; }
.faq-rev-note { color: var(--faq-muted); font-size: 11px; font-style: italic; display: block; margin-top: 2px; }

/* ================================================================
   LIGHTBOX
   ================================================================ */
#faq-lightbox {
  position: fixed;
  inset: 0;
  z-index: 9999;
  display: none;
  align-items: center;
  justify-content: center;
}
#faq-lb-backdrop {
  position: absolute;
  inset: 0;
  background: rgba(0,0,0,.85);
  backdrop-filter: blur(4px);
}
#faq-lb-content {
  position: relative;
  z-index: 1;
  max-width: 92vw;
  max-height: 92vh;
  text-align: center;
  animation: faqLbIn .2s ease;
}
@keyframes faqLbIn { from { opacity:0; transform:scale(.95); } to { opacity:1; transform:scale(1); } }
#faq-lb-img { max-width: 100%; max-height: 82vh; border-radius: var(--faq-radius); box-shadow: var(--faq-shadow-modal); }
#faq-lb-close {
  position: absolute;
  top: -16px;
  right: -16px;
  background: var(--faq-danger);
  color: #fff;
  border: none;
  border-radius: 50%;
  width: 32px;
  height: 32px;
  font-size: 15px;
  cursor: pointer;
  box-shadow: 0 2px 8px rgba(0,0,0,.3);
  transition: background var(--faq-trans), transform var(--faq-trans);
}
#faq-lb-close:hover { background: #c0392b; transform: scale(1.1); }
#faq-lb-caption { color: rgba(255,255,255,.8); font-size: 13px; margin-top: 10px; }

/* ================================================================
   ADMIN TABS
   ================================================================ */
.faq-tabs {
  display: flex;
  gap: 0;
  border-bottom: 2px solid var(--faq-border);
  margin-bottom: 20px;
}
.faq-tab {
  padding: 9px 18px;
  font-size: 13px;
  font-weight: 600;
  text-decoration: none;
  color: var(--faq-muted);
  border-bottom: 2px solid transparent;
  margin-bottom: -2px;
  transition: color var(--faq-trans), border-color var(--faq-trans);
  display: flex;
  align-items: center;
  gap: 5px;
}
.faq-tab:hover { color: var(--faq-text); }
.faq-tab.active { color: var(--faq-accent); border-bottom-color: var(--faq-accent); }

/* ================================================================
   TABLE
   ================================================================ */
.faq-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.faq-table th {
  text-align: left;
  padding: 9px 12px;
  border-bottom: 2px solid var(--faq-border);
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .07em;
  color: var(--faq-muted);
  background: var(--faq-bg-alt);
}
.faq-table th:first-child { border-radius: var(--faq-radius-sm) 0 0 0; }
.faq-table th:last-child  { border-radius: 0 var(--faq-radius-sm) 0 0; }
.faq-table td { padding: 9px 12px; border-bottom: 1px solid var(--faq-border); vertical-align: middle; }
.faq-table tr:last-child td { border-bottom: none; }
.faq-table tr:hover td { background: var(--faq-accent-bg); }

/* ================================================================
   REJECT COMMENT BANNER
   ================================================================ */
.faq-reject-comment {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  background: #fffbeb;
  border: 1px solid #fde68a;
  border-left: 4px solid var(--faq-warning);
  border-radius: var(--faq-radius-sm);
  padding: 12px 14px;
  font-size: 13px;
  margin-bottom: 16px;
  color: #92400e;
}
.faq-reject-comment::before { content: '⚠️'; flex-shrink: 0; }
.faq-reject-comment strong { font-weight: 700; }

/* ================================================================
   REVIEW QUEUE — pending counter badge
   ================================================================ */
.faq-pending-count {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 20px;
  height: 20px;
  padding: 0 5px;
  border-radius: var(--faq-radius-pill);
  background: var(--faq-warning);
  color: #fff;
  font-size: 11px;
  font-weight: 800;
}

/* ================================================================
   MODAL
   ================================================================ */
.faq-modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,.5);
  backdrop-filter: blur(2px);
  z-index: 8000;
  display: flex;
  align-items: center;
  justify-content: center;
  animation: faqFadeIn .15s ease;
}
.faq-modal {
  background: var(--faq-bg);
  border-radius: var(--faq-radius);
  padding: 24px;
  width: 480px;
  max-width: 92vw;
  box-shadow: var(--faq-shadow-modal);
  animation: faqLbIn .2s ease;
}
.faq-modal-title { font-size: 16px; font-weight: 700; margin: 0 0 16px; color: var(--faq-text); }
.faq-modal-actions { display: flex; gap: 8px; margin-top: 16px; justify-content: flex-end; }
</style>
