<?php $title = htmlspecialchars($project['title']); ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= htmlspecialchars($project['title']) ?></h2>
        <div class="flex gap-2">
            <a href="/projects/<?= $project['id'] ?>/edit" class="btn btn-secondary"><?= __("common.edit") ?></a>
            <form method="POST" action="/projects/<?= $project['id'] ?>/delete" onsubmit="return confirm('<?= __('common.confirm_delete') ?>')" style="display:inline;">
                <?= \App\Core\CSRF::field() ?>
                <button type="submit" class="btn btn-danger"><?= __("common.delete") ?></button>
            </form>
        </div>
    </div>

    <?php
    $statusLabels = [
        'idea' => __('notes.cat.idea'),
        'in_progress' => __('projects.status.in_progress'),
        'paused' => __('services.status.paused'),
        'completed' => __('projects.status.completed'),
        'cancelled' => __('services.status.cancelled'),
    ];
    $priorityLabels = ['low' => __('projects.priority.low'), 'normal' => __('projects.priority.normal'), 'high' => __('projects.priority.high')];
    ?>
    <div class="mb-3">
        <span class="badge badge-info"><?= $statusLabels[$project['status']] ?></span>
        <span class="badge badge-warning"><?= $priorityLabels[$project['priority']] ?></span>
    </div>

    <table class="table" style="width: auto;">
        <tr><th><?= __("common.client") ?>:</th><td><a href="/clients/<?= $project['client_id'] ?>"><?= htmlspecialchars($client['name']) ?></a></td></tr>
        <tr><th><?= __("services.start_date") ?>:</th><td><?= $project['start_date'] ?? '-' ?></td></tr>
        <tr><th><?= __("projects.delivery_date") ?>:</th><td><?= $project['due_date'] ?? '-' ?></td></tr>
        <tr><th><?= __("common.progress") ?>:</th><td><?= $project['progress'] ?>%</td></tr>
    </table>

    <?php if (!empty($project['description'])): ?>
    <div class="mt-3">
        <h4><?= __("common.description") ?></h4>
        <p><?= nl2br(htmlspecialchars($project['description'])) ?></p>
    </div>
    <?php endif; ?>
</div>

<?php if (!empty($todos)): ?>
<div class="card">
    <h3 class="mb-2"><?= __("tasks.title") ?></h3>
    <table class="table">
        <thead>
            <tr><th><?= __("tasks.task") ?></th><th><?= __("common.status") ?></th></tr>
        </thead>
        <tbody>
            <?php foreach ($todos as $todo):
                $stateLabels = ['todo' => __('tasks.status.draft'), 'doing' => __('projects.status.in_progress'), 'done' => __('tasks.status.completed')];
                $stateBadges = ['todo' => 'badge-secondary', 'doing' => 'badge-warning', 'done' => 'badge-success'];
            ?>
            <tr>
                <td><?= htmlspecialchars($todo['title']) ?></td>
                <td><span class="badge <?= $stateBadges[$todo['state']] ?>"><?= $stateLabels[$todo['state']] ?></span></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>