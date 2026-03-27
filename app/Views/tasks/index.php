<?php $title = __('tasks.title'); ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<?php
$statusLabels = [
    'draft' => __('tasks.status.draft'),
    'assigned' => __('tasks.status.assigned'),
    'in_progress' => __('projects.status.in_progress'),
    'in_review' => __('tasks.status.in_review'),
    'revision_needed' => __('tasks.status.revision_needed'),
    'completed' => __('projects.status.completed'),
    'on_hold' => __('tasks.status.on_hold'),
    'blocked' => __('tasks.status.blocked'),
    'cancelled' => __('services.status.cancelled'),
];

$statusBadges = [
    'draft' => 'badge-secondary',
    'assigned' => 'badge-info',
    'in_progress' => 'badge-primary',
    'in_review' => 'badge-warning',
    'revision_needed' => 'badge-urgent',
    'completed' => 'badge-success',
    'on_hold' => 'badge-secondary',
    'blocked' => 'badge-urgent',
    'cancelled' => 'badge-secondary',
];

$priorityLabels = [
    'urgent' => __('tasks.priority.urgent'),
    'high' => __('notes.priority.high'),
    'normal' => __('notes.priority.normal'),
    'low' => __('notes.priority.low'),
];

$priorityBadges = [
    'urgent' => 'badge-urgent',
    'high' => 'badge-warning',
    'normal' => 'badge-info',
    'low' => 'badge-secondary',
];
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= __('tasks.title') ?></h2>
        <div class="flex gap-2">
            <div class="dropdown" style="position:relative;display:inline-block;">
                <button type="button" onclick="this.nextElementSibling.classList.toggle('hidden')" class="btn btn-secondary"><i class="fas fa-download"></i> <?= __("common.export") ?></button>
                <div class="hidden" style="position:absolute;inset-inline-start:0;top:100%;background:white;border:1px solid #e2e8f0;border-radius:6px;box-shadow:0 4px 12px rgba(0,0,0,.15);z-index:50;min-width:160px;margin-top:4px;">
                    <a href="/export/tasks" class="dropdown-item" style="display:block;padding:8px 16px;text-decoration:none;color:#333;white-space:nowrap;"><i class="fas fa-file-csv text-green-600"></i> <?= __("common.export_csv") ?></a>
                    <a href="/export/tasks/pdf" class="dropdown-item" style="display:block;padding:8px 16px;text-decoration:none;color:#333;white-space:nowrap;"><i class="fas fa-file-pdf text-red-600"></i> <?= __("common.export_pdf") ?></a>
                </div>
            </div>
            <a href="/tasks/team-board" class="btn btn-secondary"><?= __('tasks.team_board') ?></a>
            <a href="/tasks/templates" class="btn btn-secondary"><?= __('templates.title') ?></a>
            <a href="/tasks/create" class="btn btn-primary">+ <?= __("tasks.new") ?></a>
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" class="mb-3">
        <div class="flex gap-2" style="flex-wrap: wrap;">
            <select name="status" class="form-select" style="width: 150px;">
                <option value=""><?= __("common.all_statuses") ?></option>
                <?php foreach ($statusLabels as $key => $label): ?>
                    <option value="<?= $key ?>" <?= ($_GET['status'] ?? '') === $key ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>

            <select name="priority" class="form-select" style="width: 140px;">
                <option value=""><?= __("common.all_priorities") ?></option>
                <?php foreach ($priorityLabels as $key => $label): ?>
                    <option value="<?= $key ?>" <?= ($_GET['priority'] ?? '') === $key ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>

            <select name="assigned_to" class="form-select" style="width: 180px;">
                <option value=""><?= __("common.all_employees") ?></option>
                <option value="none" <?= ($_GET['assigned_to'] ?? '') === 'none' ? 'selected' : '' ?>><?= __("common.unassigned") ?></option>
                <?php foreach ($employees as $emp): ?>
                    <option value="<?= $emp['id'] ?>" <?= ($_GET['assigned_to'] ?? '') == $emp['id'] ? 'selected' : '' ?>><?= htmlspecialchars($emp['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <select name="project_filter" class="form-select" style="width: 170px;">
                <option value=""><?= __("common.all_projects") ?></option>
                <option value="none" <?= ($_GET['project_filter'] ?? '') === 'none' ? 'selected' : '' ?>><?= __("tasks.no_project") ?></option>
            </select>

            <select name="client_id" class="form-select" style="width: 180px;">
                <option value=""><?= __("common.all_clients") ?></option>
                <?php foreach ($clients as $client): ?>
                    <option value="<?= $client['id'] ?>" <?= ($_GET['client_id'] ?? '') == $client['id'] ? 'selected' : '' ?>><?= htmlspecialchars($client['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <input type="text" name="search" class="form-input" placeholder="<?= __('common.search_placeholder') ?>" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" style="width: 200px;">

            <button type="submit" class="btn btn-secondary"><?= __("common.filter") ?></button>
            <a href="/tasks" class="btn btn-secondary"><?= __("common.reset") ?></a>
        </div>
    </form>

    <?php if (empty($tasks)): ?>
        <p><?= __("tasks.empty") ?></p>
    <?php else: ?>
        <div class="table-container">
            <table class="table bulk-table">
                <thead>
                    <tr>
                        <th style="width: 30px;"><input type="checkbox" class="bulk-select-all"></th>
                        <th><?= __("tasks.task") ?></th>
                        <th><?= __("common.status") ?></th>
                        <th><?= __("common.status") ?></th>
                        <th><?= __("tasks.assignee") ?></th>
                        <th><?= __("tasks.client_project") ?></th>
                        <th><?= __("tasks.due") ?></th>
                        <th><?= __("common.progress") ?></th>
                        <th><?= __("common.actions") ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tasks as $task): ?>
                        <tr>
                            <td><input type="checkbox" class="bulk-check" value="<?= $task['id'] ?>"></td>
                            <td>
                                <a href="/tasks/<?= $task['id'] ?>"><?= htmlspecialchars($task['title']) ?></a>
                            </td>
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
                            <td><?= htmlspecialchars($task['assignee_name'] ?? __('common.unassigned')) ?></td>
                            <td>
                                <?php if ($task['client_name']): ?>
                                    <?= htmlspecialchars($task['client_name']) ?>
                                <?php elseif ($task['project_title']): ?>
                                    <?= htmlspecialchars($task['project_title']) ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $daysRemaining = $task['days_remaining'];
                                if ($daysRemaining < 0) {
                                    echo '<span class="badge badge-urgent">' . __('tasks.overdue_days', ['days' => abs($daysRemaining)]) . '</span>';
                                } elseif ($daysRemaining === 0) {
                                    echo '<span class="badge badge-urgent">' . __('common.today') . '</span>';
                                } elseif ($daysRemaining <= 3) {
                                    echo '<span class="badge badge-warning">' . $daysRemaining . ' ' . __('common.days') . '</span>';
                                } else {
                                    echo htmlspecialchars($task['due_date'] ?? '');
                                }
                                ?>
                            </td>
                            <td>
                                <div class="progress-bar">
                                    <div class="progress-track">
                                        <div class="progress-fill <?= $task['progress_pct'] == 100 ? 'progress-fill-success' : '' ?>" style="width: <?= $task['progress_pct'] ?>%"></div>
                                    </div>
                                    <small><?= $task['progress_pct'] ?>%</small>
                                </div>
                            </td>
                            <td class="flex gap-1">
                                <a href="/tasks/<?= $task['id'] ?>" class="btn btn-sm btn-secondary"><?= __("common.view") ?></a>
                                <a href="/tasks/<?= $task['id'] ?>/edit" class="btn btn-sm btn-secondary"><?= __("common.edit") ?></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
        $bulkAction = '/bulk/tasks';
        $bulkOptions = [
            'in_progress' => __('projects.status.in_progress'),
            'completed' => __('projects.status.completed'),
            'on_hold' => __('tasks.status.on_hold'),
            'cancelled' => __('services.status.cancelled'),
        ];
        require __DIR__ . '/../partials/bulk_actions.php';
        ?>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
