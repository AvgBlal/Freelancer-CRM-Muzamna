<?php $title = __('common.details'); ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<?php
$statusLabels = [
    'pending' => __('dues.status.pending'),
    'partial' => __('dues.status.partial'),
    'paid' => __('dues.status.paid'),
    'cancelled' => __('dues.status.cancelled'),
];
$statusBadges = [
    'pending' => 'badge-warning',
    'partial' => 'badge-info',
    'paid' => 'badge-success',
    'cancelled' => 'badge-secondary',
];
$isOverdue = $due['status'] !== 'paid' && $due['status'] !== 'cancelled'
    && $due['due_date'] && $due['due_date'] < date('Y-m-d');
$remaining = $due['amount'] - $due['paid_amount'];
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= htmlspecialchars($due['person_name']) ?></h2>
        <div class="flex gap-2">
            <?php if ($due['status'] !== 'paid'): ?>
                <form method="POST" action="/dues/<?= $due['id'] ?>/mark-paid" style="display:inline;">
                    <?= \App\Core\CSRF::field() ?>
                    <button type="submit" class="btn btn-success" onclick="return confirm('<?= __('common.confirm_receive') ?>')"><?= __('common.mark_paid') ?></button>
                </form>
            <?php endif; ?>
            <a href="/dues/<?= $due['id'] ?>/edit" class="btn btn-secondary"><?= __("common.edit") ?></a>
            <form method="POST" action="/dues/<?= $due['id'] ?>/delete" onsubmit="return confirm('<?= __('common.confirm_delete') ?>')" style="display:inline;">
                <?= \App\Core\CSRF::field() ?>
                <button type="submit" class="btn btn-danger"><?= __("common.delete") ?></button>
            </form>
        </div>
    </div>

    <div class="mb-3">
        <span class="badge <?= $statusBadges[$due['status']] ?>"><?= $statusLabels[$due['status']] ?></span>
        <?php if ($isOverdue): ?>
            <span class="badge badge-urgent"><?= __('common.overdue') ?></span>
        <?php endif; ?>
    </div>

    <table class="table" style="width: auto;">
        <tr><th><?= __('dues.person') ?>:</th><td><?= htmlspecialchars($due['person_name']) ?></td></tr>
        <?php if ($due['person_phone']): ?>
            <tr><th><?= __('common.phone') ?>:</th><td class="ltr-input"><?= htmlspecialchars($due['person_phone']) ?></td></tr>
        <?php endif; ?>
        <?php if ($due['description']): ?>
            <tr><th><?= __('common.description') ?>:</th><td><?= nl2br(htmlspecialchars($due['description'])) ?></td></tr>
        <?php endif; ?>
        <tr><th><?= __('common.amount') ?>:</th><td><strong><?= number_format($due['amount'], 2) ?> <?= $due['currency_code'] ?></strong></td></tr>
        <tr><th><?= __('common.paid') ?>:</th><td><?= number_format($due['paid_amount'], 2) ?> <?= $due['currency_code'] ?></td></tr>
        <tr><th><?= __('common.remaining') ?>:</th><td><strong><?= number_format($remaining, 2) ?> <?= $due['currency_code'] ?></strong></td></tr>
        <tr><th><?= __('common.due_date') ?>:</th><td><?= $due['due_date'] ?? '-' ?></td></tr>
        <?php if ($due['paid_at']): ?>
            <tr><th><?= __('common.paid') ?>:</th><td><?= $due['paid_at'] ?></td></tr>
        <?php endif; ?>
        <tr><th><?= __('common.created_at') ?>:</th><td><?= $due['created_at'] ?></td></tr>
    </table>

    <?php if ($due['notes']): ?>
        <div class="alert alert-info mt-3">
            <strong><?= __('common.notes') ?>:</strong><br>
            <?= nl2br(htmlspecialchars($due['notes'])) ?>
        </div>
    <?php endif; ?>

    <?php if ($remaining > 0 && $due['amount'] > 0 && $due['status'] !== 'cancelled'): ?>
        <?php $paidPct = min(100, round(($due['paid_amount'] / $due['amount']) * 100)); ?>
        <div class="mt-3">
            <div class="progress-bar progress-bar-lg">
                <div class="progress-track">
                    <div class="progress-fill<?= $paidPct >= 100 ? ' progress-fill-success' : '' ?>" style="width: <?= $paidPct ?>%;"></div>
                </div>
            </div>
            <small class="text-muted"><?= $paidPct ?>% - <?= number_format($due['paid_amount'], 2) ?> / <?= number_format($due['amount'], 2) ?></small>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
