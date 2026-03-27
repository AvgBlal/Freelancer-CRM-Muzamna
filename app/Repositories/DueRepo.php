<?php
/**
 * Due Repository
 * Personal money tracking (who owes you)
 */

namespace App\Repositories;

use App\Core\DB;

class DueRepo
{
    public static function getAll(array $filters = [], int $page = 1, int $perPage = 25): array
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = "status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(person_name LIKE :search1 OR description LIKE :search2)";
            $params['search1'] = '%' . $filters['search'] . '%';
            $params['search2'] = '%' . $filters['search'] . '%';
        }

        $whereStr = implode(' AND ', $where);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT * FROM dues
                WHERE {$whereStr}
                ORDER BY
                    CASE status
                        WHEN 'pending' THEN 1
                        WHEN 'partial' THEN 2
                        WHEN 'paid' THEN 3
                        WHEN 'cancelled' THEN 4
                    END,
                    due_date ASC,
                    created_at DESC
                LIMIT :limit OFFSET :offset";

        $params['limit'] = $perPage;
        $params['offset'] = $offset;

        return DB::fetchAll($sql, $params);
    }

    public static function find(int $id): ?array
    {
        return DB::fetch("SELECT * FROM dues WHERE id = :id", ['id' => $id]);
    }

    public static function create(array $data): int
    {
        $fields = [
            'person_name' => $data['person_name'],
            'person_phone' => $data['person_phone'] ?: null,
            'description' => $data['description'] ?: null,
            'amount' => $data['amount'],
            'currency_code' => $data['currency_code'] ?? 'EGP',
            'due_date' => $data['due_date'] ?: null,
            'status' => $data['status'] ?? 'pending',
            'paid_amount' => $data['paid_amount'] ?? 0,
            'notes' => $data['notes'] ?: null,
        ];

        return DB::insert('dues', $fields);
    }

    public static function update(int $id, array $data): void
    {
        $fields = [
            'person_name' => $data['person_name'],
            'person_phone' => $data['person_phone'] ?: null,
            'description' => $data['description'] ?: null,
            'amount' => $data['amount'],
            'currency_code' => $data['currency_code'] ?? 'EGP',
            'due_date' => $data['due_date'] ?: null,
            'status' => $data['status'] ?? 'pending',
            'paid_amount' => $data['paid_amount'] ?? 0,
            'notes' => $data['notes'] ?: null,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Set paid_at when fully paid
        if (($data['status'] ?? '') === 'paid') {
            $fields['paid_at'] = date('Y-m-d H:i:s');
        }

        DB::update('dues', $fields, 'id = :id', ['id' => $id]);
    }

    public static function markPaid(int $id): void
    {
        $due = self::find($id);
        if (!$due) return;

        $fields = [
            'status' => 'paid',
            'paid_amount' => $due['amount'],
            'paid_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        DB::update('dues', $fields, 'id = :id', ['id' => $id]);
    }

    public static function delete(int $id): void
    {
        DB::delete('dues', 'id = :id', ['id' => $id]);
    }

    public static function getStats(): array
    {
        $totalPendingByCurrency = DB::fetchAll(
            "SELECT currency_code, COALESCE(SUM(amount - paid_amount), 0) as total
             FROM dues WHERE status IN ('pending', 'partial')
             GROUP BY currency_code ORDER BY total DESC"
        );

        $pendingCount = DB::fetch(
            "SELECT COUNT(*) as count FROM dues WHERE status IN ('pending', 'partial')"
        )['count'] ?? 0;

        $overdueCount = DB::fetch(
            "SELECT COUNT(*) as count FROM dues
             WHERE status IN ('pending', 'partial')
             AND due_date IS NOT NULL AND due_date < CURDATE()"
        )['count'] ?? 0;

        $paidThisMonthByCurrency = DB::fetchAll(
            "SELECT currency_code, COALESCE(SUM(paid_amount), 0) as total
             FROM dues WHERE status = 'paid'
             AND paid_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
             GROUP BY currency_code ORDER BY total DESC"
        );

        return compact('totalPendingByCurrency', 'pendingCount', 'overdueCount', 'paidThisMonthByCurrency');
    }

    public static function getOverdue(): array
    {
        $sql = "SELECT *, DATEDIFF(CURDATE(), due_date) as days_overdue
                FROM dues
                WHERE status IN ('pending', 'partial')
                AND due_date IS NOT NULL AND due_date < CURDATE()
                ORDER BY due_date ASC";

        return DB::fetchAll($sql);
    }
}
