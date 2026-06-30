<?php declare(strict_types = 1);
namespace Modules\ZbxFaq\Actions;

use CController;
use CControllerResponseData;

class FaqAdminCategoryDelete extends CController {

    use FaqPermission;

    protected function init(): void {
        $this->disableCsrfValidation();
    }

    protected function checkInput(): bool {
        return $this->validateInput(['categoryid' => 'required|int32']);
    }

    protected function checkPermissions(): bool {
        return $this->isAdminOrAbove();
    }

    protected function doAction(): void {
        $categoryId = (int)$this->getInput('categoryid');

        $artCount = DBfetch(DBselect(
            'SELECT COUNT(*) AS cnt FROM zbx_faq_article WHERE categoryid=' . $categoryId
        ));
        if ((int)$artCount['cnt'] > 0) {
            $this->setResponse(new CControllerResponseData([
                'main_block' => json_encode(['success' => false, 'error' => 'Existem artigos nessa categoria. Mova-os antes de excluir.'])
            ]));
            return;
        }

        $childCount = DBfetch(DBselect(
            'SELECT COUNT(*) AS cnt FROM zbx_faq_category WHERE parent_id=' . $categoryId
        ));
        if ((int)$childCount['cnt'] > 0) {
            $this->setResponse(new CControllerResponseData([
                'main_block' => json_encode(['success' => false, 'error' => 'Existem sub-categorias. Remova-as primeiro.'])
            ]));
            return;
        }

        DBstart();
        $ok = true;
        try {
            DBexecute('DELETE FROM zbx_faq_category WHERE categoryid=' . $categoryId);
        } catch (\Exception $e) {
            $ok = false;
        }
        DBend($ok);

        $this->setResponse(new CControllerResponseData([
            'main_block' => json_encode(['success' => $ok, 'error' => $ok ? null : 'Falha ao excluir.'])
        ]));
    }
}
