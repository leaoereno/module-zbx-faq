<?php declare(strict_types = 1);
namespace Modules\ZbxFaq\Actions;

use CController;
use CControllerResponseData;

class FaqMediaDelete extends CController {

    use FaqPermission;

    protected function init(): void {
        $this->disableCsrfValidation();
    }

    protected function checkInput(): bool {
        return $this->validateInput(['mediaid' => 'required|int32']);
    }

    protected function checkPermissions(): bool {
        return $this->getCurrentUserId() > 0;
    }

    protected function doAction(): void {
        $mediaId = (int)$this->getInput('mediaid');

        $media = DBfetch(DBselect('SELECT filename FROM zbx_faq_media WHERE mediaid=' . $mediaId));

        DBstart();
        $ok = true;
        try {
            DBexecute('DELETE FROM zbx_faq_media WHERE mediaid=' . $mediaId);
            if ($media) {
                $path = __DIR__ . '/../assets/media/' . $media['filename'];
                if (file_exists($path)) @unlink($path);
            }
        } catch (\Exception $e) {
            $ok = false;
        }
        DBend($ok);

        $this->setResponse(new CControllerResponseData([
            'main_block' => json_encode(['success' => $ok, 'error' => $ok ? null : 'Falha ao excluir mídia.'])
        ]));
    }
}
