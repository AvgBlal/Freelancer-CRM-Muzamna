<?php $title = __('templates.create'); ?>
<?php require __DIR__ . '/../../layout/header.php'; ?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= __("templates.create") ?></h2>
    </div>

    <form method="POST" action="/tasks/templates">
        <?= $csrf ?>

        <div class="form-group">
            <label class="form-label"><?= __("templates.name") ?> *</label>
            <input type="text" name="name" class="form-input" required>
        </div>

        <div class="form-group">
            <label class="form-label"><?= __("common.description") ?></label>
            <textarea name="description" class="form-textarea" rows="4"></textarea>
        </div>

        <div class="form-row flex-wrap">
            <div class="form-group">
                <label class="form-label"><?= __("expenses.category") ?></label>
                <input type="text" name="category" class="form-input" >
            </div>

            <div class="form-group">
                <label class="form-label"><?= __("common.status") ?></label>
                <select name="priority" class="form-select">
                    <option value="low"><?= __("notes.priority.low") ?></option>
                    <option value="normal" selected><?= __("notes.priority.normal") ?></option>
                    <option value="high"><?= __("notes.priority.high") ?></option>
                    <option value="urgent"><?= __("tasks.priority.urgent") ?></option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label"><?= __("tasks.estimated_hours") ?></label>
                <input type="number" name="default_hours" step="0.5" min="0" class="form-input ltr-input">
            </div>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary"><?= __("common.save") ?></button>
            <a href="/tasks/templates" class="btn btn-secondary"><?= __("common.cancel") ?></a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../../layout/footer.php'; ?>
