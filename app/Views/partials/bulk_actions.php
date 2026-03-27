<?php
/**
 * Bulk Actions Bar Partial
 * Include this after the table inside the form.
 *
 * Required variables:
 * - $bulkAction: The URL for the bulk action form (e.g., '/bulk/services')
 * - $bulkOptions: (optional) Array of extra status options ['value' => 'Label']
 */
$bulkOptions = $bulkOptions ?? [];
?>
<!-- Bulk Actions Bar -->
<div id="bulk-bar" class="hidden" style="position: sticky; bottom: 0; background: #1e293b; border-top: 2px solid #3b82f6; padding: 0.75rem 1rem; display: flex; align-items: center; gap: 1rem; z-index: 10;">
    <span class="text-sm" style="color: #94a3b8;" id="bulk-count"><?= str_replace(':count', '0', __('bulk.selected')) ?></span>
    <form method="POST" action="<?= $bulkAction ?>" id="bulk-form" style="display: flex; gap: 0.5rem; align-items: center; margin: 0;">
        <?= \App\Core\CSRF::field() ?>
        <input type="hidden" name="bulk_action" id="bulk-action-input" value="">
        <div id="bulk-ids-container"></div>
        <?php if (!empty($bulkOptions)): ?>
            <?php foreach ($bulkOptions as $value => $label): ?>
                <button type="submit" class="btn btn-sm btn-secondary bulk-action-btn" data-action="<?= $value ?>"><?= $label ?></button>
            <?php endforeach; ?>
        <?php endif; ?>
        <button type="submit" class="btn btn-sm btn-danger bulk-action-btn" data-action="delete" data-confirm="<?= __('bulk.confirm_delete') ?>"><?= __('bulk.delete_selected') ?></button>
    </form>
</div>

<script>
(function() {
    const table = document.querySelector('.bulk-table');
    if (!table) return;

    const selectAll = table.querySelector('.bulk-select-all');
    const bar = document.getElementById('bulk-bar');
    const countEl = document.getElementById('bulk-count');
    const idsContainer = document.getElementById('bulk-ids-container');

    function getCheckboxes() {
        return table.querySelectorAll('.bulk-check');
    }

    function updateBar() {
        const checked = table.querySelectorAll('.bulk-check:checked');
        const count = checked.length;

        if (count > 0) {
            bar.classList.remove('hidden');
            bar.style.display = 'flex';
        } else {
            bar.classList.add('hidden');
            bar.style.display = 'none';
        }

        countEl.textContent = '<?= __('bulk.selected') ?>'.replace(':count', count);

        idsContainer.innerHTML = '';
        checked.forEach(function(cb) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = cb.value;
            idsContainer.appendChild(input);
        });
    }

    selectAll.addEventListener('change', function() {
        getCheckboxes().forEach(function(cb) {
            cb.checked = selectAll.checked;
        });
        updateBar();
    });

    getCheckboxes().forEach(function(cb) {
        cb.addEventListener('change', function() {
            const all = getCheckboxes();
            const checked = table.querySelectorAll('.bulk-check:checked');
            selectAll.checked = all.length === checked.length;
            selectAll.indeterminate = checked.length > 0 && checked.length < all.length;
            updateBar();
        });
    });

    // Set hidden input value before form submits so it survives button disable
    document.querySelectorAll('.bulk-action-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            var confirmMsg = btn.getAttribute('data-confirm');
            if (confirmMsg && !confirm(confirmMsg)) {
                e.preventDefault();
                return;
            }
            document.getElementById('bulk-action-input').value = btn.getAttribute('data-action');
        });
    });
})();
</script>