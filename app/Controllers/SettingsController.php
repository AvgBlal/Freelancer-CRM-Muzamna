<?php
/**
 * Settings Controller
 */

namespace App\Controllers;

use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Response;
use App\Core\Validator;
use App\Repositories\SettingsRepo;
use App\Services\WhatsAppService;
use App\Services\EmailService;

class SettingsController
{
    public function index(): void
    {
        Auth::requireAuth();

        $settings = SettingsRepo::getAll();

        // Decrypt sensitive fields for display
        $encryptedKeys = ['smtp_password', 'wa_token'];
        foreach ($encryptedKeys as $key) {
            if (!empty($settings[$key])) {
                $settings[$key] = \App\Core\Crypto::decrypt($settings[$key]);
            }
        }

        $csrf = CSRF::field();

        require __DIR__ . '/../Views/settings/index.php';
    }

    public function update(): void
    {
        Auth::requireAuth();
        CSRF::verifyRequest();

        $allowedSettings = [
            'default_currency',
            'reminder_days',
            'email_enabled',
            'email_to',
            'email_driver',
            'smtp_host',
            'smtp_port',
            'smtp_username',
            'smtp_password',
            'smtp_encryption',
            'email_from_name',
            'email_from_address',
            'wa_enabled',
            'wa_api_url',
            'wa_token',
            'wa_device_id',
            'wa_recipients',
            'custom_css',
            'login_custom_html',
        ];

        $encryptedKeys = ['smtp_password', 'wa_token'];

        foreach ($allowedSettings as $key) {
            $value = $_POST[$key] ?? '';
            if (in_array($key, $encryptedKeys) && $value !== '') {
                $value = \App\Core\Crypto::encrypt($value);
            }
            SettingsRepo::set($key, $value);
        }

        Response::withSuccess(__('settings.saved'));
        Response::redirect('/settings');
    }

    public function testEmail(): void
    {
        Auth::requireAuth();
        CSRF::verifyRequest();

        $to = SettingsRepo::get('email_to', '');
        if (empty($to)) {
            Response::withError(__('settings.email_not_set'));
            Response::redirect('/settings');
            return;
        }

        try {
            $result = EmailService::sendTestEmail($to);
            if ($result) {
                Response::withSuccess(__('settings.test_sent') . ' ' . $to);
            } else {
                $error = EmailService::getLastError() ?? __('settings.unknown_error');
                Response::withError(__('settings.email_fail') . ' ' . $error);
            }
        } catch (\Exception $e) {
            Response::withError(__('settings.error_prefix') . ' ' . $e->getMessage());
        }

        Response::redirect('/settings');
    }

    public function testWhatsApp(): void
    {
        Auth::requireAuth();
        CSRF::verifyRequest();

        try {
            $result = WhatsAppService::sendTestMessage();
            if ($result) {
                Response::withSuccess(__('settings.test_sent_ok'));
            } else {
                Response::withError(__('settings.test_fail'));
            }
        } catch (\Exception $e) {
            Response::withError(__('settings.error_prefix') . ' ' . $e->getMessage());
        }

        Response::redirect('/settings');
    }
}
