<?php
/**
 * Multi-Currency Stat Display
 * Renders monetary values grouped by currency, each on its own line.
 *
 * Usage: $currencyValues = [['currency_code' => 'EGP', 'total' => 1500], ['currency_code' => 'USD', 'total' => 200]]
 * Set $currencyDecimals = 2 before including for decimal display (default: 0)
 */
$currencyDecimals = $currencyDecimals ?? 0;
$hasValues = false;
foreach ($currencyValues as $cv):
    if ((float)$cv['total'] == 0) continue;
    $hasValues = true;
?>
<div style="white-space: nowrap;"><?= number_format((float)$cv['total'], $currencyDecimals) ?> <small style="opacity:.7"><?= htmlspecialchars($cv['currency_code']) ?></small></div>
<?php endforeach;
if (!$hasValues): ?>
<div>0</div>
<?php endif; ?>
