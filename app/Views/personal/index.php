<?php $title = __('personal.title'); ?>
<?php require __DIR__ . '/../layout/header.php'; ?>
<?php $defaultCurrency = \App\Repositories\SettingsRepo::get('default_currency', 'EGP'); ?>

<!-- Quick Summary -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value"><?= $personalServiceCount ?></div>
        <div class="stat-label"><?= __("common.personal") ?></div>
    </div>
    <div class="stat-card stat-card-success">
        <div class="stat-value"><?php $currencyValues = $personalMRR; require __DIR__ . '/../partials/currency_stat.php'; ?></div>
        <div class="stat-label"><?= __("finance.monthly_recurring") ?></div>
    </div>
    <div class="stat-card <?= !empty($dueStats['totalPendingByCurrency']) ? 'stat-card-warning' : '' ?>">
        <div class="stat-value"><?php $currencyValues = $dueStats['totalPendingByCurrency'] ?? []; require __DIR__ . '/../partials/currency_stat.php'; ?></div>
        <div class="stat-label"><?= __("finance.pending_dues") ?></div>
    </div>
    <div class="stat-card <?= !empty($expenseStats['totalPendingByCurrency']) ? 'stat-card-danger' : '' ?>">
        <div class="stat-value"><?php $currencyValues = $expenseStats['totalPendingByCurrency'] ?? []; require __DIR__ . '/../partials/currency_stat.php'; ?></div>
        <div class="stat-label"><?= __("finance.pending_expenses") ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $noteStats['active'] ?></div>
        <div class="stat-label"><?= __("notes.active") ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><a href="/personal/calendar" style="color: inherit;"><?= __("common.view") ?></a></div>
        <div class="stat-label"><?= __("personal.calendar") ?></div>
    </div>
</div>

<!-- Pinned Notes -->
<?php if (!empty($pinnedNotes)): ?>
<div class="card">
    <div class="card-header">
        <h2 class="card-title">&#128204; <?= __('notes.pinned') ?></h2>
        <a href="/notes" class="btn btn-sm btn-secondary"><?= __("common.view_all") ?></a>
    </div>
    <div class="flex gap-2" style="flex-wrap: wrap;">
        <?php foreach ($pinnedNotes as $note): ?>
        <div style="flex: 1; min-width: 200px; max-width: 350px; padding: 0.75rem; border: 1px solid #e0e0e0; border-radius: 6px; background: <?= $note['color'] && $note['color'] !== '#ffffff' ? htmlspecialchars($note['color']) . '22' : '#fffde7' ?>;">
            <a href="/notes/<?= $note['id'] ?>"><strong><?= htmlspecialchars($note['title']) ?></strong></a>
            <?php if ($note['content']): ?>
                <div class="text-muted" style="font-size: 0.85rem; margin-top: 0.25rem;">
                    <?= htmlspecialchars(mb_substr($note['content'], 0, 100)) ?><?= mb_strlen($note['content']) > 100 ? '...' : '' ?>
                </div>
            <?php endif; ?>
            <?php if ($note['due_date']): ?>
                <div style="font-size: 0.8rem; margin-top: 0.5rem; color: <?= $note['due_date'] < date('Y-m-d') ? '#c62828' : '#666' ?>;">
                    <?= htmlspecialchars($note['due_date'] ?? '') ?>
                </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Overdue Notes -->
<?php if (!empty($overdueNotes)): ?>
<div class="card" style="border-inline-start: 4px solid #c62828;">
    <div class="card-header">
        <h2 class="card-title" style="color: #c62828;"><?= __("common.overdue") ?></h2>
    </div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th><?= __("common.title") ?></th>
                    <th><?= __("common.due_date") ?></th>
                    <th><?= __("projects.days_overdue") ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($overdueNotes as $note): ?>
                <tr>
                    <td><a href="/notes/<?= $note['id'] ?>"><?= htmlspecialchars($note['title']) ?></a></td>
                    <td><?= htmlspecialchars($note['due_date'] ?? '') ?></td>
                    <td><span class="badge badge-urgent"><?= $note['days_overdue'] ?> <?= __('common.day') ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<div class="flex gap-3" style="flex-wrap: wrap;">
    <!-- Personal Services Expiring -->
    <div style="flex: 1; min-width: 300px;">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title"><?= __("dashboard.expiring_services") ?></h2>
            </div>
            <?php if (empty($personalExpiringServices)): ?>
                <p class="text-muted" style="text-align: center; padding: 1rem;"><?= __('dashboard.expiring_none') ?></p>
            <?php else: ?>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr><th><?= __("services.service") ?></th><th><?= __("services.end_date") ?></th><th><?= __("services.price") ?></th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($personalExpiringServices as $s): ?>
                            <?php
                                $daysUntil = (int) ((strtotime($s['end_date']) - time()) / 86400);
                                $urgency = $daysUntil <= 7 ? 'badge-urgent' : 'badge-warning';
                            ?>
                            <tr>
                                <td><a href="/services/<?= $s['id'] ?>"><?= htmlspecialchars($s['title']) ?></a></td>
                                <td><span class="badge <?= $urgency ?>"><?= htmlspecialchars($s['end_date'] ?? '') ?></span></td>
                                <td><?= $s['price_amount'] ? number_format((float)$s['price_amount'], 0) . ' ' . ($s['currency_code'] ?? 'EGP') : '—' ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Upcoming Expenses -->
    <div style="flex: 1; min-width: 300px;">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title"><?= __("dashboard.upcoming_expenses") ?></h2>
                <a href="/expenses" class="btn btn-sm btn-secondary"><?= __("common.view_all") ?></a>
            </div>
            <?php if (empty($upcomingExpenses)): ?>
                <p class="text-muted" style="text-align: center; padding: 1rem;"><?= __('expenses.empty') ?></p>
            <?php else: ?>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr><th><?= __("expenses.expense") ?></th><th><?= __("common.date") ?></th><th><?= __("common.amount") ?></th></tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($upcomingExpenses, 0, 5) as $e): ?>
                            <tr>
                                <td><a href="/expenses/<?= $e['id'] ?>"><?= htmlspecialchars($e['title']) ?></a></td>
                                <td><?= htmlspecialchars($e['due_date'] ?? '') ?></td>
                                <td><?= number_format((float)$e['amount'], 0) ?> <?= $e['currency_code'] ?? $defaultCurrency ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Overdue Dues & Expenses -->
