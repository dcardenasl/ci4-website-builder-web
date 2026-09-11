<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\EditorBlockAnnotator;
use App\Shared\Security\PreviewLink;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

/** Renders a signed, unsaved editor draft with the production Web renderer. */
final class EditorPreviewController extends BasePublicWebController
{
    public function preview(string $locale): ResponseInterface
    {
        if (! in_array($locale, config('App')->supportedLocales, true)) {
            return $this->problem(404, lang('Editor.previewDenied'));
        }

        $input = $this->input();
        $owner = $this->authorizedOwner($input);
        if ($owner === null) {
            return $this->problem(404, lang('Editor.previewDenied'));
        }

        if (($input['__too_large'] ?? false) === true) {
            return $this->problem(413, lang('Editor.payloadTooLarge'));
        }

        $draft = $this->draft($input);
        if ($draft === null || ($draft['lang'] ?? null) !== $locale) {
            return $this->problem(422, lang('Editor.invalidPayload'));
        }

        $result = Services::editorPreviewService()->project($owner['type'], $owner['id'], $draft);
        if (! $result['ok']) {
            return $this->upstreamProblem($result['status']);
        }

        $document = is_array($result['data'] ?? null) ? $result['data'] : null;
        $blocks = is_array($document['blocks'] ?? null) && array_is_list($document['blocks'])
            ? $document['blocks']
            : null;
        if ($document === null || $blocks === null || ($document['lang'] ?? null) !== $locale) {
            return $this->problem(502, lang('Editor.previewUnavailable'));
        }

        $annotator = new EditorBlockAnnotator();
        $renderer = Services::blockRenderer(false);
        $renderer->setEditorAnnotator($annotator);
        $rendered = $renderer->render($blocks, $locale);

        if (($document['scopeType'] ?? 'document') === 'block') {
            $reference = is_string($document['scopeRef'] ?? null) ? $document['scopeRef'] : '';
            $fragment = $reference !== '' ? $annotator->fragment($reference) : null;

            return $fragment === null
                ? $this->problem(404, lang('Editor.invalidPayload'))
                : $this->html($fragment);
        }

        $response = $this->render('page', [
            'title' => '',
            'excerpt' => '',
            'showPageHeading' => false,
            'pageTitle' => lang('Editor.previewTitle'),
            'metaDescription' => '',
            'metaRobots' => 'noindex, nofollow',
            'schemaData' => null,
            'renderedBlocks' => $rendered,
            'localized_urls' => [],
            'contentLocale' => $locale,
            'cacheScopes' => [],
        ]);

        return $this->noStore($response->setContentType('text/html', 'UTF-8'));
    }

    /** @return array<string, mixed> */
    private function input(): array
    {
        $contentType = strtolower($this->request->getHeaderLine('Content-Type'));
        $body = (string) $this->request->getBody();
        if (str_contains($contentType, 'application/json') || str_starts_with(ltrim($body), '{')) {
            if ($this->payloadTooLarge($body)) {
                return ['__too_large' => true];
            }

            $decoded = json_decode($body, true);

            return is_array($decoded) && ! array_is_list($decoded) ? $decoded : [];
        }

        $post = (array) $this->request->getPost();
        $encoded = is_string($post['payload'] ?? null) ? $post['payload'] : '';
        if ($this->payloadTooLarge($encoded)) {
            $post['__too_large'] = true;
        }

        return $post;
    }

    /** @param array<string, mixed> $input
     *  @return array{type: string, id: int}|null
     */
    private function authorizedOwner(array $input): ?array
    {
        $type = is_string($input['owner_type'] ?? null) ? $input['owner_type'] : '';
        $idRaw = $input['owner_id'] ?? null;
        $id = is_int($idRaw) ? $idRaw : (is_string($idRaw) && ctype_digit($idRaw) ? (int) $idRaw : 0);
        $expires = $input['expires'] ?? null;
        $signature = $input['sig'] ?? null;

        if (! in_array($type, ['page', 'entry'], true) || $id < 1
            || ! is_scalar($expires) || ! is_string($signature)) {
            return null;
        }

        return PreviewLink::verify(
            'editor',
            $type . ':' . $id,
            (string) $expires,
            $signature,
        ) ? ['type' => $type, 'id' => $id] : null;
    }

    /** @param array<string, mixed> $input
     *  @return array<string, mixed>|null
     */
    private function draft(array $input): ?array
    {
        if (($input['__too_large'] ?? false) === true) {
            return null;
        }

        $payload = $input['payload'] ?? null;
        if (is_string($payload)) {
            $payload = json_decode($payload, true);
        }

        return is_array($payload) && ! array_is_list($payload) ? $payload : null;
    }

    private function payloadTooLarge(string $payload): bool
    {
        return strlen($payload) > config('App')->editorPreviewMaxPayloadBytes;
    }

    private function html(string $body): ResponseInterface
    {
        return $this->noStore($this->response->setContentType('text/html', 'UTF-8')->setBody($body));
    }

    private function problem(int $status, string $message): ResponseInterface
    {
        return $this->noStore($this->response->setStatusCode($status)->setJSON(['error' => $message]));
    }

    private function upstreamProblem(int $status): ResponseInterface
    {
        if ($status === 404) {
            return $this->problem(404, lang('Editor.previewDenied'));
        }
        if ($status >= 400 && $status < 500) {
            return $this->problem(422, lang('Editor.invalidPayload'));
        }

        return $this->problem(502, lang('Editor.previewUnavailable'));
    }

    private function noStore(ResponseInterface $response): ResponseInterface
    {
        return $response
            ->noCache()
            ->setHeader('X-Robots-Tag', 'noindex, nofollow');
    }
}
