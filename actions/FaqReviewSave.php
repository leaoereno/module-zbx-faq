<?php declare(strict_types = 1);

namespace Modules\ZbxFaq\Actions;

use CController, CControllerResponseData;

class FaqReviewSave extends CController {

    use FaqPermission;

    protected function init(): void {
        $this->disableCsrfValidation();
    }

    protected function checkInput(): bool {
        return $this->validateInput([
            'articleid'      => 'required|int32',
            'submit_action'  => 'required|in publish,reject,save,archive',
            'title'          => 'string',
            'content'        => 'string',
            'content_type'   => 'string',
            'categoryid'     => 'string',
            'review_comment' => 'string',
            'tags'           => 'array',
            'groups'         => 'array',
        ]);
    }

    protected function checkPermissions(): bool {
        return $this->isAdminOrAbove();
    }

    protected function doAction(): void {
        $articleId     = (int)$this->getInput('articleid');
        $submitAction  = $this->getInput('submit_action');
        $reviewComment = trim($this->getInput('review_comment', ''));
        $userId        = $this->getCurrentUserId();
        $now           = time();
        $errorMsg      = null;

        if (!$this->canReviewArticle($articleId)) {
            $this->setResponse(new CControllerResponseData([
                'main_block' => json_encode(['success' => false, 'error' => 'Sem permissão.'])
            ]));
            return;
        }

        $article = DBfetch(DBselect('SELECT * FROM zbx_faq_article WHERE articleid=' . $articleId));
        if (!$article) {
            $this->setResponse(new CControllerResponseData([
                'main_block' => json_encode(['success' => false, 'error' => 'Artigo não encontrado.'])
            ]));
            return;
        }

        $oldStatus = (int)$article['status'];

        DBstart();
        $ok = true;

        try {
            if ($submitAction === 'publish') {
                $title       = trim($this->getInput('title', $article['title']));
                $content     = $this->getInput('content', $article['content']);
                $contentType = (int)$this->getInput('content_type', $article['content_type']);
                $categoryId  = (int)$this->getInput('categoryid', $article['categoryid']);
                $newStatus   = 2;

                $revId = $this->nextId('zbx_faq_revision', 'revisionid');
                DBexecute(
                    'INSERT INTO zbx_faq_revision (revisionid,articleid,title,content,content_type,status_from,status_to,changed_by,changed_at,note)' .
                    ' VALUES (' . $revId . ',' . $articleId . ',' .
                    zbx_dbstr($title) . ',' . zbx_dbstr($content) . ',' .
                    $contentType . ',' . $oldStatus . ',' . $newStatus . ',' .
                    $userId . ',' . $now . ',' . zbx_dbstr('Publicado por revisor') . ')'
                );
                DBexecute(
                    'UPDATE zbx_faq_article SET' .
                    ' title=' . zbx_dbstr($title) .
                    ', content=' . zbx_dbstr($content) .
                    ', content_type=' . $contentType .
                    ', categoryid=' . $categoryId .
                    ', status=2, review_comment=NULL' .
                    ', updated_by=' . $userId . ', updated_at=' . $now .
                    ', published_by=' . $userId . ', published_at=' . $now .
                    ' WHERE articleid=' . $articleId
                );
                $this->saveTagsAndGroups($articleId);

            } elseif ($submitAction === 'reject') {
                $newStatus = 4;
                $revId = $this->nextId('zbx_faq_revision', 'revisionid');
                DBexecute(
                    'INSERT INTO zbx_faq_revision (revisionid,articleid,title,content,content_type,status_from,status_to,changed_by,changed_at,note)' .
                    ' VALUES (' . $revId . ',' . $articleId . ',' .
                    zbx_dbstr($article['title']) . ',' . zbx_dbstr($article['content']) . ',' .
                    (int)$article['content_type'] . ',' . $oldStatus . ',' . $newStatus . ',' .
                    $userId . ',' . $now . ',' . zbx_dbstr($reviewComment ?: 'Rejeitado pelo revisor') . ')'
                );
                DBexecute(
                    'UPDATE zbx_faq_article SET status=4' .
                    ', review_comment=' . zbx_dbstr($reviewComment) .
                    ', updated_by=' . $userId . ', updated_at=' . $now .
                    ' WHERE articleid=' . $articleId
                );

            } elseif ($submitAction === 'archive') {
                $newStatus = 3;
                $revId = $this->nextId('zbx_faq_revision', 'revisionid');
                DBexecute(
                    'INSERT INTO zbx_faq_revision (revisionid,articleid,title,content,content_type,status_from,status_to,changed_by,changed_at,note)' .
                    ' VALUES (' . $revId . ',' . $articleId . ',' .
                    zbx_dbstr($article['title']) . ',' . zbx_dbstr($article['content']) . ',' .
                    (int)$article['content_type'] . ',' . $oldStatus . ',3,' .
                    $userId . ',' . $now . ',' . zbx_dbstr('Arquivado pelo admin') . ')'
                );
                DBexecute(
                    'UPDATE zbx_faq_article SET status=3' .
                    ', updated_by=' . $userId . ', updated_at=' . $now .
                    ' WHERE articleid=' . $articleId
                );

        } else {
                // save — salva edições sem publicar
                $title       = trim($this->getInput('title', $article['title']));
                $content     = $this->getInput('content', $article['content']);
                $contentType = (int)$this->getInput('content_type', $article['content_type']);
                $categoryId  = (int)$this->getInput('categoryid', $article['categoryid']);
                $newStatus   = $oldStatus;

                $revId = $this->nextId('zbx_faq_revision', 'revisionid');
                DBexecute(
                    'INSERT INTO zbx_faq_revision (revisionid,articleid,title,content,content_type,status_from,status_to,changed_by,changed_at,note)' .
                    ' VALUES (' . $revId . ',' . $articleId . ',' .
                    zbx_dbstr($title) . ',' . zbx_dbstr($content) . ',' .
                    $contentType . ',' . $oldStatus . ',' . $oldStatus . ',' .
                    $userId . ',' . $now . ',' . zbx_dbstr('Edição pelo revisor') . ')'
                );
                DBexecute(
                    'UPDATE zbx_faq_article SET' .
                    ' title=' . zbx_dbstr($title) .
                    ', content=' . zbx_dbstr($content) .
                    ', content_type=' . $contentType .
                    ', categoryid=' . $categoryId .
                    ', updated_by=' . $userId . ', updated_at=' . $now .
                    ' WHERE articleid=' . $articleId
                );
                $this->saveTagsAndGroups($articleId);
            }

        } catch (\Exception $e) {
            $ok = false;
            $errorMsg = $e->getMessage();
        }

        DBend($ok);

        if ($ok) {
            $title = $this->getInput('title', $article['title']);
            if ($submitAction === 'publish') {
                $this->notifyArticleAuthor($articleId,
                    '[FAQ] ✅ Artigo publicado: ' . $title,
                    "Seu artigo \"{$title}\" foi aprovado e publicado!\n\nJá está disponível na seção FAQ → Artigos.",
                    '✅', '#16a34a'
                );
            } elseif ($submitAction === 'reject') {
                $this->notifyArticleAuthor($articleId,
                    '[FAQ] ❌ Artigo devolvido: ' . $title,
                    "Seu artigo \"{$title}\" foi devolvido pelo revisor.\n\nComentário: " . ($reviewComment ?: 'Sem comentário.') .
                    "\n\nAcesse FAQ → Escrever Artigo para corrigir e reenviar.",
                    '❌', '#dc2626'
                );
            } elseif ($submitAction === 'save') {
                $this->notifyArticleAuthor($articleId,
                    '[FAQ] ✏️ Artigo editado pelo revisor: ' . $title,
                    "Seu artigo \"{$title}\" foi editado pelo revisor e continua em análise.\n\nAguarde a decisão de publicação.",
                    '✏️', '#d97706'
                );
            }
        }

        $this->setResponse(new CControllerResponseData([
            'main_block' => json_encode([
                'success'   => $ok,
                'articleid' => $articleId,
                'action'    => $submitAction,
                'error'     => $ok ? null : ($errorMsg ?? 'Falha.'),
            ])
        ]));
    }
}
