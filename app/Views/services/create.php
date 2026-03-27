<?php $title = __('services.create'); ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= __('services.new') ?></h2>
    </div>

    <form method="POST" action="/services">
        <?= $csrf ?>

        <div class="form-group">
            <label class="form-label"><?= __('common.title') ?> *</label>
            <input type="text" name="title" class="form-input" required value="<?= htmlspecialchars($_SESSION['old']['title'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label class="form-label"><?= __('services.service_type') ?> *</label>
            <?php require __DIR__ . '/../partials/service_types.php'; ?>
            <select name="type" class="form-select" required>
                <option value="">--</option>
                <?php foreach ($serviceTypeLabels as $val => $label): ?>
                    <option value="<?= $val ?>" <?= ($_SESSION['old']['type'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label"><?= __('services.start_date') ?></label>
            <input type="date" name="start_date" class="form-input ltr-input" value="<?= htmlspecialchars($_SESSION['old']['start_date'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label class="form-label"><?= __('services.end_date') ?> *</label>
            <input type="date" name="end_date" class="form-input ltr-input" required value="<?= htmlspecialchars($_SESSION['old']['end_date'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label class="checkbox-label">
                <input type="checkbox" name="auto_renew" value="1" <?= ($_SESSION['old']['auto_renew'] ?? '') ? 'checked' : '' ?>>
                <?= __('services.auto_renew') ?>
            </label>
        </div>

        <div class="form-group">
            <label class="checkbox-label">
                <input type="checkbox" name="is_personal" value="1" <?= !empty($_SESSION['old']['is_personal']) ? 'checked' : '' ?>>
                <?= __("common.personal") ?>
            </label>
        </div>

        <div class="form-group">
            <label class="form-label"><?= __('common.amount') ?></label>
            <input type="number" step="0.01" name="price_amount" class="form-input ltr-input" value="<?= htmlspecialchars($_SESSION['old']['price_amount'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label class="form-label"><?= __('common.currency') ?></label>
            <?php $currencyFieldName = 'currency_code'; $currencySelected = $_SESSION['old']['currency_code'] ?? 'EGP'; $currencyShowCustom = true; ?>
            <?php require __DIR__ . '/../partials/currency_select.php'; ?>
        </div>

        <div class="form-group">
            <label class="form-label"><?= __('services.billing_cycle') ?></label>
            <select name="billing_cycle" class="form-select">
                <option value="">--</option>
                <option value="monthly" <?= ($_SESSION['old']['billing_cycle'] ?? '') === 'monthly' ? 'selected' : '' ?>><?= __('services.cycle.monthly') ?></option>
                <option value="yearly" <?= ($_SESSION['old']['billing_cycle'] ?? '') === 'yearly' ? 'selected' : '' ?>><?= __('services.cycle.yearly') ?></option>
                <option value="one_time" <?= ($_SESSION['old']['billing_cycle'] ?? '') === 'one_time' ? 'selected' : '' ?>><?= __('services.cycle.one_time') ?></option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label"><?= __("common.all_clients") ?></label>
            <select name="client_ids[]" class="form-select" multiple size="5">
                <?php foreach ($clients as $client): ?>
                    <option value="<?= $client['id'] ?>"><?= htmlspecialchars($client['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <small></small>
        </div>

        <div class="form-group">
            <label class="form-label"><?= __('common.notes') ?></label>
            <textarea name="notes_sensitive" class="form-textarea"><?= htmlspecialchars($_SESSION['old']['notes_sensitive'] ?? '') ?></textarea>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary"><?= __("common.save") ?></button>
            <a href="/services" class="btn btn-secondary"><?= __("common.cancel") ?></a>
        </div>
    </form>
</div>

<?php unset($_SESSION['old']); ?>
<?php require __DIR__ . '/../layout/footer.php'; ?>