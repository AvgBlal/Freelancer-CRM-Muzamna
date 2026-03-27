<?php
/**
 * Dashboard Controller
 */

namespace App\Controllers;

use App\Core\Auth;
use App\Core\DB;
use App\Repositories\DueRepo;
use App\Repositories\ExpenseRepo;
use App\Repositories\SettingsRepo;
use App\Repositories\ActivityLogRepo;
use App\Repositories\TaskRepo;

class DashboardController
{
    public function index(): void
    {
        Auth::requireAuth();

        if (Auth::isEmployee()) {
            $this->employeeDashboard();
        } else {
            $this->fullDashboard();
        }
    }

    private function fullDashboard(): void
    {
        $stats = $this->getStats();
        $expiringServices = $this->getExpiringServices();
        $overdueProjects = $this->getOverdueProjects();
        $dueStats = DueRepo::getStats();
        $overdueDues = DueRepo::getOverdue();
        $expenseStats = ExpenseRepo::getStats();
        $upcomingExpenses = ExpenseRepo::getUpcoming(30);
        $overdueExpenses = ExpenseRepo::getOverdue();
        $recentActivity = ActivityLogRepo::getRecent(10);

        require __DIR__ . '/../Views/dashboard/index.php';
    }

    private function employeeDashboard(): void
    {
        $userId = Auth::id();
        $taskData = TaskRepo::getDashboardTasks($userId);

        $projectCount = DB::fetch(
            "SELECT COUNT(DISTINCT t.project_id) as count FROM tasks t WHERE t.assigned_to = :uid AND t.project_id IS NOT NULL",
            ['uid' => $userId]
        );

        $employeeStats = [
            'activeTasks' => $taskData['active_count'],
            'overdueTasks' => $taskData['overdue_count'],
            'dueTodayTasks' => $taskData['due_today'],
            'workload' => $taskData['workload'],
            'projectCount' => (int) ($projectCount['count'] ?? 0),
        ];

        require __DIR__ . '/../Views/dashboard/employee.php';
    }

    private function getStats(): array
    {
        $clients = DB::fetch("SELECT COUNT(*) as count FROM clients")['count'] ?? 0;
        $activeServices = DB::fetch("SELECT COUNT(*) as count FROM services WHERE status = 'active'")['count'] ?? 0;
        $expiredServices = DB::fetch("SELECT COUNT(*) as count FROM services WHERE status = 'expired'")['count'] ?? 0;
        $totalProjects = DB::fetch("SELECT COUNT(*) as count FROM projects")['count'] ?? 0;
        $activeProjects = DB::fetch("SELECT COUNT(*) as count FROM projects WHERE status IN ('idea', 'in_progress', 'paused')")['count'] ?? 0;

        return compact('clients', 'activeServices', 'expiredServices', 'totalProjects', 'activeProjects');
    }

    private function getExpiringServices(): array
    {
        $reminderDaysStr = SettingsRepo::get('reminder_days', '30');
        $reminderDaysList = array_map('intval', array_filter(explode(',', $reminderDaysStr), 'is_numeric'));
        $maxDays = !empty($reminderDaysList) ? max($reminderDaysList) : 30;

        $sql = "SELECT s.*, GROUP_CONCAT(c.name SEPARATOR ', ') as client_names
                FROM services s
                LEFT JOIN service_clients sc ON s.id = sc.service_id
                LEFT JOIN clients c ON sc.client_id = c.id
                WHERE s.status = 'active'
                  AND s.end_date <= DATE_ADD(CURDATE(), INTERVAL :days DAY)
                  AND s.end_date >= CURDATE()
                GROUP BY s.id
                ORDER BY s.end_date ASC";

        $services = DB::fetchAll($sql, ['days' => $maxDays]);

        foreach ($services as &$service) {
            $daysUntil = (int) ((strtotime($service['end_date']) - strtotime(date('Y-m-d'))) / 86400);
            $service['days_until'] = $daysUntil;
            if ($daysUntil <= 3) {
                $service['urgency'] = 'urgent';
            } elseif ($daysUntil <= 7) {
                $service['urgency'] = 'danger';
            } elseif ($daysUntil <= 14) {
                $service['urgency'] = 'warning';
            } else {
                $service['urgency'] = 'info';
            }
        }

        return $services;
    }

    private function getOverdueProjects(): array
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
