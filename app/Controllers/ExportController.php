<?php
/**
 * Export Controller
 * CSV and PDF export for major modules
 */

namespace App\Controllers;

use App\Core\Auth;
use App\Core\DB;

class ExportController
{
    // ── Data Fetchers (shared by CSV + PDF) ──────────────────────────

    private static function fetchClients(): array
    {
        return DB::fetchAll(
            "SELECT c.id, c.name, c.type, c.email, c.phone, c.website, c.country, c.city,
                    c.timezone, c.preferred_channel, c.notes, c.created_at,
                    GROUP_CONCAT(t.name SEPARATOR ', ') as tags
             FROM clients c
             LEFT JOIN client_tags ct ON c.id = ct.client_id
             LEFT JOIN tags t ON ct.tag_id = t.id
             GROUP BY c.id
             ORDER BY c.name"
        );
    }

    private static function fetchServices(): array
    {
        return DB::fetchAll(
            "SELECT s.id, s.title, s.type, s.status, s.start_date, s.end_date,
                    s.billing_cycle, s.price_amount, s.currency_code,
                    s.is_personal, s.auto_renew, s.created_at,
                    GROUP_CONCAT(c.name SEPARATOR ', ') as client_names
             FROM services s
             LEFT JOIN service_clients sc ON s.id = sc.service_id
             LEFT JOIN clients c ON sc.client_id = c.id
             GROUP BY s.id
             ORDER BY s.end_date ASC"
        );
    }

    private static function fetchDues(): array
    {
        return DB::fetchAll(
            "SELECT id, person_name, amount, paid_amount, currency_code,
                    status, due_date, paid_at, notes, created_at
             FROM dues
             ORDER BY due_date DESC"
        );
    }

    private static function fetchExpenses(): array
    {
        return DB::fetchAll(
            "SELECT id, title, category, amount, currency_code, status,
                    due_date, paid_at, is_recurring, billing_cycle,
                    vendor, notes, created_at
             FROM expenses
             ORDER BY due_date DESC"
        );
    }

    private static function fetchTasks(): array
    {
        return DB::fetchAll(
            "SELECT t.id, t.title, t.status, t.priority, t.progress_pct as progress,
                    t.start_date, t.due_date, t.estimated_hours, t.actual_hours,
                    u.name as assignee, c.name as client_name, p.title as project_title,
                    t.created_at
             FROM tasks t
             LEFT JOIN users u ON t.assigned_to = u.id
             LEFT JOIN clients c ON t.client_id = c.id
             LEFT JOIN projects p ON t.project_id = p.id
             ORDER BY t.due_date DESC"
        );
    }

    // ── Module Config ────────────────────────────────────────────────

    private static function moduleConfig(): array
    {
        return [
            'clients' => [
                'title' => __('export.clients'),
                'headers' => ['#', __('clients.name'), __('clients.type'), __('auth.email'), __('common.phone'), __('clients.website'), __('clients.country'), __('clients.city'), __('clients.timezone'), __('clients.channel'), __('common.notes'), __('common.created_at'), __('clients.tags')],
                'fields' => ['id', 'name', 'type', 'email', 'phone', 'website', 'country', 'city', 'timezone', 'preferred_channel', 'notes', 'created_at', 'tags'],
            ],
            'services' => [
                'title' => __('export.services'),
                'headers' => ['#', __('services.service_title'), __('services.service_type'), __('common.status'), __('services.start_date'), __('services.end_date'), __('services.billing_cycle'), __('common.amount'), __('common.currency'), __('common.personal'), __('services.auto_renew'), __('common.created_at'), __('common.client')],
                'fields' => ['id', 'title', 'type', 'status', 'start_date', 'end_date', 'billing_cycle', 'price_amount', 'currency_code', 'is_personal', 'auto_renew', 'created_at', 'client_names'],
            ],
            'dues' => [
                'title' => __('export.dues'),
                'headers' => ['#', __('dues.person_name'), __('common.amount'), __('common.paid'), __('common.currency'), __('common.status'), __('common.due_date'), __('common.date'), __('common.notes'), __('common.created_at')],
                'fields' => ['id', 'person_name', 'amount', 'paid_amount', 'currency_code', 'status', 'due_date', 'paid_at', 'notes', 'created_at'],
            ],
            'expenses' => [
                'title' => __('export.expenses'),
                'headers' => ['#', __('expenses.expense_title'), __('expenses.category'), __('common.amount'), __('common.currency'), __('common.status'), __('common.due_date'), __('common.date'), __('expenses.recurring'), __('expenses.recurring_cycle'), __('expenses.vendor'), __('common.description'), __('common.created_at')],
                'fields' => ['id', 'title', 'category', 'amount', 'currency_code', 'status', 'due_date', 'paid_at', 'is_recurring', 'billing_cycle', 'vendor', 'notes', 'created_at'],
            ],
            'tasks' => [
                'title' => __('export.tasks'),
                'headers' => ['#', __('tasks.task_title'), __('common.status'), __('common.type'), __('common.progress'), __('tasks.start_date'), __('common.due_date'), __('tasks.estimated_hours'), __('tasks.actual_hours'), __('tasks.assignee'), __('common.client'), __('common.project'), __('common.created_at')],
                'fields' => ['id', 'title', 'status', 'priority', 'progress', 'start_date', 'due_date', 'estimated_hours', 'actual_hours', 'assignee', 'client_name', 'project_title', 'created_at'],
            ],
        ];
    }

