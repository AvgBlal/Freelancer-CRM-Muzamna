<?php $title = __('dashboard.title'); ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value"><?= $stats['clients'] ?></div>
        <div class="stat-label"><?= __('dashboard.total_clients') ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $stats['activeServices'] ?></div>
        <div class="stat-label"><?= __('dashboard.active_services') ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $stats['expiredServices'] ?></div>
        <div class="stat-label"><?= __('dashboard.expired_services') ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $stats['activeProjects'] ?>/<?= $stats['totalProjects'] ?></div>
        <div class="stat-label"><?= __('dashboard.active_projects') ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?php $currencyValues = $dueStats['totalPendingByCurrency']; require __DIR__ . '/../partials/currency_stat.php'; ?></div>
        <div class="stat-label"><?= __('dashboard.pending_dues') ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?php $currencyValues = $expenseStats['totalPendingByCurrency']; require __DIR__ . '/../partials/currency_stat.php'; ?></div>
        <div class="stat-label"><?= __('dashboard.pending_expenses') ?></div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= __('dashboard.expiring_services') ?></h2>
        <a href="/services" class="btn btn-secondary"><?= __('common.view_all') ?></a>
    </div>
    <?php if (empty($expiringServices)): ?>
        <p><?= __('dashboard.expiring_none') ?></p>
    <?php else: ?>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th><?= __('services.service') ?></th>
                        <th><?= __('common.client') ?></th>
                        <th><?= __('services.end_date') ?></th>
                        <th><?= __('common.remaining') ?></th>
                        <th><?= __('common.status') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($expiringServices as $service): ?>
                        <tr>
                            <td><a href="/services/<?= $service['id'] ?>"><?= htmlspecialchars($service['title']) ?></a></td>
                            <td><?= htmlspecialchars($service['client_names'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($service['end_date'] ?? '') ?></td>
                            <td><strong><?= $service['days_until'] ?? '?' ?> <?= __('common.day') ?></strong></td>
                            <td>
                                <?php if ($service['urgency'] === 'urgent'): ?>
                                    <span class="badge badge-urgent"><?= __('dashboard.urgency.critical') ?></span>
                                <?php elseif ($service['urgency'] === 'danger'): ?>
                                    <span class="badge badge-urgent"><?= __('dashboard.urgency.urgent') ?></span>
                                <?php elseif ($service['urgency'] === 'warning'): ?>
                                    <span class="badge badge-warning"><?= __('dashboard.urgency.warning') ?></span>
                                <?php else: ?>
                                    <span class="badge badge-info"><?= __('dashboard.urgency.near') ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= __('dashboard.overdue_projects') ?></h2>
        <a href="/projects" class="btn btn-secondary"><?= __('common.view_all') ?></a>
    </div>
    <?php if (empty($overdueProjects)): ?>
        <p><?= __('dashboard.overdue_projects_none') ?></p>
    <?php else: ?>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th><?= __('projects.project') ?></th>
                        <th><?= __('common.client') ?></th>
                        <th><?= __('projects.delivery_date') ?></th>
                        <th><?= __('projects.days_overdue') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($overdueProjects as $project): ?>
                        <tr>
                            <td><a href="/projects/<?= $project['id'] ?>"><?= htmlspecialchars($project['title']) ?></a></td>
                            <td><?= htmlspecialchars($project['client_name']) ?></td>
                            <td><?= htmlspecialchars($project['due_date'] ?? '') ?></td>
                            <td><span class="badge badge-urgent"><?= $project['days_overdue'] ?> <?= __('common.day') ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php if (!empty($overdueDues)): ?>
<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= __('dashboard.overdue_dues') ?></h2>
        <a href="/dues" class="btn btn-secondary"><?= __('common.view_all') ?></a>
    </div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th><?= __('dues.person') ?></th>
                    <th><?= __('common.amount') ?></th>
                    <th><?= __('common.due_date') ?></th>
                    <th><?= __('projects.days_overdue') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($overdueDues as $due): ?>
                    <tr>
                        <td><a href="/dues/<?= $due['id'] ?>"><?= htmlspecialchars($due['person_name']) ?></a></td>
                        <td><strong><?= number_format($due['amount'] - $due['paid_amount'], 2) ?> <?= $due['currency_code'] ?></strong></td>
                        <td><?= htmlspecialchars($due['due_date'] ?? '') ?></td>
                        <td><span class="badge badge-urgent"><?= $due['days_overdue'] ?> <?= __('common.day') ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($overdueExpenses)): ?>
<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= __('dashboard.overdue_expenses') ?></h2>
        <a href="/expenses" class="btn btn-secondary"><?= __('common.view_all') ?></a>
    </div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th><?= __('expenses.expense') ?></th>
                    <th><?= __('common.amount') ?></th>
                    <th><?= __('common.due_date') ?></th>
                    <th><?= __('projects.days_overdue') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($overdueExpenses as $expense): ?>
                    <tr>
                        <td><a href="/expenses/<?= $expense['id'] ?>"><?= htmlspecialchars($expense['title']) ?></a></td>
                        <td><strong><?= number_format($expense['amount'], 2) ?> <?= $expense['currency_code'] ?></strong></td>
                        <td><?= htmlspecialchars($expense['due_date'] ?? '') ?></td>
                        <td><span class="badge badge-urgent"><?= $expense['days_overdue'] ?> <?= __('common.day') ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($upcomingExpenses)): ?>
