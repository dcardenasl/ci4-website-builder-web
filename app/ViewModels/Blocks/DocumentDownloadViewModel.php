<?php

declare(strict_types=1);

namespace App\ViewModels\Blocks;

class DocumentDownloadViewModel extends AbstractBlockViewModel
{
    public function vars(): array
    {
        $url = $this->dataString('document_url');
        $path = parse_url($url, PHP_URL_PATH);
        $ext = strtolower(pathinfo(is_string($path) ? $path : '', PATHINFO_EXTENSION));

        $docType = 'generic';
        if (in_array($ext, ['pdf'], true)) {
            $docType = 'pdf';
        } elseif (in_array($ext, ['doc', 'docx', 'odt', 'rtf'], true)) {
            $docType = 'word';
        } elseif (in_array($ext, ['xls', 'xlsx', 'ods', 'csv'], true)) {
            $docType = 'excel';
        } elseif (in_array($ext, ['ppt', 'pptx', 'odp'], true)) {
            $docType = 'powerpoint';
        } elseif (in_array($ext, ['zip', 'rar', 'tar', 'gz', '7z'], true)) {
            $docType = 'archive';
        }

        return [
            'title'          => $this->dataString('title'),
            'description'    => $this->dataString('description'),
            'buttonLabel'    => $this->dataString('button_label', 'Descargar'),
            'documentUrl'    => $url,
            'docType'        => $docType,
            'ext'            => $ext !== '' ? strtoupper($ext) : 'DOC',
            'openInNewTab'   => $this->configBool('open_in_new_tab', true),
            'cssClass'       => trim($this->configString('css_class')),
        ];
    }
}
