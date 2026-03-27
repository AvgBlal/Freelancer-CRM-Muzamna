<?php $title = __('dues.create'); ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= __('dues.create') ?></h2>
    </div>

    <form method="POST" action="/dues">
        <?= $csrf ?>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label"><?= __('dues.person_name') ?> *</label>
                <input type="text" name="person_name" class="form-input" required value="<?= htmlspecialchars($_SESSION['old']['person_name'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label class="form-label"><?= __('common.phone') ?></label>
                <input type="text" name="person_phone" class="form-input ltr-input" value="<?= htmlspecialchars($_SESSION['old']['person_phone'] ?? '') ?>">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label"><?= __('common.description') ?></label>
            <textarea name="description" class="form-textarea" rows="3"><?= htmlspecialchars($_SESSION['old']['description'] ?? '') ?></textarea>
        </div>

        <div class="form-row flex-wrap">
            <div class="form-group">
                <label class="form-label"><?= __('common.amount') ?> *</label>
                <input type="number" name="amount" step="0.01" min="0" class="form-input ltr-input" required value="<?= htmlspecialchars($_SESSION['old']['amount'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label class="form-label"><?= __('common.currency') ?></label>
                <?php $currencyFieldName = 'currency_code'; $currencySelected = $_SESSION['old']['currency_code'] ?? 'EGP'; ?>
                <?php require __DIR__ . '/../partials/currency_select.php'; ?>
            </div>

            <div class="form-group">
                <label class="form-label"><?= __("common.due_date") ?></label>
                <input type="date" name="due_date" class="form-input ltr-input" value="<?= htmlspecialchars($_SESSION['old']['due_date'] ?? '') ?>">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label"><?= __("common.notes") ?></label>
            <textarea name="notes" class="form-textarea" rows="2"><?= htmlspecialchars($_SESSION['old']['notes'] ?? '') ?></textarea>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary"><?= __("common.save") ?></button>
            <a href="/dues" class="btn btn-secondary"><?= __("common.cancel") ?></a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