<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= __('dashboard.upcoming_expenses') ?></h2>
        <a href="/expenses" class="btn btn-secondary"><?= __('common.view_all') ?></a>
    </div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th><?= __('expenses.expense') ?></th>
                    <th><?= __('common.amount') ?></th>
                    <th><?= __('common.due_date') ?></th>
                    <th><?= __('common.remaining') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($upcomingExpenses as $expense): ?>
                    <tr>
                        <td><a href="/expenses/<?= $expense['id'] ?>"><?= htmlspecialchars($expense['title']) ?></a></td>
                        <td><strong><?= number_format($expense['amount'], 2) ?> <?= $expense['currency_code'] ?></strong></td>
                        <td><?= htmlspecialchars($expense['due_date'] ?? '') ?></td>
                        <td>
                            <?php if ($expense['days_until'] <= 7): ?>
                                <span class="badge badge-warning"><?= $expense['days_until'] ?> <?= __('common.day') ?></span>
                            <?php else: ?>
                                <span class="badge badge-info"><?= $expense['days_until'] ?> <?= __('common.day') ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php
$actionLabels = [
    'create' => __('action.create'), 'update' => __('action.update'), 'delete' => __('action.delete'),
    'login' => __('action.login'), 'logout' => __('action.logout'), 'auto_expire' => __('action.auto_expire'),
    'cron_run' => __('action.cron_run'), 'status_change' => __('action.status_change'),
    'renew' => __('action.renew'), 'mark_paid' => __('action.mark_paid'),
];
$entityLabels = [
    'client' => __('entity.client'), 'service' => __('entity.service'), 'project' => __('entity.project'),
    'task' => __('entity.task'), 'due' => __('entity.due'), 'expense' => __('entity.expense'),
    'note' => __('entity.note'), 'user' => __('entity.user'), 'system' => __('entity.system'),
];
?>
<?php if (!empty($recentActivity)): ?>
<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= __('dashboard.recent_activity') ?></h2>
        <a href="/logs" class="btn btn-secondary"><?= __('common.view_all') ?></a>
    </div>
    <div style="padding: 0;">
        <?php foreach ($recentActivity as $activity): ?>
            <div style="padding: 0.5rem 1rem; border-bottom: 1px solid #e0e0e0; font-size: 0.9em;">
                <span style="color: #666; font-size: 0.8em;"><?= htmlspecialchars($activity['created_at'] ?? '') ?></span>
                <strong><?= htmlspecialchars($activity['user_name'] ?? __('entity.system')) ?></strong>
                <span class="badge badge-info" style="font-size: 0.75em;"><?= $actionLabels[$activity['action'] ?? ''] ?? htmlspecialchars($activity['action'] ?? '') ?></span>
                <?= $entityLabels[$activity['entity_type'] ?? ''] ?? htmlspecialchars($activity['entity_type'] ?? '') ?>
                <?php if (!empty($activity['entity_title'])): ?>
                    "<strong><?= htmlspecialchars($activity['entity_title']) ?></strong>"
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
