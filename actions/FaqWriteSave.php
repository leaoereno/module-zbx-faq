<?php declare(strict_types = 1);

namespace Modules\ZbxFaq\Actions;

use CController, CControllerResponseData;

/**
 * NOTA: Usa 'submit_action' no body do POST (não 'action') para evitar conflito
 * com o parâmetro de roteamento $_REQUEST['action'] do Zabbix.
 */
class FaqWriteSave extends CController {

    use FaqPermission;

    protected function init(): void {
        $this->disableCsrfValidation();
    }

    protected function checkInput(): bool {
        return $this->validateInput([
            'articleid'     => 'int32',
            'title'         => 'required|string',
            'content'       => 'required|string',
            'content_type'  => 'string',
            'categoryid'    => 'required|string',
            'submit_action' => 'string',
            'tags'          => 'array',
            'groups'        => 'array',
        ]);
    }

    protected function checkPermissions(): bool {
        return $this->getCurrentUserId() > 0;
    }

    protected function doAction(): void {
        $articleId    = (int)$this->getInput('articleid', 0);
        $title        = trim($this->getInput('title'));
        $content      = $this->getInput('content');
        $contentType  = (int)$this->getInput('content_type', 1);
        $categoryId   = (int)$this->getInput('categoryid', 0);
        $submitAction = $this->getInput('submit_action', 'save');
        $userId       = $this->getCurrentUserId();
        $now          = time();
        $newStatus    = ($submitAction === 'submit') ? 1 : 0;
        $errorMsg     = null;

        DBstart();
        $ok = true;

        try {
            if ($articleId > 0) {
                $existing = DBfetch(DBselect(
                    'SELECT created_by, status FROM zbx_faq_article WHERE articleid=' . $articleId
                ));
                if (!$existing || (
                    (int)$existing['created_by'] !== $userId && !$this->isSuperAdmin()
                )) {
                    throw new \Exception('Sem permissão para editar este artigo.');
                }

                $revId = $this->nextId('zbx_faq_revision', 'revisionid');
                DBexecute(
                    'INSERT INTO zbx_faq_revision' .
                    ' (revisionid,articleid,title,content,content_type,status_from,status_to,changed_by,changed_at,note)' .
                    ' VALUES (' . $revId . ',' . $articleId . ',' .
                    zbx_dbstr($title) . ',' . zbx_dbstr($content) . ',' .
                    $contentType . ',' . (int)$existing['status'] . ',' .
                    $newStatus . ',' . $userId . ',' . $now . ',' .
                    zbx_dbstr('Edição pelo autor') . ')'
                );

                DBexecute(
                    'UPDATE zbx_faq_article SET' .
                    ' title=' . zbx_dbstr($title) .
                    ', content=' . zbx_dbstr($content) .
                    ', content_type=' . $contentType .
                    ', categoryid=' . $categoryId .
                    ', status=' . $newStatus .
                    ', review_comment=NULL' .
                    ', updated_by=' . $userId .
                    ', updated_at=' . $now .
                    ' WHERE articleid=' . $articleId
                );
            } else {
                $articleId = $this->nextId('zbx_faq_article', 'articleid');
                DBexecute(
                    'INSERT INTO zbx_faq_article' .
                    ' (articleid,categoryid,title,content,content_type,status,created_by,created_at,updated_by,updated_at)' .
                    ' VALUES (' . $articleId . ',' . $categoryId . ',' .
                    zbx_dbstr($title) . ',' . zbx_dbstr($content) . ',' .
                    $contentType . ',' . $newStatus . ',' .
                    $userId . ',' . $now . ',' . $userId . ',' . $now . ')'
                );
            }

            error_log('FAQ_GROUPS: ' . json_encode($this->getInput('groups', [])));
        $this->saveTagsAndGroups($articleId);

        } catch (\Exception $e) {
            $ok = false;
            $errorMsg = $e->getMessage();
        }

        DBend($ok);

        if ($ok && $submitAction === 'submit') {
            $this->notifyArticleAdmins(
                $articleId,
                '[FAQ] 📤 Artigo aguardando revisão: ' . $title,
                "O artigo \"{$title}\" foi enviado para revisão.\n\nAcesse: FAQ → Revisão para analisar.", "📤", "#2563eb"
            );
        }

        $this->setResponse(new CControllerResponseData([
            'main_block' => json_encode([
                'success'   => $ok,
                'articleid' => $ok ? $articleId : 0,
                'status'    => $newStatus,
                'error'     => $ok ? null : ($errorMsg ?? 'Falha ao salvar.'),
            ])
        ]));
    }
}
