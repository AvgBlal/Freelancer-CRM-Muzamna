<?php $title = __('users.title'); ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= __("users.title") ?></h2>
        <a href="/users/create" class="btn btn-primary">+ <?= __("users.create") ?></a>
    </div>

    <!-- Filters -->
    <form method="GET" class="mb-3">
        <div class="flex gap-2" style="flex-wrap: wrap;">
            <input type="text" name="search" placeholder="<?= __('common.search_placeholder') ?>" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" class="form-input" style="width: 200px;">

            <select name="role" class="form-select" style="width: 150px;">
                <option value=""><?= __("common.all_roles") ?></option>
                <option value="admin" <?= ($_GET['role'] ?? '') === 'admin' ? 'selected' : '' ?>><?= __("users.role.admin") ?></option>
                <option value="manager" <?= ($_GET['role'] ?? '') === 'manager' ? 'selected' : '' ?>><?= __("users.role.manager") ?></option>
                <option value="employee" <?= ($_GET['role'] ?? '') === 'employee' ? 'selected' : '' ?>><?= __("users.role.employee") ?></option>
            </select>

            <select name="department" class="form-select" style="width: 150px;">
                <option value=""><?= __("common.all_departments") ?></option>
                <?php foreach ($departments as $dept): ?>
                    <option value="<?= htmlspecialchars($dept) ?>" <?= ($_GET['department'] ?? '') === $dept ? 'selected' : '' ?>>
                        <?= htmlspecialchars($dept) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="btn btn-secondary"><?= __("common.filter") ?></button>
        </div>
    </form>

    <?php if (empty($users)): ?>
        <p><?= __("users.empty") ?></p>
    <?php else: ?>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th><?= __("users.employee") ?></th>
                        <th><?= __("common.email") ?></th>
                        <th><?= __("users.role") ?></th>
                        <th><?= __("users.department") ?></th>
                        <th><?= __("users.active_tasks") ?></th>
                        <th><?= __("users.capacity") ?></th>
                        <th><?= __("common.status") ?></th>
                        <th><?= __("common.actions") ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <div class="user-info">
                                    <?php if (!empty($user['avatar'])): ?>
                                        <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="" class="avatar-sm">
                                    <?php else: ?>
                                        <div class="avatar-placeholder"><?= mb_substr($user['name'], 0, 1) ?></div>
                                    <?php endif; ?>
                                    <a href="/users/<?= $user['id'] ?>"><?= htmlspecialchars($user['name']) ?></a>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td>
                                <?php
                                $roleBadge = match($user['role']) {
                                    'admin' => 'badge-urgent',
                                    'manager' => 'badge-warning',
                                    default => 'badge-info'
                                };
                                ?>
                                <span class="badge <?= $roleBadge ?>">
                                    <?= match($user['role']) {
                                        'admin' => __('users.role.admin'),
                                        'manager' => __('users.role.manager'),
                                        default => __('users.role.employee')
                                    } ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($user['department'] ?? '-') ?></td>
                            <td><?= $user['active_tasks'] ?? 0 ?></td>
                            <td><?= $user['max_tasks_capacity'] ?></td>
                            <td>
                                <?php if ($user['is_active']): ?>
                                    <span class="badge badge-success"><?= __("common.active") ?></span>
                                <?php else: ?>
                                    <span class="badge badge-secondary"><?= __("common.inactive") ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="flex gap-1">
                                    <a href="/users/<?= $user['id'] ?>" class="btn btn-sm btn-secondary"><?= __("common.view") ?></a>
                                    <a href="/users/<?= $user['id'] ?>/edit" class="btn btn-sm btn-primary"><?= __("common.edit") ?></a>
                                    <?php if ($user['id'] !== ($_SESSION['user_id'] ?? 0)): ?>
                                        <form method="POST" action="/users/<?= $user['id'] ?>/toggle-active" style="display:inline;">
                                            <?= \App\Core\CSRF::field() ?>
                                            <button type="submit" class="btn btn-sm btn-warning">
                                                <?= $user['is_active'] ? __('users.disable') : __('users.enable') ?>
                                            </button>
                                        </form>
                                        <form method="POST" action="/users/<?= $user['id'] ?>/delete" style="display:inline;" onsubmit="return confirm('<?= __('users.confirm_delete') ?>');">
                                            <?= \App\Core\CSRF::field() ?>
                                            <button type="submit" class="btn btn-sm btn-danger"><?= __("common.delete") ?></button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
