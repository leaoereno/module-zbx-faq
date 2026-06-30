<?php declare(strict_types = 1);
namespace Modules\ZbxFaq\Actions;

use CController;
use CControllerResponseData;
use CControllerResponseFatal;

class FaqWriteEdit extends CController {

    use FaqPermission;

    protected function init(): void {
        $this->disableCsrfValidation();
    }

    protected function checkInput(): bool {
        return $this->validateInput(['articleid' => 'int32']);
    }

    protected function checkPermissions(): bool {
        return $this->getCurrentUserId() > 0;
    }

    protected function doAction(): void {
        $articleId = (int)$this->getInput('articleid', 0);
        $isSA      = $this->isSuperAdmin();
        $userId    = $this->getCurrentUserId();

        $article = null;
        $tags    = [];
        $groups  = [];
        $media   = [];

        if ($articleId > 0) {
            $article = DBfetch(DBselect('SELECT * FROM zbx_faq_article WHERE articleid=' . $articleId));
            if (!$article) {
                $this->setResponse(new CControllerResponseFatal());
                return;
            }
            // Apenas o autor ou SuperAdmin pode editar
            if (!$isSA && (int)$article['created_by'] !== $userId) {
                $this->setResponse(new CControllerResponseFatal());
                return;
            }
            // Não pode editar artigos já publicados (apenas Admin/SA pode)
            if (!$this->isAdminOrAbove() && in_array((int)$article['status'], [2, 3])) {
                $this->setResponse(new CControllerResponseFatal());
                return;
            }

            $tags   = $this->getArticleTags($articleId);
            $grpRes = DBselect('SELECT usrgrpid FROM zbx_faq_article_group WHERE articleid=' . $articleId);
            while ($g = DBfetch($grpRes)) {
                $groups[] = (int)$g['usrgrpid'];
            }
            $medRes = DBselect(
                'SELECT mediaid, original_name, mime_type, file_size FROM zbx_faq_media' .
                ' WHERE articleid=' . $articleId . ' ORDER BY uploaded_at'
            );
            while ($m = DBfetch($medRes)) {
                $media[] = $m;
            }
        }

        // Grupos disponíveis para o usuário
        $userGroups  = $this->getCurrentUserGroups();
        $groupsRes   = DBselect('SELECT usrgrpid, name FROM usrgrp ORDER BY name');
        $availGroups = [];
        while ($g = DBfetch($groupsRes)) {
            if ($isSA || in_array((int)$g['usrgrpid'], $userGroups)) {
                $availGroups[] = $g;
            }
        }

        $allTagsRes = DBselect('SELECT name FROM zbx_faq_tag ORDER BY name');
        $allTags    = [];
        while ($t = DBfetch($allTagsRes)) {
            $allTags[] = $t['name'];
        }

        $response = new CControllerResponseData([
            'article'     => $article,
            'tags'        => $tags,
            'groups'      => $groups,
            'media'       => $media,
            'avail_groups'=> $availGroups,
            'all_tags'    => $allTags,
            'categories'  => $this->flatCategoryTree(),
            'is_sa'       => $isSA,
            'is_admin'    => $this->isAdminOrAbove(),
        ]);
        $response->setTitle(_('FAQ — Editor'));
        $this->setResponse($response);
    }
}
