<?php
/**
 * Activity Log Repository
 * Tracks user actions across all modules
 */

namespace App\Repositories;

use App\Core\Auth;
use App\Core\DB;

class ActivityLogRepo
{
    /**
     * Log a user action
     */
    public static function log(string $action, string $entityType, ?int $entityId = null, ?string $entityTitle = null, ?string $details = null): void
    {
        $user = Auth::user();

        DB::insert('activity_logs', [
            'user_id' => $user['id'] ?? null,
            'user_name' => $user['name'] ?? __('app.system'),
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'entity_title' => $entityTitle,
            'details' => $details,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    }

    /**
     * Log a system action (no user context, e.g. cron)
     */
    public static function logSystem(string $action, string $entityType, ?int $entityId = null, ?string $entityTitle = null, ?string $details = null): void
    {
        DB::insert('activity_logs', [
            'user_id' => null,
            'user_name' => __('app.system'),
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'entity_title' => $entityTitle,
            'details' => $details,
            'ip_address' => null,
        ]);
    }

    /**
     * Get all logs with filters and pagination
     */
    public static function getAll(array $filters = [], int $page = 1, int $perPage = 50): array
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['action'])) {
            $where[] = "action = :action";
            $params['action'] = $filters['action'];
        }

        if (!empty($filters['entity_type'])) {
            $where[] = "entity_type = :entity_type";
            $params['entity_type'] = $filters['entity_type'];
        }

        if (!empty($filters['user_id'])) {
            $where[] = "user_id = :user_id";
            $params['user_id'] = $filters['user_id'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(entity_title LIKE :search1 OR details LIKE :search2 OR user_name LIKE :search3)";
            $params['search1'] = '%' . $filters['search'] . '%';
            $params['search2'] = '%' . $filters['search'] . '%';
            $params['search3'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['date_from'])) {
            $where[] = "created_at >= :date_from";
            $params['date_from'] = $filters['date_from'] . ' 00:00:00';
        }

        if (!empty($filters['date_to'])) {
            $where[] = "created_at <= :date_to";
            $params['date_to'] = $filters['date_to'] . ' 23:59:59';
        }

        $whereStr = implode(' AND ', $where);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT * FROM activity_logs
                WHERE {$whereStr}
                ORDER BY created_at DESC
                LIMIT :limit OFFSET :offset";

        $params['limit'] = $perPage;
        $params['offset'] = $offset;

        return DB::fetchAll($sql, $params);
    }

    /**
     * Get total count with filters
     */
    public static function getCount(array $filters = []): int
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['action'])) {
            $where[] = "action = :action";
            $params['action'] = $filters['action'];
        }

        if (!empty($filters['entity_type'])) {
            $where[] = "entity_type = :entity_type";
            $params['entity_type'] = $filters['entity_type'];
        }

        if (!empty($filters['user_id'])) {
            $where[] = "user_id = :user_id";
            $params['user_id'] = $filters['user_id'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(entity_title LIKE :search1 OR details LIKE :search2 OR user_name LIKE :search3)";
            $params['search1'] = '%' . $filters['search'] . '%';
            $params['search2'] = '%' . $filters['search'] . '%';
            $params['search3'] = '%' . $filters['search'] . '%';
        }

        $whereStr = implode(' AND ', $where);
        return (int) (DB::fetch("SELECT COUNT(*) as count FROM activity_logs WHERE {$whereStr}", $params)['count'] ?? 0);
    }

    /**
     * Get recent activity (for dashboard widget)
     */
    public static function getRecent(int $limit = 10): array
    {
        return DB::fetchAll(
            "SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT :limit",
            ['limit' => $limit]
        );
    }

    /**
     * Get notification logs with pagination
     */
    public static function getNotificationLogs(array $filters = [], int $page = 1, int $perPage = 50): array
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['channel'])) {
            $where[] = "channel = :channel";
            $params['channel'] = $filters['channel'];
        }

        if (!empty($filters['status'])) {
            $where[] = "status = :status";
            $params['status'] = $filters['status'];
        }

        $whereStr = implode(' AND ', $where);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT * FROM notification_logs
                WHERE {$whereStr}
                ORDER BY sent_at DESC
                LIMIT :limit OFFSET :offset";

        $params['limit'] = $perPage;
        $params['offset'] = $offset;

        return DB::fetchAll($sql, $params);
    }

    /**
     * Get notification log count
     */
    public static function getNotificationLogCount(array $filters = []): int
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['channel'])) {
            $where[] = "channel = :channel";
            $params['channel'] = $filters['channel'];
        }

        if (!empty($filters['status'])) {
            $where[] = "status = :status";
            $params['status'] = $filters['status'];
        }

        $whereStr = implode(' AND ', $where);
        return (int) (DB::fetch("SELECT COUNT(*) as count FROM notification_logs WHERE {$whereStr}", $params)['count'] ?? 0);
    }

    /**
     * Get cron run history
     */
    public static function getCronRuns(int $page = 1, int $perPage = 25): array
    {
        $offset = ($page - 1) * $perPage;

        return DB::fetchAll(
            "SELECT * FROM cron_runs ORDER BY run_at DESC LIMIT :limit OFFSET :offset",
            ['limit' => $perPage, 'offset' => $offset]
        );
    }

    /**
     * Get cron run count
     */
    public static function getCronRunCount(): int
    {
        return (int) (DB::fetch("SELECT COUNT(*) as count FROM cron_runs")['count'] ?? 0);
    }

    /**
     * Get distinct entity types from logs
     */
    public static function getEntityTypes(): array
    {
        return DB::fetchAll("SELECT DISTINCT entity_type FROM activity_logs ORDER BY entity_type");
    }

    /**
     * Get distinct actions from logs
     */
    public static function getActions(): array
    {
        return DB::fetchAll("SELECT DISTINCT action FROM activity_logs ORDER BY action");
    }
}
