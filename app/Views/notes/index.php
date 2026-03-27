<?php $title = __('notes.title'); ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<?php
$categoryLabels = ['general' => __('notes.cat.general'), 'idea' => __('notes.cat.idea'), 'reminder' => __('notes.cat.reminder'), 'financial' => __('notes.cat.financial'), 'personal' => __('notes.cat.personal')];
$priorityLabels = ['low' => __('notes.priority.low'), 'normal' => __('notes.priority.normal'), 'high' => __('notes.priority.high')];
$priorityBadges = ['low' => 'badge-secondary', 'normal' => 'badge-info', 'high' => 'badge-urgent'];
$categoryBadges = ['general' => 'badge-secondary', 'idea' => 'badge-info', 'reminder' => 'badge-warning', 'financial' => 'badge-success', 'personal' => 'badge-info'];
?>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value"><?= $stats['active'] ?></div>
        <div class="stat-label"><?= __("notes.active") ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $stats['pinned'] ?></div>
        <div class="stat-label"><?= __("notes.pinned") ?></div>
    </div>
    <div class="stat-card <?= $stats['overdue'] > 0 ? 'stat-card-danger' : '' ?>">
        <div class="stat-value"><?= $stats['overdue'] ?></div>
        <div class="stat-label"><?= __("notes.overdue") ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $stats['archived'] ?></div>
        <div class="stat-label"><?= __("notes.archived") ?></div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= __('notes.title') ?></h2>
        <a href="/notes/create" class="btn btn-primary">+ <?= __("notes.new") ?></a>
    </div>

    <!-- Filters -->
    <form method="GET" action="/notes" class="mb-3">
        <div class="flex gap-2" style="flex-wrap: wrap; align-items: end;">
            <div class="form-group" style="flex: 2; min-width: 200px;">
                <input type="text" name="search" class="form-control" placeholder="<?= __('common.search_placeholder') ?>" value="<?= htmlspecialchars($filters['search']) ?>">
            </div>
            <div class="form-group" style="flex: 1; min-width: 120px;">
                <select name="category" class="form-control">
                    <option value=""><?= __("common.all_categories") ?></option>
                    <?php foreach ($categoryLabels as $key => $label): ?>
                        <option value="<?= $key ?>" <?= $filters['category'] === $key ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="flex: 1; min-width: 120px;">
                <select name="priority" class="form-control">
                    <option value=""><?= __("common.all_priorities") ?></option>
                    <?php foreach ($priorityLabels as $key => $label): ?>
                        <option value="<?= $key ?>" <?= $filters['priority'] === $key ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="flex: 1; min-width: 120px;">
                <select name="status" class="form-control">
                    <option value=""><?= __('notes.active_status') ?></option>
                    <option value="archived" <?= $filters['status'] === 'archived' ? 'selected' : '' ?>><?= __('notes.archived_status') ?></option>
                </select>
            </div>
            <button type="submit" class="btn btn-secondary"><?= __("common.search") ?></button>
            <?php if (!empty($filters['search']) || !empty($filters['category']) || !empty($filters['priority']) || !empty($filters['status'])): ?>
                <a href="/notes" class="btn btn-secondary"><?= __("common.clear") ?></a>
            <?php endif; ?>
        </div>
    </form>

    <?php if (empty($notes)): ?>
        <p class="text-muted" style="text-align: center; padding: 2rem;"><?= __("notes.empty") ?></p>
    <?php else: ?>
        <div class="table-container">
            <table class="table bulk-table">
                <thead>
                    <tr>
                        <th style="width: 30px;"><input type="checkbox" class="bulk-select-all"></th>
                        <th style="width: 30px;"></th>
                        <th><?= __("common.title") ?></th>
                        <th><?= __("expenses.category") ?></th>
                        <th><?= __("common.status") ?></th>
                        <th><?= __("common.due_date") ?></th>
                        <th><?= __("common.actions") ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($notes as $note): ?>
                    <?php
                        $isOverdue = $note['due_date'] && $note['due_date'] < date('Y-m-d');
                    ?>
                    <tr<?= $note['is_pinned'] ? ' style="background: #fffde7;"' : '' ?>>
                        <td><input type="checkbox" class="bulk-check" value="<?= $note['id'] ?>"></td>
                        <td>
                            <?php if ($note['is_pinned']): ?>
                                <span title="<?= __('notes.pinned') ?>">&#128204;</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="/notes/<?= $note['id'] ?>"><?= htmlspecialchars($note['title']) ?></a>
                            <?php if ($note['content']): ?>
                                <div class="text-muted" style="font-size: 0.85rem; max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    <?= htmlspecialchars(mb_substr(strip_tags($note['content']), 0, 80)) ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge <?= $categoryBadges[$note['category']] ?? 'badge-secondary' ?>"><?= $categoryLabels[$note['category']] ?? $note['category'] ?></span></td>
                        <td><span class="badge <?= $priorityBadges[$note['priority']] ?? 'badge-info' ?>"><?= $priorityLabels[$note['priority']] ?? $note['priority'] ?></span></td>
                        <td>
                            <?php if ($note['due_date']): ?>
                                <span class="<?= $isOverdue ? 'text-danger' : '' ?>"><?= htmlspecialchars($note['due_date'] ?? '') ?></span>
                                <?php if ($isOverdue): ?>
                                    <span class="badge badge-urgent"><?= __("common.overdue") ?></span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="flex gap-1">
                                <a href="/notes/<?= $note['id'] ?>" class="btn btn-sm btn-secondary"><?= __("common.view") ?></a>
                                <a href="/notes/<?= $note['id'] ?>/edit" class="btn btn-sm btn-secondary"><?= __("common.edit") ?></a>
                                <form method="POST" action="/notes/<?= $note['id'] ?>/toggle-pin" style="display:inline;">
                                    <?= \App\Core\CSRF::field() ?>
                                    <button type="submit" class="btn btn-sm btn-secondary"><?= $note['is_pinned'] ? __('notes.unpin') : __('notes.pin') ?></button>
                                </form>
                                <?php if ($note['status'] === 'active'): ?>
                                    <form method="POST" action="/notes/<?= $note['id'] ?>/archive" style="display:inline;">
                                        <?= \App\Core\CSRF::field() ?>
                                        <button type="submit" class="btn btn-sm btn-secondary"><?= __("notes.archive") ?></button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" action="/notes/<?= $note['id'] ?>/restore" style="display:inline;">
                                        <?= \App\Core\CSRF::field() ?>
                                        <button type="submit" class="btn btn-sm btn-secondary"><?= __("notes.restore") ?></button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <?php
                    $params = array_merge($filters, ['page' => $p]);
                    $params = array_filter($params);
                    $qs = http_build_query($params);
                ?>
                <a href="/notes?<?= $qs ?>" class="btn btn-sm <?= $p === $page ? 'btn-primary' : 'btn-secondary' ?>"><?= $p ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>

        <?php $bulkAction = '/bulk/notes'; require __DIR__ . '/../partials/bulk_actions.php'; ?>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
