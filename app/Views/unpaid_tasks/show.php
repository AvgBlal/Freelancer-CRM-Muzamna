<?php
$title = htmlspecialchars($task['title']);
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
        <h2 class="card-title"><?= htmlspecialchars($task['title']) ?></h2>
        <div class="flex gap-2">
            <a href="/unpaid-tasks/<?= $task['id'] ?>/edit" class="btn btn-secondary"><i class="fas fa-edit"></i> <?= __('common.edit') ?></a>
            <form method="POST" action="/unpaid-tasks/<?= $task['id'] ?>/delete" onsubmit="return confirm('<?= __('common.confirm_delete') ?>')" style="display:inline;">
                <?= \App\Core\CSRF::field() ?>
                <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> <?= __('common.delete') ?></button>
            </form>
            <a href="/unpaid-tasks" class="btn btn-secondary"><?= __("common.back") ?></a>
        </div>
    </div>

    <div class="mb-3">
        <span class="badge <?= $statusBadges[$task['status']] ?? 'badge-secondary' ?>"><?= $statusLabels[$task['status']] ?? $task['status'] ?></span>
    </div>

    <table class="table" style="width: auto;">
        <tr><th><i class="fas fa-user text-gray-400"></i> <?= __("common.client") ?>:</th><td><a href="/clients/<?= $task['client_id'] ?>"><?= htmlspecialchars($task['client_name']) ?></a></td></tr>
        <tr><th><i class="fas fa-clock text-gray-400"></i> <?= __('unpaid.hours') ?>:</th><td><?= number_format($task['hours'], 2) ?></td></tr>
        <tr><th><i class="fas fa-money-bill text-gray-400"></i> <?= __('unpaid.cost') ?>:</th><td class="ltr-input"><?= number_format($task['total_cost'], 2) ?> <?= $task['currency_code'] ?></td></tr>
        <tr><th><i class="fas fa-user-cog text-gray-400"></i> <?= __('unpaid.executor') ?>:</th><td><?= htmlspecialchars($task['assignee_name'] ?? '-') ?></td></tr>
        <?php if (!empty($task['description'])): ?>
        <tr><th><i class="fas fa-align-right text-gray-400"></i> <?= __('common.description') ?>:</th><td><?= nl2br(htmlspecialchars($task['description'])) ?></td></tr>
        <?php endif; ?>
        <?php if (!empty($task['attachment'])): ?>
        <tr><th><i class="fas fa-paperclip text-gray-400"></i> <?= __('safe.files') ?>:</th><td><a href="/<?= htmlspecialchars($task['attachment']) ?>" target="_blank" class="btn btn-sm btn-secondary"><i class="fas fa-download"></i> <?= __('common.view') ?></a></td></tr>
        <?php endif; ?>
        <tr><th><i class="fas fa-calendar text-gray-400"></i> <?= __("common.created_at") ?>:</th><td><?= $task['created_at'] ?></td></tr>
        <?php if ($task['updated_at'] !== $task['created_at']): ?>
        <tr><th><i class="fas fa-calendar-check text-gray-400"></i> <?= __("common.date") ?>:</th><td><?= $task['updated_at'] ?></td></tr>
        <?php endif; ?>
    </table>
</div>

<?php if ($task['status'] !== 'paid' && $task['status'] !== 'cancelled'): ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><?= __('common.status') ?></h3>
    </div>
    <div class="flex gap-2" style="flex-wrap: wrap;">
        <?php
        $transitions = [
            'pending' => ['quoted' => __('unpaid.status.quoted'), 'cancelled' => __('unpaid.status.cancelled')],
            'quoted' => ['invoiced' => __('unpaid.status.invoiced'), 'cancelled' => __('unpaid.status.cancelled')],
            'invoiced' => ['paid' => __('unpaid.status.paid'), 'cancelled' => __('unpaid.status.cancelled')],
        ];
        $available = $transitions[$task['status']] ?? [];
        foreach ($available as $newStatus => $label):
            $btnClass = match($newStatus) {
                'paid' => 'btn-success',
                'cancelled' => 'btn-danger',
                default => 'btn-primary',
            };
        ?>
            <form method="POST" action="/unpaid-tasks/<?= $task['id'] ?>/change-status" style="display:inline;">
                <?= \App\Core\CSRF::field() ?>
                <input type="hidden" name="status" value="<?= $newStatus ?>">
                <button type="submit" class="btn <?= $btnClass ?>"><?= $label ?></button>
            </form>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
