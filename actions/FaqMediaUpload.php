<?php declare(strict_types = 1);
namespace Modules\ZbxFaq\Actions;

use CController;
use CControllerResponseData;

class FaqMediaUpload extends CController {

    use FaqPermission;

    private const ALLOWED_MIME = [
        'image/png','image/jpeg','image/gif','image/webp','image/svg+xml',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/plain','text/csv',
        'video/mp4','video/webm',
    ];
    private const MAX_MB = 10;

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

        if (empty($_FILES['media_file']) || $_FILES['media_file']['error'] !== UPLOAD_ERR_OK) {
            $this->setResponse(new CControllerResponseData([
                'main_block' => json_encode(['success' => false, 'error' => 'Nenhum arquivo recebido.'])
            ]));
            return;
        }

        $file         = $_FILES['media_file'];
        $originalName = basename($file['name']);
        $tmpPath      = $file['tmp_name'];
        $fileSize     = (int)$file['size'];

        if ($fileSize > self::MAX_MB * 1024 * 1024) {
            $this->setResponse(new CControllerResponseData([
                'main_block' => json_encode(['success' => false, 'error' => 'Arquivo excede ' . self::MAX_MB . 'MB.'])
            ]));
            return;
        }

        $finfo    = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($tmpPath);

        if (!in_array($mimeType, self::ALLOWED_MIME)) {
            $this->setResponse(new CControllerResponseData([
                'main_block' => json_encode(['success' => false, 'error' => "Tipo não permitido: {$mimeType}"])
            ]));
            return;
        }

        $ext        = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $storedName = uniqid('faq_', true) . '.' . $ext;
        $destDir    = __DIR__ . '/../assets/media/';
        $destPath   = $destDir . $storedName;

        if (!is_dir($destDir)) mkdir($destDir, 0750, true);

        if (!move_uploaded_file($tmpPath, $destPath)) {
            $this->setResponse(new CControllerResponseData([
                'main_block' => json_encode(['success' => false, 'error' => 'Falha ao mover arquivo.'])
            ]));
            return;
        }

        DBstart();
        $ok = true;
        $mediaId = 0;
        try {
            $mediaId = $this->nextId('zbx_faq_media', 'mediaid');
            DBexecute(
                'INSERT INTO zbx_faq_media (mediaid,articleid,filename,original_name,mime_type,file_size,uploaded_by,uploaded_at)' .
                ' VALUES (' . $mediaId . ',' . $articleId . ',' .
                zbx_dbstr($storedName) . ',' . zbx_dbstr($originalName) . ',' .
                zbx_dbstr($mimeType) . ',' . $fileSize . ',' .
                $this->getCurrentUserId() . ',' . time() . ')'
            );
        } catch (\Exception $e) {
            $ok = false;
            @unlink($destPath);
        }
        DBend($ok);

        $this->setResponse(new CControllerResponseData([
            'main_block' => json_encode([
                'success'       => $ok,
                'mediaid'       => $ok ? $mediaId : 0,
                'original_name' => $originalName,
                'stored_name'   => $ok ? $storedName : '',
                'mime_type'     => $mimeType,
                'file_size'     => $fileSize,
                'error'         => $ok ? null : 'Falha ao registrar arquivo.',
            ])
        ]));
    }
}
