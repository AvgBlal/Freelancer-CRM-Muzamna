<?php
/**
 * Expense Repository
 * Track money you owe / your business costs
 */

namespace App\Repositories;

use App\Core\DB;

class ExpenseRepo
{
    public static function getAll(array $filters = [], int $page = 1, int $perPage = 25): array
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = "status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['category'])) {
            $where[] = "category = :category";
            $params['category'] = $filters['category'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(title LIKE :search1 OR vendor LIKE :search2)";
            $params['search1'] = '%' . $filters['search'] . '%';
            $params['search2'] = '%' . $filters['search'] . '%';
        }

        $whereStr = implode(' AND ', $where);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT * FROM expenses
                WHERE {$whereStr}
                ORDER BY
                    CASE status WHEN 'pending' THEN 1 WHEN 'paid' THEN 2 WHEN 'cancelled' THEN 3 END,
                    due_date ASC,
                    created_at DESC
                LIMIT :limit OFFSET :offset";

        $params['limit'] = $perPage;
        $params['offset'] = $offset;

        return DB::fetchAll($sql, $params);
    }

    public static function find(int $id): ?array
    {
        return DB::fetch("SELECT * FROM expenses WHERE id = :id", ['id' => $id]);
    }

    public static function create(array $data): int
    {
        $fields = [
            'title' => $data['title'],
            'category' => $data['category'] ?? 'other',
            'amount' => $data['amount'],
            'currency_code' => $data['currency_code'] ?? 'EGP',
            'due_date' => $data['due_date'] ?: null,
            'status' => $data['status'] ?? 'pending',
            'vendor' => $data['vendor'] ?: null,
            'is_recurring' => !empty($data['is_recurring']) ? 1 : 0,
            'billing_cycle' => $data['billing_cycle'] ?: null,
            'notes' => $data['notes'] ?: null,
        ];

        return DB::insert('expenses', $fields);
    }

    public static function update(int $id, array $data): void
    {
        $fields = [
            'title' => $data['title'],
            'category' => $data['category'] ?? 'other',
            'amount' => $data['amount'],
            'currency_code' => $data['currency_code'] ?? 'EGP',
            'due_date' => $data['due_date'] ?: null,
            'status' => $data['status'] ?? 'pending',
            'vendor' => $data['vendor'] ?: null,
            'is_recurring' => !empty($data['is_recurring']) ? 1 : 0,
            'billing_cycle' => $data['billing_cycle'] ?: null,
            'notes' => $data['notes'] ?: null,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if (($data['status'] ?? '') === 'paid') {
            $fields['paid_at'] = date('Y-m-d H:i:s');
        }

        DB::update('expenses', $fields, 'id = :id', ['id' => $id]);
    }

    public static function markPaid(int $id): void
    {
        DB::update('expenses', [
            'status' => 'paid',
            'paid_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = :id', ['id' => $id]);
    }

    public static function delete(int $id): void
    {
        DB::delete('expenses', 'id = :id', ['id' => $id]);
    }

    public static function getStats(): array
    {
        $totalPendingByCurrency = DB::fetchAll(
            "SELECT currency_code, COALESCE(SUM(amount), 0) as total FROM expenses WHERE status = 'pending'
             GROUP BY currency_code ORDER BY total DESC"
        );

        $pendingCount = DB::fetch(
            "SELECT COUNT(*) as count FROM expenses WHERE status = 'pending'"
        )['count'] ?? 0;

        $paidThisMonthByCurrency = DB::fetchAll(
            "SELECT currency_code, COALESCE(SUM(amount), 0) as total FROM expenses
             WHERE status = 'paid' AND paid_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
             GROUP BY currency_code ORDER BY total DESC"
        );

        $recurringMonthlyByCurrency = DB::fetchAll(
            "SELECT currency_code, COALESCE(SUM(CASE
                WHEN billing_cycle = 'monthly' THEN amount
                WHEN billing_cycle = 'yearly' THEN amount / 12
                ELSE 0
            END), 0) as total
            FROM expenses WHERE status = 'pending' AND is_recurring = 1
            GROUP BY currency_code ORDER BY total DESC"
        );

        return compact('totalPendingByCurrency', 'pendingCount', 'paidThisMonthByCurrency', 'recurringMonthlyByCurrency');
    }

    public static function getUpcoming(int $days = 30): array
    {
        $sql = "SELECT *, DATEDIFF(due_date, CURDATE()) as days_until
                FROM expenses
                WHERE status = 'pending'
                AND due_date IS NOT NULL
                AND due_date <= DATE_ADD(CURDATE(), INTERVAL :days DAY)
                AND due_date >= CURDATE()
                ORDER BY due_date ASC";

        return DB::fetchAll($sql, ['days' => $days]);
    }

    public static function getOverdue(): array
    {
        $sql = "SELECT *, DATEDIFF(CURDATE(), due_date) as days_overdue
                FROM expenses
                WHERE status = 'pending'
                AND due_date IS NOT NULL AND due_date < CURDATE()
                ORDER BY due_date ASC";

        return DB::fetchAll($sql);
    }

    /** Monthly totals for the finance page */
    public static function getMonthlyTotals(int $months = 12): array
    {
        $sql = "SELECT
                    DATE_FORMAT(paid_at, '%Y-%m') as month,
                    SUM(amount) as total
                FROM expenses
                WHERE status = 'paid'
                AND paid_at >= DATE_SUB(CURDATE(), INTERVAL :months MONTH)
                GROUP BY DATE_FORMAT(paid_at, '%Y-%m')
                ORDER BY month ASC";

        return DB::fetchAll($sql, ['months' => $months]);
    }

    /** Totals by category */
    public static function getTotalsByCategory(): array
    {
        $sql = "SELECT category, SUM(amount) as total, COUNT(*) as count
                FROM expenses
                WHERE status = 'paid'
                GROUP BY category
                ORDER BY total DESC";

        return DB::fetchAll($sql);
    }
}
