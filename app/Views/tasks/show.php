<?php $title = htmlspecialchars($task['title']); ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<?php
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
        <div>
            <h2 class="card-title"><?= htmlspecialchars($task['title']) ?></h2>
            <div class="flex gap-2 mt-2">
                <span class="badge <?= $statusBadges[$task['status']] ?? 'badge-secondary' ?>"><?= $statusLabels[$task['status']] ?? $task['status'] ?></span>
                <span class="badge <?= $priorityBadges[$task['priority']] ?? 'badge-secondary' ?>"><?= $priorityLabels[$task['priority']] ?? $task['priority'] ?></span>
            </div>
        </div>
        <div class="flex gap-2">
            <a href="/tasks/<?= $task['id'] ?>/edit" class="btn btn-secondary"><?= __("common.edit") ?></a>
            <form method="POST" action="/tasks/<?= $task['id'] ?>/delete" onsubmit="return confirm('<?= __('common.confirm_delete') ?>')" style="display:inline;">
                <?= \App\Core\CSRF::field() ?>
                <button type="submit" class="btn btn-danger"><?= __("common.delete") ?></button>
            </form>
        </div>
    </div>

    <div class="mb-3">
        <?php if ($task['description']): ?>
            <div class="mb-3">
                <h4><?= __("common.description") ?></h4>
                <p><?= nl2br(htmlspecialchars($task['description'])) ?></p>
            </div>
        <?php endif; ?>

        <table class="table" style="width: auto;">
            <tr><th><?= __('tasks.assignee') ?>:</th><td><?= htmlspecialchars($task['assignee_name'] ?? __('common.unassigned')) ?></td></tr>
            <tr><th><?= __('common.created_at') ?>:</th><td><?= htmlspecialchars($task['creator_name']) ?></td></tr>
            <?php if ($task['client_name']): ?>
                <tr><th><?= __('common.client') ?>:</th><td><a href="/clients/<?= $task['client_id'] ?>"><?= htmlspecialchars($task['client_name']) ?></a></td></tr>
            <?php endif; ?>
            <?php if ($task['project_title']): ?>
                <tr><th><?= __("common.project") ?>:</th><td><a href="/projects/<?= $task['project_id'] ?>"><?= htmlspecialchars($task['project_title']) ?></a></td></tr>
            <?php endif; ?>
            <?php if ($task['service_title']): ?>
                <tr><th><?= __("tasks.service") ?>:</th><td><a href="/services/<?= $task['service_id'] ?>"><?= htmlspecialchars($task['service_title']) ?></a></td></tr>
            <?php endif; ?>
            <tr><th><?= __("tasks.start_date") ?>:</th><td><?= $task['start_date'] ?? __('common.unassigned') ?></td></tr>
            <tr><th><?= __("tasks.due") ?>:</th><td><?= $task['due_date'] ?> (<?= $task['days_remaining'] >= 0 ? $task['days_remaining'] . ' ' . __('common.day') : ''  . abs($task['days_remaining']) . ' ' . __('common.day') ?>)</td></tr>
            <?php if ($task['completed_at']): ?>
                <tr><th><?= __('common.date') ?>:</th><td><?= $task['completed_at'] ?></td></tr>
            <?php endif; ?>
        </table>

        <!-- Progress -->
        <div class="mt-3">
            <h4><?= __('common.progress') ?></h4>
            <div class="progress-bar progress-bar-lg">
                <div class="progress-track">
                    <div class="progress-fill <?= $task['progress_pct'] == 100 ? 'progress-fill-success' : '' ?>" style="width: <?= $task['progress_pct'] ?>%">
                        <?= $task['progress_pct'] ?>%
                    </div>
                </div>
            </div>
            <div class="flex gap-3 mt-2">
                <?php if ($task['estimated_hours']): ?>
                    <span><?= __('tasks.estimated_hours') ?>: <strong><?= $task['estimated_hours'] ?> h</strong></span>
                <?php endif; ?>
                <span><?= __('tasks.actual_hours') ?>: <strong><?= $task['actual_hours'] ?> h</strong></span>
                <?php if ($task['estimated_hours']): ?>
                    <?php $variance = $task['actual_hours'] - $task['estimated_hours']; ?>
                    <span>: <strong style="color: <?= $variance > 0 ? 'var(--danger-color)' : 'var(--success-color)' ?>"><?= $variance > 0 ? '+' : '' ?><?= $variance ?> h</strong></span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Status Update Form -->
    <div class="card card-light mb-3">
        <h4><?= __('common.status') ?></h4>
        <form method="POST" action="/tasks/<?= $task['id'] ?>/status">
            <?= \App\Core\CSRF::field() ?>
            <div class="flex gap-2" style="flex-wrap: wrap;">
                <select name="status" class="form-select" style="width: 200px;">
                    <?php foreach ($statusLabels as $key => $label): ?>
                        <option value="<?= $key ?>" <?= $task['status'] === $key ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="number" name="hours" class="form-input" placeholder="h" step="0.25" min="0" max="24" style="width: 140px;">
                <input type="text" name="comment" class="form-input"  style="flex: 1; min-width: 200px;">
                <button type="submit" class="btn btn-primary"><?= __("common.save") ?></button>
            </div>
        </form>
    </div>
</div>

<!-- Comments -->
<div class="card">
    <h3 class="mb-2"><?= __('common.details') ?></h3>

    <?php if (!empty($comments)): ?>
        <div class="comments-list">
            <?php foreach ($comments as $comment): ?>
                <div class="comment <?= $comment['is_system_generated'] ? 'comment-system' : '' ?>">
                    <div class="flex flex-between mb-1">
                        <strong><?= htmlspecialchars($comment['user_name']) ?></strong>
                        <small class="text-muted"><?= $comment['created_at'] ?></small>
                    </div>
                    <div><?= nl2br(htmlspecialchars($comment['message'])) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="text-muted"><?= __('safe.empty') ?></p>
    <?php endif; ?>

    <!-- Add Comment -->
    <form method="POST" action="/tasks/<?= $task['id'] ?>/comments" class="mt-3">
        <?= \App\Core\CSRF::field() ?>
        <div class="form-group">
            <label class="form-label"><?= __('common.add') ?></label>
            <textarea name="message" class="form-textarea" rows="3"  required></textarea>
        </div>
        <div class="form-group">
            <label class="form-label"><?= __('tasks.actual_hours') ?></label>
            <input type="number" name="hours" class="form-input"  step="0.25" min="0" max="24" style="width: 200px;">
        </div>
        <button type="submit" class="btn btn-primary"><?= __('common.save') ?></button>
    </form>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
