<?php
/**
 * WhatsApp Service via Whatspie API
 */

namespace App\Services;

use App\Core\DB;
use App\Repositories\SettingsRepo;

class WhatsAppService
{
    public static function send(string $to, string $message): bool
    {
        $apiUrl = SettingsRepo::get('wa_api_url');
        $token = \App\Core\Crypto::decrypt(SettingsRepo::get('wa_token') ?? '');
        $deviceId = SettingsRepo::get('wa_device_id');

        if (empty($apiUrl) || empty($token)) {
            self::log('whatsapp', $to, substr($message, 0, 100), 'fail', 'Missing API configuration');
            return false;
        }

        $payload = [
            'receiver' => $to,
            'type' => 'chat',
            'params' => [
                'text' => $message,
            ],
            'simulate_typing' => 1,
        ];

        if (!empty($deviceId)) {
            $payload['device'] = $deviceId;
        }

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $token,
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        $success = $httpCode >= 200 && $httpCode < 300 && empty($error);

        self::log(
            'whatsapp',
            $to,
            substr($message, 0, 100),
            $success ? 'sent' : 'fail',
            $success ? null : ('HTTP ' . $httpCode . ': ' . ($error ?: substr($response, 0, 200)))
        );

        return $success;
    }

    public static function sendBulk(array $recipients, string $message): array
    {
        $results = [];
        foreach ($recipients as $recipient) {
            $results[$recipient] = self::send($recipient, $message);
        }
        return $results;
    }

    public static function sendTestMessage(): bool
    {
        $recipients = SettingsRepo::get('wa_recipients');
        if (empty($recipients)) {
            throw new \Exception('No recipients configured');
        }

        $numbers = array_map('trim', explode(',', $recipients));
        $testMessage = __('wa.test_message');

        $results = [];
        foreach ($numbers as $number) {
            if (empty($number)) continue;
            $results[] = self::send($number, $testMessage);
        }

        return in_array(true, $results, true);
    }

    public static function sendDailySummary(int $expiredCount, int $expiringCount, int $days): bool
    {
        $recipients = SettingsRepo::get('wa_recipients');
        if (empty($recipients)) {
            return false;
        }

        $numbers = array_map('trim', explode(',', $recipients));

        $message = "📋 *" . __('wa.daily_heading') . "*\n\n";
        $message .= "🔴 " . __('wa.expired_count', ['count' => $expiredCount]) . "\n";
        $message .= "🟡 " . __('wa.expiring_count', ['days' => $days, 'count' => $expiringCount]) . "\n\n";
        $message .= __('wa.view_dashboard');

        $results = [];
        foreach ($numbers as $number) {
            if (empty($number)) continue;
            $results[] = self::send($number, $message);
        }

        return in_array(true, $results, true);
    }

    /**
     * Send comprehensive daily summary covering all modules
     */
    public static function sendComprehensiveSummary(array $data): bool
    {
        $recipients = SettingsRepo::get('wa_recipients');
        if (empty($recipients)) {
            return false;
        }

        $message = self::buildComprehensiveMessage($data);

        $numbers = array_map('trim', explode(',', $recipients));
        $results = [];
        foreach ($numbers as $number) {
            if (empty($number)) continue;
            $results[] = self::send($number, $message);
        }

        return in_array(true, $results, true);
    }

    /**
     * Build comprehensive WhatsApp message
     */
    public static function buildComprehensiveMessage(array $data): string
    {
        $defaultCurrency = SettingsRepo::get('default_currency', 'EGP');
        $message = "📋 *" . __('wa.comp_heading') . "*\n";
        $message .= date('Y-m-d') . "\n\n";

        // Services
        $services = $data['services'] ?? [];
        $expired = count($services['recentlyExpired'] ?? []);
        $expiring = count($services['expiringSoon'] ?? []);
        $days = $services['reminderDays'] ?? 30;

        if ($expired > 0 || $expiring > 0) {
            $message .= "*" . __('wa.services') . "*\n";
            if ($expired > 0) $message .= "🔴 " . __('wa.expired_recently', ['count' => $expired]) . "\n";
            if ($expiring > 0) $message .= "🟡 " . __('wa.expiring_soon', ['days' => $days, 'count' => $expiring]) . "\n";
            $message .= "\n";
        }

        // Dues
        $dues = $data['dues'] ?? [];
        $overdueDues = count($dues['overdue'] ?? []);
        $pendingByCurrency = $dues['totalPendingByCurrency'] ?? [];
        $hasPending = !empty(array_filter($pendingByCurrency, fn($v) => (float)$v['total'] != 0));

        if ($overdueDues > 0 || $hasPending) {
            $message .= "*" . __('wa.dues') . "*\n";
            if ($overdueDues > 0) $message .= "🔴 " . __('wa.overdue', ['count' => $overdueDues]) . "\n";
            if ($hasPending) {
                $parts = [];
                foreach ($pendingByCurrency as $cv) {
                    if ((float)$cv['total'] == 0) continue;
                    $parts[] = number_format((float)$cv['total'], 0) . ' ' . $cv['currency_code'];
                }
                $message .= "💰 " . __('wa.pending', ['amount' => implode(' | ', $parts)]) . "\n";
            }
            $message .= "\n";
        }

        // Expenses
        $expenses = $data['expenses'] ?? [];
        $overdueExp = count($expenses['overdue'] ?? []);
        $upcomingExp = count($expenses['upcoming'] ?? []);

        if ($overdueExp > 0 || $upcomingExp > 0) {
            $message .= "*" . __('wa.expenses') . "*\n";
            if ($overdueExp > 0) $message .= "🔴 " . __('wa.overdue', ['count' => $overdueExp]) . "\n";
            if ($upcomingExp > 0) $message .= "⏰ " . __('wa.upcoming_week', ['count' => $upcomingExp]) . "\n";
            $message .= "\n";
        }

        // Tasks
        $overdueTasks = count($data['tasks']['overdue'] ?? []);
        if ($overdueTasks > 0) {
            $message .= "*" . __('wa.tasks') . "*\n";
            $message .= "🔴 " . __('wa.overdue', ['count' => $overdueTasks]) . "\n\n";
        }

        // Notes
        $notes = $data['notes'] ?? [];
        $overdueNotes = count($notes['overdue'] ?? []);
        $upcomingNotes = count($notes['upcoming'] ?? []);

        if ($overdueNotes > 0 || $upcomingNotes > 0) {
            $message .= "*" . __('wa.reminders') . "*\n";
            if ($overdueNotes > 0) $message .= "🔴 " . __('wa.overdue', ['count' => $overdueNotes]) . "\n";
            if ($upcomingNotes > 0) $message .= "📌 " . __('wa.upcoming', ['count' => $upcomingNotes]) . "\n";
            $message .= "\n";
        }

        $message .= __('wa.view_details');

        return $message;
    }

    private static function log(string $channel, string $target, string $summary, string $status, ?string $error): void
    {
        DB::insert('notification_logs', [
            'channel' => $channel,
            'target' => $target,
            'payload_summary' => $summary,
            'status' => $status,
            'error_message' => $error,
        ]);
    }
}
