<?php
/**
 * Reports Repository
 * Cross-module analytics and statistics
 */

namespace App\Repositories;

use App\Core\DB;

class ReportsRepo
{
    // === Client Analytics ===

    /** Top clients by active service monthly revenue */
    public static function getTopClientsByRevenue(int $limit = 10): array
    {
        $sql = "SELECT c.id, c.name, c.type,
                    COUNT(DISTINCT s.id) as service_count,
                    COALESCE(SUM(s.price_amount), 0) as total_value,
                    COALESCE(SUM(CASE
                        WHEN s.billing_cycle = 'monthly' THEN s.price_amount
                        WHEN s.billing_cycle = 'yearly' THEN s.price_amount / 12
                        ELSE 0
                    END), 0) as monthly_value
                FROM clients c
                JOIN service_clients sc ON c.id = sc.client_id
                JOIN services s ON sc.service_id = s.id
                WHERE s.status = 'active' AND s.price_amount IS NOT NULL AND s.is_personal = 0
                GROUP BY c.id, c.name, c.type
                ORDER BY monthly_value DESC
                LIMIT :limit";

        return DB::fetchAll($sql, ['limit' => $limit]);
    }

    /** New clients added per month */
    public static function getClientGrowthByMonth(int $months = 12): array
    {
        $sql = "SELECT
                    DATE_FORMAT(created_at, '%Y-%m') as month,
                    COUNT(*) as count
                FROM clients
                WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL :months MONTH)
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY month ASC";

