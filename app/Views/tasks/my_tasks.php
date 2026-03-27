<?php $title = __('tasks.my_tasks'); ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<?php
$statusLabels = [
    'assigned' => __('tasks.status.assigned'),
    'in_progress' => __('projects.status.in_progress'),
    'in_review' => __('tasks.status.in_review'),
    'revision_needed' => __('tasks.status.revision_needed'),
    'completed' => __('projects.status.completed'),
    'on_hold' => __('tasks.status.on_hold'),
    'blocked' => __('tasks.status.blocked'),
];

$statusBadges = [
    'assigned' => 'badge-info',
    'in_progress' => 'badge-primary',
    'in_review' => 'badge-warning',
    'revision_needed' => 'badge-urgent',
    'completed' => 'badge-success',
    'on_hold' => 'badge-secondary',
    'blocked' => 'badge-urgent',
];

$priorityLabels = ['urgent' => __('tasks.priority.urgent'), 'high' => __('notes.priority.high'), 'normal' => __('notes.priority.normal'), 'low' => __('notes.priority.low')];
$priorityBadges = ['urgent' => 'badge-urgent', 'high' => 'badge-warning', 'normal' => 'badge-info', 'low' => 'badge-secondary'];

$workloadClass = match($dashboardData['workload']['status']) {
    'overloaded' => 'stat-card-danger',
    'warning' => 'stat-card-warning',
    default => 'stat-card-success'
};
?>

<!-- Summary Cards -->
<div class="stats-grid mb-3">
    <div class="stat-card stat-card-danger">
        <div class="stat-value"><?= $dashboardData['overdue_count'] ?></div>
        <div class="stat-label"><?= __("common.overdue") ?></div>
    </div>
    <div class="stat-card stat-card-warning">
        <div class="stat-value"><?= count($dashboardData['due_today']) ?></div>
        <div class="stat-label"><?= __("common.today") ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $dashboardData['active_count'] ?></div>
        <div class="stat-label"><?= __("common.active") ?></div>
    </div>
    <div class="stat-card <?= $workloadClass ?>">
        <div class="stat-value"><?= $dashboardData['workload']['percentage'] ?>%</div>
        <div class="stat-label"><?= __("common.progress") ?> (<?= $dashboardData['workload']['active_count'] ?>/<?= $dashboardData['workload']['capacity'] ?>)</div>
    </div>
</div>

<?php if (!empty($dashboardData['due_today'])): ?>
<div class="card card-border-warning mb-3">
    <div class="card-header">
        <h3 class="card-title"><?= __("common.today") ?></h3>
    </div>
    <div class="table-container">
        <table class="table">
            <tbody>
                <?php foreach ($dashboardData['due_today'] as $task): ?>
                    <tr>
                        <td><a href="/tasks/<?= $task['id'] ?>"><?= htmlspecialchars($task['title']) ?></a></td>
                        <td>
                            <span class="badge <?= $priorityBadges[$task['priority']] ?>"><?= $priorityLabels[$task['priority']] ?></span>
                        </td>
                        <td><?= $task['client_name'] ? htmlspecialchars($task['client_name']) : '-' ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= __("tasks.my_tasks") ?></h2>
        <div class="flex gap-2">
            <select id="statusFilter" class="form-select" style="width: 150px;">
                <option value=""><?= __("common.all_statuses") ?></option>
                <?php foreach ($statusLabels as $key => $label): ?>
                    <option value="<?= $key ?>"><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>
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
                        <th><?= __("tasks.due") ?></th>
                        <th><?= __("common.progress") ?></th>
                        <th><?= __("tasks.actual_hours") ?></th>
                        <th><?= __("common.actions") ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tasks as $task):
                        if ($task['status'] === 'completed') continue;
                    ?>
                        <tr data-status="<?= $task['status'] ?>">
                            <td>
                                <a href="/tasks/<?= $task['id'] ?>"><?= htmlspecialchars($task['title']) ?></a>
                                <?php if ($task['client_name']): ?>
                                    <br><small class="text-muted"><?= htmlspecialchars($task['client_name']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="POST" action="/tasks/<?= $task['id'] ?>/status" style="display:inline;">
                                    <?= \App\Core\CSRF::field() ?>
                                    <select name="status" class="form-select" style="width: 130px;" onchange="this.form.submit()">
                                        <?php foreach ($statusLabels as $key => $label): ?>
                                            <option value="<?= $key ?>" <?= $task['status'] === $key ? 'selected' : '' ?>><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                            </td>
                            <td>
                                <span class="badge <?= $priorityBadges[$task['priority']] ?>"><?= $priorityLabels[$task['priority']] ?></span>
                            </td>
                            <td>
                                <?php
                                $daysRemaining = $task['days_remaining'];
                                if ($daysRemaining < 0) {
                                    echo '<span class="badge badge-urgent">' . __('tasks.overdue_days', ['days' => abs($daysRemaining)]) . '</span>';
                                } elseif ($daysRemaining === 0) {
                                    echo '<span class="badge badge-urgent">' . __('common.today') . '</span>';
                                } else {
                                    echo '<span class="badge badge-info">' . $daysRemaining . ' ' . __('common.days') . '</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <form method="POST" action="/tasks/<?= $task['id'] ?>/progress" class="form-inline">
                                    <?= \App\Core\CSRF::field() ?>
                                    <input type="range" name="progress" min="0" max="100" value="<?= $task['progress_pct'] ?>" style="width: 100px;" onchange="this.form.submit()">
                                    <small><?= $task['progress_pct'] ?>%</small>
                                </form>
                            </td>
                            <td>
                                <?php if ($task['estimated_hours']): ?>
                                    <small><?= $task['actual_hours'] ?>/<?= $task['estimated_hours'] ?> h</small>
                                <?php else: ?>
                                    <small><?= $task['actual_hours'] ?> h</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button onclick="showTimeLogModal(<?= $task['id'] ?>)" class="btn btn-sm btn-secondary"><?= __('tasks.actual_hours') ?></button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Time Log Modal -->
<div id="timeLogModal" class="modal">
    <div class="modal-content">
        <h3><?= __('tasks.actual_hours') ?></h3>
        <form id="timeLogForm" method="POST" action="">
            <?= \App\Core\CSRF::field() ?>

            <div class="form-group">
                <label class="form-label"><?= __('tasks.actual_hours') ?></label>
                <input type="number" name="hours" step="0.25" min="0.25" max="24" class="form-input" required>
            </div>

            <div class="form-group">
                <label class="form-label"><?= __('common.description') ?></label>
                <input type="text" name="description" class="form-input" >
            </div>

            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary"><?= __("common.save") ?></button>
                <button type="button" onclick="hideTimeLogModal()" class="btn btn-secondary"><?= __("common.cancel") ?></button>
            </div>
        </form>
    </div>
</div>

<script>
function showTimeLogModal(taskId) {
    document.getElementById('timeLogForm').action = '/tasks/' + taskId + '/log-time';
    document.getElementById('timeLogModal').style.display = 'block';
}

function hideTimeLogModal() {
    document.getElementById('timeLogModal').style.display = 'none';
}

// Status filter
document.getElementById('statusFilter').addEventListener('change', function() {
    var status = this.value;
    var rows = document.querySelectorAll('tbody tr');
    rows.forEach(function(row) {
        if (!status || row.dataset.status === status) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
