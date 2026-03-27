<?php
/**
 * Unpaid Task Repository
 * Track unquoted emergency work done for clients
 */

namespace App\Repositories;

use App\Core\DB;

class UnpaidTaskRepo
{
    public static function getAll(array $filters = [], int $page = 1, int $perPage = 25): array
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['client_id'])) {
            $where[] = "ut.client_id = :client_id";
            $params['client_id'] = $filters['client_id'];
        }

        if (!empty($filters['status'])) {
            $where[] = "ut.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(ut.title LIKE :search OR ut.description LIKE :search2)";
            $params['search'] = '%' . $filters['search'] . '%';
            $params['search2'] = '%' . $filters['search'] . '%';
        }

        $whereStr = implode(' AND ', $where);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT ut.*, c.name as client_name, u.name as assignee_name
                FROM unpaid_tasks ut
                JOIN clients c ON ut.client_id = c.id
                LEFT JOIN users u ON ut.assigned_to = u.id
                WHERE {$whereStr}
                ORDER BY
                    CASE ut.status
                        WHEN 'pending' THEN 1
                        WHEN 'quoted' THEN 2
                        WHEN 'invoiced' THEN 3
                        WHEN 'paid' THEN 4
                        WHEN 'cancelled' THEN 5
                    END,
                    ut.created_at DESC
                LIMIT :limit OFFSET :offset";

        $params['limit'] = $perPage;
        $params['offset'] = $offset;

        return DB::fetchAll($sql, $params);
    }

    public static function find(int $id): ?array
    {
        $sql = "SELECT ut.*, c.name as client_name, u.name as assignee_name
                FROM unpaid_tasks ut
                JOIN clients c ON ut.client_id = c.id
                LEFT JOIN users u ON ut.assigned_to = u.id
                WHERE ut.id = :id";

        return DB::fetch($sql, ['id' => $id]);
    }

    public static function getByClient(int $clientId): array
    {
        $sql = "SELECT ut.*, u.name as assignee_name
                FROM unpaid_tasks ut
                LEFT JOIN users u ON ut.assigned_to = u.id
                WHERE ut.client_id = :client_id
                ORDER BY
                    CASE ut.status
                        WHEN 'pending' THEN 1
                        WHEN 'quoted' THEN 2
                        WHEN 'invoiced' THEN 3
                        WHEN 'paid' THEN 4
                        WHEN 'cancelled' THEN 5
                    END,
                    ut.created_at DESC";

        return DB::fetchAll($sql, ['client_id' => $clientId]);
    }

    public static function create(array $data): int
    {
        $fields = [
            'client_id' => $data['client_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?: null,
            'hours' => $data['hours'] ?: 0,
            'total_cost' => $data['total_cost'] ?: 0,
            'currency_code' => $data['currency_code'] ?? 'EGP',
            'assigned_to' => $data['assigned_to'] ?: null,
            'attachment' => $data['attachment'] ?? null,
        ];

        return DB::insert('unpaid_tasks', $fields);
    }

    public static function update(int $id, array $data): void
    {
        $fields = [
            'client_id' => $data['client_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?: null,
            'hours' => $data['hours'] ?: 0,
            'total_cost' => $data['total_cost'] ?: 0,
            'currency_code' => $data['currency_code'] ?? 'EGP',
            'assigned_to' => $data['assigned_to'] ?: null,
            'status' => $data['status'] ?? 'pending',
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if (!empty($data['attachment'])) {
            $fields['attachment'] = $data['attachment'];
        }

        DB::update('unpaid_tasks', $fields, 'id = :id', ['id' => $id]);
    }

    public static function updateStatus(int $id, string $status): void
    {
        DB::update('unpaid_tasks', [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = :id', ['id' => $id]);
    }

    public static function delete(int $id): void
    {
        DB::delete('unpaid_tasks', 'id = :id', ['id' => $id]);
    }

    public static function getStatsByClient(int $clientId): array
    {
        $row = DB::fetch(
            "SELECT
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pendingCount,
                COALESCE(SUM(CASE WHEN status = 'pending' THEN hours END), 0) as totalHours,
                COALESCE(SUM(CASE WHEN status = 'pending' THEN total_cost END), 0) as totalPending
             FROM unpaid_tasks WHERE client_id = :client_id",
            ['client_id' => $clientId]
        );

        return $row ?: ['pendingCount' => 0, 'totalHours' => 0, 'totalPending' => 0];
    }
}
