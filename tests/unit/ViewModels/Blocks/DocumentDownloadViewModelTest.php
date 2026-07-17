<?php

declare(strict_types=1);

namespace Tests\Unit\ViewModels\Blocks;

use App\ViewModels\Blocks\DocumentDownloadViewModel;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class DocumentDownloadViewModelTest extends CIUnitTestCase
{
    public function testResolvesConfiguredDocumentStringAndDocumentType(): void
    {
        $vm = new DocumentDownloadViewModel([
            'block_config' => [
                'document' => 'https://example.com/files/handbook.pdf',
                'open_in_new_tab' => false,
            ],
            'block_data' => [
                'title'       => 'Policy Handbook',
                'description' => 'Internal reference',
                'button_label' => 'Download now',
            ],
        ], 'es');

        $vars = $vm->vars();

        $this->assertSame('https://example.com/files/handbook.pdf', $vars['documentUrl']);
        $this->assertSame('pdf', $vars['docType']);
        $this->assertSame('PDF', $vars['ext']);
        $this->assertFalse($vars['openInNewTab']);
    }

    public function testLegacyDocumentUrlStillWorks(): void
    {
        $vm = new DocumentDownloadViewModel([
            'block_data' => [
                'document_url' => 'https://example.com/files/handbook.docx',
            ],
        ], 'es');

        $vars = $vm->vars();

        $this->assertSame('https://example.com/files/handbook.docx', $vars['documentUrl']);
        $this->assertSame('word', $vars['docType']);
    }
}
