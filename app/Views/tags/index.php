<?php $title = __('tags.title'); ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><i class="fas fa-tags text-blue-500"></i> <?= __('tags.title') ?> (<?= count($tags) ?>)</h2>
    </div>

    <!-- Add new tag -->
    <form method="POST" action="/tags" class="mb-3">
        <?= $csrf ?>
        <div class="flex gap-2" style="flex-wrap: wrap; align-items: flex-end;">
            <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 200px; max-width: 400px;">
                <label class="form-label"><?= __("tags.new_name") ?></label>
                <input type="text" name="name" class="form-input" required placeholder="<?= __('tags.placeholder') ?>" maxlength="100">
            </div>
            <button type="submit" class="btn btn-primary" style="height: fit-content;"><i class="fas fa-plus"></i> <?= __("common.add") ?></button>
        </div>
    </form>

    <?php if (empty($tags)): ?>
        <p class="text-muted"><?= __('tags.empty') ?></p>
    <?php else: ?>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th><?= __("tags.tag") ?></th>
                        <th style="width: 120px;"><?= __("tags.client_count") ?></th>
                        <th style="width: 200px;"><?= __("common.actions") ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tags as $tag): ?>
                    <tr id="tag-row-<?= $tag['id'] ?>">
                        <td><?= $tag['id'] ?></td>
                        <td>
                            <!-- Display mode -->
                            <span id="tag-name-<?= $tag['id'] ?>">
                                <span class="badge badge-success" style="font-size: 0.9rem;"><?= htmlspecialchars($tag['name']) ?></span>
                            </span>
                            <!-- Edit mode (hidden) -->
                            <form method="POST" action="/tags/<?= $tag['id'] ?>" id="tag-edit-<?= $tag['id'] ?>" style="display:none;" class="flex gap-2" onsubmit="return true;">
                                <?= $csrf ?>
                                <input type="text" name="name" class="form-input" value="<?= htmlspecialchars($tag['name']) ?>" required maxlength="100" style="width: 200px;">
                                <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-check"></i></button>
                                <button type="button" class="btn btn-sm btn-secondary" onclick="toggleEdit(<?= $tag['id'] ?>, false)"><i class="fas fa-times"></i></button>
                            </form>
                        </td>
                        <td>
                            <?php if ($tag['client_count'] > 0): ?>
                                <span class="badge badge-info"><?= $tag['client_count'] ?> <?= __('entity.client') ?></span>
                            <?php else: ?>
                                <span class="text-muted">0</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="flex gap-1">
                                <button type="button" class="btn btn-sm btn-secondary" onclick="toggleEdit(<?= $tag['id'] ?>, true)" id="edit-btn-<?= $tag['id'] ?>"><i class="fas fa-edit"></i> <?= __('common.edit') ?></button>
                                <form method="POST" action="/tags/<?= $tag['id'] ?>/delete" style="display:inline;" onsubmit="return confirm('<?= $tag['client_count'] > 0 ? __('tags.linked_warning', ['count' => $tag['client_count']]) . ' ' : '' ?><?= __('tags.confirm_delete') ?>')">
                                    <?= $csrf ?>
                                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> <?= __('common.delete') ?></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<script>
function toggleEdit(id, show) {
    document.getElementById('tag-name-' + id).style.display = show ? 'none' : '';
    document.getElementById('tag-edit-' + id).style.display = show ? 'flex' : 'none';
    document.getElementById('edit-btn-' + id).style.display = show ? 'none' : '';
    if (show) {
        document.querySelector('#tag-edit-' + id + ' input[name="name"]').focus();
    }
}
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
