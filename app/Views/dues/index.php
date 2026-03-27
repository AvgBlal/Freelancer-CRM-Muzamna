<?php $title = __('dues.title'); ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<?php
$statusLabels = [
    'pending' => '<span class="badge badge-warning">' . __('dues.status.pending') . '</span>',
    'partial' => '<span class="badge badge-info">' . __('dues.status.partial') . '</span>',
    'paid' => '<span class="badge badge-success">' . __('dues.status.paid') . '</span>',
    'cancelled' => '<span class="badge badge-secondary">' . __('dues.status.cancelled') . '</span>',
];
?>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value"><?php $currencyValues = $stats['totalPendingByCurrency']; $currencyDecimals = 2; require __DIR__ . '/../partials/currency_stat.php'; ?></div>
        <div class="stat-label"><?= __('dues.total_due') ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $stats['pendingCount'] ?></div>
        <div class="stat-label"><?= __('dues.pending_amounts') ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $stats['overdueCount'] ?></div>
        <div class="stat-label"><?= __('dues.overdue_amounts') ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?php $currencyValues = $stats['paidThisMonthByCurrency']; $currencyDecimals = 2; require __DIR__ . '/../partials/currency_stat.php'; ?></div>
        <div class="stat-label"><?= __('dues.collected_month') ?></div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= __('dues.subtitle') ?></h2>
        <div class="flex gap-2">
            <div class="dropdown" style="position:relative;display:inline-block;">
                <button type="button" onclick="this.nextElementSibling.classList.toggle('hidden')" class="btn btn-secondary"><i class="fas fa-download"></i> <?= __('common.export') ?></button>
                <div class="hidden" style="position:absolute;inset-inline-start:0;top:100%;background:white;border:1px solid #e2e8f0;border-radius:6px;box-shadow:0 4px 12px rgba(0,0,0,.15);z-index:50;min-width:160px;margin-top:4px;">
                    <a href="/export/dues" class="dropdown-item" style="display:block;padding:8px 16px;text-decoration:none;color:#333;white-space:nowrap;"><i class="fas fa-file-csv text-green-600"></i> <?= __('common.export_csv') ?></a>
                    <a href="/export/dues/pdf" class="dropdown-item" style="display:block;padding:8px 16px;text-decoration:none;color:#333;white-space:nowrap;"><i class="fas fa-file-pdf text-red-600"></i> <?= __('common.export_pdf') ?></a>
                </div>
            </div>
            <a href="/dues/create" class="btn btn-primary">+ <?= __('dues.new') ?></a>
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" class="mb-3">
        <div class="flex gap-2" style="flex-wrap: wrap;">
            <input type="text" name="search" class="form-input" placeholder="<?= __('dues.search_placeholder') ?>" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" style="width: 250px;">

            <select name="status" class="form-select" style="width: 150px;">
                <option value=""><?= __("common.all_statuses") ?></option>
                <option value="pending" <?= ($_GET['status'] ?? '') === 'pending' ? 'selected' : '' ?>><?= __('dues.status.pending') ?></option>
                <option value="partial" <?= ($_GET['status'] ?? '') === 'partial' ? 'selected' : '' ?>><?= __('dues.status.partial') ?></option>
                <option value="paid" <?= ($_GET['status'] ?? '') === 'paid' ? 'selected' : '' ?>><?= __('dues.status.paid') ?></option>
                <option value="cancelled" <?= ($_GET['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>><?= __('dues.status.cancelled') ?></option>
            </select>

            <button type="submit" class="btn btn-secondary"><?= __("common.filter") ?></button>
        </div>
    </form>

    <?php if (empty($dues)): ?>
        <p><?= __('dues.empty') ?></p>
    <?php else: ?>
        <div class="table-container">
            <table class="table bulk-table">
                <thead>
                    <tr>
                        <th style="width: 30px;"><input type="checkbox" class="bulk-select-all"></th>
                        <th><?= __("dues.person") ?></th>
                        <th><?= __("common.description") ?></th>
                        <th><?= __("common.amount") ?></th>
                        <th><?= __("common.paid") ?></th>
                        <th><?= __("common.due_date") ?></th>
                        <th><?= __("common.status") ?></th>
                        <th><?= __("common.actions") ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dues as $due): ?>
                        <?php
                        $isOverdue = $due['status'] !== 'paid' && $due['status'] !== 'cancelled'
                            && $due['due_date'] && $due['due_date'] < date('Y-m-d');
                        ?>
                        <tr>
                            <td><input type="checkbox" class="bulk-check" value="<?= $due['id'] ?>"></td>
                            <td>
                                <a href="/dues/<?= $due['id'] ?>"><strong><?= htmlspecialchars($due['person_name']) ?></strong></a>
                                <?php if ($due['person_phone']): ?>
                                    <br><small class="text-muted"><?= htmlspecialchars($due['person_phone']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($due['description'] ? mb_substr($due['description'], 0, 50) : '-') ?></td>
                            <td><strong><?= number_format($due['amount'], 2) ?> <?= $due['currency_code'] ?></strong></td>
                            <td><?= $due['paid_amount'] > 0 ? number_format($due['paid_amount'], 2) : '-' ?></td>
                            <td>
                                <?= $due['due_date'] ?? '-' ?>
                                <?php if ($isOverdue): ?>
                                    <br><span class="badge badge-urgent"><?= __("common.overdue") ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= $statusLabels[$due['status']] ?? $due['status'] ?></td>
                            <td class="flex gap-1">
                                <?php if ($due['status'] !== 'paid'): ?>
                                    <form method="POST" action="/dues/<?= $due['id'] ?>/mark-paid" style="display:inline;">
                                        <?= \App\Core\CSRF::field() ?>
                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('<?= __('common.confirm_receive') ?>')"><?= __('common.mark_paid') ?></button>
                                    </form>
                                <?php endif; ?>
                                <a href="/dues/<?= $due['id'] ?>" class="btn btn-sm btn-secondary"><?= __("common.view") ?></a>
                                <a href="/dues/<?= $due['id'] ?>/edit" class="btn btn-sm btn-secondary"><?= __("common.edit") ?></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php $bulkAction = '/bulk/dues'; require __DIR__ . '/../partials/bulk_actions.php'; ?>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
