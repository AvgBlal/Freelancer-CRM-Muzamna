<?php
/**
 * Task Comment Repository
 */

namespace App\Repositories;

use App\Core\DB;

class TaskCommentRepo
{
    /**
     * Get comments for a task
     */
    public static function getByTask(int $taskId): array
    {
        $sql = "SELECT tc.*, u.name as user_name
                FROM task_comments tc
                JOIN users u ON tc.user_id = u.id
                WHERE tc.task_id = :task_id
                ORDER BY tc.created_at ASC";

        return DB::fetchAll($sql, ['task_id' => $taskId]);
    }

    /**
     * Create comment
     */
    public static function create(array $data): int
    {
        $fields = [
            'task_id' => $data['task_id'],
            'user_id' => $data['user_id'],
            'message' => $data['message'],
            'mentions' => !empty($data['mentions']) ? json_encode($data['mentions']) : null,
            'is_system_generated' => !empty($data['is_system_generated']) ? 1 : 0,
        ];

        return DB::insert('task_comments', $fields);
    }

    /**
     * Delete comment
     */
    public static function delete(int $id): void
    {
        DB::delete('task_comments', 'id = :id', ['id' => $id]);
    }

    /**
     * Parse mentions from message
     */
    public static function parseMentions(string $message): array
    {
        preg_match_all('/@(\w+)/', $message, $matches);

        if (empty($matches[1])) {
            return [];
        }

        $usernames = $matches[1];
        $userIds = [];

        foreach ($usernames as $username) {
            $user = DB::fetch("SELECT id FROM users WHERE name LIKE :name LIMIT 1", [
                'name' => '%' . $username . '%'
            ]);
            if ($user) {
                $userIds[] = $user['id'];
            }
        }

        return $userIds;
    }

    /**
     * Create system-generated comment
     */
    public static function logActivity(int $taskId, int $userId, string $message): int
    {
        return self::create([
            'task_id' => $taskId,
            'user_id' => $userId,
            'message' => $message,
            'is_system_generated' => true,
        ]);
    }
}
