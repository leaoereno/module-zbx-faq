<?php declare(strict_types = 1);
namespace Modules\ZbxFaq\Actions;

use CController;
use CControllerResponseData;

class FaqAdminCategorySave extends CController {

    use FaqPermission;

    protected function init(): void {
        $this->disableCsrfValidation();
    }

    protected function checkInput(): bool {
        return $this->validateInput([
            'categoryid'  => 'int32',
            'name'        => 'required|string',
            'description' => 'string',
            'parent_id'   => 'string',
            'sort_order'  => 'int32',
        ]);
    }

    protected function checkPermissions(): bool {
        return $this->isAdminOrAbove();
    }

    protected function doAction(): void {
        $categoryId  = (int)$this->getInput('categoryid', 0);
        $name        = trim($this->getInput('name'));
        $description = trim($this->getInput('description', ''));
        $parentId    = (int)$this->getInput('parent_id', 0);
        $sortOrder   = (int)$this->getInput('sort_order', 0);
        $userId      = $this->getCurrentUserId();
        $now         = time();
        $errorMsg    = null;

        DBstart();
        $ok = true;

        try {
            if ($categoryId > 0) {
                DBexecute(
                    'UPDATE zbx_faq_category SET' .
                    ' name=' . zbx_dbstr($name) .
                    ', description=' . zbx_dbstr($description) .
                    ', parent_id=' . ($parentId > 0 ? $parentId : 'NULL') .
                    ', sort_order=' . $sortOrder .
                    ' WHERE categoryid=' . $categoryId
                );
            } else {
                $categoryId = $this->nextId('zbx_faq_category', 'categoryid');
                DBexecute(
                    'INSERT INTO zbx_faq_category (categoryid,parent_id,name,description,sort_order,created_by,created_at)' .
                    ' VALUES (' . $categoryId . ',' .
                    ($parentId > 0 ? $parentId : 'NULL') . ',' .
                    zbx_dbstr($name) . ',' .
                    zbx_dbstr($description) . ',' .
                    $sortOrder . ',' . $userId . ',' . $now . ')'
                );
            }
        } catch (\Exception $e) {
            $ok = false;
            $errorMsg = $e->getMessage();
        }

        DBend($ok);

        $this->setResponse(new CControllerResponseData([
            'main_block' => json_encode([
                'success'    => $ok,
                'categoryid' => $ok ? $categoryId : 0,
                'error'      => $ok ? null : ($errorMsg ?? 'Falha ao salvar categoria.'),
            ])
        ]));
    }
}
