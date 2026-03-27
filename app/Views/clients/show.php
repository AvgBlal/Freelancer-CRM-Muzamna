
<?php $title = htmlspecialchars($client['name']); ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><i class="fas fa-user text-blue-500"></i> <?= htmlspecialchars($client['name']) ?></h2>
        <div class="flex gap-2">
            <a href="/clients/<?= $client['id'] ?>/edit" class="btn btn-secondary"><i class="fas fa-edit"></i> <?= __('common.edit') ?></a>
            <form method="POST" action="/clients/<?= $client['id'] ?>/delete" onsubmit="return confirm('<?= __('common.confirm_delete') ?>')" style="display:inline;">
                <?= \App\Core\CSRF::field() ?>
                <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> <?= __('common.delete') ?></button>
            </form>
        </div>
    </div>

    <div class="mb-3">
        <span class="badge badge-info"><?= $client['type'] === 'individual' ? __('clients.individual') : __('clients.company') ?></span>
        <?php foreach ($tags as $tag): ?>
            <span class="badge badge-success"><?= htmlspecialchars($tag['name']) ?></span>
        <?php endforeach; ?>
    </div>

    <table class="table" style="width: auto;">
        <tr><th><i class="fas fa-phone text-gray-400"></i> <?= __('common.phone') ?>:</th><td><?= htmlspecialchars($client['phone'] ?? '-') ?></td></tr>
        <tr><th><i class="fas fa-envelope text-gray-400"></i> <?= __('common.email') ?>:</th><td><?= htmlspecialchars($client['email'] ?? '-') ?></td></tr>
        <tr><th><i class="fas fa-globe text-gray-400"></i> <?= __('clients.website') ?>:</th><td><?= $client['website'] ? '<a href="' . htmlspecialchars($client['website']) . '" target="_blank">' . htmlspecialchars($client['website']) . '</a>' : '-' ?></td></tr>
        <tr><th><i class="fas fa-map-marker-alt text-gray-400"></i> <?= __('clients.country') ?>:</th><td><?= htmlspecialchars($client['city'] ?? '') ?><?= !empty($client['city']) && !empty($client['country']) ? ', ' : '' ?><?= htmlspecialchars($client['country'] ?? '') ?></td></tr>
        <tr><th><i class="fas fa-clock text-gray-400"></i> <?= __('clients.timezone') ?>:</th><td><?= htmlspecialchars($client['timezone'] ?? 'Africa/Cairo') ?></td></tr>
        <tr><th><i class="fas fa-comments text-gray-400"></i> <?= __('clients.channel') ?>:</th><td><?= $client['preferred_channel'] ?? '-' ?></td></tr>
        <?php if (!empty($client['notes'])): ?>
        <tr><th><i class="fas fa-sticky-note text-gray-400"></i> <?= __('common.notes') ?>:</th><td><?= nl2br(htmlspecialchars($client['notes'])) ?></td></tr>
        <?php endif; ?>
    </table>
</div>

