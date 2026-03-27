<?php
$title = $typeLabel ?? __('safe.title');
$routePrefix = $routePrefix ?? '/safe';
$typeIcon = $typeIcon ?? 'fa-shield-alt';
$type = $type ?? 'general';
?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="flex gap-3 mb-4" style="flex-wrap: wrap;">
    <div class="card" style="flex:1; min-width: 150px; margin-bottom: 0;">
        <div class="text-center">
            <div class="text-2xl font-bold text-blue-600"><?= $stats['total'] ?></div>
            <div class="text-sm text-gray-500"><?= __("safe.total") ?></div>
        </div>
    </div>
    <div class="card" style="flex:1; min-width: 150px; margin-bottom: 0;">
        <div class="text-center">
            <div class="text-2xl font-bold text-green-600"><?= $stats['withFiles'] ?></div>
            <div class="text-sm text-gray-500"><?= __("safe.with_files") ?></div>
        </div>
    </div>
    <div class="card" style="flex:1; min-width: 150px; margin-bottom: 0;">
        <div class="text-center">
            <div class="text-2xl font-bold text-purple-600"><?= $stats['withUrls'] ?></div>
            <div class="text-sm text-gray-500"><?= __("safe.with_urls") ?></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><i class="fas <?= $typeIcon ?> text-blue-500"></i> <?= $title ?></h2>
        <a href="<?= $routePrefix ?>/create" class="btn btn-primary"><i class="fas fa-plus"></i> <?= __("safe.create") ?></a>
    </div>

    <form method="GET" class="mb-3">
        <div class="flex gap-2" style="flex-wrap: wrap;">
            <input type="text" name="search" placeholder="<?= __('safe.search_placeholder') ?>" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" class="form-input" style="width: 250px;">
            <select name="client_id" class="form-select" style="width: 200px;">
                <option value=""><?= __("common.all_clients") ?></option>
                <?php foreach ($clients as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= ($_GET['client_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="tag" class="form-select" style="width: 200px;">
                <option value=""><?= __("common.all_tags") ?></option>
                <?php foreach ($allTags as $tagName => $tagCount): ?>
                    <option value="<?= htmlspecialchars($tagName) ?>" <?= ($_GET['tag'] ?? '') === $tagName ? 'selected' : '' ?>><?= htmlspecialchars($tagName) ?> (<?= $tagCount ?>)</option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-secondary"><?= __("common.filter") ?></button>
            <?php if (!empty($_GET['search']) || !empty($_GET['tag']) || !empty($_GET['client_id'])): ?>
                <a href="<?= $routePrefix ?>" class="btn btn-secondary"><?= __("common.clear") ?></a>
            <?php endif; ?>
        </div>
    </form>

    <?php if (empty($items)): ?>
        <p class="text-muted"><?= __("safe.empty") ?></p>
    <?php else: ?>
        <div class="table-container">
            <table class="table bulk-table">
                <thead>
                    <tr>
                        <th style="width: 30px;"><input type="checkbox" class="bulk-select-all"></th>
                        <th><?= __("common.title") ?></th>
                        <th><?= __("common.client") ?></th>
                        <th><?= __("safe.url") ?></th>
                        <th><?= __("safe.files") ?></th>
                        <th><?= __("clients.tags") ?></th>
                        <th><?= __("common.date") ?></th>
                        <th><?= __("common.actions") ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td><input type="checkbox" class="bulk-check" value="<?= $item['id'] ?>"></td>
                        <td><a href="<?= $routePrefix ?>/<?= $item['id'] ?>"><?= htmlspecialchars($item['title']) ?></a></td>
                        <td>
                            <?php if (!empty($item['client_name'])): ?>
                                <a href="/clients/<?= $item['client_id'] ?>"><?= htmlspecialchars($item['client_name']) ?></a>
                            <?php else: ?>
                                <span class="text-gray-300">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($item['url'])): ?>
                                <a href="<?= htmlspecialchars($item['url']) ?>" target="_blank" title="<?= htmlspecialchars($item['url']) ?>"><i class="fas fa-external-link-alt text-blue-500"></i></a>
                            <?php else: ?>
                                <span class="text-gray-300">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $fc = (int)($item['file_count'] ?? 0);
                            if (!$fc && !empty($item['file_path'])) $fc = 1;
                            ?>
                            <?php if ($fc > 0): ?>
                                <span class="text-green-500"><i class="fas fa-paperclip"></i> <?= $fc ?></span>
                            <?php else: ?>
                                <span class="text-gray-300">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($item['tags'])): ?>
                                <?php foreach (explode(',', $item['tags']) as $tag): ?>
                                    <a href="<?= $routePrefix ?>?tag=<?= urlencode(trim($tag)) ?>" class="badge badge-secondary" style="text-decoration:none;"><?= htmlspecialchars(trim($tag)) ?></a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </td>
                        <td><?= date('Y-m-d', strtotime($item['created_at'])) ?></td>
                        <td>
                            <div class="flex gap-1">
                                <a href="<?= $routePrefix ?>/<?= $item['id'] ?>" class="btn btn-sm btn-secondary"><?= __("common.view") ?></a>
                                <a href="<?= $routePrefix ?>/<?= $item['id'] ?>/edit" class="btn btn-sm btn-secondary"><?= __("common.edit") ?></a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
        <div class="flex gap-2 mt-3" style="justify-content: center;">
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <?php
                $queryParams = $_GET;
                $queryParams['page'] = $p;
                $qs = http_build_query($queryParams);
                ?>
                <a href="<?= $routePrefix ?>?<?= $qs ?>" class="btn btn-sm <?= $p === $page ? 'btn-primary' : 'btn-secondary' ?>"><?= $p ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>

        <?php $bulkAction = '/bulk/safe'; require __DIR__ . '/../partials/bulk_actions.php'; ?>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
