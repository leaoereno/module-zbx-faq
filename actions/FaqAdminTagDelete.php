<?php declare(strict_types = 1);
namespace Modules\ZbxFaq\Actions;
use CController, CControllerResponseData;

class FaqAdminTagDelete extends CController {
    use FaqPermission;

    protected function init(): void { $this->disableCsrfValidation(); }

    protected function checkInput(): bool {
        return $this->validateInput(['tagid' => 'required|int32']);
    }

    protected function checkPermissions(): bool {
        return $this->isSuperAdmin();
    }

    protected function doAction(): void {
        $tagId = (int)$this->getInput('tagid');
        DBstart();
        $ok = true;
        try {
            DBexecute('DELETE FROM zbx_faq_article_tag WHERE tagid=' . $tagId);
            DBexecute('DELETE FROM zbx_faq_tag WHERE tagid=' . $tagId);
        } catch (\Exception $e) { $ok = false; }
        DBend($ok);
        $this->setResponse(new CControllerResponseData([
            'main_block' => json_encode(['success' => $ok, 'tagid' => $tagId, 'error' => $ok ? null : 'Falha.'])
        ]));
    }
}
