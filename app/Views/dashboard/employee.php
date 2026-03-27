<?php $title = __('dashboard.title'); ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value"><?= $employeeStats['activeTasks'] ?></div>
        <div class="stat-label"><?= __('users.active_tasks') ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $employeeStats['overdueTasks'] ?></div>
        <div class="stat-label"><?= __('common.overdue') ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $employeeStats['projectCount'] ?></div>
        <div class="stat-label"><?= __('nav.projects') ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $employeeStats['workload']['percentage'] ?>%</div>
        <div class="stat-label"><?= __('common.progress') ?> (<?= $employeeStats['workload']['active_count'] ?>/<?= $employeeStats['workload']['capacity'] ?>)</div>
    </div>
</div>

<?php if (!empty($employeeStats['dueTodayTasks'])): ?>
<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= __('common.today') ?></h2>
        <a href="/tasks/my" class="btn btn-secondary"><?= __('nav.my_tasks') ?></a>
    </div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th><?= __('tasks.task') ?></th>
                    <th><?= __('common.client') ?></th>
                    <th><?= __('common.status') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($employeeStats['dueTodayTasks'] as $task): ?>
                    <tr>
                        <td><a href="/tasks/<?= $task['id'] ?>"><?= htmlspecialchars($task['title']) ?></a></td>
                        <td><?= htmlspecialchars($task['client_name'] ?? '-') ?></td>
                        <td>
                            <?php
                            $priorityLabels = ['urgent' => __('tasks.priority.urgent'), 'high' => __('tasks.priority.high'), 'normal' => __('tasks.priority.normal'), 'low' => __('tasks.priority.low')];
                            $priorityClasses = ['urgent' => 'badge-urgent', 'high' => 'badge-warning', 'normal' => 'badge-info', 'low' => 'badge-info'];
                            $p = $task['priority'] ?? 'normal';
                            ?>
                            <span class="badge <?= $priorityClasses[$p] ?? 'badge-info' ?>"><?= $priorityLabels[$p] ?? $p ?></span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php else: ?>
<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= __('common.today') ?></h2>
        <a href="/tasks/my" class="btn btn-secondary"><?= __('nav.my_tasks') ?></a>
    </div>
    <p><?= __('tasks.empty') ?></p>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
