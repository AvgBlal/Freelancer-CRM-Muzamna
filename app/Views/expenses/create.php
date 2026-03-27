<?php $title = __('expenses.create'); ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= __('expenses.create') ?></h2>
    </div>

    <form method="POST" action="/expenses">
        <?= $csrf ?>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label"><?= __('common.title') ?> *</label>
                <input type="text" name="title" class="form-input" required value="<?= htmlspecialchars($_SESSION['old']['title'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label class="form-label"><?= __('expenses.vendor') ?></label>
                <input type="text" name="vendor" class="form-input" value="<?= htmlspecialchars($_SESSION['old']['vendor'] ?? '') ?>">
            </div>
        </div>

        <div class="form-row flex-wrap">
            <div class="form-group">
                <label class="form-label"><?= __('expenses.category') ?> *</label>
                <select name="category" class="form-select" required>
                    <option value="">--</option>
                    <option value="hosting" <?= ($_SESSION['old']['category'] ?? '') === 'hosting' ? 'selected' : '' ?>><?= __('expenses.cat.hosting') ?></option>
                    <option value="software" <?= ($_SESSION['old']['category'] ?? '') === 'software' ? 'selected' : '' ?>><?= __('expenses.cat.software') ?></option>
                    <option value="domains" <?= ($_SESSION['old']['category'] ?? '') === 'domains' ? 'selected' : '' ?>><?= __('expenses.cat.domains') ?></option>
                    <option value="tools" <?= ($_SESSION['old']['category'] ?? '') === 'tools' ? 'selected' : '' ?>><?= __('expenses.cat.tools') ?></option>
                    <option value="subscriptions" <?= ($_SESSION['old']['category'] ?? '') === 'subscriptions' ? 'selected' : '' ?>><?= __('expenses.cat.subscriptions') ?></option>
                    <option value="freelancer" <?= ($_SESSION['old']['category'] ?? '') === 'freelancer' ? 'selected' : '' ?>><?= __('expenses.cat.freelancer') ?></option>
                    <option value="office" <?= ($_SESSION['old']['category'] ?? '') === 'office' ? 'selected' : '' ?>><?= __('expenses.cat.office') ?></option>
                    <option value="marketing" <?= ($_SESSION['old']['category'] ?? '') === 'marketing' ? 'selected' : '' ?>><?= __('expenses.cat.marketing') ?></option>
                    <option value="taxes" <?= ($_SESSION['old']['category'] ?? '') === 'taxes' ? 'selected' : '' ?>><?= __('expenses.cat.taxes') ?></option>
                    <option value="other" <?= ($_SESSION['old']['category'] ?? '') === 'other' ? 'selected' : '' ?>><?= __('expenses.cat.other') ?></option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label"><?= __('common.amount') ?> *</label>
                <input type="number" name="amount" step="0.01" min="0" class="form-input ltr-input" required value="<?= htmlspecialchars($_SESSION['old']['amount'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label class="form-label"><?= __('common.currency') ?></label>
                <?php $currencyFieldName = 'currency_code'; $currencySelected = $_SESSION['old']['currency_code'] ?? 'EGP'; ?>
                <?php require __DIR__ . '/../partials/currency_select.php'; ?>
            </div>
        </div>

        <div class="form-row flex-wrap">
            <div class="form-group">
                <label class="form-label"><?= __("common.due_date") ?></label>
                <input type="date" name="due_date" class="form-input ltr-input" value="<?= htmlspecialchars($_SESSION['old']['due_date'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label class="form-label"><?= __('expenses.recurring_cycle') ?></label>
                <select name="billing_cycle" class="form-select">
                    <option value="">--</option>
                    <option value="monthly" <?= ($_SESSION['old']['billing_cycle'] ?? '') === 'monthly' ? 'selected' : '' ?>><?= __('services.cycle.monthly') ?></option>
                    <option value="yearly" <?= ($_SESSION['old']['billing_cycle'] ?? '') === 'yearly' ? 'selected' : '' ?>><?= __('services.cycle.yearly') ?></option>
                    <option value="one_time" <?= ($_SESSION['old']['billing_cycle'] ?? '') === 'one_time' ? 'selected' : '' ?>><?= __('services.cycle.one_time') ?></option>
                </select>
            </div>

            <div class="form-group" style="align-self: flex-end;">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_recurring" value="1" <?= !empty($_SESSION['old']['is_recurring']) ? 'checked' : '' ?>>
                    <?= __('expenses.recurring') ?>
                </label>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label"><?= __("common.notes") ?></label>
            <textarea name="notes" class="form-textarea" rows="2"><?= htmlspecialchars($_SESSION['old']['notes'] ?? '') ?></textarea>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary"><?= __("common.save") ?></button>
            <a href="/expenses" class="btn btn-secondary"><?= __("common.cancel") ?></a>
        </div>
    </form>
</div>

<?php unset($_SESSION['old']); ?>
<?php require __DIR__ . '/../layout/footer.php'; ?>
