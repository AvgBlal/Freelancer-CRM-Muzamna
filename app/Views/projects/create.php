<?php $title = __('projects.create'); ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= __("projects.create") ?></h2>
    </div>

    <form method="POST" action="/projects">
        <?= $csrf ?>

        <div class="form-group">
            <label class="form-label"><?= __('common.client') ?> *</label>
            <select name="client_id" class="form-select" required>
                <option value=""><?= __('tasks.select_client') ?></option>
                <?php foreach ($clients as $client): ?>
                    <option value="<?= $client['id'] ?>" <?= ($_SESSION['old']['client_id'] ?? '') == $client['id'] ? 'selected' : '' ?>><?= htmlspecialchars($client['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label"><?= __("projects.project_title") ?> *</label>
            <input type="text" name="title" class="form-input" required value="<?= htmlspecialchars($_SESSION['old']['title'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label class="form-label"><?= __("common.description") ?></label>
            <textarea name="description" class="form-textarea"><?= htmlspecialchars($_SESSION['old']['description'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label class="form-label"><?= __('common.status') ?></label>
            <select name="status" class="form-select">
                <option value="idea" <?= ($_SESSION['old']['status'] ?? 'idea') === 'idea' ? 'selected' : '' ?>><?= __('projects.status.idea') ?></option>
                <option value="in_progress" <?= ($_SESSION['old']['status'] ?? '') === 'in_progress' ? 'selected' : '' ?>><?= __('projects.status.in_progress') ?></option>
                <option value="paused" <?= ($_SESSION['old']['status'] ?? '') === 'paused' ? 'selected' : '' ?>><?= __('projects.status.paused') ?></option>
                <option value="completed" <?= ($_SESSION['old']['status'] ?? '') === 'completed' ? 'selected' : '' ?>><?= __('projects.status.completed') ?></option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label"><?= __("common.status") ?></label>
            <select name="priority" class="form-select">
                <option value="low" <?= ($_SESSION['old']['priority'] ?? '') === 'low' ? 'selected' : '' ?>><?= __('projects.priority.low') ?></option>
                <option value="normal" <?= ($_SESSION['old']['priority'] ?? 'normal') === 'normal' ? 'selected' : '' ?>><?= __('projects.priority.normal') ?></option>
                <option value="high" <?= ($_SESSION['old']['priority'] ?? '') === 'high' ? 'selected' : '' ?>><?= __('projects.priority.high') ?></option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label"><?= __('tasks.start_date') ?></label>
            <input type="date" name="start_date" class="form-input ltr-input" value="<?= htmlspecialchars($_SESSION['old']['start_date'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label class="form-label"><?= __('projects.delivery_date') ?></label>
            <input type="date" name="due_date" class="form-input ltr-input" value="<?= htmlspecialchars($_SESSION['old']['due_date'] ?? '') ?>">
        </div>

        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary"><?= __("common.save") ?></button>
            <a href="/projects" class="btn btn-secondary"><?= __("common.cancel") ?></a>
        </div>
    </form>
</div>

<?php unset($_SESSION['old']); ?>
<?php require __DIR__ . '/../layout/footer.php'; ?>