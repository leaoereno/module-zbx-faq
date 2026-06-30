<?php declare(strict_types = 1);

namespace Modules\ZbxFaq\Actions;

trait FaqPermission {

    protected function getCurrentUserId(): int {
        return (int)\CWebUser::$data['userid'];
    }

    protected function getCurrentUserType(): int {
        $row = DBfetch(DBselect(
            'SELECT r.type FROM users u JOIN role r ON r.roleid=u.roleid WHERE u.userid=' . $this->getCurrentUserId()
        ));
        return $row ? (int)$row['type'] : 0;
    }

    protected function isSuperAdmin(): bool {
        return $this->getCurrentUserType() === USER_TYPE_SUPER_ADMIN;
    }

    protected function isAdminOrAbove(): bool {
        return $this->getCurrentUserType() >= USER_TYPE_ZABBIX_ADMIN;
    }

    protected function getCurrentUserGroups(): array {
        $result = DBselect('SELECT usrgrpid FROM users_groups WHERE userid=' . $this->getCurrentUserId());
        $groups = [];
        while ($row = DBfetch($result)) { $groups[] = (int)$row['usrgrpid']; }
        return $groups;
    }

    protected function canViewArticle(int $articleId): bool {
        if ($this->isSuperAdmin()) return true;
        $cnt = DBfetch(DBselect('SELECT COUNT(*) AS cnt FROM zbx_faq_article_group WHERE articleid=' . $articleId));
        if ((int)$cnt['cnt'] === 0) return true;
        $row = DBfetch(DBselect(
            'SELECT ag.article_groupid FROM zbx_faq_article_group ag' .
            ' JOIN users_groups ug ON ug.usrgrpid=ag.usrgrpid' .
            ' WHERE ag.articleid=' . $articleId . ' AND ug.userid=' . $this->getCurrentUserId()
        ));
        return (bool)$row;
    }

    protected function canReviewArticle(int $articleId): bool {
        if ($this->isSuperAdmin()) return true;
        if (!$this->isAdminOrAbove()) return false;
        $cnt = DBfetch(DBselect('SELECT COUNT(*) AS cnt FROM zbx_faq_article_group WHERE articleid=' . $articleId));
        if ((int)$cnt['cnt'] === 0) return true;
        $userGroups = $this->getCurrentUserGroups();
        if (!$userGroups) return false;
        $row = DBfetch(DBselect(
            'SELECT ag.article_groupid FROM zbx_faq_article_group ag' .
            ' WHERE ag.articleid=' . $articleId . ' AND ag.usrgrpid IN (' . implode(',', $userGroups) . ')'
        ));
        return (bool)$row;
    }

    protected function nextId(string $table, string $field): int {
        $row = DBfetch(DBselect('SELECT MAX(' . $field . ') AS maxid FROM ' . $table));
        return $row ? (int)$row['maxid'] + 1 : 1;
    }

    // ── Notificações ──────────────────────────────────────────────────────

    protected function sendFaqNotification(int $toUserId, string $subject, string $message, string $icon = '📋', string $color = '#c44b4b'): void {
        try {
            $mt = DBfetch(DBselect(
                "SELECT mediatypeid FROM media_type WHERE name='FAQ Notifications' AND status=0"
            ));
            if (!$mt) return;

            $mediatypeId = (int)$mt['mediatypeid'];
            $media = DBfetch(DBselect(
                'SELECT sendto FROM media WHERE userid=' . $toUserId .
                ' AND mediatypeid=' . $mediatypeId . ' AND active=0'
            ));
            if (!$media) return;

            $htmlBody = $this->buildEmailHtml($subject, $message, $icon, $color);

            $uid       = uniqid('faqm_', true);
            $emlFile   = sys_get_temp_dir() . '/' . $uid . '.eml';
            $subjectB64 = '=?UTF-8?B?' . base64_encode(mb_substr($subject, 0, 255)) . '?=';

            $eml  = 'From: FAQ Zabbix NOC <rafaelereno@gmail.com>' . "\r\n";
            $eml .= 'To: ' . $media['sendto'] . "\r\n";
            $eml .= 'Subject: ' . $subjectB64 . "\r\n";
            $eml .= 'MIME-Version: 1.0' . "\r\n";
            $eml .= 'Content-Type: text/html; charset=UTF-8' . "\r\n";
            $eml .= 'Content-Transfer-Encoding: base64' . "\r\n";
            $eml .= "\r\n";
            $eml .= chunk_split(base64_encode($htmlBody));

            file_put_contents($emlFile, $eml);

            $cmd = 'curl -s --url "smtp://192.168.0.150:25"'
                . ' --mail-from "rafaelereno@gmail.com"'
                . ' --mail-rcpt ' . escapeshellarg($media['sendto'])
                . ' --upload-file ' . escapeshellarg($emlFile)
                . ' 2>&1';

            exec($cmd);
            @unlink($emlFile);


        }
        catch (\Exception $e) {
            error_log('FAQ Notification error: ' . $e->getMessage());
        }
    }

