<?php
/**
 * Reusable currency select with search
 * Variables expected:
 *   $currencyFieldName - form field name (default: 'currency_code')
 *   $currencySelected  - currently selected code (default: 'EGP')
 *   $currencyShowCustom - show "custom" option (default: false)
 */
$_currencies = require __DIR__ . '/../../Config/currencies.php';
$_fieldName = $currencyFieldName ?? 'currency_code';
$_selected = $currencySelected ?? 'EGP';
$_showCustom = $currencyShowCustom ?? false;
$_uid = 'cur_' . substr(md5($_fieldName . rand()), 0, 6);
?>
<div class="currency-select-wrapper" id="<?= $_uid ?>_wrap">
    <input type="text" class="form-input" placeholder="<?= __('currency.search') ?>" style="margin-bottom: 4px; font-size: 0.85rem;" oninput="filterCurrency<?= $_uid ?>(this.value)">
    <select name="<?= $_fieldName ?>" class="form-select" id="<?= $_uid ?>">
        <?php foreach ($_currencies as $code => $name): ?>
        <option value="<?= $code ?>" data-search="<?= $code ?> <?= $name ?>" <?= $_selected === $code ? 'selected' : '' ?>><?= $code ?> - <?= $name ?></option>
        <?php endforeach; ?>
        <?php if ($_showCustom): ?>
        <option value="custom" data-search="custom <?= __('currency.custom') ?>" <?= $_selected === 'custom' ? 'selected' : '' ?>><?= __('currency.custom') ?></option>
        <?php endif; ?>
    </select>
</div>
<script>
function filterCurrency<?= $_uid ?>(q) {
    q = q.toLowerCase();
    var sel = document.getElementById('<?= $_uid ?>');
    var opts = sel.options;
    for (var i = 0; i < opts.length; i++) {
        var s = (opts[i].getAttribute('data-search') || '').toLowerCase();
        opts[i].style.display = (!q || s.indexOf(q) !== -1) ? '' : 'none';
    }
}
</script>
