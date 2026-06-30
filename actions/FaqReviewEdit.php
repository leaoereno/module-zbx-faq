<?php declare(strict_types = 1);
namespace Modules\ZbxFaq\Actions;

use CController;
use CControllerResponseData;
use CControllerResponseFatal;

class FaqReviewEdit extends CController {

    use FaqPermission;

    protected function init(): void {
        $this->disableCsrfValidation();
    }

    protected function checkInput(): bool {
        return $this->validateInput(['articleid' => 'required|int32']);
    }

    protected function checkPermissions(): bool {
        return $this->isAdminOrAbove();
    }

    protected function doAction(): void {
        $articleId = (int)$this->getInput('articleid');

        $article = DBfetch(DBselect('SELECT * FROM zbx_faq_article WHERE articleid=' . $articleId));
        if (!$article) {
            $this->setResponse(new CControllerResponseFatal());
            return;
        }

        if (!$this->canReviewArticle($articleId)) {
            $this->setResponse(new CControllerResponseFatal());
            return;
        }

        $tags   = $this->getArticleTags($articleId);
        $grpRes = DBselect('SELECT usrgrpid FROM zbx_faq_article_group WHERE articleid=' . $articleId);
        $groups = [];
        while ($g = DBfetch($grpRes)) {
            $groups[] = (int)$g['usrgrpid'];
        }

        $medRes = DBselect(
            'SELECT mediaid, original_name, mime_type, file_size FROM zbx_faq_media' .
            ' WHERE articleid=' . $articleId . ' ORDER BY uploaded_at'
        );
        $media = [];
        while ($m = DBfetch($medRes)) {
            $media[] = $m;
        }

        // Histórico
        $revResult = DBselect(
            'SELECT r.*, u.name, u.surname FROM zbx_faq_revision r' .
            ' LEFT JOIN users u ON u.userid=r.changed_by' .
            ' WHERE r.articleid=' . $articleId . ' ORDER BY r.changed_at DESC',
            10
        );
        $revisions = [];
        while ($r = DBfetch($revResult)) {
            $revisions[] = $r;
        }

        $allGroupsRes = DBselect('SELECT usrgrpid, name FROM usrgrp ORDER BY name');
        $allGroups    = [];
        while ($g = DBfetch($allGroupsRes)) {
            $allGroups[] = $g;
        }

        $allTagsRes = DBselect('SELECT name FROM zbx_faq_tag ORDER BY name');
        $allTags    = [];
        while ($t = DBfetch($allTagsRes)) {
            $allTags[] = $t['name'];
        }

        $response = new CControllerResponseData([
            'article'    => $article,
            'tags'       => $tags,
            'groups'     => $groups,
            'media'      => $media,
            'revisions'  => $revisions,
            'all_groups' => $allGroups,
            'all_tags'   => $allTags,
            'categories' => $this->flatCategoryTree(),
            'is_sa'      => $this->isSuperAdmin(),
        ]);
        $response->setTitle(_('FAQ — Revisão'));
        $this->setResponse($response);
    }
}
