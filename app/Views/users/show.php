<?php $title = htmlspecialchars($showUser['name']); ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<?php
$roleBadge = match($showUser['role']) {
    'admin' => 'badge-urgent',
    'manager' => 'badge-warning',
    default => 'badge-info'
};

$statusLabels = [
    'draft' => __('tasks.status.draft'), 'assigned' => __('tasks.status.assigned'), 'in_progress' => __('projects.status.in_progress'),
    'in_review' => __('tasks.status.in_review'), 'revision_needed' => __('tasks.status.revision_needed'),
    'completed' => __('projects.status.completed'), 'on_hold' => __('tasks.status.on_hold'), 'blocked' => __('tasks.status.blocked'), 'cancelled' => __('services.status.cancelled'),
];
$statusBadges = [
    'draft' => 'badge-secondary', 'assigned' => 'badge-info', 'in_progress' => 'badge-primary',
    'in_review' => 'badge-warning', 'revision_needed' => 'badge-urgent',
    'completed' => 'badge-success', 'on_hold' => 'badge-secondary',
    'blocked' => 'badge-urgent', 'cancelled' => 'badge-secondary',
];
$priorityLabels = ['urgent' => __('tasks.priority.urgent'), 'high' => __('notes.priority.high'), 'normal' => __('notes.priority.normal'), 'low' => __('notes.priority.low')];
$priorityBadges = ['urgent' => 'badge-urgent', 'high' => 'badge-warning', 'normal' => 'badge-info', 'low' => 'badge-secondary'];
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= htmlspecialchars($showUser['name']) ?></h2>
        <div class="flex gap-2">
            <a href="/users" class="btn btn-secondary"><?= __("common.back") ?></a>
            <a href="/users/<?= $showUser['id'] ?>/edit" class="btn btn-primary"><?= __("common.edit") ?></a>
        </div>
    </div>

    <div class="mb-3">
        <span class="badge <?= $roleBadge ?>">
            <?= match($showUser['role']) {
                'admin' => __('users.role.admin'),
                'manager' => __('users.role.manager'),
                default => __('users.role.employee')
            } ?>
        </span>
        <?php if ($showUser['is_active']): ?>
            <span class="badge badge-success"><?= __("common.active") ?></span>
        <?php else: ?>
            <span class="badge badge-secondary"><?= __("common.inactive") ?></span>
        <?php endif; ?>
    </div>

    <table class="table" style="width: auto;">
        <tr><th><?= __('common.name') ?>:</th><td><?= htmlspecialchars($showUser['name']) ?></td></tr>
        <tr><th><?= __('common.email') ?>:</th><td><?= htmlspecialchars($showUser['email']) ?></td></tr>
        <tr><th><?= __('users.department') ?>:</th><td><?= htmlspecialchars($showUser['department'] ?? '-') ?></td></tr>
        <tr><th><?= __('users.capacity') ?>:</th><td><?= $showUser['max_tasks_capacity'] ?> <?= __('tasks.task') ?></td></tr>
        <tr><th><?= __('common.created_at') ?>:</th><td><?= $showUser['created_at'] ?></td></tr>
    </table>
</div>

<?php
$activeCount = count(array_filter($tasks, fn($t) => !in_array($t['status'], ['completed', 'cancelled'])));
$completedCount = count(array_filter($tasks, fn($t) => $t['status'] === 'completed'));
$overdueCount = count(array_filter($tasks, fn($t) =>
    !in_array($t['status'], ['completed', 'cancelled']) &&
    $t['due_date'] < date('Y-m-d')
));
?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value"><?= $activeCount ?></div>
        <div class="stat-label"><?= __("users.active_tasks") ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $completedCount ?></div>
        <div class="stat-label"><?= __("reports.completed_tasks") ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $overdueCount ?></div>
        <div class="stat-label"><?= __("common.overdue") ?></div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= __("tasks.title") ?></h2>
        <a href="/tasks/create?assigned_to=<?= $showUser['id'] ?>" class="btn btn-primary">+ <?= __("tasks.create") ?></a>
    </div>

    <?php if (empty($tasks)): ?>
        <p><?= __("tasks.empty") ?></p>
    <?php else: ?>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th><?= __("tasks.task") ?></th>
                        <th><?= __("common.status") ?></th>
                        <th><?= __("common.status") ?></th>
                        <th><?= __("common.due_date") ?></th>
                        <th><?= __("common.progress") ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tasks as $task): ?>
                        <tr>
                            <td><a href="/tasks/<?= $task['id'] ?>"><?= htmlspecialchars($task['title']) ?></a></td>
                            <td>
                                <span class="badge <?= $statusBadges[$task['status']] ?? 'badge-secondary' ?>">
                                    <?= $statusLabels[$task['status']] ?? $task['status'] ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?= $priorityBadges[$task['priority']] ?? 'badge-secondary' ?>">
                                    <?= $priorityLabels[$task['priority']] ?? $task['priority'] ?>
                                </span>
                            </td>
                            <td><?= $task['due_date'] ?></td>
                            <td>
                                <div class="progress-bar">
                                    <div class="progress-track">
                                        <div class="progress-fill <?= ($task['progress_pct'] ?? 0) == 100 ? 'progress-fill-success' : '' ?>" style="width: <?= $task['progress_pct'] ?? 0 ?>%"></div>
                                    </div>
                                    <small><?= $task['progress_pct'] ?? 0 ?>%</small>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
