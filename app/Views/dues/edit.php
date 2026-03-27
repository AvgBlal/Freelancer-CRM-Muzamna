<?php $title = __('dues.edit'); ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= __('dues.edit') ?>: <?= htmlspecialchars($due['person_name']) ?></h2>
    </div>

    <form method="POST" action="/dues/<?= $due['id'] ?>">
        <?= $csrf ?>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label"><?= __('dues.person_name') ?> *</label>
                <input type="text" name="person_name" class="form-input" required value="<?= htmlspecialchars($due['person_name']) ?>">
            </div>

            <div class="form-group">
                <label class="form-label"><?= __('common.phone') ?></label>
                <input type="text" name="person_phone" class="form-input ltr-input" value="<?= htmlspecialchars($due['person_phone'] ?? '') ?>">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label"><?= __('common.description') ?></label>
            <textarea name="description" class="form-textarea" rows="3"><?= htmlspecialchars($due['description'] ?? '') ?></textarea>
        </div>

        <div class="form-row flex-wrap">
            <div class="form-group">
                <label class="form-label"><?= __('common.amount') ?> *</label>
                <input type="number" name="amount" step="0.01" min="0" class="form-input ltr-input" required value="<?= htmlspecialchars($due['amount']) ?>">
            </div>

            <div class="form-group">
                <label class="form-label"><?= __('common.currency') ?></label>
                <?php $currencyFieldName = 'currency_code'; $currencySelected = $due['currency_code'] ?? 'EGP'; ?>
                <?php require __DIR__ . '/../partials/currency_select.php'; ?>
            </div>

            <div class="form-group">
                <label class="form-label"><?= __("common.due_date") ?></label>
                <input type="date" name="due_date" class="form-input ltr-input" value="<?= htmlspecialchars($due['due_date'] ?? '') ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label"><?= __('common.status') ?></label>
                <select name="status" class="form-select">
                    <option value="pending" <?= $due['status'] === 'pending' ? 'selected' : '' ?>><?= __('dues.status.pending') ?></option>
                    <option value="partial" <?= $due['status'] === 'partial' ? 'selected' : '' ?>><?= __('dues.status.partial') ?></option>
                    <option value="paid" <?= $due['status'] === 'paid' ? 'selected' : '' ?>><?= __('dues.status.paid') ?></option>
                    <option value="cancelled" <?= $due['status'] === 'cancelled' ? 'selected' : '' ?>><?= __('dues.status.cancelled') ?></option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label"><?= __('common.paid') ?></label>
                <input type="number" name="paid_amount" step="0.01" min="0" class="form-input ltr-input" value="<?= htmlspecialchars($due['paid_amount']) ?>">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label"><?= __("common.notes") ?></label>
            <textarea name="notes" class="form-textarea" rows="2"><?= htmlspecialchars($due['notes'] ?? '') ?></textarea>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary"><?= __("common.save") ?></button>
            <a href="/dues/<?= $due['id'] ?>" class="btn btn-secondary"><?= __("common.cancel") ?></a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
