<?php
/**
 * Notes Repository
 * Personal notes and reminders
 */

namespace App\Repositories;

use App\Core\DB;

class NotesRepo
{
    public static function getAll(array $filters = [], int $page = 1, int $perPage = 25): array
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = "status = :status";
            $params['status'] = $filters['status'];
        } else {
            $where[] = "status = 'active'";
        }

        if (!empty($filters['category'])) {
            $where[] = "category = :category";
            $params['category'] = $filters['category'];
        }

        if (!empty($filters['priority'])) {
            $where[] = "priority = :priority";
            $params['priority'] = $filters['priority'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(title LIKE :search1 OR content LIKE :search2)";
            $params['search1'] = '%' . $filters['search'] . '%';
            $params['search2'] = '%' . $filters['search'] . '%';
        }

        if (isset($filters['is_pinned']) && $filters['is_pinned'] !== '') {
            $where[] = "is_pinned = :is_pinned";
            $params['is_pinned'] = (int) $filters['is_pinned'];
        }

        if (!empty($filters['created_by'])) {
            $where[] = "created_by = :created_by";
            $params['created_by'] = $filters['created_by'];
        }

        $whereStr = implode(' AND ', $where);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT * FROM notes
                WHERE {$whereStr}
                ORDER BY is_pinned DESC,
                    CASE priority WHEN 'high' THEN 1 WHEN 'normal' THEN 2 WHEN 'low' THEN 3 END,
                    created_at DESC
                LIMIT :limit OFFSET :offset";

        $params['limit'] = $perPage;
        $params['offset'] = $offset;

        return DB::fetchAll($sql, $params);
    }

    public static function getCount(array $filters = []): int
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = "status = :status";
            $params['status'] = $filters['status'];
        } else {
            $where[] = "status = 'active'";
        }

        if (!empty($filters['category'])) {
            $where[] = "category = :category";
            $params['category'] = $filters['category'];
        }

        if (!empty($filters['priority'])) {
            $where[] = "priority = :priority";
            $params['priority'] = $filters['priority'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(title LIKE :search1 OR content LIKE :search2)";
            $params['search1'] = '%' . $filters['search'] . '%';
            $params['search2'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['created_by'])) {
            $where[] = "created_by = :created_by";
            $params['created_by'] = $filters['created_by'];
        }

        $whereStr = implode(' AND ', $where);
        return (int) (DB::fetch("SELECT COUNT(*) as count FROM notes WHERE {$whereStr}", $params)['count'] ?? 0);
    }

    public static function find(int $id): ?array
    {
        return DB::fetch("SELECT * FROM notes WHERE id = :id", ['id' => $id]);
    }

    public static function create(array $data): int
    {
        $fields = [
            'title' => $data['title'],
            'content' => $data['content'] ?: null,
            'category' => $data['category'] ?? 'general',
            'priority' => $data['priority'] ?? 'normal',
            'status' => 'active',
            'is_pinned' => !empty($data['is_pinned']) ? 1 : 0,
            'due_date' => $data['due_date'] ?: null,
            'color' => $data['color'] ?: null,
            'created_by' => $data['created_by'] ?? null,
        ];

        return DB::insert('notes', $fields);
    }

    public static function update(int $id, array $data): void
    {
        $fields = [
            'title' => $data['title'],
            'content' => $data['content'] ?: null,
            'category' => $data['category'] ?? 'general',
            'priority' => $data['priority'] ?? 'normal',
            'is_pinned' => !empty($data['is_pinned']) ? 1 : 0,
            'due_date' => $data['due_date'] ?: null,
            'color' => $data['color'] ?: null,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        DB::update('notes', $fields, 'id = :id', ['id' => $id]);
    }

    public static function delete(int $id): void
    {
        DB::delete('notes', 'id = :id', ['id' => $id]);
    }

    public static function togglePin(int $id): void
    {
        $note = self::find($id);
        if (!$note) return;

        $newPinned = $note['is_pinned'] ? 0 : 1;
        DB::update('notes', [
            'is_pinned' => $newPinned,
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = :id', ['id' => $id]);
    }

    public static function archive(int $id): void
    {
        DB::update('notes', [
            'status' => 'archived',
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = :id', ['id' => $id]);
    }

    public static function restore(int $id): void
    {
        DB::update('notes', [
            'status' => 'active',
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = :id', ['id' => $id]);
    }

    /** Get pinned notes (for dashboard widget) */
    public static function getPinned(int $limit = 5): array
    {
        $sql = "SELECT * FROM notes
                WHERE status = 'active' AND is_pinned = 1
                ORDER BY updated_at DESC
                LIMIT :limit";

        return DB::fetchAll($sql, ['limit' => $limit]);
    }

    /** Get notes with upcoming due dates */
    public static function getUpcoming(int $days = 7): array
    {
        $sql = "SELECT *, DATEDIFF(due_date, CURDATE()) as days_until
                FROM notes
                WHERE status = 'active'
                AND due_date IS NOT NULL
                AND due_date >= CURDATE()
                AND due_date <= DATE_ADD(CURDATE(), INTERVAL :days DAY)
                ORDER BY due_date ASC";

        return DB::fetchAll($sql, ['days' => $days]);
    }

    /** Get overdue notes */
    public static function getOverdue(): array
    {
        $sql = "SELECT *, DATEDIFF(CURDATE(), due_date) as days_overdue
                FROM notes
                WHERE status = 'active'
                AND due_date IS NOT NULL
                AND due_date < CURDATE()
                ORDER BY due_date ASC";

        return DB::fetchAll($sql);
    }

    /** Stats for the notes page, optionally scoped to a user */
    public static function getStats(?int $userId = null): array
    {
        $ownerFilter = $userId ? " AND created_by = {$userId}" : '';

        $active = (int) (DB::fetch("SELECT COUNT(*) as count FROM notes WHERE status = 'active'" . $ownerFilter)['count'] ?? 0);
        $pinned = (int) (DB::fetch("SELECT COUNT(*) as count FROM notes WHERE status = 'active' AND is_pinned = 1" . $ownerFilter)['count'] ?? 0);
        $withDueDate = (int) (DB::fetch(
            "SELECT COUNT(*) as count FROM notes WHERE status = 'active' AND due_date IS NOT NULL" . $ownerFilter
        )['count'] ?? 0);
        $overdue = (int) (DB::fetch(
            "SELECT COUNT(*) as count FROM notes WHERE status = 'active' AND due_date IS NOT NULL AND due_date < CURDATE()" . $ownerFilter
        )['count'] ?? 0);
        $archived = (int) (DB::fetch("SELECT COUNT(*) as count FROM notes WHERE status = 'archived'" . $ownerFilter)['count'] ?? 0);

        return compact('active', 'pinned', 'withDueDate', 'overdue', 'archived');
    }
}
