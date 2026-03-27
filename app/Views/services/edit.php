<?php $title = __('services.edit'); ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= __('services.edit') ?>: <?= htmlspecialchars($service['title']) ?></h2>
    </div>

    <form method="POST" action="/services/<?= $service['id'] ?>">
        <?= $csrf ?>

        <div class="form-group">
            <label class="form-label"><?= __('common.title') ?> *</label>
            <input type="text" name="title" class="form-input" required value="<?= htmlspecialchars($service['title']) ?>">
        </div>

        <div class="form-group">
            <label class="form-label"><?= __('services.service_type') ?> *</label>
            <?php require __DIR__ . '/../partials/service_types.php'; ?>
            <select name="type" class="form-select" required>
                <option value="">--</option>
                <?php foreach ($serviceTypeLabels as $val => $label): ?>
                    <option value="<?= $val ?>" <?= $service['type'] === $val ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label"><?= __('common.status') ?></label>
            <select name="status" class="form-select">
                <option value="active" <?= $service['status'] === 'active' ? 'selected' : '' ?>><?= __('services.status.active') ?></option>
                <option value="expired" <?= $service['status'] === 'expired' ? 'selected' : '' ?>><?= __('services.status.expired') ?></option>
                <option value="paused" <?= $service['status'] === 'paused' ? 'selected' : '' ?>><?= __('services.status.paused') ?></option>
                <option value="cancelled" <?= $service['status'] === 'cancelled' ? 'selected' : '' ?>><?= __('services.status.cancelled') ?></option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label"><?= __('services.start_date') ?></label>
            <input type="date" name="start_date" class="form-input ltr-input" value="<?= htmlspecialchars($service['start_date'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label class="form-label"><?= __('services.end_date') ?> *</label>
            <input type="date" name="end_date" class="form-input ltr-input" required value="<?= htmlspecialchars($service['end_date']) ?>">
        </div>

        <div class="form-group">
            <label class="checkbox-label">
                <input type="checkbox" name="auto_renew" value="1" <?= $service['auto_renew'] ? 'checked' : '' ?>>
                <?= __('services.auto_renew') ?>
            </label>
        </div>

        <div class="form-group">
            <label class="checkbox-label">
                <input type="checkbox" name="is_personal" value="1" <?= !empty($service['is_personal']) ? 'checked' : '' ?>>
                <?= __("common.personal") ?>
            </label>
        </div>

        <div class="form-group">
            <label class="form-label"><?= __('common.amount') ?></label>
            <input type="number" step="0.01" name="price_amount" class="form-input ltr-input" value="<?= htmlspecialchars($service['price_amount'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label class="form-label"><?= __('common.currency') ?></label>
            <?php $currencyFieldName = 'currency_code'; $currencySelected = $service['currency_code'] ?? 'EGP'; ?>
            <?php require __DIR__ . '/../partials/currency_select.php'; ?>
        </div>

        <div class="form-group">
            <label class="form-label"><?= __("services.billing_cycle") ?></label>
            <select name="billing_cycle" class="form-select">
                <option value="">--</option>
                <option value="monthly" <?= ($service['billing_cycle'] ?? '') === 'monthly' ? 'selected' : '' ?>><?= __("services.cycle.monthly") ?></option>
                <option value="yearly" <?= ($service['billing_cycle'] ?? '') === 'yearly' ? 'selected' : '' ?>><?= __("services.cycle.yearly") ?></option>
                <option value="one_time" <?= ($service['billing_cycle'] ?? '') === 'one_time' ? 'selected' : '' ?>><?= __("services.cycle.one_time") ?></option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label"><?= __("common.all_clients") ?></label>
            <select name="client_ids[]" class="form-select" multiple size="5">
                <?php foreach ($allClients as $client): ?>
                    <option value="<?= $client['id'] ?>" <?= in_array($client['id'], $linkedClients) ? 'selected' : '' ?>><?= htmlspecialchars($client['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <small></small>
        </div>

        <div class="form-group">
            <label class="form-label"><?= __("common.notes") ?></label>
            <textarea name="notes_sensitive" class="form-textarea"><?= htmlspecialchars($service['notes_sensitive'] ?? '') ?></textarea>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary"><?= __("common.save") ?></button>
            <a href="/services/<?= $service['id'] ?>" class="btn btn-secondary"><?= __("common.cancel") ?></a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>