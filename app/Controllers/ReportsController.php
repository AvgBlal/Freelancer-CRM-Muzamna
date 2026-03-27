<?php
/**
 * Reports Controller
 * Statistics & Reports Hub
 */

namespace App\Controllers;

use App\Core\Auth;
use App\Repositories\ReportsRepo;
use App\Repositories\ServiceRepo;

class ReportsController
{
    public function index(): void
    {
        Auth::requireAuth();

        // Overall summary
        $summary = ReportsRepo::getOverallSummary();

        // Client analytics
        $topClients = ReportsRepo::getTopClientsByRevenue(10);
        $clientsByType = ReportsRepo::getClientsByType();
        $clientGrowth = ReportsRepo::getClientGrowthByMonth(12);
        $clientServiceCounts = ReportsRepo::getClientServiceCounts(10);

        // Service analytics
        $mrr = ServiceRepo::getMRR();
        $servicesByType = ServiceRepo::getCountByType();
        $revenueByType = ServiceRepo::getRevenueByType();
        $expiryForecast = ReportsRepo::getServiceExpiryForecast(6);

        // Project analytics
        $projectStats = ReportsRepo::getProjectCompletionStats();
        $projectStatusBreakdown = ReportsRepo::getProjectStatusBreakdown();
        $projectsByClient = ReportsRepo::getProjectsByClient(10);
        $avgProjectProgress = ReportsRepo::getAverageProjectProgress();

        // Task analytics
        $taskStats = ReportsRepo::getTaskCompletionStats();
        $taskStatusBreakdown = ReportsRepo::getTaskStatusBreakdown();
        $tasksByPriority = ReportsRepo::getTasksByPriority();
        $tasksByAssignee = ReportsRepo::getTasksByAssignee();
        $avgCompletionTime = ReportsRepo::getAverageCompletionTime();
        $timeTracking = ReportsRepo::getTimeTrackingSummary();

        // Monthly activity
        $monthlyActivity = ReportsRepo::getMonthlyActivity(6);

        require __DIR__ . '/../Views/reports/index.php';
    }
}
