<?php

declare(strict_types=1);

namespace App\Services;

use App\Libraries\WebApiClient;

class SiteFormService
{
    private const CACHE_TTL = 300;

    public function __construct(private WebApiClient $apiClient)
    {
    }

    /**
     * Fetch the public form definition for the given language and form key.
     *
     * @return array<string, mixed>|null
     */
    public function getDefinition(string $lang, string $formKey): ?array
    {
        $response = $this->apiClient->get(
            "public/{$lang}/forms/{$formKey}",
            [],
            self::CACHE_TTL,
            'forms'
        );

        if (! ($response['ok'] ?? false)) {
            return null;
        }

        $definition = $response['data'] ?? null;

        return is_array($definition) ? $definition : null;
    }

    /**
     * Submit a form to the domain API.
     *
     * @param  array<string, mixed>  $formData  Sanitised field values keyed by field_key
     * @param  string|null           $captchaToken  reCAPTCHA response token (when has_captcha=true)
     * @return array{ok: bool, id: int|null, messages: list<string>}
     */
    public function submit(
        string  $formKey,
        array   $formData,
        ?string $captchaToken = null
    ): array {
        $payload = [
            'form_key'  => $formKey,
            'form_data' => $formData,
        ];

        if ($captchaToken !== null) {
            $payload['captcha_token'] = $captchaToken;
        }

        $response = $this->apiClient->post('public/submissions', $payload);

        if (! ($response['ok'] ?? false)) {
            return [
                'ok'       => false,
                'id'       => null,
                'messages' => $response['messages'] ?? ['Error al enviar el formulario.'],
            ];
        }

        $id = isset($response['data']['id']) ? (int) $response['data']['id'] : null;

        return ['ok' => true, 'id' => $id, 'messages' => []];
    }
}
