<?php $title = __('services.title'); ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<?php
require __DIR__ . '/../partials/service_types.php';
$typeLabels = $serviceTypeLabels;
$currentSort = $_GET['sort_by'] ?? 'end_date';
$currentDir = $_GET['sort_dir'] ?? 'asc';

$sortUrl = function(string $column) use ($currentSort, $currentDir) {
    $params = $_GET;
    $params['sort_by'] = $column;
    $params['sort_dir'] = ($currentSort === $column && $currentDir === 'asc') ? 'desc' : 'asc';
    return '?' . http_build_query($params);
};
$sortIcon = function(string $column) use ($currentSort, $currentDir) {
    if ($currentSort !== $column) return '';
    return $currentDir === 'asc' ? ' ▲' : ' ▼';
};
?>

<!-- Stats Overview -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value"><?= $overviewStats['active'] ?></div>
        <div class="stat-label"><?= __('services.active') ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?php $currencyValues = $overviewStats['totalValueByCurrency']; require __DIR__ . '/../partials/currency_stat.php'; ?></div>
        <div class="stat-label"><?= __("finance.active_value") ?></div>
    </div>
    <div class="stat-card <?= $overviewStats['expiringCount'] > 0 ? 'stat-card-warning' : '' ?>">
        <div class="stat-value"><?= $overviewStats['expiringCount'] ?></div>
        <div class="stat-label"><?= __('services.expiring_30') ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $overviewStats['expired'] ?></div>
        <div class="stat-label"><?= __('services.expired') ?></div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= __('services.title') ?> (<?= $totalCount ?>)</h2>
        <div class="flex gap-2">
            <div class="dropdown" style="position:relative;display:inline-block;">
                <button type="button" onclick="this.nextElementSibling.classList.toggle('hidden')" class="btn btn-secondary"><i class="fas fa-download"></i> <?= __('common.export') ?></button>
                <div class="hidden" style="position:absolute;inset-inline-start:0;top:100%;background:white;border:1px solid #e2e8f0;border-radius:6px;box-shadow:0 4px 12px rgba(0,0,0,.15);z-index:50;min-width:160px;margin-top:4px;">
                    <a href="/export/services" class="dropdown-item" style="display:block;padding:8px 16px;text-decoration:none;color:#333;white-space:nowrap;"><i class="fas fa-file-csv text-green-600"></i> <?= __('common.export_csv') ?></a>
                    <a href="/export/services/pdf" class="dropdown-item" style="display:block;padding:8px 16px;text-decoration:none;color:#333;white-space:nowrap;"><i class="fas fa-file-pdf text-red-600"></i> <?= __('common.export_pdf') ?></a>
                </div>
            </div>
            <a href="/services/create" class="btn btn-primary"><?= __('services.new') ?></a>
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" class="mb-3">
        <div class="flex gap-2" style="flex-wrap: wrap; align-items: flex-end;">
            <div class="form-group" style="margin-bottom: 0;">
                <input type="text" name="search" class="form-input" placeholder="<?= __('common.search_placeholder') ?>" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" style="width: 200px;">
            </div>

            <select name="status" class="form-select" style="width: 130px;">
                <option value=""><?= __("common.all_statuses") ?></option>
                <option value="active" <?= ($_GET['status'] ?? '') === 'active' ? 'selected' : '' ?>><?= __('services.status.active') ?></option>
                <option value="expired" <?= ($_GET['status'] ?? '') === 'expired' ? 'selected' : '' ?>><?= __('services.status.expired') ?></option>
                <option value="paused" <?= ($_GET['status'] ?? '') === 'paused' ? 'selected' : '' ?>><?= __('services.status.paused') ?></option>
                <option value="cancelled" <?= ($_GET['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>><?= __('services.status.cancelled') ?></option>
            </select>

            <select name="type" class="form-select" style="width: 130px;">
                <option value=""><?= __("common.all_types") ?></option>
                <?php foreach ($typeLabels as $key => $label): ?>
                    <option value="<?= $key ?>" <?= ($_GET['type'] ?? '') === $key ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>

            <select name="is_personal" class="form-select" style="width: 130px;">
                <option value=""><?= __("common.all") ?></option>
                <option value="0" <?= ($_GET['is_personal'] ?? '') === '0' ? 'selected' : '' ?>><?= __("common.work") ?></option>
                <option value="1" <?= ($_GET['is_personal'] ?? '') === '1' ? 'selected' : '' ?>><?= __("common.personal") ?></option>
            </select>

            <select name="client_id" class="form-select" style="width: 180px;">
                <option value=""><?= __("common.all_clients") ?></option>
                <?php foreach ($clients as $client): ?>
                    <option value="<?= $client['id'] ?>" <?= ($_GET['client_id'] ?? '') == $client['id'] ? 'selected' : '' ?>><?= htmlspecialchars($client['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <select name="billing_cycle" class="form-select" style="width: 130px;">
                <option value=""><?= __("common.all_cycles") ?></option>
                <option value="monthly" <?= ($_GET['billing_cycle'] ?? '') === 'monthly' ? 'selected' : '' ?>><?= __('services.cycle.monthly') ?></option>
                <option value="yearly" <?= ($_GET['billing_cycle'] ?? '') === 'yearly' ? 'selected' : '' ?>><?= __('services.cycle.yearly') ?></option>
                <option value="one_time" <?= ($_GET['billing_cycle'] ?? '') === 'one_time' ? 'selected' : '' ?>><?= __('services.cycle.one_time') ?></option>
            </select>

            <input type="hidden" name="sort_by" value="<?= htmlspecialchars($currentSort) ?>">
            <input type="hidden" name="sort_dir" value="<?= htmlspecialchars($currentDir) ?>">

            <button type="submit" class="btn btn-secondary"><?= __("common.filter") ?></button>
            <?php if (!empty($_GET['search']) || !empty($_GET['status']) || !empty($_GET['type']) || ($_GET['is_personal'] ?? '') !== '' || !empty($_GET['client_id']) || !empty($_GET['billing_cycle'])): ?>
                <a href="/services" class="btn btn-secondary"><?= __("common.clear") ?></a>
            <?php endif; ?>
        </div>
    </form>

    <?php if (empty($services)): ?>
        <p><?= __("services.empty") ?></p>
    <?php else: ?>
        <div class="table-container">
            <table class="table bulk-table">
                <thead>
                    <tr>
                        <th style="width: 30px;"><input type="checkbox" class="bulk-select-all"></th>
                        <th><a href="<?= $sortUrl('title') ?>" class="sort-link"><?= __('services.service') ?><?= $sortIcon('title') ?></a></th>
                        <th><?= __("common.client") ?></th>
                        <th><a href="<?= $sortUrl('type') ?>" class="sort-link"><?= __('common.type') ?><?= $sortIcon('type') ?></a></th>
                        <th><a href="<?= $sortUrl('status') ?>" class="sort-link"><?= __('common.status') ?><?= $sortIcon('status') ?></a></th>
                        <th><a href="<?= $sortUrl('end_date') ?>" class="sort-link"><?= __('services.end_date') ?><?= $sortIcon('end_date') ?></a></th>
                        <th><a href="<?= $sortUrl('price') ?>" class="sort-link"><?= __('services.price') ?><?= $sortIcon('price') ?></a></th>
                        <th><?= __("common.actions") ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($services as $service): ?>
                        <tr>
                            <td><input type="checkbox" class="bulk-check" value="<?= $service['id'] ?>"></td>
                            <td>
                                <a href="/services/<?= $service['id'] ?>"><?= htmlspecialchars($service['title']) ?></a>
                                <?php if (!empty($service['is_personal'])): ?>
                                    <span class="badge badge-primary"><?= __("common.personal") ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($service['client_names'] ?? '-') ?></td>
                            <td><?= $typeLabels[$service['type']] ?? $service['type'] ?></td>
                            <td>
                                <?php
                                $statusLabels = [
                                    'active' => '<span class="badge badge-success">' . __('services.status.active') . '</span>',
                                    'expired' => '<span class="badge badge-urgent">' . __('services.status.expired') . '</span>',
                                    'paused' => '<span class="badge badge-warning">' . __('services.status.paused') . '</span>',
                                    'cancelled' => '<span class="badge badge-info">' . __('services.status.cancelled') . '</span>',
                                ];
                                echo $statusLabels[$service['status']] ?? htmlspecialchars($service['status']);
                                ?>
                            </td>
                            <td>
                                <?= $service['end_date'] ?>
                                <?php if ($service['status'] === 'active' && !empty($service['end_date']) && $service['end_date'] < date('Y-m-d')): ?>
                                    <br><span class="badge badge-urgent"><?= __('services.renewal_overdue') ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= $service['price_amount'] ? number_format((float)$service['price_amount'], 0) . ' ' . $service['currency_code'] : '-' ?></td>
                            <td class="flex gap-1">
                                <a href="/services/<?= $service['id'] ?>" class="btn btn-sm btn-secondary"><?= __("common.view") ?></a>
                                <a href="/services/<?= $service['id'] ?>/edit" class="btn btn-sm btn-secondary"><?= __("common.edit") ?></a>
                                <?php if ($service['status'] === 'active'): ?>
                                    <form method="POST" action="/services/<?= $service['id'] ?>/change-status" style="display:inline;">
                                        <?= \App\Core\CSRF::field() ?>
                                        <input type="hidden" name="status" value="expired">
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('<?= __('services.confirm_expire') ?>')"><?= __('services.expire_btn') ?></button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="flex gap-1 mt-2" style="justify-content: center;">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php
                    $pParams = $_GET;
                    $pParams['page'] = $i;
                    ?>
                    <a href="?<?= http_build_query($pParams) ?>"
                       class="btn btn-sm <?= $i === $page ? 'btn-primary' : 'btn-secondary' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>

        <?php
        $bulkAction = '/bulk/services';
        $bulkOptions = [
            'active' => __('services.status.active'),
            'paused' => __('services.status.paused'),
            'cancelled' => __('services.status.cancelled'),
        ];
        require __DIR__ . '/../partials/bulk_actions.php';
        ?>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
