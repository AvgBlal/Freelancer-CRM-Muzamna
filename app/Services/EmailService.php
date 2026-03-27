<?php
/**
 * Email Service
 * Supports native mail() or SMTP (fsockopen with STARTTLS/SSL)
 * Config is read from the Settings table in the database
 */

namespace App\Services;

use App\Core\DB;
use App\Repositories\SettingsRepo;

class EmailService
{
    private static ?string $lastError = null;

    public static function send(string $to, string $subject, string $body, bool $isHtml = true): bool
    {
        self::$lastError = null;
        $config = self::getConfig();

        if ($config['driver'] === 'smtp') {
            return self::sendViaSmtp($to, $subject, $body, $isHtml, $config);
        }

        return self::sendViaNative($to, $subject, $body, $isHtml, $config);
    }

    public static function getLastError(): ?string
    {
        return self::$lastError;
    }

    // ── Native mail() ────────────────────────────────────────────────

    private static function sendViaNative(string $to, string $subject, string $body, bool $isHtml, array $config): bool
    {
        $from = $config['from_address'] ?: 'noreply@localhost';
        $fromName = $config['from_name'] ?: __('app.name');

        $headers = "From: =?UTF-8?B?" . base64_encode($fromName) . "?= <{$from}>\r\n";
        $headers .= "Reply-To: {$from}\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";

        if ($isHtml) {
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        } else {
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        }

        $encodedSubject = "=?UTF-8?B?" . base64_encode($subject) . "?=";

        $result = @mail($to, $encodedSubject, $body, $headers);

        if (!$result) {
            self::$lastError = 'mail() returned false — check server sendmail config';
        }

        self::log($to, $subject, $result ? 'sent' : 'fail', self::$lastError);
        return $result;
    }

    // ── SMTP via fsockopen ───────────────────────────────────────────

