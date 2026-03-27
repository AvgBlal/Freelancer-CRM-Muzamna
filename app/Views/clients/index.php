<?php $title = __('clients.title'); ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= __('clients.title') ?></h2>
        <div class="flex gap-2">
            <div class="dropdown" style="position:relative;display:inline-block;">
                <button type="button" onclick="this.nextElementSibling.classList.toggle('hidden')" class="btn btn-secondary"><i class="fas fa-download"></i> <?= __('common.export') ?></button>
                <div class="hidden" style="position:absolute;inset-inline-start:0;top:100%;background:white;border:1px solid #e2e8f0;border-radius:6px;box-shadow:0 4px 12px rgba(0,0,0,.15);z-index:50;min-width:160px;margin-top:4px;">
                    <a href="/export/clients" class="dropdown-item" style="display:block;padding:8px 16px;text-decoration:none;color:#333;white-space:nowrap;"><i class="fas fa-file-csv text-green-600"></i> <?= __('common.export_csv') ?></a>
                    <a href="/export/clients/pdf" class="dropdown-item" style="display:block;padding:8px 16px;text-decoration:none;color:#333;white-space:nowrap;"><i class="fas fa-file-pdf text-red-600"></i> <?= __('common.export_pdf') ?></a>
                </div>
            </div>
            <a href="/clients/create" class="btn btn-primary">+ <?= __('clients.new') ?></a>
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" class="mb-3">
        <div class="flex gap-2" style="flex-wrap: wrap;">
            <input type="text" name="search" placeholder="<?= __('common.search_placeholder') ?>" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" class="form-input" style="width: 200px;">

            <select name="type" class="form-select" style="width: 150px;">
                <option value=""><?= __('common.all_types') ?></option>
                <option value="individual" <?= ($_GET['type'] ?? '') === 'individual' ? 'selected' : '' ?>><?= __('clients.individual') ?></option>
                <option value="company" <?= ($_GET['type'] ?? '') === 'company' ? 'selected' : '' ?>><?= __('clients.company') ?></option>
            </select>

            <select name="tag" class="form-select" style="width: 150px;">
                <option value=""><?= __('common.all_tags') ?></option>
                <?php foreach ($tags as $tag): ?>
                    <option value="<?= htmlspecialchars($tag['name']) ?>" <?= ($_GET['tag'] ?? '') === $tag['name'] ? 'selected' : '' ?>><?= htmlspecialchars($tag['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="btn btn-secondary"><?= __('common.filter') ?></button>
        </div>
    </form>

    <?php if (empty($clients)): ?>
        <p><?= __('clients.empty') ?></p>
    <?php else: ?>
        <div class="table-container">
            <table class="table bulk-table">
                <thead>
                    <tr>
                        <th style="width: 30px;"><input type="checkbox" class="bulk-select-all"></th>
                        <th><?= __('common.name') ?></th>
                        <th><?= __('common.type') ?></th>
                        <th><?= __('common.phone') ?></th>
                        <th><?= __('common.email') ?></th>
                        <th><?= __('clients.tags') ?></th>
                        <th><?= __('common.actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clients as $client): ?>
                        <tr>
                            <td><input type="checkbox" class="bulk-check" value="<?= $client['id'] ?>"></td>
                            <td><a href="/clients/<?= $client['id'] ?>"><?= htmlspecialchars($client['name']) ?></a></td>
                            <td><?= $client['type'] === 'individual' ? __('clients.individual') : __('clients.company') ?></td>
                            <td><?= htmlspecialchars($client['phone'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($client['email'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($client['tag_list'] ?? '-') ?></td>
                            <td class="flex gap-1">
                                <a href="/clients/<?= $client['id'] ?>" class="btn btn-secondary" style="padding: 0.25rem 0.5rem;"><?= __('common.view') ?></a>
                                <a href="/clients/<?= $client['id'] ?>/edit" class="btn btn-secondary" style="padding: 0.25rem 0.5rem;"><?= __('common.edit') ?></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php $bulkAction = '/bulk/clients'; require __DIR__ . '/../partials/bulk_actions.php'; ?>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
