<?php $title = __('expenses.title'); ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<?php
$categoryLabels = [
    'hosting' => __('expenses.cat.hosting'), 'software' => __('expenses.cat.software'), 'domains' => __('expenses.cat.domains'),
    'tools' => __('expenses.cat.tools'), 'subscriptions' => __('expenses.cat.subscriptions'), 'freelancer' => __('expenses.cat.freelancer'),
    'office' => __('expenses.cat.office'), 'marketing' => __('expenses.cat.marketing'), 'taxes' => __('expenses.cat.taxes'), 'other' => __('expenses.cat.other'),
];
$statusLabels = [
    'pending' => '<span class="badge badge-warning">' . __('expenses.status.pending') . '</span>',
    'paid' => '<span class="badge badge-success">' . __('expenses.status.paid') . '</span>',
    'cancelled' => '<span class="badge badge-secondary">' . __('expenses.status.cancelled') . '</span>',
];
?>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value"><?php $currencyValues = $stats['totalPendingByCurrency']; $currencyDecimals = 2; require __DIR__ . '/../partials/currency_stat.php'; ?></div>
        <div class="stat-label"><?= __("expenses.pending") ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $stats['pendingCount'] ?></div>
        <div class="stat-label"><?= __("expenses.pending_bills") ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?php $currencyValues = $stats['paidThisMonthByCurrency']; $currencyDecimals = 2; require __DIR__ . '/../partials/currency_stat.php'; ?></div>
        <div class="stat-label"><?= __("expenses.paid_month") ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?php $currencyValues = $stats['recurringMonthlyByCurrency']; $currencyDecimals = 2; require __DIR__ . '/../partials/currency_stat.php'; ?></div>
        <div class="stat-label"><?= __("expenses.monthly_recurring") ?></div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= __('expenses.title') ?></h2>
        <div class="flex gap-2">
            <div class="dropdown" style="position:relative;display:inline-block;">
                <button type="button" onclick="this.nextElementSibling.classList.toggle('hidden')" class="btn btn-secondary"><i class="fas fa-download"></i> <?= __('common.export') ?></button>
                <div class="hidden" style="position:absolute;inset-inline-start:0;top:100%;background:white;border:1px solid #e2e8f0;border-radius:6px;box-shadow:0 4px 12px rgba(0,0,0,.15);z-index:50;min-width:160px;margin-top:4px;">
                    <a href="/export/expenses" class="dropdown-item" style="display:block;padding:8px 16px;text-decoration:none;color:#333;white-space:nowrap;"><i class="fas fa-file-csv text-green-600"></i> <?= __('common.export_csv') ?></a>
                    <a href="/export/expenses/pdf" class="dropdown-item" style="display:block;padding:8px 16px;text-decoration:none;color:#333;white-space:nowrap;"><i class="fas fa-file-pdf text-red-600"></i> <?= __('common.export_pdf') ?></a>
                </div>
            </div>
            <a href="/expenses/create" class="btn btn-primary">+ <?= __('expenses.new') ?></a>
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" class="mb-3">
        <div class="flex gap-2" style="flex-wrap: wrap;">
            <input type="text" name="search" class="form-input" placeholder="<?= __('common.search_placeholder') ?>" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" style="width: 200px;">

            <select name="status" class="form-select" style="width: 150px;">
                <option value=""><?= __("common.all_statuses") ?></option>
                <option value="pending" <?= ($_GET['status'] ?? '') === 'pending' ? 'selected' : '' ?>><?= __('expenses.status.pending') ?></option>
                <option value="paid" <?= ($_GET['status'] ?? '') === 'paid' ? 'selected' : '' ?>><?= __('expenses.status.paid') ?></option>
                <option value="cancelled" <?= ($_GET['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>><?= __('expenses.status.cancelled') ?></option>
            </select>

            <select name="category" class="form-select" style="width: 150px;">
                <option value=""><?= __("common.all_categories") ?></option>
                <?php foreach ($categoryLabels as $key => $label): ?>
                    <option value="<?= $key ?>" <?= ($_GET['category'] ?? '') === $key ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="btn btn-secondary"><?= __("common.filter") ?></button>
        </div>
    </form>

    <?php if (empty($expenses)): ?>
        <p><?= __("expenses.empty") ?></p>
    <?php else: ?>
        <div class="table-container">
            <table class="table bulk-table">
                <thead>
                    <tr>
                        <th style="width: 30px;"><input type="checkbox" class="bulk-select-all"></th>
                        <th><?= __("expenses.expense") ?></th>
                        <th><?= __("expenses.category") ?></th>
                        <th><?= __("common.amount") ?></th>
                        <th><?= __("common.due_date") ?></th>
                        <th><?= __("common.status") ?></th>
                        <th><?= __("common.actions") ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($expenses as $expense): ?>
                        <?php
                        $isOverdue = $expense['status'] === 'pending'
                            && $expense['due_date'] && $expense['due_date'] < date('Y-m-d');
                        ?>
                        <tr>
                            <td><input type="checkbox" class="bulk-check" value="<?= $expense['id'] ?>"></td>
                            <td>
                                <a href="/expenses/<?= $expense['id'] ?>"><strong><?= htmlspecialchars($expense['title']) ?></strong></a>
                                <?php if ($expense['vendor']): ?>
                                    <br><small class="text-muted"><?= htmlspecialchars($expense['vendor']) ?></small>
                                <?php endif; ?>
                                <?php if ($expense['is_recurring']): ?>
                                    <span class="badge badge-info"><?= __('expenses.recurring') ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= $categoryLabels[$expense['category']] ?? $expense['category'] ?></td>
                            <td><strong><?= number_format($expense['amount'], 2) ?> <?= $expense['currency_code'] ?></strong></td>
                            <td>
                                <?= $expense['due_date'] ?? '-' ?>
                                <?php if ($isOverdue): ?>
                                    <br><span class="badge badge-urgent"><?= __("common.overdue") ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= $statusLabels[$expense['status']] ?? $expense['status'] ?></td>
                            <td class="flex gap-1">
                                <?php if ($expense['status'] === 'pending'): ?>
                                    <form method="POST" action="/expenses/<?= $expense['id'] ?>/mark-paid" style="display:inline;">
                                        <?= \App\Core\CSRF::field() ?>
                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('<?= __('common.confirm_payment') ?>')"><?= __('common.mark_paid') ?></button>
                                    </form>
                                <?php endif; ?>
                                <a href="/expenses/<?= $expense['id'] ?>" class="btn btn-sm btn-secondary"><?= __("common.view") ?></a>
                                <a href="/expenses/<?= $expense['id'] ?>/edit" class="btn btn-sm btn-secondary"><?= __("common.edit") ?></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php $bulkAction = '/bulk/expenses'; require __DIR__ . '/../partials/bulk_actions.php'; ?>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
