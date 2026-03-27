<?php $title = __('logs.notifications'); ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= __("logs.notifications") ?></h2>
        <div class="flex gap-2">
            <a href="/logs" class="btn btn-secondary"><?= __("logs.title") ?></a>
            <a href="/logs/cron" class="btn btn-secondary"><?= __("logs.cron") ?></a>
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" class="mb-3">
        <div class="flex gap-2">
            <div class="form-group">
                <label><?= __('clients.channel') ?></label>
                <select name="channel" class="form-control">
                    <option value=""><?= __("common.all") ?></option>
                    <option value="email" <?= ($_GET['channel'] ?? '') === 'email' ? 'selected' : '' ?>><?= __('common.email') ?></option>
                    <option value="whatsapp" <?= ($_GET['channel'] ?? '') === 'whatsapp' ? 'selected' : '' ?>>WhatsApp</option>
                </select>
            </div>
            <div class="form-group">
                <label><?= __('common.status') ?></label>
                <select name="status" class="form-control">
                    <option value=""><?= __("common.all") ?></option>
                    <option value="sent" <?= ($_GET['status'] ?? '') === 'sent' ? 'selected' : '' ?>><?= __('logs.status_sent') ?></option>
                    <option value="fail" <?= ($_GET['status'] ?? '') === 'fail' ? 'selected' : '' ?>><?= __('logs.status_fail') ?></option>
                </select>
            </div>
            <div class="form-group" style="display: flex; align-items: flex-end; gap: 0.5rem;">
                <button type="submit" class="btn btn-primary"><?= __("common.search") ?></button>
                <a href="/logs/notifications" class="btn btn-secondary"><?= __("common.clear") ?></a>
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
                    <th><?= __("clients.channel") ?></th>
                    <th><?= __("logs.entity") ?></th>
                    <th><?= __("common.description") ?></th>
                    <th><?= __("common.status") ?></th>
                    <th><?= __("common.status") ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td style="white-space: nowrap; font-size: 0.85em;"><?= htmlspecialchars($log['sent_at'] ?? $log['created_at'] ?? '-') ?></td>
                    <td>
                        <?php if ($log['channel'] === 'email'): ?>
                            <span class="badge badge-info"><?= __("common.email") ?></span>
                        <?php elseif ($log['channel'] === 'whatsapp'): ?>
                            <span class="badge badge-success">WhatsApp</span>
                        <?php else: ?>
                            <span class="badge badge-secondary"><?= htmlspecialchars($log['channel']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($log['target'] ?? '-') ?></td>
                    <td style="max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?= htmlspecialchars($log['payload_summary'] ?? '-') ?></td>
                    <td>
                        <?php if ($log['status'] === 'sent'): ?>
                            <span class="badge badge-success"><?= __("common.active") ?></span>
                        <?php else: ?>
                            <span class="badge badge-urgent"><?= __("common.inactive") ?></span>
                        <?php endif; ?>
                    </td>
                    <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: #c62828;"><?= htmlspecialchars($log['error_message'] ?? '-') ?></td>
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