    private function buildEmailHtml(string $subject, string $message, string $icon, string $color): string {
        $msgHtml     = nl2br(htmlspecialchars($message, ENT_QUOTES));
        $subjectHtml = htmlspecialchars($subject, ENT_QUOTES);
        $colorHtml   = htmlspecialchars($color, ENT_QUOTES);
        return '<!DOCTYPE html>
<html lang="pt-BR">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>' . $subjectHtml . '</title></head>
<body style="margin:0;padding:0;background:#f0f2f5;font-family:Arial,Helvetica,sans-serif">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f0f2f5;padding:40px 0">
  <tr><td align="center">
    <table width="580" cellpadding="0" cellspacing="0"
           style="background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 4px 16px rgba(0,0,0,.12)">
      <tr>
        <td style="background:' . $colorHtml . ';padding:28px 36px;text-align:center">
          <div style="font-size:48px;line-height:1;margin-bottom:12px">' . $icon . '</div>
          <h1 style="margin:0;color:#fff;font-size:18px;font-weight:700">' . $subjectHtml . '</h1>
        </td>
      </tr>
      <tr>
        <td style="padding:32px 36px">
          <p style="margin:0;color:#444;font-size:15px;line-height:1.7">' . $msgHtml . '</p>
        </td>
      </tr>
      <tr><td style="padding:0 36px"><hr style="border:none;border-top:1px solid #ececec;margin:0"></td></tr>
      <tr>
        <td style="padding:20px 36px;background:#fafafa;text-align:center">
          <p style="margin:0;color:#aaa;font-size:12px;line-height:1.5">
            Notifica&ccedil;&atilde;o autom&aacute;tica do m&oacute;dulo <strong style="color:#888">FAQ</strong> &mdash; Zabbix NOC<br>
            N&atilde;o responda este e-mail.
          </p>
        </td>
      </tr>
    </table>
    <p style="margin:16px 0 0;color:#bbb;font-size:11px">Zabbix 7.0 &bull; FAQ Module v2.1.0</p>
  </td></tr>
</table>
</body>
</html>';
    }

    protected function notifyArticleAuthor(int $articleId, string $subject, string $message, string $icon = '📋', string $color = '#c44b4b'): void {
        $article = DBfetch(DBselect('SELECT created_by FROM zbx_faq_article WHERE articleid=' . $articleId));
        if ($article) {
            $this->sendFaqNotification((int)$article['created_by'], $subject, $message, $icon, $color);
        }
    }

    protected function notifyArticleAdmins(int $articleId, string $subject, string $message, string $icon = '📋', string $color = '#c44b4b'): void {
        $grpRes = DBselect('SELECT usrgrpid FROM zbx_faq_article_group WHERE articleid=' . $articleId);
        $groups = [];
        while ($g = DBfetch($grpRes)) { $groups[] = (int)$g['usrgrpid']; }
        if (!$groups) return;
        $userRes = DBselect(
            'SELECT DISTINCT ug.userid FROM users_groups ug' .
            ' JOIN users u ON u.userid=ug.userid JOIN role r ON r.roleid=u.roleid' .
            ' WHERE ug.usrgrpid IN (' . implode(',', $groups) . ') AND r.type >= 2'
        );
        while ($u = DBfetch($userRes)) {
            $this->sendFaqNotification((int)$u['userid'], $subject, $message, $icon, $color);
        }
    }

    // ── Helpers de categoria ──────────────────────────────────────────────

