<?php

declare(strict_types=1);

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;
use Config\Services;

/**
 * Handles public contact form submissions.
 *
 * Flow:
 * 1. Validate CSRF (CI4 auto)
 * 2. Validate reCAPTCHA v3 token (if configured)
 * 3. Validate fields server-side
 * 4. POST to domain API → cms_form_submissions
 * 5. Send email to admin + autoresponder to user
 * 6. Redirect back with flash message
 */
class ContactController extends BasePublicWebController
{
    /** Minimum reCAPTCHA v3 score to accept (0.0–1.0) */
    private const RECAPTCHA_MIN_SCORE = 0.5;

    public function store(): RedirectResponse
    {
        $request = $this->request;

        // ── 1. Basic field validation ─────────────────────────────────────
        $rules = [
            'name'    => 'required|string|min_length[2]|max_length[100]',
            'email'   => 'required|valid_email|max_length[150]',
            'message' => 'required|string|min_length[10]|max_length[2000]',
            'phone'   => 'permit_empty|string|max_length[30]',
            'company' => 'permit_empty|string|max_length[100]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('contact_error', implode(' ', $this->validator->getErrors()));
        }

        // ── 2. reCAPTCHA v3 verification ─────────────────────────────────
        $recaptchaSecret = (string) env('RECAPTCHA_SECRET_KEY', '');
        if ($recaptchaSecret !== '') {
            $token = (string) $request->getPost('g_recaptcha_response');
            if (! $this->verifyRecaptcha($token, $recaptchaSecret)) {
                return redirect()->back()
                    ->withInput()
                    ->with('contact_error', 'Verificación de seguridad fallida. Por favor inténtelo de nuevo.');
            }
        }

        // ── 3. Build clean form data ──────────────────────────────────────
        $formData = [
            'name'    => strip_tags((string) $request->getPost('name')),
            'email'   => strtolower(trim((string) $request->getPost('email'))),
            'phone'   => strip_tags((string) $request->getPost('phone')),
            'company' => strip_tags((string) $request->getPost('company')),
            'message' => strip_tags((string) $request->getPost('message')),
        ];

        // ── 4. Persist via domain API ─────────────────────────────────────
        /** @var \App\Services\SiteContactService $contactService */
        $contactService = Services::siteContactService();
        $result = $contactService->submit($formData, 'contact');

        if (! $result['ok']) {
            log_message('error', '[ContactController] Domain API error: ' . implode(', ', $result['messages']));
            // Don't expose API errors to users — still show generic error
        }

        // ── 5. Send emails ────────────────────────────────────────────────
        $contactConfig = $this->resolveContactConfig();
        $this->sendAdminNotification($formData, $contactConfig);
        $this->sendUserAutoReply($formData, $contactConfig);

        // ── 6. Redirect with success ──────────────────────────────────────
        return redirect()->back()->with('contact_sent', true);
    }

    // ── Email helpers ─────────────────────────────────────────────────────

    /**
     * @param array<string, mixed> $formData
     * @param array{
     *     adminEmail: string,
     *     fromEmail: string,
     *     siteName: string,
     *     autoReplyMessage: string
     * } $contactConfig
     */
    private function sendAdminNotification(array $formData, array $contactConfig): void
    {
        $adminEmail = $contactConfig['adminEmail'] ?? '';
        if ($adminEmail === '') {
            log_message('warning', '[ContactController] CONTACT_ADMIN_EMAIL not set — admin notification skipped.');
            return;
        }

        $siteName = $contactConfig['siteName'] ?? 'Sitio Web';

        try {
            $email = \Config\Services::email();
            $email->setFrom(
                $contactConfig['fromEmail'] ?? 'no-reply@localhost',
                $siteName
            );
            $email->setTo($adminEmail);
            $email->setSubject('Nuevo mensaje de contacto — ' . esc($formData['name'] ?? ''));
            $email->setMessage($this->buildAdminEmailBody($formData, $siteName));
            $email->setMailType('html');
            $email->send();
        } catch (\Throwable $e) {
            log_message('error', '[ContactController] Admin email failed: ' . $e->getMessage());
        }
    }

