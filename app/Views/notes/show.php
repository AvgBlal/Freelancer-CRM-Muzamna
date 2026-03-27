<?php $title = $note['title']; ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<?php
$categoryLabels = ['general' => __('notes.cat.general'), 'idea' => __('notes.cat.idea'), 'reminder' => __('notes.cat.reminder'), 'financial' => __('notes.cat.financial'), 'personal' => __('notes.cat.personal')];
$priorityLabels = ['low' => __('notes.priority.low'), 'normal' => __('notes.priority.normal'), 'high' => __('notes.priority.high')];
$priorityBadges = ['low' => 'badge-secondary', 'normal' => 'badge-info', 'high' => 'badge-urgent'];
$categoryBadges = ['general' => 'badge-secondary', 'idea' => 'badge-info', 'reminder' => 'badge-warning', 'financial' => 'badge-success', 'personal' => 'badge-info'];
$isOverdue = $note['due_date'] && $note['due_date'] < date('Y-m-d') && $note['status'] === 'active';
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">
            <?php if ($note['is_pinned']): ?><span title="<?= __('notes.pinned') ?>">&#128204;</span> <?php endif; ?>
            <?= htmlspecialchars($note['title']) ?>
        </h2>
        <div class="flex gap-1">
            <a href="/notes/<?= $note['id'] ?>/edit" class="btn btn-secondary"><?= __("common.edit") ?></a>
            <a href="/notes" class="btn btn-secondary"><?= __("common.back") ?></a>
        </div>
    </div>

    <div class="flex gap-2 mb-3" style="flex-wrap: wrap;">
        <span class="badge <?= $categoryBadges[$note['category']] ?? 'badge-secondary' ?>"><?= $categoryLabels[$note['category']] ?? $note['category'] ?></span>
        <span class="badge <?= $priorityBadges[$note['priority']] ?? 'badge-info' ?>"><?= __("common.status") ?>: <?= $priorityLabels[$note['priority']] ?? $note['priority'] ?></span>
        <?php if ($note['status'] === 'archived'): ?>
            <span class="badge badge-secondary"><?= __("notes.archived_status") ?></span>
        <?php endif; ?>
        <?php if ($isOverdue): ?>
            <span class="badge badge-urgent"><?= __("common.overdue") ?></span>
        <?php endif; ?>
    </div>

    <?php if ($note['due_date']): ?>
        <div class="mb-2">
            <strong><?= __("common.due_date") ?>:</strong>
            <span class="<?= $isOverdue ? 'text-danger' : '' ?>"><?= $note['due_date'] ?></span>
        </div>
    <?php endif; ?>

    <?php if ($note['content']): ?>
        <div class="mb-3" style="white-space: pre-wrap; line-height: 1.8; padding: 1rem; background: #f8f9fa; border-radius: 4px;">
<?= htmlspecialchars($note['content']) ?>
        </div>
    <?php else: ?>
        <p class="text-muted mb-3"><?= __("notes.empty") ?></p>
    <?php endif; ?>

    <?php if ($note['color'] && $note['color'] !== '#ffffff'): ?>
        <div class="mb-2">
            <strong><?= __("common.status") ?>:</strong>
            <span style="display: inline-block; width: 20px; height: 20px; background: <?= htmlspecialchars($note['color']) ?>; border: 1px solid #ccc; border-radius: 3px; vertical-align: middle;"></span>
        </div>
    <?php endif; ?>

    <div class="text-muted" style="font-size: 0.85rem;">
        <?= __('common.created_at') ?>: <?= $note['created_at'] ?>
        <?php if ($note['updated_at'] !== $note['created_at']): ?>
            | <?= $note['updated_at'] ?>
        <?php endif; ?>
    </div>

    <hr>

    <div class="flex gap-1">
        <form method="POST" action="/notes/<?= $note['id'] ?>/toggle-pin" style="display:inline;">
            <?= \App\Core\CSRF::field() ?>
            <button type="submit" class="btn btn-secondary"><?= $note['is_pinned'] ? __('notes.unpin') : __('notes.pin') ?></button>
        </form>

        <?php if ($note['status'] === 'active'): ?>
            <form method="POST" action="/notes/<?= $note['id'] ?>/archive" style="display:inline;">
                <?= \App\Core\CSRF::field() ?>
                <button type="submit" class="btn btn-secondary"><?= __("notes.archive") ?></button>
            </form>
        <?php else: ?>
            <form method="POST" action="/notes/<?= $note['id'] ?>/restore" style="display:inline;">
                <?= \App\Core\CSRF::field() ?>
                <button type="submit" class="btn btn-secondary"><?= __("notes.restore") ?></button>
            </form>
        <?php endif; ?>

        <form method="POST" action="/notes/<?= $note['id'] ?>/delete" style="display:inline;" onsubmit="return confirm('<?= __('common.confirm_delete') ?>');">
            <?= \App\Core\CSRF::field() ?>
            <button type="submit" class="btn btn-danger"><?= __("common.delete") ?></button>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
