<?php declare(strict_types = 1);
namespace Modules\ZbxFaq\Actions;

use CController;
use CControllerResponseData;

class FaqArticles extends CController {

    use FaqPermission;

    protected function init(): void {
        $this->disableCsrfValidation();
    }

    protected function checkInput(): bool {
        return $this->validateInput([
            'search'     => 'string',
            'categoryid' => 'int32',
            'tag'        => 'string',
            'page'       => 'ge 1',
        ]);
    }

    protected function checkPermissions(): bool {
        return $this->getCurrentUserId() > 0;
    }

    protected function doAction(): void {
        $isSA       = $this->isSuperAdmin();
        $search     = $this->getInput('search', '');
        $categoryId = (int)$this->getInput('categoryid', 0);
        $tag        = $this->getInput('tag', '');
        $page       = max(1, (int)$this->getInput('page', 1));
        $limit      = 20;
        $offset     = ($page - 1) * $limit;

        // Apenas artigos publicados
        $where = ['a.status = 2'];

        if ($search !== '') {
            $safe    = addslashes($search);
            $where[] = "(a.title LIKE '%{$safe}%' OR a.content LIKE '%{$safe}%')";
        }

        if ($categoryId > 0) {
            $catIds  = $this->getCategoryAndDescendants($categoryId);
            $where[] = 'a.categoryid IN (' . implode(',', $catIds) . ')';
        }

        if ($tag !== '') {
            $safeTag = addslashes($tag);
            $where[] = "EXISTS (SELECT 1 FROM zbx_faq_article_tag at2 JOIN zbx_faq_tag t ON t.tagid=at2.tagid WHERE at2.articleid=a.articleid AND t.name='{$safeTag}')";
        }

        // Segmentação de grupos
        if (!$isSA) {
            $userGroups = $this->getCurrentUserGroups();
            if ($userGroups) {
                $groupList = implode(',', array_map('intval', $userGroups));
                // Artigos sem grupo só para SuperAdmin
                $where[] = 'EXISTS (SELECT 1 FROM zbx_faq_article_group ag2 WHERE ag2.articleid=a.articleid AND ag2.usrgrpid IN (' . $groupList . '))';
            } else {
                // Usuário sem grupos não vê nada
                $where[] = '1=0';
            }
        }

        $whereClause = implode(' AND ', $where);

        $totalRow = DBfetch(DBselect('SELECT COUNT(*) AS cnt FROM zbx_faq_article a WHERE ' . $whereClause));
        $total    = (int)$totalRow['cnt'];

        $result = DBselect(
            'SELECT a.articleid, a.title, a.content_type, a.published_at,' .
            ' c.name AS category_name,' .
            ' u.name AS author_name, u.surname AS author_surname' .
            ' FROM zbx_faq_article a' .
            ' LEFT JOIN zbx_faq_category c ON c.categoryid=a.categoryid' .
            ' LEFT JOIN users u ON u.userid=a.created_by' .
            ' WHERE ' . $whereClause . ' ORDER BY a.published_at DESC',
            $limit, $offset
        );

        $articles = [];
        while ($row = DBfetch($result)) {
            $row['tags'] = $this->getArticleTags((int)$row['articleid']);
            $articles[]  = $row;
        }

        $allTagsRes = DBselect('SELECT name FROM zbx_faq_tag ORDER BY name');
        $allTags    = [];
        while ($t = DBfetch($allTagsRes)) {
            $allTags[] = $t['name'];
        }

        $response = new CControllerResponseData([
            'articles'   => $articles,
            'categories' => $this->buildCategoryTree(),
            'all_tags'   => $allTags,
            'total'      => $total,
            'page'       => $page,
            'limit'      => $limit,
            'search'     => $search,
            'categoryid' => $categoryId,
            'tag'        => $tag,
            'is_admin'   => $this->isAdminOrAbove(),
            'is_sa'      => $isSA,
        ]);
        $response->setTitle(_('FAQ — Artigos'));
        $this->setResponse($response);
    }
}
