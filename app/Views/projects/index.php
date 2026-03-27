<?php $title = __('projects.title'); ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= __('projects.title') ?></h2>
        <a href="/projects/create" class="btn btn-primary">+ <?= __("projects.new") ?></a>
    </div>

    <!-- Filters -->
    <form method="GET" class="mb-3">
        <div class="flex gap-2" style="flex-wrap: wrap;">
            <select name="status" class="form-select" style="width: 150px;">
                <option value=""><?= __("common.all_statuses") ?></option>
                <option value="idea" <?= ($_GET['status'] ?? '') === 'idea' ? 'selected' : '' ?>><?= __("notes.cat.idea") ?></option>
                <option value="in_progress" <?= ($_GET['status'] ?? '') === 'in_progress' ? 'selected' : '' ?>><?= __("projects.status.in_progress") ?></option>
                <option value="paused" <?= ($_GET['status'] ?? '') === 'paused' ? 'selected' : '' ?>><?= __("projects.status.paused") ?></option>
                <option value="completed" <?= ($_GET['status'] ?? '') === 'completed' ? 'selected' : '' ?>><?= __("projects.status.completed") ?></option>
            </select>

            <select name="priority" class="form-select" style="width: 150px;">
                <option value=""><?= __("common.all_priorities") ?></option>
                <option value="high" <?= ($_GET['priority'] ?? '') === 'high' ? 'selected' : '' ?>><?= __('projects.priority.high') ?></option>
                <option value="normal" <?= ($_GET['priority'] ?? '') === 'normal' ? 'selected' : '' ?>><?= __('projects.priority.normal') ?></option>
                <option value="low" <?= ($_GET['priority'] ?? '') === 'low' ? 'selected' : '' ?>><?= __('projects.priority.low') ?></option>
            </select>

            <button type="submit" class="btn btn-secondary"><?= __("common.filter") ?></button>
        </div>
    </form>

    <?php if (empty($projects)): ?>
        <p><?= __("projects.empty") ?></p>
    <?php else: ?>
        <div class="table-container">
            <table class="table bulk-table">
                <thead>
                    <tr>
                        <th style="width: 30px;"><input type="checkbox" class="bulk-select-all"></th>
                        <th><?= __("projects.project") ?></th>
                        <th><?= __("common.client") ?></th>
                        <th><?= __("common.status") ?></th>
                        <th><?= __("common.status") ?></th>
                        <th><?= __("common.progress") ?></th>
                        <th><?= __("projects.delivery_date") ?></th>
                        <th><?= __("common.actions") ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projects as $project): ?>
                        <tr>
                            <td><input type="checkbox" class="bulk-check" value="<?= $project['id'] ?>"></td>
                            <td><a href="/projects/<?= $project['id'] ?>"><?= htmlspecialchars($project['title']) ?></a></td>
                            <td><?= htmlspecialchars($project['client_name']) ?></td>
                            <td>
                                <?php
                                $statusLabels = [
                                    'idea' => __('notes.cat.idea'),
                                    'in_progress' => __('projects.status.in_progress'),
                                    'paused' => __('services.status.paused'),
                                    'completed' => __('projects.status.completed'),
                                    'cancelled' => __('services.status.cancelled'),
                                ];
                                echo $statusLabels[$project['status']] ?? htmlspecialchars($project['status']);
                                ?>
                            </td>
                            <td>
                                <?php
                                $priorityColors = [
                                    'high' => 'badge-urgent',
                                    'normal' => 'badge-info',
                                    'low' => 'badge-success',
                                ];
                                $priorityLabels = [
                                    'high' => __('projects.priority.high'),
                                    'normal' => __('projects.priority.normal'),
                                    'low' => __('projects.priority.low'),
                                ];
                                ?>
                                <span class="badge <?= $priorityColors[$project['priority']] ?? '' ?>"><?= $priorityLabels[$project['priority']] ?? $project['priority'] ?></span>
                            </td>
                            <td>
                                <div style="background: #e5e7eb; border-radius: 4px; height: 20px; width: 100px;">
                                    <div style="background: <?= $project['progress'] == 100 ? '#16a34a' : '#2563eb' ?>; width: <?= $project['progress'] ?>%; height: 100%; border-radius: 4px;"></div>
                                </div>
                                <?= $project['progress'] ?>%
                            </td>
                            <td><?= $project['due_date'] ?? '-' ?></td>
                            <td class="flex gap-1">
                                <a href="/projects/<?= $project['id'] ?>" class="btn btn-secondary" style="padding: 0.25rem 0.5rem;"><?= __("common.view") ?></a>
                                <a href="/projects/<?= $project['id'] ?>/edit" class="btn btn-secondary" style="padding: 0.25rem 0.5rem;"><?= __("common.edit") ?></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php $bulkAction = '/bulk/projects'; require __DIR__ . '/../partials/bulk_actions.php'; ?>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>