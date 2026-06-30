<?php declare(strict_types = 1);
namespace Modules\ZbxFaq\Actions;

use CController;
use CControllerResponseData;

class FaqReview extends CController {

    use FaqPermission;

    protected function init(): void {
        $this->disableCsrfValidation();
    }

    protected function checkInput(): bool {
        return $this->validateInput(['page' => 'ge 1']);
    }

    protected function checkPermissions(): bool {
        return $this->isAdminOrAbove();
    }

    protected function doAction(): void {
        $isSA      = $this->isSuperAdmin();
        $userId    = $this->getCurrentUserId();
        $page      = max(1, (int)$this->getInput('page', 1));
        $limit     = 20;
        $offset    = ($page - 1) * $limit;

        // Admin vê artigos em revisão dos seus grupos
        // SuperAdmin vê todos em revisão
        $where = ['a.status = 1'];

        if (!$isSA) {
            $userGroups = $this->getCurrentUserGroups();
            if ($userGroups) {
                $groupList = implode(',', array_map('intval', $userGroups));
                // Artigos sem grupo só para SuperAdmin
                $where[] = 'EXISTS (SELECT 1 FROM zbx_faq_article_group ag2 WHERE ag2.articleid=a.articleid AND ag2.usrgrpid IN (' . $groupList . '))';
            } else {
                $where[] = '1=0';
            }
        }

        $whereClause = implode(' AND ', $where);

        $totalRow = DBfetch(DBselect('SELECT COUNT(*) AS cnt FROM zbx_faq_article a WHERE ' . $whereClause));
        $total    = (int)$totalRow['cnt'];

        $result = DBselect(
            'SELECT a.articleid, a.title, a.status, a.content_type,' .
            ' a.created_at, a.updated_at,' .
            ' c.name AS category_name,' .
            ' u.name AS author_name, u.surname AS author_surname' .
            ' FROM zbx_faq_article a' .
            ' LEFT JOIN zbx_faq_category c ON c.categoryid=a.categoryid' .
            ' LEFT JOIN users u ON u.userid=a.created_by' .
            ' WHERE ' . $whereClause . ' ORDER BY a.updated_at ASC',
            $limit, $offset
        );

        $articles = [];
        while ($row = DBfetch($result)) {
            $row['tags']         = $this->getArticleTags((int)$row['articleid']);
            $row['status_label'] = $this->statusLabel((int)$row['status']);
            $articles[]          = $row;
        }

        $response = new CControllerResponseData([
            'articles' => $articles,
            'total'    => $total,
            'page'     => $page,
            'limit'    => $limit,
            'is_sa'    => $isSA,
        ]);
        $response->setTitle(_('FAQ — Publicação'));
        $this->setResponse($response);
    }
}
