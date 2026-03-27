<?php $title = __('logs.title'); ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<?php
$actionLabels = [
    'create' => __('action.create'),
    'update' => __('action.update'),
    'delete' => __('action.delete'),
    'login' => __('action.login'),
    'logout' => __('action.logout'),
    'auto_expire' => __('action.auto_expire'),
    'cron_run' => __('action.cron_run'),
    'status_change' => __('action.status_change'),
    'renew' => __('action.renew'),
    'mark_paid' => __('action.mark_paid'),
    'toggle_pin' => __('action.toggle_pin'),
    'archive' => __('action.archive'),
    'restore' => __('action.restore'),
];
$entityLabels = [
    'client' => __('entity.client'),
    'service' => __('entity.service'),
    'project' => __('entity.project'),
    'task' => __('entity.task'),
    'due' => __('entity.due'),
    'expense' => __('entity.expense'),
    'note' => __('entity.note'),
    'user' => __('entity.user'),
    'settings' => __('entity.settings'),
    'system' => __('entity.system'),
];
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= __("logs.title") ?></h2>
        <div class="flex gap-2">
            <a href="/logs/notifications" class="btn btn-secondary"><?= __("logs.notifications") ?></a>
            <a href="/logs/cron" class="btn btn-secondary"><?= __("logs.cron") ?></a>
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" class="mb-3">
        <div class="form-grid" style="grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));">
            <div class="form-group">
                <label><?= __("logs.action") ?></label>
                <select name="action" class="form-control">
                    <option value=""><?= __("common.all") ?></option>
                    <?php foreach ($actions as $a): ?>
                        <option value="<?= htmlspecialchars($a['action']) ?>" <?= ($_GET['action'] ?? '') === $a['action'] ? 'selected' : '' ?>>
                            <?= $actionLabels[$a['action']] ?? $a['action'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label><?= __("logs.entity_type") ?></label>
                <select name="entity_type" class="form-control">
                    <option value=""><?= __("common.all") ?></option>
                    <?php foreach ($entityTypes as $et): ?>
                        <option value="<?= htmlspecialchars($et['entity_type']) ?>" <?= ($_GET['entity_type'] ?? '') === $et['entity_type'] ? 'selected' : '' ?>>
                            <?= $entityLabels[$et['entity_type']] ?? $et['entity_type'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label><?= __("common.search") ?></label>
                <input type="text" name="search" class="form-control" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" placeholder="<?= __('common.search_placeholder') ?>">
            </div>
            <div class="form-group">
                <label><?= __("logs.from_date") ?></label>
                <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label><?= __("logs.to_date") ?></label>
                <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>">
            </div>
            <div class="form-group" style="display: flex; align-items: flex-end; gap: 0.5rem;">
                <button type="submit" class="btn btn-primary"><?= __("common.search") ?></button>
                <a href="/logs" class="btn btn-secondary"><?= __("common.clear") ?></a>
            </div>
        </div>
    </form>

    <p class="text-muted mb-2"><?= __('logs.total', ['count' => $total]) ?></p>

    <?php if (empty($logs)): ?>
        <p class="text-muted" style="text-align: center; padding: 2rem;"><?= __("logs.empty") ?></p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th><?= __("common.date") ?></th>
                    <th><?= __("logs.user") ?></th>
                    <th><?= __("logs.action") ?></th>
                    <th><?= __("common.type") ?></th>
                    <th><?= __("logs.entity") ?></th>
                    <th><?= __("common.details") ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td style="white-space: nowrap; font-size: 0.85em;"><?= htmlspecialchars($log['created_at']) ?></td>
                    <td>
                        <?= htmlspecialchars($log['user_name'] ?? __('entity.system')) ?>
                        <?php if (empty($log['user_id'])): ?>
                            <span class="badge badge-secondary"><?= __("common.automatic") ?></span>
                        <?php endif; ?>
                    </td>
                    <td><span class="badge badge-info"><?= $actionLabels[$log['action']] ?? htmlspecialchars($log['action']) ?></span></td>
                    <td><?= $entityLabels[$log['entity_type']] ?? htmlspecialchars($log['entity_type']) ?></td>
                    <td>
                        <?php if ($log['entity_title']): ?>
                            <?= htmlspecialchars($log['entity_title']) ?>
                        <?php elseif ($log['entity_id']): ?>
                            #<?= $log['entity_id'] ?>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?= htmlspecialchars($log['details'] ?? '-') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="flex gap-1" style="justify-content: center; margin-top: 1rem;">
            <?php
                $queryParams = $_GET;
                for ($i = 1; $i <= $totalPages; $i++):
                    $queryParams['page'] = $i;
            ?>
                <?php if ($i === $page): ?>
                    <span class="btn btn-primary" style="min-width: 2.5rem;"><?= $i ?></span>
                <?php else: ?>
                    <a href="?<?= http_build_query($queryParams) ?>" class="btn btn-secondary" style="min-width: 2.5rem;"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