<?php if (!empty($contacts)): ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-address-book text-blue-500"></i> (<?= count($contacts) ?>)</h3>
    </div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th><i class="fas fa-user"></i> <?= __('common.name') ?></th>
                    <th><i class="fas fa-briefcase"></i> <?= __('common.title') ?></th>
                    <th><i class="fas fa-envelope"></i> <?= __('common.email') ?></th>
                    <th><i class="fas fa-phone"></i> <?= __('common.phone') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contacts as $contact): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($contact['name']) ?></strong></td>
                    <td><?= htmlspecialchars($contact['job_title'] ?? '-') ?></td>
                    <td class="ltr-input"><?= htmlspecialchars($contact['email'] ?? '-') ?></td>
                    <td class="ltr-input"><?= htmlspecialchars($contact['phone'] ?? '-') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($services)): ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-server text-blue-500"></i> <?= __('services.title') ?></h3>
    </div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr><th><?= __('services.service') ?></th><th><?= __('common.status') ?></th><th><?= __('services.end_date') ?></th></tr>
            </thead>
            <tbody>
                <?php foreach ($services as $service): ?>
                <tr>
                    <td><a href="/services/<?= $service['id'] ?>"><?= htmlspecialchars($service['title']) ?></a></td>
                    <td><?= $service['status'] ?></td>
                    <td><?= $service['end_date'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($projects)): ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-project-diagram text-blue-500"></i> <?= __('projects.title') ?></h3>
    </div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr><th><?= __('projects.project') ?></th><th><?= __('common.status') ?></th><th><?= __('common.progress') ?></th></tr>
            </thead>
            <tbody>
                <?php foreach ($projects as $project): ?>
                <tr>
                    <td><a href="/projects/<?= $project['id'] ?>"><?= htmlspecialchars($project['title']) ?></a></td>
                    <td><?= $project['status'] ?></td>
                    <td><?= $project['progress'] ?>%</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-exclamation-triangle text-yellow-500"></i> <?= __('unpaid.title') ?> (<?= count($unpaidTasks) ?>)</h3>
        <a href="/unpaid-tasks/create?client_id=<?= $client['id'] ?>" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> <?= __('common.add') ?></a>
    </div>

    <?php if ($unpaidTaskStats['pendingCount'] > 0): ?>
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-lg p-3 mb-3">
            <i class="fas fa-exclamation-circle"></i>
            <?= $unpaidTaskStats['pendingCount'] ?> -
            <?= number_format($unpaidTaskStats['totalHours'], 1) ?> h -
            <?= number_format($unpaidTaskStats['totalPending'], 2) ?>
        </div>
    <?php endif; ?>

    <?php if (empty($unpaidTasks)): ?>
        <p class="text-muted"><?= __('unpaid.empty') ?></p>
    <?php else: ?>
        <?php
        $statusLabelsClient = [
            'pending' => __('unpaid.status.pending'), 'quoted' => __('unpaid.status.quoted'),
            'invoiced' => __('unpaid.status.invoiced'), 'paid' => __('unpaid.status.paid'), 'cancelled' => __('unpaid.status.cancelled'),
        ];
        $statusBadgesClient = [
            'pending' => 'badge-warning', 'quoted' => 'badge-info',
            'invoiced' => 'badge-primary', 'paid' => 'badge-success', 'cancelled' => 'badge-secondary',
        ];
        ?>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th><?= __('unpaid.task') ?></th>
                        <th><?= __('unpaid.hours') ?></th>
                        <th><?= __('unpaid.cost') ?></th>
                        <th><?= __('unpaid.executor') ?></th>
                        <th><?= __('common.status') ?></th>
                        <th><?= __('common.date') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($unpaidTasks as $ut): ?>
                    <tr>
                        <td><a href="/unpaid-tasks/<?= $ut['id'] ?>"><?= htmlspecialchars($ut['title']) ?></a></td>
                        <td><?= number_format($ut['hours'], 1) ?></td>
                        <td class="ltr-input"><?= number_format($ut['total_cost'], 2) ?> <?= $ut['currency_code'] ?></td>
                        <td><?= htmlspecialchars($ut['assignee_name'] ?? '-') ?></td>
                        <td><span class="badge <?= $statusBadgesClient[$ut['status']] ?? 'badge-secondary' ?>"><?= $statusLabelsClient[$ut['status']] ?? $ut['status'] ?></span></td>
                        <td><?= date('Y-m-d', strtotime($ut['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Quotations Section -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-file-invoice-dollar text-blue-500"></i> <?= __('quotations.title') ?> (<?= count($quotations) ?>)</h3>
        <a href="/quotations/create?client_id=<?= $client['id'] ?>" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> <?= __('common.add') ?></a>
    </div>

    <?php if (empty($quotations)): ?>
        <p class="text-muted"><?= __('safe.empty') ?></p>
    <?php else: ?>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th><?= __('common.title') ?></th>
                        <th><?= __('safe.files') ?></th>
                        <th><?= __('clients.tags') ?></th>
                        <th><?= __('common.date') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($quotations as $q): ?>
                    <tr>
                        <td><a href="/quotations/<?= $q['id'] ?>"><?= htmlspecialchars($q['title']) ?></a></td>
                        <td>
                            <?php $fc = (int)($q['file_count'] ?? 0); ?>
                            <?php if ($fc > 0): ?>
                                <span class="text-green-500"><i class="fas fa-paperclip"></i> <?= $fc ?></span>
                            <?php else: ?>
                                <span class="text-gray-300">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($q['tags'])): ?>
                                <?php foreach (explode(',', $q['tags']) as $tag): ?>
                                    <span class="badge badge-secondary"><?= htmlspecialchars(trim($tag)) ?></span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </td>
                        <td><?= date('Y-m-d', strtotime($q['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Invoices Section -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-file-invoice text-green-500"></i> <?= __('invoices.title') ?> (<?= count($invoices) ?>)</h3>
        <a href="/invoices/create?client_id=<?= $client['id'] ?>" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> <?= __('common.add') ?></a>
    </div>

    <?php if (empty($invoices)): ?>
        <p class="text-muted"><?= __('safe.empty') ?></p>
    <?php else: ?>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th><?= __('common.title') ?></th>
                        <th><?= __('safe.files') ?></th>
                        <th><?= __('clients.tags') ?></th>
                        <th><?= __('common.date') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invoices as $inv): ?>
                    <tr>
                        <td><a href="/invoices/<?= $inv['id'] ?>"><?= htmlspecialchars($inv['title']) ?></a></td>
                        <td>
                            <?php $fc = (int)($inv['file_count'] ?? 0); ?>
                            <?php if ($fc > 0): ?>
                                <span class="text-green-500"><i class="fas fa-paperclip"></i> <?= $fc ?></span>
                            <?php else: ?>
                                <span class="text-gray-300">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($inv['tags'])): ?>
                                <?php foreach (explode(',', $inv['tags']) as $tag): ?>
                                    <span class="badge badge-secondary"><?= htmlspecialchars(trim($tag)) ?></span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </td>
                        <td><?= date('Y-m-d', strtotime($inv['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
