<?php

declare(strict_types=1);

namespace Tests\Unit\ViewModels\Blocks;

use App\ViewModels\Blocks\PdfViewerViewModel;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class PdfViewerViewModelTest extends CIUnitTestCase
{
    public function testResolvesConfiguredPdfFileStringAndDefaults(): void
    {
        $vm = new PdfViewerViewModel([
            'block_config' => [
                'pdf_file' => 'https://example.com/files/manual.pdf',
                'height'   => '800px',
                'allow_download' => false,
            ],
            'block_data' => [
                'heading' => 'Manual',
            ],
        ], 'es');

        $vars = $vm->vars();

        $this->assertSame('https://example.com/files/manual.pdf', $vars['pdfUrl']);
        $this->assertSame('external_url', $vars['pdfFile']['source_kind']);
        $this->assertSame('800px', $vars['height']);
        $this->assertFalse($vars['allowDownload']);
    }

    public function testLegacyDataPdfFileStillWorks(): void
    {
        $vm = new PdfViewerViewModel([
            'block_data' => [
                'pdf_file' => 'https://example.com/files/legacy-handbook.pdf',
            ],
        ], 'es');

        $vars = $vm->vars();

        $this->assertSame('https://example.com/files/legacy-handbook.pdf', $vars['pdfUrl']);
    }
}