        return DB::fetchAll($sql, ['months' => $months]);
    }

    /** Clients grouped by type */
    public static function getClientsByType(): array
    {
        $sql = "SELECT type, COUNT(*) as count
                FROM clients
                GROUP BY type
                ORDER BY count DESC";

        return DB::fetchAll($sql);
    }

    /** Clients with most services */
    public static function getClientServiceCounts(int $limit = 10): array
    {
        $sql = "SELECT c.id, c.name,
                    COUNT(DISTINCT CASE WHEN s.status = 'active' THEN s.id END) as active_services,
                    COUNT(DISTINCT s.id) as total_services
                FROM clients c
                JOIN service_clients sc ON c.id = sc.client_id
                JOIN services s ON sc.service_id = s.id
                WHERE s.is_personal = 0
                GROUP BY c.id, c.name
                ORDER BY active_services DESC, total_services DESC
                LIMIT :limit";

        return DB::fetchAll($sql, ['limit' => $limit]);
    }

    // === Project Analytics ===

    /** Project count by status */
    public static function getProjectStatusBreakdown(): array
    {
        $sql = "SELECT status, COUNT(*) as count
                FROM projects
                GROUP BY status
                ORDER BY FIELD(status, 'in_progress', 'idea', 'paused', 'completed', 'cancelled')";

        return DB::fetchAll($sql);
    }

    /** Project completion stats */
    public static function getProjectCompletionStats(): array
    {
        $total = (int) (DB::fetch("SELECT COUNT(*) as count FROM projects")['count'] ?? 0);
        $completed = (int) (DB::fetch("SELECT COUNT(*) as count FROM projects WHERE status = 'completed'")['count'] ?? 0);
        $inProgress = (int) (DB::fetch("SELECT COUNT(*) as count FROM projects WHERE status = 'in_progress'")['count'] ?? 0);
        $overdue = (int) (DB::fetch(
            "SELECT COUNT(*) as count FROM projects
             WHERE status IN ('idea', 'in_progress', 'paused')
             AND due_date IS NOT NULL AND due_date < CURDATE()"
        )['count'] ?? 0);

        $rate = $total > 0 ? round(($completed / $total) * 100, 1) : 0;

        return compact('total', 'completed', 'inProgress', 'overdue', 'rate');
    }

    /** Projects per client (top) */
    public static function getProjectsByClient(int $limit = 10): array
    {
        $sql = "SELECT c.id, c.name,
                    COUNT(*) as project_count,
                    SUM(CASE WHEN p.status = 'completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN p.status = 'in_progress' THEN 1 ELSE 0 END) as in_progress
                FROM projects p
                JOIN clients c ON p.client_id = c.id
                GROUP BY c.id, c.name
                ORDER BY project_count DESC
                LIMIT :limit";

        return DB::fetchAll($sql, ['limit' => $limit]);
    }

    /** Average project progress */
    public static function getAverageProjectProgress(): float
    {
        $result = DB::fetch(
            "SELECT AVG(progress) as avg_progress FROM projects WHERE status NOT IN ('cancelled')"
        );
        return round((float) ($result['avg_progress'] ?? 0), 1);
    }

    // === Task Analytics ===

    /** Task count by status */
    public static function getTaskStatusBreakdown(): array
    {
        $sql = "SELECT status, COUNT(*) as count
                FROM tasks
                GROUP BY status
                ORDER BY count DESC";

        return DB::fetchAll($sql);
    }

    /** Task count by priority */
    public static function getTasksByPriority(): array
    {
        $sql = "SELECT priority, COUNT(*) as count
                FROM tasks
                GROUP BY priority
                ORDER BY FIELD(priority, 'urgent', 'high', 'normal', 'low')";

        return DB::fetchAll($sql);
    }

    /** Task completion stats */
    public static function getTaskCompletionStats(): array
    {
        $total = (int) (DB::fetch("SELECT COUNT(*) as count FROM tasks")['count'] ?? 0);
        $completed = (int) (DB::fetch("SELECT COUNT(*) as count FROM tasks WHERE status = 'completed'")['count'] ?? 0);
        $overdue = (int) (DB::fetch(
            "SELECT COUNT(*) as count FROM tasks
             WHERE status NOT IN ('completed', 'cancelled')
             AND due_date IS NOT NULL AND due_date < CURDATE()"
        )['count'] ?? 0);

        $rate = $total > 0 ? round(($completed / $total) * 100, 1) : 0;

        return compact('total', 'completed', 'overdue', 'rate');
    }

    /** Average task completion time (days from creation to completed_at) */
    public static function getAverageCompletionTime(): float
    {
        $result = DB::fetch(
            "SELECT AVG(DATEDIFF(completed_at, created_at)) as avg_days
             FROM tasks
             WHERE status = 'completed' AND completed_at IS NOT NULL"
        );
        return round((float) ($result['avg_days'] ?? 0), 1);
    }

    /** Tasks per assignee */
    public static function getTasksByAssignee(): array
    {
        $sql = "SELECT u.id, u.name,
                    COUNT(*) as total_tasks,
                    SUM(CASE WHEN t.status = 'completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN t.status NOT IN ('completed', 'cancelled') THEN 1 ELSE 0 END) as active,
                    COALESCE(SUM(t.actual_hours), 0) as total_hours
                FROM tasks t
                JOIN users u ON t.assigned_to = u.id
                GROUP BY u.id, u.name
                ORDER BY active DESC";

        return DB::fetchAll($sql);
    }

    /** Time tracking summary */
    public static function getTimeTrackingSummary(): array
    {
        $totalEstimated = DB::fetch(
            "SELECT COALESCE(SUM(estimated_hours), 0) as total FROM tasks WHERE estimated_hours IS NOT NULL"
        )['total'] ?? 0;

        $totalActual = DB::fetch(
            "SELECT COALESCE(SUM(actual_hours), 0) as total FROM tasks WHERE actual_hours IS NOT NULL"
        )['total'] ?? 0;

        $tasksWithTime = DB::fetch(
            "SELECT COUNT(*) as count FROM tasks WHERE actual_hours > 0"
        )['count'] ?? 0;

        return [
            'totalEstimated' => (float) $totalEstimated,
            'totalActual' => (float) $totalActual,
            'tasksWithTime' => (int) $tasksWithTime,
            'efficiency' => $totalEstimated > 0 ? round(($totalActual / $totalEstimated) * 100, 1) : 0,
        ];
    }

    // === Cross-module Overview ===

    /** Overall system summary */
    public static function getOverallSummary(): array
    {
        $clients = (int) (DB::fetch("SELECT COUNT(*) as count FROM clients")['count'] ?? 0);
        $activeServices = (int) (DB::fetch("SELECT COUNT(*) as count FROM services WHERE status = 'active'")['count'] ?? 0);
        $totalServices = (int) (DB::fetch("SELECT COUNT(*) as count FROM services")['count'] ?? 0);
        $activeProjects = (int) (DB::fetch("SELECT COUNT(*) as count FROM projects WHERE status IN ('idea', 'in_progress')")['count'] ?? 0);
        $totalProjects = (int) (DB::fetch("SELECT COUNT(*) as count FROM projects")['count'] ?? 0);
        $activeTasks = (int) (DB::fetch("SELECT COUNT(*) as count FROM tasks WHERE status NOT IN ('completed', 'cancelled')")['count'] ?? 0);
        $totalTasks = (int) (DB::fetch("SELECT COUNT(*) as count FROM tasks")['count'] ?? 0);
        $pendingDuesByCurrency = DB::fetchAll("SELECT currency_code, COALESCE(SUM(amount - paid_amount), 0) as total FROM dues WHERE status IN ('pending', 'partial') GROUP BY currency_code ORDER BY total DESC");
        $pendingExpensesByCurrency = DB::fetchAll("SELECT currency_code, COALESCE(SUM(amount), 0) as total FROM expenses WHERE status = 'pending' GROUP BY currency_code ORDER BY total DESC");

        return compact(
            'clients', 'activeServices', 'totalServices',
            'activeProjects', 'totalProjects',
            'activeTasks', 'totalTasks',
            'pendingDuesByCurrency', 'pendingExpensesByCurrency'
        );
    }

    /** Monthly activity across all modules */
    public static function getMonthlyActivity(int $months = 6): array
    {
        $result = [];

        // New clients per month
        $clientRows = DB::fetchAll(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count
             FROM clients WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL :months MONTH)
             GROUP BY DATE_FORMAT(created_at, '%Y-%m')",
            ['months' => $months]
        );

        // New services per month
        $serviceRows = DB::fetchAll(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count
             FROM services WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL :months MONTH)
             GROUP BY DATE_FORMAT(created_at, '%Y-%m')",
            ['months' => $months]
        );

        // Tasks completed per month
        $taskRows = DB::fetchAll(
            "SELECT DATE_FORMAT(completed_at, '%Y-%m') as month, COUNT(*) as count
             FROM tasks WHERE status = 'completed' AND completed_at IS NOT NULL
             AND completed_at >= DATE_SUB(CURDATE(), INTERVAL :months MONTH)
             GROUP BY DATE_FORMAT(completed_at, '%Y-%m')",
            ['months' => $months]
        );

        // Build combined map
        $clientMap = array_column($clientRows, 'count', 'month');
        $serviceMap = array_column($serviceRows, 'count', 'month');
        $taskMap = array_column($taskRows, 'count', 'month');

        // Generate month list
        for ($i = $months - 1; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-{$i} months"));
            $result[] = [
                'month' => $month,
                'new_clients' => (int) ($clientMap[$month] ?? 0),
                'new_services' => (int) ($serviceMap[$month] ?? 0),
                'tasks_completed' => (int) ($taskMap[$month] ?? 0),
            ];
        }

        return $result;
    }

    /** Service expiry forecast (upcoming expirations by month) */
    public static function getServiceExpiryForecast(int $months = 6): array
    {
        $sql = "SELECT
                    DATE_FORMAT(end_date, '%Y-%m') as month,
                    COUNT(*) as count,
                    COALESCE(SUM(price_amount), 0) as value_at_risk
                FROM services
                WHERE status = 'active'
                AND end_date >= CURDATE()
                AND end_date <= DATE_ADD(CURDATE(), INTERVAL :months MONTH)
                GROUP BY DATE_FORMAT(end_date, '%Y-%m')
                ORDER BY month ASC";

        return DB::fetchAll($sql, ['months' => $months]);
    }
}
