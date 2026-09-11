<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Shared\Security\PreviewLink;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\HermeticFeatureTestCase;

/** @internal */
final class EditorPreviewControllerTest extends HermeticFeatureTestCase
{
    use FeatureTestTrait;

    private const SECRET = 'testing-preview-secret-at-least-32-characters';
    private const OWNER_ID = 7;

    protected function setUp(): void
    {
        parent::setUp();
        $this->configureLocales(['es', 'en']);
        config('App')->editorPanelOrigin = 'http://localhost:8192';
        putenv('CMS_PREVIEW_SECRET=' . self::SECRET);
        $_ENV['CMS_PREVIEW_SECRET'] = self::SECRET;
        $_SERVER['CMS_PREVIEW_SECRET'] = self::SECRET;
    }

    protected function tearDown(): void
    {
        putenv('CMS_PREVIEW_SECRET');
        unset($_ENV['CMS_PREVIEW_SECRET'], $_SERVER['CMS_PREVIEW_SECRET']);
        parent::tearDown();
    }

    public function testSignedDocumentPreviewUsesDomainProjectionAndAnnotatesBlocks(): void
    {
        $this->domainAdapter->fakePost($this->projectionPath(), $this->document());

        $fields = $this->signedFields([
            'lang' => 'es',
            'scope' => ['type' => 'document'],
            'blocks' => [$this->block('id_7')],
        ]);
        $response = $this->withBody(json_encode($fields, JSON_THROW_ON_ERROR))
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post('/es/_editor/preview');

        $response->assertStatus(200);
        self::assertStringContainsString('no-store', $response->response()->getHeader('Cache-Control')->getValueLine());
        self::assertSame('noindex, nofollow', $response->response()->getHeaderLine('X-Robots-Tag'));
        self::assertSame('', $response->response()->getHeaderLine('X-Frame-Options'));
        self::assertStringContainsString('frame-ancestors http://localhost:8192', $response->response()->getHeaderLine('Content-Security-Policy'));
        self::assertStringContainsString('data-block-instance="id_7"', (string) $response->response()->getBody());
    }

    public function testMissingOrTamperedSignatureFailsBeforeDomainProjection(): void
    {
        $fields = $this->signedFields(['lang' => 'es', 'scope' => ['type' => 'document'], 'blocks' => []]);
        unset($fields['sig']);

        $response = $this->post('/es/_editor/preview', $fields);

        $response->assertStatus(404);
        self::assertStringContainsString('no-store', $response->response()->getHeader('Cache-Control')->getValueLine());

        $fields = $this->signedFields(['lang' => 'es', 'scope' => ['type' => 'document'], 'blocks' => []]);
        $fields['sig'] = str_repeat('0', 64);

        $this->post('/es/_editor/preview', $fields)->assertStatus(404);
    }

    public function testExpiredSignatureAndContradictoryLocaleAreRejected(): void
    {
        $expired = time() - 1;
        $fields = [
            'owner_type' => 'page',
            'owner_id' => (string) self::OWNER_ID,
            'expires' => (string) $expired,
            'sig' => hash_hmac('sha256', 'editor:page:' . self::OWNER_ID . ':' . $expired, self::SECRET),
            'payload' => json_encode(['lang' => 'es', 'scope' => ['type' => 'document'], 'blocks' => []], JSON_THROW_ON_ERROR),
        ];

        $this->post('/es/_editor/preview', $fields)->assertStatus(404);

        $this->domainAdapter->fakePost($this->projectionPath(), $this->document());
        $this->post('/es/_editor/preview', $this->signedFields([
            'lang' => 'en',
            'scope' => ['type' => 'document'],
            'blocks' => [],
        ]))->assertStatus(422);
    }

    public function testDomainErrorsBecomeSafeResponses(): void
    {
        $this->domainAdapter->fakePostFailure($this->projectionPath(), 500);
        $response = $this->post('/es/_editor/preview', $this->signedFields([
            'lang' => 'es',
            'scope' => ['type' => 'document'],
            'blocks' => [],
        ]));

        $response->assertStatus(502);
        self::assertStringNotContainsString('Upstream failure', (string) $response->response()->getBody());
    }

    public function testPreviewRemainsUnframeableWhenPanelOriginIsNotConfigured(): void
    {
        config('App')->editorPanelOrigin = '';
        $this->domainAdapter->fakePost($this->projectionPath(), $this->document());

        $response = $this->post('/es/_editor/preview', $this->signedFields([
            'lang' => 'es',
            'scope' => ['type' => 'document'],
            'blocks' => [],
        ]));

        $response->assertStatus(200);
        self::assertSame('DENY', $response->response()->getHeaderLine('X-Frame-Options'));
        self::assertStringContainsString("frame-ancestors 'none'", $response->response()->getHeaderLine('Content-Security-Policy'));
    }

    public function testOversizedDraftIsRejected(): void
    {
        config('App')->editorPreviewMaxPayloadBytes = 32;
        $response = $this->post('/es/_editor/preview', $this->signedFields([
            'lang' => 'es',
            'scope' => ['type' => 'document'],
            'blocks' => [],
            'padding' => str_repeat('x', 100),
        ]));

        $response->assertStatus(413);
    }

    /** @param array<string, mixed> $draft
     *  @return array<string, mixed>
     */
    private function signedFields(array $draft): array
    {
        $token = PreviewLink::sign('editor', 'page:' . self::OWNER_ID);
        self::assertIsArray($token);

        return [
            'owner_type' => 'page',
            'owner_id' => (string) self::OWNER_ID,
            'expires' => $token['expires'],
            'sig' => $token['sig'],
            'payload' => json_encode($draft, JSON_THROW_ON_ERROR),
        ];
    }

    /** @return array<string, mixed> */
    private function document(): array
    {
        return [
            'lang' => 'es',
            'blocks' => [$this->block('id_7')],
            'scopeType' => 'document',
            'scopeRef' => null,
            'fallbackRefs' => [],
        ];
    }

    /** @return array<string, mixed> */
    private function block(string $reference): array
    {
        return [
            'editor_ref' => $reference,
            'block_key' => 'container',
            'block_config' => [],
            'block_data' => [],
            'children' => [],
        ];
    }

    private function projectionPath(): string
    {
        return 'public/editor-projection/pages/' . self::OWNER_ID;
    }
}
