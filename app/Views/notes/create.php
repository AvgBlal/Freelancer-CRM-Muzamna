<?php $title = __('notes.create'); ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= __("notes.create") ?></h2>
        <a href="/notes" class="btn btn-secondary"><?= __("common.back") ?></a>
    </div>

    <form method="POST" action="/notes">
        <?= \App\Core\CSRF::field() ?>

        <div class="form-group">
            <label for="title"><?= __('common.title') ?> *</label>
            <input type="text" id="title" name="title" class="form-control" required value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="content"><?= __("common.description") ?></label>
            <textarea id="content" name="content" class="form-control" rows="6"><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
        </div>

        <div class="flex gap-2" style="flex-wrap: wrap;">
            <div class="form-group" style="flex: 1; min-width: 150px;">
                <label for="category"><?= __("expenses.category") ?></label>
                <select id="category" name="category" class="form-control">
                    <option value="general"><?= __("notes.cat.general") ?></option>
                    <option value="idea"><?= __("notes.cat.idea") ?></option>
                    <option value="reminder"><?= __("notes.cat.reminder") ?></option>
                    <option value="financial"><?= __("notes.cat.financial") ?></option>
                    <option value="personal"><?= __("notes.cat.personal") ?></option>
                </select>
            </div>

            <div class="form-group" style="flex: 1; min-width: 150px;">
                <label for="priority"><?= __("common.status") ?></label>
                <select id="priority" name="priority" class="form-control">
                    <option value="normal"><?= __("notes.priority.normal") ?></option>
                    <option value="high"><?= __("notes.priority.high") ?></option>
                    <option value="low"><?= __("notes.priority.low") ?></option>
                </select>
            </div>

            <div class="form-group" style="flex: 1; min-width: 150px;">
                <label for="due_date"><?= __("common.due_date") ?></label>
                <input type="date" id="due_date" name="due_date" class="form-control ltr-input" value="<?= htmlspecialchars($_POST['due_date'] ?? '') ?>">
            </div>
        </div>

        <div class="form-group">
            <label>
                <input type="checkbox" name="is_pinned" value="1"> <?= __("notes.pin") ?>
            </label>
        </div>

        <div class="form-group">
            <label for="color"><?= __("common.status") ?></label>
            <input type="color" id="color" name="color" value="<?= htmlspecialchars($_POST['color'] ?? '#ffffff') ?>" style="width: 60px; height: 36px; padding: 2px;">
        </div>

        <button type="submit" class="btn btn-primary"><?= __("common.save") ?></button>
    </form>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
