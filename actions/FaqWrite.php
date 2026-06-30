<?php declare(strict_types = 1);
namespace Modules\ZbxFaq\Actions;

use CController;
use CControllerResponseData;

class FaqWrite extends CController {

    use FaqPermission;

    protected function init(): void {
        $this->disableCsrfValidation();
    }

    protected function checkInput(): bool {
        return $this->validateInput([
            'page' => 'ge 1',
        ]);
    }

    protected function checkPermissions(): bool {
        return $this->getCurrentUserId() > 0;
    }

    protected function doAction(): void {
        $userId = $this->getCurrentUserId();
        $isSA   = $this->isSuperAdmin();
        $page   = max(1, (int)$this->getInput('page', 1));
        $limit  = 20;
        $offset = ($page - 1) * $limit;

        // Autores veem TODOS os próprios artigos (qualquer status)
        // SuperAdmin vê todos os artigos de todos
        $where = $isSA ? '1=1' : 'a.created_by=' . $userId;

        $totalRow = DBfetch(DBselect('SELECT COUNT(*) AS cnt FROM zbx_faq_article a WHERE ' . $where));
        $total    = (int)$totalRow['cnt'];

        $result = DBselect(
            'SELECT a.articleid, a.title, a.status, a.content_type,' .
            ' a.created_at, a.updated_at, a.review_comment,' .
            ' c.name AS category_name' .
            ' FROM zbx_faq_article a' .
            ' LEFT JOIN zbx_faq_category c ON c.categoryid=a.categoryid' .
            ' WHERE ' . $where .
            ' ORDER BY a.updated_at DESC',
            $limit, $offset
        );

        $articles = [];
        while ($row = DBfetch($result)) {
            $row['tags']         = $this->getArticleTags((int)$row['articleid']);
            $row['status_label'] = $this->statusLabel((int)$row['status']);
            $articles[]          = $row;
        }

        // Grupos do usuário para o formulário de novo artigo
        $userGroups  = $this->getCurrentUserGroups();
        $groupsRes   = DBselect('SELECT usrgrpid, name FROM usrgrp ORDER BY name');
        $availGroups = [];
        while ($g = DBfetch($groupsRes)) {
            // SuperAdmin vê todos os grupos; outros só os próprios
            if ($isSA || in_array((int)$g['usrgrpid'], $userGroups)) {
                $availGroups[] = $g;
            }
        }

        $response = new CControllerResponseData([
            'articles'    => $articles,
            'categories'  => $this->flatCategoryTree(),
            'avail_groups'=> $availGroups,
            'total'       => $total,
            'page'        => $page,
            'limit'       => $limit,
            'is_sa'       => $isSA,
        ]);
        $response->setTitle(_('FAQ — Escrever Artigo'));
        $this->setResponse($response);
    }
}
