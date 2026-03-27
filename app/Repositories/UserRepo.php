<?php
/**
 * User Repository
 */

namespace App\Repositories;

use App\Core\DB;

class UserRepo
{
    /**
     * Get all users (employees)
     */
    public static function getAll(array $filters = [], int $page = 1, int $perPage = 25): array
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['role'])) {
            $where[] = "role = :role";
            $params['role'] = $filters['role'];
        }

        if (!empty($filters['department'])) {
            $where[] = "department = :department";
            $params['department'] = $filters['department'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(u.name LIKE :search1 OR u.email LIKE :search2)";
            $params['search1'] = '%' . $filters['search'] . '%';
            $params['search2'] = '%' . $filters['search'] . '%';
        }

        $whereStr = implode(' AND ', $where);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT u.*,
                COUNT(DISTINCT t.id) as active_tasks
                FROM users u
                LEFT JOIN tasks t ON u.id = t.assigned_to
                    AND t.status NOT IN ('completed', 'cancelled')
                WHERE {$whereStr}
                GROUP BY u.id
                ORDER BY u.name ASC
                LIMIT :limit OFFSET :offset";

        $params['limit'] = $perPage;
        $params['offset'] = $offset;

        return DB::fetchAll($sql, $params);
    }

    /**
     * Find user by ID
     */
    public static function find(int $id): ?array
    {
        return DB::fetch("SELECT * FROM users WHERE id = :id", ['id' => $id]);
    }

    /**
     * Find user by email
     */
    public static function findByEmail(string $email): ?array
    {
        return DB::fetch("SELECT * FROM users WHERE email = :email", ['email' => $email]);
    }

    /**
     * Create new user
     */
    public static function create(array $data): int
    {
        $fields = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'role' => $data['role'] ?? 'employee',
            'department' => $data['department'] ?? null,
            'max_tasks_capacity' => $data['max_tasks_capacity'] ?? 5,
            'avatar' => $data['avatar'] ?? null,
            'is_active' => !empty($data['is_active']) ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        return DB::insert('users', $fields);
    }

    /**
     * Update user
     */
    public static function update(int $id, array $data): void
    {
        $fields = [
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'] ?? 'employee',
            'department' => $data['department'] ?? null,
            'max_tasks_capacity' => $data['max_tasks_capacity'] ?? 5,
            'is_active' => !empty($data['is_active']) ? 1 : 0,
        ];

        // Only update password if provided
        if (!empty($data['password'])) {
            $fields['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        DB::update('users', $fields, 'id = :id', ['id' => $id]);
    }

    /**
     * Delete user
     */
    public static function delete(int $id): void
    {
        DB::delete('users', 'id = :id', ['id' => $id]);
    }

    /**
     * Get departments list
     */
    public static function getDepartments(): array
    {
        $sql = "SELECT DISTINCT department FROM users WHERE department IS NOT NULL AND department != '' ORDER BY department";
        $results = DB::fetchAll($sql);
        return array_column($results, 'department');
    }

    /**
     * Toggle user active status
     */
    public static function toggleActive(int $id): void
    {
        $user = self::find($id);
        if ($user) {
            $newStatus = $user['is_active'] ? 0 : 1;
            DB::update('users', ['is_active' => $newStatus], 'id = :id', ['id' => $id]);
        }
    }
}
