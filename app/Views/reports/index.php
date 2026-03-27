<?php $title = __('reports.title'); ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<?php $defaultCurrency = \App\Repositories\SettingsRepo::get('default_currency', 'EGP'); ?>
<?php
$arabicMonths = [
    '01' => __('month.01'), '02' => __('month.02'), '03' => __('month.03'), '04' => __('month.04'),
    '05' => __('month.05'), '06' => __('month.06'), '07' => __('month.07'), '08' => __('month.08'),
    '09' => __('month.09'), '10' => __('month.10'), '11' => __('month.11'), '12' => __('month.12'),
];
$formatMonth = function(string $ym) use ($arabicMonths) {
    $parts = explode('-', $ym);
    return ($arabicMonths[$parts[1]] ?? $parts[1]) . ' ' . $parts[0];
};

require __DIR__ . '/../partials/service_types.php';
$typeLabels = $serviceTypeLabels;

$projectStatusLabels = [
    'idea' => __('notes.cat.idea'), 'in_progress' => __('projects.status.in_progress'), 'paused' => __('services.status.paused'),
    'completed' => __('projects.status.completed'), 'cancelled' => __('services.status.cancelled'),
];
$projectStatusBadges = [
    'idea' => 'badge-info', 'in_progress' => 'badge-warning', 'paused' => 'badge-secondary',
    'completed' => 'badge-success', 'cancelled' => 'badge-urgent',
];

$taskStatusLabels = [
    'draft' => __('tasks.status.draft'), 'assigned' => __('tasks.status.assigned'), 'in_progress' => __('projects.status.in_progress'),
    'review' => __('tasks.status.in_review'), 'completed' => __('tasks.status.completed'), 'cancelled' => __('tasks.status.cancelled'),
    'on_hold' => __('tasks.status.on_hold'), 'blocked' => __('tasks.status.blocked'), 'testing' => 'testing',
];
$priorityLabels = ['urgent' => __('tasks.priority.urgent'), 'high' => __('notes.priority.high'), 'normal' => __('notes.priority.normal'), 'low' => __('notes.priority.low')];
$priorityBadges = ['urgent' => 'badge-urgent', 'high' => 'badge-warning', 'normal' => 'badge-info', 'low' => 'badge-secondary'];
?>

<!-- Overall Summary -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value"><?= $summary['clients'] ?></div>
        <div class="stat-label"><?= __("dashboard.total_clients") ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $summary['activeServices'] ?>/<?= $summary['totalServices'] ?></div>
        <div class="stat-label"><?= __('reports.active_total') ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $summary['activeProjects'] ?>/<?= $summary['totalProjects'] ?></div>
        <div class="stat-label"><?= __('reports.projects_active') ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $summary['activeTasks'] ?>/<?= $summary['totalTasks'] ?></div>
        <div class="stat-label"><?= __('reports.tasks_active') ?></div>
    </div>
    <div class="stat-card stat-card-success">
        <div class="stat-value"><?php $currencyValues = $mrr; require __DIR__ . '/../partials/currency_stat.php'; ?></div>
        <div class="stat-label"><?= __("finance.mrr") ?></div>
    </div>
    <div class="stat-card <?= !empty($summary['pendingDuesByCurrency']) ? 'stat-card-warning' : '' ?>">
        <div class="stat-value"><?php $currencyValues = $summary['pendingDuesByCurrency']; require __DIR__ . '/../partials/currency_stat.php'; ?></div>
        <div class="stat-label"><?= __("finance.pending_dues") ?></div>
    </div>
</div>

