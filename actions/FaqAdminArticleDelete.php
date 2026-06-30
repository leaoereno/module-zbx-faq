<?php declare(strict_types = 1);
namespace Modules\ZbxFaq\Actions;

use CController;
use CControllerResponseData;

class FaqAdminArticleDelete extends CController {

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

        DBstart();
        $ok = true;
        try {
            $mediaRes = DBselect('SELECT filename FROM zbx_faq_media WHERE articleid=' . $articleId);
            while ($m = DBfetch($mediaRes)) {
                $path = __DIR__ . '/../assets/media/' . $m['filename'];
                if (file_exists($path)) @unlink($path);
            }
            DBexecute('DELETE FROM zbx_faq_media WHERE articleid=' . $articleId);
            DBexecute('DELETE FROM zbx_faq_article_tag WHERE articleid=' . $articleId);
            DBexecute('DELETE FROM zbx_faq_article_group WHERE articleid=' . $articleId);
            DBexecute('DELETE FROM zbx_faq_revision WHERE articleid=' . $articleId);
            DBexecute('DELETE FROM zbx_faq_article WHERE articleid=' . $articleId);
        } catch (\Exception $e) {
            $ok = false;
        }
        DBend($ok);

        $this->setResponse(new CControllerResponseData([
            'main_block' => json_encode(['success' => $ok, 'error' => $ok ? null : 'Falha ao excluir.'])
        ]));
    }
}
