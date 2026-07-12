<?php

declare(strict_types=1);

namespace App\ViewModels\Blocks;

class DocumentGalleryViewModel extends AbstractBlockViewModel
{
    public function vars(): array
    {
        $rawDocs = $this->data()['documents'] ?? [];
        $rawDocs = is_array($rawDocs) ? $rawDocs : [];

        $documents = [];
        foreach ($rawDocs as $d) {
            $fileUrl = is_scalar($d['file_url'] ?? null) ? (string) $d['file_url'] : '';
            $title   = is_scalar($d['title'] ?? null) ? (string) $d['title'] : '';
            $desc    = is_scalar($d['description'] ?? null) ? (string) $d['description'] : '';

            $ext = strtolower(pathinfo(parse_url($fileUrl, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));

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

            $documents[] = [
                'fileUrl'     => $fileUrl,
                'title'       => $title,
                'description' => $desc,
                'docType'     => $docType,
                'ext'         => $ext !== '' ? strtoupper($ext) : 'DOC',
            ];
        }

        return [
            'title'          => $this->dataString('title'),
            'description'    => $this->dataString('description'),
            'documents'      => $documents,
            'layout'         => $this->configString('layout', 'grid_cards'),
            'showFileMeta'   => $this->configBool('show_file_meta', true),
            'openInNewTab'   => $this->configBool('open_in_new_tab', true),
            'cssClass'       => trim($this->configString('css_class')),
        ];
    }
}
