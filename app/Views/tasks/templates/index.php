<?php $title = __('templates.title'); ?>
<?php require __DIR__ . '/../../layout/header.php'; ?>

<?php
$priorityLabels = ['urgent' => __('tasks.priority.urgent'), 'high' => __('notes.priority.high'), 'normal' => __('notes.priority.normal'), 'low' => __('notes.priority.low')];
$priorityBadges = ['urgent' => 'badge-urgent', 'high' => 'badge-warning', 'normal' => 'badge-info', 'low' => 'badge-secondary'];
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= __("templates.title") ?></h2>
        <a href="/tasks/templates/create" class="btn btn-primary">+ <?= __("templates.new") ?></a>
    </div>

    <!-- Category Filter -->
    <?php if (!empty($categories)): ?>
        <div class="flex gap-2 mb-3">
            <a href="/tasks/templates" class="btn btn-secondary"><?= __("common.view_all") ?></a>
            <?php foreach ($categories as $cat): ?>
                <a href="/tasks/templates?category=<?= urlencode($cat) ?>" class="btn btn-secondary"><?= htmlspecialchars($cat) ?></a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (empty($templates)): ?>
        <p><?= __("safe.empty") ?></p>
    <?php else: ?>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th><?= __("templates.name") ?></th>
                        <th><?= __("expenses.category") ?></th>
                        <th><?= __("common.status") ?></th>
                        <th><?= __("tasks.estimated_hours") ?></th>
                        <th><?= __("common.actions") ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($templates as $template): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($template['name']) ?></strong>
                                <?php if ($template['description']): ?>
                                    <br><small class="text-muted"><?= htmlspecialchars(substr($template['description'], 0, 100)) ?>...</small>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($template['category'] ?? '-') ?></td>
                            <td>
                                <span class="badge <?= $priorityBadges[$template['priority']] ?>"><?= $priorityLabels[$template['priority']] ?></span>
                            </td>
                            <td><?= $template['default_hours'] ? $template['default_hours'] . ' h' : '-' ?></td>
                            <td class="flex gap-1">
                                <a href="/tasks/templates/<?= $template['id'] ?>/use" class="btn btn-sm btn-primary"><?= __("common.view") ?></a>
                                <a href="/tasks/templates/<?= $template['id'] ?>/edit" class="btn btn-sm btn-secondary"><?= __("common.edit") ?></a>
                                <form method="POST" action="/tasks/templates/<?= $template['id'] ?>/delete" style="display:inline;" onsubmit="return confirm('<?= __('common.confirm_delete') ?>')">
                                    <?= \App\Core\CSRF::field() ?>
                                    <button type="submit" class="btn btn-sm btn-danger"><?= __("common.delete") ?></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../../layout/footer.php'; ?>
