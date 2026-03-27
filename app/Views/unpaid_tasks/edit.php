<?php
$title = __('unpaid.edit');
$statusLabels = [
    'pending' => __('unpaid.status.pending'), 'quoted' => __('unpaid.status.quoted'),
    'invoiced' => __('unpaid.status.invoiced'), 'paid' => __('unpaid.status.paid'), 'cancelled' => __('unpaid.status.cancelled'),
];
?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= __('common.edit') ?>: <?= htmlspecialchars($task['title']) ?></h2>
        <a href="/unpaid-tasks/<?= $task['id'] ?>" class="btn btn-secondary"><?= __("common.back") ?></a>
    </div>

    <form method="POST" action="/unpaid-tasks/<?= $task['id'] ?>" enctype="multipart/form-data">
        <?= $csrf ?>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="client_id"><?= __('common.client') ?> *</label>
                <select id="client_id" name="client_id" class="form-select" required>
                    <option value=""><?= __('common.all_clients') ?></option>
                    <?php foreach ($clients as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= ($_SESSION['old']['client_id'] ?? $task['client_id']) == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="title"><?= __('unpaid.task_title') ?> *</label>
                <input type="text" id="title" name="title" class="form-input" value="<?= htmlspecialchars($_SESSION['old']['title'] ?? $task['title']) ?>" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="hours"><?= __('unpaid.hours') ?> *</label>
                <input type="number" id="hours" name="hours" class="form-input ltr-input" value="<?= $_SESSION['old']['hours'] ?? $task['hours'] ?>" step="0.25" min="0.25" max="100" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="total_cost"><?= __('unpaid.cost') ?> *</label>
                <input type="number" id="total_cost" name="total_cost" class="form-input ltr-input" value="<?= $_SESSION['old']['total_cost'] ?? $task['total_cost'] ?>" step="0.01" min="0" required>
            </div>

            <div class="form-group">
                <label class="form-label"><?= __('common.currency') ?></label>
                <?php $currencyFieldName = 'currency_code'; $currencySelected = $_SESSION['old']['currency_code'] ?? $task['currency_code'] ?? 'EGP'; ?>
                <?php require __DIR__ . '/../partials/currency_select.php'; ?>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="assigned_to"><?= __('unpaid.executor') ?></label>
                <select id="assigned_to" name="assigned_to" class="form-select">
                    <option value=""><?= __("common.unassigned") ?></option>
                    <?php foreach ($users as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= ($_SESSION['old']['assigned_to'] ?? $task['assigned_to']) == $u['id'] ? 'selected' : '' ?>><?= htmlspecialchars($u['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="status"><?= __('common.status') ?></label>
                <select id="status" name="status" class="form-select">
                    <?php foreach ($statusLabels as $key => $label): ?>
                        <option value="<?= $key ?>" <?= ($_SESSION['old']['status'] ?? $task['status']) === $key ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="description"><?= __('common.description') ?></label>
            <textarea id="description" name="description" class="form-textarea" rows="3"><?= htmlspecialchars($_SESSION['old']['description'] ?? $task['description'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label class="form-label" for="attachment"><?= __('safe.files') ?></label>
            <?php if (!empty($task['attachment'])): ?>
                <div class="mb-2">
                    <a href="/<?= htmlspecialchars($task['attachment']) ?>" target="_blank" class="btn btn-sm btn-secondary">
                        <i class="fas fa-download"></i> <?= __('common.view') ?>
                    </a>
                    <label class="checkbox-label" style="display: inline-block; margin-inline-start: 10px;">
                        <input type="checkbox" name="remove_attachment" value="1"> <?= __("common.delete") ?>
                    </label>
                </div>
            <?php endif; ?>
            <input type="file" id="attachment" name="attachment" class="form-input" accept=".pdf,.jpg,.jpeg,.png,.gif,.doc,.docx,.xls,.xlsx,.zip,.txt">
        </div>

        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary"><?= __("common.save") ?></button>
            <a href="/unpaid-tasks/<?= $task['id'] ?>" class="btn btn-secondary"><?= __("common.cancel") ?></a>
        </div>
    </form>
</div>

<?php unset($_SESSION['old']); ?>
<?php require __DIR__ . '/../layout/footer.php'; ?>
