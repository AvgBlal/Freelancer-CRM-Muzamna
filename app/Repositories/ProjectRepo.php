<?php
/**
 * Project Repository
 */

namespace App\Repositories;

use App\Core\DB;

class ProjectRepo
{
    public static function getAll(array $filters = [], int $page = 1, int $perPage = 25): array
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = "p.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['priority'])) {
            $where[] = "p.priority = :priority";
            $params['priority'] = $filters['priority'];
        }

        if (!empty($filters['client_id'])) {
            $where[] = "p.client_id = :client_id";
            $params['client_id'] = $filters['client_id'];
        }

        $whereStr = implode(' AND ', $where);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT p.*, c.name as client_name
                FROM projects p
                JOIN clients c ON p.client_id = c.id
                WHERE {$whereStr}
                ORDER BY p.created_at DESC
                LIMIT :limit OFFSET :offset";

        $params['limit'] = $perPage;
        $params['offset'] = $offset;

        return DB::fetchAll($sql, $params);
    }

    public static function find(int $id): ?array
    {
        return DB::fetch("SELECT * FROM projects WHERE id = :id", ['id' => $id]);
    }

    public static function create(array $data): int
    {
        $fields = [
            'client_id' => $data['client_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 'idea',
            'priority' => $data['priority'] ?? 'normal',
            'start_date' => $data['start_date'] ?: null,
            'due_date' => $data['due_date'] ?: null,
            'progress' => 0,
        ];

        return DB::insert('projects', $fields);
    }

    public static function update(int $id, array $data): void
    {
        $fields = [
            'client_id' => $data['client_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 'idea',
            'priority' => $data['priority'] ?? 'normal',
            'start_date' => $data['start_date'] ?: null,
            'due_date' => $data['due_date'] ?: null,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        DB::update('projects', $fields, 'id = :id', ['id' => $id]);
    }

    public static function delete(int $id): void
    {
        DB::delete('projects', 'id = :id', ['id' => $id]);
    }

    public static function getTodos(int $projectId): array
    {
        return DB::fetchAll(
            "SELECT * FROM project_todos WHERE project_id = :project_id ORDER BY sort_order, created_at",
            ['project_id' => $projectId]
        );
    }

    public static function saveTodos(int $projectId, array $todos): void
    {
        // Delete existing todos
        DB::delete('project_todos', 'project_id = :project_id', ['project_id' => $projectId]);

        // Insert new todos
        $order = 0;
        foreach ($todos as $todo) {
            if (empty($todo['title'])) continue;

            DB::insert('project_todos', [
                'project_id' => $projectId,
                'title' => $todo['title'],
                'state' => $todo['state'] ?? 'todo',
                'sort_order' => $order++,
            ]);
        }

        // Recalculate progress
        self::recalculateProgress($projectId);
    }

    public static function recalculateProgress(int $projectId): void
    {
        $todos = self::getTodos($projectId);

        if (empty($todos)) {
            $progress = 0;
        } else {
            $done = count(array_filter($todos, fn($t) => $t['state'] === 'done'));
            $progress = (int) round(($done / count($todos)) * 100);
        }

        DB::update('projects', ['progress' => $progress], 'id = :id', ['id' => $projectId]);
    }

    public static function recalculateProgressFromTasks(int $projectId): void
    {
        $result = DB::fetch(
            "SELECT COUNT(*) as total, SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed FROM tasks WHERE project_id = :project_id",
            ['project_id' => $projectId]
        );

        $total = (int) ($result['total'] ?? 0);
        if ($total === 0) {
            return;
        }

        $completed = (int) ($result['completed'] ?? 0);
        $progress = (int) round(($completed / $total) * 100);

        DB::update('projects', ['progress' => $progress], 'id = :id', ['id' => $projectId]);
    }

    public static function updateProgress(int $projectId, int $progress): void
    {
        DB::update('projects', ['progress' => $progress], 'id = :id', ['id' => $projectId]);
    }

    /**
     * Get projects that have tasks assigned to a specific employee
     */
    public static function getByEmployee(int $userId, array $filters = [], int $page = 1, int $perPage = 25): array
    {
        $where = ['1=1'];
        $params = ['user_id' => $userId];

        if (!empty($filters['status'])) {
            $where[] = "p.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['priority'])) {
            $where[] = "p.priority = :priority";
            $params['priority'] = $filters['priority'];
        }

        $whereStr = implode(' AND ', $where);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT DISTINCT p.*, c.name as client_name
                FROM projects p
                JOIN clients c ON p.client_id = c.id
                INNER JOIN tasks t ON t.project_id = p.id AND t.assigned_to = :user_id
                WHERE {$whereStr}
                ORDER BY p.created_at DESC
                LIMIT :limit OFFSET :offset";

        $params['limit'] = $perPage;
        $params['offset'] = $offset;

        return DB::fetchAll($sql, $params);
    }

    /**
     * Check if a project has any tasks assigned to a specific employee
     */
    public static function hasEmployeeTasks(int $projectId, int $userId): bool
    {
        $result = DB::fetch(
            "SELECT COUNT(*) as count FROM tasks WHERE project_id = :project_id AND assigned_to = :user_id",
            ['project_id' => $projectId, 'user_id' => $userId]
        );
        return ((int) ($result['count'] ?? 0)) > 0;
    }

    public static function getOverdue(): array
    {
        $sql = "SELECT p.*, c.name as client_name,
                DATEDIFF(CURDATE(), p.due_date) as days_overdue
                FROM projects p
                JOIN clients c ON p.client_id = c.id
                WHERE p.status IN ('idea', 'in_progress', 'paused')
                AND p.due_date < CURDATE()
                ORDER BY p.due_date ASC";

        return DB::fetchAll($sql);
    }
}
