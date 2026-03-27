<?php
$title = __('search.title');
use App\Repositories\SearchRepo;
?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= __("search.comprehensive") ?></h2>
    </div>

    <form method="GET" class="mb-3">
        <div class="flex gap-2">
            <input type="text" name="q" class="form-control" style="flex: 1;"
                   value="<?= htmlspecialchars($query) ?>"
                   placeholder="<?= __('search.placeholder') ?>"
                   autofocus>
            <button type="submit" class="btn btn-primary"><?= __("common.search") ?></button>
        </div>
    </form>

    <?php if (!empty($query)): ?>
        <p class="text-muted mb-2">
            <?= __('search.results') ?> "<strong><?= htmlspecialchars($query) ?></strong>": <?= __('search.result_count', ['count' => $total]) ?>
        </p>

        <?php if (empty($results)): ?>
            <p class="text-muted" style="text-align: center; padding: 2rem;"><?= __('search.no_results') ?></p>
        <?php else: ?>
            <div class="search-results">
                <?php foreach ($results as $result): ?>
                    <div class="search-result-item" style="padding: 0.75rem; border-bottom: 1px solid #e0e0e0;">
                        <div class="flex gap-2" style="align-items: center;">
                            <span class="badge <?= SearchRepo::getModuleBadge($result['module']) ?>" style="min-width: 60px; text-align: center;">
                                <?= SearchRepo::getModuleLabel($result['module']) ?>
                            </span>
                            <div style="flex: 1;">
                                <a href="<?= SearchRepo::getUrl($result['module'], $result['id']) ?>" style="font-weight: 600; text-decoration: none;">
                                    <?= htmlspecialchars($result['title']) ?>
                                </a>
                                <?php if (!empty($result['subtitle'])): ?>
                                    <span class="text-muted" style="font-size: 0.85em; margin-inline-start: 0.5rem;">
                                        <?= htmlspecialchars($result['subtitle']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <span class="text-muted" style="font-size: 0.8em; white-space: nowrap;">
                                <?= $result['created_at'] ?? '' ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php elseif (isset($_GET['q'])): ?>
        <p class="text-muted" style="text-align: center; padding: 2rem;"><?= __('search.min_chars') ?></p>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