<!-- Monthly Activity -->
<?php if (!empty($monthlyActivity)): ?>
<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= __("reports.monthly_activity") ?></h2>
    </div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th><?= __("finance.month") ?></th>
                    <th><?= __("reports.new_clients") ?></th>
                    <th><?= __("reports.new_services") ?></th>
                    <th><?= __("reports.completed_tasks") ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (array_reverse($monthlyActivity) as $row): ?>
                <tr>
                    <td><?= $formatMonth($row['month']) ?></td>
                    <td><?= $row['new_clients'] ?></td>
                    <td><?= $row['new_services'] ?></td>
                    <td><?= $row['tasks_completed'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Client Analytics Section -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= __('reports.client_analytics') ?></h2>
    </div>

    <div class="flex gap-3" style="flex-wrap: wrap;">
        <!-- Client types -->
        <div style="flex: 1; min-width: 250px;">
            <h3 class="mb-2"><?= __('reports.clients_by_type') ?></h3>
            <?php if (!empty($clientsByType)): ?>
                <?php
                $clientTypeLabels = ['individual' => __('clients.individual'), 'company' => __('clients.company'), 'government' => __('clients.government'), 'ngo' => __('clients.ngo')];
                ?>
                <?php foreach ($clientsByType as $row): ?>
                    <div class="flex flex-between mb-1">
                        <span><?= $clientTypeLabels[$row['type']] ?? $row['type'] ?></span>
                        <strong><?= $row['count'] ?></strong>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted"><?= __('reports.no_data') ?></p>
            <?php endif; ?>
        </div>

        <!-- Client growth -->
        <div style="flex: 2; min-width: 300px;">
            <h3 class="mb-2"><?= __('reports.client_growth') ?></h3>
            <?php if (!empty($clientGrowth)): ?>
                <?php $maxGrowth = max(array_column($clientGrowth, 'count')); ?>
                <?php foreach ($clientGrowth as $row): ?>
                    <div class="flex gap-2 mb-1" style="align-items: center;">
                        <span style="width: 100px; font-size: 0.85rem;"><?= $formatMonth($row['month']) ?></span>
                        <div class="progress-bar" style="flex: 1;">
                            <div class="progress-track">
                                <div class="progress-fill" style="width: <?= $maxGrowth > 0 ? round(($row['count'] / $maxGrowth) * 100) : 0 ?>%"></div>
                            </div>
                        </div>
                        <strong style="width: 30px;"><?= $row['count'] ?></strong>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted"><?= __('reports.no_data') ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Top Clients by Revenue -->
<?php if (!empty($topClients)): ?>
<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= __('reports.top_clients') ?></h2>
    </div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th><?= __("common.client") ?></th>
                    <th><?= __("finance.service_count") ?></th>
                    <th><?= __("finance.total_value") ?></th>
                    <th><?= __("finance.monthly_value") ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($topClients as $client): ?>
                <tr>
                    <td><a href="/clients/<?= $client['id'] ?>"><?= htmlspecialchars($client['name']) ?></a></td>
                    <td><?= $client['service_count'] ?></td>
                    <td><?= number_format((float)$client['total_value'], 0) ?> <?= $defaultCurrency ?></td>
                    <td><strong><?= number_format((float)$client['monthly_value'], 0) ?> <?= $defaultCurrency ?></strong></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Service Analytics -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= __('reports.service_analytics') ?></h2>
    </div>

    <div class="flex gap-3" style="flex-wrap: wrap;">
        <!-- Revenue by type -->
        <div style="flex: 1; min-width: 300px;">
            <h3 class="mb-2"><?= __('reports.revenue_by_type') ?></h3>
            <?php if (!empty($revenueByType)): ?>
                <table class="table">
                    <thead>
                        <tr><th><?= __("common.type") ?></th><th><?= __('reports.count') ?></th><th><?= __('finance.monthly_value') ?></th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($revenueByType as $row): ?>
                        <tr>
                            <td><?= $typeLabels[$row['type']] ?? $row['type'] ?></td>
                            <td><?= $row['count'] ?></td>
                            <td><?= number_format((float)$row['monthly_value'], 0) ?> <?= $defaultCurrency ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-muted"><?= __('reports.no_active_services') ?></p>
            <?php endif; ?>
        </div>

        <!-- Services by type/status breakdown -->
        <div style="flex: 1; min-width: 300px;">
            <h3 class="mb-2"><?= __('reports.services_by_type_status') ?></h3>
            <?php if (!empty($servicesByType)): ?>
                <?php
                $statusLabelsAr = ['active' => __('services.status.active'), 'expired' => __('services.status.expired'), 'paused' => __('services.status.paused'), 'cancelled' => __('services.status.cancelled')];
                $statusBadgesMap = ['active' => 'badge-success', 'expired' => 'badge-urgent', 'paused' => 'badge-warning', 'cancelled' => 'badge-secondary'];
                ?>
                <table class="table">
                    <thead>
                        <tr><th><?= __("common.type") ?></th><th><?= __("common.status") ?></th><th><?= __('reports.count') ?></th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($servicesByType as $row): ?>
                        <tr>
                            <td><?= $typeLabels[$row['type']] ?? htmlspecialchars($row['type']) ?></td>
                            <td><span class="badge <?= $statusBadgesMap[$row['status']] ?? 'badge-info' ?>"><?= $statusLabelsAr[$row['status']] ?? htmlspecialchars($row['status']) ?></span></td>
                            <td><?= $row['count'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-muted"><?= __('reports.no_data') ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Service Expiry Forecast -->
<?php if (!empty($expiryForecast)): ?>
<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= __('reports.expiry_forecast') ?></h2>
    </div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th><?= __("finance.month") ?></th>
                    <th><?= __("finance.service_count") ?></th>
                    <th><?= __('reports.value_at_risk') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($expiryForecast as $row): ?>
                <tr>
                    <td><?= $formatMonth($row['month']) ?></td>
                    <td><span class="badge badge-warning"><?= $row['count'] ?></span></td>
                    <td><?= number_format((float)$row['value_at_risk'], 0) ?> <?= $defaultCurrency ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Project Analytics -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= __('reports.project_analytics') ?></h2>
    </div>

    <div class="stats-grid" style="margin-bottom: 1.5rem;">
        <div class="stat-card">
            <div class="stat-value"><?= $projectStats['rate'] ?>%</div>
            <div class="stat-label"><?= __('reports.completion_rate') ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $projectStats['completed'] ?>/<?= $projectStats['total'] ?></div>
            <div class="stat-label"><?= __('reports.completed_projects') ?></div>
        </div>
        <div class="stat-card <?= $projectStats['overdue'] > 0 ? 'stat-card-danger' : '' ?>">
            <div class="stat-value"><?= $projectStats['overdue'] ?></div>
            <div class="stat-label"><?= __('reports.overdue_projects') ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $avgProjectProgress ?>%</div>
            <div class="stat-label"><?= __('reports.avg_progress') ?></div>
        </div>
    </div>

    <div class="flex gap-3" style="flex-wrap: wrap;">
        <!-- Status breakdown -->
        <div style="flex: 1; min-width: 250px;">
            <h3 class="mb-2"><?= __('reports.by_status') ?></h3>
            <?php foreach ($projectStatusBreakdown as $row): ?>
                <div class="flex flex-between mb-1">
                    <span class="badge <?= $projectStatusBadges[$row['status']] ?? 'badge-info' ?>"><?= $projectStatusLabels[$row['status']] ?? $row['status'] ?></span>
                    <strong><?= $row['count'] ?></strong>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Projects by client -->
        <?php if (!empty($projectsByClient)): ?>
        <div style="flex: 2; min-width: 300px;">
            <h3 class="mb-2"><?= __('reports.projects_by_client') ?></h3>
            <table class="table">
                <thead>
                    <tr><th><?= __("common.client") ?></th><th><?= __("common.total") ?></th><th><?= __("projects.status.in_progress") ?></th><th><?= __("projects.status.completed") ?></th></tr>
                </thead>
                <tbody>
                    <?php foreach ($projectsByClient as $row): ?>
                    <tr>
                        <td><a href="/clients/<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></a></td>
                        <td><?= $row['project_count'] ?></td>
                        <td><?= $row['in_progress'] ?></td>
                        <td><?= $row['completed'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Task Analytics -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= __('reports.task_analytics') ?></h2>
    </div>

    <div class="stats-grid" style="margin-bottom: 1.5rem;">
        <div class="stat-card">
            <div class="stat-value"><?= $taskStats['rate'] ?>%</div>
            <div class="stat-label"><?= __('reports.completion_rate') ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $taskStats['completed'] ?>/<?= $taskStats['total'] ?></div>
            <div class="stat-label"><?= __('reports.completed_tasks') ?></div>
        </div>
        <div class="stat-card <?= $taskStats['overdue'] > 0 ? 'stat-card-danger' : '' ?>">
            <div class="stat-value"><?= $taskStats['overdue'] ?></div>
            <div class="stat-label"><?= __('reports.overdue_tasks') ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $avgCompletionTime ?></div>
            <div class="stat-label"><?= __('reports.avg_completion_days') ?></div>
        </div>
    </div>

    <div class="flex gap-3" style="flex-wrap: wrap;">
        <!-- Priority breakdown -->
        <div style="flex: 1; min-width: 250px;">
            <h3 class="mb-2"><?= __('reports.by_priority') ?></h3>
            <?php foreach ($tasksByPriority as $row): ?>
                <div class="flex flex-between mb-1">
                    <span class="badge <?= $priorityBadges[$row['priority']] ?? 'badge-info' ?>"><?= $priorityLabels[$row['priority']] ?? $row['priority'] ?></span>
                    <strong><?= $row['count'] ?></strong>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Status breakdown -->
        <div style="flex: 1; min-width: 250px;">
            <h3 class="mb-2"><?= __('reports.by_status') ?></h3>
            <?php foreach ($taskStatusBreakdown as $row): ?>
                <div class="flex flex-between mb-1">
                    <span><?= $taskStatusLabels[$row['status']] ?? $row['status'] ?></span>
                    <strong><?= $row['count'] ?></strong>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Time Tracking -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= __('reports.time_tracking') ?></h2>
    </div>
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value"><?= number_format($timeTracking['totalEstimated'], 1) ?></div>
            <div class="stat-label"><?= __('reports.estimated_hours') ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= number_format($timeTracking['totalActual'], 1) ?></div>
            <div class="stat-label"><?= __('reports.actual_hours') ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $timeTracking['tasksWithTime'] ?></div>
            <div class="stat-label"><?= __('reports.tasks_with_time') ?></div>
        </div>
        <div class="stat-card <?= $timeTracking['efficiency'] > 100 ? 'stat-card-danger' : ($timeTracking['efficiency'] > 0 ? 'stat-card-success' : '') ?>">
            <div class="stat-value"><?= $timeTracking['efficiency'] ?>%</div>
            <div class="stat-label"><?= __('reports.efficiency') ?></div>
        </div>
    </div>
</div>

<!-- Task Workload by Assignee -->
<?php if (!empty($tasksByAssignee)): ?>
<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= __('reports.workload_by_employee') ?></h2>
    </div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th><?= __("users.employee") ?></th>
                    <th><?= __("common.total") ?></th>
                    <th><?= __("common.active") ?></th>
                    <th><?= __("projects.status.completed") ?></th>
                    <th><?= __("tasks.actual_hours") ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tasksByAssignee as $row): ?>
                <tr>
                    <td><a href="/users/<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></a></td>
                    <td><?= $row['total_tasks'] ?></td>
                    <td><span class="badge badge-warning"><?= $row['active'] ?></span></td>
                    <td><span class="badge badge-success"><?= $row['completed'] ?></span></td>
                    <td><?= number_format((float)$row['total_hours'], 1) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Client Service Counts -->
<?php if (!empty($clientServiceCounts)): ?>
<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= __('reports.clients_by_services') ?></h2>
    </div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th><?= __("common.client") ?></th>
                    <th><?= __("dashboard.active_services") ?></th>
                    <th><?= __("common.total") ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clientServiceCounts as $row): ?>
                <tr>
                    <td><a href="/clients/<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></a></td>
                    <td><span class="badge badge-success"><?= $row['active_services'] ?></span></td>
                    <td><?= $row['total_services'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
