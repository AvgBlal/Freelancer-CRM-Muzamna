<?php $title = htmlspecialchars($service['title']); ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<?php
require __DIR__ . '/../partials/service_types.php';
$typeLabels = $serviceTypeLabels;
$cycleLabels = ['monthly' => __('services.cycle.monthly'), 'yearly' => __('services.cycle.yearly'), 'one_time' => __('services.cycle.one_time')];
$statusLabels = ['active' => __('services.status.active'), 'expired' => __('services.status.expired'), 'paused' => __('services.status.paused'), 'cancelled' => __('services.status.cancelled')];
$statusBadges = ['active' => 'badge-success', 'expired' => 'badge-urgent', 'paused' => 'badge-warning', 'cancelled' => 'badge-secondary'];
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= htmlspecialchars($service['title']) ?></h2>
        <div class="flex gap-2">
            <a href="/services/<?= $service['id'] ?>/edit" class="btn btn-secondary"><?= __("common.edit") ?></a>
            <form method="POST" action="/services/<?= $service['id'] ?>/delete" onsubmit="return confirm('<?= __('common.confirm_delete') ?>')" style="display:inline;">
                <?= \App\Core\CSRF::field() ?>
                <button type="submit" class="btn btn-danger"><?= __("common.delete") ?></button>
            </form>
        </div>
    </div>

    <div class="mb-3 flex gap-2" style="align-items: center; flex-wrap: wrap;">
        <span class="badge <?= $statusBadges[$service['status']] ?? 'badge-info' ?>"><?= $statusLabels[$service['status']] ?? $service['status'] ?></span>
        <?php if (!empty($service['is_personal'])): ?>
            <span class="badge badge-primary"><?= __("common.personal") ?></span>
        <?php endif; ?>
        <?php if ($service['auto_renew']): ?>
            <span class="badge badge-success"><?= __("services.auto_renew") ?></span>
        <?php endif; ?>
        <?php if ($renewalCount > 0): ?>
            <span class="badge badge-info"><?= $renewalCount ?></span>
        <?php endif; ?>

        <!-- Status change buttons -->
        <?php if ($service['status'] !== 'expired'): ?>
            <form method="POST" action="/services/<?= $service['id'] ?>/change-status" style="display:inline;">
                <?= \App\Core\CSRF::field() ?>
                <input type="hidden" name="status" value="expired">
                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('<?= __("services.confirm_expire") ?>')"><?= __("services.expire_btn") ?></button>
            </form>
        <?php endif; ?>
        <?php if ($service['status'] !== 'paused' && $service['status'] !== 'expired' && $service['status'] !== 'cancelled'): ?>
            <form method="POST" action="/services/<?= $service['id'] ?>/change-status" style="display:inline;">
                <?= \App\Core\CSRF::field() ?>
                <input type="hidden" name="status" value="paused">
                <button type="submit" class="btn btn-sm btn-warning"><?= __("services.status.paused") ?></button>
            </form>
        <?php endif; ?>
        <?php if ($service['status'] !== 'cancelled'): ?>
            <form method="POST" action="/services/<?= $service['id'] ?>/change-status" style="display:inline;">
                <?= \App\Core\CSRF::field() ?>
                <input type="hidden" name="status" value="cancelled">
                <button type="submit" class="btn btn-sm btn-secondary" onclick="return confirm('<?= __("common.confirm_delete") ?>')"><?= __("common.cancel") ?></button>
            </form>
        <?php endif; ?>
        <?php if ($service['status'] !== 'active'): ?>
            <form method="POST" action="/services/<?= $service['id'] ?>/change-status" style="display:inline;">
                <?= \App\Core\CSRF::field() ?>
                <input type="hidden" name="status" value="active">
                <button type="submit" class="btn btn-sm btn-success"><?= __("services.active_value") ?></button>
            </form>
        <?php endif; ?>
    </div>

    <table class="table" style="width: auto;">
        <tr><th><?= __("common.type") ?>:</th><td><?= $typeLabels[$service['type']] ?? $service['type'] ?></td></tr>
        <tr><th><?= __("services.start_date") ?>:</th><td><?= $service['start_date'] ?? '-' ?></td></tr>
        <tr><th><?= __("services.end_date") ?>:</th><td><?= $service['end_date'] ?><?php if ($service['status'] === 'active' && !empty($service['end_date']) && $service['end_date'] < date('Y-m-d')): ?> <span class="badge badge-urgent"><?= __("services.renewal_overdue") ?></span><?php endif; ?></td></tr>
        <tr><th><?= __("services.price") ?>:</th><td><?= $service['price_amount'] ? number_format((float)$service['price_amount'], 2) . ' ' . $service['currency_code'] : '-' ?></td></tr>
        <tr><th><?= __("services.billing_cycle") ?>:</th><td><?= $cycleLabels[$service['billing_cycle']] ?? ($service['billing_cycle'] ?? '-') ?></td></tr>
    </table>

    <?php if (!empty($service['notes_sensitive'])): ?>
    <div class="alert alert-info mt-3">
        <strong><?= __("common.notes") ?>:</strong><br>
        <?= nl2br(htmlspecialchars($service['notes_sensitive'])) ?>
    </div>
    <?php endif; ?>
</div>

<!-- Renewal Form -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><?= __("action.renew") ?></h3>
    </div>
    <form method="POST" action="/services/<?= $service['id'] ?>/renew">
        <?= \App\Core\CSRF::field() ?>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label"><?= __("services.new_end_date") ?> *</label>
                <input type="date" name="new_end_date" class="form-input ltr-input" required
                       value="<?= htmlspecialchars($suggestedEndDate) ?>">
                <span class="form-hint"><?= $cycleLabels[$service['billing_cycle']] ?? __('services.cycle.monthly') ?></span>
            </div>
            <div class="form-group">
                <label class="form-label"><?= __("common.notes") ?></label>
                <input type="text" name="renewal_notes" class="form-input" >
            </div>
        </div>
        <button type="submit" class="btn btn-success"><?= __("action.renew") ?></button>
    </form>
</div>

<?php if (!empty($clients)): ?>
<div class="card">
    <h3 class="mb-2"><?= __("common.all_clients") ?></h3>
    <table class="table">
        <thead>
            <tr><th><?= __("common.client") ?></th><th><?= __("common.phone") ?></th><th><?= __("common.email") ?></th></tr>
        </thead>
        <tbody>
            <?php foreach ($clients as $client): ?>
            <tr>
                <td><a href="/clients/<?= $client['id'] ?>"><?= htmlspecialchars($client['name']) ?></a></td>
                <td><?= htmlspecialchars($client['phone'] ?? '-') ?></td>
                <td><?= htmlspecialchars($client['email'] ?? '-') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<!-- Renewal History -->
<?php if (!empty($renewalHistory)): ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><?= __("common.details") ?> (<?= count($renewalHistory) ?>)</h3>
    </div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th><?= __("common.date") ?></th>
                    <th><?= __("logs.from_date") ?></th>
                    <th><?= __("logs.to_date") ?></th>
                    <th><?= __("logs.user") ?></th>
                    <th><?= __('common.notes') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($renewalHistory as $renewal): ?>
                    <tr>
                        <td><?= date('Y-m-d H:i', strtotime($renewal['created_at'])) ?></td>
                        <td><?= $renewal['old_end_date'] ?></td>
                        <td><?= $renewal['new_end_date'] ?></td>
                        <td><?= htmlspecialchars($renewal['renewed_by_name'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($renewal['notes'] ?? '-') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
