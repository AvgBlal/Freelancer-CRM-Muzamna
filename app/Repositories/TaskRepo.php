<?php
/**
 * Task Repository
 */

namespace App\Repositories;

use App\Core\DB;

class TaskRepo
{
    /**
     * Get all tasks with filters and pagination
     */
    public static function getAll(array $filters = [], int $page = 1, int $perPage = 25): array
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = "t.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['priority'])) {
            $where[] = "t.priority = :priority";
            $params['priority'] = $filters['priority'];
        }

        if (!empty($filters['assigned_to'])) {
            $where[] = "t.assigned_to = :assigned_to";
            $params['assigned_to'] = $filters['assigned_to'];
        }

        if (!empty($filters['unassigned_user'])) {
            $where[] = "t.assigned_to IS NULL";
        }

        if (!empty($filters['created_by'])) {
            $where[] = "t.created_by = :created_by";
            $params['created_by'] = $filters['created_by'];
        }

        if (!empty($filters['client_id'])) {
            $where[] = "t.client_id = :client_id";
            $params['client_id'] = $filters['client_id'];
        }

        if (!empty($filters['project_id'])) {
            $where[] = "t.project_id = :project_id";
            $params['project_id'] = $filters['project_id'];
        }

        if (!empty($filters['unassigned_project'])) {
            $where[] = "t.project_id IS NULL";
        }

        if (!empty($filters['search'])) {
            $where[] = "(t.title LIKE :search1 OR t.description LIKE :search2)";
            $params['search1'] = '%' . $filters['search'] . '%';
            $params['search2'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['due_before'])) {
            $where[] = "t.due_date <= :due_before";
            $params['due_before'] = $filters['due_before'];
        }

        if (!empty($filters['due_after'])) {
            $where[] = "t.due_date >= :due_after";
            $params['due_after'] = $filters['due_after'];
        }

        $whereStr = implode(' AND ', $where);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT t.*,
                creator.name as creator_name,
                assignee.name as assignee_name,
                c.name as client_name,
                p.title as project_title,
                DATEDIFF(t.due_date, CURDATE()) as days_remaining
                FROM tasks t
                LEFT JOIN users creator ON t.created_by = creator.id
                LEFT JOIN users assignee ON t.assigned_to = assignee.id
                LEFT JOIN clients c ON t.client_id = c.id
                LEFT JOIN projects p ON t.project_id = p.id
                WHERE {$whereStr}
                ORDER BY
                    FIELD(t.priority, 'urgent', 'high', 'normal', 'low'),
                    t.due_date ASC,
                    t.created_at DESC
                LIMIT :limit OFFSET :offset";

        $params['limit'] = $perPage;
        $params['offset'] = $offset;

        return DB::fetchAll($sql, $params);
    }

    /**
     * Get task by ID with all relations
     */
    public static function find(int $id): ?array
    {
        $sql = "SELECT t.*,
                creator.name as creator_name,
                assignee.name as assignee_name,
                assignee.email as assignee_email,
                c.name as client_name,
                p.title as project_title,
                s.title as service_title,
                DATEDIFF(t.due_date, CURDATE()) as days_remaining
                FROM tasks t
                LEFT JOIN users creator ON t.created_by = creator.id
                LEFT JOIN users assignee ON t.assigned_to = assignee.id
                LEFT JOIN clients c ON t.client_id = c.id
                LEFT JOIN projects p ON t.project_id = p.id
                LEFT JOIN services s ON t.service_id = s.id
                WHERE t.id = :id";

        return DB::fetch($sql, ['id' => $id]);
    }

    /**
     * Get tasks assigned to a specific user
     */
    public static function getByAssignedTo(int $userId, array $filters = []): array
    {
        $filters['assigned_to'] = $userId;
        return self::getAll($filters, 1, 100);
    }

    /**
     * Get tasks created by a specific user
     */
    public static function getByCreatedBy(int $userId, array $filters = []): array
    {
        $filters['created_by'] = $userId;
        return self::getAll($filters, 1, 100);
    }

    /**
     * Create new task
     */
    public static function create(array $data): int
    {
        $fields = [
            'title' => $data['title'],
            'description' => $data['description'] ?: null,
            'status' => $data['status'] ?? 'draft',
            'priority' => $data['priority'] ?? 'normal',
            'created_by' => $data['created_by'],
            'assigned_to' => $data['assigned_to'] ?: null,
            'client_id' => $data['client_id'] ?: null,
            'project_id' => $data['project_id'] ?: null,
            'service_id' => $data['service_id'] ?: null,
            'start_date' => $data['start_date'] ?: null,
            'due_date' => $data['due_date'],
            'estimated_hours' => $data['estimated_hours'] ?: null,
        ];

        // Auto-set status to 'assigned' if assigned_to is provided
        if (!empty($data['assigned_to']) && $fields['status'] === 'draft') {
            $fields['status'] = 'assigned';
        }

        return DB::insert('tasks', $fields);
    }

    /**
     * Update task
     */
    public static function update(int $id, array $data): void
    {
        $fields = [
            'title' => $data['title'],
            'description' => $data['description'] ?: null,
            'priority' => $data['priority'] ?? 'normal',
            'assigned_to' => $data['assigned_to'] ?: null,
            'client_id' => $data['client_id'] ?: null,
            'project_id' => $data['project_id'] ?: null,
            'service_id' => $data['service_id'] ?: null,
            'start_date' => $data['start_date'] ?: null,
            'due_date' => $data['due_date'],
            'estimated_hours' => $data['estimated_hours'] ?: null,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        DB::update('tasks', $fields, 'id = :id', ['id' => $id]);
    }

    /**
     * Delete task
     */
    public static function delete(int $id): void
    {
        DB::delete('tasks', 'id = :id', ['id' => $id]);
    }

    /**
     * Update task status
     */
    public static function updateStatus(int $id, string $status, ?string $comment = null): void
    {
        $fields = [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Auto-set completed_at when marked as completed
        if ($status === 'completed') {
            $fields['completed_at'] = date('Y-m-d H:i:s');
            $fields['progress_pct'] = 100;
        }

        DB::update('tasks', $fields, 'id = :id', ['id' => $id]);
    }

    /**
     * Update task progress percentage
     */
    public static function updateProgress(int $id, int $percentage): void
    {
        $fields = [
            'progress_pct' => max(0, min(100, $percentage)),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Auto-complete if 100%
        if ($percentage >= 100) {
            $fields['status'] = 'completed';
            $fields['completed_at'] = date('Y-m-d H:i:s');
        }

        DB::update('tasks', $fields, 'id = :id', ['id' => $id]);
    }

    /**
     * Log time spent on task
     */
    public static function logTime(int $id, float $hours): void
    {
        $sql = "UPDATE tasks
                SET actual_hours = actual_hours + :hours,
                    updated_at = NOW()
                WHERE id = :id";

        DB::query($sql, ['id' => $id, 'hours' => $hours]);
    }

    /**
     * Get active task count for a user
     */
    public static function getActiveTaskCount(int $userId): int
    {
        $sql = "SELECT COUNT(*) as count
                FROM tasks
                WHERE assigned_to = :user_id
                AND status NOT IN ('completed', 'cancelled')";

        $result = DB::fetch($sql, ['user_id' => $userId]);
        return (int) ($result['count'] ?? 0);
    }

    /**
     * Get workload info for a user
     */
    public static function getWorkload(int $userId): array
    {
        $activeCount = self::getActiveTaskCount($userId);

        $user = DB::fetch("SELECT max_tasks_capacity FROM users WHERE id = :id", ['id' => $userId]);
        $capacity = (int) ($user['max_tasks_capacity'] ?? 5);

        $percentage = $capacity > 0 ? round(($activeCount / $capacity) * 100) : 0;

        return [
            'active_count' => $activeCount,
            'capacity' => $capacity,
            'percentage' => $percentage,
            'status' => $percentage >= 100 ? 'overloaded' : ($percentage >= 80 ? 'warning' : 'normal'),
        ];
    }

    /**
     * Get team workload for all employees
     */
    public static function getTeamWorkload(): array
    {
        $sql = "SELECT
                    u.id,
                    u.name,
                    u.max_tasks_capacity,
                    COUNT(t.id) as active_tasks
                FROM users u
                LEFT JOIN tasks t ON u.id = t.assigned_to
                    AND t.status NOT IN ('completed', 'cancelled')
                WHERE u.role IN ('employee', 'manager')
                    AND u.is_active = 1
                GROUP BY u.id, u.name, u.max_tasks_capacity
                ORDER BY u.name";

        $users = DB::fetchAll($sql);

        foreach ($users as &$user) {
            $capacity = (int) ($user['max_tasks_capacity'] ?? 5);
            $active = (int) $user['active_tasks'];
            $percentage = $capacity > 0 ? round(($active / $capacity) * 100) : 0;

            $user['capacity'] = $capacity;
            $user['workload_percentage'] = $percentage;
            $user['status'] = $percentage >= 100 ? 'overloaded' : ($percentage >= 80 ? 'warning' : 'normal');
        }

        return $users;
    }

    /**
     * Get overdue task count for a user
     */
    public static function getOverdueCount(int $userId): int
    {
        $sql = "SELECT COUNT(*) as count
                FROM tasks
                WHERE assigned_to = :user_id
                AND status NOT IN ('completed', 'cancelled')
                AND due_date < CURDATE()";

        $result = DB::fetch($sql, ['user_id' => $userId]);
        return (int) ($result['count'] ?? 0);
    }

    /**
     * Get tasks due today for a user
     */
    public static function getDueToday(int $userId): array
    {
        $sql = "SELECT t.*, c.name as client_name
                FROM tasks t
                LEFT JOIN clients c ON t.client_id = c.id
                WHERE t.assigned_to = :user_id
                AND t.status NOT IN ('completed', 'cancelled')
                AND t.due_date = CURDATE()
                ORDER BY t.priority DESC";

        return DB::fetchAll($sql, ['user_id' => $userId]);
    }

    /**
     * Get tasks for dashboard widget
     */
    public static function getDashboardTasks(int $userId): array
    {
        return [
            'due_today' => self::getDueToday($userId),
            'overdue_count' => self::getOverdueCount($userId),
            'active_count' => self::getActiveTaskCount($userId),
            'workload' => self::getWorkload($userId),
        ];
    }

    /**
     * Get tasks expiring soon (for notifications)
     */
    public static function getDueSoon(int $hours = 24): array
    {
        $sql = "SELECT t.*, u.name as assignee_name, u.email as assignee_email
                FROM tasks t
                JOIN users u ON t.assigned_to = u.id
                WHERE t.status NOT IN ('completed', 'cancelled')
                AND t.due_date <= DATE_ADD(CURDATE(), INTERVAL :hours HOUR)
                AND t.due_date >= CURDATE()
                AND t.id NOT IN (
                    SELECT task_id FROM task_comments
                    WHERE is_system_generated = 1
                    AND message LIKE '%تذكير%'
                    AND created_at > DATE_SUB(NOW(), INTERVAL 12 HOUR)
                )
                ORDER BY t.due_date ASC";

        return DB::fetchAll($sql, ['hours' => $hours]);
    }

    /**
     * Get overdue tasks (for notifications)
     */
    public static function getOverdue(): array
    {
        $sql = "SELECT t.*, u.name as assignee_name, u.email as assignee_email,
                DATEDIFF(CURDATE(), t.due_date) as days_overdue
                FROM tasks t
                JOIN users u ON t.assigned_to = u.id
                WHERE t.status NOT IN ('completed', 'cancelled')
                AND t.due_date < CURDATE()
                ORDER BY t.due_date ASC";

        return DB::fetchAll($sql);
    }

    /**
     * Get all employees for assignment dropdown
     */
    public static function getEmployeesForAssignment(): array
    {
        $sql = "SELECT u.id, u.name, u.max_tasks_capacity,
                COUNT(t.id) as active_tasks
                FROM users u
                LEFT JOIN tasks t ON u.id = t.assigned_to
                    AND t.status NOT IN ('completed', 'cancelled')
                WHERE u.role IN ('employee', 'manager')
                    AND u.is_active = 1
                GROUP BY u.id, u.name, u.max_tasks_capacity
                ORDER BY u.name";

        return DB::fetchAll($sql);
    }
}