    private static function sendViaSmtp(string $to, string $subject, string $body, bool $isHtml, array $config): bool
    {
        $host = $config['smtp_host'];
        $port = (int) $config['smtp_port'];
        $username = $config['smtp_username'];
        $password = $config['smtp_password'];
        $encryption = $config['smtp_encryption']; // 'tls', 'ssl', or ''
        $from = $config['from_address'] ?: $username;
        $fromName = $config['from_name'] ?: __('app.name');

        if (empty($host) || empty($username) || empty($password)) {
            self::$lastError = 'SMTP config incomplete: host, username, or password missing';
            self::log($to, $subject, 'fail', self::$lastError);
            return false;
        }

        try {
            // Connect
            $connectHost = ($encryption === 'ssl') ? 'ssl://' . $host : $host;
            $errno = 0;
            $errstr = '';
            $socket = @fsockopen($connectHost, $port, $errno, $errstr, 15);

            if (!$socket) {
                self::$lastError = "SMTP connect failed: {$errstr} ({$errno})";
                self::log($to, $subject, 'fail', self::$lastError);
                return false;
            }

            stream_set_timeout($socket, 30);

            // Read greeting
            $greeting = self::smtpRead($socket);
            if (strpos($greeting, '220') !== 0) {
                self::$lastError = "SMTP bad greeting: {$greeting}";
                fclose($socket);
                self::log($to, $subject, 'fail', self::$lastError);
                return false;
            }

            // EHLO
            $ehloHost = $host;
            self::smtpWrite($socket, "EHLO {$ehloHost}");
            $ehloResponse = self::smtpRead($socket);

            // STARTTLS if needed
            if ($encryption === 'tls') {
                self::smtpWrite($socket, "STARTTLS");
                $tlsResponse = self::smtpRead($socket);
                if (strpos($tlsResponse, '220') !== 0) {
                    self::$lastError = "STARTTLS rejected: {$tlsResponse}";
                    fclose($socket);
                    self::log($to, $subject, 'fail', self::$lastError);
                    return false;
                }

                $crypto = stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT);
                if (!$crypto) {
                    self::$lastError = 'STARTTLS crypto handshake failed';
                    fclose($socket);
                    self::log($to, $subject, 'fail', self::$lastError);
                    return false;
                }

                // Re-EHLO after TLS
                self::smtpWrite($socket, "EHLO {$ehloHost}");
                self::smtpRead($socket);
            }

            // AUTH LOGIN
            self::smtpWrite($socket, "AUTH LOGIN");
            $authResponse = self::smtpRead($socket);
            if (strpos($authResponse, '334') !== 0) {
                self::$lastError = "AUTH LOGIN rejected: {$authResponse}";
                fclose($socket);
                self::log($to, $subject, 'fail', self::$lastError);
                return false;
            }

            // Username
            self::smtpWrite($socket, base64_encode($username));
            $userResponse = self::smtpRead($socket);
            if (strpos($userResponse, '334') !== 0) {
                self::$lastError = "AUTH username rejected: {$userResponse}";
                fclose($socket);
                self::log($to, $subject, 'fail', self::$lastError);
                return false;
            }

            // Password
            self::smtpWrite($socket, base64_encode($password));
            $passResponse = self::smtpRead($socket);
            if (strpos($passResponse, '235') !== 0) {
                self::$lastError = "AUTH failed (bad credentials): {$passResponse}";
                fclose($socket);
                self::log($to, $subject, 'fail', self::$lastError);
                return false;
            }

            // MAIL FROM
            self::smtpWrite($socket, "MAIL FROM:<{$from}>");
            $fromResponse = self::smtpRead($socket);
            if (strpos($fromResponse, '250') !== 0) {
                self::$lastError = "MAIL FROM rejected: {$fromResponse}";
                fclose($socket);
                self::log($to, $subject, 'fail', self::$lastError);
                return false;
            }

            // RCPT TO
            self::smtpWrite($socket, "RCPT TO:<{$to}>");
            $rcptResponse = self::smtpRead($socket);
            if (strpos($rcptResponse, '250') !== 0 && strpos($rcptResponse, '251') !== 0) {
                self::$lastError = "RCPT TO rejected: {$rcptResponse}";
                fclose($socket);
                self::log($to, $subject, 'fail', self::$lastError);
                return false;
            }

            // DATA
            self::smtpWrite($socket, "DATA");
            $dataResponse = self::smtpRead($socket);
            if (strpos($dataResponse, '354') !== 0) {
                self::$lastError = "DATA rejected: {$dataResponse}";
                fclose($socket);
                self::log($to, $subject, 'fail', self::$lastError);
                return false;
            }

            // Build message
            $encodedSubject = "=?UTF-8?B?" . base64_encode($subject) . "?=";
            $encodedFromName = "=?UTF-8?B?" . base64_encode($fromName) . "?=";
            $contentType = $isHtml ? 'text/html' : 'text/plain';
            $boundary = md5(uniqid(time()));

            $message = "From: {$encodedFromName} <{$from}>\r\n";
            $message .= "To: {$to}\r\n";
            $message .= "Subject: {$encodedSubject}\r\n";
            $message .= "MIME-Version: 1.0\r\n";
            $message .= "Content-Type: {$contentType}; charset=UTF-8\r\n";
            $message .= "Content-Transfer-Encoding: base64\r\n";
            $message .= "Date: " . date('r') . "\r\n";
            $message .= "Message-ID: <" . $boundary . "@{$host}>\r\n";
            $message .= "\r\n";
            $message .= chunk_split(base64_encode($body));
            $message .= "\r\n.";

            self::smtpWrite($socket, $message);
            $sendResponse = self::smtpRead($socket);
            if (strpos($sendResponse, '250') !== 0) {
                self::$lastError = "Message rejected: {$sendResponse}";
                fclose($socket);
                self::log($to, $subject, 'fail', self::$lastError);
                return false;
            }

            // QUIT
            self::smtpWrite($socket, "QUIT");
            fclose($socket);

            self::log($to, $subject, 'sent', null);
            return true;

        } catch (\Throwable $e) {
            self::$lastError = 'SMTP exception: ' . $e->getMessage();
            self::log($to, $subject, 'fail', self::$lastError);
            return false;
        }
    }

    private static function smtpWrite($socket, string $data): void
    {
        fwrite($socket, $data . "\r\n");
    }

    private static function smtpRead($socket): string
    {
        $response = '';
        while ($line = fgets($socket, 512)) {
            $response .= $line;
            // SMTP multiline: 4th char is '-' for continuation, ' ' for last line
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }
        return trim($response);
    }

    // ── Config from Settings DB ──────────────────────────────────────

    private static function getConfig(): array
    {
        $driver = SettingsRepo::get('email_driver', 'mail');

        return [
            'driver' => $driver,
            'smtp_host' => SettingsRepo::get('smtp_host', ''),
            'smtp_port' => SettingsRepo::get('smtp_port', '587'),
            'smtp_username' => SettingsRepo::get('smtp_username', ''),
            'smtp_password' => \App\Core\Crypto::decrypt(SettingsRepo::get('smtp_password', '')),
            'smtp_encryption' => SettingsRepo::get('smtp_encryption', 'tls'),
            'from_address' => SettingsRepo::get('email_from_address', ''),
            'from_name' => SettingsRepo::get('email_from_name', __('app.name')),
        ];
    }

    // ── Test Connection ──────────────────────────────────────────────

    public static function sendTestEmail(string $to): bool
    {
        $subject = __('email.test_subject');
        $body = '<!DOCTYPE html><html dir="rtl" lang="ar"><head><meta charset="UTF-8"></head><body>';
        $body .= '<h2>' . __('email.test_heading') . '</h2>';
        $body .= '<p>' . __('email.test_body') . '</p>';
        $body .= '<p>' . __('email.time') . ': ' . date('Y-m-d H:i:s') . '</p>';
        $body .= '<p>' . __('email.method') . ': ' . (self::getConfig()['driver'] === 'smtp' ? 'SMTP' : 'mail()') . '</p>';
        $body .= '</body></html>';

        return self::send($to, $subject, $body, true);
    }

    // ── Report Builders (unchanged) ──────────────────────────────────

    public static function sendDailyReport(string $to, array $recentlyExpired, array $expiringSoon, int $days): bool
    {
        $subject = __('email.daily_subject');
        $body = self::buildReportBody($recentlyExpired, $expiringSoon, $days);
        return self::send($to, $subject, $body, true);
    }

    private static function buildReportBody(array $recentlyExpired, array $expiringSoon, int $days): string
    {
        $html = '<!DOCTYPE html><html dir="rtl" lang="ar"><head><meta charset="UTF-8"><style>';
        $html .= 'body { font-family: Arial, sans-serif; direction: rtl; }';
        $html .= 'table { width: 100%; border-collapse: collapse; margin: 20px 0; }';
        $html .= 'th, td { padding: 10px; border: 1px solid #ddd; text-align: right; }';
        $html .= 'th { background-color: #f5f5f5; }';
        $html .= '.urgent { color: #d32f2f; }';
        $html .= '.warning { color: #f57c00; }';
        $html .= '</style></head><body>';

        $html .= '<h1>' . __('email.daily_heading') . '</h1>';

        $html .= '<h2>' . __('email.expired_section') . '</h2>';
        if (empty($recentlyExpired)) {
            $html .= '<p>' . __('email.no_expired') . '</p>';
        } else {
            $html .= '<table><thead><tr><th>' . __('email.th_service') . '</th><th>' . __('email.th_client') . '</th><th>' . __('email.th_end_date') . '</th></tr></thead><tbody>';
            foreach ($recentlyExpired as $service) {
                $html .= '<tr class="urgent">';
                $html .= '<td>' . htmlspecialchars($service['title']) . '</td>';
                $html .= '<td>' . htmlspecialchars($service['client_names'] ?? '-') . '</td>';
                $html .= '<td>' . $service['end_date'] . '</td>';
                $html .= '</tr>';
            }
            $html .= '</tbody></table>';
        }

        $html .= '<h2>' . __('email.expiring_section', ['days' => $days]) . '</h2>';
        if (empty($expiringSoon)) {
            $html .= '<p>' . __('email.no_expiring') . '</p>';
        } else {
            $html .= '<table><thead><tr><th>' . __('email.th_service') . '</th><th>' . __('email.th_client') . '</th><th>' . __('email.th_end_date') . '</th></tr></thead><tbody>';
            foreach ($expiringSoon as $service) {
                $html .= '<tr class="warning">';
                $html .= '<td>' . htmlspecialchars($service['title']) . '</td>';
                $html .= '<td>' . htmlspecialchars($service['client_names'] ?? '-') . '</td>';
                $html .= '<td>' . $service['end_date'] . '</td>';
                $html .= '</tr>';
            }
            $html .= '</tbody></table>';
        }

        $html .= '<hr><p>' . __('email.footer') . '</p>';
        $html .= '</body></html>';

        return $html;
    }

    public static function sendComprehensiveReport(string $to, array $data): bool
    {
        $subject = __('email.comp_subject');
        $body = self::buildComprehensiveBody($data);
        return self::send($to, $subject, $body, true);
    }

    public static function buildComprehensiveBody(array $data): string
    {
        $defaultCurrency = SettingsRepo::get('default_currency', 'EGP');
        $html = '<!DOCTYPE html><html dir="rtl" lang="ar"><head><meta charset="UTF-8"><style>';
        $html .= 'body { font-family: Arial, sans-serif; direction: rtl; max-width: 700px; margin: 0 auto; }';
        $html .= 'table { width: 100%; border-collapse: collapse; margin: 10px 0; }';
        $html .= 'th, td { padding: 8px; border: 1px solid #ddd; text-align: right; }';
        $html .= 'th { background-color: #f5f5f5; }';
        $html .= '.section { margin: 20px 0; padding: 15px; border: 1px solid #e0e0e0; border-radius: 4px; }';
        $html .= '.urgent { color: #d32f2f; } .warning { color: #f57c00; } .ok { color: #2e7d32; }';
        $html .= 'h1 { color: #1565c0; } h2 { color: #333; border-bottom: 2px solid #e0e0e0; padding-bottom: 5px; }';
        $html .= '</style></head><body>';

        $html .= '<h1>' . __('email.comp_heading') . '</h1>';
        $html .= '<p>' . __('email.comp_date') . ': ' . date('Y-m-d H:i') . '</p>';

        $services = $data['services'] ?? [];
        $expired = $services['recentlyExpired'] ?? [];
        $expiring = $services['expiringSoon'] ?? [];
        $days = $services['reminderDays'] ?? 30;

        $html .= '<div class="section"><h2>' . __('email.services_section') . '</h2>';
        if (!empty($expired)) {
            $html .= '<h3 class="urgent">' . __('email.expired_recently', ['count' => count($expired)]) . '</h3>';
            $html .= '<table><thead><tr><th>' . __('email.th_service') . '</th><th>' . __('email.th_client') . '</th><th>' . __('email.th_end_date') . '</th></tr></thead><tbody>';
            foreach ($expired as $s) {
                $html .= '<tr><td>' . htmlspecialchars($s['title']) . '</td><td>' . htmlspecialchars($s['client_names'] ?? '-') . '</td><td>' . $s['end_date'] . '</td></tr>';
            }
            $html .= '</tbody></table>';
        }
        if (!empty($expiring)) {
            $html .= '<h3 class="warning">' . __('email.expiring_soon', ['days' => $days, 'count' => count($expiring)]) . '</h3>';
            $html .= '<table><thead><tr><th>' . __('email.th_service') . '</th><th>' . __('email.th_client') . '</th><th>' . __('email.th_end_date') . '</th></tr></thead><tbody>';
            foreach ($expiring as $s) {
                $html .= '<tr><td>' . htmlspecialchars($s['title']) . '</td><td>' . htmlspecialchars($s['client_names'] ?? '-') . '</td><td>' . $s['end_date'] . '</td></tr>';
            }
            $html .= '</tbody></table>';
        }
        if (empty($expired) && empty($expiring)) {
            $html .= '<p class="ok">' . __('email.no_attention') . '</p>';
        }
        $html .= '</div>';

        $dues = $data['dues'] ?? [];
        $overdueDues = $dues['overdue'] ?? [];
        $pendingByCurrency = $dues['totalPendingByCurrency'] ?? [];
        $hasPending = !empty(array_filter($pendingByCurrency, fn($v) => (float)$v['total'] != 0));
        if (!empty($overdueDues) || $hasPending) {
            $html .= '<div class="section"><h2>' . __('email.dues_section') . '</h2>';
            if ($hasPending) {
                $parts = [];
                foreach ($pendingByCurrency as $cv) {
                    if ((float)$cv['total'] == 0) continue;
                    $parts[] = number_format((float)$cv['total'], 0) . ' ' . $cv['currency_code'];
                }
                $html .= '<p>' . __('email.total_pending') . ': <strong>' . implode(' / ', $parts) . '</strong></p>';
            }
            if (!empty($overdueDues)) {
                $html .= '<h3 class="urgent">' . __('email.overdue_count', ['count' => count($overdueDues)]) . '</h3><table><thead><tr><th>' . __('email.th_person') . '</th><th>' . __('email.th_amount') . '</th><th>' . __('email.overdue_by') . '</th></tr></thead><tbody>';
                foreach ($overdueDues as $d) { $r = (float)$d['amount'] - (float)($d['paid_amount'] ?? 0); $html .= '<tr><td>' . htmlspecialchars($d['person_name']) . '</td><td>' . number_format($r, 0) . ' ' . ($d['currency_code'] ?? $defaultCurrency) . '</td><td>' . ($d['days_overdue'] ?? '?') . ' ' . __('email.days_unit') . '</td></tr>'; }
                $html .= '</tbody></table>';
            }
            $html .= '</div>';
        }

        $expenses = $data['expenses'] ?? [];
        $overdueExpenses = $expenses['overdue'] ?? [];
        $upcomingExpenses = $expenses['upcoming'] ?? [];
        if (!empty($overdueExpenses) || !empty($upcomingExpenses)) {
            $html .= '<div class="section"><h2>' . __('email.expenses_section') . '</h2>';
            if (!empty($overdueExpenses)) {
                $html .= '<h3 class="urgent">' . __('email.overdue_count', ['count' => count($overdueExpenses)]) . '</h3><table><thead><tr><th>' . __('email.th_expense') . '</th><th>' . __('email.th_amount') . '</th><th>' . __('email.overdue_by') . '</th></tr></thead><tbody>';
                foreach ($overdueExpenses as $e) { $html .= '<tr><td>' . htmlspecialchars($e['title']) . '</td><td>' . number_format((float)$e['amount'], 0) . ' ' . ($e['currency_code'] ?? $defaultCurrency) . '</td><td>' . ($e['days_overdue'] ?? '?') . ' ' . __('email.days_unit') . '</td></tr>'; }
                $html .= '</tbody></table>';
            }
            if (!empty($upcomingExpenses)) {
                $html .= '<h3 class="warning">' . __('email.upcoming_week', ['count' => count($upcomingExpenses)]) . '</h3><table><thead><tr><th>' . __('email.th_expense') . '</th><th>' . __('email.th_amount') . '</th><th>' . __('email.th_date') . '</th></tr></thead><tbody>';
                foreach ($upcomingExpenses as $e) { $html .= '<tr><td>' . htmlspecialchars($e['title']) . '</td><td>' . number_format((float)$e['amount'], 0) . ' ' . ($e['currency_code'] ?? $defaultCurrency) . '</td><td>' . $e['due_date'] . '</td></tr>'; }
                $html .= '</tbody></table>';
            }
            $html .= '</div>';
        }

        $tasks = $data['tasks'] ?? [];
        $overdueTasks = $tasks['overdue'] ?? [];
        if (!empty($overdueTasks)) {
            $html .= '<div class="section"><h2 class="urgent">' . __('email.overdue_tasks', ['count' => count($overdueTasks)]) . '</h2><table><thead><tr><th>' . __('email.th_task') . '</th><th>' . __('email.th_assignee') . '</th><th>' . __('email.overdue_by') . '</th></tr></thead><tbody>';
            foreach ($overdueTasks as $t) { $html .= '<tr><td>' . htmlspecialchars($t['title']) . '</td><td>' . htmlspecialchars($t['assignee_name'] ?? '-') . '</td><td>' . ($t['days_overdue'] ?? '?') . ' ' . __('email.days_unit') . '</td></tr>'; }
            $html .= '</tbody></table></div>';
        }

        $notes = $data['notes'] ?? [];
        $overdueNotes = $notes['overdue'] ?? [];
        $upcomingNotes = $notes['upcoming'] ?? [];
        if (!empty($overdueNotes) || !empty($upcomingNotes)) {
            $html .= '<div class="section"><h2>' . __('email.reminders_section') . '</h2>';
            if (!empty($overdueNotes)) { $html .= '<h3 class="urgent">' . __('email.overdue_reminders', ['count' => count($overdueNotes)]) . '</h3><ul>'; foreach ($overdueNotes as $n) { $html .= '<li>' . htmlspecialchars($n['title']) . ' (' . __('email.overdue_label') . ' ' . ($n['days_overdue'] ?? '?') . ' ' . __('email.days_unit') . ')</li>'; } $html .= '</ul>'; }
            if (!empty($upcomingNotes)) { $html .= '<h3 class="warning">' . __('email.upcoming_reminders') . '</h3><ul>'; foreach ($upcomingNotes as $n) { $html .= '<li>' . htmlspecialchars($n['title']) . ' (' . $n['due_date'] . ')</li>'; } $html .= '</ul>'; }
            $html .= '</div>';
        }

        $html .= '<hr><p style="color:#999;font-size:12px;">' . __('email.comp_footer') . ' — ' . date('Y-m-d H:i') . '</p>';
        $html .= '</body></html>';

        return $html;
    }

    // ── Logging ──────────────────────────────────────────────────────

    private static function log(string $target, string $summary, string $status, ?string $error): void
    {
        DB::insert('notification_logs', [
            'channel' => 'email',
            'target' => $target,
            'payload_summary' => substr($summary, 0, 100),
            'status' => $status,
            'error_message' => $error,
        ]);
    }
}