<?php if (!empty($overdueDues) || !empty($overdueExpenses)): ?>
<div class="card" style="border-inline-start: 4px solid #c62828;">
    <div class="card-header">
        <h2 class="card-title" style="color: #c62828;"><?= __("common.overdue") ?></h2>
    </div>
    <div class="flex gap-3" style="flex-wrap: wrap;">
        <?php if (!empty($overdueDues)): ?>
        <div style="flex: 1; min-width: 300px;">
            <h3 class="mb-2"><?= __("dashboard.overdue_dues") ?></h3>
            <table class="table">
                <thead><tr><th><?= __("dues.person") ?></th><th><?= __("common.amount") ?></th><th><?= __("projects.days_overdue") ?></th></tr></thead>
                <tbody>
                    <?php foreach ($overdueDues as $d): ?>
                    <tr>
                        <td><a href="/dues/<?= $d['id'] ?>"><?= htmlspecialchars($d['person_name']) ?></a></td>
                        <td><?= number_format((float)$d['amount'] - (float)($d['paid_amount'] ?? 0), 0) ?> <?= $d['currency_code'] ?? $defaultCurrency ?></td>
                        <td><span class="badge badge-urgent"><?= $d['days_overdue'] ?> <?= __('common.day') ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <?php if (!empty($overdueExpenses)): ?>
        <div style="flex: 1; min-width: 300px;">
            <h3 class="mb-2"><?= __("dashboard.overdue_expenses") ?></h3>
            <table class="table">
                <thead><tr><th><?= __("expenses.expense") ?></th><th><?= __("common.amount") ?></th><th><?= __("projects.days_overdue") ?></th></tr></thead>
                <tbody>
                    <?php foreach ($overdueExpenses as $e): ?>
                    <tr>
                        <td><a href="/expenses/<?= $e['id'] ?>"><?= htmlspecialchars($e['title']) ?></a></td>
                        <td><?= number_format((float)$e['amount'], 0) ?> <?= $e['currency_code'] ?? $defaultCurrency ?></td>
                        <td><span class="badge badge-urgent"><?= $e['days_overdue'] ?> <?= __('common.day') ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- Upcoming Notes -->
<?php if (!empty($upcomingNotes)): ?>
<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= __("common.overdue") ?></h2>
        <a href="/notes" class="btn btn-sm btn-secondary"><?= __("common.view_all") ?></a>
    </div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr><th><?= __("common.title") ?></th><th><?= __("expenses.category") ?></th><th><?= __("common.date") ?></th><th><?= __("common.remaining") ?></th></tr>
            </thead>
            <tbody>
                <?php
                $categoryLabels = ['general' => __('notes.cat.general'), 'idea' => __('notes.cat.idea'), 'reminder' => __('notes.cat.reminder'), 'financial' => __('notes.cat.financial'), 'personal' => __('notes.cat.personal')];
                ?>
                <?php foreach ($upcomingNotes as $n): ?>
                <tr>
                    <td><a href="/notes/<?= $n['id'] ?>"><?= htmlspecialchars($n['title']) ?></a></td>
                    <td><?= $categoryLabels[$n['category']] ?? $n['category'] ?></td>
                    <td><?= htmlspecialchars($n['due_date'] ?? '') ?></td>
                    <td><?= $n['days_until'] ?> <?= __('common.day') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- All Personal Services -->
<?php if (!empty($personalServices)): ?>
<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= __("services.title") ?></h2>
        <a href="/services?is_personal=1" class="btn btn-sm btn-secondary"><?= __("common.view") ?></a>
    </div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr><th><?= __("services.service") ?></th><th><?= __("common.type") ?></th><th><?= __("services.end_date") ?></th><th><?= __("services.price") ?></th><th><?= __("services.billing_cycle") ?></th></tr>
            </thead>
            <tbody>
                <?php
                require __DIR__ . '/../partials/service_types.php';
                $typeLabels = $serviceTypeLabels;
                $cycleLabels = ['monthly' => __('services.cycle.monthly'), 'yearly' => __('services.cycle.yearly'), 'one_time' => __('services.cycle.one_time')];
                ?>
                <?php foreach ($personalServices as $s): ?>
                <tr>
                    <td><a href="/services/<?= $s['id'] ?>"><?= htmlspecialchars($s['title']) ?></a></td>
                    <td><?= $typeLabels[$s['type']] ?? $s['type'] ?></td>
                    <td><?= htmlspecialchars($s['end_date'] ?? '') ?></td>
                    <td><?= $s['price_amount'] ? number_format((float)$s['price_amount'], 0) . ' ' . ($s['currency_code'] ?? 'EGP') : '—' ?></td>
                    <td><?= $cycleLabels[$s['billing_cycle']] ?? ($s['billing_cycle'] ?? '—') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
