<?php
$title = __('unpaid.title');
$statusLabels = [
    'pending' => __('unpaid.status.pending'), 'quoted' => __('unpaid.status.quoted'),
    'invoiced' => __('unpaid.status.invoiced'), 'paid' => __('unpaid.status.paid'), 'cancelled' => __('unpaid.status.cancelled'),
];
$statusBadges = [
    'pending' => 'badge-warning', 'quoted' => 'badge-info',
    'invoiced' => 'badge-primary', 'paid' => 'badge-success', 'cancelled' => 'badge-secondary',
];
?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><i class="fas fa-exclamation-triangle text-yellow-500"></i> <?= __('unpaid.title') ?></h2>
        <a href="/unpaid-tasks/create" class="btn btn-primary"><i class="fas fa-plus"></i> <?= __('common.add') ?></a>
    </div>

    <form method="GET" class="mb-3">
        <div class="flex gap-2" style="flex-wrap: wrap;">
            <input type="text" name="search" placeholder="<?= __('common.search_placeholder') ?>" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" class="form-input" style="width: 200px;">
            <select name="status" class="form-select" style="width: 180px;">
                <option value=""><?= __("common.all_statuses") ?></option>
                <?php foreach ($statusLabels as $key => $label): ?>
                    <option value="<?= $key ?>" <?= ($_GET['status'] ?? '') === $key ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
            <select name="client_id" class="form-select" style="width: 200px;">
                <option value=""><?= __("common.all_clients") ?></option>
                <?php foreach ($clients as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= ($_GET['client_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-secondary"><?= __("common.filter") ?></button>
        </div>
    </form>

    <?php if (empty($tasks)): ?>
        <p class="text-muted"><?= __('unpaid.empty') ?></p>
    <?php else: ?>
        <div class="table-container">
            <table class="table bulk-table">
                <thead>
                    <tr>
                        <th style="width: 30px;"><input type="checkbox" class="bulk-select-all"></th>
                        <th><?= __("unpaid.task") ?></th>
                        <th><?= __("common.client") ?></th>
                        <th><?= __("unpaid.hours") ?></th>
                        <th><?= __("unpaid.cost") ?></th>
                        <th><?= __("unpaid.executor") ?></th>
                        <th><?= __("common.status") ?></th>
                        <th><?= __("common.date") ?></th>
                        <th><?= __("common.actions") ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tasks as $t): ?>
                    <tr>
                        <td><input type="checkbox" class="bulk-check" value="<?= $t['id'] ?>"></td>
                        <td><a href="/unpaid-tasks/<?= $t['id'] ?>"><?= htmlspecialchars($t['title']) ?></a></td>
                        <td><a href="/clients/<?= $t['client_id'] ?>"><?= htmlspecialchars($t['client_name']) ?></a></td>
                        <td><?= number_format($t['hours'], 1) ?></td>
                        <td class="ltr-input"><?= number_format($t['total_cost'], 2) ?> <?= $t['currency_code'] ?></td>
                        <td><?= htmlspecialchars($t['assignee_name'] ?? '-') ?></td>
                        <td><span class="badge <?= $statusBadges[$t['status']] ?? 'badge-secondary' ?>"><?= $statusLabels[$t['status']] ?? $t['status'] ?></span></td>
                        <td><?= date('Y-m-d', strtotime($t['created_at'])) ?></td>
                        <td>
                            <div class="flex gap-1">
                                <a href="/unpaid-tasks/<?= $t['id'] ?>" class="btn btn-sm btn-secondary"><?= __("common.view") ?></a>
                                <a href="/unpaid-tasks/<?= $t['id'] ?>/edit" class="btn btn-sm btn-secondary"><?= __("common.edit") ?></a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php $bulkAction = '/bulk/unpaid-tasks'; require __DIR__ . '/../partials/bulk_actions.php'; ?>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
