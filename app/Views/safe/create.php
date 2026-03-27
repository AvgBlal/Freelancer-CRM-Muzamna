<?php
$title = ($typeLabel ?? __('safe.title')) . ' - ' . __('safe.create');
$routePrefix = $routePrefix ?? '/safe';
$type = $type ?? 'general';
$clientRequired = $clientRequired ?? false;
?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= __('safe.create') ?></h2>
        <a href="<?= $routePrefix ?>" class="btn btn-secondary"><?= __("common.back") ?></a>
    </div>

    <form method="POST" action="<?= $routePrefix ?>" enctype="multipart/form-data">
        <?= $csrf ?>

        <div class="form-group">
            <label class="form-label" for="client_id"><?= __('common.client') ?> <?= $clientRequired ? '*' : '' ?></label>
            <select id="client_id" name="client_id" class="form-select" <?= $clientRequired ? 'required' : '' ?>>
                <option value="">-- <?= __('common.all_clients') ?> --</option>
                <?php foreach ($clients as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= ($_SESSION['old']['client_id'] ?? ($_GET['client_id'] ?? '')) == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label" for="title"><?= __('common.title') ?> *</label>
            <input type="text" id="title" name="title" class="form-input" value="<?= htmlspecialchars($_SESSION['old']['title'] ?? '') ?>" required maxlength="255">
        </div>

        <div class="form-group">
            <label class="form-label" for="url"><?= __("safe.url") ?></label>
            <input type="url" id="url" name="url" class="form-input ltr-input" value="<?= htmlspecialchars($_SESSION['old']['url'] ?? '') ?>" placeholder="https://example.com" dir="ltr">
        </div>

        <div class="form-group">
            <label class="form-label" for="files"><?= __("safe.files") ?></label>
            <input type="file" id="files" name="files[]" class="form-input" multiple accept=".zip,.gz,.tar,.rar,.7z,.pdf,.doc,.docx,.xls,.xlsx,.txt,.md,.json,.xml,.csv,.jpg,.jpeg,.png,.gif,.svg,.sql,.html,.css,.js">
            <small class="form-hint"></small>
        </div>

        <div class="form-group">
            <label class="form-label" for="tags"><?= __("clients.tags") ?></label>
            <input type="text" id="tags" name="tags" class="form-input" value="<?= htmlspecialchars($_SESSION['old']['tags'] ?? '') ?>">
            <small class="form-hint"></small>
        </div>

        <div class="form-group">
            <label class="form-label" for="notes"><?= __("common.notes") ?></label>
            <textarea id="notes" name="notes" class="form-textarea" rows="5"><?= htmlspecialchars($_SESSION['old']['notes'] ?? '') ?></textarea>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary"><?= __("common.save") ?></button>
            <a href="<?= $routePrefix ?>" class="btn btn-secondary"><?= __("common.cancel") ?></a>
        </div>
    </form>
</div>

<?php unset($_SESSION['old']); ?>
<?php require __DIR__ . '/../layout/footer.php'; ?>
