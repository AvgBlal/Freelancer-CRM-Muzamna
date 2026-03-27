<?php $title = isset($template) ? __('tasks.create_from_tpl') : __('tasks.create'); ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= $title ?></h2>
    </div>

    <?php if (isset($template)): ?>
        <div class="alert alert-info mb-3">
            <strong><?= __("common.title") ?>:</strong> <?= htmlspecialchars($template['name']) ?><br>
            <?php if ($template['description']): ?>
                <small><?= htmlspecialchars($template['description']) ?></small>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= isset($template) ? '/tasks/templates/' . $template['id'] . '/create-task' : '/tasks' ?>">
        <?= $csrf ?>

        <div class="form-group">
            <label class="form-label"><?= __('common.title') ?> *</label>
            <input type="text" name="title" class="form-input" required
                   value="<?= htmlspecialchars($_SESSION['old']['title'] ?? $template['name'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label class="form-label"><?= __("common.description") ?></label>
            <textarea name="description" class="form-textarea" rows="4"><?= htmlspecialchars($_SESSION['old']['description'] ?? $template['description'] ?? '') ?></textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label"><?= __("common.status") ?></label>
                <select name="priority" class="form-select">
                    <?php
                    $priorities = ['low' => __('notes.priority.low'), 'normal' => __('notes.priority.normal'), 'high' => __('notes.priority.high'), 'urgent' => __('tasks.priority.urgent')];
                    $selectedPriority = $_SESSION['old']['priority'] ?? $template['priority'] ?? 'normal';
                    foreach ($priorities as $key => $label):
                    ?>
                        <option value="<?= $key ?>" <?= $selectedPriority === $key ? 'selected' : '' ?>><?= $label ?></option>
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
                        <option value="<?= $emp['id'] ?>" <?= ($_SESSION['old']['assigned_to'] ?? '') == $emp['id'] ? 'selected' : '' ?>>
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
                        <option value="<?= $client['id'] ?>" <?= ($_SESSION['old']['client_id'] ?? '') == $client['id'] ? 'selected' : '' ?>>
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
                        <option value="<?= $project['id'] ?>" <?= ($_SESSION['old']['project_id'] ?? '') == $project['id'] ? 'selected' : '' ?>>
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
                        <option value="<?= $service['id'] ?>" <?= ($_SESSION['old']['service_id'] ?? '') == $service['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($service['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-row" style="flex-wrap: wrap;">
            <div class="form-group">
                <label class="form-label"><?= __("tasks.start_date") ?></label>
                <input type="date" name="start_date" class="form-input ltr-input"
                       value="<?= htmlspecialchars($_SESSION['old']['start_date'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label class="form-label"><?= __("tasks.due") ?> *</label>
                <input type="date" name="due_date" class="form-input ltr-input" required
                       value="<?= htmlspecialchars($_SESSION['old']['due_date'] ?? date('Y-m-d', strtotime('+7 days'))) ?>">
            </div>

            <div class="form-group">
                <label class="form-label"><?= __("tasks.estimated_hours") ?></label>
                <input type="number" name="estimated_hours" step="0.5" min="0" class="form-input ltr-input"
                       value="<?= htmlspecialchars($_SESSION['old']['estimated_hours'] ?? $template['default_hours'] ?? '') ?>">
            </div>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary">
                <?= isset($template) ? __('tasks.create_from_tpl_btn') : __('tasks.create_btn') ?>
            </button>
            <a href="/tasks" class="btn btn-secondary"><?= __("common.cancel") ?></a>
        </div>
    </form>
</div>

<?php unset($_SESSION['old']); ?>
<?php require __DIR__ . '/../layout/footer.php'; ?>
