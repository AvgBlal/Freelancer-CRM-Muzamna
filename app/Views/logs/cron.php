<?php $title = __('logs.cron'); ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= __("logs.cron") ?></h2>
        <div class="flex gap-2">
            <a href="/logs" class="btn btn-secondary"><?= __("logs.title") ?></a>
            <a href="/logs/notifications" class="btn btn-secondary"><?= __("logs.notifications") ?></a>
        </div>
    </div>

    <p class="text-muted mb-2"><?= __('logs.total', ['count' => $total]) ?></p>

    <?php if (empty($runs)): ?>
        <p class="text-muted" style="text-align: center; padding: 2rem;"><?= __('logs.empty') ?></p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th><?= __("common.date") ?></th>
                    <th><?= __("common.status") ?></th>
                    <th><?= __("common.description") ?></th>
                    <th><?= __("common.status") ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($runs as $run): ?>
                <tr>
                    <td style="white-space: nowrap; font-size: 0.85em;"><?= htmlspecialchars($run['run_at'] ?? $run['created_at'] ?? '-') ?></td>
                    <td>
                        <?php if ($run['status'] === 'success'): ?>
                            <span class="badge badge-success"><?= __("common.active") ?></span>
                        <?php else: ?>
                            <span class="badge badge-urgent"><?= __("common.inactive") ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($run['summary'] ?? '-') ?></td>
                    <td style="color: #c62828;"><?= htmlspecialchars($run['error_message'] ?? '-') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="flex gap-1" style="justify-content: center; margin-top: 1rem;">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php if ($i === $page): ?>
                    <span class="btn btn-primary" style="min-width: 2.5rem;"><?= $i ?></span>
                <?php else: ?>
                    <a href="?page=<?= $i ?>" class="btn btn-secondary" style="min-width: 2.5rem;"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
