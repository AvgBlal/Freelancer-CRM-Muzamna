<?php $title = __('expenses.edit'); ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= __('expenses.edit') ?>: <?= htmlspecialchars($expense['title']) ?></h2>
    </div>

    <form method="POST" action="/expenses/<?= $expense['id'] ?>">
        <?= $csrf ?>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label"><?= __('common.title') ?> *</label>
                <input type="text" name="title" class="form-input" required value="<?= htmlspecialchars($expense['title']) ?>">
            </div>

            <div class="form-group">
                <label class="form-label"><?= __('expenses.vendor') ?></label>
                <input type="text" name="vendor" class="form-input" value="<?= htmlspecialchars($expense['vendor'] ?? '') ?>">
            </div>
        </div>

        <div class="form-row flex-wrap">
            <div class="form-group">
                <label class="form-label"><?= __('expenses.category') ?> *</label>
                <select name="category" class="form-select" required>
                    <option value="hosting" <?= $expense['category'] === 'hosting' ? 'selected' : '' ?>><?= __('expenses.cat.hosting') ?></option>
                    <option value="software" <?= $expense['category'] === 'software' ? 'selected' : '' ?>><?= __('expenses.cat.software') ?></option>
                    <option value="domains" <?= $expense['category'] === 'domains' ? 'selected' : '' ?>><?= __('expenses.cat.domains') ?></option>
                    <option value="tools" <?= $expense['category'] === 'tools' ? 'selected' : '' ?>><?= __('expenses.cat.tools') ?></option>
                    <option value="subscriptions" <?= $expense['category'] === 'subscriptions' ? 'selected' : '' ?>><?= __('expenses.cat.subscriptions') ?></option>
                    <option value="freelancer" <?= $expense['category'] === 'freelancer' ? 'selected' : '' ?>><?= __('expenses.cat.freelancer') ?></option>
                    <option value="office" <?= $expense['category'] === 'office' ? 'selected' : '' ?>><?= __('expenses.cat.office') ?></option>
                    <option value="marketing" <?= $expense['category'] === 'marketing' ? 'selected' : '' ?>><?= __('expenses.cat.marketing') ?></option>
                    <option value="taxes" <?= $expense['category'] === 'taxes' ? 'selected' : '' ?>><?= __('expenses.cat.taxes') ?></option>
                    <option value="other" <?= $expense['category'] === 'other' ? 'selected' : '' ?>><?= __('expenses.cat.other') ?></option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label"><?= __('common.amount') ?> *</label>
                <input type="number" name="amount" step="0.01" min="0" class="form-input ltr-input" required value="<?= htmlspecialchars($expense['amount']) ?>">
            </div>

            <div class="form-group">
                <label class="form-label"><?= __('common.currency') ?></label>
                <?php $currencyFieldName = 'currency_code'; $currencySelected = $expense['currency_code'] ?? 'EGP'; ?>
                <?php require __DIR__ . '/../partials/currency_select.php'; ?>
            </div>
        </div>

        <div class="form-row flex-wrap">
            <div class="form-group">
                <label class="form-label"><?= __('common.status') ?></label>
                <select name="status" class="form-select">
                    <option value="pending" <?= $expense['status'] === 'pending' ? 'selected' : '' ?>><?= __('expenses.status.pending') ?></option>
                    <option value="paid" <?= $expense['status'] === 'paid' ? 'selected' : '' ?>><?= __('expenses.status.paid') ?></option>
                    <option value="cancelled" <?= $expense['status'] === 'cancelled' ? 'selected' : '' ?>><?= __('expenses.status.cancelled') ?></option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label"><?= __("common.due_date") ?></label>
                <input type="date" name="due_date" class="form-input ltr-input" value="<?= htmlspecialchars($expense['due_date'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label class="form-label"><?= __('expenses.recurring_cycle') ?></label>
                <select name="billing_cycle" class="form-select">
                    <option value="">--</option>
                    <option value="monthly" <?= ($expense['billing_cycle'] ?? '') === 'monthly' ? 'selected' : '' ?>><?= __('services.cycle.monthly') ?></option>
                    <option value="yearly" <?= ($expense['billing_cycle'] ?? '') === 'yearly' ? 'selected' : '' ?>><?= __('services.cycle.yearly') ?></option>
                    <option value="one_time" <?= ($expense['billing_cycle'] ?? '') === 'one_time' ? 'selected' : '' ?>><?= __('services.cycle.one_time') ?></option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="checkbox-label">
                <input type="checkbox" name="is_recurring" value="1" <?= $expense['is_recurring'] ? 'checked' : '' ?>>
                <?= __('expenses.recurring') ?>
            </label>
        </div>

        <div class="form-group">
            <label class="form-label"><?= __("common.notes") ?></label>
            <textarea name="notes" class="form-textarea" rows="2"><?= htmlspecialchars($expense['notes'] ?? '') ?></textarea>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary"><?= __("common.save") ?></button>
            <a href="/expenses/<?= $expense['id'] ?>" class="btn btn-secondary"><?= __("common.cancel") ?></a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
