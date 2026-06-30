<?php declare(strict_types = 1);
namespace Modules\ZbxFaq\Actions;

use CController;
use CControllerResponseData;
use CControllerResponseFatal;

class FaqView extends CController {

    use FaqPermission;

    protected function init(): void {
        $this->disableCsrfValidation();
    }

    protected function checkInput(): bool {
        return $this->validateInput(['articleid' => 'required|int32']);
    }

    protected function checkPermissions(): bool {
        return $this->getCurrentUserId() > 0;
    }

    protected function doAction(): void {
        $articleId = (int)$this->getInput('articleid');
        $isAdmin   = $this->isAdminOrAbove();
        $isSA      = $this->isSuperAdmin();

        $article = DBfetch(DBselect(
            'SELECT a.*,' .
            ' c.name AS category_name,' .
            ' u.name AS author_name, u.surname AS author_surname,' .
            ' pu.name AS publisher_name, pu.surname AS publisher_surname' .
            ' FROM zbx_faq_article a' .
            ' LEFT JOIN zbx_faq_category c ON c.categoryid=a.categoryid' .
            ' LEFT JOIN users u ON u.userid=a.created_by' .
            ' LEFT JOIN users pu ON pu.userid=a.published_by' .
            ' WHERE a.articleid=' . $articleId
        ));

        if (!$article) {
            $this->setResponse(new CControllerResponseFatal());
            return;
        }

        // Usuários comuns só veem publicados
        if (!$isAdmin && (int)$article['status'] !== 2) {
            $this->setResponse(new CControllerResponseFatal());
            return;
        }

        if (!$this->canViewArticle($articleId)) {
            $this->setResponse(new CControllerResponseFatal());
            return;
        }

        $tags = $this->getArticleTags($articleId);

        $mediaResult = DBselect(
            'SELECT mediaid, filename, original_name, mime_type, file_size' .
            ' FROM zbx_faq_media WHERE articleid=' . $articleId . ' ORDER BY uploaded_at'
        );
        $media = [];
        while ($m = DBfetch($mediaResult)) {
            $media[] = $m;
        }

        $groupResult = DBselect(
            'SELECT g.usrgrpid, g.name FROM usrgrp g' .
            ' JOIN zbx_faq_article_group ag ON ag.usrgrpid=g.usrgrpid' .
            ' WHERE ag.articleid=' . $articleId . ' ORDER BY g.name'
        );
        $groups = [];
        while ($g = DBfetch($groupResult)) {
            $groups[] = $g;
        }

        $revisions = [];
        if ($isAdmin) {
            $revResult = DBselect(
                'SELECT r.*, u.name, u.surname FROM zbx_faq_revision r' .
                ' LEFT JOIN users u ON u.userid=r.changed_by' .
                ' WHERE r.articleid=' . $articleId . ' ORDER BY r.changed_at DESC',
                10
            );
            while ($r = DBfetch($revResult)) {
                $revisions[] = $r;
            }
        }

        $response = new CControllerResponseData([
            'article'   => $article,
            'tags'      => $tags,
            'media'     => $media,
            'groups'    => $groups,
            'revisions' => $revisions,
            'is_admin'  => $isAdmin,
            'is_sa'     => $isSA,
        ]);
        $response->setTitle(_('FAQ — Artigo'));
        $this->setResponse($response);
    }
}
