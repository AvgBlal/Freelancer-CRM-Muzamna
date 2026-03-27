<?php
/**
 * Logs Controller
 * Activity logs, notification logs, and cron run history
 */

namespace App\Controllers;

use App\Core\Auth;
use App\Repositories\ActivityLogRepo;

class LogsController
{
    public function activity(): void
    {
        Auth::requireRole('admin');

        $filters = [];
        if (!empty($_GET['action'])) $filters['action'] = $_GET['action'];
        if (!empty($_GET['entity_type'])) $filters['entity_type'] = $_GET['entity_type'];
        if (!empty($_GET['user_id'])) $filters['user_id'] = (int) $_GET['user_id'];
        if (!empty($_GET['search'])) $filters['search'] = $_GET['search'];
        if (!empty($_GET['date_from'])) $filters['date_from'] = $_GET['date_from'];
        if (!empty($_GET['date_to'])) $filters['date_to'] = $_GET['date_to'];

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 50;

        $logs = ActivityLogRepo::getAll($filters, $page, $perPage);
        $total = ActivityLogRepo::getCount($filters);
        $totalPages = max(1, (int) ceil($total / $perPage));

        $entityTypes = ActivityLogRepo::getEntityTypes();
        $actions = ActivityLogRepo::getActions();

        require __DIR__ . '/../Views/logs/activity.php';
    }

    public function notifications(): void
    {
        Auth::requireRole('admin');

        $filters = [];
        if (!empty($_GET['channel'])) $filters['channel'] = $_GET['channel'];
        if (!empty($_GET['status'])) $filters['status'] = $_GET['status'];

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 50;

        $logs = ActivityLogRepo::getNotificationLogs($filters, $page, $perPage);
        $total = ActivityLogRepo::getNotificationLogCount($filters);
        $totalPages = max(1, (int) ceil($total / $perPage));

        require __DIR__ . '/../Views/logs/notifications.php';
    }

    public function cron(): void
    {
        Auth::requireRole('admin');

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 25;

        $runs = ActivityLogRepo::getCronRuns($page, $perPage);
        $total = ActivityLogRepo::getCronRunCount();
        $totalPages = max(1, (int) ceil($total / $perPage));

        require __DIR__ . '/../Views/logs/cron.php';
    }
}
