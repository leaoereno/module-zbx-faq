<?php declare(strict_types = 1);
namespace Modules\ZbxFaq\Actions;

use CController;
use CControllerResponseData;

class FaqAdmin extends CController {

    use FaqPermission;

    protected function init(): void {
        $this->disableCsrfValidation();
    }

    protected function checkInput(): bool {
        return $this->validateInput([
            'tab'  => 'in categories,articles,tags,notifications',
            'page' => 'ge 1',
        ]);
    }

    protected function checkPermissions(): bool {
        return $this->isAdminOrAbove();
    }

    protected function doAction(): void {
        $isSA = $this->isSuperAdmin();
        $tab  = $this->getInput('tab', 'categories');
        $page = max(1, (int)$this->getInput('page', 1));

        // Categorias
        $categories = $this->buildCategoryTree();

        // Todos os artigos (paginados)
        $limit  = 30;
        $offset = ($page - 1) * $limit;

        // Admin vê artigos dos seus grupos; SA vê todos
        $where = '1=1';
        if (!$isSA) {
            $userGroups = $this->getCurrentUserGroups();
            if ($userGroups) {
                $groupList = implode(',', array_map('intval', $userGroups));
                $where = '(NOT EXISTS (SELECT 1 FROM zbx_faq_article_group ag WHERE ag.articleid=a.articleid)' .
                         ' OR EXISTS (SELECT 1 FROM zbx_faq_article_group ag2 WHERE ag2.articleid=a.articleid AND ag2.usrgrpid IN (' . $groupList . ')))';
            }
        }

        $totalRow = DBfetch(DBselect('SELECT COUNT(*) AS cnt FROM zbx_faq_article a WHERE ' . $where));
        $total    = (int)$totalRow['cnt'];

        $result = DBselect(
            'SELECT a.articleid, a.title, a.status, a.content_type,' .
            ' a.created_at, a.updated_at,' .
            ' c.name AS category_name,' .
            ' u.name AS author_name, u.surname AS author_surname' .
            ' FROM zbx_faq_article a' .
            ' LEFT JOIN zbx_faq_category c ON c.categoryid=a.categoryid' .
            ' LEFT JOIN users u ON u.userid=a.created_by' .
            ' WHERE ' . $where . ' ORDER BY a.updated_at DESC',
            $limit, $offset
        );

        $articles = [];
        while ($row = DBfetch($result)) {
            $row['status_label'] = $this->statusLabel((int)$row['status']);
            $articles[] = $row;
        }

        // Tags
        $tagsRes = DBselect(
            'SELECT t.tagid, t.name, COUNT(at2.article_tagid) AS usage_count' .
            ' FROM zbx_faq_tag t' .
            ' LEFT JOIN zbx_faq_article_tag at2 ON at2.tagid=t.tagid' .
            ' GROUP BY t.tagid, t.name ORDER BY t.name'
        );
        $tags = [];
        while ($t = DBfetch($tagsRes)) { $tags[] = $t; }

        // Media types para configuração de notificação
        $mtRes = DBselect('SELECT mediatypeid, name FROM media_type WHERE status=0 ORDER BY name');
        $mediaTypes = [];
        while ($mt = DBfetch($mtRes)) {
            $mediaTypes[] = $mt;
        }

        // Media type atual configurado para FAQ
        $faqMt = DBfetch(DBselect(
            "SELECT mediatypeid, name FROM media_type WHERE name='FAQ Notifications' AND status=0"
        ));

        $response = new CControllerResponseData([
            'tab'           => $tab,
            'categories'    => $categories,
            'flat_cats'     => $this->flatCategoryTree(),
            'articles'      => $articles,
            'total'         => $total,
            'page'          => $page,
            'limit'         => $limit,
            'media_types'   => $mediaTypes,
            'faq_mediatype' => $faqMt ?: null,
            'tags'          => $tags,
            'is_sa'         => $isSA,
        ]);
        $response->setTitle(_('FAQ — Administração'));
        $this->setResponse($response);
    }
}
