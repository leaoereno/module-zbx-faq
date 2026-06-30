# module-zbx-faq

**Base de Conhecimento FAQ** para Zabbix 7.0 LTS — módulo frontend em PHP com fluxo completo de criação, revisão, publicação e administração de artigos.

Desenvolvido por [Rafael M. A. Leão Ereno](https://github.com/leaoereno) para o NOC Claro Empresas / Embratel.

---

## Funcionalidades

- Artigos em **Markdown**, **HTML** ou **texto puro** com toolbar de edição
- **Fluxo editorial**: Rascunho → Em Revisão → Publicado / Rejeitado → Arquivado
- **Segmentação por grupo**: artigos visíveis apenas para grupos específicos do Zabbix
- **Tags** para categorização e busca
- **Upload de mídias** (imagens, PDFs, documentos) vinculadas ao artigo — com auto-save de rascunho
- **Histórico de revisões** com rastreabilidade completa
- **Notificações HTML por e-mail** via SMTP relay com ícones e cores por evento
- **Hierarquia de categorias** com subcategorias
- Integração nativa com **roles e grupos** do Zabbix 7.0

---

## Regras de Negócio

### Visibilidade de Artigos

| Usuário | Vê |
|---|---|
| User (qualquer grupo) | Artigos publicados do seu grupo |
| Admin (qualquer grupo) | Artigos publicados do seu grupo |
| SuperAdmin | Todos os artigos, incluindo sem grupo |

> Artigos **sem grupo definido** são visíveis apenas para SuperAdmin.

### Submenus e Permissões

| Submenu | Quem acessa |
|---|---|
| **Artigos** | Todos os usuários autenticados |
| **Escrever Artigo** | Todos os usuários autenticados |
| **Revisão** | Admin (type ≥ 2) — apenas artigos do próprio grupo |
| **Administração** | SuperAdmin (type = 3) — acesso total |

### Fluxo de Status

```
Rascunho (0) → Em Revisão (1) → Publicado (2)
                             ↘ Rejeitado (4) → autor corrige → Em Revisão
Publicado (2) → Arquivado (3)  [apenas SuperAdmin]
```

---

## Requisitos

| Componente | Versão |
|---|---|
| Zabbix Frontend | 7.0 LTS |
| PHP | 8.0+ |
| MariaDB / MySQL | 10.5+ |
| curl | com suporte a SMTP |
| SMTP Relay | Postfix ou similar |

---

## Instalação

### 1. Clonar

```bash
cd /usr/share/zabbix/modules
git clone https://github.com/leaoereno/module-zbx-faq.git
```

### 2. Banco de dados

```bash
mysql -u zabbix -p zabbix < module-zbx-faq/install.sql
mysql -u zabbix -p zabbix < module-zbx-faq/role_rule.sql
```

### 3. Diretório de mídias

```bash
mkdir -p /usr/share/zabbix/modules/module-zbx-faq/assets/media
chown apache:apache /usr/share/zabbix/modules/module-zbx-faq/assets/media
chmod 755 /usr/share/zabbix/modules/module-zbx-faq/assets/media
```

### 4. Media type para notificações

```sql
INSERT INTO media_type
    (mediatypeid, type, name, smtp_server, smtp_helo, smtp_email,
     smtp_port, message_format, status, script, exec_path,
     maxsessions, maxattempts, attempt_interval, timeout, description)
VALUES (
    (SELECT t.mx+1 FROM (SELECT MAX(mediatypeid) AS mx FROM media_type) t),
    0, 'FAQ Notifications',
    '192.168.0.150', 'localhost', 'seu-email@gmail.com',
    25, 1, 0, '', '', 1, 3, '10s', '30s',
    'Notificacoes do modulo FAQ'
);
```

Configure o remetente em `actions/FaqPermission.php` (busca por `rafaelereno@gmail.com`) e adicione o media type aos usuários em **Usuários → Mídias**.

### 5. Ativar no Zabbix

**Administração → Geral → Módulos** → Verificar atualizações → Ativar **FAQ**.

---

## Notificações por E-mail

| Evento | Ícone | Cor | Destinatário |
|---|---|---|---|
| Enviado para revisão | 📤 | Azul `#2563eb` | Admins do grupo |
| Publicado | ✅ | Verde `#16a34a` | Autor |
| Rejeitado/devolvido | ❌ | Vermelho `#dc2626` | Autor |
| Editado pelo revisor | ✏️ | Âmbar `#d97706` | Autor |

---

## Notas Técnicas

### `submit_action` vs `action`
O Zabbix usa `$_REQUEST['action']` para roteamento. Nunca envie `action` no body do POST — use `submit_action`.

### `type="button"` obrigatório
Todo `<button>` com `onclick` precisa de `type="button"`. Sem isso, o listener global do Zabbix (`zabbix.php:1340`) intercepta o clique.

### `confirm()` / `alert()` bloqueados
O Firefox bloqueia `confirm()` e `alert()` no contexto do Zabbix. Use mensagens inline com `showMsg()`.

### JS inline obrigatório
O F5 BIG-IP de produção bloqueia `.js` estáticos. Todo JS deve ser inline nas views PHP.

### Grupos preservados na publicação
O `saveTagsAndGroups` usa `hasInput('groups')` para só atualizar grupos se foram explicitamente enviados no POST — evita que a publicação pelo revisor apague os grupos definidos pelo autor.

### IDs customizados
Use `MAX(id)+1` para tabelas `zbx_faq_*`. Nunca use `\DB::reserveIds()` para tabelas customizadas.

---

## Changelog

### v2.2.0 (2026-06-14)

**Correções de segmentação:**
- Artigos sem grupo definido agora são visíveis/revisáveis apenas pelo SuperAdmin
- `FaqArticles` e `FaqReview` corrigidos para filtrar por grupo do usuário logado
- `saveTagsAndGroups` preserva grupos existentes quando não enviados no POST (evita apagar grupos ao publicar)

**Correções de UI/UX:**
- `type="button"` adicionado em todos os botões com `onclick` na view de Administração — corrige cliques não disparados pelo listener global do Zabbix
- Removidos `confirm()` e `alert()` que eram bloqueados silenciosamente pelo Firefox — substituídos por mensagens inline
- Submenus **Revisão** e **Administração** agora respeitam o tipo de usuário (User não vê mais esses submenus)

**Novas funcionalidades:**
- Aba **Tags** na Administração — SuperAdmin pode excluir tags com contagem de uso
- Action `zbx.faq.admin.tag.delete` + `FaqAdminTagDelete`
- Action `archive` no `FaqReviewSave` — SuperAdmin pode arquivar artigos pela tela de Administração
- Upload automático de mídia em artigos novos: salva rascunho automaticamente antes do upload
- Notificações HTML por e-mail via `curl` + SMTP relay com ícones e cores por evento tipo
- `faqInitUploadAutoSave` no `faq.common.js.php` com callback para obter articleId dinamicamente

**Infraestrutura de notificações:**
- Substitui `\CApi::callMethod('task.create')` (incompatível com Zabbix 7.0) por `curl` SMTP direto
- Remetente configurável em `FaqPermission::sendFaqNotification()`
- Logs de erro em `error_log()` para diagnóstico sem interromper o fluxo

### v2.1.0 (2026-06-12)

- Fix crítico: `submit_action` no body dos POSTs
- Fix: `"layout": "layout.json"` explícito nas actions de save
- Fix: `Module.php` com `try/catch` para requests JSON
- Fix: remoção de arquivos v1 conflitantes
- `declare(strict_types = 1)` em todos os actions

### v2.0.0

- Arquitetura completa com 4 submenus
- Fluxo editorial com 5 status
- 7 tabelas customizadas com histórico de revisões
- Segmentação por grupo de usuários
- Upload de mídias com validação MIME
- Editor Markdown com toolbar e preview
