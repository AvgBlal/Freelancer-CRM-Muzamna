<?php $title = __('common.details'); ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<?php
$categoryLabels = [
    'hosting' => __('expenses.cat.hosting'), 'software' => __('expenses.cat.software'), 'domains' => __('expenses.cat.domains'),
    'tools' => __('expenses.cat.tools'), 'subscriptions' => __('expenses.cat.subscriptions'), 'freelancer' => __('expenses.cat.freelancer'),
    'office' => __('expenses.cat.office'), 'marketing' => __('expenses.cat.marketing'), 'taxes' => __('expenses.cat.taxes'), 'other' => __('expenses.cat.other'),
];
$statusLabels = ['pending' => __('expenses.status.pending'), 'paid' => __('expenses.status.paid'), 'cancelled' => __('expenses.status.cancelled')];
$statusBadges = ['pending' => 'badge-warning', 'paid' => 'badge-success', 'cancelled' => 'badge-secondary'];
$cycleLabels = ['monthly' => __('services.cycle.monthly'), 'yearly' => __('services.cycle.yearly'), 'one_time' => __('services.cycle.one_time')];
$isOverdue = $expense['status'] === 'pending' && $expense['due_date'] && $expense['due_date'] < date('Y-m-d');
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= htmlspecialchars($expense['title']) ?></h2>
        <div class="flex gap-2">
            <?php if ($expense['status'] === 'pending'): ?>
                <form method="POST" action="/expenses/<?= $expense['id'] ?>/mark-paid" style="display:inline;">
                    <?= \App\Core\CSRF::field() ?>
                    <button type="submit" class="btn btn-success" onclick="return confirm('<?= __('common.confirm_payment') ?>')"><?= __('common.mark_paid') ?></button>
                </form>
            <?php endif; ?>
            <a href="/expenses/<?= $expense['id'] ?>/edit" class="btn btn-secondary"><?= __("common.edit") ?></a>
            <form method="POST" action="/expenses/<?= $expense['id'] ?>/delete" onsubmit="return confirm('<?= __('common.confirm_delete') ?>')" style="display:inline;">
                <?= \App\Core\CSRF::field() ?>
                <button type="submit" class="btn btn-danger"><?= __("common.delete") ?></button>
            </form>
        </div>
    </div>

    <div class="mb-3">
        <span class="badge <?= $statusBadges[$expense['status']] ?>"><?= $statusLabels[$expense['status']] ?></span>
        <?php if ($expense['is_recurring']): ?>
            <span class="badge badge-info"><?= __('expenses.recurring') ?></span>
        <?php endif; ?>
        <?php if ($isOverdue): ?>
            <span class="badge badge-urgent"><?= __('common.overdue') ?></span>
        <?php endif; ?>
    </div>

    <table class="table" style="width: auto;">
        <tr><th><?= __('expenses.category') ?>:</th><td><?= $categoryLabels[$expense['category']] ?? $expense['category'] ?></td></tr>
        <?php if ($expense['vendor']): ?>
            <tr><th><?= __('expenses.vendor') ?>:</th><td><?= htmlspecialchars($expense['vendor']) ?></td></tr>
        <?php endif; ?>
        <tr><th><?= __('common.amount') ?>:</th><td><strong><?= number_format($expense['amount'], 2) ?> <?= $expense['currency_code'] ?></strong></td></tr>
        <tr><th><?= __('common.due_date') ?>:</th><td><?= $expense['due_date'] ?? '-' ?></td></tr>
        <?php if ($expense['billing_cycle']): ?>
            <tr><th><?= __('expenses.recurring_cycle') ?>:</th><td><?= $cycleLabels[$expense['billing_cycle']] ?? $expense['billing_cycle'] ?></td></tr>
        <?php endif; ?>
        <?php if ($expense['paid_at']): ?>
            <tr><th><?= __('common.paid') ?>:</th><td><?= $expense['paid_at'] ?></td></tr>
        <?php endif; ?>
        <tr><th><?= __('common.created_at') ?>:</th><td><?= $expense['created_at'] ?></td></tr>
    </table>

    <?php if ($expense['notes']): ?>
        <div class="alert alert-info mt-3">
            <strong><?= __('common.notes') ?>:</strong><br>
            <?= nl2br(htmlspecialchars($expense['notes'])) ?>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
