<?php
$title = __('safe.edit') . ': ' . htmlspecialchars($item['title']);
$routePrefix = $routePrefix ?? '/safe';
$type = $type ?? 'general';
$clientRequired = $clientRequired ?? false;
?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= __('common.edit') ?>: <?= htmlspecialchars($item['title']) ?></h2>
        <a href="<?= $routePrefix ?>/<?= $item['id'] ?>" class="btn btn-secondary"><?= __("common.back") ?></a>
    </div>

    <form method="POST" action="<?= $routePrefix ?>/<?= $item['id'] ?>" enctype="multipart/form-data">
        <?= $csrf ?>

        <div class="form-group">
            <label class="form-label" for="client_id"><?= __('common.client') ?> <?= $clientRequired ? '*' : '' ?></label>
            <select id="client_id" name="client_id" class="form-select" <?= $clientRequired ? 'required' : '' ?>>
                <option value="">-- <?= __('common.all_clients') ?> --</option>
                <?php foreach ($clients as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= ($_SESSION['old']['client_id'] ?? $item['client_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label" for="title"><?= __('common.title') ?> *</label>
            <input type="text" id="title" name="title" class="form-input" value="<?= htmlspecialchars($_SESSION['old']['title'] ?? $item['title']) ?>" required maxlength="255">
        </div>

        <div class="form-group">
            <label class="form-label" for="url"><?= __("safe.url") ?></label>
            <input type="url" id="url" name="url" class="form-input ltr-input" value="<?= htmlspecialchars($_SESSION['old']['url'] ?? $item['url'] ?? '') ?>" placeholder="https://example.com" dir="ltr">
        </div>

        <!-- Existing files -->
        <?php $files = $item['files'] ?? []; ?>
        <?php if (!empty($files)): ?>
        <div class="form-group">
            <label class="form-label"><?= __("safe.files") ?></label>
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <?php foreach ($files as $f): ?>
                <div style="display: flex; align-items: center; gap: 10px; padding: 8px; background: #f9fafb; border-radius: 6px; border: 1px solid #e5e7eb;">
                    <a href="/safe/files/<?= $f['id'] ?>/download" class="btn btn-sm btn-secondary">
                        <i class="fas fa-download"></i> <?= htmlspecialchars($f['file_original_name']) ?>
                    </a>
                    <small class="text-gray-500">(<?= $f['file_size'] > 1048576 ? number_format($f['file_size'] / 1048576, 1) . ' MB' : number_format($f['file_size'] / 1024, 0) . ' KB' ?>)</small>
                    <label class="checkbox-label" style="margin-inline-start: auto;">
                        <input type="checkbox" name="remove_files[]" value="<?= $f['id'] ?>"> <?= __("common.delete") ?>
                    </label>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Legacy single file -->
        <?php if (!empty($item['file_path']) && empty($files)): ?>
        <div class="form-group">
            <label class="form-label"><?= __("safe.files") ?></label>
            <div class="mb-2">
                <a href="<?= $routePrefix ?>/<?= $item['id'] ?>/download" class="btn btn-sm btn-secondary">
                    <i class="fas fa-download"></i> <?= htmlspecialchars($item['file_original_name']) ?>
                    <small>(<?= $item['file_size'] > 1048576 ? number_format($item['file_size'] / 1048576, 1) . ' MB' : number_format($item['file_size'] / 1024, 0) . ' KB' ?>)</small>
                </a>
                <label class="checkbox-label" style="display: inline-block; margin-inline-start: 10px;">
                    <input type="checkbox" name="remove_file" value="1"> <?= __("common.delete") ?>
                </label>
            </div>
        </div>
        <?php endif; ?>

        <div class="form-group">
            <label class="form-label" for="files"><?= __("common.add") ?></label>
            <input type="file" id="files" name="files[]" class="form-input" multiple accept=".zip,.gz,.tar,.rar,.7z,.pdf,.doc,.docx,.xls,.xlsx,.txt,.md,.json,.xml,.csv,.jpg,.jpeg,.png,.gif,.svg,.sql,.html,.css,.js">
            <small class="form-hint"></small>
        </div>

        <div class="form-group">
            <label class="form-label" for="tags"><?= __("clients.tags") ?></label>
            <input type="text" id="tags" name="tags" class="form-input" value="<?= htmlspecialchars($_SESSION['old']['tags'] ?? $item['tags'] ?? '') ?>">
            <small class="form-hint"></small>
        </div>

        <div class="form-group">
            <label class="form-label" for="notes"><?= __("common.notes") ?></label>
            <textarea id="notes" name="notes" class="form-textarea" rows="5"><?= htmlspecialchars($_SESSION['old']['notes'] ?? $item['notes'] ?? '') ?></textarea>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary"><?= __("common.save") ?></button>
            <a href="<?= $routePrefix ?>/<?= $item['id'] ?>" class="btn btn-secondary"><?= __("common.cancel") ?></a>
        </div>
    </form>
</div>

<?php unset($_SESSION['old']); ?>
<?php require __DIR__ . '/../layout/footer.php'; ?>
