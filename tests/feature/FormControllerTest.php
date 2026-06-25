<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\SiteFormService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;

/**
 * @internal
 */
final class FormControllerTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function setUp(): void
    {
        parent::setUp();

        Services::resetSingle('siteFormService');
    }

    protected function tearDown(): void
    {
        Services::resetSingle('siteFormService');

        parent::tearDown();
    }

    public function testSubmitValidationFailsWhenRequiredFieldIsEmpty(): void
    {
        $formService = $this->createMock(SiteFormService::class);
        $formService->expects($this->once())
            ->method('getDefinition')
            ->with('es', 'contact')
            ->willReturn([
                'form_key' => 'contact',
                'fields'   => [
                    [
                        'field_key'      => 'name',
                        'field_type'     => 'text',
                        'is_required'    => 1,
                        'error_required' => 'Nombre es requerido.',
                    ],
                ],
            ]);

        Services::injectMock('siteFormService', $formService);

        $result = $this->withHeaders(['Referer' => 'http://localhost:8186/contacto'])
            ->post('forms/contact/submit', [
                'name' => '',
            ]);

        $result->assertStatus(302);
        $errors = session()->getFlashdata('form_errors_contact');
        $this->assertArrayHasKey('name', $errors);
        $this->assertSame('Nombre es requerido.', $errors['name']);
    }

    public function testSubmitValidationFailsWhenEmailIsInvalid(): void
    {
        $formService = $this->createMock(SiteFormService::class);
        $formService->expects($this->once())
            ->method('getDefinition')
            ->with('es', 'contact')
            ->willReturn([
                'form_key' => 'contact',
                'fields'   => [
                    [
                        'field_key'     => 'email',
                        'field_type'    => 'email',
                        'is_required'   => 1,
                        'error_invalid' => 'Email no válido.',
                    ],
                ],
            ]);

        Services::injectMock('siteFormService', $formService);

        $result = $this->withHeaders(['Referer' => 'http://localhost:8186/contacto'])
            ->post('forms/contact/submit', [
                'email' => 'not-an-email',
            ]);

        $result->assertStatus(302);
        $errors = session()->getFlashdata('form_errors_contact');
        $this->assertArrayHasKey('email', $errors);
        $this->assertSame('Email no válido.', $errors['email']);
    }

    public function testSubmitSuccess(): void
    {
        $formService = $this->createMock(SiteFormService::class);
        $formService->expects($this->once())
            ->method('getDefinition')
            ->with('es', 'contact')
            ->willReturn([
                'form_key' => 'contact',
                'fields'   => [
                    [
                        'field_key'   => 'name',
                        'field_type'  => 'text',
                        'is_required' => 1,
                    ],
                    [
                        'field_key'   => 'email',
                        'field_type'  => 'email',
                        'is_required' => 1,
                    ],
                ],
            ]);

        $formService->expects($this->once())
            ->method('submit')
            ->with(
                'contact',
                [
                    'name'  => 'Ada Lovelace',
                    'email' => 'ada@example.com',
                ],
                null
            )
            ->willReturn([
                'ok'       => true,
                'id'       => 123,
                'messages' => [],
            ]);

        Services::injectMock('siteFormService', $formService);

        $result = $this->withHeaders(['Referer' => 'http://localhost:8186/contacto'])
            ->post('forms/contact/submit', [
                'name'  => 'Ada Lovelace',
                'email' => 'ada@example.com',
            ]);

        $result->assertStatus(302);
        $this->assertTrue(session()->getFlashdata('form_sent_contact'));
    }
}
