<?php $title = __('users.edit'); ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= __('users.edit') ?>: <?= htmlspecialchars($editUser['name']) ?></h2>
        <a href="/users" class="btn btn-secondary"><?= __("common.back") ?></a>
    </div>

    <form method="POST" action="/users/<?= $editUser['id'] ?>">
        <?= $csrf ?>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="name"><?= __("common.name") ?> *</label>
                <input type="text" id="name" name="name" class="form-input" value="<?= htmlspecialchars($_SESSION['old']['name'] ?? $editUser['name']) ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="email"><?= __("common.email") ?> *</label>
                <input type="email" id="email" name="email" class="form-input ltr-input" value="<?= htmlspecialchars($_SESSION['old']['email'] ?? $editUser['email']) ?>" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="password"><?= __("auth.password") ?></label>
                <input type="password" id="password" name="password" class="form-input" minlength="6">
                <small class="form-hint"><?= __('users.password_empty_hint') ?></small>
            </div>

            <div class="form-group">
                <label class="form-label" for="role"><?= __("users.role") ?> *</label>
                <select id="role" name="role" class="form-select" required>
                    <option value="employee" <?= ($_SESSION['old']['role'] ?? $editUser['role']) === 'employee' ? 'selected' : '' ?>><?= __("users.role.employee") ?></option>
                    <option value="manager" <?= ($_SESSION['old']['role'] ?? $editUser['role']) === 'manager' ? 'selected' : '' ?>><?= __("users.role.manager") ?></option>
                    <option value="admin" <?= ($_SESSION['old']['role'] ?? $editUser['role']) === 'admin' ? 'selected' : '' ?>><?= __("users.role.admin") ?></option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="department"><?= __("users.department") ?></label>
                <input type="text" id="department" name="department" class="form-input" list="departments" value="<?= htmlspecialchars($_SESSION['old']['department'] ?? $editUser['department'] ?? '') ?>">
                <datalist id="departments">
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?= htmlspecialchars($dept) ?>">
                    <?php endforeach; ?>
                </datalist>
            </div>

            <div class="form-group">
                <label class="form-label" for="max_tasks_capacity"><?= __("users.capacity") ?></label>
                <input type="number" id="max_tasks_capacity" name="max_tasks_capacity" class="form-input" value="<?= $_SESSION['old']['max_tasks_capacity'] ?? $editUser['max_tasks_capacity'] ?? 5 ?>" min="1" max="20">
                <small class="form-hint"><?= __('users.capacity_hint') ?></small>
            </div>
        </div>

        <div class="form-group">
            <label class="checkbox-label">
                <input type="checkbox" name="is_active" <?= (isset($_SESSION['old']) ? !empty($_SESSION['old']['is_active']) : !empty($editUser['is_active'])) ? 'checked' : '' ?>>
                <?= __('users.active_login') ?>
            </label>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary"><?= __("common.save") ?></button>
            <a href="/users/<?= $editUser['id'] ?>" class="btn btn-secondary"><?= __("common.details") ?></a>
            <a href="/users" class="btn btn-secondary"><?= __("common.cancel") ?></a>
        </div>
    </form>
</div>

<?php unset($_SESSION['old']); ?>
<?php require __DIR__ . '/../layout/footer.php'; ?>
