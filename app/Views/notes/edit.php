<?php $title = __('notes.edit'); ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<?php
$categoryLabels = ['general' => __('notes.cat.general'), 'idea' => __('notes.cat.idea'), 'reminder' => __('notes.cat.reminder'), 'financial' => __('notes.cat.financial'), 'personal' => __('notes.cat.personal')];
$priorityLabels = ['low' => __('notes.priority.low'), 'normal' => __('notes.priority.normal'), 'high' => __('notes.priority.high')];
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= __('common.edit') ?>: <?= htmlspecialchars($note['title']) ?></h2>
        <a href="/notes/<?= $note['id'] ?>" class="btn btn-secondary"><?= __("common.back") ?></a>
    </div>

    <form method="POST" action="/notes/<?= $note['id'] ?>">
        <?= \App\Core\CSRF::field() ?>

        <div class="form-group">
            <label for="title"><?= __('common.title') ?> *</label>
            <input type="text" id="title" name="title" class="form-control" required value="<?= htmlspecialchars($note['title']) ?>">
        </div>

        <div class="form-group">
            <label for="content"><?= __("common.description") ?></label>
            <textarea id="content" name="content" class="form-control" rows="6"><?= htmlspecialchars($note['content'] ?? '') ?></textarea>
        </div>

        <div class="flex gap-2" style="flex-wrap: wrap;">
            <div class="form-group" style="flex: 1; min-width: 150px;">
                <label for="category"><?= __("expenses.category") ?></label>
                <select id="category" name="category" class="form-control">
                    <?php foreach ($categoryLabels as $key => $label): ?>
                        <option value="<?= $key ?>" <?= $note['category'] === $key ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" style="flex: 1; min-width: 150px;">
                <label for="priority"><?= __("common.status") ?></label>
                <select id="priority" name="priority" class="form-control">
                    <?php foreach ($priorityLabels as $key => $label): ?>
                        <option value="<?= $key ?>" <?= $note['priority'] === $key ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" style="flex: 1; min-width: 150px;">
                <label for="due_date"><?= __("common.due_date") ?></label>
                <input type="date" id="due_date" name="due_date" class="form-control ltr-input" value="<?= htmlspecialchars($note['due_date'] ?? '') ?>">
            </div>
        </div>

        <div class="form-group">
            <label>
                <input type="checkbox" name="is_pinned" value="1" <?= $note['is_pinned'] ? 'checked' : '' ?>> <?= __("notes.pin") ?>
            </label>
        </div>

        <div class="form-group">
            <label for="color"><?= __("common.status") ?></label>
            <input type="color" id="color" name="color" value="<?= htmlspecialchars($note['color'] ?? '#ffffff') ?>" style="width: 60px; height: 36px; padding: 2px;">
        </div>

        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary"><?= __("common.save") ?></button>
            <form method="POST" action="/notes/<?= $note['id'] ?>/delete" style="display:inline;" onsubmit="return confirm('<?= __('common.confirm_delete') ?>');">
                <?= \App\Core\CSRF::field() ?>
                <button type="submit" class="btn btn-danger"><?= __("common.delete") ?></button>
            </form>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
