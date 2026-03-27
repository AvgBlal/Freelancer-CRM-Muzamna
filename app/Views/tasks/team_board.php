<?php $title = __('tasks.team_board'); ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<?php
$statusLabels = [
    'assigned' => __('tasks.status.assigned'),
    'in_progress' => __('projects.status.in_progress'),
    'in_review' => __('tasks.status.in_review'),
    'completed' => __('projects.status.completed'),
];

$priorityBadges = ['urgent' => 'badge-urgent', 'high' => 'badge-warning', 'normal' => 'badge-info', 'low' => 'badge-secondary'];
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= __("tasks.team_board") ?></h2>
        <a href="/tasks" class="btn btn-secondary"><?= __("common.view_all") ?></a>
    </div>

    <!-- Team Workload Summary -->
    <div class="mb-3">
        <h3><?= __("common.progress") ?></h3>
        <div class="flex gap-2" style="flex-wrap: wrap;">
            <?php foreach ($teamWorkload as $member):
                $borderClass = match($member['status']) {
                    'overloaded' => 'border-top-danger',
                    'warning' => 'border-top-warning',
                    default => 'border-top-success'
                };
                $badgeClass = match($member['status']) {
                    'overloaded' => 'badge-urgent',
                    'warning' => 'badge-warning',
                    default => 'badge-success'
                };
                $barColor = match($member['status']) {
                    'overloaded' => 'var(--danger-color)',
                    'warning' => 'var(--warning-color)',
                    default => 'var(--success-color)'
                };
            ?>
                <div class="card <?= $borderClass ?>" style="min-width: 200px; flex: 1;">
                    <div class="flex flex-between mb-1">
                        <strong><?= htmlspecialchars($member['name']) ?></strong>
                        <span class="badge <?= $badgeClass ?>">
                            <?= $member['active_tasks'] ?>/<?= $member['capacity'] ?>
                        </span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-track">
                            <div class="progress-fill" style="width: <?= min($member['workload_percentage'], 100) ?>%; background: <?= $barColor ?>;"></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Kanban Board -->
    <div class="kanban-board">
        <?php foreach ($statusLabels as $status => $label): ?>
            <div class="card card-light">
                <div class="card-header">
                    <h4><?= $label ?></h4>
                    <span class="badge badge-info"><?= count(array_filter($tasks, fn($t) => $t['status'] === $status)) ?></span>
                </div>

                <div class="kanban-column">
                    <?php foreach ($tasks as $task):
                        if ($task['status'] !== $status) continue;
                        $borderClass = match($task['priority']) {
                            'urgent' => 'border-right-danger',
                            'high' => 'border-right-warning',
                            default => 'border-right-success'
                        };
                    ?>
                        <div class="card kanban-card mb-2 <?= $borderClass ?>" onclick="location.href='/tasks/<?= $task['id'] ?>'">
                            <div class="flex flex-between mb-1">
                                <strong style="font-size: 0.875rem;"><?= htmlspecialchars($task['title']) ?></strong>
                                <span class="badge <?= $priorityBadges[$task['priority']] ?>"><?= $task['priority'] ?></span>
                            </div>

                            <div class="text-muted mb-1" style="font-size: 0.75rem;">
                                <?= htmlspecialchars($task['assignee_name'] ?? __('common.unassigned')) ?>
                            </div>

                            <div class="flex flex-between text-muted" style="font-size: 0.75rem;">
                                <span><?= $task['due_date'] ?></span>
                                <span><?= $task['progress_pct'] ?>%</span>
                            </div>

                            <?php if ($task['days_remaining'] < 0): ?>
                                <span class="badge badge-urgent mt-1"><?= __("common.overdue") ?></span>
                            <?php elseif ($task['days_remaining'] <= 2): ?>
                                <span class="badge badge-warning mt-1"><?= $task['days_remaining'] ?> <?= __('common.day') ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
