<?php $title = __('finance.title'); ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<?php
$categoryLabels = [
    'hosting' => __('expenses.cat.hosting'), 'software' => __('expenses.cat.software'), 'domains' => __('expenses.cat.domains'),
    'tools' => __('expenses.cat.tools'), 'subscriptions' => __('expenses.cat.subscriptions'), 'freelancer' => __('expenses.cat.freelancer'),
    'office' => __('expenses.cat.office'), 'marketing' => __('expenses.cat.marketing'), 'taxes' => __('expenses.cat.taxes'), 'other' => __('expenses.cat.other'),
];
require __DIR__ . '/../partials/service_types.php';
$typeLabels = $serviceTypeLabels;
$arabicMonths = [
    '01' => __('month.01'), '02' => __('month.02'), '03' => __('month.03'), '04' => __('month.04'),
    '05' => __('month.05'), '06' => __('month.06'), '07' => __('month.07'), '08' => __('month.08'),
    '09' => __('month.09'), '10' => __('month.10'), '11' => __('month.11'), '12' => __('month.12'),
];
?>

<!-- Overview Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value"><?php $currencyValues = $mrr; $currencyDecimals = 2; require __DIR__ . '/../partials/currency_stat.php'; ?></div>
        <div class="stat-label"><?= __("finance.mrr") ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?php $currencyValues = $dueStats['totalPendingByCurrency']; $currencyDecimals = 2; require __DIR__ . '/../partials/currency_stat.php'; ?></div>
        <div class="stat-label"><?= __("dashboard.pending_dues") ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?php $currencyValues = $expenseStats['totalPendingByCurrency']; $currencyDecimals = 2; require __DIR__ . '/../partials/currency_stat.php'; ?></div>
        <div class="stat-label"><?= __("dashboard.pending_expenses") ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?php $currencyValues = $totalActiveServicesValueByCurrency; $currencyDecimals = 2; require __DIR__ . '/../partials/currency_stat.php'; ?></div>
        <div class="stat-label"><?= __("finance.active_value") ?></div>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value"><?php $currencyValues = $totalPaidDuesByCurrency; $currencyDecimals = 2; require __DIR__ . '/../partials/currency_stat.php'; ?></div>
        <div class="stat-label"><?= __("finance.total_income") ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?php $currencyValues = $totalPaidExpensesByCurrency; $currencyDecimals = 2; require __DIR__ . '/../partials/currency_stat.php'; ?></div>
        <div class="stat-label"><?= __("finance.total_expenses") ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-value">
            <?php foreach ($netProfitByCurrency as $npc): if ((float)$npc['total'] == 0) continue; ?>
            <div style="white-space: nowrap; color: <?= (float)$npc['total'] >= 0 ? 'var(--success-color)' : 'var(--danger-color)' ?>">
                <?= number_format((float)$npc['total'], 2) ?> <small style="opacity:.7"><?= htmlspecialchars($npc['currency_code']) ?></small>
            </div>
            <?php endforeach; if (empty($netProfitByCurrency)): ?><div>0</div><?php endif; ?>
        </div>
        <div class="stat-label"><?= __("finance.net_profit") ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?php $currencyValues = $expenseStats['recurringMonthlyByCurrency']; $currencyDecimals = 2; require __DIR__ . '/../partials/currency_stat.php'; ?></div>
        <div class="stat-label"><?= __("finance.monthly_recurring") ?></div>
    </div>
</div>

<!-- Monthly Profit/Loss -->
<?php if (!empty($monthlyProfitLoss)): ?>
<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= __("finance.monthly_pl") ?></h2>
    </div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th><?= __("finance.month") ?></th>
                    <th><?= __("finance.income") ?></th>
                    <th><?= __("finance.expenses_col") ?></th>
                    <th><?= __("finance.net") ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (array_reverse($monthlyProfitLoss) as $row): ?>
                    <?php
                    $parts = explode('-', $row['month']);
                    $monthName = ($arabicMonths[$parts[1]] ?? $parts[1]) . ' ' . $parts[0];
                    ?>
                    <tr>
                        <td><strong><?= $monthName ?></strong></td>
                        <td style="color: var(--success-color);"><?= number_format($row['income'], 2) ?></td>
                        <td style="color: var(--danger-color);"><?= number_format($row['expenses'], 2) ?></td>
                        <td>
                            <strong style="color: <?= $row['profit'] >= 0 ? 'var(--success-color)' : 'var(--danger-color)' ?>">
                                <?= number_format($row['profit'], 2) ?>
                            </strong>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Revenue by Client -->
<?php if (!empty($revenueByClient)): ?>
<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= __("finance.by_client") ?></h2>
    </div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th><?= __("common.client") ?></th>
                    <th><?= __("finance.service_count") ?></th>
                    <th><?= __("finance.total_value") ?></th>
                    <th><?= __("finance.monthly_value") ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($revenueByClient as $row): ?>
                    <tr>
                        <td><a href="/clients/<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></a></td>
                        <td><?= $row['service_count'] ?></td>
                        <td><?= number_format($row['total_value'], 2) ?></td>
                        <td><strong><?= number_format($row['monthly_value'], 2) ?></strong></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Revenue by Service Type -->
<?php if (!empty($revenueByType)): ?>
<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= __("finance.by_type") ?></h2>
    </div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th><?= __("common.type") ?></th>
                    <th><?= __("finance.service_count") ?></th>
                    <th><?= __("finance.total_value") ?></th>
                    <th><?= __("finance.monthly_value") ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($revenueByType as $row): ?>
                    <tr>
                        <td><?= $typeLabels[$row['type']] ?? $row['type'] ?></td>
                        <td><?= $row['count'] ?></td>
                        <td><?= number_format($row['total_value'], 2) ?></td>
                        <td><strong><?= number_format($row['monthly_value'], 2) ?></strong></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Expenses by Category -->
<?php if (!empty($expensesByCategory)): ?>
<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= __("finance.by_category") ?></h2>
    </div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th><?= __("expenses.category") ?></th>
                    <th><?= __("finance.expense_count") ?></th>
                    <th><?= __("common.total") ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($expensesByCategory as $row): ?>
                    <tr>
                        <td><?= $categoryLabels[$row['category']] ?? $row['category'] ?></td>
                        <td><?= $row['count'] ?></td>
                        <td><strong><?= number_format($row['total'], 2) ?></strong></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
