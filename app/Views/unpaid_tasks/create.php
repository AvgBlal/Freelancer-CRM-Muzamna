<?php $title = __('unpaid.create'); ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= __('unpaid.create') ?></h2>
        <a href="/unpaid-tasks" class="btn btn-secondary"><?= __("common.back") ?></a>
    </div>

    <form method="POST" action="/unpaid-tasks" enctype="multipart/form-data">
        <?= $csrf ?>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="client_id"><?= __('common.client') ?> *</label>
                <select id="client_id" name="client_id" class="form-select" required>
                    <option value=""><?= __('common.all_clients') ?></option>
                    <?php foreach ($clients as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= ($_SESSION['old']['client_id'] ?? $selectedClientId) == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="title"><?= __('unpaid.task_title') ?> *</label>
                <input type="text" id="title" name="title" class="form-input" value="<?= htmlspecialchars($_SESSION['old']['title'] ?? '') ?>" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="hours"><?= __('unpaid.hours') ?> *</label>
                <input type="number" id="hours" name="hours" class="form-input ltr-input" value="<?= $_SESSION['old']['hours'] ?? '' ?>" step="0.25" min="0.25" max="100" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="total_cost"><?= __('unpaid.cost') ?> *</label>
                <input type="number" id="total_cost" name="total_cost" class="form-input ltr-input" value="<?= $_SESSION['old']['total_cost'] ?? '' ?>" step="0.01" min="0" required>
            </div>

            <div class="form-group">
                <label class="form-label"><?= __('common.currency') ?></label>
                <?php $currencyFieldName = 'currency_code'; $currencySelected = $_SESSION['old']['currency_code'] ?? 'EGP'; ?>
                <?php require __DIR__ . '/../partials/currency_select.php'; ?>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="assigned_to"><?= __('unpaid.executor') ?></label>
            <select id="assigned_to" name="assigned_to" class="form-select">
                <option value=""><?= __("common.unassigned") ?></option>
                <?php foreach ($users as $u): ?>
                    <option value="<?= $u['id'] ?>" <?= ($_SESSION['old']['assigned_to'] ?? '') == $u['id'] ? 'selected' : '' ?>><?= htmlspecialchars($u['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label" for="description"><?= __('common.description') ?></label>
            <textarea id="description" name="description" class="form-textarea" rows="3"><?= htmlspecialchars($_SESSION['old']['description'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label class="form-label" for="attachment"><?= __('safe.files') ?></label>
            <input type="file" id="attachment" name="attachment" class="form-input" accept=".pdf,.jpg,.jpeg,.png,.gif,.doc,.docx,.xls,.xlsx,.zip,.txt">
        </div>

        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary"><?= __("common.save") ?></button>
            <?php if ($selectedClientId): ?>
                <a href="/clients/<?= (int)$selectedClientId ?>" class="btn btn-secondary"><?= __("common.cancel") ?></a>
            <?php else: ?>
                <a href="/unpaid-tasks" class="btn btn-secondary"><?= __("common.cancel") ?></a>
            <?php endif; ?>
        </div>
    </form>
</div>

<?php unset($_SESSION['old']); ?>
<?php require __DIR__ . '/../layout/footer.php'; ?>
