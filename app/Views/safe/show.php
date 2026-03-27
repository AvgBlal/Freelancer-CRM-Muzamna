<?php
$title = htmlspecialchars($item['title']);
$routePrefix = $routePrefix ?? '/safe';
$typeLabel = $typeLabel ?? __('safe.title');
$typeIcon = $typeIcon ?? 'fa-shield-alt';
?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= htmlspecialchars($item['title']) ?></h2>
        <div class="flex gap-2">
            <a href="<?= $routePrefix ?>/<?= $item['id'] ?>/edit" class="btn btn-secondary"><i class="fas fa-edit"></i> <?= __('common.edit') ?></a>
            <form method="POST" action="<?= $routePrefix ?>/<?= $item['id'] ?>/delete" onsubmit="return confirm('<?= __('common.confirm_delete') ?>')" style="display:inline;">
                <?= \App\Core\CSRF::field() ?>
                <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> <?= __('common.delete') ?></button>
            </form>
            <a href="<?= $routePrefix ?>" class="btn btn-secondary"><?= __("common.back") ?></a>
        </div>
    </div>

    <?php if (!empty($item['tags'])): ?>
    <div class="mb-3">
        <?php foreach (explode(',', $item['tags']) as $tag): ?>
            <a href="<?= $routePrefix ?>?tag=<?= urlencode(trim($tag)) ?>" class="badge badge-secondary" style="text-decoration:none;"><?= htmlspecialchars(trim($tag)) ?></a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <table class="table" style="width: auto;">
        <?php if (!empty($item['client_name'])): ?>
        <tr>
            <th><i class="fas fa-user text-gray-400"></i> <?= __("common.client") ?>:</th>
            <td><a href="/clients/<?= $item['client_id'] ?>"><?= htmlspecialchars($item['client_name']) ?></a></td>
        </tr>
        <?php endif; ?>
        <?php if (!empty($item['url'])): ?>
        <tr>
            <th><i class="fas fa-link text-gray-400"></i> <?= __("safe.url") ?>:</th>
            <td><a href="<?= htmlspecialchars($item['url']) ?>" target="_blank" class="ltr-input"><?= htmlspecialchars($item['url']) ?> <i class="fas fa-external-link-alt"></i></a></td>
        </tr>
        <?php endif; ?>

        <!-- Multi-file list -->
        <?php $files = $item['files'] ?? []; ?>
        <?php if (!empty($files)): ?>
        <tr>
            <th><i class="fas fa-paperclip text-gray-400"></i> <?= __("safe.files") ?> (<?= count($files) ?>):</th>
            <td>
                <div style="display: flex; flex-direction: column; gap: 6px;">
                <?php foreach ($files as $f): ?>
                    <div>
                        <a href="/safe/files/<?= $f['id'] ?>/download" class="btn btn-sm btn-secondary">
                            <i class="fas fa-download"></i> <?= htmlspecialchars($f['file_original_name']) ?>
                        </a>
                        <small class="text-gray-500">(<?= $f['file_size'] > 1048576 ? number_format($f['file_size'] / 1048576, 1) . ' MB' : number_format($f['file_size'] / 1024, 0) . ' KB' ?>)</small>
                    </div>
                <?php endforeach; ?>
                </div>
            </td>
        </tr>
        <?php endif; ?>

        <!-- Legacy single file -->
        <?php if (!empty($item['file_path']) && empty($files)): ?>
        <tr>
            <th><i class="fas fa-file text-gray-400"></i> <?= __("safe.files") ?>:</th>
            <td>
                <a href="<?= $routePrefix ?>/<?= $item['id'] ?>/download" class="btn btn-sm btn-secondary">
                    <i class="fas fa-download"></i> <?= htmlspecialchars($item['file_original_name']) ?>
                </a>
                <small class="text-gray-500">(<?= $item['file_size'] > 1048576 ? number_format($item['file_size'] / 1048576, 1) . ' MB' : number_format($item['file_size'] / 1024, 0) . ' KB' ?>)</small>
            </td>
        </tr>
        <?php endif; ?>

        <?php if (!empty($item['notes'])): ?>
        <tr>
            <th><i class="fas fa-sticky-note text-gray-400"></i> <?= __("common.notes") ?>:</th>
            <td><?= nl2br(htmlspecialchars($item['notes'])) ?></td>
        </tr>
        <?php endif; ?>
        <tr>
            <th><i class="fas fa-user text-gray-400"></i> <?= __("logs.user") ?>:</th>
            <td><?= htmlspecialchars($item['creator_name'] ?? '-') ?></td>
        </tr>
        <tr>
            <th><i class="fas fa-calendar text-gray-400"></i> <?= __("common.created_at") ?>:</th>
            <td><?= $item['created_at'] ?></td>
        </tr>
        <?php if ($item['updated_at'] !== $item['created_at']): ?>
        <tr>
            <th><i class="fas fa-calendar-check text-gray-400"></i> <?= __("common.date") ?>:</th>
            <td><?= $item['updated_at'] ?></td>
        </tr>
        <?php endif; ?>
    </table>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
