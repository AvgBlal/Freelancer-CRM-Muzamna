<?php $title = __('clients.edit'); ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><i class="fas fa-user-edit text-blue-500"></i> <?= __('clients.edit') ?>: <?= htmlspecialchars($client['name']) ?></h2>
    </div>

    <form method="POST" action="/clients/<?= $client['id'] ?>">
        <?= $csrf ?>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label"><?= __('clients.name') ?> *</label>
                <input type="text" name="name" class="form-input" required value="<?= htmlspecialchars($client['name']) ?>">
            </div>
            <div class="form-group">
                <label class="form-label"><?= __('clients.type') ?> *</label>
                <select name="type" class="form-select" required>
                    <option value="individual" <?= $client['type'] === 'individual' ? 'selected' : '' ?>><?= __('clients.individual') ?></option>
                    <option value="company" <?= $client['type'] === 'company' ? 'selected' : '' ?>><?= __('clients.company') ?></option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label"><i class="fas fa-phone text-gray-400"></i> <?= __('common.phone') ?></label>
                <input type="tel" name="phone" class="form-input ltr-input" value="<?= htmlspecialchars($client['phone'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label class="form-label"><i class="fas fa-envelope text-gray-400"></i> <?= __('common.email') ?></label>
                <input type="email" name="email" class="form-input ltr-input" value="<?= htmlspecialchars($client['email'] ?? '') ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label"><i class="fas fa-globe text-gray-400"></i> <?= __('clients.website') ?></label>
                <input type="url" name="website" class="form-input ltr-input" value="<?= htmlspecialchars($client['website'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label class="form-label"><i class="fas fa-comments text-gray-400"></i> <?= __('clients.channel') ?></label>
                <select name="preferred_channel" class="form-select">
                    <option value="">-- --</option>
                    <option value="whatsapp" <?= ($client['preferred_channel'] ?? '') === 'whatsapp' ? 'selected' : '' ?>>WhatsApp</option>
                    <option value="email" <?= ($client['preferred_channel'] ?? '') === 'email' ? 'selected' : '' ?>><?= __('common.email') ?></option>
                    <option value="phone" <?= ($client['preferred_channel'] ?? '') === 'phone' ? 'selected' : '' ?>><?= __('common.phone') ?></option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label"><?= __('clients.country') ?></label>
                <input type="text" name="country" class="form-input" value="<?= htmlspecialchars($client['country'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label class="form-label"><?= __('clients.city') ?></label>
                <input type="text" name="city" class="form-input" value="<?= htmlspecialchars($client['city'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label class="form-label"><?= __('clients.timezone') ?></label>
                <input type="text" name="timezone" class="form-input ltr-input" value="<?= htmlspecialchars($client['timezone'] ?? 'Africa/Cairo') ?>">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label"><?= __('common.notes') ?></label>
            <textarea name="notes" class="form-textarea" rows="3"><?= htmlspecialchars($client['notes'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label class="form-label"><i class="fas fa-tags text-gray-400"></i> <?= __('clients.tags') ?></label>
            <div id="tags-picker" class="flex gap-1" style="flex-wrap: wrap; padding: 8px; border: 1px solid #e2e8f0; border-radius: 8px; min-height: 42px; background: white;">
                <?php foreach ($allTags as $tag):
                    $isSelected = in_array($tag['id'], $clientTags);
                ?>
                <label style="cursor:pointer; margin: 2px;">
                    <input type="checkbox" name="tags[]" value="<?= $tag['id'] ?>" <?= $isSelected ? 'checked' : '' ?> style="display:none;" onchange="this.parentElement.classList.toggle('tag-selected', this.checked)">
                    <span class="badge <?= $isSelected ? 'badge-success' : 'badge-secondary' ?>" style="font-size: 0.85rem; padding: 4px 10px; transition: all 0.15s;"><?= htmlspecialchars($tag['name']) ?></span>
                </label>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Contacts Section -->
        <div class="card" style="background: #f9fafb; margin-top: 1rem;">
            <div class="flex flex-between flex-center mb-2">
                <h3 style="font-size: 1rem;"><i class="fas fa-address-book text-blue-500"></i></h3>
                <button type="button" onclick="addContact()" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus"></i> <?= __('common.add') ?>
                </button>
            </div>
            <div id="contacts-container">
                <!-- Dynamic contact rows inserted here -->
            </div>
            <p id="no-contacts-msg" class="text-muted" style="font-size: 0.85rem; <?= !empty($contacts) ? 'display:none;' : '' ?>"></p>
        </div>

        <div class="flex gap-2 mt-3">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= __('common.save') ?></button>
            <a href="/clients/<?= $client['id'] ?>" class="btn btn-secondary"><?= __('common.cancel') ?></a>
        </div>
    </form>
</div>

<script>
var contactIndex = 0;
function addContact(data) {
    data = data || {};
    var idx = contactIndex++;
    var html = '<div class="contact-row" id="contact-' + idx + '" style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 0.75rem; margin-bottom: 0.75rem; background: white;">'
        + '<div class="flex flex-between flex-center" style="margin-bottom: 0.5rem;">'
        + '<strong style="font-size: 0.85rem;"><i class="fas fa-user"></i> #' + (idx + 1) + '</strong>'
        + '<button type="button" onclick="removeContact(' + idx + ')" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>'
        + '</div>'
        + '<div class="form-row">'
        + '<div class="form-group"><label class="form-label"><?= __('common.name') ?> *</label><input type="text" name="contacts[' + idx + '][name]" class="form-input" required value="' + escHtml(data.name || '') + '"></div>'
        + '<div class="form-group"><label class="form-label"><?= __('common.title') ?></label><input type="text" name="contacts[' + idx + '][job_title]" class="form-input" value="' + escHtml(data.job_title || '') + '"></div>'
        + '</div>'
        + '<div class="form-row">'
        + '<div class="form-group"><label class="form-label"><?= __('common.email') ?></label><input type="email" name="contacts[' + idx + '][email]" class="form-input ltr-input" value="' + escHtml(data.email || '') + '"></div>'
        + '<div class="form-group"><label class="form-label"><?= __('common.phone') ?></label><input type="tel" name="contacts[' + idx + '][phone]" class="form-input ltr-input" value="' + escHtml(data.phone || '') + '"></div>'
        + '</div>'
        + '</div>';
    document.getElementById('contacts-container').insertAdjacentHTML('beforeend', html);
    document.getElementById('no-contacts-msg').style.display = 'none';
}
function removeContact(idx) {
    var el = document.getElementById('contact-' + idx);
    if (el) el.remove();
    if (document.querySelectorAll('.contact-row').length === 0) {
        document.getElementById('no-contacts-msg').style.display = '';
    }
}
function escHtml(s) {
    var d = document.createElement('div');
    d.textContent = s;
    return d.innerHTML.replace(/"/g, '&quot;');
}

// Load existing contacts
<?php foreach ($contacts as $c): ?>
addContact(<?= json_encode([
    'name' => $c['name'],
    'job_title' => $c['job_title'] ?? '',
    'email' => $c['email'] ?? '',
    'phone' => $c['phone'] ?? '',
]) ?>);
<?php endforeach; ?>
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
