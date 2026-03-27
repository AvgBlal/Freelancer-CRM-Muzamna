<?php $title = __('service_types.title'); ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><i class="fas fa-layer-group text-blue-500"></i> <?= __('service_types.title') ?> (<?= count($types) ?>)</h2>
    </div>

    <!-- Add new type -->
    <form method="POST" action="/service-types" class="mb-3">
        <?= $csrf ?>
        <div class="flex gap-2" style="flex-wrap: wrap; align-items: flex-end;">
            <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 120px; max-width: 200px;">
                <label class="form-label"><?= __("service_types.code") ?></label>
                <input type="text" name="slug" class="form-input" required placeholder="<?= __('service_types.code_placeholder') ?>" maxlength="50" pattern="[a-z0-9_-]+" title="<?= __('service_types.code_hint') ?>" dir="ltr">
            </div>
            <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 150px; max-width: 300px;">
                <label class="form-label"><?= __("service_types.name_ar") ?></label>
                <input type="text" name="label" class="form-input" required placeholder="<?= __('service_types.name_placeholder') ?>" maxlength="100">
            </div>
            <button type="submit" class="btn btn-primary" style="height: fit-content;"><i class="fas fa-plus"></i> <?= __("common.add") ?></button>
        </div>
    </form>

    <?php if (empty($types)): ?>
        <p class="text-muted"><?= __('safe.empty') ?></p>
    <?php else: ?>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th><?= __("service_types.code") ?></th>
                        <th><?= __("common.name") ?></th>
                        <th style="width: 120px;"><?= __("finance.service_count") ?></th>
                        <th style="width: 250px;"><?= __("common.actions") ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($types as $type): ?>
                    <tr id="type-row-<?= $type['id'] ?>">
                        <td><?= $type['id'] ?></td>
                        <td>
                            <span id="type-slug-<?= $type['id'] ?>"><code dir="ltr"><?= htmlspecialchars($type['slug']) ?></code></span>
                        </td>
                        <td>
                            <!-- Display mode -->
                            <span id="type-label-<?= $type['id'] ?>">
                                <span class="badge badge-info" style="font-size: 0.9rem;"><?= htmlspecialchars($type['label']) ?></span>
                            </span>
                            <!-- Edit mode (hidden) -->
                            <form method="POST" action="/service-types/<?= $type['id'] ?>" id="type-edit-<?= $type['id'] ?>" style="display:none;" class="flex gap-2" onsubmit="return true;">
                                <?= $csrf ?>
                                <input type="text" name="slug" class="form-input" value="<?= htmlspecialchars($type['slug']) ?>" required maxlength="50" pattern="[a-z0-9_-]+" dir="ltr" style="width: 120px;">
                                <input type="text" name="label" class="form-input" value="<?= htmlspecialchars($type['label']) ?>" required maxlength="100" style="width: 150px;">
                                <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-check"></i></button>
                                <button type="button" class="btn btn-sm btn-secondary" onclick="toggleEdit(<?= $type['id'] ?>, false)"><i class="fas fa-times"></i></button>
                            </form>
                        </td>
                        <td>
                            <?php if ($type['service_count'] > 0): ?>
                                <a href="/services?type=<?= urlencode($type['slug']) ?>" class="badge badge-info"><?= $type['service_count'] ?> <?= __('entity.service') ?></a>
                            <?php else: ?>
                                <span class="text-muted">0</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="flex gap-1">
                                <button type="button" class="btn btn-sm btn-secondary" onclick="toggleEdit(<?= $type['id'] ?>, true)" id="edit-btn-<?= $type['id'] ?>"><i class="fas fa-edit"></i> <?= __('common.edit') ?></button>
                                <form method="POST" action="/service-types/<?= $type['id'] ?>/delete" style="display:inline;" onsubmit="return confirm('<?= $type['service_count'] > 0 ? __('service_types.linked_warning', ['count' => $type['service_count']]) . ' ' : '' ?><?= __('common.confirm_delete') ?>')">
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
    document.getElementById('type-slug-' + id).style.display = show ? 'none' : '';
    document.getElementById('type-label-' + id).style.display = show ? 'none' : '';
    document.getElementById('type-edit-' + id).style.display = show ? 'flex' : 'none';
    document.getElementById('edit-btn-' + id).style.display = show ? 'none' : '';
    if (show) {
        document.querySelector('#type-edit-' + id + ' input[name="label"]').focus();
    }
}
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
