<?php $title = __('personal.calendar'); ?>
<?php require __DIR__ . '/../layout/header.php'; ?>
<?php $defaultCurrency = \App\Repositories\SettingsRepo::get('default_currency', 'EGP'); ?>

<?php
$typeLabels = [
    'service_expiry' => __('personal.timeline.service'),
    'expense_due' => __('personal.timeline.expense'),
    'due_collection' => __('personal.timeline.due'),
    'project_deadline' => __('personal.timeline.project'),
    'task_deadline' => __('personal.timeline.task'),
    'note_reminder' => __('personal.timeline.reminder'),
];
$typeBadges = [
    'service_expiry' => 'badge-warning',
    'expense_due' => 'badge-urgent',
    'due_collection' => 'badge-success',
    'project_deadline' => 'badge-info',
    'task_deadline' => 'badge-info',
    'note_reminder' => 'badge-secondary',
];
$typeColors = [
    'service_expiry' => '#ff9800',
    'expense_due' => '#c62828',
    'due_collection' => '#2e7d32',
    'project_deadline' => '#1565c0',
    'task_deadline' => '#1565c0',
    'note_reminder' => '#666',
];

// Group events by date
$grouped = [];
foreach ($events as $event) {
    $grouped[$event['date']][] = $event;
}
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= __('personal.calendar') ?></h2>
        <a href="/personal" class="btn btn-secondary"><?= __("common.back") ?></a>
    </div>

    <!-- Legend -->
    <div class="flex gap-2 mb-3" style="flex-wrap: wrap;">
        <?php foreach ($typeLabels as $type => $label): ?>
            <span class="badge <?= $typeBadges[$type] ?>"><?= $label ?></span>
        <?php endforeach; ?>
    </div>

    <!-- Summary -->
    <?php if (!empty($events)): ?>
    <div class="stats-grid mb-3">
        <div class="stat-card">
            <div class="stat-value"><?= count($events) ?></div>
            <div class="stat-label"><?= __("common.total") ?></div>
        </div>
        <?php
            $totalIncoming = array_sum(array_map(fn($e) => $e['type'] === 'due_collection' ? (float)($e['amount'] ?? 0) : 0, $events));
            $totalOutgoing = array_sum(array_map(fn($e) => in_array($e['type'], ['expense_due', 'service_expiry']) ? (float)($e['amount'] ?? 0) : 0, $events));
        ?>
        <div class="stat-card stat-card-success">
            <div class="stat-value"><?= number_format($totalIncoming, 0) ?></div>
            <div class="stat-label"><?= __('personal.expected_income') ?> (<?= $defaultCurrency ?>)</div>
        </div>
        <div class="stat-card stat-card-danger">
            <div class="stat-value"><?= number_format($totalOutgoing, 0) ?></div>
            <div class="stat-label"><?= __('personal.expected_expense') ?> (<?= $defaultCurrency ?>)</div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (empty($events)): ?>
        <p class="text-muted" style="text-align: center; padding: 2rem;"><?= __('safe.empty') ?></p>
    <?php else: ?>
        <?php foreach ($grouped as $date => $dayEvents): ?>
        <?php
            $isToday = $date === date('Y-m-d');
            $dayName = '';
            $dayOfWeek = date('w', strtotime($date));
            $arabicDays = [__('day.sun'), __('day.mon'), __('day.tue'), __('day.wed'), __('day.thu'), __('day.fri'), __('day.sat')];
            $dayName = $arabicDays[$dayOfWeek];
            $daysFromNow = (int) ((strtotime($date) - strtotime(date('Y-m-d'))) / 86400);
        ?>
        <div class="mb-3" style="border-inline-start: 3px solid <?= $isToday ? '#1565c0' : '#e0e0e0' ?>; padding-inline-start: 1rem;">
            <div class="flex flex-between mb-1">
                <strong style="<?= $isToday ? 'color: #1565c0;' : '' ?>">
                    <?= $dayName ?> - <?= $date ?>
                    <?php if ($isToday): ?>
                        <span class="badge badge-info"><?= __("common.today") ?></span>
                    <?php elseif ($daysFromNow === 1): ?>
                        <span class="text-muted">(<?= __('common.tomorrow') ?>)</span>
                    <?php elseif ($daysFromNow <= 7): ?>
                        <span class="text-muted">(<?= __('common.in_days', ['count' => $daysFromNow]) ?>)</span>
                    <?php endif; ?>
                </strong>
            </div>

            <?php foreach ($dayEvents as $event): ?>
            <div class="flex gap-2 mb-1" style="padding: 0.5rem; background: #f8f9fa; border-radius: 4px; align-items: center;">
                <span style="width: 4px; height: 100%; min-height: 30px; background: <?= $typeColors[$event['type']] ?? '#666' ?>; border-radius: 2px;"></span>
                <span class="badge <?= $typeBadges[$event['type']] ?? 'badge-secondary' ?>" style="white-space: nowrap;"><?= $typeLabels[$event['type']] ?? $event['type'] ?></span>
                <a href="<?= htmlspecialchars($event['link']) ?>" style="flex: 1;"><?= htmlspecialchars($event['title']) ?></a>
                <?php if ($event['amount']): ?>
                    <strong style="white-space: nowrap; color: <?= $event['type'] === 'due_collection' ? '#2e7d32' : ($event['type'] === 'expense_due' ? '#c62828' : '#333') ?>;">
                        <?= number_format((float)$event['amount'], 0) ?> <?= $event['currency_code'] ?? $defaultCurrency ?>
                    </strong>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