    // ── CSV Endpoints ────────────────────────────────────────────────

    public function clients(): void
    {
        Auth::requireAuth();
        $config = self::moduleConfig()['clients'];
        self::outputCsv('clients_' . date('Y-m-d'), $config['headers'], self::fetchClients(), $config['fields']);
    }

    public function services(): void
    {
        Auth::requireAuth();
        $config = self::moduleConfig()['services'];
        self::outputCsv('services_' . date('Y-m-d'), $config['headers'], self::fetchServices(), $config['fields']);
    }

    public function dues(): void
    {
        Auth::requireAuth();
        $config = self::moduleConfig()['dues'];
        self::outputCsv('dues_' . date('Y-m-d'), $config['headers'], self::fetchDues(), $config['fields']);
    }

    public function expenses(): void
    {
        Auth::requireAuth();
        $config = self::moduleConfig()['expenses'];
        self::outputCsv('expenses_' . date('Y-m-d'), $config['headers'], self::fetchExpenses(), $config['fields']);
    }

    public function tasks(): void
    {
        Auth::requireAuth();
        $config = self::moduleConfig()['tasks'];
        self::outputCsv('tasks_' . date('Y-m-d'), $config['headers'], self::fetchTasks(), $config['fields']);
    }

    // ── PDF Endpoints (print-optimized HTML) ─────────────────────────

    public function clientsPdf(): void
    {
        Auth::requireAuth();
        $config = self::moduleConfig()['clients'];
        $rows = self::fetchClients();
        $reportTitle = $config['title'];
        $headers = $config['headers'];
        $fields = $config['fields'];
        $filename = 'clients_' . date('Y-m-d');
        require __DIR__ . '/../Views/export/print.php';
    }

    public function servicesPdf(): void
    {
        Auth::requireAuth();
        $config = self::moduleConfig()['services'];
        $rows = self::fetchServices();
        $reportTitle = $config['title'];
        $headers = $config['headers'];
        $fields = $config['fields'];
        $filename = 'services_' . date('Y-m-d');
        require __DIR__ . '/../Views/export/print.php';
    }

    public function duesPdf(): void
    {
        Auth::requireAuth();
        $config = self::moduleConfig()['dues'];
        $rows = self::fetchDues();
        $reportTitle = $config['title'];
        $headers = $config['headers'];
        $fields = $config['fields'];
        $filename = 'dues_' . date('Y-m-d');
        require __DIR__ . '/../Views/export/print.php';
    }

    public function expensesPdf(): void
    {
        Auth::requireAuth();
        $config = self::moduleConfig()['expenses'];
        $rows = self::fetchExpenses();
        $reportTitle = $config['title'];
        $headers = $config['headers'];
        $fields = $config['fields'];
        $filename = 'expenses_' . date('Y-m-d');
        require __DIR__ . '/../Views/export/print.php';
    }

    public function tasksPdf(): void
    {
        Auth::requireAuth();
        $config = self::moduleConfig()['tasks'];
        $rows = self::fetchTasks();
        $reportTitle = $config['title'];
        $headers = $config['headers'];
        $fields = $config['fields'];
        $filename = 'tasks_' . date('Y-m-d');
        require __DIR__ . '/../Views/export/print.php';
    }

    // ── CSV Output ───────────────────────────────────────────────────

    private static function outputCsv(string $filename, array $headers, array $rows, array $fields): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        header('Cache-Control: no-cache, no-store, must-revalidate');

        $output = fopen('php://output', 'w');

        // UTF-8 BOM for Excel
        fwrite($output, "\xEF\xBB\xBF");

        fputcsv($output, $headers);

        foreach ($rows as $row) {
            $line = [];
            foreach ($fields as $field) {
                $line[] = $row[$field] ?? '';
            }
            fputcsv($output, $line);
        }

        fclose($output);
        exit;
    }
}