    /**
     * @param array<string, mixed> $formData
     * @param array{
     *     adminEmail: string,
     *     fromEmail: string,
     *     siteName: string,
     *     autoReplyMessage: string
     * } $contactConfig
     */
    private function sendUserAutoReply(array $formData, array $contactConfig): void
    {
        $userEmail = (string) ($formData['email'] ?? '');
        if ($userEmail === '' || ! filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $siteName = $contactConfig['siteName'] ?? 'Sitio Web';
        $autoReplyMsg = $contactConfig['autoReplyMessage'] ?? 'Hemos recibido tu mensaje. Nos pondremos en contacto a la brevedad.';

        try {
            $email = \Config\Services::email();
            $email->setFrom(
                $contactConfig['fromEmail'] ?? 'no-reply@localhost',
                $siteName
            );
            $email->setTo($userEmail);
            $email->setSubject('Recibimos tu mensaje — ' . $siteName);
            $email->setMessage($this->buildAutoReplyBody($formData, $siteName, $autoReplyMsg));
            $email->setMailType('html');
            $email->send();
        } catch (\Throwable $e) {
            log_message('error', '[ContactController] Auto-reply email failed: ' . $e->getMessage());
        }
    }

    /**
     * Resolve contact defaults from CMS settings, allowing environment variables
     * to override specific values when they are explicitly configured.
     *
     * @return array{
     *     adminEmail: string,
     *     fromEmail: string,
     *     siteName: string,
     *     autoReplyMessage: string
     * }
     */
    private function resolveContactConfig(): array
    {
        /** @var \App\Services\SiteSettingsService $settingsService */
        $settingsService = Services::siteSettingsService();
        $defaults = $settingsService->getContactDefaults();

        $adminEmail = (string) env('CONTACT_ADMIN_EMAIL', '');
        $fromEmail = (string) env('CONTACT_FROM_EMAIL', '');
        $siteName = (string) env('CONTACT_SITE_NAME', '');
        $autoReplyMessage = (string) env('CONTACT_AUTOREPLY_MESSAGE', '');

        return [
            'adminEmail' => $adminEmail !== '' ? $adminEmail : $defaults['contact_admin_email'],
            'fromEmail' => $fromEmail !== '' ? $fromEmail : ($defaults['contact_from_email'] !== '' ? $defaults['contact_from_email'] : 'no-reply@localhost'),
            'siteName' => $siteName !== '' ? $siteName : ($defaults['contact_site_name'] !== '' ? $defaults['contact_site_name'] : 'Sitio Web'),
            'autoReplyMessage' => $autoReplyMessage !== '' ? $autoReplyMessage : ($defaults['contact_autoreply_message'] !== '' ? $defaults['contact_autoreply_message'] : 'Hemos recibido tu mensaje. Nos pondremos en contacto a la brevedad.'),
        ];
    }

    // ── reCAPTCHA ─────────────────────────────────────────────────────────

    private function verifyRecaptcha(string $token, string $secret): bool
    {
        if ($token === '') {
            return false;
        }

        try {
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL            => 'https://www.google.com/recaptcha/api/siteverify',
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => http_build_query(['secret' => $secret, 'response' => $token]),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 5,
            ]);
            $response = curl_exec($curl);
            curl_close($curl);

            if (! is_string($response)) {
                return false;
            }

            $data = json_decode($response, true);

            return ($data['success'] ?? false) === true
                && (float) ($data['score'] ?? 0.0) >= self::RECAPTCHA_MIN_SCORE;
        } catch (\Throwable) {
            return false;
        }
    }

    // ── Email body builders ───────────────────────────────────────────────

    /**
     * @param array<string, mixed> $formData
     */
    private function buildAdminEmailBody(array $formData, string $siteName): string
    {
        $rows = '';
        $labels = [
            'name'    => 'Nombre',
            'email'   => 'Email',
            'phone'   => 'Teléfono',
            'company' => 'Empresa',
            'message' => 'Mensaje',
        ];

        foreach ($labels as $key => $label) {
            $value = esc((string) ($formData[$key] ?? ''));
            if ($value === '') {
                continue;
            }
            $display = $key === 'message'
                ? '<pre style="white-space:pre-wrap;font-family:inherit">' . $value . '</pre>'
                : $value;
            $rows .= "<tr>
                <td style=\"padding:8px 12px;font-weight:600;color:#475569;white-space:nowrap;vertical-align:top\">{$label}</td>
                <td style=\"padding:8px 12px;color:#1e293b\">{$display}</td>
              </tr>";
        }

        return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><title>Nuevo mensaje de contacto</title></head>
<body style="font-family:system-ui,sans-serif;background:#f8fafc;margin:0;padding:24px">
  <div style="max-width:560px;margin:0 auto;background:#fff;border-radius:12px;overflow:hidden;border:1px solid #e2e8f0">
    <div style="background:#1e293b;padding:24px 28px">
      <p style="margin:0;color:#94a3b8;font-size:12px;text-transform:uppercase;letter-spacing:.1em">{$siteName}</p>
      <h1 style="margin:4px 0 0;color:#fff;font-size:20px">Nuevo mensaje de contacto</h1>
    </div>
    <div style="padding:24px 28px">
      <table style="width:100%;border-collapse:collapse">
        {$rows}
      </table>
    </div>
    <div style="padding:16px 28px;background:#f1f5f9;font-size:12px;color:#94a3b8">
      Este mensaje fue enviado desde el formulario de contacto del sitio web.
    </div>
  </div>
</body>
</html>
HTML;
    }

    /**
     * @param array<string, mixed> $formData
     */
    private function buildAutoReplyBody(array $formData, string $siteName, string $autoReplyMsg): string
    {
        $userName = esc((string) ($formData['name'] ?? 'estimado/a visitante'));

        return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><title>Recibimos tu mensaje</title></head>
<body style="font-family:system-ui,sans-serif;background:#f8fafc;margin:0;padding:24px">
  <div style="max-width:560px;margin:0 auto;background:#fff;border-radius:12px;overflow:hidden;border:1px solid #e2e8f0">
    <div style="background:#1e293b;padding:24px 28px">
      <p style="margin:0;color:#94a3b8;font-size:12px;text-transform:uppercase;letter-spacing:.1em">{$siteName}</p>
      <h1 style="margin:4px 0 0;color:#fff;font-size:20px">Gracias por escribirnos</h1>
    </div>
    <div style="padding:24px 28px;color:#334155;line-height:1.6">
      <p>Hola <strong>{$userName}</strong>,</p>
      <p>{$autoReplyMsg}</p>
    </div>
    <div style="padding:16px 28px;background:#f1f5f9;font-size:12px;color:#94a3b8">
      Por favor no respondas a este email. Si tienes más preguntas, usa el formulario de contacto.
    </div>
  </div>
</body>
</html>
HTML;
    }
}
