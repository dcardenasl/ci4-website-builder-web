<?php

declare(strict_types=1);

namespace App\Services;

use App\Libraries\WebApiClient;

/**
 * Sends a contact form submission to the CMS domain API.
 */
class SiteContactService
{
    public function __construct(private WebApiClient $apiClient)
    {
    }

    /**
     * Submit a contact form to the domain API.
     *
     * @param  array<string, mixed>  $formData  Sanitised form fields
     * @return array{ok: bool, id: int|null, messages: list<string>}
     */
    public function submit(
        array  $formData,
        string $formKey = 'contact',
        ?int   $languageId = null
    ): array {
        $response = $this->apiClient->post('public/submissions', [
            'form_key'    => $formKey,
            'language_id' => $languageId,
            'form_data'   => $formData,
        ]);

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
