<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\SiteContactService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;

/**
 * @internal
 */
final class ContactControllerTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function setUp(): void
    {
        parent::setUp();

        Services::resetSingle('siteSettingsService');
        Services::resetSingle('siteContactService');
        Services::resetSingle('email');
    }

    protected function tearDown(): void
    {
        Services::resetSingle('siteSettingsService');
        Services::resetSingle('siteContactService');
        Services::resetSingle('email');

        parent::tearDown();
    }

    public function testStoreUsesCmsContactDefaultsAndRedirectsBack(): void
    {
        $contactService = $this->createMock(SiteContactService::class);
        $contactService->expects($this->once())
            ->method('submit')
            ->with(
                $this->callback(static function (array $formData): bool {
                    return $formData['name'] === 'Ada Lovelace'
                        && $formData['email'] === 'ada@example.com'
                        && $formData['message'] === 'Quiero más información.'
                        && $formData['phone'] === '123456'
                        && $formData['company'] === 'Analytical Engines';
                }),
                'contact'
            )
            ->willReturn(['ok' => true, 'id' => 99, 'messages' => []]);

        $email = new class () {
            /** @var list<array{method: string, args: array<int, mixed>}> */
            public array $calls = [];

            public function setFrom(string $address, string $name): self
            {
                $this->calls[] = ['method' => 'setFrom', 'args' => [$address, $name]];

                return $this;
            }

            public function setTo(string $address): self
            {
                $this->calls[] = ['method' => 'setTo', 'args' => [$address]];

                return $this;
            }

            public function setSubject(string $subject): self
            {
                $this->calls[] = ['method' => 'setSubject', 'args' => [$subject]];

                return $this;
            }

            public function setMessage(string $message): self
            {
                $this->calls[] = ['method' => 'setMessage', 'args' => [$message]];

                return $this;
            }

            public function setMailType(string $type): self
            {
                $this->calls[] = ['method' => 'setMailType', 'args' => [$type]];

                return $this;
            }

            public function send(): bool
            {
                $this->calls[] = ['method' => 'send', 'args' => []];

                return true;
            }
        };

        Services::injectMock('siteContactService', $contactService);
        Services::injectMock('email', $email);

        $result = $this->withHeaders(['Referer' => 'http://example.com/contacto'])
            ->post('contacto/enviar', [
                'name' => 'Ada Lovelace',
                'email' => 'ada@example.com',
                'message' => 'Quiero más información.',
                'phone' => '123456',
                'company' => 'Analytical Engines',
            ]);

        $result->assertStatus(302);
        $location = $result->response()->getHeaderLine('Location');
        $this->assertNotSame('', $location);
        $this->assertSame('/', (string) parse_url($location, PHP_URL_PATH));
        $this->assertTrue(session()->getFlashdata('contact_sent'));

        $this->assertCount(12, $email->calls);
        $this->assertSame(['method' => 'setFrom', 'args' => ['no-reply@localhost', 'Sitio Web']], $email->calls[0]);
        $this->assertSame(['method' => 'setTo', 'args' => ['admin@example.com']], $email->calls[1]);
        $this->assertSame(['method' => 'setSubject', 'args' => ['Nuevo mensaje de contacto — Ada Lovelace']], $email->calls[2]);
        $this->assertSame('setMessage', $email->calls[3]['method']);
        $this->assertStringContainsString('Ada Lovelace', $email->calls[3]['args'][0]);
        $this->assertSame(['method' => 'setMailType', 'args' => ['html']], $email->calls[4]);
        $this->assertSame(['method' => 'send', 'args' => []], $email->calls[5]);
        $this->assertSame(['method' => 'setFrom', 'args' => ['no-reply@localhost', 'Sitio Web']], $email->calls[6]);
        $this->assertSame(['method' => 'setTo', 'args' => ['ada@example.com']], $email->calls[7]);
        $this->assertSame(['method' => 'setSubject', 'args' => ['Recibimos tu mensaje — Sitio Web']], $email->calls[8]);
        $this->assertSame('setMessage', $email->calls[9]['method']);
        $this->assertStringContainsString('Hemos recibido tu mensaje y nos pondremos en contacto a la brevedad.', $email->calls[9]['args'][0]);
        $this->assertSame(['method' => 'setMailType', 'args' => ['html']], $email->calls[10]);
        $this->assertSame(['method' => 'send', 'args' => []], $email->calls[11]);
    }
}