    protected function buildCategoryTree(int $parentId = 0, int $depth = 0): array {
        $result = DBselect(
            'SELECT categoryid, name, parent_id FROM zbx_faq_category WHERE ' .
            ($parentId === 0 ? 'parent_id IS NULL' : 'parent_id=' . $parentId) .
            ' ORDER BY sort_order, name'
        );
        $nodes = [];
        while ($row = DBfetch($result)) {
            $row['depth']    = $depth;
            $row['children'] = $this->buildCategoryTree((int)$row['categoryid'], $depth + 1);
            $nodes[] = $row;
        }
        return $nodes;
    }

    protected function flatCategoryTree(int $excludeId = 0, int $parentId = 0, int $depth = 0): array {
        $result = DBselect(
            'SELECT categoryid, name FROM zbx_faq_category WHERE ' .
            ($parentId === 0 ? 'parent_id IS NULL' : 'parent_id=' . $parentId) .
            ' ORDER BY sort_order, name'
        );
        $flat = [];
        while ($row = DBfetch($result)) {
            if ($excludeId > 0 && (int)$row['categoryid'] === $excludeId) continue;
            $row['depth'] = $depth;
            $row['label'] = str_repeat('— ', $depth) . $row['name'];
            $flat[] = $row;
            foreach ($this->flatCategoryTree($excludeId, (int)$row['categoryid'], $depth + 1) as $child) {
                $flat[] = $child;
            }
        }
        return $flat;
    }

    protected function getCategoryAndDescendants(int $rootId): array {
        $all = []; $queue = [$rootId];
        while ($queue) {
            $current = array_shift($queue); $all[] = $current;
            $childRes = DBselect('SELECT categoryid FROM zbx_faq_category WHERE parent_id=' . $current);
            while ($c = DBfetch($childRes)) { $queue[] = (int)$c['categoryid']; }
        }
        return $all ?: [0];
    }

    protected function getArticleTags(int $articleId): array {
        $res = DBselect(
            'SELECT t.name FROM zbx_faq_tag t JOIN zbx_faq_article_tag at2 ON at2.tagid=t.tagid' .
            ' WHERE at2.articleid=' . $articleId . ' ORDER BY t.name'
        );
        $tags = [];
        while ($row = DBfetch($res)) { $tags[] = $row['name']; }
        return $tags;
    }

    protected function statusLabel(int $status): string {
        return [0=>'Rascunho',1=>'Em Revisão',2=>'Publicado',3=>'Arquivado',4=>'Rejeitado'][$status] ?? '?';
    }

    protected function saveTagsAndGroups(int $articleId): void {
        $tags     = (array)$this->getInput('tags', []);
        $groupIds = array_map('intval', (array)$this->getInput('groups', []));

        // Tags — sempre atualiza
        DBexecute('DELETE FROM zbx_faq_article_tag WHERE articleid=' . $articleId);
        foreach ($tags as $tagName) {
            $tagName = trim((string)$tagName);
            if ($tagName === '') continue;
            $tagRow = DBfetch(DBselect('SELECT tagid FROM zbx_faq_tag WHERE name=' . zbx_dbstr($tagName)));
            if ($tagRow) {
                $tagId = (int)$tagRow['tagid'];
            } else {
                $tagId = $this->nextId('zbx_faq_tag', 'tagid');
                DBexecute('INSERT INTO zbx_faq_tag (tagid,name) VALUES (' . $tagId . ',' . zbx_dbstr($tagName) . ')');
            }
            $atId = $this->nextId('zbx_faq_article_tag', 'article_tagid');
            DBexecute('INSERT INTO zbx_faq_article_tag (article_tagid,articleid,tagid) VALUES (' . $atId . ',' . $articleId . ',' . $tagId . ')');
        }

        // Grupos — só atualiza se groups[] foi explicitamente enviado no POST
        if (!$this->hasInput('groups')) {
            return; // Preserva grupos existentes
        }

        DBexecute('DELETE FROM zbx_faq_article_group WHERE articleid=' . $articleId);
        foreach ($groupIds as $gid) {
            if ($gid <= 0) continue;
            $agId = $this->nextId('zbx_faq_article_group', 'article_groupid');
            DBexecute('INSERT INTO zbx_faq_article_group (article_groupid,articleid,usrgrpid) VALUES (' . $agId . ',' . $articleId . ',' . $gid . ')');
        }
    }
}
