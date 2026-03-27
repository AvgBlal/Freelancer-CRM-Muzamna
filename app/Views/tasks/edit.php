<?php $title = __('tasks.edit'); ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= __("tasks.edit") ?>: <?= htmlspecialchars($task['title']) ?></h2>
    </div>

    <form method="POST" action="/tasks/<?= $task['id'] ?>">
        <?= $csrf ?>

        <div class="form-group">
            <label class="form-label"><?= __('common.title') ?> *</label>
            <input type="text" name="title" class="form-input" required value="<?= htmlspecialchars($task['title']) ?>">
        </div>

        <div class="form-group">
            <label class="form-label"><?= __("common.description") ?></label>
            <textarea name="description" class="form-textarea" rows="4"><?= htmlspecialchars($task['description'] ?? '') ?></textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label"><?= __("common.status") ?></label>
                <select name="priority" class="form-select">
                    <?php
                    $priorities = ['low' => __('notes.priority.low'), 'normal' => __('notes.priority.normal'), 'high' => __('notes.priority.high'), 'urgent' => __('tasks.priority.urgent')];
                    foreach ($priorities as $key => $label):
                    ?>
                        <option value="<?= $key ?>" <?= $task['priority'] === $key ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label"><?= __("tasks.assign_to") ?></label>
                <select name="assigned_to" class="form-select">
                    <option value="">-- <?= __('tasks.select_employee') ?> --</option>
                    <?php foreach ($employees as $emp):
                        $workload = round(($emp['active_tasks'] / $emp['max_tasks_capacity']) * 100);
                        $workloadColor = $workload >= 100 ? '🔴' : ($workload >= 80 ? '🟡' : '🟢');
                    ?>
                        <option value="<?= $emp['id'] ?>" <?= ($task['assigned_to'] ?? '') == $emp['id'] ? 'selected' : '' ?>>
                            <?= $workloadColor ?> <?= htmlspecialchars($emp['name']) ?> (<?= $emp['active_tasks'] ?>/<?= $emp['max_tasks_capacity'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-row" style="flex-wrap: wrap;">
            <div class="form-group">
                <label class="form-label"><?= __("common.client") ?></label>
                <select name="client_id" class="form-select">
                    <option value="">-- <?= __('tasks.select_client') ?> --</option>
                    <?php foreach ($clients as $client): ?>
                        <option value="<?= $client['id'] ?>" <?= ($task['client_id'] ?? '') == $client['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($client['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label"><?= __("common.project") ?></label>
                <select name="project_id" class="form-select">
                    <option value="">-- <?= __('tasks.select_project') ?> --</option>
                    <?php foreach ($projects as $project): ?>
                        <option value="<?= $project['id'] ?>" <?= ($task['project_id'] ?? '') == $project['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($project['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label"><?= __("tasks.service") ?></label>
                <select name="service_id" class="form-select">
                    <option value="">-- <?= __('tasks.select_service') ?> --</option>
                    <?php foreach ($services as $service): ?>
                        <option value="<?= $service['id'] ?>" <?= ($task['service_id'] ?? '') == $service['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($service['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-row" style="flex-wrap: wrap;">
            <div class="form-group">
                <label class="form-label"><?= __("tasks.start_date") ?></label>
                <input type="date" name="start_date" class="form-input ltr-input" value="<?= htmlspecialchars($task['start_date'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label class="form-label"><?= __("tasks.due") ?> *</label>
                <input type="date" name="due_date" class="form-input ltr-input" required value="<?= htmlspecialchars($task['due_date']) ?>">
            </div>

            <div class="form-group">
                <label class="form-label"><?= __("tasks.estimated_hours") ?></label>
                <input type="number" name="estimated_hours" step="0.5" min="0" class="form-input ltr-input" value="<?= htmlspecialchars($task['estimated_hours'] ?? '') ?>">
            </div>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary"><?= __("common.save") ?></button>
            <a href="/tasks/<?= $task['id'] ?>" class="btn btn-secondary"><?= __("common.cancel") ?></a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
